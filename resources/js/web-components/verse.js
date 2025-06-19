import {TBPElement} from "./base.js";
import {customElement, property, query} from "lit/decorators.js";
import {css, html} from "@spectrum-web-components/base";
import {classMap} from "lit/directives/class-map.js";
import interact from 'interactjs';
import {is_mobile, is_safari} from "../helpers.js";

@customElement('tbp-verse')
export class Verse extends TBPElement {
    @property({type: String}) verse = '';
    @property({type: String}) text = '';
    @property({type: Boolean}) selectable = false;
    @property({type: Boolean, reflect: true, attribute: true}) selected = false;
    @query('.verse') verseEl;

    tapTimeout = null;
    selectionChangeListener = null;

    createRenderRoot() {
        return this;
    }

    get classMap() {
        const {selected} = this;
        return classMap({
            "verse": true,
            "verse--selected": selected,
        })
    }

    render() {
        return html`
            <span class="${this.classMap}"
                  .aria-selected="${this.selected}"
            >
                <span class="verse__reference">
                    ${this.verse}
                </span>
                ${this.text}
            </span>
        `;
    }
}
