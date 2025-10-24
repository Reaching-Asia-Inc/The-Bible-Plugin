import {customElement, property, query, state} from "lit/decorators.js";
import {onSet} from "nanostores";
import {withStores} from "@nanostores/lit";
import {TBPElement} from "./base.js";
import {html, nothing} from "lit";
import {__} from "../helpers.js";
import {css} from "@spectrum-web-components/base";
import {$share, $canShare} from "../stores/share.js";
import {$selectionCount} from "../stores/selection.js";
import {trackEvent} from "../analytics.js";

@customElement('tbp-share-button')
export class SelectionButton extends withStores(TBPElement, [$canShare, $selectionCount]) {
  @state()
  message = false

  static get styles() {
    return css`
      .count {
        display: block;
        border-radius: 50%;
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
    $share();
  }

  render() {
    if (!$canShare.get()) {
      return nothing;
    }

    return html`
      <sp-action-button
        ?emphasized="${this.displayCount}"
        ?quiet="${!this.displayCount}"
        ?selected="${this.displayCount}"
        icon-only="${!this.displayCount}"
        label="${this.displayCount ? __("Selected") : ""}"
        @click=${() => this.handleShareClick()}
      >
        ${this.displayCount ?  html`
          <b slot="icon" class="count">
            ${$selectionCount.get()}
          </b>
        ` : html`<sp-icon-share slot="icon"></sp-icon-share>`}
        ${__( this.displayCount ? "Selected" : nothing)}
      </sp-action-button>
    `
  }
}
