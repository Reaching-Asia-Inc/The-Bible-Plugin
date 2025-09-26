import {customElement, state, query} from "lit/decorators.js";
import {css, html, nothing} from "@spectrum-web-components/base";
import {TBPElement} from "./base.js";
import {__, apiUrl} from "../helpers.js";
import {$fullScreen} from "../stores/full-screen.js";

@customElement('tbp-language-menu')
export class LanguageMenu extends TBPElement {
    @state() languages = []; // [{ value, label, bible: { value, label }, is_default }]
    @state() selectedLanguage = '';
    @state() selectedBible = '';
    @query('[slot="trigger"]') trigger;

    static get styles() {
        return [
            css`
              :host {
                display: contents;
              }

              /* Match book menu popover styling */
              sp-popover {
                padding: 0px;
              }

              /* Scrollable language list to prevent overflow */
              #language-menu {
                max-height: 60vh;
                overflow-y: auto;
                overflow-x: hidden;
                --mod-popover-content-area-spacing-vertical: 0px;
                min-width: 275px;
              }

              /* Consistent item alignment and look */
              sp-action-button {
                border-radius: 0 !important;
                justify-content: flex-start;
              }
            `
        ]
    }

    connectedCallback() {
        super.connectedCallback();
        this.loadAvailable().then(() => {
            this.initSelection();
        });
    }

    // Try to read configured languages from global, then from API. Hide UI if none available.
    async loadAvailable() {
        try {
            const possibleGlobals = [
                // common global placements
                (window.tbp && window.tbp.languages),
                (window._tbp && window._tbp.languages),
                window.tbp_languages
            ].filter(Boolean)[0];

            let raw = possibleGlobals;
            if (!raw) {
                // Attempt REST endpoint for configured languages
                const res = await fetch(apiUrl('languages'), {credentials: 'same-origin'});
                if (res.ok) {
                    raw = await res.json();
                }
            }

            if (!raw || !Array.isArray(raw) || raw.length === 0) {
                this.languages = [];
                return;
            }

            // Normalize incoming items
            const normalized = await Promise.all(raw.map(async (item) => {
                const langVal = item.value || item.language || item.code;
                const langLabel = item.itemText || item.label || item.name || langVal;
                const bibleVal = item.bibles || item.bible || '';
                let bibleLabel = item.bible_label || '';

                // If we don't have a bible label but have a language+value, try to resolve via options endpoint.
                if (bibleVal && !bibleLabel && langVal) {
                    try {
                        const url = apiUrl('bibles/options', {language: langVal});
                        const res = await fetch(url, {credentials: 'same-origin'});
                        if (res.ok) {
                            const opts = await res.json(); // expect [{value, itemText}]
                            const match = Array.isArray(opts) ? opts.find(o => o.value === bibleVal) : null;
                            if (match) {
                                bibleLabel = match.itemText || match.label || bibleVal;
                            }
                        }
                    } catch (e) {
                        // noop
                    }
                }

                return {
                    value: langVal,
                    label: langLabel,
                    is_default: !!item.is_default,
                    bible: {
                        value: bibleVal,
                        label: bibleLabel || bibleVal
                    }
                }
            }));

            // Only keep items that have at least a language value
            this.languages = normalized.filter(l => !!l.value);
        } catch (e) {
            this.languages = [];
        }
    }

    initSelection() {
        if (!this.languages.length) return;

        const url = new URL(window.location.href);
        const urlLang = url.searchParams.get('language');
        const urlBible = url.searchParams.get('bible');

        const lsLang = localStorage.getItem('tbp.language') || '';
        const lsBible = localStorage.getItem('tbp.bible') || '';

        // Default language preference: URL > localStorage > default flag > first
        const langCandidate = urlLang || lsLang || (this.languages.find(l => l.is_default)?.value) || this.languages[0].value;
        const langExists = this.languages.find(l => l.value === langCandidate);
        this.selectedLanguage = langExists ? langCandidate : this.languages[0].value;

        // Versions are constrained to configured bible(s) for the selected language (typically one)
        const allowedBible = this.getAllowedBibleForLanguage(this.selectedLanguage);
        const bibleCandidate = urlBible || lsBible || (allowedBible?.value) || '';
        this.selectedBible = (allowedBible && bibleCandidate === allowedBible.value) ? bibleCandidate : (allowedBible?.value || '');
    }

