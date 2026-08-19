import {html} from "lit";
import {customElement, property, queryAll, state} from "lit/decorators.js";
import {TBPElement} from "./base.js";
import {has_touch, is_safari, shadow_host, __} from "../helpers.js";
import {$clearSelection, $selectables} from "../stores/selection.js";
import {css} from "@spectrum-web-components/base";
import "./share-button.js";

const LONG_PRESS_MS = 500;
const LONG_PRESS_MOVE_TOLERANCE = 10;

@customElement('tbp-selection-manager')
export class SelectionManager extends TBPElement {
  static get styles() {
    return [
      super.styles,
      css`
        :host {
          display: block;
        }

        .selection-mode-bar {
          position: sticky;
          top: 0;
          z-index: 10;
          display: flex;
          align-items: center;
          justify-content: space-between;
          gap: 1rem;
          padding: .5rem 1rem;
          background: var(--tbp-selection-mode-bar-background-color, var(--spectrum-gray-100, #fff));
          border-bottom: 1px solid var(--spectrum-gray-300, #ccc);
        }

        .selection-mode-bar__done {
          border: none;
          background: none;
          color: var(--spectrum-accent-color-900, #2680eb);
          font-weight: 600;
          cursor: pointer;
          padding: .25rem .5rem;
        }
      `
    ];
  }

  @state()
  mouseDownCoordinates = {x: 0, y: 0}

  @state()
  contextClick = false

  @state()
  selectionMode = false

  @state()
  selectionCount = 0

  selectionTimeout = null;

  downSelectable = null;

  longPressTimeout = null;

  longPressStart = null;

  @property({type: Boolean, reflect: true, attribute: 'touch-mode'})
  touchMode = has_touch();

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
    this.onSelectableTouchStart = this.selectableTouchStartListener.bind(this);
    this.onSelectableTouchEnd = this.selectableTouchEndListener.bind(this);
    this.onSelectableTouchMove = this.selectableTouchMoveListener.bind(this);
    this.onSelectableToggle = this.selectableToggleListener.bind(this);
    this.onSelectableContextMenu = this.selectableContextMenuListener.bind(this);
  }

  connectedCallback() {
    super.connectedCallback();
    $clearSelection()

    this.selectables.forEach((selectable) => {
      if (this.touchMode) {
        selectable.addEventListener('pointerdown', this.onSelectableTouchStart);
        selectable.addEventListener('pointerup', this.onSelectableTouchEnd);
        selectable.addEventListener('pointermove', this.onSelectableTouchMove);
        selectable.addEventListener('pointercancel', this.onSelectableTouchEnd);
        selectable.addEventListener('selectable-toggle', this.onSelectableToggle);
        // The browser's own long-press (text selection callout / context menu)
        // would otherwise fire alongside our custom long-press handling.
        selectable.addEventListener('contextmenu', this.onSelectableContextMenu);
      } else {
        selectable.addEventListener('pointerdown', this.onSelectableClick);
        selectable.addEventListener('pointerup', this.onSelectableClickUp);
      }
    });
  }

  selectableContextMenuListener(e) {
    e.preventDefault();
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

  selectableTouchStartListener(e) {
    this.longPressStart = {x: e.clientX, y: e.clientY};
    const selectable = e.currentTarget;

    clearTimeout(this.longPressTimeout);
    this.longPressTimeout = setTimeout(() => {
      if (this.selectionMode) {
        this.selectSelectable(selectable);
      } else {
        this.enterSelectionMode(selectable);
      }
    }, LONG_PRESS_MS);
  }

  selectableTouchMoveListener(e) {
    if (!this.longPressStart) {
      return;
    }

    const dx = Math.abs(e.clientX - this.longPressStart.x);
    const dy = Math.abs(e.clientY - this.longPressStart.y);

    if (dx > LONG_PRESS_MOVE_TOLERANCE || dy > LONG_PRESS_MOVE_TOLERANCE) {
      clearTimeout(this.longPressTimeout);
      this.longPressStart = null;
    }
  }

  selectableTouchEndListener(e) {
    clearTimeout(this.longPressTimeout);
    this.longPressStart = null;
  }

  selectableToggleListener(e) {
    const selectable = e.currentTarget;
    selectable.selected = e.detail.selected;
    this.finalizeSelection(selectable);
  }

  selectSelectable(selectable) {
    selectable.selected = true;
    this.finalizeSelection(selectable);
  }

  enterSelectionMode(selectable) {
    this.selectionMode = true;
    this.dispatchEvent(new CustomEvent('context', {detail: {selectable}}));
    this.selectSelectable(selectable);

    this.updateComplete.then(() => {
      this.scrollSelectableIntoView(selectable);
    });
  }

  scrollSelectableIntoView(selectable) {
    const bar = this.shadowRoot?.querySelector('.selection-mode-bar');
    const barHeight = bar?.getBoundingClientRect().height ?? 0;
    const rect = selectable.getBoundingClientRect();
    const visibleTop = barHeight;
    const visibleBottom = window.innerHeight;

    const hiddenAbove = rect.top < visibleTop;
    const hiddenBelow = rect.bottom > visibleBottom;

    if (!hiddenAbove && !hiddenBelow) {
      return;
    }

    let delta;
    if (hiddenAbove) {
      // Align the top of the verse just below the bar.
      delta = rect.top - visibleTop - 16;
    } else {
      // Align the bottom of the verse just above the viewport edge,
      // but never scroll past the point where the top would go under the bar.
      delta = Math.min(rect.bottom - visibleBottom + 16, rect.top - visibleTop - 16);
    }

    const scrollParent = this.getScrollParent();

    if (scrollParent === document.scrollingElement || scrollParent === document.documentElement) {
      window.scrollBy({top: delta, behavior: 'smooth'});
    } else {
      scrollParent.scrollBy({top: delta, behavior: 'smooth'});
    }
  }

  getScrollParent() {
    let node = this;

    while (node) {
      node = node.parentElement ?? node.getRootNode()?.host ?? null;

      if (!node) {
        break;
      }

      const style = getComputedStyle(node);
      const canScroll = /(auto|scroll)/.test(style.overflowY) && node.scrollHeight > node.clientHeight;
      if (canScroll) {
        return node;
      }
    }

    return document.scrollingElement ?? document.documentElement;
  }

  exitSelectionMode() {
    this.selectionMode = false;
    $clearSelection();
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

      this.selectionCount = this.selected.length;
      this.dispatchSelection();
    }, 1);
  }

  disconnectedCallback() {
    this.selectables.forEach((selectable) => {
      selectable.removeEventListener('pointerdown', this.onSelectableClick);
      selectable.removeEventListener('pointerup', this.onSelectableClickUp);
      selectable.removeEventListener('pointerdown', this.onSelectableTouchStart);
      selectable.removeEventListener('pointerup', this.onSelectableTouchEnd);
      selectable.removeEventListener('pointermove', this.onSelectableTouchMove);
      selectable.removeEventListener('pointercancel', this.onSelectableTouchEnd);
      selectable.removeEventListener('selectable-toggle', this.onSelectableToggle);
      selectable.removeEventListener('contextmenu', this.onSelectableContextMenu);
    });
    super.disconnectedCallback();
  }

  render() {
    this.selectables.forEach((selectable) => {
      selectable.selectionMode = this.selectionMode;
    });

    return html`
      ${this.selectionMode ? html`
        <div class="selection-mode-bar">
          <tbp-share-button></tbp-share-button>
          <button type="button" class="selection-mode-bar__done" @click="${() => this.exitSelectionMode()}">
            ${__('Done')}
          </button>
        </div>
      ` : ''}
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
