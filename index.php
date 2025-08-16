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
        <video src="images/banner.mp4" autoplay loop muted class="hero-video"></video>
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

<!-- Hot Deals Section -->
<section class="hot-deals-section" id="hot-deals">
  <div class="section-header">
    <div class="section-badge hot-deals-badge">
      <i class="fas fa-fire"></i>
      <span>Limited Time</span>
    </div>
    <h2 class="section-title">Hot <span class="highlight">Deals</span></h2>
    <p class="section-subtitle">Don't miss out on these amazing offers - limited time only!</p>
  </div>

  <div class="hot-deals-container">
    <div class="deals-carousel-wrapper">
      <button class="carousel-btn prev-btn" id="prevBtn">
        <i class="fas fa-chevron-left"></i>
      </button>

      <div class="deals-carousel" id="dealsCarousel">
        <div class="deals-track" id="dealsTrack">
          <?php
          // Check if hot_deals table exists first
          $table_exists = false;
          $table_check = $conn->query("SHOW TABLES LIKE 'hot_deals'");
          if ($table_check && $table_check->num_rows > 0) {
            $table_exists = true;
          }

          if ($table_exists) {
            $hot_deals_sql = "SELECT p.*, hd.original_price, hd.discount_price, hd.discount_percentage 
                               FROM products p 
                               JOIN hot_deals hd ON p.id = hd.product_id 
                               WHERE hd.is_active = 1 AND p.stock > 0 
                               AND (hd.end_date IS NULL OR hd.end_date > NOW())
                               ORDER BY hd.discount_percentage DESC, hd.created_at DESC";
            $hot_deals_result = $conn->query($hot_deals_sql);
          } else {
            $hot_deals_result = false;
          }

          if ($hot_deals_result && $hot_deals_result->num_rows > 0) {
            while ($deal = $hot_deals_result->fetch_assoc()) {
              $category_class = strtolower($deal["category"]);
              $savings = $deal["original_price"] - $deal["discount_price"];

              echo '<div class="deal-card" data-category="' . $category_class . '">';
              echo '<div class="deal-image-container">';
              echo '<img src="' . htmlspecialchars($deal["image_path"]) . '" alt="' . htmlspecialchars($deal["name"]) . '" class="deal-image">';
              echo '<div class="deal-overlay">';
              echo '<div class="deal-actions">';
              echo '<a href="product_details.php?id=' . $deal["id"] . '" class="action-btn view-btn"><i class="fas fa-eye"></i></a>';
              echo '</div>';
              echo '</div>';
              echo '<div class="discount-badge">' . $deal["discount_percentage"] . '% OFF</div>';
              echo '<div class="hot-badge"><i class="fas fa-fire"></i> HOT</div>';
              if ($deal["stock"] <= 5) {
                echo '<div class="stock-badge limited-stock"><i class="fas fa-exclamation-triangle"></i> Few Left</div>';
              }
              echo '</div>';

              echo '<div class="deal-info">';
              echo '<h4 class="deal-title">' . htmlspecialchars($deal["name"]) . '</h4>';
              echo '<div class="deal-pricing">';
              echo '<span class="original-price">৳' . number_format($deal["original_price"], 2) . '</span>';
              echo '<span class="discount-price">৳' . number_format($deal["discount_price"], 2) . '</span>';
              echo '<span class="savings">Save ৳' . number_format($savings, 2) . '</span>';
              echo '</div>';
              echo '</div>';

              echo '<form action="php/cart_manager.php" method="POST" class="deal-form">';
              echo '<input type="hidden" name="product_id" value="' . $deal["id"] . '">';
              echo '<input type="hidden" name="action" value="add">';
              echo '<input type="hidden" name="deal_price" value="' . $deal["discount_price"] . '">';
              echo '<button type="submit" class="add-to-cart-btn deal-btn">';
              echo '<i class="fas fa-shopping-cart"></i>';
              echo '<span>Add to Cart</span>';
              echo '</button>';
              echo '</form>';
              echo '</div>';
            }
          } else {
            echo '<div class="no-deals">';
            echo '<i class="fas fa-fire"></i>';
            echo '<h3>No Hot Deals Available</h3>';
            if (!$table_exists) {
              echo '<p>Hot deals feature needs to be set up. <a href="setup_hot_deals.php" style="color: #4CAF50;">Click here to set up</a></p>';
            } else {
              echo '<p>Check back soon for amazing offers!</p>';
            }
            echo '</div>';
          }
          ?>
        </div>
      </div>

      <button class="carousel-btn next-btn" id="nextBtn">
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>

    <div class="carousel-indicators" id="carouselIndicators">
      <!-- Dots will be generated by JavaScript -->
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
    $sql = "SELECT id, name, price, unit, quantity, display_unit, image_path, stock, category FROM products WHERE stock > 0 ORDER BY created_at DESC LIMIT 10";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $category_class = strtolower($row["category"]);

        // Use display_unit from the database, or format it if not set
        $price_unit = ' for ' . (isset($row["display_unit"]) ? $row["display_unit"] : (isset($row["quantity"]) ? $row["quantity"] . ' ' . $row["unit"] : $row["unit"]));

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
<section class="why-choose-section green-gradient" id="about">
  <div class="section-header">
    <div class="section-badge">
      <i class="fas fa-heart"></i>
      <span>Why Choose Us</span>
    </div>
    <h2 class="section-title">Why Choose <span class="highlight">AgroKart</span>?</h2>
    <p class="section-subtitle">We're committed to bringing you the best farm-fresh experience</p>
  </div>

  <div class="features-grid">
    <div class="feature-card" style="background: linear-gradient(135deg, #2c5f2d, #3a7c40) !important; color: white;">
      <div class="feature-icon">
        <i class="fas fa-leaf"></i>
      </div>
      <h3>Freshness Guaranteed</h3>
      <p>All our produce is sourced directly from farms to ensure top-notch freshness and quality.</p>
      <div class="feature-highlight"></div>
    </div>

    <div class="feature-card" style="background: linear-gradient(135deg, #2c5f2d, #3a7c40) !important; color: white;">
      <div class="feature-icon">
        <i class="fas fa-tags"></i>
      </div>
      <h3>Affordable Prices</h3>
      <p>We offer competitive prices that make healthy eating more accessible to everyone.</p>
      <div class="feature-highlight"></div>
    </div>

    <div class="feature-card" style="background: linear-gradient(135deg, #2c5f2d, #3a7c40) !important; color: white;">
      <div class="feature-icon">
        <i class="fas fa-truck"></i>
      </div>
      <h3>Fast Delivery</h3>
      <p>Enjoy quick and reliable delivery right to your doorstep with real-time tracking.</p>
      <div class="feature-highlight"></div>
    </div>

    <div class="feature-card" style="background: linear-gradient(135deg, #2c5f2d, #3a7c40) !important; color: white;">
      <div class="feature-icon">
        <i class="fas fa-shield-alt"></i>
      </div>
      <h3>Quality Assured</h3>
      <p>Every product undergoes strict quality checks to ensure you get only the best.</p>
      <div class="feature-highlight"></div>
    </div>

    <div class="feature-card" style="background: linear-gradient(135deg, #2c5f2d, #3a7c40) !important; color: white;">
      <div class="feature-icon">
        <i class="fas fa-users"></i>
      </div>
      <h3>Direct Farm Connection</h3>
      <p>Connect directly with local farmers, eliminating middlemen and ensuring fair prices for both farmers and customers.</p>
      <div class="feature-highlight"></div>
    </div>

    <div class="feature-card" style="background: linear-gradient(135deg, #2c5f2d, #3a7c40) !important; color: white;">
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

  /* Hot Deals Section */
  .hot-deals-section {
    padding: 60px 0;
    background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 50%, #dcedc8 100%);
    position: relative;
    overflow: hidden;
  }

  .hot-deals-badge {
    background: linear-gradient(135deg, #ff6b35, #f7931e) !important;
    animation: pulse 2s infinite;
  }

  .hot-deals-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
    position: relative;
  }

  .deals-carousel-wrapper {
    position: relative;
    overflow: hidden;
    border-radius: 20px;
    background: white;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    padding: 20px 60px;
  }

  .deals-carousel {
    overflow: hidden;
    width: 100%;
  }

  .deals-track {
    display: flex;
    transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    gap: 20px;
  }

  .deal-card {
    flex: 0 0 calc(20% - 16px);
    /* 5 cards per view */
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
    min-height: 380px;
    display: flex;
    flex-direction: column;
  }

  .deal-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
  }

  .deal-image-container {
    position: relative;
    height: 180px;
    overflow: hidden;
  }

  .deal-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
  }

  .deal-card:hover .deal-image {
    transform: scale(1.1);
  }

  .deal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 107, 53, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  .deal-card:hover .deal-overlay {
    opacity: 1;
  }

  .discount-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: linear-gradient(135deg, #ff6b35, #f7931e);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    box-shadow: 0 3px 10px rgba(255, 107, 53, 0.3);
    animation: bounce 2s infinite;
  }

  .hot-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: linear-gradient(135deg, #ff4444, #cc0000);
    color: white;
    padding: 4px 8px;
    border-radius: 15px;
    font-size: 10px;
    font-weight: 700;
    animation: fire 1.5s infinite alternate;
  }

  .deal-info {
    padding: 15px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    /* center the text block */
    text-align: center;
  }

  .deal-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 10px;
    line-height: 1.3;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    height: 2.6em;
  }

  .deal-pricing {
    margin-bottom: 15px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    align-items: center;
    /* center price lines */
  }

  .original-price {
    font-size: 0.9rem;
    color: #999;
    text-decoration: line-through;
    font-weight: 500;
  }

  .discount-price {
    font-size: 1.3rem;
    color: #ff6b35;
    font-weight: 700;
  }

  .savings {
    font-size: 0.8rem;
    color: #4CAF50;
    font-weight: 600;
    background: rgba(76, 175, 80, 0.1);
    padding: 2px 8px;
    border-radius: 10px;
    align-self: center;
    /* center savings badge */
  }

  .deal-form {
    margin-top: auto;
    padding: 0 15px 15px;
    display: flex;
    justify-content: center;
  }

  .deal-btn {
    background: linear-gradient(135deg, #ff6b35, #f7931e);
    border: none;
    color: white;
    padding: 12px 20px;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: auto;
    /* shrink to content */
    box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
  }

  .deal-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
  }

  .carousel-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: white;
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #ff6b35;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    z-index: 10;
  }

  .carousel-btn:hover {
    background: #ff6b35;
    color: white;
    transform: translateY(-50%) scale(1.1);
  }

  .prev-btn {
    left: 10px;
  }

  .next-btn {
    right: 10px;
  }

  .carousel-indicators {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 30px;
  }

  .indicator-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 107, 53, 0.3);
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .indicator-dot.active {
    background: #ff6b35;
    transform: scale(1.2);
  }

  .no-deals {
    text-align: center;
    padding: 60px 20px;
    color: #666;
    width: 100%;
  }

  .no-deals i {
    font-size: 4rem;
    color: #ff6b35;
    margin-bottom: 20px;
  }

  /* Animations */
  @keyframes bounce {

    0%,
    20%,
    50%,
    80%,
    100% {
      transform: translateY(0);
    }

    40% {
      transform: translateY(-3px);
    }

    60% {
      transform: translateY(-2px);
    }
  }

  @keyframes fire {
    0% {
      transform: scale(1) rotate(-1deg);
      filter: hue-rotate(0deg);
    }

    100% {
      transform: scale(1.05) rotate(1deg);
      filter: hue-rotate(10deg);
    }
  }

  @keyframes pulse {
    0% {
      box-shadow: 0 0 0 0 rgba(255, 107, 53, 0.7);
    }

    70% {
      box-shadow: 0 0 0 10px rgba(255, 107, 53, 0);
    }

    100% {
      box-shadow: 0 0 0 0 rgba(255, 107, 53, 0);
    }
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
    padding: 10px 15px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
  }

  .product-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 8px;
    line-height: 1.3;
    overflow: hidden;
    display: -webkit-box;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    height: auto;
    min-height: 2.6em;
  }

  .product-meta {
    margin-top: auto;
    margin-bottom: 8px;
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
    padding: 0 15px 10px;
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
    background: linear-gradient(135deg, #f1f8e9 0%, #e8f5e9 50%, #dcedc8 100%);
    position: relative;
    overflow: hidden;
  }

  .why-choose-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('images/leaf.png') no-repeat;
    background-position: -5% 105%;
    background-size: 300px;
    opacity: 0.05;
    z-index: 0;
  }

  .features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    max-width: 1200px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
    padding: 0 20px;
  }

  .feature-card {
    background: #2c5f2d !important;
    /* Force green background */
    background-image: linear-gradient(135deg, #2c5f2d, #3a7c40) !important;
    padding: 35px 25px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    border: 1px solid rgba(76, 175, 80, 0.3);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(44, 95, 45, 0.25);
    background: #3a7c40 !important;
    /* Force hover background */
    background-image: linear-gradient(135deg, #3a7c40, #4CAF50) !important;
  }

  .feature-icon {
    width: 80px;
    height: 80px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    color: #2c5f2d;
    font-size: 2rem;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    border: 3px solid rgba(255, 255, 255, 0.2);
  }

  .feature-card h3 {
    font-size: 1.3rem;
    font-weight: 700;
    color: white;
    margin-bottom: 15px;
  }

  .feature-card p {
    color: rgba(255, 255, 255, 0.85);
    line-height: 1.6;
  }

  .feature-highlight {
    display: none;
    /* Remove the underline highlight effect */
  }

  .feature-card:hover .feature-highlight {
    transform: none;
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

    .deal-card {
      flex: 0 0 calc(25% - 15px);
      /* 4 cards per view */
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

    .deal-card {
      flex: 0 0 calc(33.333% - 14px);
      /* 3 cards per view */
    }

    .features-grid {
      grid-template-columns: repeat(2, 1fr);
    }

    .deals-carousel-wrapper {
      padding: 20px 40px;
    }
  }

  @media (max-width: 768px) {
    .product-grid {
      grid-template-columns: repeat(2, 1fr);
    }

    .deal-card {
      flex: 0 0 calc(50% - 10px);
      /* 2 cards per view */
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

    .deals-carousel-wrapper {
      padding: 15px 30px;
    }

    .carousel-btn {
      width: 40px;
      height: 40px;
      font-size: 1rem;
    }
  }

  @media (max-width: 480px) {
    .product-grid {
      grid-template-columns: 1fr;
    }

    .deal-card {
      flex: 0 0 calc(100% - 0px);
      /* 1 card per view */
    }

    .hero-title {
      font-size: 1.8rem;
    }

    .deals-carousel-wrapper {
      padding: 10px 20px;
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

    // Initialize Hot Deals Carousel
    initializeHotDealsCarousel();

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

  // Hot Deals Carousel functionality
  function initializeHotDealsCarousel() {
    const track = document.getElementById('dealsTrack');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const indicatorsContainer = document.getElementById('carouselIndicators');

    if (!track) return;

    const cards = track.querySelectorAll('.deal-card');
    if (cards.length === 0) return;

    let currentIndex = 0;
    let cardsPerView = getCardsPerView();
    let maxIndex = Math.max(0, cards.length - cardsPerView);
    let autoSlideInterval;

    // Create indicators
    createIndicators();

    // Event listeners
    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        goToSlide(currentIndex - 1);
        resetAutoSlide();
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        goToSlide(currentIndex + 1);
        resetAutoSlide();
      });
    }

    // Touch/swipe support
    let startX = 0;
    let isDragging = false;

    track.addEventListener('mousedown', handleStart);
    track.addEventListener('touchstart', handleStart);
    track.addEventListener('mousemove', handleMove);
    track.addEventListener('touchmove', handleMove);
    track.addEventListener('mouseup', handleEnd);
    track.addEventListener('touchend', handleEnd);
    track.addEventListener('mouseleave', handleEnd);

    function handleStart(e) {
      isDragging = true;
      startX = e.type === 'mousedown' ? e.clientX : e.touches[0].clientX;
      track.style.cursor = 'grabbing';
      pauseAutoSlide();
    }

    function handleMove(e) {
      if (!isDragging) return;
      e.preventDefault();
    }

    function handleEnd(e) {
      if (!isDragging) return;
      isDragging = false;
      track.style.cursor = 'grab';

      const endX = e.type === 'mouseup' ? e.clientX : e.changedTouches[0].clientX;
      const diff = startX - endX;

      if (Math.abs(diff) > 50) { // Minimum swipe distance
        if (diff > 0 && currentIndex < maxIndex) {
          goToSlide(currentIndex + 1);
        } else if (diff < 0 && currentIndex > 0) {
          goToSlide(currentIndex - 1);
        }
      }

      resetAutoSlide();
    }

    function getCardsPerView() {
      const width = window.innerWidth;
      if (width <= 480) return 1;
      if (width <= 768) return 2;
      if (width <= 992) return 3;
      if (width <= 1200) return 4;
      return 5;
    }

    function createIndicators() {
      if (!indicatorsContainer) return;

      indicatorsContainer.innerHTML = '';
      const totalSlides = maxIndex + 1;

      for (let i = 0; i < totalSlides; i++) {
        const dot = document.createElement('div');
        dot.className = `indicator-dot ${i === 0 ? 'active' : ''}`;
        dot.addEventListener('click', () => {
          goToSlide(i);
          resetAutoSlide();
        });
        indicatorsContainer.appendChild(dot);
      }
    }

    function updateIndicators() {
      const dots = indicatorsContainer.querySelectorAll('.indicator-dot');
      dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentIndex);
      });
    }

    function goToSlide(index) {
      currentIndex = Math.max(0, Math.min(index, maxIndex));
      const cardWidth = cards[0].offsetWidth + 20; // card width + gap
      const translateX = -currentIndex * cardWidth;

      track.style.transform = `translateX(${translateX}px)`;
      updateIndicators();

      // Update button states
      if (prevBtn) prevBtn.disabled = currentIndex === 0;
      if (nextBtn) nextBtn.disabled = currentIndex === maxIndex;
    }

    function nextSlide() {
      if (currentIndex < maxIndex) {
        goToSlide(currentIndex + 1);
      } else {
        goToSlide(0); // Loop back to start
      }
    }

    function startAutoSlide() {
      autoSlideInterval = setInterval(nextSlide, 3000); // 3 seconds
    }

    function pauseAutoSlide() {
      clearInterval(autoSlideInterval);
    }

    function resetAutoSlide() {
      pauseAutoSlide();
      startAutoSlide();
    }

    // Handle window resize
    window.addEventListener('resize', () => {
      const newCardsPerView = getCardsPerView();
      if (newCardsPerView !== cardsPerView) {
        cardsPerView = newCardsPerView;
        maxIndex = Math.max(0, cards.length - cardsPerView);
        currentIndex = Math.min(currentIndex, maxIndex);
        createIndicators();
        goToSlide(currentIndex);
      }
    });

    // Pause on hover
    const carousel = document.querySelector('.deals-carousel-wrapper');
    if (carousel) {
      carousel.addEventListener('mouseenter', pauseAutoSlide);
      carousel.addEventListener('mouseleave', startAutoSlide);
    }

    // Initialize
    goToSlide(0);
    startAutoSlide();
  }

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
    // Find and attach event listeners to all product forms (including deal forms)
    const addToCartForms = document.querySelectorAll('.product-form, .deal-form');

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
    const cartLink = document.querySelector('.cart-link');
    const cartBadge = document.querySelector('.cart-badge');

    // Update the cart link data-count attribute
    if (cartLink) {
      cartLink.setAttribute('data-count', count);
    }

    if (count > 0) {
      if (cartBadge) {
        // If badge exists, update its content
        cartBadge.textContent = count;

        // Remove and re-add the animation class to trigger it again
        cartBadge.classList.remove('cart-badge');
        void cartBadge.offsetWidth; // Force browser reflow
        cartBadge.classList.add('cart-badge');
      } else {
        // Create new badge if it doesn't exist
        const cartWrapper = document.querySelector('.cart-wrapper');
        if (cartWrapper) {
          const newBadge = document.createElement('span');
          newBadge.className = 'cart-badge';
          newBadge.textContent = count;
          cartWrapper.appendChild(newBadge);
        }
      }
    } else if (cartBadge) {
      // Remove badge if count is 0
      cartBadge.remove();
    }
  }
</script>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php
include 'includes/footer.php';
$conn->close();
?>