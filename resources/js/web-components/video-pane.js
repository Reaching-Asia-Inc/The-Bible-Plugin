import {customElement} from "lit/decorators.js";
import {html, LitElement, nothing} from "lit";
import {withStores} from "@nanostores/lit";
import {TBPElement} from "./base.js";
import {$canShare} from "../stores/share.js";
import {$selectionCount} from "../stores/selection.js";
import {$hasVideo, $videoOpen, $video} from "../stores/video.js";

@customElement('tbp-video-pane')
export class VideoPane extends withStores(TBPElement, [$hasVideo, $videoOpen, $video]) {
  render() {
    if (!$hasVideo.get() || !$videoOpen.get()) {
      return nothing;
    }

    console.log($video.get())
    return html`
            <tbp-video .content="${$video.get()}"></tbp-video>`;

  }
}
