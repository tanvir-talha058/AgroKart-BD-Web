<?php
// FILE: index.php
include 'includes/header.php';
?>

<!-- Hero Section with Enhanced Design -->
<section class="hero-section">
  <div class="hero-background">
    <div class="floating-elements">
      <div class="floating-leaf leaf-1"></div>
      <div class="floating-leaf leaf-2"></div>
      <div class="floating-leaf leaf-3"></div>
      <div class="floating-circle circle-1"></div>
      <div class="floating-circle circle-2"></div>
    </div>
  </div>

  <div class="hero-content">
    <div class="hero-text">
      <div class="hero-badge">
        <i class="fas fa-leaf"></i>
        <span>Fresh & Organic</span>
      </div>
      <h1 class="hero-title">Fresh From <span class="highlight">Farm</span></h1>
      <p class="hero-subtitle">Get the freshest vegetables delivered to your doorstep with guaranteed quality and freshness.</p>
      <div class="hero-features">
        <div class="feature">
          <i class="fas fa-truck"></i>
          <span>Free Delivery</span>
        </div>
        <div class="feature">
          <i class="fas fa-clock"></i>
          <span>Same Day</span>
        </div>
        <div class="feature">
          <i class="fas fa-shield-alt"></i>
          <span>Quality Assured</span>
        </div>
      </div>
      <div class="hero-cta">
        <a href="#products" class="cta-primary">Shop Now</a>
        <a href="#about" class="cta-secondary">Learn More</a>
      </div>
    </div>

    <div class="hero-visual">
      <div class="video-container">
        <video src="../images/banner.mp4" autoplay loop muted class="hero-video"></video>
        <div class="video-overlay"></div>
      </div>
      <div class="carousel-container">
        <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
          <div class="carousel-inner">
            <div class="carousel-item active">
              <img src="images/slider1.png" class="carousel-img" alt="Fresh Vegetables">
            </div>
            <div class="carousel-item">
              <img src="images/slider2.png" class="carousel-img" alt="Organic Fruits">
            </div>
            <div class="carousel-item">
              <img src="images/slider3.png" class="carousel-img" alt="Fresh Herbs">
            </div>
            <div class="carousel-item">
              <img src="images/slider4.png" class="carousel-img" alt="Farm Fresh">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Enhanced Products Section -->
<section class="product-section" id="products">
  <div class="section-header">
    <div class="section-badge">
      <i class="fas fa-star"></i>
      <span>Featured</span>
    </div>
    <h2 class="section-title">Our <span class="highlight">Products</span></h2>
    <p class="section-subtitle">Discover our handpicked selection of fresh, organic produce</p>
  </div>

  <div class="product-grid" id="productContainer">
    <?php
    $sql = "SELECT id, name, price, image_path, stock, category FROM products WHERE stock > 0 ORDER BY created_at DESC LIMIT 10";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $category_class = strtolower($row["category"]);

        // Determine pricing unit based on category
        $price_unit = '';
        switch (strtolower($row["category"])) {
          case 'vegetable':
          case 'fruit':
            $price_unit = '/kg';
            break;
          case 'spice':
            $price_unit = '/gm';
            break;
          default:
            $price_unit = '';
        }

        echo '<div class="product-card" data-category="' . $category_class . '">';
        echo '<div class="product-image-container">';
        echo '<img src="' . htmlspecialchars($row["image_path"]) . '" alt="' . htmlspecialchars($row["name"]) . '" class="product-image">';
        echo '<div class="product-overlay">';
        echo '<div class="product-actions">';
        echo '<a href="product_details.php?id=' . $row["id"] . '" class="action-btn view-btn"><i class="fas fa-eye"></i></a>';
        echo '</div>';
        echo '</div>';
        echo '<div class="category-badge ' . $category_class . '">' . htmlspecialchars($row["category"]) . '</div>';
        if ($row["stock"] <= 5) {
          echo '<div class="stock-badge limited-stock"><i class="fas fa-exclamation-triangle"></i> Limited Stock</div>';
        }
        echo '</div>';
        echo '<div class="product-info">';
        echo '<h4 class="product-title">' . htmlspecialchars($row["name"]) . '</h4>';
        echo '<div class="product-meta">';
        echo '<span class="price">৳' . htmlspecialchars($row["price"]) . '<span class="price-unit">' . $price_unit . '</span></span>';
        echo '</div>';
        echo '</div>';
        echo '<form action="php/cart_manager.php" method="POST" class="product-form">';
        echo '<input type="hidden" name="product_id" value="' . $row["id"] . '">';
        echo '<input type="hidden" name="action" value="add">';
        echo '<button type="submit" class="add-to-cart-btn">';
        echo '<i class="fas fa-shopping-cart"></i>';
        echo '<span>Add to Cart</span>';
        echo '</button>';
        echo '</form>';
        echo '</div>';
      }
    } else {
      echo '<div class="no-products">';
      echo '<i class="fas fa-seedling"></i>';
      echo '<h3>No products available</h3>';
      echo '<p>Check back soon for fresh arrivals!</p>';
      echo '</div>';
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
    echo '<button id="showMoreBtn" class="show-more-btn">';
    echo '<i class="fas fa-plus"></i>';
    echo '<span>Show More Products</span>';
    echo '</button>';
    echo '</div>';
  }
  ?>
