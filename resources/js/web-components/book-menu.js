import {customElement} from "lit/decorators.js";
import {withStores} from "@nanostores/lit";
import {TBPElement} from "./base.js";
import {css, html, nothing} from "@spectrum-web-components/base";
import {$visitBook} from "../stores/book.js"
import {property, state, query} from "lit/decorators.js";
import {$book} from "../stores/book.js";
import {trackEvent} from "../analytics.js";

@customElement('tbp-book-menu')
export class BookMenu extends withStores(TBPElement, [$book]) {

    @property({type: String}) label = "Books";
    @property({type: Array}) books = [];
    @state() selectedBook = null;
    @query('[slot="trigger"]') trigger;

    static get styles() {
        return css`
            :host {
                --mod-popover-animation-distance: 0px;
                --mod-menu-section-divider-margin-block: 0;
                --spectrum-divider-thickness-medium: 1px;
            }

            #book-menu {
                max-height: 60vh;
                overflow-y: scroll;
                overflow-x: hidden;
                --mod-popover-content-area-spacing-vertical: 0px;
                min-width: 275px;
            }

            .book-menu__item > sp-action-button {
                justify-content: flex-start;
            }

            sp-popover {
                padding: 0px;
            }

            sp-action-button {
                border-radius: 0 !important;
            }

            .book-menu__chapter-menu {
                display: grid;
                grid-template-columns: repeat(6, 1fr);
                gap: 0;
            }
        `
    }

    render() {
        return html`
            <overlay-trigger placement="bottom-end" offest="1">
                <sp-action-button label="${this.label}" slot="trigger">
                    <sp-icon-more slot="icon"></sp-icon-more>
                    ${this.label}
                </sp-action-button>
                <sp-popover slot="click-content" direction="bottom-end">
                    <sp-action-group
                            id="book-menu"
                            selects="single"
                            compact
                            vertical
                    >
                        ${this.books.map(this.renderBook.bind(this))}
                    </sp-action-group>
                </sp-popover>
            </overlay-trigger>
        `
    }

    renderBook(book) {
        return html`
            <div class="book-menu__item">
                <sp-action-button
                        ?selected="${this.isBookSelected(book) || this.isBookCurrent(book) }"
                        @click=${(e) => this.handleBookClick(e, book)}
                >${book.name}
                </sp-action-button>
                ${this.isBookSelected(book) ? this.renderChapterMenu(book) : nothing}
            </div>
        `
    }

    renderChapterMenu(book) {
        return html`
            <div class="book-menu__chapter-menu">
                ${book.chapters.map(chapter => this.renderChapter(book, chapter))}
                </sp-action-group>
        `
    }

    renderChapter(book, chapter) {
        return html`
            <sp-action-button
                    emphasized
                    selected
                    class="chapter-menu__item"
                    @click=${() => this.handleChapterClick(book, chapter)}
            >${chapter}
            </sp-action-button>
        `
    }

    handleChapterClick(book, chapter) {
        this.visitBook(book, chapter);
    }

    handleBookClick(e, book) {
        if (this.isBookSelected(book)) {
            this.visitBook(book)
        } else {
            this.selectBook(book)
        }
    }

    selectBook(book) {
        this.selectedBook = book;
    }

    visitBook(book, chapter = 1) {
        trackEvent('tbp_book_visit', {
            book: book.name,
            book_id: book.id,
            chapter: chapter
        });
        $visitBook(book, chapter);
        this.trigger.click();
        this.selectedBook = null;
    }

    isBookCurrent(book) {
        return $book.get().id === book.id;
    }

    isBookSelected(book) {
        return this.selectedBook && this.selectedBook.id === book.id;
    }
}
