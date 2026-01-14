/**
 * Represents the settings form for modifying the BibleBrain settings.
 */
import form from "./form.js";

window.br_bible_brains_key_form = (props) => {
    return {
        ...form(),
        fields: {
            bible_brains_key: "",
            google_tag_id: "",
        },
        dirty_bible_brains_key: "",
        dirty_google_tag_id: "",
        ...props,

        get verified() {
            return this.fields.bible_brains_key
                && (this.dirty_bible_brains_key === this.fields.bible_brains_key)
        },

        submission() {
            return {
                bible_brains_key: this.dirty_bible_brains_key,
                google_tag_id: this.dirty_google_tag_id
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
            if (this.fields.google_tag_id) {
                this.dirty_google_tag_id = this.fields.google_tag_id
            }
        },

        /**
         * Revert to the original value because the key verification failed
         *
         * @returns {void}
         */
        submissionFailed() {
            this.dirty_bible_brains_key = this.fields.bible_brains_key
            this.dirty_google_tag_id = this.fields.google_tag_id
        },
    }
}
