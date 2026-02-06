/**
 * Represents the settings form for modifying the BibleBrain settings.
 */
import form from "./form.js";

window.br_bible_brains_key_form = (props) => {
    return {
        ...form(),
        fields: {
            bible_brains_key: "",
        },
        dirty_bible_brains_key: "",
        action_delete: "",
        ...props,

        get verified() {
            return this.fields.bible_brains_key
                && (this.dirty_bible_brains_key === this.fields.bible_brains_key)
        },

        submission() {
            return {
                bible_brains_key: this.dirty_bible_brains_key,
            }
        },

        /**
         * Initializes the method with necessary watches and bindings
         *
         * @method init
         *
         * @returns {void}
         */
        initialized() {
            if (this.fields.bible_brains_key) {
                this.dirty_bible_brains_key = this.fields.bible_brains_key
            }
        },

        /**
         * Revert to the original value because the key verification failed
         *
         * @returns {void}
         */
        submissionFailed() {
            this.dirty_bible_brains_key = this.fields.bible_brains_key
        },

        /**
         * Remove the stored key via API and reset local state
         */
        async removeKey(e = null) {
            if (this.submitting) { return }
            if (e) { e.preventDefault(); e.stopPropagation(); }

            this.reset_alerts()
            this.submitting = true
            try {
                const { response, data } = await this.request(this.action_delete, {}, { method: 'POST' })
                this.submitting = false
                if (response.status === 200 && (data.success === undefined ? true : !!data.success)) {
                    this.fields.bible_brains_key = ""
                    this.dirty_bible_brains_key = ""
                    this.success = this.success_message || true
                    this.toggleAlerts()
                    if (this.refresh) {
                        window.location.reload()
                    }
                } else {
                    this.handleSubmissionFailed(data && data.message ? data.message : undefined)
                }
            } catch (e2) {
                this.submitting = false
                this.handleSubmissionFailed()
            }
        },
    }
}
