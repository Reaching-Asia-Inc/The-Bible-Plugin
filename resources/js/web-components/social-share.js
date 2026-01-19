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
            quiet
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
            quiet
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
          @click=${() => this.openShare(this.facebookUrl, 'facebook')}
        >
          <iconify-icon
            icon="simple-icons:facebook"
            slot="icon"
            width="18"
            height="18"
            style="color: currentColor"
          ></iconify-icon>
          Facebook
        </sp-button>

        <sp-button
          label="X"
          @click=${() => this.openShare(this.xUrl, 'x')}
        >
          <iconify-icon
            icon="simple-icons:x"
            slot="icon"
            width="16"
            height="16"
            style="color: currentColor"
          ></iconify-icon>
          Twitter
        </sp-button>
      </div>
    `;
  }
}
