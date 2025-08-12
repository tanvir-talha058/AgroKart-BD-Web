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