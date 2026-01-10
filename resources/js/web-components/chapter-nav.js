import {customElement, property} from "lit/decorators.js";
import {TBPElement} from "./base.js";
import {withStores} from "@nanostores/lit";
import {css, html, nothing} from "@spectrum-web-components/base";
import {trackEvent} from "../analytics.js";

import {
    $chapter,
    $hasPreviousChapter,
    $hasNextChapter,
    $visitPreviousChapter,
    $visitNextChapter
} from "../stores/chapter.js"

@customElement('tbp-chapter-nav')
export class ChapterNav extends withStores(TBPElement, [$chapter, $hasPreviousChapter, $hasNextChapter]) {
    static get styles() {
        return [
            super.styles,
            css`
                sp-button-group {
                    flex-wrap: nowrap;
                }

            `
        ];
    }

    render() {
        return html`
            <sp-button-group class="#chapter-nav">
                ${$hasPreviousChapter.get() ? html`
                    <sp-button @click="${() => {
                        $visitPreviousChapter();
                        trackEvent('tbp_chapter_nav', { direction: 'previous' });
                    }}"
                               icon-only>
                        <sp-icon-arrow500 slot="icon" name="ui:Arrow100" style="transform: rotate(180deg);">
                        </sp-icon-arrow500>
                    </sp-button>` : ''}

                ${$hasNextChapter.get() ? html`
                    <sp-button @click="${() => {
                        $visitNextChapter();
                        trackEvent('tbp_chapter_nav', { direction: 'next' });
                    }}"
                               icon-only>
                        <sp-icon-arrow500 slot="icon" name="ui:Arrow100"></sp-icon-arrow500>
                    </sp-button>` : ''}
            </sp-button-group>
        `;
    }
}
