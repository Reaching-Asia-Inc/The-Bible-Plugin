import '../css/theme.css';
import "../css/plugin.css";
import "plyr/dist/plyr.css";

import '@spectrum-web-components/theme/sp-theme.js';
import '@spectrum-web-components/theme/src/themes.js';

import '@spectrum-web-components/action-bar/sp-action-bar.js';
import '@spectrum-web-components/action-button/sp-action-button.js';
import '@spectrum-web-components/action-group/sp-action-group.js';
import '@spectrum-web-components/field-group/sp-field-group.js';
import '@spectrum-web-components/field-label/sp-field-label.js';
import '@spectrum-web-components/action-menu/sp-action-menu.js';
import '@spectrum-web-components/banner/sp-banner.js';
import '@spectrum-web-components/dialog/sp-dialog.js';
import '@spectrum-web-components/dialog/sp-dialog-wrapper.js';
import '@spectrum-web-components/divider/sp-divider.js';
import '@spectrum-web-components/icon/sp-icon.js';
import '@spectrum-web-components/icons-ui/icons/sp-icon-arrow500.js';
import '@spectrum-web-components/icons-workflow/icons/sp-icon-play.js';
import '@spectrum-web-components/icons-workflow/icons/sp-icon-replay.js';
import '@spectrum-web-components/icons-workflow/icons/sp-icon-share.js';
import '@spectrum-web-components/menu/sp-menu.js';
import '@spectrum-web-components/menu/sp-menu-group.js';
import '@spectrum-web-components/menu/sp-menu-item.js';
import '@spectrum-web-components/menu/sp-menu-divider.js';
import '@spectrum-web-components/overlay/sp-overlay.js';
import '@spectrum-web-components/overlay/overlay-trigger.js';
import '@spectrum-web-components/popover/sp-popover.js';
import '@spectrum-web-components/progress-circle/sp-progress-circle.js';
import '@spectrum-web-components/textfield/sp-textfield.js';
import '@spectrum-web-components/top-nav/sp-top-nav.js';
import '@spectrum-web-components/top-nav/sp-top-nav-item.js';
import '@spectrum-web-components/toast/sp-toast.js';
import 'iconify-icon';

import "./web-components/content.js"

import "./web-components/dialog-wrapper.js"
import "./web-components/verse.js"
import "./web-components/audio.js"
import "./web-components/audio-bar.js"
import "./web-components/video.js"
import "./web-components/video-pane.js"
import "./web-components/reader.js"
import "./web-components/player.js"
import "./web-components/selection-button.js"
import "./web-components/selection-manager.js"
import "./web-components/share-button.js"
import "./web-components/bible.js"
import "./web-components/bible-menu.js"
import "./web-components/book-menu.js"
import "./web-components/chapter-nav.js"
import "./web-components/copyright.js"
import "./web-components/footer.js"
import "./web-components/header.js"


import {loaded} from "./helpers.js";

loaded(() => {
    document.querySelectorAll('.tbp-cloak').forEach((el) => el.classList.remove('tbp-cloak'));
});
