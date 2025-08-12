<?php
require_once 'includes/db_connect.php';

// Protect this page and ensure the user is a seller
if (!isset($_SESSION['loggedin']) || $_SESSION['user_role'] !== 'Seller') {
    header('Location: login.php');
    exit;
}

$seller_id = $_SESSION['user_id'];

// Create hidden customers table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS seller_hidden_customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    customer_id INT NOT NULL,
    hidden_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_seller_customer (seller_id, customer_id),
    FOREIGN KEY (seller_id) REFERENCES users(id),
    FOREIGN KEY (customer_id) REFERENCES users(id)
)";

$conn->query($create_table_sql);

// Get customers who have bought from this seller and are not hidden
$sql = "SELECT DISTINCT u.id, u.name, u.email, u.phone, u.division, u.district, u.city, u.profile_image_path, 
        u.created_at, COUNT(DISTINCT o.id) as order_count, 
        SUM(oi.quantity * oi.price) as total_spent
        FROM users u 
        JOIN orders o ON u.id = o.buyer_id
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE p.seller_id = ? AND u.role = 'Buyer'
        AND NOT EXISTS (
            SELECT 1 FROM seller_hidden_customers shc 
            WHERE shc.seller_id = ? AND shc.customer_id = u.id
        )
        GROUP BY u.id
        ORDER BY total_spent DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $seller_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$customers = [];

