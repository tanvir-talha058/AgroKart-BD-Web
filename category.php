<?php
// FILE: category.php

// Get the category from URL parameter
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$category_name = '';

// Validate category
$valid_categories = ['Vegetable', 'Fruit', 'Spice'];
if (!in_array($category, $valid_categories)) {
    header('Location: index.php');
    exit;
}

// Include header after checking for redirect
include 'includes/header.php';

// Set display name for category
switch ($category) {
    case 'Vegetable':
        $category_name = 'Vegetables';
        break;
    case 'Fruit':
        $category_name = 'Fruits';
        break;
    case 'Spice':
        $category_name = 'Spices';
        break;
}
?>

<section class="category-header">
    <div class="container">
        <h1><?php echo htmlspecialchars($category_name); ?></h1>
        <p>Fresh <?php echo strtolower($category_name); ?> delivered to your doorstep</p>
    </div>
</section>

<section class="product-section">
    <div class="container">
        <div class="product-row">
            <?php
            // Fetch products by category
            $sql = "SELECT id, name, price, image_path, stock, description FROM products WHERE category = ? AND stock > 0 ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $result = $stmt->get_result();

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
                echo '<div class="no-products">';
                echo '<p>No ' . strtolower($category_name) . ' available at the moment.</p>';
                echo '<a href="index.php" class="back-to-home">Back to All Products</a>';
                echo '</div>';
            }
            $stmt->close();
            ?>
        </div>
    </div>
</section>

<link rel="stylesheet" href="css/category-style.css">

<script>
    // Set category dropdown to current category
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('categorySelect');
        if (categorySelect) {
            categorySelect.value = '<?php echo htmlspecialchars($category); ?>';
        }
    });
</script>

<?php
include 'includes/footer.php';
$conn->close();
?>