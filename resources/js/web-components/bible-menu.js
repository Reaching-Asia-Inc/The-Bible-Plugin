import {customElement} from "lit/decorators.js";
import {TBPElement} from "./base.js";
import {html, nothing} from "lit";
import {withStores} from "@nanostores/lit";
import {$ntBooks, $otBooks} from "../stores/book.js";
import {$selection} from "../stores/selection.js";
import {$hasAudio, $audioOpen, $playAudio} from "../stores/audio.js";

import {__} from "../helpers.js"
import {css} from "@spectrum-web-components/base";

@customElement('tbp-bible-menu')
export class BibleMenu extends withStores(TBPElement, [$otBooks, $ntBooks, $selection, $hasAudio, $audioOpen]) {

    static get styles() {
        return [
            super.styles,
            css`
                :host {
                    overflow-x: auto;
                    overflow-y: hidden;
                }

                @media screen and (max-width: 600px) {
                    --spectrum-actionbutton-font-size: 10px;
                }

                sp-action-group {
                    margin-inline-start: auto;
                    flex-wrap: nowrap;
                    padding-inline-start: var(--spectrum-global-dimension-size-200);
                    padding-right: 2px;
                }

                sp-divider {
                    background-color: var(--spectrum-global-color-gray-300);
                }

                .book-menu--mobile {
                    display: none;
                }

                @media screen and (max-width: 600px) {
                    .book-menu--mobile {
                        display: initial;
                    }
                }

                @media screen and (max-width: 600px) {
                    .book-menu--desktop {
                        display: none;
                    }
                }
            `
        ]
    }

    render() {
        return html`
            <sp-action-group style="margin-inline-start: auto;">
                ${$selection.get().length ? html`
                    <tbp-selection-button></tbp-selection-button>
                    <sp-divider
                            size="s"
                            style="align-self: stretch; height: auto;"
                            vertical
                    ></sp-divider>
                ` : nothing}
                ${$hasAudio.get() ? html`
                            <sp-button
                                    variant="accent"
                                    label="Icon only"
                                    icon-only
                                    @click=${$playAudio}
                            >
                                ${$audioOpen.get() ? html`
                                    <sp-icon-replay slot="icon"></sp-icon-replay>` : html`
                                    <iconify-icon icon="${"hugeicons:audio-book-03"}"
                                                  slot="icon"
                                                  width="18px"
                                                  height="18px"></iconify-icon>`}
                            </sp-button>
                            <sp-divider
                                    size="s"
                                    style="align-self: stretch; height: auto;"
                                    vertical
                            ></sp-divider>
                        `
                        : nothing}
                <tbp-book-menu label="${__("Books")}"
                               .books=${$otBooks.get().concat($ntBooks.get())}
                               class="book-menu--mobile"></tbp-book-menu>
                <tbp-book-menu label="${__("Old Testament")}" .books=${$otBooks.get()}
                               class="book-menu--desktop"></tbp-book-menu>
                <tbp-book-menu label="${__("New Testament")}" .books=${$ntBooks.get()}
                               class="book-menu--desktop"></tbp-book-menu>
            </sp-action-group>
        `;
    }
}