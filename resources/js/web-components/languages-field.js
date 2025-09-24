import {customElement, property, query} from "lit/decorators.js";
import {css, html, nothing, SpectrumElement} from "@spectrum-web-components/base";
import {TBPElement} from "./base.js";
import {__, apiUrl} from "../helpers.js";

@customElement('tbp-languages-field')
export class LanguagesField extends TBPElement {
    @property({type: Array, reflect: true}) value = [];
    @property({type: Array, attribute: 'media-type-options'}) mediaTypeOptions = [];

    get hasUnselectedLanguage() {
        return this.value.some(({value}) => !value);
    }

    get hasDefaultLanguage() {
        return this.value.some(({is_default}) => is_default);
    }

    static get styles() {
        return [
            css`
                :host {
                    display: block;
                    width: 100%;
                    margin-bottom: var(--spectrum-global-dimension-size-400);
                }

                #languages {
                    margin: var(--spectrum-global-dimension-size-200) 0;
                    display: flex;
                    flex-direction: column;
                    gap: var(--spectrum-global-dimension-size-100);
                    width: 100%;
                    max-width: 600px;
                }

                sp-field-group {
                    margin-bottom: var(--spectrum-global-dimension-size-100);
                    display: block;
                }

                sp-help-text {
                    display: block;
                    width: 100%;
                }

              #shortcodes {
                font-size: 10px;
                background-color: var(--spectrum-global-color-gray-200);
                padding: 10px;
              }
            `]
    }

    render() {
        return html`
            ${this.renderLanguages()}
            ${this.renderAddButton()}
        `;
    }

    renderLanguages() {
        return html`
            <div id="languages">
                ${this.value.map(((language, idx) => this.renderLanguage(language, idx)))}
            </div>
        `
    }

    renderLanguage({bibles = [], media_types = '', itemText = '', value = '', is_default = false, default_bible = ''}, idx) {
        // Ensure bibles is an array for rendering
        const bibleValues = Array.isArray(bibles) ? bibles : (typeof bibles === 'string' && bibles ? bibles.split(',') : []);
        const exampleBible = default_bible || (bibleValues[0] || '');

        return html`
            <sp-card heading="${itemText ? itemText : __('Language')}" class="language">
                <div slot="footer">

                    ${(!value) ? html`
                        <sp-field-group>
                            <tbp-overlay-picker placeholder="${__('Search') + '...'}"
                                                label="${__('Languages')}"
                                                searchLabel="${__('Search')}"
                                                value="${value}"
                                                @change="${(e) => this.onLanguageChange(e, idx)}"
                                                optionsUrl="${apiUrl('languages/options')}"
                                                ?prefetch
                            >
                            </tbp-overlay-picker>

                            <sp-help-text size="s">
                                ${__('Select the bible language you would like to make available.')}
                            </sp-help-text>
                        </sp-field-group>
                    ` : nothing}

                    ${value ? html`
                        <sp-field-group>
                            <sp-field-label
                                    required
                                    for="${`languages_${idx}_bibles`}">
                                ${__('Bible Versions')}
                            </sp-field-label>

                            <tbp-overlay-picker id="${`languages_${idx}_bibles`}"
                                                placeholder="${__('Search') + '...'}"
                                                label="${__('Translations')}"
                                                searchLabel="${__('Search')}"
                                                value="${bibleValues.join(',')}"
                                                @change="${(e) => this.onBiblesChange(e, idx)}"
                                                optionsUrl="${apiUrl('bibles/options', {language: value})}"
                                                selects="multiple"
                                                ?required
                                                ?searchable
                                                ?prefetch
                            >
                            </tbp-overlay-picker>

                            <sp-help-text size="s">
                                ${__('Select one or more bible versions you would like to make available for this language.')}
                            </sp-help-text>
                        </sp-field-group>

                        <sp-field-group>
                            <sp-field-label
                                    required
                                    for="${`languages_${idx}_media_types`}"
                            >

                                ${__('Media Types')}
                            </sp-field-label>

                            <sp-field-group horizontal id="${`languages_${idx}_media_types`}">
                                ${this.mediaTypeOptions.map(({value, itemText}) => html`
                                    <sp-checkbox value="${value}"
                                                 @change="${(e) => this.onMediaTypeChange(e, idx)}"
                                                 ?checked="${this.value[idx].media_types.split(',').includes(value)}">
                                        ${itemText}
                                    </sp-checkbox>
                                `)}
                                <sp-help-text size="s">
                                    ${__('Note that some bible versions do not support all media types.')}
                                </sp-help-text>
                            </sp-field-group>
                        </sp-field-group>

                        ${!is_default && this.hasDefaultLanguage ? nothing : html`
                            <sp-field-group>
                                <sp-field-label
                                        for="${`languages_${idx}_is_default`}"
                                >

                                    ${__('Default Language?')}
                                </sp-field-label>

                                <sp-field-group>
                                    <sp-checkbox id=${`languages_${idx}_is_default`}
                                                 value="${is_default}"
                                                 @change="${(e) => this.onDefaultChange(e, idx)}"
                                                 ?checked="${is_default}">
                                    </sp-checkbox>
                                    <sp-help-text size="s">
                                        ${__('Make this the default language.')}
                                    </sp-help-text>
                                </sp-field-group>
                            </sp-field-group>
                        `}

                        ${is_default ? html`
                            <sp-field-group>
                                <sp-field-label
                                        required
                                        for="${`languages_${idx}_default_bible`}"
                                >
                                    ${__('Default Bible')}
                                </sp-field-label>

                                <tbp-overlay-picker id="${`languages_${idx}_default_bible`}"
                                                    placeholder="${__('Search') + '...'}"
                                                    label="${__('Default Bible')}"
                                                    searchLabel="${__('Search')}"
                                                    value="${default_bible}"
                                                    @change="${(e) => this.onDefaultBibleChange(e, idx)}"
                                                    optionsUrl="${apiUrl('bibles/options', {language: value})}"
                                                    ?required
                                                    ?searchable
                                                    ?prefetch
                                >
                                </tbp-overlay-picker>

                                <sp-help-text size="s">
                                    ${__('Choose which bible should be used by default for this language.')}
                                </sp-help-text>
                            </sp-field-group>
                        ` : nothing}
                    ` : nothing}
                  </sp-field-group>

                  <sp-field-group>
                    <sp-field-label
                      for="shortcodes"
                    >
                      ${__('Example Shortcode')}
                    </sp-field-label>
                    <pre id="shortcodes">[tbp-scripture reference="John 3:16" language="${value}" bible="${exampleBible}"]</pre>
                  </sp-field-group>
                </div>


                <sp-button slot="actions" lass="language__remove"
                           treatment="fill"
                           variant="negative"
                           label="${`Remove ` + itemText}"
                           size="s"
                           @click="${() => this.onRemove(idx)}"
                           icon-only>
                    <sp-icon-delete slot="icon"></sp-icon-delete>
                </sp-button>
            </sp-card>
        `;
    }

