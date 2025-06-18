import {customElement, property} from "lit/decorators.js";
import {TBPElement} from "./base.js";
import {withStores} from "@nanostores/lit";
import {css, html, nothing} from "@spectrum-web-components/base";
import {$query} from "../stores/query.js"
import {$bookName} from "../stores/book.js"
import {$fullScreen} from "../stores/full-screen.js";
import {$chapter} from "../stores/chapter.js"
import {$message} from "../stores/message.js"
import {$error} from "../stores/error.js"
import {$reference} from "../stores/reference.js";
import {__} from "../helpers.js";
import {$hasVideo} from "../stores/video.js";

@customElement('tbp-bible')
export class Bible extends withStores(TBPElement, [$query, $bookName, $chapter, $fullScreen, $message, $error, $hasVideo]) {
    @property({type: String}) reference = 'JHN 1'

    static get styles() {
        return [
            super.styles,
            css`
                :host {
                    --tbp-reader-height: var(--mod-tbp-reader-height, calc(100vh - 250px));
                }

                #loader,
                main {
                    height: 100%;
                    min-height: var(--tbp-reader-height);
                }


                #loader {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100%;
                }

                tbp-header {
                  display: block;
                  z-index: 1;
                  position: relative;
                }

                #error {
                    --mod-toast-max-inline-size: 100%;
                }
            `
        ];
    }

    firstUpdated(_changedProperties) {
        $reference.set(this.reference);
        document.addEventListener('keydown', (e) => {
            if (e.key === "Escape") {
                $fullScreen.set(false);
            }
        });
    }

    render() {
        if ($query.get().loading) {
            return this.renderLoader();
        }
        return $error.get() ? this.renderError() : this.renderSuccess()
    }

    renderSuccess() {
        return html`
            <main>${$fullScreen.get() ? this.renderFullScreen() : this.renderStructure()}</main>`;
    }

    renderError() {
        return html`
            <sp-toast open variant="negative" id="error"
                      .key="${$error.get()}" size="s">
                ${$error.get()}

                <sp-button
                        slot="action"
                        static="white"
                        variant="secondary"
                        treatment="outline"
                        @click=${() => this.handleErrorClick()}
                >
                    Reload
                </sp-button>
            </sp-toast>
        `
    }

    handleErrorClick() {
        const url = new URL(window.location.href);
        window.location.href = url.protocol + "//" + url.host + url.pathname;
    }

    renderFullScreen() {
        return html`
            <sp-overlay type="page" open headline="Full Screen">
                <tbp-dialog-wrapper
                        mode="fullscreenTakeover"
                        no-divider
                        headline-visibility="hidden"
                >
                    ${this.renderStructure()}
                </tbp-dialog-wrapper>
            </sp-overlay>
        `
    }

    renderStructure() {
        const {loading} = $query.get();

        return html`
            <tbp-header></tbp-header>
            ${loading ? this.renderLoader() : html`
              <tbp-video-pane></tbp-video-pane>
              <tbp-reader></tbp-reader>
              <tbp-audio-bar></tbp-audio-bar>
            `}
            <tbp-footer></tbp-footer>
        `
    }

    renderContent() {
      return
    }

    renderLoader() {
        return html`
            <div id="loader">
                <sp-progress-circle
                        size="m"
                        label="${__("Loading")}"
                        indeterminate
                ></sp-progress-circle>
            </div>
        `
    }
}
