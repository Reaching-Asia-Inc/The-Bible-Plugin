import {customElement, property, state, queryAll} from "lit/decorators.js";
import {html} from "lit";
import {TBPElement} from "./base.js";
import {reference_from_content, find_media_type, is_mobile, is_safari} from "../helpers.js";
import {$selection, $selectionOpen} from "../stores/selection.js";
import {withStores} from "@nanostores/lit";
import {css} from "@spectrum-web-components/base";

/**
 * Represents a custom element called "tbp-content".
 * Extends the TBPElement class.
 */
@customElement('tbp-content')
export class Content extends withStores(TBPElement, [$selection, $selectionOpen]) {

    static get styles() {
        return [
            super.styles,
            css`
              .verse__reference {
                color: var(--tpb-verse-reference-color, var(--spectrum-neutral-visual-color, gray));
                font-weight: var(--tbp-verse-reference-font-weight, normal);
              }

              .verse {
                line-height: var(--tbp-verse-line-height, 2);
                padding: .5em 0;
                display: block;
              }

              dir[dir="rtl"] .verse {
                direction: rtl;
                text-align: right;
              }

              dir[dir="ltr"] .verse {
                direction: ltr;
                text-align: left;
              }

              tbp-verse[selectable] .verse:hover:not(.verse--selected) {
                background-color: var(--tbp-verse-hover-background-color, var(--spectrum-transparent-black-200));
              }

              .verse--selected {
                background-color: var(--tbp-verse-selected-background-color, var(--spectrum-accent-color-600));
                color: var(--tbp-verse-selected-color, var(--spectrum-alias-text-color-overbackground));
              }

              .verse--selected .verse__reference {
                color: var(--tbp-verse-selected-reference-color, var(--spectrum-alias-text-color-overbackground));
              }
            `];
    }

    /**
     * Represents an array of content.
     *
     * @type {Array}
     */
    @property({type: Array})
    content = [];

    /**
     * The media type to display
     *
     * @typedef {string} TypeString
     */
    @property({type: String})
    type = ""

    /**
     * Determines whether the heading should display or not
     *
     * @param {boolean} heading - The heading value.
     * @returns {boolean} - Display scripture reference if heading is true, hide if false
     */
    @property({type: Boolean})
    heading = false

    /**
     * Custom heading text to display in place of the reference
     */
    @property({type: String})
    heading_text = ""

    @property({type: Object})
    reference = {
        book: "",
        chapter: "",
        verse_start: "",
        verse_end: ""
    }

    @property({type: Boolean})
    selectable = false

    @property({type: Boolean}) autoplay = false;

    @property({type: String}) dir = 'ltr';


    //Without the query
    get pageUrl() {
        return window.location.protocol + "//" + window.location.host + window.location.pathname;
    }

    /**
     * Retrieves the reference from the content.
     *chapter
     * @returns {Reference} The reference obtained from the content.
     */
    get heading_label() {
        if (this.heading_text) {
            return this.heading_text
        }

        if (this.reference
            && this.reference.book
            && this.reference.chapter
            && !this.reference.verse_start) {
            return `${this.reference.book} ${this.reference.chapter}`
        }

        return reference_from_content(this.content)
    }

    /**
     * Retrieves the media type based on the current type.
     * @returns {string|boolean} - The media type or false if the current type is not recognized.
     */
    get media_type() {
        return find_media_type(
            this.type
        )
    }

    /**
     * Renders the HTML content for the given method.
     *
     * @returns {string} The rendered HTML content.
     */
    render() {
        if (this.selectable) {
            return html`
                <tbp-selection-manager
                        @selection="${this.handleSelection.bind(this)}"
                        @context="${this.handleContextMenu.bind(this)}"
                >
                    ${this.renderSections()}
                </tbp-selection-manager>
            `
        }

        return this.renderSections();
    }

    renderSections() {
        return html`
            ${this.renderHeading()}
            ${this.renderContent()}
        `;
    }


    renderHeading() {
        if (this.heading === false) {
            return html``
        }

        return html`
            <h2>${this.heading_label}</h2>
        `
    }

    /**
     * Renders the content based on the media type.
     *
     * @return {Array|HTMLElement} - The rendered content based on the media type.
     */
    renderContent() {
        switch (this.media_type?.key) {
            case "text":
                return this.content.map((item, idx) => this.renderText(item, idx))
            case "audio":
                return this.renderAudio()
            case "video":
                return this.renderVideo()
            default:
                return html``
        }
    }

    /**
     * Renders the text for the given item.
     *
     * @param {object} item - The item to render.
     * @return {string} The rendered text.
     */
    renderText(item, idx) {
        switch (this.media_type?.key) {
            case "text":
                return this.renderVerse(item)
            default:
                return html``
        }
    }

    /**
     * Render a verse
     *
     * @param {object} options - The verse options.
     * @param {number} options.verse_start - The verse start.
     * @param {string} options.verse_text - The verse text.
     * @return {string} The rendered verse HTML.
     */
    renderVerse({verse_start, verse_text}) {
        return html`
            <tbp-verse
                    verse="${verse_start}"
                    text="${verse_text}"
                    ?selectable="${this.selectable}"
                    dir="${this.dir}"
            />
        `
    }

    /**
     * Renders an audio element
     *
     * @return {HTMLTemplateElement} The template element that renders the audio.
     */
    renderAudio() {
        return html`
            <tbp-audio
                    .content="${this.content}"
                    ?autoplay="${this.autoplay}"
            />
        `
    }

    /**
     * Renders the video content.
     *
     * @return {string} The rendered HTML for the video content.
     */
    renderVideo() {
        return html`
            <tbp-video
                    .content="${this.content}"
                    ?autoplay="${this.autoplay}"
            />
        `
    }


    handleSelection(e) {
        $selection.set(e.detail.selected.map((selectable) => this.mapSelectable(selectable)))
    }

    handleContextMenu(e) {
        $selectionOpen.set(true)
    }

    mapSelectable(selectable) {
        return {
            selectable: selectable,
            text: selectable.textContent.replace(/[\r\n]+/gm, ' ').trim().replace(/\s\s+/g, ' '),
            book: this.reference.book,
            chapter: this.reference.chapter,
            verse_start: parseInt(selectable.verse),
            verse_end: parseInt(selectable.verse),
        }
    }
}
