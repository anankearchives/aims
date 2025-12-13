
// Popup Modal Functions

document.addEventListener('DOMContentLoaded', function() {
    // Handle popup triggers
    const popupTriggers = document.querySelectorAll('[data-popup][data-action="show"]');
    
    popupTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const popupId = this.getAttribute('data-popup');
            showPopup(popupId);
        });
    });
    
    // Close popup when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('popup')) {
            closePopup(e.target.id);
        }
    });
});

// Show popup
function showPopup(popupId) {
    const popup = document.getElementById(popupId);
    if (popup) {
        popup.style.display = 'flex';
        popup.classList.add('show');
    }
}

// Close popup
function closePopup(popupId) {
    const popup = document.getElementById(popupId);
    if (popup) {
        popup.style.display = 'none';
        popup.classList.remove('show');
    }
}

// Close all popups
function closeAllPopups() {
    const popups = document.querySelectorAll('.popup');
    popups.forEach(popup => {
        popup.style.display = 'none';
        popup.classList.remove('show');
    });
}

// Confirm action
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Release vehicle (for inline buttons)
function releaseVehicle(vehicleId) {
    const confirmed = confirm('Are you sure you want to release this vehicle?');
    if (confirmed) {
        window.location.href = 'release.php?id=' + vehicleId;
    }
}

// Delete confirmation
function confirmDelete(itemName, deleteUrl) {
    const confirmed = confirm('Are you sure you want to delete ' + itemName + '?');
    if (confirmed) {
        window.location.href = deleteUrl;
    }
}

// Format currency
function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Calculate days between dates
function daysBetween(date1, date2) {
    const oneDay = 24 * 60 * 60 * 1000;
    const firstDate = new Date(date1);
    const secondDate = new Date(date2);
    return Math.round(Math.abs((firstDate - secondDate) / oneDay));
}

// Print page
function printPage() {
    window.print();
}

// Export table to CSV
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
        }
        
        csv.push(row.join(','));
    }
    
    downloadCSV(csv.join('\n'), filename);
}

function downloadCSV(csv, filename) {
    const csvFile = new Blob([csv], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

// Search highlight
function highlightSearchTerm(searchTerm) {
    if (!searchTerm) return;
    
    const regex = new RegExp('(' + searchTerm + ')', 'gi');
    const elements = document.querySelectorAll('td');
    
    elements.forEach(element => {
        const text = element.innerHTML;
        element.innerHTML = text.replace(regex, '<mark>$1</mark>');
    });
}

// Auto-uppercase input
function autoUpperCase(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }
}

// Validate Philippine plate number format
function validatePlateNumber(plateNumber) {
    // Format: ABC 1234 or ABC1234
    const regex = /^[A-Z]{3}\s?\d{4}$/;
    return regex.test(plateNumber.toUpperCase());
}

// Validate Philippine license number format
function validateLicenseNumber(licenseNumber) {
    // Format: N01-12-345678
    const regex = /^[A-Z]\d{2}-\d{2}-\d{6}$/;
    return regex.test(licenseNumber);
}

// Validate Philippine phone number
function validatePhoneNumber(phoneNumber) {
    // Format: 09171234567
    const regex = /^09\d{9}$/;
    return regex.test(phoneNumber);
}

// Form validation helpers
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

function validateYear(year) {
    const currentYear = new Date().getFullYear();
    return year >= 1900 && year <= currentYear + 1;
}

// Show loading spinner
function showLoading() {
    const loader = document.createElement('div');
    loader.id = 'loading-overlay';
    loader.innerHTML = '<div class="spinner"></div>';
    loader.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    `;
    document.body.appendChild(loader);
}

function hideLoading() {
    const loader = document.getElementById('loading-overlay');
    if (loader) {
        loader.remove();
    }
}

// Toast notification
function showToast(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 15px 25px;
        background: ${type === 'success' ? '#51cf66' : type === 'error' ? '#ff6b6b' : '#4dabf7'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}
`;
document.head.appendChild(style);