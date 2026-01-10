import {customElement} from "lit/decorators.js";
import {TBPElement} from "./base.js";
import {html, nothing} from "lit";
import {withStores} from "@nanostores/lit";
import {$ntBooks, $otBooks} from "../stores/book.js";
import {$hasSelection} from "../stores/selection.js";
import {$canShare} from "../stores/share.js";
import {$hasAudio, $audioOpen, $playAudio} from "../stores/audio.js";
import {$hasVideo, $videoOpen, $playVideo} from "../stores/video.js";
import {$availableLanguages, $selectedLanguage, $availableBibles, $selectedBible, $translationOptions, $selectedCombined, setSelectedCombined} from "../stores/settings.js";
import {$fullScreen} from "../stores/full-screen.js";

import {__} from "../helpers.js"
import {css} from "@spectrum-web-components/base";
import "./language-menu.js";
import "./social-share.js";

@customElement('tbp-bible-menu')
export class BibleMenu extends withStores(TBPElement, [$otBooks, $ntBooks, $hasAudio, $audioOpen, $hasVideo, $videoOpen, $hasSelection, $canShare, $availableLanguages, $selectedLanguage, $availableBibles, $selectedBible, $translationOptions, $selectedCombined, $fullScreen]) {

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
        const showPicker = $translationOptions.get().length > 1;

        return html`
            <sp-action-group style="margin-inline-start: auto;">
                <tbp-book-menu label="${__("Books")}"
                               .books=${$otBooks.get().concat($ntBooks.get())}
                               class="book-menu--mobile"></tbp-book-menu>
                <tbp-book-menu label="${__("Old Testament")}" .books=${$otBooks.get()}
                               class="book-menu--desktop"></tbp-book-menu>
                <tbp-book-menu label="${__("New Testament")}" .books=${$ntBooks.get()}
                               class="book-menu--desktop"></tbp-book-menu>

                <sp-divider
                  size="s"
                  style="align-self: stretch; height: auto;"
                  vertical
                ></sp-divider>

                ${showPicker ? html`
                    ${this.renderTranslationPicker()}
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
                      <iconify-icon icon="${"material-symbols:play-arrow"}"
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

<!--                <tbp-language-menu></tbp-language-menu>-->

                ${$canShare.get() ? html`
                    <tbp-share-button></tbp-share-button>
                ` : html`
                    <tbp-selection-button></tbp-selection-button>
                ` }
            </sp-action-group>
        `;
    }

    renderTranslationPicker() {
        const options = $translationOptions.get();
        const selectedValue = $selectedCombined.get();
        const selectedOption = options.find(opt => opt.value === selectedValue);
        const label = selectedOption ? selectedOption.label : '';
        const isFullScreen = $fullScreen.get();

        return html`
            <sp-picker
                    quiet
                    size="s"
                    value="${selectedValue}"
                    title="${label}"
                    @change="${(e) => setSelectedCombined(e.target.value)}"
                    style="margin-bottom: 0; max-width: ${isFullScreen ? 'none' : '120px'}; padding-top:10px"
            >
                ${options.map(opt => html`
                    <sp-menu-item value="${opt.value}">${opt.label}</sp-menu-item>
                `)}
            </sp-picker>
        `;
    }
}
