<div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <div class="text-lg font-medium text-gray-900">Loading...</div>
    </div>
</div>

<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2">
    <!-- Toasts will be dynamically added here -->
</div>

<script>
// Loading overlay functionality
function showLoading(message = 'Loading...') {
    const overlay = document.getElementById('loading-overlay');
    const loadingText = overlay.querySelector('.text-lg');
    loadingText.textContent = message;
    overlay.classList.remove('hidden');
}

function hideLoading() {
    const overlay = document.getElementById('loading-overlay');
    overlay.classList.add('hidden');
}

// Toast notification functionality
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    
    const toast = document.createElement('div');
    toast.className = `max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto flex ring-1 ring-black ring-opacity-5 transform transition-all duration-300 ease-in-out translate-x-full`;
    
    const iconClass = type === 'success' ? 'fas fa-check-circle text-green-400' : 
                     type === 'error' ? 'fas fa-exclamation-circle text-red-400' : 
                     type === 'warning' ? 'fas fa-exclamation-triangle text-yellow-400' : 
                     'fas fa-info-circle text-blue-400';
    
    const bgClass = type === 'success' ? 'bg-green-50' : 
                   type === 'error' ? 'bg-red-50' : 
                   type === 'warning' ? 'bg-yellow-50' : 
                   'bg-blue-50';
    
    toast.innerHTML = `
        <div class="flex-1 w-0 p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="${iconClass} text-xl"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-900">${message}</p>
                </div>
            </div>
        </div>
        <div class="flex border-l border-gray-200">
            <button onclick="this.parentElement.parentElement.remove()" class="w-full border border-transparent rounded-none rounded-r-lg p-4 flex items-center justify-center text-sm font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Add background color
    toast.querySelector('.flex-1').classList.add(bgClass);
    
    container.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 300);
    }, 5000);
}

// Enhanced form submission with loading states
function submitFormWithLoading(form, message = 'Processing...') {
    showLoading(message);
    form.submit();
}

// Auto-submit functionality for filters
function setupAutoSubmit() {
    const selects = document.querySelectorAll('select[name="per_page"], select[name="status"], select[name="company_id"]');
    selects.forEach(select => {
        select.addEventListener('change', function() {
            showLoading('Applying filters...');
            this.form.submit();
        });
    });
}

// Initialize auto-submit when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    setupAutoSubmit();
});

// Enhanced search with debouncing
let searchTimeout;
function debouncedSearch(input) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        if (input.value.length >= 3 || input.value.length === 0) {
            showLoading('Searching...');
            input.form.submit();
        }
    }, 500);
}

// Confirm dialog enhancements
function confirmAction(message, callback) {
    // Create custom confirm dialog
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex items-center mb-4">
                <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl mr-3"></i>
                <h3 class="text-lg font-medium text-gray-900">Confirm Action</h3>
            </div>
            <p class="text-gray-600 mb-6">${message}</p>
            <div class="flex justify-end space-x-3">
                <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Cancel
                </button>
                <button onclick="this.closest('.fixed').remove(); ${callback};" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                    Confirm
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// Progress indicator for bulk operations
function showProgress(operation, total) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex items-center mb-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-3"></div>
                <h3 class="text-lg font-medium text-gray-900">${operation}</h3>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <p class="text-sm text-gray-600 mt-2" id="progress-text">Processing 0 of ${total} items...</p>
        </div>
    `;
    
    document.body.appendChild(modal);
    return modal;
}

function updateProgress(modal, current, total) {
    const progressBar = modal.querySelector('.bg-blue-600');
    const progressText = modal.querySelector('#progress-text');
    const percentage = (current / total) * 100;
    
    progressBar.style.width = percentage + '%';
    progressText.textContent = `Processing ${current} of ${total} items...`;
    
    if (current === total) {
        setTimeout(() => {
            modal.remove();
            showToast('Operation completed successfully!', 'success');
        }, 500);
    }
}
</script>