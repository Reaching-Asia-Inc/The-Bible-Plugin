import {css, html, unsafeCSS} from "@spectrum-web-components/base";
import {customElement, property, state} from "lit/decorators.js";
import {TBPElement} from "./base.js";
import {createRef, ref} from 'lit/directives/ref.js';
import Hls from "hls.js";

@customElement('tbp-video')
export class Video extends TBPElement {
    @property({type: Array}) content = [];

    @property({type: Boolean, attribute: false}) error = false;

    @property({type: Boolean}) autoplay = false;

    playerRef = createRef();

    videoRef = createRef();

    @state() ready = false;

    get thumbnail() {
        return this.content[0].thumbnail;
    }

    firstUpdated() {
        this.init()
    }

    init() {
        setTimeout(() => {
            const isHls = this.content.some(({files}) => {
              return Array.isArray(files) && files.some(stream => stream.url.includes('.m3u8'));
            });
            let initEvent = new CustomEvent('tpb-player:initialize', {
                bubbles: true,
                composed: true
            });

            if (isHls && Hls.isSupported()) {
                const hls = new Hls();
                this.content.forEach(({files}) => {
                    hls.loadSource(this.selectStream(files)?.url);
                });
                hls.attachMedia(this.videoRef.value);
                hls.on(Hls.Events.MANIFEST_PARSED, () => {
                    this.playerRef.value.dispatchEvent(initEvent);
                });
            } else {
                this.content.forEach(({files}) => {
                    const source = document.createElement('source');
                    source.src = this.selectStream(files)?.url;
                    this.videoRef.value.appendChild(source);
                });
            }
        }, 10)
    }

    render() {
        return html`
            <tbp-player
                    uninitialized
                    ${ref(this.playerRef)}>
                <video ${ref(this.videoRef)}
                       controls
                       ?autoplay="${this.autoplay}"
                       poster="${this.thumbnail}"></video>
            </tbp-player>`;

    }

  selectStream(files) {
    // Get screen dimensions
    const screenWidth = window.innerWidth;
    const screenHeight = window.innerHeight;

    // Parse resolutions into width and height
    const streams = files.map(stream => {
      const [width, height] = stream.resolution.split('x').map(Number);
      return {
        ...stream,
        width,
        height
      };
    });

    // Find the stream that best matches the screen resolution
    // Prefer slightly higher resolution than lower for better quality
    let bestStream = streams[0];
    let minDiff = Infinity;

    streams.forEach(stream => {
      // Calculate how well this stream matches the screen size
      // We compare the width since that's typically the limiting factor
      const diff = Math.abs(stream.width - screenWidth);

      // If this stream is a better match AND not too much larger than the screen
      if (diff < minDiff && stream.width <= screenWidth * 1.5) {
        minDiff = diff;
        bestStream = stream;
      }
    });

    return bestStream;
  }
}
