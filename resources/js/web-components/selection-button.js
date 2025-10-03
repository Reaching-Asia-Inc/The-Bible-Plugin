import {customElement, property, query, state} from "lit/decorators.js";
import {onSet} from "nanostores";
import {withStores} from "@nanostores/lit";
import {TBPElement} from "./base.js";
import {
  $selection,
  $clearSelection,
  $openSelection,
  $selectionOpen,
  $selectionCount,
} from "../stores/selection.js";
import {
  $shareUrl,
  $shareText,
} from "../stores/share.js";
import {html, nothing} from "lit";
import {__} from "../helpers.js";
import {css} from "@spectrum-web-components/base";
import {$book} from "../stores/book.js";
import {$displayMessage} from "../stores/message.js";
import "./social-share.js";

@customElement('tbp-selection-button')
export class SelectionButton extends withStores(TBPElement, [$selection, $selectionOpen, $shareUrl, $shareText, $book, $selectionCount]) {
  @state()
  message = false

  @query('sp-dialog-wrapper')
  dialog

  @query('#trigger')
  trigger

  get displayCount() {
    return $selectionCount.get() !== 0;
  }

  static get styles() {
    return css`
      sp-toast {
        position: fixed;
        right: 50%;
        transform: translate(50%, 0);
        top: 50%;
      }

      .selection__copy {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        margin-bottom: .5rem;

        sp-textfield {
          flex: 1;
          width: 100%;
          display: block;
        }
      }

      .count {
        display: block;
        border-radius: 50%;
      }
    `
  }

  render() {
    return html`
      <sp-action-button
        ?emphasized="${this.displayCount}"
        ?quiet="${!this.displayCount}"
        ?selected="${this.displayCount}"
        icon-only="${!this.displayCount}"
        label="Share"
        id="trigger"
      >
        ${this.displayCount ? html`
          <b slot="icon" class="count">
            ${$selectionCount.get()}
          </b>
        ` : html`
          <sp-icon-share slot="icon"></sp-icon-share>`}
        ${__(this.displayCount ? "Selected" : nothing)}
      </sp-action-button>

      <sp-overlay trigger="trigger@click"
                  class="selection_modal"
                  type="modal"
      ">
      <sp-dialog-wrapper headline="${__('Share')}"
                         size="l"
                         dismissable
                         underlay
                         @close="${() => this.handleClose()}">
        <div class="selection__copy">
          <sp-label>${__('Link')}</sp-label>
          <sp-textfield disabled value="${$shareUrl.get()}"></sp-textfield>
          <sp-button @click="${() => this.copyText($shareUrl.get())}">
            ${__('Copy')}
          </sp-button>
        </div>

        <div class="selection__copy">
          <sp-label>${__('Text')}</sp-label>
          <sp-textfield disabled multiline value="${$shareText.get()}"></sp-textfield>
          <sp-button @click="${() => this.copyText($shareText.get())}">
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

  handleClose() {
    $selectionOpen.set(false)
  }

  copyText(text) {
    navigator.clipboard.writeText(text)
    $displayMessage(__('Copied to clipboard'))
    this.close()
  }

  close() {
    this.dialog.close()
  }
}
