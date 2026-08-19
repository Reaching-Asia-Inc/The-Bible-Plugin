import {TBPElement} from "./base.js";
import {customElement, property, query} from "lit/decorators.js";
import {css, html} from "@spectrum-web-components/base";
import {classMap} from "lit/directives/class-map.js";
import {is_safari} from "../helpers.js";

@customElement('tbp-verse')
export class Verse extends TBPElement {
    @property({type: String}) verse = '';
    @property({type: String}) text = '';
    @property({type: Boolean}) selectable = false;
    @property({type: Boolean, reflect: true, attribute: true}) selected = false;
    @property({type: Boolean, reflect: true, attribute: 'selection-mode'}) selectionMode = false;
    @query('.verse') verseEl;

    tapTimeout = null;
    selectionChangeListener = null;

    createRenderRoot() {
        return this;
    }

    get classMap() {
        const {selected, selectionMode} = this;
        return classMap({
            "verse": true,
            "verse--selected": selected,
            "verse--selection-mode": selectionMode,
        })
    }

    handleCheckboxChange(e) {
        this.dispatchEvent(new CustomEvent('selectable-toggle', {
            detail: {selected: e.target.checked},
        }));
    }

    render() {
        return html`
            <span class="${this.classMap}"
                  .aria-selected="${this.selected}"
            >
                ${this.selectionMode ? html`
                    <sp-checkbox
                            class="verse__checkbox"
                            ?checked="${this.selected}"
                            @change="${this.handleCheckboxChange.bind(this)}"
                    ></sp-checkbox>
                ` : ''}
                <span class="verse__reference">
                    ${this.verse}
                </span>
                ${this.text}
            </span>
        `;
    }
}
