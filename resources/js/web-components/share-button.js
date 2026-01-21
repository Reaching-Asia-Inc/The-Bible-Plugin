import {customElement, property, query, state} from "lit/decorators.js";
import {onSet} from "nanostores";
import {withStores} from "@nanostores/lit";
import {TBPElement} from "./base.js";
import {html, nothing} from "lit";
import {__} from "../helpers.js";
import {css} from "@spectrum-web-components/base";
import {$share, $canShare, $shareUrl, $shareText} from "../stores/share.js";
import {$selectionCount} from "../stores/selection.js";
import {$displayMessage} from "../stores/message.js";
import {trackEvent} from "../analytics.js";
import "./social-share.js";

@customElement('tbp-share-button')
export class ShareButton extends withStores(TBPElement, [$canShare, $selectionCount, $shareUrl, $shareText]) {
  @state()
  message = false

  @query('sp-dialog-wrapper')
  dialog

  static get styles() {
    return css`
      .count {
        display: block;
        border-radius: 50%;
      }

      .selection__copy {
        display: flex;
        gap: 1rem;
        align-items: center;
        margin-bottom: 1rem;

        sp-label {
          width: 50px;
          flex-shrink: 0;
        }

        sp-textfield {
          flex: 1;
          display: block;
        }

        sp-button {
          flex-shrink: 0;
          background-color: var(--spectrum-accent-color-default);
          color: white;
          border: none;
          cursor: pointer;
          --mod-button-background-color-default: var(--spectrum-accent-color-default);
          --mod-button-background-color-hover: var(--spectrum-accent-color-700);
          --mod-button-background-color-active: var(--spectrum-accent-color-800);
          --mod-button-content-color-default: white;
          --mod-button-content-color-hover: white;
          --mod-button-content-color-active: white;
        }
      }

      .selection__copy.text-row {
        align-items: flex-start;

        sp-label {
          margin-top: 8px;
        }
      }
    `
  }

  get displayCount() {
    return $selectionCount.get() !== 0;
  }

  handleShareClick() {
    try {
      trackEvent('tbp_share_click', {
        selection_count: $selectionCount.get()
      });
    } catch (e) {
      // noop
    }
  }

  copyText(text, type) {
    trackEvent('tbp_copy_clipboard', {
        type: type,
        text_length: text?.length || 0
    });
    navigator.clipboard.writeText(text)
    $displayMessage(__('Copied to clipboard'))
    this.close()
  }

  close() {
    this.dialog.close()
  }

  render() {
    return html`
      <sp-action-button
        ?emphasized="${this.displayCount}"
        ?quiet="${!this.displayCount}"
        ?selected="${this.displayCount}"
        icon-only="${!this.displayCount}"
        label="${this.displayCount ? __("Selected") : ""}"
        @click=${() => this.handleShareClick()}
        id="trigger"
      >
        ${this.displayCount ?  html`
          <b slot="icon" class="count">
            ${$selectionCount.get()}
          </b>
        ` : html`<sp-icon-share slot="icon"></sp-icon-share>`}
        ${__( this.displayCount ? "Selected" : nothing)}
      </sp-action-button>

      <sp-overlay trigger="trigger@click"
                  class="selection_modal"
                  type="modal"
      >
        <sp-dialog-wrapper headline="${__('Share')}"
                           size="l"
                           dismissable
                           underlay>
          <div class="selection__copy">
            <sp-label>${__('Link')}</sp-label>
            <sp-textfield disabled value="${$shareUrl.get()}"></sp-textfield>
            <sp-button @click="${() => this.copyText($shareUrl.get(), 'url')}">
              ${__('Copy')}
            </sp-button>
          </div>

          <div class="selection__copy text-row">
            <sp-label>${__('Text')}</sp-label>
            <sp-textfield disabled multiline value="${$shareText.get()}"></sp-textfield>
            <sp-button @click="${() => this.copyText($shareText.get(), 'text')}">
              ${__('Copy')}
            </sp-button>
          </div>

          <div class="selection__copy">
            <sp-label>${__('Share')}</sp-label>
            <tbp-social-share></tbp-social-share>
          </div>
        </sp-dialog-wrapper>
      </sp-overlay>
    `
  }
}
