<?php
// FILE: index.php
include 'includes/header.php';
?>
<section class="video-section">
  <video src="../images/banner.mp4" autoplay loop muted class="center-video"></video>
</section>

<section class="banner">
  <div class="banner-text">
    <h2>Fresh From Farm</h2>
    <p>Get the freshest vegetables delivered to your doorstep.</p>
    <span>Up to 50% OFF</span>
  </div>
  <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2000" style="width:420px;">
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="images/slider1.png" class="d-block w-100" alt="Farm Banner 1">
      </div>
      <div class="carousel-item">
        <img src="images/slider2.png" class="d-block w-100" alt="Farm Banner 2">
      </div>
      <div class="carousel-item">
        <img src="images/slider3.png" class="d-block w-100" alt="Farm Banner 3">
      </div>
      <div class="carousel-item">
        <img src="images/slider4.png" class="d-block w-100" alt="Farm Banner 4">
      </div>
    </div>
  </div>
  <img src="images/leaf.png" class="leaf leaf1" alt="Leaf">
  <img src="images/leaf.png" class="leaf leaf2" alt="Leaf">
</section>

<section class="product-section">
  <h2>Our Products</h2>
  <div class="product-row" id="productContainer">
    <?php
    $sql = "SELECT id, name, price, image_path, stock FROM products WHERE stock > 0 ORDER BY created_at DESC LIMIT 10";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        echo '<div class="product-card">';
        echo '<a href="product_details.php?id=' . $row["id"] . '">';
        echo '<img src="' . htmlspecialchars($row["image_path"]) . '" alt="' . htmlspecialchars($row["name"]) . '">';
        echo '<h4>' . htmlspecialchars($row["name"]) . '</h4>';
        echo '</a>';
        echo '<p><span class="price">৳' . htmlspecialchars($row["price"]) . '</span></p>';
        echo '<form action="php/cart_manager.php" method="POST">';
        echo '<input type="hidden" name="product_id" value="' . $row["id"] . '">';
        echo '<input type="hidden" name="action" value="add">';
        echo '<button type="submit" class="add-to-cart-btn">Add to Cart</button>';
        echo '</form>';
        echo '</div>';
      }
    } else {
      echo "<p>No products available at the moment.</p>";
    }
    ?>
  </div>

  <?php
  // Check if there are more than 10 products
  $count_sql = "SELECT COUNT(*) as total FROM products WHERE stock > 0";
  $count_result = $conn->query($count_sql);
  $total_products = $count_result->fetch_assoc()['total'];

  if ($total_products > 10) {
    echo '<div class="show-more-container">';
    echo '<button id="showMoreBtn" class="show-more-btn">Show More</button>';
    echo '</div>';
  }
  ?>
</section>

<section class="why-choose-section">
  <h2 class="why-choose-title">Why Choose AgroKart?</h2>
  <div class="why-choose-row">
    <div class="why-choose-card">
      <h3>Freshness Guaranteed</h3>
      <p>All our produce is sourced directly from farms to ensure top-notch freshness.</p>
    </div>
    <div class="why-choose-card">
      <h3>Affordable Prices</h3>
      <p>We offer competitive prices that make healthy eating more accessible.</p>
    </div>
    <div class="why-choose-card">
      <h3>Fast Delivery</h3>
      <p>Enjoy quick and reliable delivery right to your doorstep.</p>
    </div>
  </div>
</section>

<style>
  .show-more-container {
    text-align: center;
    margin-top: 40px;
    padding: 20px 0;
  }

  .show-more-btn {
    background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
    color: white;
    border: none;
    padding: 15px 40px;
    border-radius: 25px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
  }

  .show-more-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
    background: linear-gradient(135deg, #45a049 0%, #388e3c 100%);
  }

  .show-more-btn:active {
    transform: translateY(0);
  }

  .show-more-btn.loading {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
  }

  .product-row {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 20px;
    margin-bottom: 20px;
  }

  @media (max-width: 1200px) {
    .product-row {
      grid-template-columns: repeat(4, 1fr);
    }
  }

  @media (max-width: 992px) {
    .product-row {
      grid-template-columns: repeat(3, 1fr);
    }
  }

  @media (max-width: 768px) {
    .product-row {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 480px) {
    .product-row {
      grid-template-columns: 1fr;
    }
  }
</style>

<script>
  // Set category dropdown to "All Categories" when on main page
  document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('categorySelect');
    if (categorySelect) {
      categorySelect.value = '';
    }

    // Show More functionality
    const showMoreBtn = document.getElementById('showMoreBtn');
    if (showMoreBtn) {
      showMoreBtn.addEventListener('click', function() {
        loadAllProducts();
      });
    }
  });

  function loadAllProducts() {
    const showMoreBtn = document.getElementById('showMoreBtn');
    const productContainer = document.getElementById('productContainer');

    // Show loading state
    showMoreBtn.textContent = 'Loading...';
    showMoreBtn.classList.add('loading');

    // Make AJAX request to load all products
    fetch('php/load_all_products.php')
      .then(response => response.text())
      .then(data => {
        // Replace the product container content with all products
        productContainer.innerHTML = data;

        // Hide the show more button
        showMoreBtn.style.display = 'none';
      })
      .catch(error => {
        console.error('Error loading products:', error);
        showMoreBtn.textContent = 'Show More';
        showMoreBtn.classList.remove('loading');
      });
  }
</script>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php
include 'includes/footer.php';
$conn->close();
?>