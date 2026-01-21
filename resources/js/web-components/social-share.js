import {customElement, property} from "lit/decorators.js";
import {withStores} from "@nanostores/lit";
import {TBPElement} from "./base.js";
import {html} from "lit";
import {css} from "@spectrum-web-components/base";
import {__} from "../helpers.js";
import {$shareUrl, $shareText} from "../stores/share.js";
import {trackEvent} from "../analytics.js";

@customElement('tbp-social-share')
export class SocialShare extends withStores(TBPElement, [$shareUrl, $shareText]) {
  @property({type: Boolean, reflect: true}) compact = false;

  static get styles() {
    return [
      super.styles,
      css`
        :host {
          display: inline-block;
        }

        .row {
          display: flex;
          gap: 1rem;
          align-items: center;
          flex-wrap: wrap;
        }

        .label {
          margin-inline-end: 1rem;
        }

        iconify-icon {
          display: flex;
        }

        sp-button {
          background-color: var(--spectrum-accent-color-default);
          border-radius: 50%;
          color: white;
          --mod-button-padding-inline-start: 0;
          --mod-button-padding-inline-end: 0;
          --mod-button-background-color-default: var(--spectrum-accent-color-default);
          --mod-button-background-color-hover: var(--spectrum-accent-color-700);
          --mod-button-background-color-active: var(--spectrum-accent-color-800);
          --mod-button-content-color-default: white;
          --mod-button-content-color-hover: white;
          --mod-button-content-color-active: white;
          width: 36px;
          height: 36px;
          min-width: 36px;
          display: flex;
          align-items: center;
          justify-content: center;
          border: none;
          cursor: pointer;
        }

        sp-button:hover {
            background-color: var(--spectrum-accent-color-700);
            color: white;
        }
      `
    ];
  }

  openShare(url, platform) {
    try {
      trackEvent('tbp_social_share', {
        platform: platform,
        url: $shareUrl.get() || window.location.href,
        text: $shareText.get() || ""
      });
      window.open(url, "_blank", "noopener,noreferrer");
    } catch (e) {
      // ignore
    }
  }

  get facebookUrl() {
    const url = $shareUrl.get() || window.location.href;
    return `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
  }

  get xUrl() {
    const url = $shareUrl.get() || window.location.href;
    const text = $shareText.get() || "";
    // X supports /intent/post, include both text and url for best results
    return `https://x.com/intent/post?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`;
  }

  render() {
    if (this.compact) {
      return html`
        <div class="row" role="group" aria-label="${__('Share on')}">
          <sp-button
            label="Facebook"
            icon-only
            @click=${() => this.openShare(this.facebookUrl, 'facebook')}
          >
            <iconify-icon
              icon="simple-icons:facebook"
              slot="icon"
              width="18"
              height="18"
              style="color: currentColor"
            ></iconify-icon>
          </sp-button>

          <sp-button
            label="X"
            icon-only
            @click=${() => this.openShare(this.xUrl, 'x')}
          >
            <iconify-icon
              icon="simple-icons:x"
              slot="icon"
              width="16"
              height="16"
              style="color: currentColor"
            ></iconify-icon>
          </sp-button>
        </div>
      `;
    }

    return html`
      <div class="row">
        <sp-button
          label="Facebook"
          icon-only
          @click=${() => this.openShare(this.facebookUrl, 'facebook')}
        >
          <iconify-icon
            icon="simple-icons:facebook"
            slot="icon"
            width="18"
            height="18"
            style="color: currentColor"
          ></iconify-icon>
        </sp-button>

        <sp-button
          label="X"
          icon-only
          @click=${() => this.openShare(this.xUrl, 'x')}
        >
          <iconify-icon
            icon="simple-icons:x"
            slot="icon"
            width="16"
            height="16"
            style="color: currentColor"
          ></iconify-icon>
        </sp-button>
      </div>
    `;
  }
}
