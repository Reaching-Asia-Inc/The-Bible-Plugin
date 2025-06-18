// store/posts.ts
import {fetchState} from '../nanostore/fetch-state.js';
import {$bible, $bibleAbbr} from "./bible.js";
import {$book, $bookId} from "./book.js";
import {$chapter} from "./chapter.js";
import {$reference} from './reference.js';
import {$media} from "./media.js";
import {$language, $languageId, $languageIso} from "./language.js";
import {$error} from "./error.js";
import {apiUrl} from "../helpers.js";

let endpoint = apiUrl('scripture')
if (!endpoint.includes('?')) {
    endpoint += '?'
} else {
    endpoint += '&'
}

export const $query = fetchState([endpoint, 'reference=', $reference, '&video=', true]);
$query.listen((response) => {
    const {loading, data, error} = response;

    if (loading) {
        return;
    }

    if (error) {
        $error.set(error.message)
        return;
    }

    if (data.error) {
        $error.set(data.error)
        return;
    }

    $bible.set(data.bible ?? {});
    $bibleAbbr.set(data.bible.abbr ?? '');
    $book.set(data.book ?? {});
    $bookId.set(data.book.book_id ?? '');
    $chapter.set(data.chapter ?? '');
    $language.set(data.language);
    $languageId.set(data.language.id);
    $languageIso.set(data.language.iso);
    $media.set(data.media ?? {})
});
