import {customElement, property} from "lit/decorators.js";
import {TBPElement} from "./base.js";
import Plyr from 'plyr';
import plyrStyles from 'plyr/dist/plyr.css?inline';
import {css, html, unsafeCSS} from "@spectrum-web-components/base";
import {createRef, ref} from "lit/directives/ref.js";
import svg from 'plyr/dist/plyr.svg?raw'
import {unsafeSVG} from "lit-html/directives/unsafe-svg.js";
import {trackEvent} from "../analytics.js";

/**
 * Represents a Scripture element that wraps around a video or audio player.
 * @extends TBPElement
 * @customElement('tbp-player')
 */
@customElement('tbp-player')
export class Scripture extends TBPElement {
    @property({type: Boolean}) uninitialized = false;

    /**
     * Variable representing a reference to the root of a data structure.
     *
     * @type {Object}
     * @name rootRef
     * @function
     * @description Creates a new reference object.
     * @returns {Object} A new reference object.
     */
    rootRef = createRef()


    /**
     * Retrieves the element within the shadow root with the tag 'video' or 'audio'.
     *
     * @return {Element} The DOM element found within the shadow root.
     */
    get el() {
        return this.shadowRoot.querySelector('video,audio')
    }

    /**
     * Checks if the element has autoplay attribute.
     *
     * @return {boolean} - True if the element has autoplay attribute, false otherwise.
     */
    get autoplay() {
        return this.el && this.el.hasAttribute('autoplay')
    }

    /**
     * Retrieves the styles for the player.
     *
     * @return {Array} Array of CSS styles for the player.
     */
    static get styles() {
        return [
            unsafeCSS(plyrStyles),
            super.styles,
            css`
                :host {
                    --plyr-color-main: var(--spectrum-accent-color-1300);
                }

                .player__svg {
                    display: none;
                }
            `
        ]
    }

    /**
     * Initializes the component by adding an event listener and calling the
     * superclass's `connectedCallback` method.
     *
     * @return {void}
     */
    connectedCallback() {
        this.addEventListener('tbp-player:initialize', this.init.bind(this))
        super.connectedCallback();
    }

    /**
     * Executes the initialization logic if the component is not uninitialized.
     *
     * @param {Object} _changedProperties - The object containing the properties which have been changed.
     * @return {void}
     */
    firstUpdated(_changedProperties) {
        if (!this.uninitialized) {
            this.init()
        }
    }

    /**
     * Render the HTML representation of the player component.
     * @return {string} The HTML string representing the player component.
     */
    render() {
        return html`
            <div class="player__wrapper">
                <div class="player" ${ref(this.rootRef)}>
                    ${this.children}
                </div>
                <div class="player__svg">
                    ${unsafeSVG(svg)}
                </div>
            </div>`
    }

    /**
     * Initializes the Plyr player.
     *
     * @method init
     * @memberof ClassName
     *
     * @returns {void} Nothing is returned.
     */
    init() {
        if (!this.el) {
            return
        }
        this.plyr = new Plyr(
            this.el
        )

        this.plyr.on('play', () => {
            trackEvent('tbp_player_play', {
                type: this.el.tagName.toLowerCase(),
                src: this.el.currentSrc
            });
        });

        this.plyr.on('pause', () => {
            trackEvent('tbp_player_pause', {
                type: this.el.tagName.toLowerCase(),
                src: this.el.currentSrc
            });
        });

        this.plyr.on('ended', () => {
            trackEvent('tbp_player_ended', {
                type: this.el.tagName.toLowerCase(),
                src: this.el.currentSrc
            });
        });

        setTimeout(() => {
            if (this.autoplay) {
                this.plyr.play()
            }
        })
    }
}
