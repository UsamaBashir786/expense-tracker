// main.js - Fixed version with delete functionality and no fallback data
const API_BASE = 'api/';
let trendChart = null;
let categoryChart = null;

// Debug mode
const DEBUG = true;

// Initialize Lucide icons
lucide.createIcons();

// Mobile detection
const isMobile = () => window.innerWidth <= 640;

// Debug logging
function debugLog(...args) {
    if (DEBUG) {
        console.log('[Expense Tracker]', ...args);
    }
}

// Update recent expenses display
function updateRecentExpenses(expenses) {
    const container = document.getElementById('recentExpenses');
    const isMobileView = isMobile();
    
    if (!expenses || expenses.length === 0) {
        container.innerHTML = `
            <div class="text-center py-6 sm:py-8">
                <i data-lucide="inbox" class="h-10 w-10 sm:h-12 sm:w-12 mx-auto text-muted-foreground mb-3 sm:mb-4"></i>
                <p class="text-sm sm:text-base text-muted-foreground font-medium">No expenses yet</p>
                <p class="text-xs sm:text-sm text-muted-foreground mt-1">Add your first expense to get started</p>
                <button onclick="openModal()" class="btn btn-primary btn-sm sm:btn-md mt-3 sm:mt-4">
                    <i data-lucide="plus" class="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2"></i>
                    Add Expense
                </button>
            </div>
        `;
        lucide.createIcons();
        return;
    }
    
    container.innerHTML = expenses.map(expense => {
        // Mobile: vertical layout, Desktop: horizontal layout
        if (isMobileView) {
            return `
                <div class="p-2.5 rounded-lg border hover:border-primary/50 transition-colors animate-fade-in">
                    <div class="flex items-start gap-2 mb-2">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg flex-shrink-0" 
                             style="background-color: ${expense.color}20">
                            <span class="text-base">${expense.icon}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-foreground leading-tight">${expense.description}</p>
                            <p class="text-xs text-muted-foreground mt-0.5">${expense.category}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pl-11">
                        <span class="text-xs text-muted-foreground">${expense.formatted_date}</span>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-foreground">Rs ${formatCurrency(expense.amount)}</span>
                            <button onclick="deleteExpense(${expense.id})" 
                                    class="p-1.5 rounded hover:bg-destructive/10 transition-colors"
                                    title="Delete">
                                <i data-lucide="trash-2" class="h-3.5 w-3.5 text-destructive"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        } else {
            return `
                <div class="flex items-center gap-3 p-3 rounded-lg border hover:border-primary/50 transition-colors animate-fade-in group">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg flex-shrink-0" 
                         style="background-color: ${expense.color}20">
                        <span class="text-lg">${expense.icon}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate text-foreground">${expense.description}</p>
                        <p class="text-xs text-muted-foreground truncate">${expense.category} • ${expense.formatted_date}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="text-sm font-semibold text-foreground whitespace-nowrap">Rs ${formatCurrency(expense.amount)}</span>
                        <button onclick="deleteExpense(${expense.id})" 
                                class="p-1.5 rounded hover:bg-destructive/10 opacity-0 group-hover:opacity-100 transition-all"
                                title="Delete expense">
                            <i data-lucide="trash-2" class="h-4 w-4 text-destructive"></i>
                        </button>
                    </div>
                </div>
            `;
        }
    }).join('');
    
    lucide.createIcons();
}

// Delete expense function
async function deleteExpense(id) {
    if (!confirm('Are you sure you want to delete this expense?')) {
        return;
    }
    
    debugLog('Deleting expense:', id);
    
    try {
        const response = await fetch(`${API_BASE}delete_expense.php?id=${id}`, {
            method: 'DELETE'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const result = await response.json();
        debugLog('Delete response:', result);
        
        if (result.success) {
            showNotification('Expense deleted successfully');
            
            // Refresh all data
            setTimeout(() => {
                loadExpenses();
                loadStats();
                updateCharts();
                loadQuickInsights();
            }, 300);
        } else {
            showNotification('Error deleting expense: ' + (result.error || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Error deleting expense:', error);
        showNotification('Error deleting expense. Please try again.', 'error');
    }
}

// Load expenses via AJAX - NO FALLBACK DATA
async function loadExpenses() {
    debugLog('Loading expenses...');
    const container = document.getElementById('recentExpenses');
    
    // Show loading state
    container.innerHTML = `
        <div class="text-center py-8">
            <div class="loading-spinner mx-auto mb-4"></div>
            <p class="text-muted-foreground text-sm">Loading expenses...</p>
        </div>
    `;
    
    try {
        const limit = isMobile() ? 5 : 10;
        const response = await fetch(`${API_BASE}get_expenses.php?limit=${limit}`);
        
        debugLog('Response status:', response.status, response.statusText);
        
        if (!response.ok) {
            let errorText = 'Unknown error';
            try {
                const errorData = await response.text();
                errorText = errorData.substring(0, 100);
            } catch (e) {
                errorText = response.statusText;
            }
            
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            debugLog('Non-JSON response:', text.substring(0, 200));
            throw new Error('Server returned non-JSON response. Check PHP errors.');
        }
        
        const expenses = await response.json();
        debugLog('Expenses loaded:', Array.isArray(expenses) ? `${expenses.length} items` : 'Not an array');
        
        // Validate response is an array
        if (!Array.isArray(expenses)) {
            debugLog('Invalid expenses data:', expenses);
            throw new Error('Server returned invalid data format');
        }
        
        updateRecentExpenses(expenses);
        
    } catch (error) {
        console.error('Error loading expenses:', error);
        
        // Show user-friendly error message
        let errorMessage = error.message;
        if (error.message.includes('Failed to fetch')) {
            errorMessage = 'Cannot connect to server. Please check your internet connection and ensure the API is running.';
        } else if (error.message.includes('404')) {
            errorMessage = 'API endpoint not found. Check that api/get_expenses.php exists.';
        } else if (error.message.includes('500')) {
            errorMessage = 'Server error. Check PHP error logs for details.';
        }
        
        container.innerHTML = `
            <div class="text-center py-8">
                <i data-lucide="alert-circle" class="h-12 w-12 mx-auto text-destructive mb-4"></i>
                <p class="text-destructive font-medium">Failed to load expenses</p>
                <p class="text-sm text-muted-foreground mt-2">${errorMessage}</p>
                <div class="text-xs text-muted-foreground mt-2 p-3 bg-muted rounded-lg text-left max-w-md mx-auto">
                    <strong>Debug Info:</strong><br>
                    API URL: ${API_BASE}get_expenses.php<br>
                    Error: ${error.message}
                </div>
                <div class="flex gap-2 justify-center mt-4">
                    <button onclick="loadExpenses()" class="btn btn-outline btn-sm">
                        <i data-lucide="refresh-cw" class="h-4 w-4 mr-2"></i>
                        Retry
                    </button>
                    <button onclick="openModal()" class="btn btn-primary btn-sm">
                        <i data-lucide="plus" class="h-4 w-4 mr-2"></i>
                        Add Expense
                    </button>
                </div>
            </div>
        `;
        
        lucide.createIcons();
    }
}

// Load stats via AJAX
async function loadStats() {
    debugLog('Loading stats...');
    try {
        const response = await fetch(API_BASE + 'get_stats.php');
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const stats = await response.json();
        debugLog('Stats loaded:', stats);
        updateStatsUI(stats);
    } catch (error) {
        console.error('Error loading stats:', error);
        showNotification('Failed to load statistics', 'error');
        
        const statsContainer = document.getElementById('statsContainer');
        statsContainer.innerHTML = `
            <div class="card col-span-2 lg:col-span-4">
                <div class="text-center py-8">
                    <i data-lucide="alert-circle" class="h-12 w-12 mx-auto text-destructive mb-4"></i>
                    <p class="text-destructive font-medium">Failed to load statistics</p>
                    <p class="text-sm text-muted-foreground mt-1">Error: ${error.message}</p>
                    <button onclick="loadStats()" class="btn btn-outline mt-4">
                        <i data-lucide="refresh-cw" class="h-4 w-4 mr-2"></i>
                        Retry
                    </button>
                </div>
            </div>
        `;
        lucide.createIcons();
    }
}

// Load quick insights
async function loadQuickInsights() {
    debugLog('Loading insights...');
    try {
        const response = await fetch(API_BASE + 'get_insights.php');
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const insights = await response.json();
        debugLog('Insights loaded:', insights);
        
        document.getElementById('dailyAvg').textContent = `Rs ${formatCurrency(insights.daily_avg || 0)}`;
        document.getElementById('highestDay').textContent = insights.highest_day || '-';
        document.getElementById('savingsRate').textContent = insights.savings_rate || '0%';
    } catch (error) {
        console.error('Error loading insights:', error);
    }
}

// Update stats UI
function updateStatsUI(stats) {
    debugLog('Updating stats UI:', stats);
    const isMobileView = isMobile();
    
    if (stats.error) {
        const statsContainer = document.getElementById('statsContainer');
        statsContainer.innerHTML = `
            <div class="card col-span-2 lg:col-span-4">
                <div class="text-center py-8">
                    <i data-lucide="alert-circle" class="h-12 w-12 mx-auto text-destructive mb-4"></i>
                    <p class="text-destructive font-medium">Database Error</p>
                    <p class="text-sm text-muted-foreground mt-1">${stats.error}</p>
                </div>
            </div>
        `;
        return;
    }
    
    const statsData = [
        {
            title: 'Total Expenses',
            value: `Rs ${formatStatsCurrency(stats.total || 0)}`,
            icon: 'wallet',
            color: 'text-blue-500',
            bgColor: 'bg-blue-50'
        },
        {
            title: 'This Month',
            value: `Rs ${formatStatsCurrency(stats.month || 0)}`,
            icon: 'calendar',
            color: 'text-emerald-500',
            bgColor: 'bg-emerald-50'
        },
        {
            title: 'Top Category',
            value: isMobileView ? (stats.top_category?.split(' ')[0] || 'None') : (stats.top_category || 'None'),
            icon: 'pie-chart',
            color: 'text-purple-500',
            bgColor: 'bg-purple-50'
        },
        {
            title: 'Transactions',
            value: formatNumber(stats.count || 0),
            icon: 'list-checks',
            color: 'text-amber-500',
            bgColor: 'bg-amber-50'
        }
    ];

    const statsContainer = document.getElementById('statsContainer');
    statsContainer.innerHTML = statsData.map(stat => `
        <div class="card card-hover animate-fade-in">
            <div class="flex items-center justify-between mb-${isMobileView ? '2' : '4'}">
                <div class="p-${isMobileView ? '1.5' : '2'} rounded-lg ${stat.bgColor}">
                    <i data-lucide="${stat.icon}" class="h-${isMobileView ? '4' : '5'} w-${isMobileView ? '4' : '5'} ${stat.color}"></i>
                </div>
            </div>
            <p class="text-${isMobileView ? 'xs' : 'sm'} text-muted-foreground mb-1 truncate">${stat.title}</p>
            <p class="text-${isMobileView ? 'xl' : '2xl'} font-semibold truncate text-foreground">${stat.value}</p>
        </div>
    `).join('');

    lucide.createIcons();
}

// Initialize charts
function initCharts() {
    debugLog('Initializing charts...');
    
    const trendCanvas = document.getElementById('trendChart');
    const categoryCanvas = document.getElementById('categoryChart');
    
    if (!trendCanvas || !categoryCanvas) {
        console.error('Chart canvases not found!');
        return;
    }
    
    const trendCtx = trendCanvas.getContext('2d');
    const categoryCtx = categoryCanvas.getContext('2d');
    
    const isMobileView = isMobile();
    
    if (trendChart) trendChart.destroy();
    if (categoryChart) categoryChart.destroy();
    
    try {
        trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Daily Expenses',
                    data: [],
                    borderColor: 'hsl(var(--primary))',
                    backgroundColor: 'hsl(var(--primary) / 0.1)',
                    borderWidth: isMobileView ? 1.5 : 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'hsl(var(--primary))',
                    pointBorderColor: 'hsl(var(--background))',
                    pointBorderWidth: isMobileView ? 1.5 : 2,
                    pointRadius: isMobileView ? 3 : 4,
                    pointHoverRadius: isMobileView ? 4 : 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Rs ${formatCurrency(context.parsed.y)}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: !isMobileView }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return `Rs ${formatCurrency(value)}`;
                            }
                        }
                    }
                }
            }
        });
        
        categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [],
                    borderWidth: isMobileView ? 1.5 : 2,
                    borderColor: 'hsl(var(--background))'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: isMobileView ? '60%' : '70%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
        
        updateCharts();
        
    } catch (error) {
        console.error('Error initializing charts:', error);
    }
}

// Update charts
async function updateCharts() {
    debugLog('Updating charts...');
    const period = document.getElementById('trendPeriod').value;
    
    try {
        const response = await fetch(`${API_BASE}get_chart_data.php?period=${period}`);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        
        // Update trend chart
        const dates = Object.keys(data.trend || {});
        const amounts = Object.values(data.trend || {});
        
        trendChart.data.labels = dates.map(d => new Date(d).toLocaleDateString('en-PK', { month: 'short', day: 'numeric' }));
        trendChart.data.datasets[0].data = amounts;
        trendChart.update();
        
        // Update category chart
        const categories = (data.categories || []).filter(c => c.total > 0);
        
        categoryChart.data.labels = categories.map(c => c.name);
        categoryChart.data.datasets[0].data = categories.map(c => c.total);
        categoryChart.data.datasets[0].backgroundColor = categories.map(c => c.color);
        categoryChart.update();
        
        // Update legend
        const legendContainer = document.getElementById('categoryLegend');
        if (legendContainer) {
            legendContainer.innerHTML = categories.map(category => `
                <div class="flex items-center justify-between p-3 rounded-lg border mb-2">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="w-3 h-3 rounded-full" style="background-color: ${category.color}"></div>
                        <span class="text-sm font-medium truncate">${category.icon} ${category.name}</span>
                    </div>
                    <span class="text-sm font-semibold">Rs ${formatCurrency(category.total)}</span>
                </div>
            `).join('');
        }
        
        lucide.createIcons();
        
    } catch (error) {
        console.error('Error loading chart data:', error);
    }
}

// Format currency
function formatCurrency(amount) {
    const num = parseFloat(amount);
    if (isNaN(num)) return '0';
    if (num < 1000) return num.toFixed(2);
    if (num < 1000000) return (num / 1000).toFixed(1) + 'k';
    return (num / 1000000).toFixed(1) + 'M';
}

function formatStatsCurrency(amount) {
    const num = parseFloat(amount);
    if (isNaN(num) || num === 0) return '0';
    if (num < 1000) return num.toFixed(0);
    if (num < 1000000) return (num / 1000).toFixed(1) + 'k';
    return (num / 1000000).toFixed(2) + 'M';
}

function formatNumber(num) {
    const n = parseInt(num);
    if (isNaN(n)) return '0';
    if (n >= 1000) return (n / 1000).toFixed(1) + 'k';
    return n.toString();
}

// Add expense
async function addExpense(event) {
    event.preventDefault();
    
    const amount = document.getElementById('amount').value;
    const category_id = document.getElementById('category').value;
    const description = document.getElementById('description').value;
    const date = document.getElementById('date').value;
    
    if (!amount || amount <= 0) {
        showNotification('Please enter a valid amount', 'error');
        return false;
    }
    
    if (!category_id) {
        showNotification('Please select a category', 'error');
        return false;
    }
    
    const expenseData = {
        amount: parseFloat(amount),
        category_id: parseInt(category_id),
        description: description.trim(),
        date: date
    };
    
    try {
        const response = await fetch(API_BASE + 'add_expense.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(expenseData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Expense added successfully!');
            closeModal();
            
            setTimeout(() => {
                loadExpenses();
                loadStats();
                updateCharts();
                loadQuickInsights();
            }, 500);
        } else {
            showNotification('Error: ' + (result.error || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Error adding expense:', error);
        showNotification('Error adding expense', 'error');
    }
    
    return false;
}

// Show notification
function showNotification(message, type = 'success') {
    const existingNotifs = document.querySelectorAll('.notification');
    existingNotifs.forEach(notif => notif.remove());
    
    const notif = document.createElement('div');
    notif.className = `notification fixed top-4 right-4 z-50 flex items-center gap-3 p-4 rounded-lg border shadow-lg ${
        type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' :
        type === 'error' ? 'bg-red-50 border-red-200 text-red-800' : 
        'bg-blue-50 border-blue-200 text-blue-800'
    }`;
    
    notif.innerHTML = `
        <i data-lucide="${type === 'success' ? 'check-circle' : type === 'error' ? 'alert-circle' : 'info'}" class="h-5 w-5"></i>
        <span class="text-sm font-medium">${message}</span>
        <button onclick="this.parentElement.remove()" class="ml-4">
            <i data-lucide="x" class="h-4 w-4"></i>
        </button>
    `;
    
    document.body.appendChild(notif);
    lucide.createIcons();
    
    setTimeout(() => notif.remove(), 4000);
}

// Modal functions
function openModal() {
    document.getElementById('expenseModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('expenseModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('expenseForm').reset();
    document.getElementById('date').value = new Date().toISOString().split('T')[0];
}

// Export functions
function exportData() {
    window.location.href = API_BASE + 'export_data.php';
}

function downloadPDF() {
    showNotification('Generating PDF...', 'info');
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    doc.setFontSize(20);
    doc.text('Expense Report', 20, 20);
    
    fetch(API_BASE + 'get_expenses.php?limit=100')
        .then(response => response.json())
        .then(expenses => {
            let y = 45;
            expenses.forEach(expense => {
                if (y > 270) {
                    doc.addPage();
                    y = 20;
                }
                doc.setFontSize(10);
                doc.text(`${expense.formatted_date} - ${expense.category}: Rs ${expense.amount}`, 20, y);
                doc.text(expense.description, 20, y + 5);
                y += 15;
            });
            
            doc.save('expense-report.pdf');
            showNotification('PDF downloaded');
        })
        .catch(error => {
            console.error('PDF error:', error);
            showNotification('Error generating PDF', 'error');
        });
}

function backupData() {
    fetch(API_BASE + 'get_expenses.php?limit=1000')
        .then(response => response.json())
        .then(expenses => {
            const backup = {
                expenses,
                exportDate: new Date().toISOString()
            };
            
            const blob = new Blob([JSON.stringify(backup, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `expense-backup-${new Date().toISOString().split('T')[0]}.json`;
            a.click();
            showNotification('Backup created');
        })
        .catch(error => {
            console.error('Backup error:', error);
            showNotification('Error creating backup', 'error');
        });
}

// Initialize
async function initializeApp() {
    debugLog('Initializing...');
    
    lucide.createIcons();
    
    await loadStats();
    await loadExpenses();
    await loadQuickInsights();
    
    initCharts();
}

// Handle resize
let resizeTimeout;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        if (trendChart || categoryChart) {
            updateCharts();
        }
    }, 250);
});

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', initializeApp);