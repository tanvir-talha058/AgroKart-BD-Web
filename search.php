<?php
// FILE: search.php

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

<style>
    /* Enhanced Search Hero Section */
    .search-hero {
        position: relative;
        /* THIS IS THE COLOR YOU REQUESTED */
        background: linear-gradient(135deg, #1a472a 0%, #2c5f2d 50%, #4CAF50 100%);
        padding: 60px 0;
        overflow: hidden;
        margin-bottom: 60px;
    }
    .search-hero-bg {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        /* This is the matching overlay color */
        background: linear-gradient(135deg, rgba(26, 71, 42, 0.9) 0%, rgba(44, 95, 45, 0.9) 50%, rgba(76, 175, 80, 0.9) 100%);
    }
    .floating-shapes { position: absolute; top: 0; left: 0; right: 0; bottom: 0; overflow: hidden; }
    .shape { position: absolute; background: rgba(255, 255, 255, 0.1); border-radius: 50%; animation: float 6s ease-in-out infinite; }
    .shape-1 { width: 80px; height: 80px; top: 20%; left: 10%; animation-delay: 0s; }
    .shape-2 { width: 120px; height: 120px; top: 60%; right: 15%; animation-delay: 2s; }
    .shape-3 { width: 60px; height: 60px; bottom: 20%; left: 20%; animation-delay: 4s; }
    @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-20px); } }
    .search-hero-content { position: relative; z-index: 2; text-align: center; color: white; }
    .search-hero h1 { font-size: 3.5rem; font-weight: 700; margin-bottom: 15px; }
    .search-query { font-size: 1.3rem; margin-bottom: 30px; }
    .highlight { background: rgba(255, 255, 255, 0.2); padding: 5px 15px; border-radius: 25px; font-weight: 600; }
    .search-results-section { padding: 0 20px 60px; }
    .container { max-width: 1400px; margin: 0 auto; }
    .results-container { background: white; border-radius: 20px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1); overflow: hidden; }
    .search-stats-card { grid-column: 1 / -1; background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; padding: 30px; }
    .stats-content { display: flex; align-items: center; gap: 20px; max-width: 400px; margin: 0 auto; }
    .stats-icon { font-size: 2.5rem; }
    .stats-text h3 { margin: 0 0 5px 0; font-size: 1.5rem; }
    .stats-text p { margin: 0; font-size: 1rem; }
    .product-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; padding: 40px; background: #f8f9fa; }
    .enhanced-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; position: relative; }
    .enhanced-card:hover { transform: translateY(-8px); box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15); }
    .card-badge { position: absolute; top: 15px; right: 15px; z-index: 2; }
    .category-badge { background: rgba(76, 175, 80, 0.9); color: white; padding: 5px 12px; border-radius: 15px; font-size: 0.8rem; }
    .card-image-container { position: relative; overflow: hidden; height: 220px; }
    .card-image-container img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease; }
    .enhanced-card:hover .card-image-container img { transform: scale(1.05); }
    .image-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease; }
    .enhanced-card:hover .image-overlay { opacity: 1; }
    .image-overlay i { color: white; font-size: 2rem; }
    .card-content { padding: 20px; }
    .card-content h4 { margin: 0 0 15px 0; font-size: 1.2rem; }
    .price-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .price { font-size: 1.4rem; font-weight: 700; color: #4CAF50; }
    .stock-status { display: flex; align-items: center; gap: 5px; }
    .stock-status.in-stock { color: #4CAF50; }
    .stock-status.out-of-stock { color: #f44336; }
    .enhanced-btn { width: 100%; padding: 12px 20px; border: none; border-radius: 10px; font-size: 1rem; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px; background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3); }
    .enhanced-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4); }
    .enhanced-btn:disabled { background: #ccc; cursor: not-allowed; box-shadow: none; }
    .no-results-container { grid-column: 1 / -1; padding: 60px 20px; }
    .no-results-card { background: white; border-radius: 20px; padding: 50px; text-align: center; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1); max-width: 600px; margin: 0 auto; }
    .no-results-icon { font-size: 4rem; color: #ccc; margin-bottom: 30px; }
    .no-results-card h3 { font-size: 2rem; }
    .no-results-card p { font-size: 1.1rem; color: #666; margin-bottom: 40px; }
    .back-to-home-btn { display: inline-flex; align-items: center; gap: 10px; padding: 15px 30px; background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; text-decoration: none; border-radius: 25px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3); }
    .back-to-home-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4); color: white; }
</style>

<?php
include 'includes/footer.php';
$conn->close();
?>
