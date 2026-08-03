/**
 * AI Banking GRC Platform - Chart Utilities
 * 
 * This file contains Chart.js utilities and helper functions
 */

'use strict';

// ============================================================
// CHART COLORS
// ============================================================

const CHART_COLORS = {
    primary: '#2563EB',
    secondary: '#0B3D91',
    success: '#22C55E',
    warning: '#F59E0B',
    danger: '#EF4444',
    info: '#3B82F6',
    purple: '#8B5CF6',
    pink: '#EC4899',
    gray: '#64748B',
    lightGray: '#E2E8F0'
};

const CHART_PALETTES = {
    main: ['#2563EB', '#F59E0B', '#EF4444', '#22C55E', '#8B5CF6'],
    risk: ['#22C55E', '#F59E0B', '#F97316', '#EF4444', '#DC2626'],
    status: ['#22C55E', '#F59E0B', '#EF4444', '#3B82F6'],
    monochrome: ['#2563EB', '#3B82F6', '#60A5FA', '#93C5FD', '#DBEAFE']
};

// ============================================================
// CHART DEFAULT OPTIONS
// ============================================================

const CHART_DEFAULTS = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                usePointStyle: true,
                boxWidth: 8,
                padding: 20
            }
        },
        tooltip: {
            backgroundColor: '#FFFFFF',
            titleColor: '#1E293B',
            bodyColor: '#64748B',
            borderColor: '#E2E8F0',
            borderWidth: 1,
            padding: 12,
            cornerRadius: 8
        }
    },
    animation: {
        duration: 800,
        easing: 'easeOutQuart'
    }
};

// ============================================================
// CHART UTILITY FUNCTIONS
// ============================================================

/**
 * Create a chart
 */
function createChart(canvasId, config) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return null;
    
    const ctx = canvas.getContext('2d');
    const mergedConfig = mergeDeep(CHART_DEFAULTS, config);
    return new Chart(ctx, mergedConfig);
}

/**
 * Create a line chart
 */
function createLineChart(canvasId, labels, datasets, options = {}) {
    const config = {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            ...CHART_DEFAULTS,
            ...options,
            plugins: {
                ...CHART_DEFAULTS.plugins,
                ...(options.plugins || {})
            }
        }
    };
    return createChart(canvasId, config);
}

/**
 * Create a bar chart
 */
function createBarChart(canvasId, labels, datasets, options = {}) {
    const config = {
        type: 'bar',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            ...CHART_DEFAULTS,
            ...options,
            plugins: {
                ...CHART_DEFAULTS.plugins,
                ...(options.plugins || {})
            }
        }
    };
    return createChart(canvasId, config);
}

/**
 * Create a pie chart
 */
function createPieChart(canvasId, labels, data, colors = null) {
    const config = {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors || CHART_PALETTES.main,
                borderWidth: 0
            }]
        },
        options: {
            ...CHART_DEFAULTS,
            plugins: {
                ...CHART_DEFAULTS.plugins
            }
        }
    };
    return createChart(canvasId, config);
}

/**
 * Create a doughnut chart
 */
function createDoughnutChart(canvasId, labels, data, colors = null) {
    const config = {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors || CHART_PALETTES.main,
                borderWidth: 0
            }]
        },
        options: {
            ...CHART_DEFAULTS,
            cutout: '65%',
            plugins: {
                ...CHART_DEFAULTS.plugins
            }
        }
    };
    return createChart(canvasId, config);
}

/**
 * Create a radar chart
 */
function createRadarChart(canvasId, labels, datasets, options = {}) {
    const config = {
        type: 'radar',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            ...CHART_DEFAULTS,
            ...options,
            plugins: {
                ...CHART_DEFAULTS.plugins,
                ...(options.plugins || {})
            }
        }
    };
    return createChart(canvasId, config);
}

/**
 * Deep merge utility
 */
function mergeDeep(target, source) {
    const output = { ...target };
    
    if (isObject(target) && isObject(source)) {
        Object.keys(source).forEach(key => {
            if (isObject(source[key])) {
                if (!(key in target)) {
                    Object.assign(output, { [key]: source[key] });
                } else {
                    output[key] = mergeDeep(target[key], source[key]);
                }
            } else {
                Object.assign(output, { [key]: source[key] });
            }
        });
    }
    
    return output;
}

/**
 * Check if value is an object
 */
function isObject(item) {
    return item && typeof item === 'object' && !Array.isArray(item);
}

/**
 * Update chart data
 */
function updateChartData(chart, newData) {
    if (!chart) return;
    
    if (newData.labels) {
        chart.data.labels = newData.labels;
    }
    
    if (newData.datasets) {
        chart.data.datasets = newData.datasets;
    }
    
    chart.update();
}

/**
 * Add data to chart
 */
function addChartData(chart, label, data) {
    if (!chart) return;
    
    chart.data.labels.push(label);
    chart.data.datasets.forEach((dataset, index) => {
        if (Array.isArray(data[index])) {
            dataset.data.push(data[index]);
        } else {
            dataset.data.push(data);
        }
    });
    chart.update();
}

/**
 * Remove data from chart
 */
function removeChartData(chart, index) {
    if (!chart) return;
    
    chart.data.labels.splice(index, 1);
    chart.data.datasets.forEach(dataset => {
        dataset.data.splice(index, 1);
    });
    chart.update();
}

// ============================================================
// EXPOSE FUNCTIONS GLOBALLY
// ============================================================

window.CHART_COLORS = CHART_COLORS;
window.CHART_PALETTES = CHART_PALETTES;
window.CHART_DEFAULTS = CHART_DEFAULTS;
window.createChart = createChart;
window.createLineChart = createLineChart;
window.createBarChart = createBarChart;
window.createPieChart = createPieChart;
window.createDoughnutChart = createDoughnutChart;
window.createRadarChart = createRadarChart;
window.updateChartData = updateChartData;
window.addChartData = addChartData;
window.removeChartData = removeChartData;