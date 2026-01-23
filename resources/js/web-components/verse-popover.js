import {customElement, property, state} from "lit/decorators.js";
import {html, nothing} from "lit";
import {TBPElement} from "./base.js";
import {apiUrl, reference_from_content} from "../helpers.js";
import {$selectedLanguage, $selectedBible, $availableLanguages} from "../stores/settings.js";
import {css} from "@spectrum-web-components/base";
import {trackEvent} from "../analytics.js";

// Global cache for verses to make popovers "instant" on repeated hovers
const verseCache = new Map();
import { computePosition, flip, shift, offset, arrow } from '@floating-ui/dom';

@customElement('tbp-verse-popover')
export class VersePopover extends TBPElement {
  @property({type: String}) reference = "";
  @property({type: String}) bible = "";
  @property({type: String}) language = "";

  @state() content = null;
  @state() loading = false;
  @state() error = null;
  @state() fallbackUsed = false;
  @state() fallbackBibleName = "";
  @state() open = false;

  constructor() {
    super();
    this.observer = null;
  }

  connectedCallback() {
    super.connectedCallback();
    // Move children to a fragment to preserve them before Lit's first render
    // only if we haven't captured them yet
    if (!this._initialContent && this.childNodes.length > 0) {
      this._initialContent = document.createDocumentFragment();
      while (this.childNodes.length > 0) {
        this._initialContent.appendChild(this.childNodes[0]);
      }
      console.log('TBP: Captured initial content for Light DOM', this.reference);
    }

    // Add event listeners to the host element itself for better reliability in Light DOM
    this.addEventListener('mouseenter', this.showPopover.bind(this));
    this.addEventListener('mouseleave', this.hidePopover.bind(this));
    this.addEventListener('click', this.handleTriggerClick.bind(this));

    // Check cache immediately
    const cacheKey = this.getCacheKey();
    if (verseCache.has(cacheKey)) {
      this.content = verseCache.get(cacheKey);
    } else {
      // Pre-fetch when it comes into view
      this.setupIntersectionObserver();
    }
  }

  disconnectedCallback() {
    if (this.observer) {
      this.observer.disconnect();
    }
    super.disconnectedCallback();
  }

