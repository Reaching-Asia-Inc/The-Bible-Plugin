/**
 * Alpine component for Custom Paratext Bibles settings
 * Exposes: window.customBiblesFormHandler(initialProps)
 */
import form from "./form.js";

window.br_local_bibles_form = function(initial = {}) {
    return {
        ...form(),
        bibles: initial.bibles || {},
        nonce: initial.nonce || '',
        actionBase: initial.actionBase || '', // legacy; no longer used for REST
        restBase: initial.restBase || (window.$tbp && $tbp.apiUrl ? $tbp.apiUrl : ''),
        notice: { message: '', type: 'info' },
        messages: {
            upload_success: 'Upload successful.',
            error_upload: 'Error uploading.',
            saved_success: 'Saved successfully.',
            error_saving: 'Error saving.',
            delete_confirm: 'Are you sure you want to delete this bible?',
            deleted_success: 'Deleted successfully.',
            error_deleting: 'Error deleting.',
        },

        // Safe alert toggling in case refs are not present in the template
        toggleAlerts() {
            if (this.$refs && this.$refs.success_alert) {
                this.$refs.success_alert.open = !!this.success;
            }
            if (this.$refs && this.$refs.error_alert) {
                this.$refs.error_alert.open = !!this.error;
            }
        },

        refreshTable(bibles) {
            this.bibles = bibles || {};
        },

        // Build FormData payload for upload using the shared submit() from form.js
        submission() {
            const formEl = this.$refs && this.$refs.uploadForm ? this.$refs.uploadForm : null;
            if (!formEl) {
                return new FormData();
            }
            // Validate file type client-side (zip only)
            const fileInput = formEl.querySelector('input[type="file"]');
            if (fileInput && fileInput.files && fileInput.files[0]) {
                const name = fileInput.files[0].name || '';
                if (!/\.zip$/i.test(name)) {
                    // Set a response-like object so submissionFailed can show a message
                    this.response = { message: 'Invalid file type. Please upload a ZIP file.' };
                    return new FormData();
                }
            }
            return new FormData(formEl);
        },

        // Configure the REST upload endpoint once the component initializes (form.init calls initialized())
        initialized() {
            const base = (this.restBase || '').replace(/\/$/, '');
            this.action = base ? (base + '/local-bibles/upload') : (this.action || '');
        },

        // Post-submit hooks from form.js (used by other actions and upload)
        submissionSucceeded() {
            if (this.response && this.response.bibles) {
                this.refreshTable(this.response.bibles);
            }
            if (this.$refs && this.$refs.uploadForm) {
                try { this.$refs.uploadForm.reset(); } catch (e) {}
            }
            // Prefer built-in alerts from form.js
            if (this.response && this.response.message) {
                this.toggleAlerts();
            }
        },

        submissionFailed() {
            if (this.response && this.response.message) {
                this.error = this.response.message;
                this.toggleAlerts();
            }
        },

        async saveBible(id) {
            const base = (this.restBase || '').replace(/\/$/, '');
            const url = base + '/local-bibles/update';
            this.reset_alerts();
            this.submitting = true;
            try {
                const { response, data } = await this.request(url, { id, language_id: (this.bibles[id]?.language_id || '') }, { method: 'POST' });
                if (response.ok) {
                    if (data && data.bibles) {
                        this.refreshTable(data.bibles);
                    }
                    this.handleSubmissionSuccess(data && data.message ? data.message : this.messages.saved_success);
                } else {
                    this.handleSubmissionFailed(data && data.message ? data.message : this.messages.error_saving);
                }
            } catch (err) {
                const errorMessage = (err && (err.message || (err.errors && Object.values(err.errors).join(', ')))) || this.messages.error_saving;
                this.handleSubmissionFailed(errorMessage);
            }
        },

        async deleteBible(id) {
            if (!confirm(this.messages.delete_confirm)) {
                return;
            }
            const base = (this.restBase || '').replace(/\/$/, '');
            const url = base + '/local-bibles/delete';
            this.reset_alerts();
            this.submitting = true;
            try {
                const { response, data } = await this.request(url, { id }, { method: 'POST' });
                if (response.ok) {
                    if (data && data.bibles) {
                        this.refreshTable(data.bibles);
                    }
                    this.handleSubmissionSuccess(data && data.message ? data.message : this.messages.deleted_success);
                } else {
                    this.handleSubmissionFailed(data && data.message ? data.message : this.messages.error_deleting);
                }
            } catch (err) {
                const errorMessage = (err && (err.message || (err.errors && Object.values(err.errors).join(', ')))) || this.messages.error_deleting;
                this.handleSubmissionFailed(errorMessage);
            }
        }
    }
}
