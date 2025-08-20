<?php

// Get the search query from URL parameter
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

// If no search query, redirect to home page before including header
if (empty($search_query)) {
    header('Location: index.php');
    exit;
}

// Include header after checking for redirect
include 'includes/header.php';
?>

<section class="search-hero">
    <div class="search-hero-bg">
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>
    <div class="container">
        <div class="search-hero-content">
            <h1>Search Results</h1>
            <p class="search-query">Showing results for: <span class="highlight">"<?php echo htmlspecialchars($search_query); ?>"</span></p>
        </div>
    </div>
</section>

<section class="search-results-section">
    <div class="container">
        <div class="results-container">
            <div class="product-row" id="searchResults">
                <?php
                if (!empty($search_query)) {
                    // Search in product name, category, and description
                    $sql = "SELECT id, name, price, image_path, stock, category, description 
                            FROM products 
                            WHERE stock > 0 
                            AND (name LIKE ? OR category LIKE ? OR description LIKE ?)
                            ORDER BY 
                                CASE 
                                    WHEN name LIKE ? THEN 1
                                    WHEN category LIKE ? THEN 2
                                    ELSE 3
                                END,
                                created_at DESC";

                    $search_term = "%$search_query%";
                    $stmt = $conn->prepare($sql);
                    // There are 5 placeholders (?) in the query
                    $stmt->bind_param("sssss", $search_term, $search_term, $search_term, $search_term, $search_term);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        echo '<div class="search-stats-card">';
                        echo '<div class="stats-content">';
                        echo '<div class="stats-icon"><i class="fas fa-check-circle"></i></div>';
                        echo '<div class="stats-text">';
                        echo '<h3>Found ' . $result->num_rows . ' product(s)</h3>';
                        echo '<p>matching your search criteria</p>';
                        echo '</div></div></div>';

                        while ($row = $result->fetch_assoc()) {
                            // Enhanced Product Card HTML
                            echo '<div class="enhanced-card">';
                            echo '<div class="card-badge"><span class="category-badge">' . htmlspecialchars($row["category"]) . '</span></div>';
                            echo '<div class="card-image-container">';
                            echo '<a href="product_details.php?id=' . $row["id"] . '">';
                            echo '<img src="' . htmlspecialchars($row["image_path"]) . '" alt="' . htmlspecialchars($row["name"]) . '">';
                            echo '<div class="image-overlay"><i class="fas fa-eye"></i></div>';
                            echo '</a></div>';
                            echo '<div class="card-content">';
                            echo '<h4>' . htmlspecialchars($row["name"]) . '</h4>';
                            echo '<div class="price-section">';
                            echo '<span class="price">৳' . htmlspecialchars($row["price"]) . '</span>';
                            echo '<span class="stock-status ' . ($row["stock"] > 0 ? 'in-stock' : 'out-of-stock') . '">';
                            echo '<i class="fas fa-' . ($row["stock"] > 0 ? 'check' : 'times') . '"></i> ';
                            echo ($row["stock"] > 0 ? 'In Stock' : 'Out of Stock');
                            echo '</span></div>';
                            echo '<form action="php/cart_manager.php" method="POST" class="card-form">';
                            echo '<input type="hidden" name="product_id" value="' . $row["id"] . '">';
                            echo '<input type="hidden" name="action" value="add">';
                            echo '<button type="submit" class="add-to-cart-btn enhanced-btn" ' . ($row["stock"] <= 0 ? 'disabled' : '') . '>';
                            echo '<i class="fas fa-shopping-cart"></i>';
                            echo '<span>' . ($row["stock"] > 0 ? 'Add to Cart' : 'Out of Stock') . '</span>';
                            echo '</button></form></div></div>';
                        }
                    } else {
                        // No Results Found HTML
                        echo '<div class="no-results-container">';
                        echo '<div class="no-results-card">';
                        echo '<div class="no-results-icon"><i class="fas fa-search"></i></div>';
                        echo '<h3>No products found</h3>';
                        echo '<p>Sorry, we couldn\'t find any products matching <strong>"' . htmlspecialchars($search_query) . '"</strong></p>';
                        echo '<a href="index.php" class="back-to-home-btn"><i class="fas fa-home"></i><span>Back to Homepage</span></a>';
                        echo '</div></div>';
                    }
                    $stmt->close();
                }
                ?>
            </div>
        </div>
    </div>
</section>

<link rel="stylesheet" href="css/search.css">

<?php
include 'includes/footer.php';
$conn->close();
?>