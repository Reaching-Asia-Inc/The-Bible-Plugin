import {computed, task} from "nanostores";
import {reference_from_object} from "../helpers.js";
import {$selection, $hasSelection, $selectables} from "./selection.js";
import {$displayMessage} from "./message.js";
import {$referenceLabel} from "./reference.js";

export const $shareUrl = computed($selection, (selection) => {
  const pageUrl = new URL(window.location.href);
  if (!selection.length) {
    return pageUrl.toString();
  }
  const first = selection[0];
  const last = selection[selection.length - 1];

  const newReference = encodeURIComponent(reference_from_object({
    book: first.book,
    chapter: first.chapter,
    verse_start: first.verse_start,
    verse_end: last.verse_end
  }));

  pageUrl.searchParams.set('reference', newReference);

  return pageUrl.toString();
});

export const $shareText = computed([$selection, $selectables], (selection, selectables) => {
  const elements = selection.length ? selection : selectables;

  return elements.map((item) => {
    return item.text
  }).join("\n")
})

export const $canShare = computed([$shareUrl, $shareText], userId => task(async () => {
  if (!navigator.share) {
    return false;
  }
  return navigator.canShare({
    title: $referenceLabel.get(),
    url: $shareUrl.get(),
    text: $shareText.get()
  })
}))

export const $share = async () => {
  const canShare = await $canShare.get();

  if (!canShare) {
    $displayMessage("Sharing is not supported on this device.")
    return false;
  }

  try {
    await navigator.share({
      title: $referenceLabel.get(),
      url: $shareUrl.get(),
      text: $shareText.get()
    })
  } catch (error) {
    //do nothing
  }
}
