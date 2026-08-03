/**
 * AI Banking GRC Platform - AJAX Handling
 * 
 * This file contains AJAX utility functions
 */

'use strict';

// ============================================================
// AJAX CONFIG
// ============================================================

const AJAX_CONFIG = {
    baseURL: '',
    timeout: 30000,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
    }
};

// ============================================================
// AJAX CLASS
// ============================================================

class Ajax {
    constructor(config = {}) {
        this.config = { ...AJAX_CONFIG, ...config };
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    }

    /**
     * GET request
     */
    get(url, data = null) {
        return this.request('GET', url, data);
    }

    /**
     * POST request
     */
    post(url, data = null) {
        return this.request('POST', url, data);
    }

    /**
     * PUT request
     */
    put(url, data = null) {
        return this.request('PUT', url, data);
    }

    /**
     * DELETE request
     */
    delete(url, data = null) {
        return this.request('DELETE', url, data);
    }

    /**
     * PATCH request
     */
    patch(url, data = null) {
        return this.request('PATCH', url, data);
    }

    /**
     * Make HTTP request
     */
    request(method, url, data = null) {
        const fullUrl = this.config.baseURL + url;
        const options = {
            method: method,
            headers: {
                ...this.config.headers,
                'X-CSRF-TOKEN': this.csrfToken || ''
            }
        };

        if (data) {
            if (data instanceof FormData) {
                options.body = data;
            } else {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(data);
            }
        }

        // Add timeout
        const controller = new AbortController();
        options.signal = controller.signal;
        const timeoutId = setTimeout(() => controller.abort(), this.config.timeout);

        return fetch(fullUrl, options)
            .then(response => {
                clearTimeout(timeoutId);
                return this.handleResponse(response);
            })
            .catch(error => {
                clearTimeout(timeoutId);
                if (error.name === 'AbortError') {
                    throw new Error('Request timeout');
                }
                throw error;
            });
    }

    /**
     * Handle response
     */
    async handleResponse(response) {
        const contentType = response.headers.get('Content-Type') || '';
        const isJson = contentType.includes('application/json');

        if (!response.ok) {
            let errorMessage = `HTTP Error ${response.status}`;
            let errorData = null;

            if (isJson) {
                try {
                    errorData = await response.json();
                    errorMessage = errorData.message || errorMessage;
                } catch (e) {
                    // Use default error message
                }
            }

            const error = new Error(errorMessage);
            error.status = response.status;
            error.data = errorData;
            throw error;
        }

        if (isJson) {
            return response.json();
        }

        return response.text();
    }

    /**
     * Upload file
     */
    upload(url, file, onProgress = null) {
        const formData = new FormData();
        formData.append('file', file);

        return this.post(url, formData, {
            onUploadProgress: onProgress
        });
    }

    /**
     * Download file
     */
    download(url, filename = null) {
        return fetch(this.config.baseURL + url, {
            headers: {
                'X-CSRF-TOKEN': this.csrfToken || ''
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Download failed: ${response.status}`);
            }
            return response.blob();
        })
        .then(blob => {
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename || 'download';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        });
    }
}

// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Create Ajax instance
 */
function createAjax(config = {}) {
    return new Ajax(config);
}

/**
 * Quick GET request
 */
function ajaxGet(url, data = null) {
    return createAjax().get(url, data);
}

/**
 * Quick POST request
 */
function ajaxPost(url, data = null) {
    return createAjax().post(url, data);
}

/**
 * Quick PUT request
 */
function ajaxPut(url, data = null) {
    return createAjax().put(url, data);
}

/**
 * Quick DELETE request
 */
function ajaxDelete(url, data = null) {
    return createAjax().delete(url, data);
}

/**
 * Quick PATCH request
 */
function ajaxPatch(url, data = null) {
    return createAjax().patch(url, data);
}

// ============================================================
// EXPOSE FUNCTIONS GLOBALLY
// ============================================================

window.Ajax = Ajax;
window.createAjax = createAjax;
window.ajaxGet = ajaxGet;
window.ajaxPost = ajaxPost;
window.ajaxPut = ajaxPut;
window.ajaxDelete = ajaxDelete;
window.ajaxPatch = ajaxPatch;