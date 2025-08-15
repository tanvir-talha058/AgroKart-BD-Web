document.addEventListener('DOMContentLoaded', function() {
    // Add animations to stat cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('animated');
        }, 100 * index);
    });
    
    // Chart.js configuration
    initializeCharts();
    // Load fresh data initially
    if (typeof fetchFreshSalesData === 'function') {
        fetchFreshSalesData();
    }

    // Add last updated badge
    ensureLastUpdatedBadge();
    
    // Add refresh button for charts
    addRefreshButton();
    
    // Chart tabs functionality
    const chartTabs = document.querySelectorAll('.chart-tab');
    if (chartTabs.length > 0) {
        chartTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                chartTabs.forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                this.classList.add('active');
                
                const period = this.getAttribute('data-period');
                // Use the update chart data function to switch between periods
                window.updateChartData(period);
            });
        });
    }
    
    // Form validation for add product form
    const addProductForm = document.querySelector('.add-product-form');
    if (addProductForm) {
        addProductForm.addEventListener('submit', function(event) {
            const productName = addProductForm.querySelector('[name="product_name"]').value;
            const category = addProductForm.querySelector('[name="category"]').value;
            const price = addProductForm.querySelector('[name="price"]').value;
            const stock = addProductForm.querySelector('[name="stock"]').value;
            const description = addProductForm.querySelector('[name="description"]').value;
            const image = addProductForm.querySelector('[name="product_image"]').files[0];
            
            let isValid = true;
            
            // Simple validation
            if (productName.trim() === '') {
                isValid = false;
                showFormError('Product name is required');
            } else if (category === '') {
                isValid = false;
                showFormError('Please select a category');
            } else if (price <= 0) {
                isValid = false;
                showFormError('Price must be greater than 0');
            } else if (stock < 0) {
                isValid = false;
                showFormError('Stock cannot be negative');
            } else if (description.trim() === '') {
                isValid = false;
                showFormError('Description is required');
            } else if (!image) {
                isValid = false;
                showFormError('Please upload a product image');
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
    }
    
    function showFormError(message) {
        const errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        errorElement.textContent = message;
        
        const existingError = document.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        const form = document.querySelector('.add-product-form');
        form.insertBefore(errorElement, form.firstChild);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            errorElement.style.opacity = '0';
            setTimeout(() => {
                errorElement.remove();
            }, 300);
        }, 5000);
    }
    
    // Make order status dropdowns more interactive
    const statusSelects = document.querySelectorAll('select.status');
    statusSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Add a loading effect before submitting the form
            this.classList.add('updating');
        });
    });
    
    // Add a visual feedback when a message is shown
    const messages = document.querySelectorAll('.error-message, .success-message');
    messages.forEach(message => {
        // Fade out messages after 5 seconds
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => {
                message.remove();
            }, 300);
        }, 5000);
    });
});

// Function to add refresh button to chart container
function addRefreshButton() {
    const chartContainer = document.querySelector('.chart-container');
    if (chartContainer) {
        // Create refresh button
        const refreshButton = document.createElement('button');
        refreshButton.id = 'refresh-charts';
        refreshButton.className = 'refresh-btn';
        refreshButton.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
        refreshButton.style.position = 'absolute';
        refreshButton.style.top = '10px';
        refreshButton.style.right = '10px';
        refreshButton.style.zIndex = '10';
        refreshButton.style.padding = '5px 10px';
        refreshButton.style.background = '#fff';
        refreshButton.style.border = '1px solid #ddd';
        refreshButton.style.borderRadius = '4px';
        refreshButton.style.cursor = 'pointer';
        refreshButton.style.display = 'flex';
        refreshButton.style.alignItems = 'center';
        refreshButton.style.gap = '5px';
        refreshButton.style.fontSize = '14px';
        refreshButton.style.color = '#333';
        
        // Add loading spinner (hidden by default)
        const loadingSpinner = document.createElement('div');
        loadingSpinner.id = 'chart-loading';
        loadingSpinner.className = 'spinner';
        loadingSpinner.style.display = 'none';
        loadingSpinner.style.width = '16px';
        loadingSpinner.style.height = '16px';
        loadingSpinner.style.border = '2px solid rgba(0, 0, 0, 0.1)';
        loadingSpinner.style.borderLeft = '2px solid #333';
        loadingSpinner.style.borderRadius = '50%';
        loadingSpinner.style.animation = 'spin 1s linear infinite';
        
        // Add keyframes animation for spinner
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
        
        // Add click event
        refreshButton.addEventListener('click', function() {
            fetchFreshSalesData();
        });
        
        // Add button and spinner to container
        chartContainer.style.position = 'relative';
        refreshButton.appendChild(loadingSpinner);
        chartContainer.appendChild(refreshButton);
    }
}

