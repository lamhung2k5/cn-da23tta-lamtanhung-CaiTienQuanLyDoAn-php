/**
 * MAIN JAVASCRIPT
 * JavaScript tùy chỉnh cho hệ thống QLĐT
 */

(function() {
    'use strict';

    // ===== UTILITY FUNCTIONS =====
    
    /**
     * Show loading spinner
     */
    function showLoading() {
        const spinner = document.createElement('div');
        spinner.className = 'spinner-overlay';
        spinner.id = 'loadingSpinner';
        spinner.innerHTML = `
            <div class="spinner-border text-light spinner-border-custom" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        `;
        document.body.appendChild(spinner);
    }

    /**
     * Hide loading spinner
     */
    function hideLoading() {
        const spinner = document.getElementById('loadingSpinner');
        if (spinner) {
            spinner.remove();
        }
    }

    /**
     * Show toast notification
     */
    function showToast(message, type = 'info') {
        const toastContainer = document.getElementById('toastContainer') || createToastContainer();
        
        const toastId = 'toast-' + Date.now();
        const bgClass = {
            'success': 'bg-success',
            'error': 'bg-danger',
            'warning': 'bg-warning',
            'info': 'bg-info'
        }[type] || 'bg-info';
        
        const icon = {
            'success': 'bi-check-circle-fill',
            'error': 'bi-exclamation-triangle-fill',
            'warning': 'bi-exclamation-circle-fill',
            'info': 'bi-info-circle-fill'
        }[type] || 'bi-info-circle-fill';
        
        const toastHTML = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi ${icon} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
        toast.show();
        
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    }

    /**
     * Create toast container if not exists
     */
    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }

    /**
     * Confirm dialog
     */
    function confirmAction(message, callback) {
        if (confirm(message)) {
            callback();
        }
    }

    /**
     * Format date to Vietnamese format
     */
    function formatDate(dateString) {
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${day}/${month}/${year} ${hours}:${minutes}`;
    }

    /**
     * Debounce function
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // ===== AJAX HELPERS =====
    
    /**
     * Make AJAX request
     */
    async function ajaxRequest(url, method = 'GET', data = null) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }
        
        try {
            const response = await fetch(url, options);
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('AJAX Error:', error);
            throw error;
        }
    }

    // ===== TABLE UTILITIES =====
    
    /**
     * Search in table
     */
    function searchTable(inputId, tableId, columnIndex = null) {
        const input = document.getElementById(inputId);
        const table = document.getElementById(tableId);
        
        if (!input || !table) return;
        
        input.addEventListener('keyup', debounce(function() {
            const filter = this.value.toLowerCase();
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            let visibleCount = 0;
            
            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;
                
                if (columnIndex !== null) {
                    // Search in specific column
                    const cell = cells[columnIndex];
                    if (cell) {
                        const text = cell.textContent || cell.innerText;
                        found = text.toLowerCase().indexOf(filter) > -1;
                    }
                } else {
                    // Search in all columns
                    for (let j = 0; j < cells.length; j++) {
                        const text = cells[j].textContent || cells[j].innerText;
                        if (text.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                if (found) {
                    rows[i].style.display = '';
                    visibleCount++;
                    // Update STT
                    if (cells[0]) {
                        cells[0].textContent = visibleCount;
                    }
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }, 300));
    }

    /**
     * Sort table
     */
    function sortTable(tableId, columnIndex, isNumeric = false) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const tbody = table.getElementsByTagName('tbody')[0];
        const rows = Array.from(tbody.getElementsByTagName('tr'));
        
        let ascending = true;
        
        rows.sort((a, b) => {
            const aValue = a.getElementsByTagName('td')[columnIndex].textContent.trim();
            const bValue = b.getElementsByTagName('td')[columnIndex].textContent.trim();
            
            if (isNumeric) {
                return ascending ? 
                    parseFloat(aValue) - parseFloat(bValue) : 
                    parseFloat(bValue) - parseFloat(aValue);
            } else {
                return ascending ? 
                    aValue.localeCompare(bValue, 'vi') : 
                    bValue.localeCompare(aValue, 'vi');
            }
        });
        
        rows.forEach(row => tbody.appendChild(row));
        ascending = !ascending;
    }

    // ===== FORM UTILITIES =====
    
    /**
     * Validate form
     */
    function validateForm(formId) {
        const form = document.getElementById(formId);
        if (!form) return false;
        
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return false;
        }
        
        return true;
    }

    /**
     * Reset form
     */
    function resetForm(formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
    }

    /**
     * Serialize form data to object
     */
    function serializeForm(formId) {
        const form = document.getElementById(formId);
        if (!form) return {};
        
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        return data;
    }

    // ===== NOTIFICATION UTILITIES =====
    
    /**
     * Load notifications
     */
    async function loadNotifications() {
        try {
            const result = await ajaxRequest('/api/notifications.php');
            if (result.success) {
                updateNotificationBadge(result.unread_count);
                renderNotifications(result.notifications);
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }

    /**
     * Update notification badge
     */
    function updateNotificationBadge(count) {
        const badge = document.querySelector('#notificationDropdown .badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 9 ? '9+' : count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    /**
     * Render notifications
     */
    function renderNotifications(notifications) {
        const container = document.querySelector('#notificationDropdown + .dropdown-menu');
        if (!container) return;
        
        // Clear existing notifications (except header and divider)
        const items = container.querySelectorAll('.dropdown-item');
        items.forEach(item => item.remove());
        
        if (notifications.length === 0) {
            container.insertAdjacentHTML('beforeend', `
                <li><span class="dropdown-item-text text-muted text-center small">Không có thông báo mới</span></li>
            `);
        } else {
            notifications.forEach(notif => {
                const iconClass = {
                    'success': 'bi-check-circle-fill text-success',
                    'danger': 'bi-exclamation-triangle-fill text-danger',
                    'warning': 'bi-exclamation-circle-fill text-warning',
                    'info': 'bi-info-circle-fill text-info'
                }[notif.loai] || 'bi-info-circle-fill text-info';
                
                container.insertAdjacentHTML('beforeend', `
                    <li>
                        <a class="dropdown-item ${notif.da_doc ? '' : 'fw-bold'}" href="${notif.link || '#'}" 
                           data-notification-id="${notif.id}">
                            <i class="bi ${iconClass} me-2"></i>
                            <div class="small">
                                <div>${notif.tieu_de}</div>
                                <div class="text-muted">${notif.noi_dung}</div>
                            </div>
                        </a>
                    </li>
                `);
            });
            
            container.insertAdjacentHTML('beforeend', `
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-center small" href="/notifications.php">Xem tất cả thông báo</a></li>
            `);
        }
    }

    // ===== INITIALIZATION =====
    
    /**
     * Initialize on DOM ready
     */
    document.addEventListener('DOMContentLoaded', function() {
        console.log('QLDT System Initialized');
        
        // Load notifications if logged in
        if (document.getElementById('notificationDropdown')) {
            loadNotifications();
            // Refresh every 30 seconds
            setInterval(loadNotifications, 30000);
        }
        
        // Initialize search tables if exists
        if (document.getElementById('searchTable')) {
            searchTable('searchTable', 'dataTable');
        }
    });

    // ===== EXPORT TO GLOBAL =====
    
    window.QLDT = {
        showLoading,
        hideLoading,
        showToast,
        confirmAction,
        formatDate,
        ajaxRequest,
        searchTable,
        sortTable,
        validateForm,
        resetForm,
        serializeForm,
        loadNotifications
    };

})();
