import {computed, atom, map, onMount} from "nanostores"
import {$media, findContent} from "./media"
import {$hasText} from "./text.js";
import {$audioOpen} from "./audio.js";  // Import videoOpen store

export const $video = computed($media, media => findContent('video') ?? []);
export const $hasVideo = computed($media, media => Object
    .values(media)
    .filter(({key}) => key === "video").length > 0)

export const $videoOpen = atom(true)

export const $playVideo = () => {
    if (!$hasVideo.get()) return

    if ($videoOpen.get()) {
        $videoOpen.set(false)
        setTimeout(() => $videoOpen.set(true))
    } else {
        $videoOpen.set(true)
        $audioOpen.set(false)
    }
}
