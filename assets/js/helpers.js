/**
 * AI Banking GRC Platform - Helper Functions
 * 
 * This file contains general helper functions
 */

'use strict';

// ============================================================
// STRING HELPERS
// ============================================================

/**
 * Truncate string
 */
function truncateString(str, maxLength, suffix = '...') {
    if (!str || str.length <= maxLength) return str;
    return str.substring(0, maxLength) + suffix;
}

/**
 * Capitalize first letter
 */
function capitalizeFirst(str) {
    if (!str) return str;
    return str.charAt(0).toUpperCase() + str.slice(1);
}

/**
 * Convert to slug
 */
function slugify(str) {
    return str
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

/**
 * Generate random string
 */
function randomString(length = 10) {
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * characters.length));
    }
    return result;
}

// ============================================================
// NUMBER HELPERS
// ============================================================

/**
 * Format number with commas
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * Format currency
 */
function formatCurrency(amount, currency = 'PKR') {
    const formatter = new Intl.NumberFormat('en-PK', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
    return formatter.format(amount);
}

/**
 * Format percentage
 */
function formatPercentage(value, decimals = 1) {
    return value.toFixed(decimals) + '%';
}

/**
 * Clamp number between min and max
 */
function clamp(num, min, max) {
    return Math.min(Math.max(num, min), max);
}

// ============================================================
// DATE HELPERS
// ============================================================

/**
 * Format date
 */
function formatDate(date, format = 'short') {
    const d = new Date(date);
    
    const formats = {
        short: { day: '2-digit', month: 'short', year: 'numeric' },
        long: { day: '2-digit', month: 'long', year: 'numeric' },
        full: { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' },
        time: { hour: '2-digit', minute: '2-digit' },
        datetime: { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }
    };
    
    return d.toLocaleDateString('en-PK', formats[format] || formats.short);
}

/**
 * Time ago (relative time)
 */
function timeAgo(date) {
    const now = new Date();
    const diff = Math.floor((now - new Date(date)) / 1000);
    
    const intervals = {
        year: 31536000,
        month: 2592000,
        week: 604800,
        day: 86400,
        hour: 3600,
        minute: 60
    };
    
    for (const [unit, seconds] of Object.entries(intervals)) {
        const count = Math.floor(diff / seconds);
        if (count >= 1) {
            return count + ' ' + unit + (count > 1 ? 's' : '') + ' ago';
        }
    }
    
    return 'Just now';
}

/**
 * Is today
 */
function isToday(date) {
    const today = new Date();
    const d = new Date(date);
    return d.getDate() === today.getDate() &&
           d.getMonth() === today.getMonth() &&
           d.getFullYear() === today.getFullYear();
}

/**
 * Is past due
 */
function isPastDue(date) {
    return new Date(date) < new Date();
}

// ============================================================
// ARRAY HELPERS
// ============================================================

/**
 * Group array by key
 */
function groupBy(array, key) {
    return array.reduce((result, item) => {
        const groupKey = typeof key === 'function' ? key(item) : item[key];
        if (!result[groupKey]) {
            result[groupKey] = [];
        }
        result[groupKey].push(item);
        return result;
    }, {});
}

/**
 * Sort array by key
 */
function sortBy(array, key, ascending = true) {
    return [...array].sort((a, b) => {
        let aVal = typeof key === 'function' ? key(a) : a[key];
        let bVal = typeof key === 'function' ? key(b) : b[key];
        
        if (typeof aVal === 'string') aVal = aVal.toLowerCase();
        if (typeof bVal === 'string') bVal = bVal.toLowerCase();
        
        if (aVal < bVal) return ascending ? -1 : 1;
        if (aVal > bVal) return ascending ? 1 : -1;
        return 0;
    });
}

/**
 * Unique array
 */
function unique(array) {
    return [...new Set(array)];
}

/**
 * Chunk array
 */
function chunk(array, size) {
    const chunks = [];
    for (let i = 0; i < array.length; i += size) {
        chunks.push(array.slice(i, i + size));
    }
    return chunks;
}

// ============================================================
// OBJECT HELPERS
// ============================================================

/**
 * Deep clone object
 */
function deepClone(obj) {
    return JSON.parse(JSON.stringify(obj));
}

/**
 * Pick properties from object
 */
function pick(obj, keys) {
    const result = {};
    keys.forEach(key => {
        if (obj && obj.hasOwnProperty(key)) {
            result[key] = obj[key];
        }
    });
    return result;
}

/**
 * Omit properties from object
 */
function omit(obj, keys) {
    const result = { ...obj };
    keys.forEach(key => {
        delete result[key];
    });
    return result;
}

// ============================================================
// DOM HELPERS
// ============================================================

/**
 * Get element by selector
 */
function $(selector, context = document) {
    return context.querySelector(selector);
}

/**
 * Get all elements by selector
 */
function $$(selector, context = document) {
    return context.querySelectorAll(selector);
}

/**
 * Create element
 */
function createElement(tag, className = '', content = '') {
    const el = document.createElement(tag);
    if (className) el.className = className;
    if (content) el.innerHTML = content;
    return el;
}

/**
 * Toggle element visibility
 */
function toggleVisibility(el) {
    if (el.style.display === 'none') {
        el.style.display = '';
    } else {
        el.style.display = 'none';
    }
}

/**
 * Add event listener with delegation
 */
function delegate(parent, selector, event, handler) {
    parent.addEventListener(event, function(e) {
        const target = e.target.closest(selector);
        if (target) {
            handler.call(target, e);
        }
    });
}

// ============================================================
// URL HELPERS
// ============================================================

/**
 * Get URL parameter
 */
function getUrlParam(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

/**
 * Set URL parameter
 */
function setUrlParam(name, value) {
    const url = new URL(window.location);
    url.searchParams.set(name, value);
    history.pushState({}, '', url);
}

/**
 * Remove URL parameter
 */
function removeUrlParam(name) {
    const url = new URL(window.location);
    url.searchParams.delete(name);
    history.pushState({}, '', url);
}

// ============================================================
// COOKIE HELPERS
// ============================================================

/**
 * Get cookie
 */
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

/**
 * Set cookie
 */
function setCookie(name, value, days = 7) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = `${name}=${value}; expires=${date.toUTCString()}; path=/; SameSite=Strict`;
}

/**
 * Delete cookie
 */
function deleteCookie(name) {
    document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
}

// ============================================================
// EXPOSE HELPERS GLOBALLY
// ============================================================

window.truncateString = truncateString;
window.capitalizeFirst = capitalizeFirst;
window.slugify = slugify;
window.randomString = randomString;
window.formatNumber = formatNumber;
window.formatCurrency = formatCurrency;
window.formatPercentage = formatPercentage;
window.clamp = clamp;
window.formatDate = formatDate;
window.timeAgo = timeAgo;
window.isToday = isToday;
window.isPastDue = isPastDue;
window.groupBy = groupBy;
window.sortBy = sortBy;
window.unique = unique;
window.chunk = chunk;
window.deepClone = deepClone;
window.pick = pick;
window.omit = omit;
window.$ = $;
window.$$ = $$;
window.createElement = createElement;
window.toggleVisibility = toggleVisibility;
window.delegate = delegate;
window.getUrlParam = getUrlParam;
window.setUrlParam = setUrlParam;
window.removeUrlParam = removeUrlParam;
window.getCookie = getCookie;
window.setCookie = setCookie;
window.deleteCookie = deleteCookie;