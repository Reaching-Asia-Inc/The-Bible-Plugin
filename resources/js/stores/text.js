import {computed, atom} from "nanostores"
import {$media, findContent} from "./media"

export const $text = computed($media, media => findContent('text') ?? []);
export const $hasText = computed($media, media => Object
    .values(media)
    .filter(({key}) => key === "text").length > 0)
