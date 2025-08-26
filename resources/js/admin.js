/**
 * Styles
 */
import '../css/theme.css';
import '../css/admin.css';

/**
 * Dependencies
 */
import Alpine from 'alpinejs'
import {loaded} from './helpers';

/**
 * Spectrum Web Components
 */
import '@spectrum-web-components/theme/sp-theme.js';
import '@spectrum-web-components/theme/src/themes.js';

import '@spectrum-web-components/icons-workflow/icons/sp-icon-add.js';
import '@spectrum-web-components/icons-workflow/icons/sp-icon-delete.js';
import '@spectrum-web-components/icons-workflow/icons/sp-icon-key.js';
import '@spectrum-web-components/icons-workflow/icons/sp-icon-link-out-light.js';
import '@spectrum-web-components/badge/sp-badge.js';
import '@spectrum-web-components/button/sp-button.js';
import '@spectrum-web-components/button-group/sp-button-group.js';
import '@spectrum-web-components/card/sp-card.js';
import '@spectrum-web-components/checkbox/sp-checkbox.js';
import '@spectrum-web-components/combobox/sp-combobox.js';
import '@spectrum-web-components/divider/sp-divider.js';
import '@spectrum-web-components/field-group/sp-field-group.js';
import '@spectrum-web-components/field-label/sp-field-label.js';
import '@spectrum-web-components/action-button/sp-action-button.js';
import '@spectrum-web-components/help-text/sp-help-text.js';
import '@spectrum-web-components/infield-button/sp-infield-button.js';
import '@spectrum-web-components/link/sp-link.js';
import '@spectrum-web-components/tabs/sp-tab.js';
import '@spectrum-web-components/tabs/sp-tab-panel.js';
import '@spectrum-web-components/tabs/sp-tabs.js';
import '@spectrum-web-components/textfield/sp-textfield.js';
import '@spectrum-web-components/toast/sp-toast.js';
import '@spectrum-web-components/action-group/sp-action-group.js';
import '@spectrum-web-components/action-bar/sp-action-bar.js';
import '@spectrum-web-components/banner/sp-banner.js';
import '@spectrum-web-components/icon/sp-icon.js';
import '@spectrum-web-components/search/sp-search.js';
import '@spectrum-web-components/tags/sp-tags.js';
import '@spectrum-web-components/tags/sp-tag.js';
import '@spectrum-web-components/split-view/sp-split-view.js';
import '@spectrum-web-components/dialog/sp-dialog-wrapper.js';
import '@spectrum-web-components/menu/sp-menu.js';
import '@spectrum-web-components/menu/sp-menu-group.js';
import '@spectrum-web-components/menu/sp-menu-item.js';
import '@spectrum-web-components/menu/sp-menu-divider.js';
import '@spectrum-web-components/picker/sp-picker.js';
import '@spectrum-web-components/color-slider/sp-color-slider.js';
import '@spectrum-web-components/swatch/sp-swatch.js';
import '@spectrum-web-components/slider/sp-slider.js';
import '@spectrum-web-components/swatch/sp-swatch-group.js';
import '@spectrum-web-components/accordion/sp-accordion.js';
import '@spectrum-web-components/accordion/sp-accordion-item.js';
import '@spectrum-web-components/card/sp-card.js';


/**
 * Custom Web Components
 */
import './web-components/alert-banner.js';
import './web-components/overlay-picker.js';
import './web-components/color-slider.js';
import './web-components/color-steps.js';
import './web-components/languages-field.js';

/**
 * Alpine Components
 */
import './alpine-components/bible-brains-key-form.js';
import './alpine-components/usx-bibles-form.js';

loaded(() => {
    document.querySelectorAll('.tbp-cloak').forEach((el) => el.classList.remove('tbp-cloak'));
});

window.Alpine = Alpine
Alpine.start()
