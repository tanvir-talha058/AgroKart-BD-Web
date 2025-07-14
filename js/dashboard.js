document.addEventListener('DOMContentLoaded', function() {
    const addProductBtn = document.querySelector('.add-product');
    const modal = document.getElementById('addProductModal');
    const closeModalBtn = document.querySelector('.close-modal');
    if (addProductBtn) { addProductBtn.onclick = () => { modal.style.display = 'flex'; }; }
    if (closeModalBtn) { closeModalBtn.onclick = () => { modal.style.display = 'none'; }; }
    window.onclick = (event) => { if (event.target === modal) { modal.style.display = 'none'; } };
});