// Function to fetch fresh data with cache busting
function fetchFreshSalesData() {
    // Show loading indicator
    const loadingSpinner = document.getElementById('chart-loading');
    if (loadingSpinner) loadingSpinner.style.display = 'inline-block';
    
    // Add timestamp to URL to prevent caching
    const timestamp = new Date().getTime();
    fetch('php/load_chart_data.php?t=' + timestamp)
        .then(response => response.json())
        .then(data => {
            console.log('Fresh chart data received:', data);
            // Update the global sales data
            if (data.weekly) {
                console.log('Weekly data:', data.weekly);
                window.salesData.weekly = data.weekly;
            }
            if (data.monthly) {
                console.log('Monthly data:', data.monthly);
                window.salesData.monthly = data.monthly;
            }
            if (data.yearly) {
                console.log('Yearly data:', data.yearly);
                window.salesData.yearly = data.yearly;
            }
            
            // Update category chart if it exists
            if (data.categories && window.categoryChart) {
                window.categoryChart.data.labels = data.categories.labels;
                window.categoryChart.data.datasets[0].data = data.categories.data;
                window.categoryChart.update();
            }
            
            // Update top products chart if it exists
            if (data.topProducts && window.topProductsChart) {
                window.topProductsChart.data.labels = data.topProducts.labels;
                window.topProductsChart.data.datasets[0].data = data.topProducts.data;
                window.topProductsChart.update();
            }
            
            // Update current sales chart with fresh data
            const activePeriodTab = document.querySelector('.chart-tab.active');
            const currentPeriod = activePeriodTab ? activePeriodTab.getAttribute('data-period') : 'weekly';
            updateChartData(currentPeriod);
            
            // Hide loading indicator
            if (loadingSpinner) loadingSpinner.style.display = 'none';
            
            // Show success message
            showToast('Charts updated with latest data', 'success');

            // Update last updated badge
            updateLastUpdatedBadge();
        })
        .catch(error => {
            console.error('Error fetching fresh sales data:', error);
            // Hide loading indicator
            if (loadingSpinner) loadingSpinner.style.display = 'none';
            
            // Show error message
            showToast('Failed to update charts', 'error');
        });
}

// Initial fetch of sales data from server
function fetchSalesData() {
    fetch('php/load_chart_data.php')
        .then(response => response.json())
        .then(data => {
            console.log('Initial chart data received:', data);
            // Update the sales data with real data from server
            if (data.weekly) {
                console.log('Initial weekly data:', data.weekly);
                window.salesData.weekly = data.weekly;
            }
            if (data.monthly) {
                console.log('Initial monthly data:', data.monthly);
                window.salesData.monthly = data.monthly;
            }
            if (data.yearly) {
                console.log('Initial yearly data:', data.yearly);
                window.salesData.yearly = data.yearly;
            }
            
            // Update category chart if it exists
            if (data.categories && window.categoryChart) {
                window.categoryChart.data.labels = data.categories.labels;
                window.categoryChart.data.datasets[0].data = data.categories.data;
                window.categoryChart.update();
            }
            
            // Update top products chart if it exists
            if (data.topProducts && window.topProductsChart) {
                window.topProductsChart.data.labels = data.topProducts.labels;
                window.topProductsChart.data.datasets[0].data = data.topProducts.data;
                window.topProductsChart.update();
            }
            
            // Get active period and update chart
            const activePeriodTab = document.querySelector('.chart-tab.active');
            const currentPeriod = activePeriodTab ? activePeriodTab.getAttribute('data-period') : 'weekly';
            updateChartData(currentPeriod);

            // Update last updated badge
            updateLastUpdatedBadge();
        })
        .catch(error => {
            console.error('Error fetching sales data:', error);
        });
}

