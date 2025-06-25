import {customElement} from "lit/decorators.js";
import {css, html, LitElement, nothing} from "lit";
import {withStores} from "@nanostores/lit";
import {TBPElement} from "./base.js";
import {$canShare} from "../stores/share.js";
import {$selectionCount} from "../stores/selection.js";
import {$hasVideo, $videoOpen, $video} from "../stores/video.js";

@customElement('tbp-video-pane')
export class VideoPane extends withStores(TBPElement, [$hasVideo, $videoOpen, $video]) {

  static get styles() {
    return [
      super.styles,
      css`
        #video {
          display: block;
          margin: 0 auto;
            max-width: var(--wp--style--global--wide-size, 1200px);
        }
            `
    ];
  }

  render() {
    if (!$hasVideo.get() || !$videoOpen.get()) {
      return nothing;
    }

    console.log($video.get())
    return html`
            <tbp-video .content="${$video.get()}" id="video"></tbp-video>`;

  }
}
