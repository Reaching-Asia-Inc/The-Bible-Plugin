import hasArrayable from "./mixins/has-arrayable.js";

/**
 * Alpine component or mixin for handling form validation and submission.
 *
 */
export const form = (props = {}) => {
    return {
        ...hasArrayable,
        success: false,
        error: false,
        submitting: false,
        action: "",
        refresh: false,
        redirect: "",
        nonce: null,
        ...props,

        /**
         * Initializes the method with necessary watches and bindings
         *
         * @method init
         *
         * @returns {void}
         */
        init() {
            this.toggleAlerts()
            this.initialized()
        },

        toggleAlerts() {
            this.$refs.success_alert.open = !!this.success
            this.$refs.error_alert.open = !!this.error
        },

        /**
         * Resets the alerts by setting error, message, success_alert.open, and error_alert.open to false.
         *
         * @return {void}
         */
        reset_alerts() {
            this.error = false
            this.success = false
            this.$refs.success_alert.open = false
            this.$refs.error_alert.open = false
        },

        /**
         * Submits the data to the server.
         *
         * @async
         * @return {void}
         */
        async submit(e = null) {
            if (this.submitting) {
                return
            }
            if (e) {
                e.preventDefault()
                e.stopPropagation()
            }

            this.reset_alerts()
            this.submitting = true
            try {
                this.beforeSubmission()

                const payload = this.submission()
                const { response, data } = await this.request(this.action, payload, { method: 'POST' })

                this.afterSubmission()

                if (response.status === 200) {
                    this.handleSubmissionSuccess(data.success === undefined ? true : !!data.success)
                    return;
                } else if (data && data.message) {
                    this.handleSubmissionFailed(data.message)
                    return;
                }

                this.handleSubmissionFailed()

            } catch (e2) {
                this.handleSubmissionFailed()
            }
        },

        /**
         * Retrieves the submission of the form.
         *
         * @function
         * @returns {object} - The submission object containing the form fields.
         */
        submission() {
            return this.fields
        },

        /**
         * Generic request helper used by submit() and components.
         * Automatically sets headers and handles JSON vs FormData payloads.
         * Exposes parsed response on this.response.
         */
        async request(url, payload = null, opts = {}) {
            const method = opts.method || 'POST'
            const isFormData = (typeof FormData !== 'undefined') && (payload instanceof FormData)

            // Resolve a valid REST nonce from multiple reliable sources (core first)
            const resolveRestNonce = () => {
              if (this.nonce) {
                return this.nonce
              }
              if (window.wpApiSettings && window.wpApiSettings.nonce) {
                return window.wpApiSettings.nonce
              }
              if (window.$tbp && $tbp.nonce) {
                return $tbp.nonce
              }
              const meta = document && document.querySelector ? document.querySelector('meta[name="wp-rest-nonce"]') : null
              if (meta && meta.content) {
                return meta.content
              }
                return ''
            }

            const headers = {
                'X-WP-Nonce': resolveRestNonce()
            }
            if (!isFormData) {
                headers['Content-Type'] = 'application/json'
                headers['Accept'] = 'application/json, text/plain, */*'
            }
            const options = {
                method,
                headers,
                // Include credentials to ensure auth cookies are sent for REST
                credentials: 'include'
            }
            if (payload !== null) {
                options.body = isFormData ? payload : JSON.stringify(payload)
            }

            const response = await fetch(url || this.action, options)

            // Try parse JSON, otherwise fallback to text
            const ct = response.headers.get('content-type') || ''
            let data = {}
            if (ct.includes('application/json')) {
                data = await response.json().catch(() => ({}))
            } else {
                const text = await response.text().catch(() => '')
                data = text ? { message: text } : {}
            }

            // expose last response for hooks
            this.response = data

            return { response, data }
        },


        handleSubmissionSuccess(success = true) {
            this.success = success
            this.error = false
            this.submitting = false
            this.toggleAlerts()
            this.submissionSucceeded()
            if (this.redirect) {
                setTimeout(() => window.location.href = this.redirect, 1000)
            } else if (this.refresh) {
                setTimeout(() => location.reload(), 1000)
            }
        },

        /**
         * Set the necessary state variables when a submission fails.
         *
         * @function handleSubmissionFailed*
         * @returns {void}
         */
        handleSubmissionFailed(error = true) {
            this.success = false
            this.error = error
            this.submitting = false
            this.toggleAlerts()
            this.submissionFailed()
        },

        /**
         * The component is initialized.
         *
         * @function initialized
         * @memberof module:MyModule
         * @returns {void}
         */
        initialized() {

        },

        /**
         * Performs necessary tasks before submitting the document.
         *
         * @function beforeSubmission
         * @description This method is responsible for executing the required actions before submitting the document.
         *              It can be used to perform any necessary pre-submission checks or preparations.
         *
         * @returns {undefined} This method does not return any value.
         *
         */
        beforeSubmission() {

        },

        /**
         * Override this method in the component to handle su
         * mission failure.
         *
         * @returns {void}
         */
        submissionFailed() {
        },

        /**
         * Override this method in the component to handle submission success.
         *
         * @returns {void}
         */
        submissionSucceeded() {
        },

        /**
         * Performs necessary actions after submission.
         * This method can be implemented to handle any post-submission logic, such as
         * saving the data, sending notifications, or performing additional processing.
         *
         * @function afterSubmission
         * @returns {void}
         */
        afterSubmission() {

        }

    }
}

window.br_form = form

export default form