// Simple toast notification function
function showToast(message, type = 'info') {
    // Check if toastContainer exists, create if not
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.position = 'fixed';
        toastContainer.style.top = '20px';
        toastContainer.style.right = '20px';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.minWidth = '250px';
    toast.style.margin = '10px';
    toast.style.padding = '15px';
    toast.style.borderRadius = '4px';
    toast.style.boxShadow = '0 0 10px rgba(0,0,0,0.2)';
    toast.style.backgroundColor = type === 'success' ? '#4CAF50' : 
                                 type === 'error' ? '#F44336' : '#2196F3';
    toast.style.color = 'white';
    toast.style.opacity = '0';
    toast.style.transition = 'opacity 0.3s ease';
    
    toast.textContent = message;
    
    // Add to container
    toastContainer.appendChild(toast);
    
    // Show the toast
    setTimeout(() => {
        toast.style.opacity = '1';
    }, 10);
    
    // Auto hide after 3 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
            toastContainer.removeChild(toast);
        }, 300);
    }, 3000);
}
function initializeCharts() {
    // Sales chart
    const salesChartEl = document.getElementById('salesChart');
    if (salesChartEl) {
        const ctx = salesChartEl.getContext('2d');
        
        // Create a distribution of sales throughout the week
        // Create weekly data arrays with more realistic distribution
        const weeklyData = [0, 0, 0, 0, 0, 0, 0]; // Will be replaced with actual data
        const weeklyOrders = [0, 0, 0, 0, 0, 0, 0]; // Will be replaced with actual data
        
        // Real data based on actual sales performance
        window.salesData = {
            weekly: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [
                    {
                        label: 'Sales (৳)',
                        data: weeklyData,
                        borderColor: '#2e7d32',
                        backgroundColor: 'rgba(46, 125, 50, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Orders',
                        data: weeklyOrders,
                        borderColor: '#1976d2',
                        backgroundColor: 'rgba(25, 118, 210, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            monthly: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [
                    {
                        label: 'Sales (৳)',
                        data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0], // Will be replaced with actual data
                        borderColor: '#2e7d32',
                        backgroundColor: 'rgba(46, 125, 50, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Orders',
                        data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0], // Will be replaced with actual data
                        borderColor: '#1976d2',
                        backgroundColor: 'rgba(25, 118, 210, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            yearly: {
                labels: ['2020', '2021', '2022', '2023', '2024', '2025'], // Will be replaced with actual year range
                datasets: [
                    {
                        label: 'Sales (৳)',
                        data: [0, 0, 0, 0, 0, 0], // Will be replaced with actual data
                        borderColor: '#2e7d32',
                        backgroundColor: 'rgba(46, 125, 50, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Orders',
                        data: [0, 0, 0, 0, 0, 0], // Will be replaced with actual data
                        borderColor: '#1976d2',
                        backgroundColor: 'rgba(25, 118, 210, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }
                ]
            }
        };
        
        window.salesChart = new Chart(ctx, {
            type: 'line',
            data: salesData.weekly,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            font: {
                                family: "'Inter', sans-serif",
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#1e293b',
                        bodyColor: '#64748b',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        padding: 12,
                        boxPadding: 6,
                        usePointStyle: true,
                        bodyFont: {
                            family: "'Inter', sans-serif"
                        },
                        titleFont: {
                            family: "'Inter', sans-serif",
                            weight: 'bold'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: 50, // Adjusted to show small values better
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                family: "'Inter', sans-serif"
                            },
                            precision: 0
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                family: "'Inter', sans-serif"
                            }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });
        
        // Function to update chart based on selected period
        window.updateChartData = function(period) {
            // Get the corresponding data for the selected period
            const newData = window.salesData[period];
            
            // Adjust y-axis scale based on data values
            const maxSales = Math.max(...newData.datasets[0].data);
            const suggestedMax = maxSales > 0 ? Math.ceil(maxSales * 1.2) : 50;
            
            // Update scale options to better fit the data
            window.salesChart.options.scales.y.suggestedMax = suggestedMax;
            
            // Update the chart with this period's data
            window.salesChart.data = newData;
            window.salesChart.update();
        };
    }
    
    // Category Distribution Chart
    const categoryChartEl = document.getElementById('categoryChart');
    if (categoryChartEl) {
        const ctx = categoryChartEl.getContext('2d');
        
        // Sample data - this would ideally come from your backend
        window.categoryChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Vegetable', 'Fruit', 'Spice'],
                datasets: [{
                    data: [1, 0, 0],
                    backgroundColor: [
                        'rgba(76, 175, 80, 0.8)',
                        'rgba(255, 152, 0, 0.8)',
                        'rgba(233, 30, 99, 0.8)'
                    ],
                    borderColor: [
                        'rgba(76, 175, 80, 1)',
                        'rgba(255, 152, 0, 1)',
                        'rgba(233, 30, 99, 1)'
                    ],
                    borderWidth: 1,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                family: "'Inter', sans-serif",
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#1e293b',
                        bodyColor: '#64748b',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        padding: 12,
                        bodyFont: {
                            family: "'Inter', sans-serif"
                        },
                        titleFont: {
                            family: "'Inter', sans-serif",
                            weight: 'bold'
                        },
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((acc, data) => acc + data, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${percentage}% (${value} products)`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 2000,
                    easing: 'easeOutQuart'
                },
                cutout: '70%'
            }
        });
    }
    
    // Top Products Chart
    const topProductsChartEl = document.getElementById('topProductsChart');
    if (topProductsChartEl) {
        const ctx = topProductsChartEl.getContext('2d');
        
        // Sample data - this would ideally come from your backend
        window.topProductsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Potato', 'No other products sold yet'],
                datasets: [{
                    label: 'Units Sold',
                    data: [1, 0],
                    backgroundColor: [
                        'rgba(76, 175, 80, 0.7)',
                        'rgba(255, 87, 34, 0.7)',
                        'rgba(156, 39, 176, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(3, 169, 244, 0.7)'
                    ],
                    borderColor: [
                        'rgba(76, 175, 80, 1)',
                        'rgba(255, 87, 34, 1)',
                        'rgba(156, 39, 176, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(3, 169, 244, 1)'
                    ],
                    borderWidth: 1,
                    borderRadius: 6,
                    barThickness: 20,
                    maxBarThickness: 30
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        suggestedMax: 5, // Set a low max for better visibility with small values
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#1e293b',
                        bodyColor: '#64748b',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        padding: 12,
                        bodyFont: {
                            family: "'Inter', sans-serif"
                        },
                        titleFont: {
                            family: "'Inter', sans-serif",
                            weight: 'bold'
                        },
                        callbacks: {
                            label: function(context) {
                                return `Units Sold: ${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: "'Inter', sans-serif"
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                family: "'Inter', sans-serif"
                            }
                        }
                    }
                },
                animation: {
                    delay: function(context) {
                        return context.dataIndex * 100;
                    },
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
    }
}

// Ensure a last-updated badge exists on the chart card
function ensureLastUpdatedBadge() {
    const chartContainer = document.querySelector('.chart-container');
    if (!chartContainer) return;
    let badge = document.getElementById('chart-last-updated');
    if (!badge) {
        badge = document.createElement('div');
        badge.id = 'chart-last-updated';
        badge.style.position = 'absolute';
        badge.style.bottom = '10px';
        badge.style.right = '10px';
        badge.style.background = 'rgba(0,0,0,0.6)';
        badge.style.color = '#fff';
        badge.style.padding = '4px 8px';
        badge.style.borderRadius = '12px';
        badge.style.fontSize = '12px';
        badge.style.pointerEvents = 'none';
        badge.textContent = 'Last updated: —';
        chartContainer.style.position = 'relative';
        chartContainer.appendChild(badge);
    }
}

function updateLastUpdatedBadge() {
    const badge = document.getElementById('chart-last-updated');
    if (!badge) return;
    const now = new Date();
    const hh = String(now.getHours()).padStart(2, '0');
    const mm = String(now.getMinutes()).padStart(2, '0');
    const ss = String(now.getSeconds()).padStart(2, '0');
    badge.textContent = `Last updated: ${hh}:${mm}:${ss}`;
}