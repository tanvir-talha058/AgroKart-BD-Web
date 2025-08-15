<?php
// Customer Rating API for Chatbot Experience
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    require_once '../includes/db_connect.php';
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $sessionId = $data['sessionId'] ?? '';
    $rating = (int)($data['rating'] ?? 0);
    $timestamp = $data['timestamp'] ?? date('Y-m-d H:i:s');
    
    if (empty($sessionId)) {
        throw new Exception('Session ID is required');
    }
    
    if ($rating < 1 || $rating > 5) {
        throw new Exception('Rating must be between 1 and 5 stars');
    }
    
    // Create ratings table if it doesn't exist
    $createTableQuery = "
        CREATE TABLE IF NOT EXISTS chatbot_customer_ratings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(100) NOT NULL,
            user_id INT NULL,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            feedback TEXT NULL,
            improvement_suggestions TEXT NULL,
            would_recommend BOOLEAN NULL,
            experience_category VARCHAR(50) NULL,
            response_helpfulness INT NULL CHECK (response_helpfulness >= 1 AND response_helpfulness <= 5),
            ease_of_use INT NULL CHECK (ease_of_use >= 1 AND ease_of_use <= 5),
            response_speed INT NULL CHECK (response_speed >= 1 AND response_speed <= 5),
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX(session_id),
            INDEX(user_id),
            INDEX(rating),
            INDEX(created_at)
        )
    ";
    $conn->query($createTableQuery);
    
    // Check if rating already exists for this session
    $checkStmt = $conn->prepare("SELECT id FROM chatbot_customer_ratings WHERE session_id = ?");
    $checkStmt->bind_param("s", $sessionId);
    $checkStmt->execute();
    $existingRating = $checkStmt->get_result();
    
    $userId = $_SESSION['user_id'] ?? null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    if ($existingRating->num_rows > 0) {
        // Update existing rating
        $updateStmt = $conn->prepare("
            UPDATE chatbot_customer_ratings 
            SET rating = ?, updated_at = NOW()
            WHERE session_id = ?
        ");
        $updateStmt->bind_param("is", $rating, $sessionId);
        $success = $updateStmt->execute();
        $action = 'updated';
    } else {
        // Insert new rating
        $insertStmt = $conn->prepare("
            INSERT INTO chatbot_customer_ratings 
            (session_id, user_id, rating, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $insertStmt->bind_param("siiss", $sessionId, $userId, $rating, $ipAddress, $userAgent);
        $success = $insertStmt->execute();
        $action = 'created';
    }
    
    if (!$success) {
        throw new Exception('Failed to save rating');
    }
    
    // Generate response message based on rating
    $responseMessage = generateRatingResponse($rating);
    
    // Get aggregated statistics
    $stats = getRatingStatistics($conn);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'rating_saved' => true,
            'action' => $action,
            'session_id' => $sessionId,
            'rating' => $rating,
            'message' => $responseMessage,
            'statistics' => $stats,
            'follow_up' => getFollowUpSuggestions($rating)
        ],
        'message' => 'Thank you for your feedback!'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => 'Failed to save rating'
    ]);
}

function generateRatingResponse($rating) {
    $responses = [
        1 => [
            "We're sorry you had a poor experience. We'll work hard to improve our service.",
            "Thank you for the feedback. We take all concerns seriously and will address them.",
            "We apologize for not meeting your expectations. Your feedback helps us improve."
        ],
        2 => [
            "We appreciate your feedback and will use it to make improvements.",
            "Thank you for letting us know. We're committed to providing better service.",
            "We value your input and are working to enhance your experience."
        ],
        3 => [
            "Thank you for the rating! We're always looking for ways to improve.",
            "We appreciate your feedback and are working to make our service even better.",
            "Thanks for your review. We're committed to continuous improvement."
        ],
        4 => [
            "Great! We're glad you had a good experience. Thank you for the feedback!",
            "Thank you for the positive rating! We're happy we could help you today.",
            "Wonderful! We appreciate your positive feedback and continued trust."
        ],
        5 => [
            "Excellent! We're thrilled you had such a great experience with our chatbot!",
            "Thank you for the 5-star rating! We're delighted we exceeded your expectations!",
            "Amazing! Your perfect rating motivates us to keep providing excellent service!",
            "Fantastic! We're overjoyed that you had an outstanding experience!"
        ]
    ];
    
    $ratingResponses = $responses[$rating];
    return $ratingResponses[array_rand($ratingResponses)];
}

function getRatingStatistics($conn) {
    try {
        $stats = [];
        
        // Overall average rating
        $avgStmt = $conn->prepare("
            SELECT 
                AVG(rating) as average_rating,
                COUNT(*) as total_ratings
            FROM chatbot_customer_ratings
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $avgStmt->execute();
        $avgResult = $avgStmt->get_result()->fetch_assoc();
        
        $stats['average_rating'] = round($avgResult['average_rating'], 2);
        $stats['total_ratings'] = (int)$avgResult['total_ratings'];
        
        // Rating distribution
        $distStmt = $conn->prepare("
            SELECT 
                rating,
                COUNT(*) as count,
                ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM chatbot_customer_ratings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY))), 1) as percentage
            FROM chatbot_customer_ratings
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY rating
            ORDER BY rating DESC
        ");
        $distStmt->execute();
        $distResult = $distStmt->get_result();
        
        $distribution = [];
        while ($row = $distResult->fetch_assoc()) {
            $distribution[] = [
                'stars' => (int)$row['rating'],
                'count' => (int)$row['count'],
                'percentage' => (float)$row['percentage']
            ];
        }
        $stats['distribution'] = $distribution;
        
        // Recent trend (last 7 days vs previous 7 days)
        $trendStmt = $conn->prepare("
            SELECT 
                AVG(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN rating END) as current_week_avg,
                AVG(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY) THEN rating END) as previous_week_avg,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as current_week_count,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as previous_week_count
            FROM chatbot_customer_ratings
        ");
        $trendStmt->execute();
        $trendResult = $trendStmt->get_result()->fetch_assoc();
        
        $stats['trend'] = [
            'current_week_average' => round($trendResult['current_week_avg'], 2),
            'previous_week_average' => round($trendResult['previous_week_avg'], 2),
            'current_week_count' => (int)$trendResult['current_week_count'],
            'previous_week_count' => (int)$trendResult['previous_week_count'],
            'improvement' => round($trendResult['current_week_avg'] - $trendResult['previous_week_avg'], 2)
        ];
        
        return $stats;
        
    } catch (Exception $e) {
        return [
            'average_rating' => 0,
            'total_ratings' => 0,
            'distribution' => [],
            'trend' => null,
            'error' => 'Could not fetch statistics'
        ];
    }
}

function getFollowUpSuggestions($rating) {
    $suggestions = [];
    
    switch ($rating) {
        case 1:
        case 2:
            $suggestions = [
                'Would you like to speak with a live agent?',
                'Can you tell us what went wrong?',
                'Would you like us to follow up with you?',
                'How can we make this right?'
            ];
            break;
            
        case 3:
            $suggestions = [
                'What could we do better next time?',
                'Any specific suggestions for improvement?',
                'Would you like to provide more feedback?'
            ];
            break;
            
        case 4:
        case 5:
            $suggestions = [
                'Would you recommend our chatbot to others?',
                'Any features you\'d like to see added?',
                'Share your experience on social media!',
                'Join our loyalty program for exclusive deals!'
            ];
            break;
    }
    
    return $suggestions;
}
?>
