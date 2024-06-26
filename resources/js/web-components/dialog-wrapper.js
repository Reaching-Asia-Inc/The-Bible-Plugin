import {customElement, property} from "lit/decorators.js";
import {css, html, nothing} from "@spectrum-web-components/base";
import {DialogWrapper as SpectrumDialogWrapper} from "@spectrum-web-components/dialog";
import {ifDefined} from 'lit/directives/if-defined.js';

@customElement('tbp-dialog-wrapper')
export class DialogWrapper extends SpectrumDialogWrapper {
    renderDialog() {
        const hideDivider =
            this.noDivider ||
            !this.headline ||
            this.headlineVisibility === 'none';

        return html`
            <sp-dialog
                    ?dismissable=${this.dismissable}
                    dismiss-label=${this.dismissLabel}
                    ?no-divider=${hideDivider}
                    ?error=${this.error}
                    mode=${ifDefined(this.mode)}
                    size=${ifDefined(this.size)}
            >
                ${this.hero
                        ? html`
                            <img
                                    src="${this.hero}"
                                    slot="hero"
                                    aria-hidden=${ifDefined(
                                            this.heroLabel ? undefined : 'true'
                                    )}
                                    alt=${ifDefined(
                                            this.heroLabel ? this.heroLabel : undefined
                                    )}
                            />
                        `
                        : nothing}
                <slot></slot>
                <div slot="footer">
                    <slot name="bottom">
                        ${this.footer}
                    </slot>
                </div>
                ${this.cancelLabel
                        ? html`
                            <sp-button
                                    variant="secondary"
                                    treatment="outline"
                                    slot="button"
                                    @click=${this.clickCancel}
                            >
                                ${this.cancelLabel}
                            </sp-button>
                        `
                        : nothing}
                ${this.secondaryLabel
                        ? html`
                            <sp-button
                                    variant="primary"
                                    treatment="outline"
                                    slot="button"
                                    @click=${this.clickSecondary}
                            >
                                ${this.secondaryLabel}
                            </sp-button>
                        `
                        : nothing}
                ${this.confirmLabel
                        ? html`
                            <sp-button
                                    variant="accent"
                                    slot="button"
                                    @click=${this.clickConfirm}
                            >
                                ${this.confirmLabel}
                            </sp-button>
                        `
                        : nothing}
            </sp-dialog>
        `;
    }
}