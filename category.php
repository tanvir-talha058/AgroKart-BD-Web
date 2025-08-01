<?php
// FILE: category.php
include 'includes/header.php';

// Get the category from URL parameter
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$category_name = '';

// Validate category
$valid_categories = ['Vegetable', 'Fruit', 'Spice'];
if (!in_array($category, $valid_categories)) {
    header('Location: index.php');
    exit;
}

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

<style>
    .category-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 60px 0;
        text-align: center;
        margin-bottom: 40px;
    }

    .category-header h1 {
        font-size: 2.5rem;
        margin-bottom: 10px;
        font-weight: 700;
    }

    .category-header p {
        font-size: 1.1rem;
        opacity: 0.9;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .no-products {
        text-align: center;
        padding: 60px 20px;
        grid-column: 1 / -1;
    }

    .no-products p {
        font-size: 1.2rem;
        color: #666;
        margin-bottom: 20px;
    }

    .back-to-home {
        display: inline-block;
        padding: 12px 24px;
        background-color: #4CAF50;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .back-to-home:hover {
        background-color: #45a049;
        color: white;
        text-decoration: none;
    }

    .product-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 30px;
        margin-top: 30px;
    }

    .product-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        text-align: center;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
    }

    .product-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    .product-card h4 {
        margin: 10px 0;
        color: #333;
        font-size: 1.1rem;
    }

    .product-card .price {
        font-size: 1.2rem;
        font-weight: bold;
        color: #4CAF50;
    }

    .add-to-cart-btn {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.3s ease;
        margin-top: 10px;
        width: 100%;
    }

    .add-to-cart-btn:hover {
        background-color: #45a049;
    }

    @media (max-width: 768px) {
        .category-header h1 {
            font-size: 2rem;
        }

        .product-row {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
    }
</style>

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