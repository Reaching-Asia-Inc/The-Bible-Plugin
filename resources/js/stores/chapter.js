import {atom, computed} from "nanostores"
import {$book} from "./book.js";
import {$visitReference} from "./reference.js";

export const $chapter = atom("1")
export const $chapters = computed($book, book => book.chapters ?? [])

export const $hasChapter = (chapter) => {
    return $chapters.get().includes(parseInt(chapter))
}

export const $hasPreviousChapter = computed([$chapter, $chapters], (chapter) => {
    return $hasChapter(chapter - 1)
})

export const $hasNextChapter = computed([$chapter, $chapters], (chapter) => {
    return $hasChapter(chapter + 1)
})

export const $visitChapter = (chapter) => {
    if ($hasChapter(chapter)) {
        $visitReference(`${$book.get().book_id} ${chapter}`)
    }
}

export const $visitNextChapter = () => {
    $visitChapter(parseInt($chapter.get()) + 1)
}

export const $visitPreviousChapter = () => {
    $visitChapter(parseInt($chapter.get()) - 1)
}