/**
 * Alpine component for Custom USX Bibles settings
 * Exposes: window.usxBiblesSettings(initialProps)
 */
import form from "./form.js";

window.usxBiblesFormHandler = function(initial = {}) {
    return {
        ...form,
        bibles: initial.bibles || {},
        nonce: initial.nonce || '',
        actionBase: initial.actionBase || '',
        messages: {
            upload_success: 'Upload successful.',
            error_upload: 'Error uploading.',
            saved_success: 'Saved successfully.',
            error_saving: 'Error saving.',
            delete_confirm: 'Are you sure you want to delete this bible?',
            deleted_success: 'Deleted successfully.',
            error_deleting: 'Error deleting.',
        },

        /**
         * Initializes the method with necessary watches and bindings
         *
         * @method init
         *
         * @returns {void}
         */
        init() {

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

        async postFormData(url, formData) {
            const res = await fetch(url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json, text/plain, */*',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const ct = res.headers.get('content-type') || '';
            let data = null;
            if (ct.includes('application/json')) {
                try { data = await res.json(); } catch (e) {}
            } else {
                const text = await res.text();
                throw {
                    success: false,
                    status: res.status,
                    message: (text || '').replace(/<[^>]+>/g, '').trim().slice(0, 500) || 'Unexpected non-JSON response'
                };
            }
            if (!res.ok || !data || !data.success) {
                throw data || { success: false, status: res.status, message: 'Request failed' };
            }
            return data;
        },

        async postJSON(url, payload) {
            const params = new URLSearchParams({ ...(payload || {}), _wpnonce: this.nonce });
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'Accept': 'application/json, text/plain, */*',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: params.toString(),
                credentials: 'same-origin'
            });
            const ct = res.headers.get('content-type') || '';
            let data = null;
            if (ct.includes('application/json')) {
                try { data = await res.json(); } catch (e) {}
            } else {
                const text = await res.text();
                throw {
                    success: false,
                    status: res.status,
                    message: (text || '').replace(/<[^>]+>/g, '').trim().slice(0, 500) || 'Unexpected non-JSON response'
                };
            }
            if (!res.ok || !data || !data.success) {
                throw data || { success: false, status: res.status, message: 'Request failed' };
            }
            return data;
        },

        refreshTable(bibles) {
            this.bibles = bibles || {};
        },

        async uploadBible(e) {
            const formEl = this.$refs.uploadForm;
            const url = formEl.getAttribute('action');
            const formData = new FormData(formEl);

            try {
                const data = await this.postFormData(url, formData);
                this.refreshTable(data.bibles);
                this.showNotice(data.message || this.messages.upload_success, 'success');
                formEl.reset();
            } catch (err) {
                const errorMessage = (err && (err.message || (err.errors && Object.values(err.errors).join(', ')))) || this.messages.error_upload;
                this.showNotice(errorMessage, 'error');
            }
        },

        async saveBible(id) {
            const url = this.actionBase + '&tab=usx_bibles_update';
            try {
                const data = await this.postJSON(url, { id, language_id: (this.bibles[id]?.language_id || '') });
                this.refreshTable(data.bibles);
                if (data.message) {
                    this.showNotice(data.message, 'success');
                } else {
                    this.showNotice(this.messages.saved_success, 'success');
                }
            } catch (err) {
                const errorMessage = (err && (err.message || (err.errors && Object.values(err.errors).join(', ')))) || this.messages.error_saving;
                this.showNotice(errorMessage, 'error');
            }
        },

        async deleteBible(id) {
            if (!confirm(this.messages.delete_confirm)) {
                return;
            }
            const url = this.actionBase + '&tab=usx_bibles_delete';
            try {
                const data = await this.postJSON(url, { id });
                this.refreshTable(data.bibles);
                if (data.message) {
                    this.showNotice(data.message, 'success');
                } else {
                    this.showNotice(this.messages.deleted_success, 'success');
                }
            } catch (err) {
                const errorMessage = (err && (err.message || (err.errors && Object.values(err.errors).join(', ')))) || this.messages.error_deleting;
                this.showNotice(errorMessage, 'error');
            }
        }
    }
}
