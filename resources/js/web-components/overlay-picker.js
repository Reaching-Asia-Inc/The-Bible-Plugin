import {css, html, LitElement} from "lit";
import uniqua from "uniqua";
import {customElement, property, query, state} from "lit/decorators.js";

@customElement('tbp-overlay-picker')

export class OverlayPicker extends LitElement {
    @property({type: Array, reflect: true, attribute: true}) options = [];
    @property({type: String, reflect: true, attribute: true}) optionsUrl = [];
    @property({type: String}) optionsValueKey = 'value'
    @property({type: String}) optionsLabelKey = 'itemText'
    @property({type: String, reflect: true, attribute: true}) label = ''
    @property({type: Boolean}) prefetch = false
    @property({type: Boolean}) searchFetch = true
    @property({type: String}) value = ''
    @property({type: String}) searchLabel = 'Search'
    @property({type: Boolean, attribute: true}) searchable = false
    @property({type: String, attribute: true}) selects = "single"

    @query('sp-dialog-wrapper') dialogWrapper

    @state() option_history = {}
    @state() selectedOptions = {}
    @state() values = []
    @state() search = ''

    /**
     * Returns an array of CSS styles including choices.js CSS
     *
     * @return {Array} The array of CSS styles.
     */
    static get styles() {
        return [css`
            sp-tag[deletable],
            sp-tag[deletable] * {
                cursor: pointer;
                box-sizing: border-box;
            }

            .multiselect {
                max-width: 400px;
                display: flex;
                flex-direction: column;
                gap: 10px;;
            }

            .multiselect__menu {
                max-height: 90vh;
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            sp-search {
                width: 100%;
            }

            sp-menu {
                overflow: hidden;
            }
        `];
    }

    init() {
        this.handleOptionChange()
        this.handleValueChange()
    }

    firstUpdated() {
        if (this.prefetch) {
            this.refreshOptions()
        }
    }

    /**
     * Updates the state of the component based on the changed properties.
     *
     * @param {Set} changedProperties - The set of properties that have changed.
     *
     * @return {void}
     */
    updated(changedProperties) {
        //Sync options with Choices
        if (changedProperties.has('options')) {
            this.handleOptionChange()
        }

        if (changedProperties.has('value')) {
            this.handleValueChange()
        }

        if (changedProperties.has('optionsUrl') && (this.has_updated || this.prefetch)) {
            this.refreshOptions()
        }

        this.has_updated = true
    }

    refreshSelectedOptions() {
        this.selectedOptions = this.values.map(value => this.option_history[value]).filter(option => !!option)
        this.requestUpdate()
    }

    handleOptionChange() {
        this.options.forEach(option => {
            this.option_history[option[this.optionsValueKey]] = option
        })
        this.refreshSelectedOptions()
        this.dispatchEvent(
            new Event('options', {
                bubbles: false,
                cancelable: true,
                composed: true
            })
        );
    }

    handleValueChange() {
        this.values = this.value.split(',')
        this.refreshSelectedOptions()
        const detail = {
            values: this.values,
            options: this.selectedOptions
        }
        this.dispatchEvent(
            new Event('input', {
                bubbles: false,
                cancelable: true,
                composed: true,
                detail
            })
        );
        this.dispatchEvent(
            new Event('change', {
                bubbles: false,
                cancelable: true,
                composed: true,
                detail
            })
        );
    }

    render() {
        return html`
            <div class="multiselect">
                <sp-action-group size="m" emphasized>
                    ${Object.values(this.selectedOptions).map((option) =>
                            html`
                                <sp-tag deletable @click="${() => this.removeValue(option[this.optionsValueKey])}">
                                    ${option[this.optionsLabelKey]}
                                </sp-tag>
                            `
                    )}
                    <sp-action-button @click="${this.searching = true}"
                                      id="search">
                        <sp-icon-magnify slot="icon"></sp-icon-magnify>
                    </sp-action-button>
                </sp-action-group>

                <sp-overlay trigger="search@click" type="modal">
                    <sp-dialog-wrapper
                            dismissable
                            underlay
                            headline="${this.label}"
                    >
                        <div class="multiselect__menu">
                            ${this.searchable ? html`
                                <sp-search quiet value="${this.search}"
                                           @input="${this.handleSearch}"></sp-search>` : ''}

                            <sp-menu
                                    selects="${this.selects}"
                            >
                                ${this.options.map((option) =>
                                        html`
                                            <sp-menu-item value="${option[this.optionsValueKey]}"
                                                          @click="${this.handleOptionClick}"
                                                          ?selected=${this.isValueSelected(option[this.optionsValueKey])}>
                                                ${option[this.optionsLabelKey]}
                                            </sp-menu-item>
                                        `
                                )}
                            </sp-menu>

                        </div>

                    </sp-dialog-wrapper>
                </sp-overlay>
            </div>
        `;
    }

    handleOptionClick(e) {
        if (this.isValueSelected(e.target.value)) {
            this.removeValue(e.target.value)
            e.target.selected = false
        } else {
            this.addValue(e.target.value)
            e.target.selected = true
        }
    }

    addValue(value) {
        if (this.selects === 'single') {
            this.value = value
            this.dialogWrapper.dialog.close()
        } else {
            this.value = uniqua(this.values.concat(value)).join(',')
        }
        this.requestUpdate()
    }

    isValueSelected(value) {
        return this.value.includes(value)
    }

    async handleSearch(e) {
        if (!this.searchFetch || !this.optionsUrl || !e.target.value) {
            return;
        }
        try {
            const result = await this.fetchOptions(e.target.value)
            const {data: options} = await result.json()
            if (options) {
                this.options = options
            }
        } catch (error) {
            console.error(error)
        }
    }

    removeValue(value) {
        this.value = this.values.filter(v => v !== value).join(',')
    }

    /**
     * Refreshes the options by fetching data and updating the options property.
     *
     * @async
     * @return {void}
     */
    async refreshOptions() {
        try {
            const result = await this.fetchOptions()
            const {data: options} = await result.json()
            if (options) {
                this.options = options
            }
        } catch (error) {
            console.error(error)
        }

    }

    /**
     * Performs a search by sending a GET request to the options URL with the specified search searchTerm.
     *
     * @param {string} searchTerm - The search searchTerm.
     * @return {Promise} - A Promise that resolves with the JSON response from the server.
     */
    fetchOptions(searchTerm = '') {
        if (!this.optionsUrl) {
            return;
        }

        if (this.abortController) {
            this.abortController.abort("Fetching new data")
        }
        this.abortController = new AbortController()

        const {signal} = this.abortController;
        let url = this.optionsUrl
        if (!url.includes('?')) {
            url += '?'
        }

        return fetch(`${url}&search=${searchTerm}`, {
            signal,
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': $tbp.nonce
            }
        })
    }

    getSelectedOptions() {
        return this.selectedOptions
    }
}