    getAllowedBibleForLanguage(language) {
        const lang = this.languages.find(l => l.value === language);
        return lang ? lang.bible : null;
    }

    get hasConfig() {
        return this.languages && this.languages.length > 0;
    }

    handleLanguageChange(e) {
        const value = e.target.value;
        if (!value) return;
        this.selectedLanguage = value;

        // Snap version to the configured one for this language
        const allowedBible = this.getAllowedBibleForLanguage(this.selectedLanguage);
        this.selectedBible = allowedBible ? allowedBible.value : '';

        // Immediately persist and apply so the bible aligns with the selected language
        this.applySelection();
    }

    handleBibleChange(e) {
        const value = e.target.value;
        if (!value) return;
        // Since versions are constrained per-language, accept only the configured one.
        const allowedBible = this.getAllowedBibleForLanguage(this.selectedLanguage);
        if (allowedBible && value === allowedBible.value) {
            this.selectedBible = value;
        }
    }

    applySelection() {
        // Persist only language (bible handled server-side for selected language)
        localStorage.setItem('tbp.language', this.selectedLanguage || '');

        // Update URL params (do not include empty params)
        const url = new URL(window.location.href);
        if (this.selectedLanguage) {
            url.searchParams.set('language', this.selectedLanguage);
        } else {
            url.searchParams.delete('language');
        }
        // Do not set bible param from language menu

        // Preserve full screen state across navigation
        const isFullScreen = (() => {
            try {
                return $fullScreen?.get ? $fullScreen.get() : false;
            } catch (e) {
                return false;
            }
        })();
        if (isFullScreen) {
            url.searchParams.set('fullScreen', '1');
        } else {
            url.searchParams.delete('fullScreen');
        }

        // Notify listeners
        this.dispatchEvent(new CustomEvent('tbp-language-change', {
            bubbles: true,
            composed: true,
            detail: {
                language: this.selectedLanguage
            }
        }));

        // Navigate so content reloads in the new language
        window.location.href = url.toString();
    }

    render() {
        if (!this.hasConfig) return nothing;

        const isFull = (() => {
            try { return $fullScreen?.get ? $fullScreen.get() : false; } catch { return false; }
        })();

        const currentLang = this.languages.find(l => l.value === this.selectedLanguage);
        // In full screen show the selected language label; otherwise show the language value
        const triggerText = isFull
            ? (currentLang?.label || this.selectedLanguage || __('Language'))
            : (this.selectedLanguage || __('Language'));

        return html`
          <overlay-trigger placement="bottom-end" offset="1">
            <sp-action-button label="${triggerText}" slot="trigger">
              <iconify-icon icon="mdi:translate" slot="icon" width="18px" height="18px"></iconify-icon>
              ${triggerText}
            </sp-action-button>

            <sp-popover slot="click-content" direction="bottom-end">
              <sp-action-group
                id="language-menu"
                selects="single"
                compact
                vertical
              >
                ${this.languages.map(l => html`
                  <sp-action-button
                    ?selected="${l.value === this.selectedLanguage}"
                    @click=${() => {
                        this.selectedLanguage = l.value;
                        const allowed = this.getAllowedBibleForLanguage(l.value);
                        this.selectedBible = allowed ? allowed.value : '';
                        this.applySelection();
                        this.trigger?.click();
                    }}
                  >
                    ${l.label}
                  </sp-action-button>
                `)}
              </sp-action-group>
            </sp-popover>
          </overlay-trigger>
        `;
    }
}
