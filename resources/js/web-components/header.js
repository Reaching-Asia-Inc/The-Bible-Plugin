import {css, html, nothing} from "@spectrum-web-components/base";
import {customElement, property} from "lit/decorators.js";
import {TBPElement} from "./base.js";
import {withStores} from "@nanostores/lit";
import {$query} from "../stores/query.js";
import {$bookName} from "../stores/book.js";
import {$chapter} from "../stores/chapter.js";
import {$fullScreen} from "../stores/full-screen.js";
import {$message} from "../stores/message.js"

@customElement('tbp-header')
export class Header extends withStores(TBPElement, [$query, $bookName, $chapter, $message, $fullScreen]) {
    @property({type: String}) text = '';

    static get styles() {
        return [
            super.styles,
            css`
                :host {
                    display: block;
                    margin-bottom: var(--spectrum-global-dimension-size-200);
                    width: 100%;
                }

                sp-top-nav-item {
                    max-width: var(--tbp-header-item-max-width, 120px);
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }

                sp-top-nav {
                    max-width: var(--wp--style--global--wide-size, 1200px);
                    margin: 0 auto;
                }

                #message {
                    position: fixed;
                    right: var(--spectrum-global-dimension-size-400);
                    bottom: var(--spectrum-global-dimension-size-400);
                    z-index: var(--tbp-message-z-index, 10000);
                }
            `
        ];
    }

    render() {
        const {loading} = $query.get();
        const isFullScreen = $fullScreen.get();

        return html`

            <sp-top-nav size="m" style="${isFullScreen ? '--tbp-header-item-max-width: none' : ''}">
                <sp-top-nav-item size="m" title="${$bookName.get() + " " + $chapter.get()}">
                    ${loading ? html`
                        <sp-progress-circle
                                size="s"
                                label="Loading bible..."
                                indeterminate
                        ></sp-progress-circle>` : $bookName.get() + " " + $chapter.get()}
                </sp-top-nav-item>
                ${this.renderMessage()}
                <div style="margin-inline-start: auto; display: flex; align-items: center;">
                  <tbp-bible-menu></tbp-bible-menu>
                </div>
            </sp-top-nav>
        `
    }

    renderMessage() {
        if (!$message.get()) {
            return nothing;
        }
        return html`
            <div id="message">
                <sp-toast open variant="positive"
                          timeout="6000"
                          .key="${$message.get()}" size="s">
                    ${$message.get()}
                </sp-toast>
            </div>
        `
    }
}
