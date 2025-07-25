import {atom} from "nanostores"

export const $media = atom({})

export const findMedia = (type) => {
    return $media.get()[type] ?? null
}

export const findContent = (type) => {
    return findMedia(type)?.content ?? null
}