while ($row = $result->fetch_assoc()) {
    $customers[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - AgroKartBD</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/customers.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <div class="logo">
                <img src="images/AGrO.png" alt="Logo">
                <span>AgroKartBD</span>
            </div>
            <ul class="nav-menu">
                <li><a href="dashboard.php"><span class="icon"><i class="fas fa-chart-bar"></i></span>Dashboard</a></li>
                <li class="active"><a href="#"><span class="icon"><i class="fas fa-users"></i></span>Customers</a></li>
                <li><a href="php/logout.php"><span class="icon"><i class="fas fa-sign-out-alt"></i></span>Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="top-header">
                <h1 class="page-title"><i class="fas fa-users"></i> Customer Management</h1>
                <div class="header-right">
                    <div class="user-profile">
                        <img src="images/profiles/user_<?php echo $_SESSION['user_id']; ?>_<?php echo time(); ?>.jpg" alt="Profile" onerror="this.src='images/AGrO.png'">
                    </div>
                </div>
            </header>

            <?php
            if (isset($_SESSION['error'])) {
                echo '<p class="error-message">' . $_SESSION['error'] . '</p>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['message'])) {
                echo '<p class="success-message">' . $_SESSION['message'] . '</p>';
                unset($_SESSION['message']);
            }
            ?>

            <!-- Customer Statistics -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-users"></i></div><span>Total Customers</span>
                    </div>
                    <div class="stat-info">
                        <h2><?php echo count($customers); ?></h2>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-user-check"></i></div><span>Returning Customers</span>
                    </div>
                    <div class="stat-info">
                        <?php
                        $returning = 0;
                        foreach ($customers as $customer) {
                            if ($customer['order_count'] > 1) {
                                $returning++;
                            }
                        }
                        ?>
                        <h2><?php echo $returning; ?></h2>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div><span>Avg. Customer Spend</span>
                    </div>
                    <div class="stat-info">
                        <?php
                        $total_spent = array_sum(array_column($customers, 'total_spent'));
                        $avg_spent = count($customers) > 0 ? $total_spent / count($customers) : 0;
                        ?>
                        <h2>৳<?php echo number_format($avg_spent, 2); ?></h2>
                    </div>
                </div>
            </div>

            <!-- Customer List -->
            <div class="customer-list-container">
                <div class="list-header">
                    <h3>Customer List</h3>
                    <div class="search-container">
                        <input type="text" id="customerSearch" placeholder="Search customers..." class="search-input">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>

                <?php if (count($customers) > 0): ?>
                    <div class="customer-grid">
                        <?php foreach ($customers as $customer): ?>
                            <div class="customer-card">
                                <div class="customer-actions">
                                    <button class="delete-btn" onclick="confirmDelete(<?php echo $customer['id']; ?>, '<?php echo htmlspecialchars($customer['name']); ?>')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                                <div class="customer-avatar">
                                    <?php if ($customer['profile_image_path']): ?>
                                        <img src="<?php echo htmlspecialchars($customer['profile_image_path']); ?>" alt="<?php echo htmlspecialchars($customer['name']); ?>">
                                    <?php else: ?>
                                        <div class="avatar-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="customer-info">
                                    <h4 class="customer-name"><?php echo htmlspecialchars($customer['name']); ?></h4>
                                    <div class="customer-detail"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($customer['email']); ?></div>
                                    <div class="customer-detail"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($customer['phone']); ?></div>
                                    <div class="customer-detail"><i class="fas fa-map-marker-alt"></i>
                                        <?php
                                        $location = [];
                                        if (!empty($customer['city'])) $location[] = $customer['city'];
                                        if (!empty($customer['district'])) $location[] = $customer['district'];
                                        if (!empty($customer['division'])) $location[] = $customer['division'];
                                        echo !empty($location) ? htmlspecialchars(implode(', ', $location)) : 'Location not provided';
                                        ?>
                                    </div>
                                </div>
                                <div class="customer-stats">
                                    <div class="stat">
                                        <span class="stat-label">Orders</span>
                                        <span class="stat-value"><?php echo $customer['order_count']; ?></span>
                                    </div>
                                    <div class="stat">
                                        <span class="stat-label">Total Spent</span>
                                        <span class="stat-value">৳<?php echo number_format($customer['total_spent'], 2); ?></span>
                                    </div>
                                    <div class="stat">
                                        <span class="stat-label">Since</span>
                                        <span class="stat-value"><?php echo date('M Y', strtotime($customer['created_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-customers">
                        <div class="no-data-icon"><i class="fas fa-users-slash"></i></div>
                        <h3>No Customers Found</h3>
                        <p>You don't have any customers yet. Once buyers purchase your products, they will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div class="modal-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h2>Confirm Deletion</h2>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete customer <strong id="customerName"></strong>?</p>
                <p class="warning-text">This action cannot be undone and will remove this customer from your list.</p>
                <p class="note-text">Note: This only removes the customer from your view and doesn't delete their account from the system.</p>
            </div>
            <div class="modal-footer">
                <form action="php/delete_customer.php" method="POST">
                    <input type="hidden" id="customerId" name="customer_id" value="">
                    <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="delete-confirm-btn">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Customer search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('customerSearch');
            const customerCards = document.querySelectorAll('.customer-card');

            searchInput.addEventListener('keyup', function() {
                const searchTerm = searchInput.value.toLowerCase();

                customerCards.forEach(card => {
                    const name = card.querySelector('.customer-name').textContent.toLowerCase();
                    const email = card.querySelector('.customer-detail:nth-child(2)').textContent.toLowerCase();
                    const phone = card.querySelector('.customer-detail:nth-child(3)').textContent.toLowerCase();
                    const location = card.querySelector('.customer-detail:nth-child(4)').textContent.toLowerCase();

                    if (name.includes(searchTerm) || email.includes(searchTerm) ||
                        phone.includes(searchTerm) || location.includes(searchTerm)) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });

            // Modal functionality
            const modal = document.getElementById('deleteModal');
            const closeBtn = document.querySelector('.close-modal');

            if (closeBtn) {
                closeBtn.onclick = closeModal;
            }

            window.onclick = (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            };

            // Add animations to cards
            customerCards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('animated');
                }, 100 * index);
            });
        });

        // Delete confirmation functionality
        function confirmDelete(id, name) {
            const modal = document.getElementById('deleteModal');
            document.getElementById('customerId').value = id;
            document.getElementById('customerName').textContent = name;
            modal.style.display = 'flex';
            document.body.classList.add('modal-open');
        }

        function closeModal() {
            const modal = document.getElementById('deleteModal');
            document.body.classList.remove('modal-open');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
    </script>
</body>

</html>