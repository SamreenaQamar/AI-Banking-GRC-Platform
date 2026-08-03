/**
 * AI Banking GRC Platform - Table Utilities
 * 
 * This file contains table utility functions
 */

'use strict';

// ============================================================
// TABLE CLASS
// ============================================================

class DataTable {
    constructor(tableElement, options = {}) {
        this.table = tableElement;
        this.options = {
            perPage: options.perPage || 10,
            searchable: options.searchable !== undefined ? options.searchable : true,
            sortable: options.sortable !== undefined ? options.sortable : true,
            pagination: options.pagination !== undefined ? options.pagination : true,
            ...options
        };
        
        this.currentPage = 1;
        this.sortColumn = null;
        this.sortOrder = 'asc';
        this.searchTerm = '';
        this.data = [];
        this.filteredData = [];
        
        this.init();
    }

    init() {
        // Get data from table
        this.extractData();
        
        // Build table
        this.buildTable();
        
        // Setup search
        if (this.options.searchable) {
            this.setupSearch();
        }
        
        // Setup sorting
        if (this.options.sortable) {
            this.setupSorting();
        }
        
        // Setup pagination
        if (this.options.pagination) {
            this.setupPagination();
        }
    }

    extractData() {
        const thead = this.table.querySelector('thead');
        const tbody = this.table.querySelector('tbody');
        
        if (!thead || !tbody) return;
        
        // Get headers
        this.headers = [];
        thead.querySelectorAll('th').forEach(th => {
            this.headers.push({
                text: th.textContent.trim(),
                sortable: th.dataset.sortable !== 'false',
                key: th.dataset.key || th.textContent.trim().toLowerCase()
            });
        });
        
        // Get rows
        this.data = [];
        tbody.querySelectorAll('tr').forEach(tr => {
            const row = {};
            tr.querySelectorAll('td').forEach((td, index) => {
                const key = this.headers[index]?.key || index;
                row[key] = td.textContent.trim();
                row._html = td.innerHTML;
                row._element = td;
            });
            this.data.push(row);
        });
        
        this.filteredData = [...this.data];
    }

    buildTable() {
        // Rebuild table with header and body
        const table = this.table;
        const thead = table.querySelector('thead');
        const tbody = table.querySelector('tbody');
        
        // Clear table
        table.innerHTML = '';
        
        // Build header
        const newThead = document.createElement('thead');
        const headerRow = document.createElement('tr');
        this.headers.forEach(header => {
            const th = document.createElement('th');
            th.textContent = header.text;
            if (header.sortable) {
                th.classList.add('sortable');
                th.dataset.key = header.key;
            }
            headerRow.appendChild(th);
        });
        newThead.appendChild(headerRow);
        table.appendChild(newThead);
        
        // Build body
        const newTbody = document.createElement('tbody');
        table.appendChild(newTbody);
        
        // Render data
        this.render();
    }

    render() {
        const tbody = this.table.querySelector('tbody');
        if (!tbody) return;
        
        // Clear body
        tbody.innerHTML = '';
        
        // Filter data
        this.applySearch();
        this.applySort();
        
        // Paginate
        const start = (this.currentPage - 1) * this.options.perPage;
        const end = start + this.options.perPage;
        const pageData = this.filteredData.slice(start, end);
        
        // Render rows
        pageData.forEach(row => {
            const tr = document.createElement('tr');
            this.headers.forEach(header => {
                const td = document.createElement('td');
                td.textContent = row[header.key] || '';
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });
        
        // Update pagination
        if (this.options.pagination) {
            this.updatePagination();
        }
    }

    applySearch() {
        if (!this.searchTerm) {
            this.filteredData = [...this.data];
            return;
        }
        
        const term = this.searchTerm.toLowerCase();
        this.filteredData = this.data.filter(row => {
            return Object.values(row).some(value => {
                return String(value).toLowerCase().includes(term);
            });
        });
    }

    applySort() {
        if (!this.sortColumn) return;
        
        this.filteredData.sort((a, b) => {
            const aVal = a[this.sortColumn] || '';
            const bVal = b[this.sortColumn] || '';
            
            if (this.sortOrder === 'asc') {
                return aVal.localeCompare(bVal);
            } else {
                return bVal.localeCompare(aVal);
            }
        });
    }

    setupSearch() {
        const searchWrapper = document.createElement('div');
        searchWrapper.className = 'table-search';
        
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control';
        input.placeholder = 'Search...';
        input.addEventListener('input', (e) => {
            this.searchTerm = e.target.value;
            this.currentPage = 1;
            this.render();
        });
        
        searchWrapper.appendChild(input);
        this.table.parentNode.insertBefore(searchWrapper, this.table);
    }

    setupSorting() {
        this.table.querySelectorAll('thead th.sortable').forEach(th => {
            th.style.cursor = 'pointer';
            th.addEventListener('click', () => {
                const key = th.dataset.key;
                if (this.sortColumn === key) {
                    this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortColumn = key;
                    this.sortOrder = 'asc';
                }
                this.render();
            });
        });
    }

    setupPagination() {
        this.paginationContainer = document.createElement('div');
        this.paginationContainer.className = 'table-pagination';
        this.table.parentNode.insertBefore(this.paginationContainer, this.table.nextSibling);
        this.renderPagination();
    }

    renderPagination() {
        if (!this.paginationContainer) return;
        
        const totalPages = Math.ceil(this.filteredData.length / this.options.perPage);
        const info = document.createElement('div');
        info.className = 'pagination-info';
        const start = (this.currentPage - 1) * this.options.perPage + 1;
        const end = Math.min(this.currentPage * this.options.perPage, this.filteredData.length);
        info.textContent = `Showing ${start} to ${end} of ${this.filteredData.length} entries`;
        
        const controls = document.createElement('div');
        controls.className = 'pagination-controls';
        
        // Previous button
        const prevBtn = document.createElement('button');
        prevBtn.className = 'btn btn-sm btn-outline-secondary';
        prevBtn.textContent = 'Previous';
        prevBtn.disabled = this.currentPage <= 1;
        prevBtn.addEventListener('click', () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.render();
            }
        });
        controls.appendChild(prevBtn);
        
        // Page numbers
        const pageInfo = document.createElement('span');
        pageInfo.className = 'mx-2';
        pageInfo.textContent = `Page ${this.currentPage} of ${totalPages}`;
        controls.appendChild(pageInfo);
        
        // Next button
        const nextBtn = document.createElement('button');
        nextBtn.className = 'btn btn-sm btn-outline-secondary';
        nextBtn.textContent = 'Next';
        nextBtn.disabled = this.currentPage >= totalPages;
        nextBtn.addEventListener('click', () => {
            if (this.currentPage < totalPages) {
                this.currentPage++;
                this.render();
            }
        });
        controls.appendChild(nextBtn);
        
        this.paginationContainer.innerHTML = '';
        this.paginationContainer.appendChild(info);
        this.paginationContainer.appendChild(controls);
    }

    updatePagination() {
        if (this.options.pagination) {
            this.renderPagination();
        }
    }

    refresh() {
        this.extractData();
        this.render();
    }

    setData(data) {
        this.data = data;
        this.filteredData = [...data];
        this.currentPage = 1;
        this.render();
    }

    search(term) {
        this.searchTerm = term;
        this.currentPage = 1;
        this.render();
    }

    sort(column, order = 'asc') {
        this.sortColumn = column;
        this.sortOrder = order;
        this.render();
    }
}

// ============================================================
// EXPOSE FUNCTIONS GLOBALLY
// ============================================================

window.DataTable = DataTable;