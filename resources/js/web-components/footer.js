import {css, html} from "@spectrum-web-components/base";
import {customElement, property} from "lit/decorators.js";
import {TBPElement} from "./base.js";
import {withStores} from "@nanostores/lit";
import {$fullScreen} from "../stores/full-screen.js";

@customElement('tbp-footer')
export class Footer extends withStores(TBPElement, [$fullScreen]) {
    @property({type: String}) text = '';

    static get styles() {
        return [
            super.styles,
            css`
                footer {
                    max-width: var(--wp--style--global--wide-size, 1200px);
                    margin: var(--tpb-footer-margin, 25px auto);
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    gap: 1rem;
                }
            `
        ];
    }

    render() {
        return html`
            <footer>
                <tbp-chapter-nav></tbp-chapter-nav>
                <tbp-copyright></tbp-copyright>
                <sp-button-group>
                    <sp-button icon-only variant="secondary" @click=${() => $fullScreen.set(!$fullScreen.get())}>
                        <iconify-icon icon="${"ic:round-fullscreen"}"
                                      slot="icon"
                                      width="18px"
                                      height="18px"></iconify-icon>
                    </sp-button>
                </sp-button-group>
            </footer>
        `;
    }
}
