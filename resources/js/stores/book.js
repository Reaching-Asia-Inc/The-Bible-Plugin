import {atom, computed} from "nanostores"
import {$bible} from "./bible.js";
import {$visitReference} from "./reference.js";

export const $books = computed($bible, (bible) => bible.books ?? [])

export const $otBooks = computed($books, (books) => books.filter((book) => book.testament === "OT"))

export const $ntBooks = computed($books, (books) => books.filter((book) => book.testament === "NT"))

export const $book = atom({})
export const $bookId = atom("")

export const $bookName = computed($book, (book) => book.name)

export const $visitBook = (book, chapter = 1) => {
    $visitReference(`${book.id} ${chapter}`)
}
