<?php
// No output before this line
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once 'db_connect.php';

// Check if user is logging in and needs to load their saved cart from the database
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
  // Check if we need to load the cart (either first time login or forced reload)
  if (!isset($_SESSION['cart_loaded']) || $_SESSION['cart_loaded'] === false) {
    // Load user's cart from the database
    $user_id = $_SESSION['user_id'];

    // First clear any existing cart - important to prevent cart data mixing between users
    $_SESSION['cart'] = [];

    // Get cart items from database (include stored price)
    $cart_query = "SELECT uc.product_id, uc.quantity, uc.price AS cart_price, p.name, p.price AS product_price, p.unit, p.image_path, p.stock 
          FROM user_cart uc 
          JOIN products p ON uc.product_id = p.id 
          WHERE uc.user_id = ?";
    $stmt = $conn->prepare($cart_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Add database items to session cart
    while ($item = $result->fetch_assoc()) {
      $product_id = $item['product_id'];
      $price_to_use = isset($item['cart_price']) && $item['cart_price'] > 0 ? (float)$item['cart_price'] : (float)$item['product_price'];
      $_SESSION['cart'][$product_id] = [
        'name' => $item['name'],
        'price' => $price_to_use,
        'unit' => $item['unit'],
        'image' => $item['image_path'],
        'quantity' => $item['quantity'],
        'stock' => $item['stock'],
        'is_deal' => $price_to_use < (float)$item['product_price'],
        'original_price' => $price_to_use < (float)$item['product_price'] ? (float)$item['product_price'] : null,
      ];
    }
    $stmt->close();

    // Mark cart as loaded to prevent loading it on every page refresh
    $_SESSION['cart_loaded'] = true;
  }
} // Calculate cart count
$cart_count = 0;
if (isset($_SESSION['cart'])) {
  $cart_count = count($_SESSION['cart']); // Count unique products instead of quantities
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AgroKart BD</title>
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/form-style.css">
  <link rel="stylesheet" href="css/cart-style.css">
  <link rel="stylesheet" href="css/chatbot.css">
  <link rel="stylesheet" href="css/modern-chatbot.css">
  <link rel="icon" type="image/x-icon" href="images/AGrO.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <!-- Enhanced Navigation Bar -->
  <header class="navbar-enhanced">
    <div class="nav-container">
      <!-- Logo Section -->
      <div class="nav-logo">
        <a href="index.php" class="logo-link">
          <div class="logo-wrapper">
            <img src="images/AGrO.png" alt="AgroKart BD Logo" class="logo-img">
            <div class="logo-text">
              <span class="logo-title">AgroKart</span>
              <span class="logo-subtitle">BD</span>
            </div>
          </div>
        </a>
      </div>

      <!-- Search Section -->
      <div class="nav-search">
        <form class="search-form" action="search.php" method="get">
          <div class="search-wrapper">
            <div class="search-icon">
              <i class="fas fa-search"></i>
            </div>
            <input type="text" name="q" placeholder="Search for fresh produce..." class="search-input" />
            <button type="submit" class="search-btn">
              <i class="fas fa-arrow-right"></i>
            </button>
          </div>
        </form>
      </div>

      <!-- Category Dropdown -->
      <div class="nav-category">
        <div class="category-wrapper">
          <div class="category-icon">
            <i class="fas fa-filter"></i>
          </div>
          <select id="categorySelect" class="category-select" onchange="filterByCategory()">
            <option value="">All Categories</option>
            <option value="Vegetable">🥬 Vegetables</option>
            <option value="Fruit">🍎 Fruits</option>
            <option value="Spice">🌶️ Spices</option>
          </select>
          <div class="category-arrow">
            <i class="fas fa-chevron-down"></i>
          </div>
        </div>
      </div>

      <!-- Navigation Links -->
      <nav class="nav-links">
        <a href="index.php" class="nav-link">
          <i class="fas fa-home"></i>
          <span>Home</span>
        </a>
        <a href="my_orders.php" class="nav-link">
          <i class="fas fa-box"></i>
          <span>Orders</span>
        </a>

        <!-- Cart Icon with Badge -->
        <a href="cart.php" class="nav-link cart-link" data-count="<?php echo $cart_count; ?>">
          <div class="cart-wrapper">
            <i class="fas fa-shopping-basket"></i>
            <span class="cart-text">Cart</span>
            <?php if ($cart_count > 0): ?>
              <span class="cart-badge"><?php echo $cart_count; ?></span>
            <?php endif; ?>
          </div>
        </a>

        <!-- User Profile Section -->
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
          <div class="nav-profile">
            <div class="profile-dropdown">
              <button class="profile-btn">
                <div class="profile-avatar">
                  <img src="<?php echo isset($_SESSION['profile_image_path']) ? htmlspecialchars($_SESSION['profile_image_path']) : 'images/default-profile.png'; ?>" class="profile-pic" alt="Profile">
                  <div class="profile-status"></div>
                </div>
                <div class="profile-info">
                  <span class="profile-name"><?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?></span>
                </div>
                <i class="fas fa-chevron-down profile-arrow"></i>
              </button>
              <div class="profile-menu">
                <a href="profile.php" class="profile-menu-item">
                  <i class="fas fa-user-edit"></i>
                  <span>Edit Profile</span>
                </a>
                <a href="my_orders.php" class="profile-menu-item">
                  <i class="fas fa-shopping-bag"></i>
                  <span>My Orders</span>
                </a>
                <a href="loyalty_program.php" class="profile-menu-item">
                  <i class="fas fa-crown"></i>
                  <span>Loyalty Program</span>
                </a>
                <a href="quick_reorder.php" class="profile-menu-item">
                  <i class="fas fa-redo-alt"></i>
                  <span>Quick Reorder</span>
                </a>
                <a href="notifications.php" class="profile-menu-item">
                  <i class="fas fa-bell"></i>
                  <span>Notifications</span>
                </a>
                <?php if ($_SESSION['user_role'] == 'Seller'): ?>
                  <a href="dashboard.php" class="profile-menu-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Dashboard</span>
                  </a>
                <?php endif; ?>
                <div class="profile-menu-divider"></div>
                <a href="php/logout.php" class="profile-menu-item logout-item">
                  <i class="fas fa-sign-out-alt"></i>
                  <span>Logout</span>
                </a>
              </div>
            </div>
          </div>
        <?php else: ?>
          <a href="login.php" class="nav-link auth-link">
            <i class="fas fa-user-circle"></i>
            <span>Login</span>
          </a>
        <?php endif; ?>
      </nav>

      <!-- Mobile Menu Toggle -->
      <div class="nav-mobile-toggle">
        <div class="hamburger">
          <span></span>
          <span></span>
          <span></span>
        </div>
      </div>
    </div>
  </header>

  <!-- Chatbot Container -->
  <div id="chatbot-container"></div>

  <!-- Enhanced Navbar Styles -->
  <style>
    /* Enhanced Navbar */
    .navbar-enhanced {
      background: linear-gradient(135deg, #2c5f2d 0%, #4CAF50 50%, #8BC34A 100%);
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 0;
      z-index: 1000;
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .nav-container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 70px;
    }

    /* Logo Section */
    .nav-logo {
      flex-shrink: 0;
    }

    .logo-link {
      text-decoration: none;
      color: white;
      display: flex;
      align-items: center;
    }

    .logo-wrapper {
      display: flex;
      align-items: center;
      gap: 12px;
      transition: transform 0.3s ease;
    }

    .logo-wrapper:hover {
      transform: scale(1.05);
    }

    .logo-img {
      width: 40px;
      height: 40px;
      filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
    }

    .logo-text {
      display: flex;
      flex-direction: column;
      line-height: 1;
    }

    .logo-title {
      font-size: 1.5rem;
      font-weight: 800;
      color: white;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .logo-subtitle {
      font-size: 0.8rem;
      font-weight: 600;
      color: #e8f5e8;
      letter-spacing: 1px;
    }

    /* Search Section */
    .nav-search {
      flex: 1;
      max-width: 500px;
      margin: 0 30px;
    }

    .search-form {
      width: 100%;
    }

    .search-wrapper {
      position: relative;
      display: flex;
      align-items: center;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 25px;
      padding: 8px 16px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .search-wrapper:hover,
    .search-wrapper:focus-within {
      background: white;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
      transform: translateY(-1px);
    }

    .search-icon {
      color: #4CAF50;
      margin-right: 12px;
      font-size: 16px;
    }

    .search-input {
      flex: 1;
      border: none;
      background: transparent;
      font-size: 14px;
      color: #333;
      outline: none;
      padding: 8px 0;
    }

    .search-input::placeholder {
      color: #999;
    }

    .search-btn {
      background: linear-gradient(135deg, #4CAF50, #8BC34A);
      border: none;
      color: white;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-left: 8px;
    }

    .search-btn:hover {
      transform: scale(1.1);
      box-shadow: 0 4px 15px rgba(76, 175, 80, 0.4);
    }

    /* Category Dropdown */
    .nav-category {
      margin: 0 20px;
    }

    .category-wrapper {
      position: relative;
      display: flex;
      align-items: center;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 20px;
      padding: 8px 16px;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s ease;
    }

    .category-wrapper:hover {
      background: rgba(255, 255, 255, 0.2);
      transform: translateY(-1px);
    }

    .category-icon {
      color: white;
      margin-right: 8px;
      font-size: 14px;
    }

    .category-select {
      background: transparent;
      border: none;
      color: white;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      outline: none;
      padding-right: 20px;
      min-width: 120px;
    }

    .category-select option {
      background: white;
      color: #333;
    }

    .category-arrow {
      color: white;
      font-size: 12px;
      margin-left: 8px;
      transition: transform 0.3s ease;
    }

    .category-wrapper:hover .category-arrow {
      transform: rotate(180deg);
    }

    /* Navigation Links */
    .nav-links {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .nav-link {
      display: flex;
      align-items: center;
      gap: 8px;
      color: white;
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
      padding: 10px 16px;
      border-radius: 20px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .nav-link::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s ease;
    }

    .nav-link:hover::before {
      left: 100%;
    }

    .nav-link:hover {
      background: rgba(255, 255, 255, 0.1);
      transform: translateY(-2px);
    }

    .nav-link i {
      font-size: 16px;
    }

    /* Cart Link */
    .cart-link {
      position: relative;
    }

    .cart-wrapper {
      display: flex;
      align-items: center;
      gap: 8px;
      position: relative;
    }

    .cart-badge {
      position: absolute;
      top: -8px;
      right: -8px;
      background: #ff4757;
      color: white;
      font-size: 10px;
      font-weight: 700;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 2px solid white;
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0% {
        transform: scale(1);
      }

      50% {
        transform: scale(1.1);
      }

      100% {
        transform: scale(1);
      }
    }

    /* Profile Section */
    .nav-profile {
      position: relative;
    }

    .profile-dropdown {
      position: relative;
    }

    .profile-btn {
      display: flex;
      align-items: center;
      gap: 12px;
      background: rgba(255, 255, 255, 0.1);
      border: none;
      color: white;
      padding: 8px 16px;
      border-radius: 25px;
      cursor: pointer;
      transition: all 0.3s ease;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .profile-btn:hover {
      background: rgba(255, 255, 255, 0.2);
      transform: translateY(-1px);
    }

    .profile-avatar {
      position: relative;
    }

    .profile-pic {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .profile-status {
      position: absolute;
      bottom: 0;
      right: 0;
      width: 10px;
      height: 10px;
      background: #4CAF50;
      border-radius: 50%;
      border: 2px solid white;
    }

    .profile-info {
      display: flex;
      flex-direction: column;
      line-height: 1.2;
    }

    .profile-name {
      font-weight: 600;
      font-size: 14px;
    }

    .profile-role {
      font-size: 11px;
      opacity: 0.8;
      text-transform: capitalize;
    }

    .profile-arrow {
      font-size: 12px;
      transition: transform 0.3s ease;
    }

    .profile-dropdown:hover .profile-arrow {
      transform: rotate(180deg);
    }

    /* Profile Menu */
    .profile-menu {
      position: absolute;
      top: 100%;
      right: 0;
      margin-top: 10px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      min-width: 200px;
      opacity: 0;
      visibility: hidden;
      transform: translateY(-10px);
      transition: all 0.3s ease;
      z-index: 1000;
      border: 2px solid #333;
      padding: 8px;
    }

    .profile-dropdown:hover .profile-menu {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }

    .profile-menu-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      color: #000 !important;
      text-decoration: none;
      font-size: 14px;
      font-weight: 700;
      transition: all 0.3s ease;
      border-radius: 8px;
      margin: 4px;
      background: white;
    }

    .profile-menu-item:hover {
      background: #f8f9fa;
      color: #4CAF50 !important;
    }

    .profile-menu-item i {
      color: #333 !important;
      width: 16px;
      text-align: center;
    }

    .profile-menu-item:hover i {
      color: #4CAF50 !important;
    }

    .profile-menu-item span {
      color: #000 !important;
      font-weight: 700;
    }

    .profile-menu-item:hover span {
      color: #4CAF50 !important;
    }

    .profile-menu-divider {
      height: 1px;
      background: #e9ecef;
      margin: 8px 0;
    }

    .logout-item {
      color: #dc3545;
    }

    .logout-item:hover {
      background: #fff5f5;
      color: #dc3545;
    }

    /* Auth Link */
    .auth-link {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(10px);
    }

    .auth-link:hover {
      background: rgba(255, 255, 255, 0.2);
    }

    /* Mobile Toggle */
    .nav-mobile-toggle {
      display: none;
      cursor: pointer;
    }

    .hamburger {
      width: 24px;
      height: 20px;
      position: relative;
    }

    .hamburger span {
      display: block;
      width: 100%;
      height: 2px;
      background: white;
      position: absolute;
      transition: all 0.3s ease;
    }

    .hamburger span:nth-child(1) {
      top: 0;
    }

    .hamburger span:nth-child(2) {
      top: 9px;
    }

    .hamburger span:nth-child(3) {
      top: 18px;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
      .nav-search {
        max-width: 400px;
        margin: 0 20px;
      }
    }

    @media (max-width: 992px) {
      .nav-container {
        padding: 0 15px;
      }

      .nav-search {
        max-width: 300px;
        margin: 0 15px;
      }

      .nav-links {
        gap: 15px;
      }

      .nav-link span {
        display: none;
      }

      .profile-info {
        display: none;
      }
    }

    @media (max-width: 768px) {
      .nav-search {
        display: none;
      }

      .nav-category {
        display: none;
      }

      .nav-mobile-toggle {
        display: block;
      }

      .nav-links {
        position: fixed;
        top: 70px;
        left: -100%;
        width: 100%;
        height: calc(100vh - 70px);
        background: linear-gradient(135deg, #2c5f2d 0%, #4CAF50 100%);
        flex-direction: column;
        padding: 20px;
        transition: left 0.3s ease;
        z-index: 999;
      }

      .nav-links.active {
        left: 0;
      }

      .nav-link {
        width: 100%;
        justify-content: center;
        padding: 15px;
        border-radius: 10px;
      }

      .nav-link span {
        display: block;
      }
    }
  </style>

  <script>
    function filterByCategory() {
      const category = document.getElementById('categorySelect').value;
      if (category) {
        window.location.href = 'category.php?category=' + encodeURIComponent(category);
      } else {
        window.location.href = 'index.php';
      }
    }

    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function() {
      const mobileToggle = document.querySelector('.nav-mobile-toggle');
      const navLinks = document.querySelector('.nav-links');

      if (mobileToggle && navLinks) {
        mobileToggle.addEventListener('click', function() {
          navLinks.classList.toggle('active');
        });
      }
    });
  </script>

  <!-- Enhanced Chatbot Script -->
  <script src="js/chatbot_enhanced.js"></script>
  
  <!-- Initialize Enhanced Chatbot -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize the enhanced chatbot
      if (typeof AgroKartChatbot !== 'undefined') {
        window.agroKartChatbot = new AgroKartChatbot();
        console.log('Enhanced AgroKart Chatbot initialized successfully!');
      } else {
        console.warn('Enhanced chatbot not loaded');
      }
    });
  </script>