  setupIntersectionObserver() {
    if (!('IntersectionObserver' in window)) return;

    this.observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          this.fetchVerse();
          this.observer.disconnect();
        }
      });
    }, { rootMargin: '200px' }); // Pre-fetch 200px before it scrolls into view

    this.observer.observe(this);
  }

  getCacheKey(bible = null, language = null) {
    const b = bible || this.bible || $selectedBible.get();
    const l = language || this.language || $selectedLanguage.get();
    return `${this.getNormalizedReference()}|${b}|${l}`;
  }

  static get styles() {
    return [
      super.styles,
      css`
        :host {
          display: inline-block;
        }
        .popover-content {
          padding: 15px;
          max-width: 300px;
          max-height: 400px;
          overflow-y: auto;
        }
        sp-progress-circle {
          display: block;
          margin: 10px auto;
        }
        .verse-text {
          font-size: 14px;
          line-height: 1.5;
        }
        .verse-reference {
          font-weight: bold;
          margin-bottom: 8px;
          display: block;
        }
        [slot="trigger"] {
          text-decoration: underline;
          cursor: pointer;
        }
        sp-theme {
          display: inline;
        }
        .verse-reference a {
          color: inherit;
          text-decoration: none;
        }
        .verse-reference a:hover {
          text-decoration: underline;
        }
        .fallback-notice {
          font-size: 11px;
          color: var(--spectrum-global-color-gray-600, #666);
          margin-top: 8px;
          font-style: italic;
          border-top: 1px solid var(--spectrum-global-color-gray-200, #ddd);
          padding-top: 5px;
        }
      `
    ];
  }

  createRenderRoot() {
    return this;
  }

  getReaderUrl() {
    const url = new URL(window.location.href);
    url.searchParams.set('reference', this.getNormalizedReference());
    url.searchParams.set('language', this.language || $selectedLanguage.get());
    url.searchParams.set('bible', this.bible || $selectedBible.get());
    return url.toString();
  }

  getNormalizedReference() {
    if (!this.reference) return "";

    let ref = decodeURIComponent(this.reference).trim()
      .replace(/([a-zA-Z])(\d)/g, '$1 $2') // Space between letters and digits
      .replace(/^(\d)([a-zA-Z])/g, '$1 $2'); // Space at start between digit and letters (e.g. 1John -> 1 John)

    // Handle hyphens used instead of colons for chapter-verse separator (e.g. John 3-16 -> John 3:16)
    // But only if there isn't already a colon and it looks like Chapter-Verse
    if (!ref.includes(':') && ref.match(/\d+-\d+/)) {
      ref = ref.replace(/(\d+)-(\d+)/, '$1:$2');
    }

    return ref;
  }

  async fetchVerse(bibleToTry = null, languageToTry = null) {
    const cacheKey = this.getCacheKey(bibleToTry, languageToTry);
    if (verseCache.has(cacheKey) && !bibleToTry) {
      this.content = verseCache.get(cacheKey);
      return;
    }

    if (this.content && !bibleToTry) return;
    if (this.loading && !bibleToTry) return;
    if (!window.$tbp || !window.$tbp.apiUrl) {
      console.error('TBP: Plugin configuration not loaded', window.$tbp);
      this.error = "Plugin configuration not loaded";
      return;
    }

    this.loading = true;
    this.error = null;

    const reference = this.getNormalizedReference();
    console.log(`TBP: Fetching verse for ${reference}`);
    const language = languageToTry || this.language || $selectedLanguage.get();
    const bible = bibleToTry || this.bible || $selectedBible.get();

    try {
      const endpoint = apiUrl('scripture', {
        reference: reference,
        language: language,
        bible: bible,
      });

      trackEvent('tbp_scripture_load', {
        reference: reference,
        language: language,
        bible: bible,
        is_fallback: !!this.fallbackUsed
      });

      const response = await fetch(endpoint, {
        headers: {
          'Accept': 'application/json',
          'X-WP-Nonce': window.$tbp?.nonce
        }
      });

      const data = await response.json();

      if (!response.ok || data.success === false) {
        const errorMsg = data.message || data.error || (data.data && data.data.error) || 'Failed to fetch verse';

        // Fallback logic: if "does not contain" error and we haven't tried fallback yet
        if (errorMsg.includes('does not contain') && !this.fallbackUsed) {
          const available = $availableLanguages.get();
          const defaultLang = available.find(l => l.is_default) || available[0];

          if (defaultLang && (defaultLang.value !== language || defaultLang.default_bible !== bible)) {
            this.fallbackUsed = true;
            this.fallbackBibleName = defaultLang.bibles.find(b => b.value === defaultLang.default_bible)?.label || defaultLang.default_bible;
            return this.fetchVerse(defaultLang.default_bible, defaultLang.value);
          }
        }

        throw new Error(errorMsg);
      }

      this.content = data.data || data;
      // Store in global cache
      verseCache.set(cacheKey, this.content);
    } catch (e) {
      console.error('TBP Popover Error:', e);
      this.error = e.message;
    } finally {
      this.loading = false;
    }
  }

  showPopover(e) {
    if (e) {
      // If the event target is inside the popover-theme, we don't want to re-trigger or interfere
      const popoverTheme = this.querySelector('.popover-theme');
      if (popoverTheme && popoverTheme.contains(e.target)) {
        if (this._hideTimeout) {
          clearTimeout(this._hideTimeout);
          this._hideTimeout = null;
        }
        return;
      }
    }

    console.log('TBP: showPopover triggered', e?.type, 'Open:', this.open, 'Reference:', this.reference, 'Target:', e?.target?.tagName);
    if (e && e.type === 'mouseenter' && e.buttons !== 0) return; // Ignore mouseenter if buttons are pressed (drag)
    if (this._hideTimeout) {
      clearTimeout(this._hideTimeout);
      this._hideTimeout = null;
    }
    if (this.open) {
      console.log('TBP: Already open, keeping open');
      return;
    }
    this.open = true;
    this.fetchVerse();
    // Use requestAnimationFrame to ensure the element is visible before calculating position
    requestAnimationFrame(() => {
      console.log('TBP: RAF updatePosition');
      this.updatePosition();
    });
  }

  handleTriggerClick(e) {
    console.log('TBP: handleTriggerClick triggered');
    e.preventDefault();
    e.stopPropagation();
    if (this.open) {
      this.hidePopoverImmediately();
    } else {
      this.showPopover(e);
    }
  }

  hidePopover(e) {
    if (e && e.relatedTarget) {
      // If we are moving into the popover-theme or the host itself, don't hide
      const popoverTheme = this.querySelector('.popover-theme');
      if (this.contains(e.relatedTarget) || (popoverTheme && popoverTheme.contains(e.relatedTarget))) {
        return;
      }
    }
    console.log('TBP: hidePopover (delayed)');
    this._hideTimeout = setTimeout(() => {
      this.open = false;
      this._hideTimeout = null;
    }, 200);
  }

  hidePopoverImmediately() {
    console.log('TBP: hidePopoverImmediately');
    if (this._hideTimeout) {
      clearTimeout(this._hideTimeout);
      this._hideTimeout = null;
    }
    this.open = false;
  }

  async updatePosition() {
    if (!this.open) return;
    const trigger = this; // Use the host element itself as the trigger
    const popoverTheme = this.querySelector('.popover-theme');
    const arrowElement = this.querySelector('.popover-arrow');
    if (!trigger || !popoverTheme) {
      console.warn('TBP: updatePosition failed - elements not found', {trigger, popoverTheme});
      // Try again in next frame just in case
      if (this.open) {
        requestAnimationFrame(() => this.updatePosition());
      }
      return;
    }

    console.log('TBP: Updating position for', this.reference);
    try {
      const {x, y, placement, middlewareData} = await computePosition(trigger, popoverTheme, {
        strategy: 'fixed',
        placement: 'top',
        middleware: [
          offset(10),
          flip(),
          shift({padding: 5}),
          arrow({element: arrowElement}),
        ],
      });

      Object.assign(popoverTheme.style, {
        left: `${x}px`,
        top: `${y}px`,
      });

      if (arrowElement && middlewareData.arrow) {
        const {x: arrowX, y: arrowY} = middlewareData.arrow;

        const staticSide = {
          top: 'bottom',
          right: 'left',
          bottom: 'top',
          left: 'right',
        }[placement.split('-')[0]];

        Object.assign(arrowElement.style, {
          left: arrowX != null ? `${arrowX}px` : '',
          top: arrowY != null ? `${arrowY}px` : '',
          right: '',
          bottom: '',
          [staticSide]: '-7px',
          // Hide arrow borders that shouldn't be visible depending on placement
          borderBottomColor: staticSide === 'bottom' ? 'transparent' : '',
          borderRightColor: staticSide === 'bottom' ? 'transparent' : '',
          borderTopColor: staticSide === 'top' ? 'transparent' : '',
          borderLeftColor: staticSide === 'top' ? 'transparent' : '',
          borderTopLeftRadius: staticSide === 'top' ? '2px' : '',
          borderBottomRightRadius: staticSide === 'bottom' ? '2px' : '',
        });
      }
    } catch (err) {
      console.error('TBP: computePosition error', err);
    }
  }

  updated(changedProperties) {
    super.updated(changedProperties);
    if (this.open) {
      this.updatePosition();
    }
  }

  render() {
    return html`
      <style>
        tbp-verse-popover {
          display: inline;
          position: relative;
          text-decoration: underline;
          cursor: pointer;
        }
        tbp-verse-popover:hover {
          text-decoration: none;
        }
        tbp-verse-popover .popover-content {
          padding: 15px;
          max-width: 300px;
          max-height: 400px;
          overflow-y: auto;
          color: #333;
          background: #fff;
          border-radius: 4px;
          pointer-events: auto;
        }
        tbp-verse-popover sp-progress-circle {
          display: block;
          margin: 10px auto;
        }
        tbp-verse-popover .verse-text {
          font-size: 14px;
          line-height: 1.5;
        }
        tbp-verse-popover .verse-reference {
          font-weight: bold;
          margin-bottom: 8px;
          display: block;
        }
        tbp-verse-popover .popover-trigger-wrapper {
          display: inline;
        }
        tbp-verse-popover .popover-theme {
          position: fixed;
          z-index: 10000;
          width: max-content;
          pointer-events: none;
          top: -9999px;
          left: -9999px;
          background: white; /* Ensure visibility */
          border: 1px solid #999;
          box-shadow: 0 4px 15px rgba(0,0,0,0.15);
          visibility: hidden;
          margin: 0;
          padding: 0;
          border-radius: 4px;
        }
        .popover-arrow {
          position: absolute;
          background: white;
          width: 12px;
          height: 12px;
          transform: rotate(45deg);
          border: 1px solid #999;
          z-index: -1;
        }
        tbp-verse-popover .popover-theme[open] {
          visibility: visible;
          /* Note: pointer-events: auto is on .popover-content to allow interaction
             but we also need it here if sp-theme has dimensions */
          pointer-events: auto;
        }
        tbp-verse-popover .verse-reference a {
          color: inherit !important;
          text-decoration: none !important;
        }
        tbp-verse-popover .verse-reference a:hover {
          text-decoration: underline !important;
        }
        tbp-verse-popover .fallback-notice {
          font-size: 11px;
          color: #666;
          margin-top: 8px;
          font-style: italic;
          border-top: 1px solid #ddd;
          padding-top: 5px;
        }
      </style>
      <span class="popover-trigger-wrapper">
              ${this._initialContent || nothing}
          </span>
      <sp-theme color="light" scale="medium" class="popover-theme" ?open=${this.open}
                @mouseenter=${this.showPopover}
                @mouseleave=${this.hidePopover}
                style="display: ${this.open ? 'block' : 'none'}; visibility: ${this.open ? 'visible' : 'hidden'};">
        <div class="popover-arrow"></div>
        <div class="popover-element">
          <div class="popover-content">
            ${this.renderContent()}
          </div>
        </div>
      </sp-theme>
    `;
  }

  getVerses() {
    if (!this.content || !this.content.media) return [];

    // Prefer text media
    if (this.content.media.text && this.content.media.text.content) {
      return this.content.media.text.content;
    }

    // Fallback to any available media content
    const firstMedia = Object.values(this.content.media).find(m => m && m.content);
    return firstMedia ? firstMedia.content : [];
  }

  renderContent() {
    if (this.loading) {
      return html`<sp-progress-circle size="s" indeterminate></sp-progress-circle>`;
    }
    if (this.error) {
      return html`<div class="error">${this.error}</div>`;
    }

    const verses = this.getVerses();

    if (verses && verses.length > 0) {
      return html`
        <div class="verse-reference">
          <a href="${this.getReaderUrl()}">${reference_from_content(verses)}</a>
        </div>
        <div class="verse-text">
          ${verses.map(item => html`
            <span class="verse-item">${item.verse_text || item.text}</span>
          `)}
        </div>
        ${this.fallbackUsed ? html`
          <div class="fallback-notice">
            Showing version: ${this.fallbackBibleName}
          </div>
        ` : nothing}
      `;
    }

    if (this.content) {
      return html`<div class="error">No content available for this reference.</div>`;
    }

    return html`Loading...`;
  }
}
