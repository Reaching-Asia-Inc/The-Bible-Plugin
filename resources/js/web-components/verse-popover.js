import {customElement, property, state} from "lit/decorators.js";
import {html, nothing} from "lit";
import {TBPElement} from "./base.js";
import {apiUrl, reference_from_content} from "../helpers.js";
import {$selectedLanguage, $selectedBible, $availableLanguages} from "../stores/settings.js";
import {css} from "@spectrum-web-components/base";

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
        if (this.content && !bibleToTry) return;
        if (this.loading && !bibleToTry) return;
        if (!window.$tbp || !window.$tbp.apiUrl) {
            this.error = "Plugin configuration not loaded";
            return;
        }

        this.loading = true;
        this.error = null;

        const reference = this.getNormalizedReference();
        const language = languageToTry || this.language || $selectedLanguage.get();
        const bible = bibleToTry || this.bible || $selectedBible.get();

        try {
            const endpoint = apiUrl('scripture', {
                reference: reference,
                language: language,
                bible: bible,
            });

            const response = await fetch(endpoint, {
                headers: {
                    'Accept': 'application/json',
                    'X-WP-Nonce': window.$tbp?.nonce
                }
            });

            const data = await response.json();

            if (!response.ok) {
                const errorMsg = data.message || data.error || 'Failed to fetch verse';

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
        } catch (e) {
            console.error('TBP Popover Error:', e);
            this.error = e.message;
        } finally {
            this.loading = false;
        }
    }

    render() {
        return html`
            <sp-theme color="light" scale="medium">
                <overlay-trigger type="hover" placement="top" offset="-10">
                    <span slot="trigger" @mouseenter=${this.fetchVerse} @click=${(e) => e.preventDefault()}>
                        <slot></slot>
                    </span>
                    <sp-popover slot="hover-content" tip>
                        <div class="popover-content">
                            ${this.renderContent()}
                        </div>
                    </sp-popover>
                </overlay-trigger>
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