</section>

<!-- Enhanced Why Choose Section -->
<section class="why-choose-section" id="about">
  <div class="section-header">
    <div class="section-badge">
      <i class="fas fa-heart"></i>
      <span>Why Choose Us</span>
    </div>
    <h2 class="section-title">Why Choose <span class="highlight">AgroKart</span>?</h2>
    <p class="section-subtitle">We're committed to bringing you the best farm-fresh experience</p>
  </div>

  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon">
        <i class="fas fa-leaf"></i>
      </div>
      <h3>Freshness Guaranteed</h3>
      <p>All our produce is sourced directly from farms to ensure top-notch freshness and quality.</p>
      <div class="feature-highlight"></div>
    </div>

    <div class="feature-card">
      <div class="feature-icon">
        <i class="fas fa-tags"></i>
      </div>
      <h3>Affordable Prices</h3>
      <p>We offer competitive prices that make healthy eating more accessible to everyone.</p>
      <div class="feature-highlight"></div>
    </div>

    <div class="feature-card">
      <div class="feature-icon">
        <i class="fas fa-truck"></i>
      </div>
      <h3>Fast Delivery</h3>
      <p>Enjoy quick and reliable delivery right to your doorstep with real-time tracking.</p>
      <div class="feature-highlight"></div>
    </div>

    <div class="feature-card">
      <div class="feature-icon">
        <i class="fas fa-shield-alt"></i>
      </div>
      <h3>Quality Assured</h3>
      <p>Every product undergoes strict quality checks to ensure you get only the best.</p>
      <div class="feature-highlight"></div>
    </div>

    <div class="feature-card">
      <div class="feature-icon">
        <i class="fas fa-users"></i>
      </div>
      <h3>Direct Farm Connection</h3>
      <p>Connect directly with local farmers, eliminating middlemen and ensuring fair prices for both farmers and customers.</p>
      <div class="feature-highlight"></div>
    </div>

    <div class="feature-card">
      <div class="feature-icon">
        <i class="fas fa-mobile-alt"></i>
      </div>
      <h3>Easy Online Ordering</h3>
      <p>Simple and secure online platform for browsing, ordering, and tracking your fresh produce with just a few clicks.</p>
      <div class="feature-highlight"></div>
    </div>
  </div>
</section>

