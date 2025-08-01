<?php
require_once 'db_connect.php';
$cart_count = 0;
if (isset($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
  }
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
  <link rel="icon" type="image/x-icon" href="../images/AGrO.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <header class="navbar">
    <div class="logo">
      <a href="index.php" style="text-decoration: none; color: white; display:flex; align-items:center; gap:12px;">
        <img src="../images/AGrO.png" alt="AgroKart BD Logo" class="logo-img">
        <span>AgroKart BD</span>
      </a>
    </div>
    <form class="navbar-search" action="search.php" method="get">
      <input type="text" name="q" placeholder="Search for vegetables, fruits, crops..." />
      <button type="submit"><i class="fas fa-search"></i></button>
    </form>
    <div class="category-dropdown">
      <select id="categorySelect" onchange="filterByCategory()">
        <option value="">All Categories</option>
        <option value="Vegetable">Vegetables</option>
        <option value="Fruit">Fruits</option>
        <option value="Spice">Spices</option>
      </select>
    </div>
    <nav class="nav-links">
      <a href="index.php">Home</a>
      <a href="my_orders.php">My Orders</a>
      <a href="cart.php" class="nav-icon cart-icon" data-count="<?php echo $cart_count; ?>"><i class="fas fa-shopping-basket"></i></a>
      <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
        <div class="dropdown">
          <button class="dropbtn profile-btn">
            <img src="<?php echo isset($_SESSION['profile_image_path']) ? htmlspecialchars($_SESSION['profile_image_path']) : 'images/default-profile.png'; ?>" class="profile-pic-nav" alt="Profile">
            <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?>
          </button>
          <div class="dropdown-content">
            <a href="profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a>
            <?php if ($_SESSION['user_role'] == 'Seller'): ?>
              <a href="dashboard.php"><i class="fas fa-chart-bar"></i> Dashboard</a>
            <?php endif; ?>
            <a href="php/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
          </div>
        </div>
      <?php else: ?>
        <a href="login.php" class="nav-icon" title="Login/Register"><i class="far fa-user"></i></a>
      <?php endif; ?>
    </nav>
  </header>
  <style>
    .profile-pic-nav {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 8px;
      border: 2px solid white;
    }

    .category-dropdown {
      margin: 0 15px;
    }

    .category-dropdown select {
      padding: 8px 12px;
      border: none;
      border-radius: 5px;
      background-color: #f8f9fa;
      color: #333;
      font-size: 14px;
      cursor: pointer;
      outline: none;
      transition: all 0.3s ease;
    }

    .category-dropdown select:hover {
      background-color: #e9ecef;
    }

    .category-dropdown select:focus {
      box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
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
  </script>