    onLanguageChange(e, idx) {
        const selectedOptions = e.target.getSelectedOptions();
        this.value[idx].value = e.target.value;
        this.value[idx].itemText = selectedOptions.length ? selectedOptions[0].itemText : '';
        this.requestUpdate();
    }

    onBiblesChange(e, idx) {
        const incoming = e.target.value;
        const list = Array.isArray(incoming)
            ? incoming
            : (typeof incoming === 'string' && incoming.length ? incoming.split(',') : []);
        // unique and non-empty
        const clean = [...new Set(list.filter(Boolean))];
        this.value[idx].bibles = clean;

        // If language is default and default_bible is not set, pick the first selected
        if (this.value[idx].is_default && !this.value[idx].default_bible) {
            this.value[idx].default_bible = clean[0] || '';
        }
        this.requestUpdate();
    }

    onDefaultChange(e, idx) {
        this.value[idx].is_default = e.target.checked;
        // If became default and we have bibles but no default_bible, set it
        if (this.value[idx].is_default && (!this.value[idx].default_bible)) {
            const b = this.value[idx].bibles;
            const list = Array.isArray(b) ? b : (typeof b === 'string' && b ? b.split(',') : []);
            this.value[idx].default_bible = list[0] || '';
        }
        this.requestUpdate();
    }

    onDefaultBibleChange(e, idx) {
        this.value[idx].default_bible = e.target.value || '';
        this.requestUpdate();
    }

    onRemove(idx) {
        this.value.splice(idx, 1);
        this.requestUpdate()
    }

    onMediaTypeChange(e, idx) {
        if (!e.target.getAttribute('value')) {
            return;
        }
        let mediaTypes = this.value[idx].media_types
            .split(',')
            .filter((value, index, self) => !!value);
        if (e.target.checked) {
            mediaTypes.push(e.target.getAttribute('value'));
        } else {
            mediaTypes.splice(mediaTypes.indexOf(e.target.getAttribute('value')), 1);
        }
        mediaTypes = mediaTypes.filter((value, index, self) => !!value);
        this.value[idx].media_types = mediaTypes.join(',');
    }

    renderAddButton() {
        if (this.hasUnselectedLanguage) return nothing;
        return html`
            <sp-button variant="secondary" label="Add" @click="${this.onAddClick}">
                Add Language
                <sp-icon-add slot="icon"></sp-icon-add>
            </sp-button>
        `;
    }

    onAddClick() {
        this.value = [...this.value, {
            bibles: [],
            media_types: this.mediaTypeOptions.map(({value}) => value).join(','),
            itemText: 'Language',
            value: '',
            is_default: this.value.length === 0,
            default_bible: ''
        }];
    }
}
