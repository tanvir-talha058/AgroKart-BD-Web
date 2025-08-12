document.addEventListener('DOMContentLoaded', function() {
    // Modal functionality
    const addProductBtn = document.querySelector('.add-product');
    const modal = document.getElementById('addProductModal');
    const closeModalBtn = document.querySelector('.close-modal');
    
    if (addProductBtn) { 
        addProductBtn.onclick = () => { 
            modal.style.display = 'flex';
            // Add a small delay before adding the class to trigger animation
            setTimeout(() => {
                document.body.classList.add('modal-open');
            }, 10);
        }; 
    }
    
    if (closeModalBtn) { 
        closeModalBtn.onclick = () => { 
            document.body.classList.remove('modal-open');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300); // Match this delay with your CSS transition time
        }; 
    }
    
    window.onclick = (event) => { 
        if (event.target === modal) { 
            document.body.classList.remove('modal-open');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        } 
    };
    
    // Add animations to stat cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('animated');
        }, 100 * index);
    });
    
    // Add icon to Add Product button
    const addProductButton = document.querySelector('.add-product');
    if (addProductButton && !addProductButton.querySelector('i')) {
        addProductButton.innerHTML = '<i class="fas fa-plus"></i> Add New Product';
    }
    
    // Chart.js configuration
    initializeCharts();
    
    // Chart tabs functionality
    const chartTabs = document.querySelectorAll('.chart-tab');
    if (chartTabs.length > 0) {
        chartTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                chartTabs.forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Update chart data based on selected period
                updateChartData(this.getAttribute('data-period'));
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

// Initialize charts
function initializeCharts() {
    // Sales chart
    const salesChartEl = document.getElementById('salesChart');
    if (salesChartEl) {
        const ctx = salesChartEl.getContext('2d');
        
        // Sample data - this would ideally come from your backend
        const salesData = {
            weekly: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [
                    {
                        label: 'Sales (৳)',
                        data: [1200, 1900, 800, 1400, 1800, 2200, 1600],
                        borderColor: '#2e7d32',
                        backgroundColor: 'rgba(46, 125, 50, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Orders',
                        data: [5, 8, 3, 6, 7, 9, 6],
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
                        data: [5000, 7000, 6000, 8000, 9500, 8500, 10000, 11000, 9000, 8000, 10000, 12000],
                        borderColor: '#2e7d32',
                        backgroundColor: 'rgba(46, 125, 50, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Orders',
                        data: [20, 28, 25, 32, 38, 34, 40, 45, 36, 32, 40, 48],
                        borderColor: '#1976d2',
                        backgroundColor: 'rgba(25, 118, 210, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            yearly: {
                labels: ['2020', '2021', '2022', '2023', '2024', '2025'],
                datasets: [
                    {
                        label: 'Sales (৳)',
                        data: [60000, 85000, 110000, 150000, 190000, 120000],
                        borderColor: '#2e7d32',
                        backgroundColor: 'rgba(46, 125, 50, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Orders',
                        data: [240, 340, 440, 600, 760, 480],
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
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
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
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });
        
        // Function to update chart based on selected period
        window.updateChartData = function(period) {
            window.salesChart.data = salesData[period];
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
                    data: [45, 35, 20],
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
                labels: ['Fresh Tomatoes', 'Organic Apples', 'Red Potatoes', 'Turmeric Powder', 'Green Peppers'],
                datasets: [{
                    label: 'Units Sold',
                    data: [85, 72, 65, 53, 48],
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