<!-- Enhanced CSS Styles -->
<style>
  /* Hero Section */
  .hero-section {
    position: relative;
    min-height: 100vh;
    background: linear-gradient(135deg, #f8fff9 0%, #e8f5e8 50%, #d4edda 100%);
    overflow: hidden;
    display: flex;
    align-items: center;
  }

  .hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
  }

  .floating-elements {
    position: absolute;
    width: 100%;
    height: 100%;
  }

  .floating-leaf {
    position: absolute;
    width: 60px;
    height: 60px;
    background: url('images/leaf.png') no-repeat center/contain;
    opacity: 0.3;
    animation: float 6s ease-in-out infinite;
  }

  .leaf-1 {
    top: 20%;
    left: 10%;
    animation-delay: 0s;
  }

  .leaf-2 {
    top: 60%;
    right: 15%;
    animation-delay: 2s;
  }

  .leaf-3 {
    bottom: 20%;
    left: 20%;
    animation-delay: 4s;
  }

  .floating-circle {
    position: absolute;
    border-radius: 50%;
    background: linear-gradient(45deg, #4CAF50, #8BC34A);
    opacity: 0.1;
    animation: pulse 4s ease-in-out infinite;
  }

  .circle-1 {
    width: 100px;
    height: 100px;
    top: 30%;
    right: 30%;
  }

  .circle-2 {
    width: 150px;
    height: 150px;
    bottom: 30%;
    left: 30%;
    animation-delay: 2s;
  }

  @keyframes float {

    0%,
    100% {
      transform: translateY(0px) rotate(0deg);
    }

    50% {
      transform: translateY(-20px) rotate(180deg);
    }
  }

  @keyframes pulse {

    0%,
    100% {
      transform: scale(1);
      opacity: 0.1;
    }

    50% {
      transform: scale(1.1);
      opacity: 0.2;
    }
  }

  .hero-content {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
  }

  .hero-text {
    max-width: 500px;
  }

  .hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #4CAF50, #8BC34A);
    color: white;
    padding: 8px 16px;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
  }

  .hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 20px;
    line-height: 1.2;
  }

  .highlight {
    background: linear-gradient(135deg, #4CAF50, #8BC34A);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .hero-subtitle {
    font-size: 1.2rem;
    color: #666;
    margin-bottom: 30px;
    line-height: 1.6;
  }

  .hero-features {
    display: flex;
    gap: 20px;
    margin-bottom: 40px;
  }

  .feature {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #4CAF50;
    font-weight: 600;
  }

  .hero-cta {
    display: flex;
    gap: 20px;
  }

  .cta-primary {
    background: linear-gradient(135deg, #4CAF50, #8BC34A);
    color: white;
    padding: 15px 30px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
  }

  .cta-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
    color: white;
  }

  .cta-secondary {
    background: transparent;
    color: #4CAF50;
    padding: 15px 30px;
    border: 2px solid #4CAF50;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .cta-secondary:hover {
    background: #4CAF50;
    color: white;
  }

  .hero-visual {
    position: relative;
  }

  .video-container {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  }

  .hero-video {
    width: 100%;
    height: auto;
    display: block;
  }

  .video-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, rgba(76, 175, 80, 0.1), rgba(139, 195, 74, 0.1));
  }

  .carousel-container {
    margin-top: 30px;
  }

  .carousel-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 15px;
  }

  /* Section Headers */
  .section-header {
    text-align: center;
    margin-bottom: 40px;
  }

  .section-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #4CAF50, #8BC34A);
    color: white;
    padding: 8px 16px;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
  }

  .section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 15px;
  }

  .section-subtitle {
    font-size: 1.1rem;
    color: #666;
    max-width: 600px;
    margin: 0 auto;
  }

  /* Product Section */
  .product-section {
    padding: 80px 0;
    background: #f8fff9;
  }

  .product-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 25px;
    margin-bottom: 40px;
    padding: 0 20px;
    max-width: 1400px;
    margin-left: auto;
    margin-right: auto;
  }

  .product-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
    margin: 0;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    height: 100%;
    /* Set fixed height for cards */
  }

  .product-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  }

  .product-image-container {
    position: relative;
    height: 200px;
    overflow: hidden;
  }

  .product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
  }

  .product-card:hover .product-image {
    transform: scale(1.1);
  }

  .product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(76, 175, 80, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  .product-card:hover .product-overlay {
    opacity: 1;
  }

  .product-actions {
    display: flex;
    gap: 15px;
  }

  .action-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: white;
    color: #4CAF50;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  }

  .action-btn:hover {
    transform: scale(1.1);
    color: #4CAF50;
  }

  .category-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    color: white;
  }

  .category-badge.vegetable {
    background: #4CAF50;
  }

  .category-badge.fruit {
    background: #FF9800;
  }

  .category-badge.spice {
    background: #9C27B0;
  }

  .product-info {
    padding: 15px 20px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
  }

  .product-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 15px;
    line-height: 1.4;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    height: auto;
    min-height: 2.8em;
  }

  .product-meta {
    margin-top: auto;
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .price {
    font-size: 1.2rem;
    font-weight: 700;
    color: #4CAF50;
  }

  .price-unit {
    font-size: 0.9rem;
    color: #666;
    font-weight: 500;
    margin-left: 2px;
  }

  .product-form {
    margin-top: auto;
    padding: 0 20px 15px;
  }

  /* Enhanced Cart Badge Animation */
  .cart-badge {
    animation: cartBadgePulse 0.3s ease-in-out;
  }

  @keyframes cartBadgePulse {
    0% {
      transform: scale(1);
    }

    50% {
      transform: scale(1.2);
    }

    100% {
      transform: scale(1);
    }
  }

  /* Add to cart button feedback */
  .add-to-cart-btn:active {
    transform: scale(0.95);
  }

  .add-to-cart-btn.added {
    background: linear-gradient(135deg, #4CAF50, #2E7D32);
    animation: addedFeedback 0.6s ease-in-out;
  }

  @keyframes addedFeedback {
    0% {
      transform: scale(1);
    }

    50% {
      transform: scale(1.05);
      background: linear-gradient(135deg, #66BB6A, #4CAF50);
    }

    100% {
      transform: scale(1);
    }
  }

  .no-products {
    text-align: center;
    padding: 60px 20px;
    color: #666;
  }

  .no-products i {
    font-size: 4rem;
    color: #4CAF50;
    margin-bottom: 20px;
  }

  /* Show More Button */
  .show-more-container {
    text-align: center;
    margin-top: 60px;
  }

  .show-more-btn {
    background: linear-gradient(135deg, #4CAF50, #8BC34A);
    color: white;
    border: none;
    padding: 18px 40px;
    border-radius: 30px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
    display: inline-flex;
    align-items: center;
    gap: 10px;
  }

  .show-more-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
  }

  .show-more-btn.loading {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
  }

  /* Why Choose Section */
  .why-choose-section {
    padding: 80px 0;
    background: white;
  }

  .features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
  }

  .feature-card {
    background: white;
    padding: 35px 25px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  }

  .feature-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #4CAF50, #8BC34A);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    color: white;
    font-size: 2rem;
    box-shadow: 0 10px 30px rgba(76, 175, 80, 0.3);
  }

  .feature-card h3 {
    font-size: 1.3rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 15px;
  }

  .feature-card p {
    color: #666;
    line-height: 1.6;
  }

  .feature-highlight {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(135deg, #4CAF50, #8BC34A);
    transform: scaleX(0);
    transition: transform 0.3s ease;
  }

  .feature-card:hover .feature-highlight {
    transform: scaleX(1);
  }

  /* Notification Styles */
  .cart-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    padding: 15px 20px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    transform: translateX(120%);
    transition: transform 0.3s ease;
  }

  .cart-notification.show {
    transform: translateX(0);
  }

  .cart-notification.success {
    border-left: 4px solid #4CAF50;
  }

  .cart-notification.error {
    border-left: 4px solid #f44336;
  }

  .notification-content {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .notification-content i {
    font-size: 1.5rem;
  }

  .cart-notification.success i {
    color: #4CAF50;
  }

  .cart-notification.error i {
    color: #f44336;
  }

  /* Responsive Design */
  @media (max-width: 1200px) {
    .product-grid {
      grid-template-columns: repeat(4, 1fr);
    }
  }

  @media (max-width: 992px) {
    .hero-content {
      grid-template-columns: 1fr;
      gap: 40px;
      text-align: center;
    }

    .hero-title {
      font-size: 2.5rem;
    }

    .hero-features {
      justify-content: center;
    }

    .product-grid {
      grid-template-columns: repeat(3, 1fr);
    }

    .features-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 768px) {
    .product-grid {
      grid-template-columns: repeat(2, 1fr);
    }

    .features-grid {
      grid-template-columns: 1fr;
    }

    .hero-title {
      font-size: 2rem;
    }

    .hero-features {
      flex-direction: column;
      gap: 15px;
    }

    .hero-cta {
      flex-direction: column;
      gap: 15px;
    }
  }

  @media (max-width: 480px) {
    .product-grid {
      grid-template-columns: 1fr;
    }

    .hero-title {
      font-size: 1.8rem;
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

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });
  });

  function loadAllProducts() {
    const showMoreBtn = document.getElementById('showMoreBtn');
    const productContainer = document.getElementById('productContainer');

    // Show loading state
    showMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Loading...</span>';
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
        showMoreBtn.innerHTML = '<i class="fas fa-plus"></i><span>Show More Products</span>';
        showMoreBtn.classList.remove('loading');
      });
  }

  // AJAX Cart functionality
  document.addEventListener('DOMContentLoaded', function() {
    // Find and attach event listeners to all product forms
    const addToCartForms = document.querySelectorAll('.product-form');

    addToCartForms.forEach(form => {
      form.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent the normal form submission

        const formData = new FormData(this);
        formData.append('ajax', '1'); // Add AJAX flag

        const button = this.querySelector('.add-to-cart-btn');
        const originalContent = button.innerHTML;

        // Show loading state
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Adding...</span>';
        button.disabled = true;

        fetch('php/cart_manager.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Show success notification
              showNotification(data.message, 'success');

              // Update cart count
              updateCartCount(data.cart_count);

              // Show success UI on button
              button.innerHTML = '<i class="fas fa-check"></i><span>Added!</span>';
              button.classList.add('added');

              // Restore original button after delay
              setTimeout(() => {
                button.innerHTML = originalContent;
                button.classList.remove('added');
                button.disabled = false;
              }, 2000);
            } else {
              // Show error notification
              showNotification(data.message, 'error');
              button.innerHTML = originalContent;
              button.disabled = false;
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred. Please try again.', 'error');
            button.innerHTML = originalContent;
            button.disabled = false;
          });
      });
    });
  });

  // Make sure the notification functions are defined
  function showNotification(message, type) {
    // Remove any existing notifications
    const existingNotifications = document.querySelectorAll('.cart-notification');
    existingNotifications.forEach(notification => notification.remove());

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `cart-notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;

    // Add to page
    document.body.appendChild(notification);

    // Show notification
    setTimeout(() => notification.classList.add('show'), 100);

    // Hide notification after 3 seconds
    setTimeout(() => {
      notification.classList.remove('show');
      setTimeout(() => notification.remove(), 300);
    }, 3000);
  }

  // Update cart count in the header
  function updateCartCount(count) {
    // Target all possible cart counter elements more precisely
    const cartIcon = document.querySelector('.cart-icon');
    const cartCountBadge = document.querySelector('.cart-icon[data-count]');
    const cartText = document.querySelector('.cart-text .cart-count');
    
    // Update the main cart icon in header
    if (cartIcon) {
      cartIcon.setAttribute('data-count', count);
    }
    
    // Update any text-based counter
    if (cartText) {
      cartText.textContent = count;
    }
    
    // Add animation effect
    if (cartCountBadge) {
      // Remove and re-add the animation class to trigger it again
      cartCountBadge.classList.remove('cart-badge');
      
      // Force browser reflow to restart animation
      void cartCountBadge.offsetWidth;
      
      // Add animation class
      cartCountBadge.classList.add('cart-badge');
    }
  }
</script>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php
include 'includes/footer.php';
$conn->close();
?>