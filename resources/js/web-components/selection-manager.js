import {html} from "lit";
import {customElement, queryAll, state} from "lit/decorators.js";
import {TBPElement} from "./base.js";
import {is_mobile, is_safari, shadow_host} from "../helpers.js";
import {$clearSelection, $selectables} from "../stores/selection.js";
import {css} from "@spectrum-web-components/base";

@customElement('tbp-selection-manager')
export class SelectionManager extends TBPElement {
  @state()
  mouseDownCoordinates = {x: 0, y: 0}

  @state()
  contextClick = false

  selectionTimeout = null;

  downSelectable = null;

  get rootComponenet() {
    return document.querySelector('sp-theme') ?? this;
  }

  get hasShadowRootSelection() {
    if (!this.rootComponenet.shadowRoot.getSelection) {
      return false;
    }

    return !!this.rootComponenet.shadowRoot.getSelection().toString();
  }

  get selection() {
    if (this.hasShadowRootSelection) {
      return this.rootComponenet.shadowRoot.getSelection();
    }

    return window.getSelection();
  }

  get selectables() {
    const selectables = this.querySelectorAll('[selectable]');
    $selectables.set(Array.from(selectables));
    return selectables;
  }

  get firstSelectedSelectable() {
    return this.selected[0];
  }

  get lastSelectedSelectable() {
    const idx = this.selected.length - 1;
    return this.selected[idx];
  }

  get unselected() {
    return this.querySelectorAll('[selectable][unselected]');
  }

  get selected() {
    return this.querySelectorAll('[selected]');
  }

  get groups() {
    const groups = [];
    let currentGroup = 0
    this.selectables.forEach((selectable) => {
      if (selectable.selected) {
        if (!groups[currentGroup]) {
          groups[currentGroup] = []
        }
        groups[currentGroup].push(selectable);
      } else {
        if (groups[currentGroup]) {
          currentGroup++;
        }
      }
    });
    return groups;
  }

  constructor() {
    super();

    this.onSelectableClick = this.selectableClickListener.bind(this);
    this.onSelectableClickUp = this.selectableClickUpListener.bind(this);
  }

  connectedCallback() {
    super.connectedCallback();
    $clearSelection()

    this.selectables.forEach((selectable) => {
      selectable.addEventListener('pointerdown', this.onSelectableClick);
      selectable.addEventListener('pointerup', this.onSelectableClickUp);
    });
  }

  selectableClickListener(e) {
    this.downSelectable = e.currentTarget;
    const selectable = e.currentTarget;
    const newValue = !selectable.selected;

    selectable.selected = newValue;

    this.finalizeSelection(selectable);
  }

  selectableClickUpListener(e) {
    window.getSelection().empty();
    const downSelectable = this.downSelectable;
    this.downSelectable = null;
    const selectable = e.currentTarget;

    if (downSelectable === selectable) {
      return;
    }

    if (!selectable) {
      return;
    }

    if (selectable.selected) {
      return;
    }

    selectable.selected = true;
    this.finalizeSelection(selectable);
  }

  finalizeSelection(selectable) {
    setTimeout(() => {
      if (selectable.selected) {
        //We added a new value, fill in any selections between the first and last selected
        const firstSelected = this.firstSelectedSelectable;
        const lastSelected = this.lastSelectedSelectable;
        let foundFirst = false;
        let foundLast = false;
        this.selectables.forEach((el) => {
          if (el === firstSelected) {
            foundFirst = true;
          }
          el.selected = foundFirst && !foundLast;
          if (el === lastSelected) {
            foundLast = true;
          }
        });
      } else {
        //We removed a selection, remove extra groups
        this.groups.forEach((group, idx) => {
          if (idx) {
            group.forEach((el) => {
              el.selected = false;
            });
          }
        });
      }

      this.dispatchSelection();
    }, 1);
  }

  disconnectedCallback() {
    this.selectables.forEach((selectable) => {
      selectable.removeEventListener('click', this.onSelectableClick);
    });
    super.disconnectedCallback();
  }

  render() {
    return html`
      <slot></slot>
    `
  }

  dispatchSelection() {
    setTimeout(() => {
      this.dispatchEvent(new CustomEvent('selection', {
        detail: {
          selectedText: this.selection.toString(),
          selectables: Array.from(this.selectables),
          selected: Array.from(this.selected),
          unselected: Array.from(this.unselected)
        }
      }));
    })

  }
}
