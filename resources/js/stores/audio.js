import {computed, atom, map, onMount} from "nanostores"
import {$media, findContent} from "./media"
import {$hasText} from "./text.js";
import {$videoOpen} from "./video.js";  // Import videoOpen store

export const $audio = computed($media, media => findContent('audio') ?? []);
export const $hasAudio = computed($media, media => Object
    .values(media)
    .filter(({key}) => key === "audio").length > 0)

export const $audioOpen = atom(false)

export const $playAudio = () => {
    if (!$hasAudio.get()) return

    if ($audioOpen.get()) {
        $audioOpen.set(false)
        setTimeout(() => $audioOpen.set(true))
    } else {
        $audioOpen.set(true)
    }
}
