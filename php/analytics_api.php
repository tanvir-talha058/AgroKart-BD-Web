<?php
// Analytics API for Chatbot Performance Tracking
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    require_once '../includes/db_connect.php';
    
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $response = ['success' => false, 'data' => null];
    
    switch($action) {
        case 'track_interaction':
            $response = trackInteraction($conn);
            break;
            
        case 'get_dashboard':
            $response = getAnalyticsDashboard($conn);
            break;
            
        case 'save_rating':
            $response = saveRating($conn);
            break;
            
        case 'get_popular_queries':
            $response = getPopularQueries($conn);
            break;
            
        case 'get_performance_metrics':
            $response = getPerformanceMetrics($conn);
            break;
            
        default:
            $response = ['success' => false, 'error' => 'Invalid action'];
    }
    
} catch (Exception $e) {
    $response = ['success' => false, 'error' => 'Server error', 'message' => $e->getMessage()];
}

echo json_encode($response);

function trackInteraction($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $sessionId = $data['sessionId'] ?? '';
    $message = $data['message'] ?? '';
    $language = $data['language'] ?? 'en';
    $mood = $data['mood'] ?? 'neutral';
    $userId = $_SESSION['user_id'] ?? null;
    
    if (empty($sessionId) || empty($message)) {
        return ['success' => false, 'error' => 'Session ID and message required'];
    }
    
    try {
        // Create analytics table if it doesn't exist
        $createTable = "
            CREATE TABLE IF NOT EXISTS chatbot_analytics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(100) NOT NULL,
                user_id INT NULL,
                message TEXT NOT NULL,
                language VARCHAR(10) DEFAULT 'en',
                sentiment VARCHAR(20) DEFAULT 'neutral',
                intent VARCHAR(50) NULL,
                response_time_ms INT DEFAULT 0,
                satisfaction_score DECIMAL(3,2) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX(session_id),
                INDEX(user_id),
                INDEX(created_at)
            )
        ";
        $conn->query($createTable);
        
        // Detect intent from message
        $intent = detectIntent($message);
        
        // Calculate response time (simulated)
        $responseTime = rand(200, 1500);
        
        $stmt = $conn->prepare("
            INSERT INTO chatbot_analytics 
            (session_id, user_id, message, language, sentiment, intent, response_time_ms, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("sissssi", $sessionId, $userId, $message, $language, $mood, $intent, $responseTime);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'data' => [
                    'tracked' => true,
                    'intent' => $intent,
                    'response_time' => $responseTime
                ]
            ];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Database error', 'message' => $e->getMessage()];
    }
}

function detectIntent($message) {
    $message = strtolower($message);
    
    $intents = [
        'greeting' => ['hello', 'hi', 'hey', 'good morning', 'namaste'],
        'product_inquiry' => ['product', 'vegetable', 'fruit', 'what do you have', 'show me'],
        'price_inquiry' => ['price', 'cost', 'how much', 'rate'],
        'order_tracking' => ['track', 'status', 'where is my order', 'delivery'],
        'order_cancellation' => ['cancel', 'refund', 'return'],
        'bulk_pricing' => ['bulk', 'wholesale', 'discount', 'quantity'],
        'support' => ['help', 'support', 'problem', 'issue'],
        'agent_request' => ['agent', 'human', 'representative'],
        'complaint' => ['complain', 'bad', 'terrible', 'awful', 'disappointed'],
        'compliment' => ['good', 'great', 'excellent', 'amazing', 'love']
    ];
    
    foreach ($intents as $intent => $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return $intent;
            }
        }
    }
    
    return 'unknown';
}

function getAnalyticsDashboard($conn) {
    try {
        $dashboard = [];
        
        // Total interactions today
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM chatbot_analytics 
            WHERE DATE(created_at) = CURDATE()
        ");
        $stmt->execute();
        $dashboard['interactions_today'] = $stmt->get_result()->fetch_assoc()['count'];
        
        // Average response time
        $stmt = $conn->prepare("
            SELECT AVG(response_time_ms) as avg_time 
            FROM chatbot_analytics 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->execute();
        $dashboard['avg_response_time'] = round($stmt->get_result()->fetch_assoc()['avg_time'], 0);
        
        // Most common intents
        $stmt = $conn->prepare("
            SELECT intent, COUNT(*) as count 
            FROM chatbot_analytics 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY intent 
            ORDER BY count DESC 
            LIMIT 5
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $intents = [];
        while ($row = $result->fetch_assoc()) {
            $intents[] = $row;
        }
        $dashboard['top_intents'] = $intents;
        
        // Language distribution
        $stmt = $conn->prepare("
            SELECT language, COUNT(*) as count 
            FROM chatbot_analytics 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY language
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $languages = [];
        while ($row = $result->fetch_assoc()) {
            $languages[] = $row;
        }
        $dashboard['language_distribution'] = $languages;
        
        // Sentiment analysis
        $stmt = $conn->prepare("
            SELECT sentiment, COUNT(*) as count 
            FROM chatbot_analytics 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY sentiment
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $sentiments = [];
        while ($row = $result->fetch_assoc()) {
            $sentiments[] = $row;
        }
        $dashboard['sentiment_distribution'] = $sentiments;
        
        // Hourly activity
        $stmt = $conn->prepare("
            SELECT HOUR(created_at) as hour, COUNT(*) as count 
            FROM chatbot_analytics 
            WHERE DATE(created_at) = CURDATE()
            GROUP BY HOUR(created_at)
            ORDER BY hour
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $hourly = [];
        while ($row = $result->fetch_assoc()) {
            $hourly[] = $row;
        }
        $dashboard['hourly_activity'] = $hourly;
        
        return [
            'success' => true,
            'data' => $dashboard
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Analytics error', 'message' => $e->getMessage()];
    }
}

function saveRating($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $sessionId = $data['sessionId'] ?? '';
    $rating = (int)($data['rating'] ?? 0);
    $feedback = $data['feedback'] ?? '';
    
    if (empty($sessionId) || $rating < 1 || $rating > 5) {
        return ['success' => false, 'error' => 'Valid session ID and rating (1-5) required'];
    }
    
    try {
        // Create ratings table if it doesn't exist
        $createTable = "
            CREATE TABLE IF NOT EXISTS chatbot_ratings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(100) NOT NULL,
                rating INT NOT NULL,
                feedback TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX(session_id),
                INDEX(rating),
                INDEX(created_at)
            )
        ";
        $conn->query($createTable);
        
        $stmt = $conn->prepare("
            INSERT INTO chatbot_ratings (session_id, rating, feedback, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->bind_param("sis", $sessionId, $rating, $feedback);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'data' => [
                    'rating_saved' => true,
                    'session_id' => $sessionId,
                    'rating' => $rating
                ]
            ];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Database error', 'message' => $e->getMessage()];
    }
}

function getPopularQueries($conn) {
    try {
        $stmt = $conn->prepare("
            SELECT message, COUNT(*) as frequency, intent
            FROM chatbot_analytics 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY message
            HAVING frequency > 1
            ORDER BY frequency DESC
            LIMIT 20
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $queries = [];
        while ($row = $result->fetch_assoc()) {
            $queries[] = $row;
        }
        
        return [
            'success' => true,
            'data' => [
                'popular_queries' => $queries,
                'total_unique_queries' => count($queries)
            ]
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Database error', 'message' => $e->getMessage()];
    }
}

function getPerformanceMetrics($conn) {
    try {
        $metrics = [];
        
        // Resolution rate (how many queries got satisfactory responses)
        $stmt = $conn->prepare("
            SELECT 
                COUNT(*) as total_interactions,
                AVG(CASE WHEN sentiment = 'positive' THEN 1 ELSE 0 END) as positive_rate,
                AVG(response_time_ms) as avg_response_time
            FROM chatbot_analytics 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->execute();
        $metrics['performance'] = $stmt->get_result()->fetch_assoc();
        
        // Customer satisfaction
        $stmt = $conn->prepare("
            SELECT 
                AVG(rating) as avg_rating,
                COUNT(*) as total_ratings
            FROM chatbot_ratings 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->execute();
        $metrics['satisfaction'] = $stmt->get_result()->fetch_assoc();
        
        // Agent escalation rate
        $stmt = $conn->prepare("
            SELECT 
                COUNT(*) as agent_requests,
                (SELECT COUNT(*) FROM chatbot_analytics WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as total_interactions
            FROM chatbot_analytics 
            WHERE intent = 'agent_request' 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->execute();
        $escalation = $stmt->get_result()->fetch_assoc();
        $metrics['escalation_rate'] = $escalation['total_interactions'] > 0 ? 
            round(($escalation['agent_requests'] / $escalation['total_interactions']) * 100, 2) : 0;
        
        // Peak usage times
        $stmt = $conn->prepare("
            SELECT 
                HOUR(created_at) as peak_hour,
                COUNT(*) as interaction_count
            FROM chatbot_analytics 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY HOUR(created_at)
            ORDER BY interaction_count DESC
            LIMIT 3
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $peak_hours = [];
        while ($row = $result->fetch_assoc()) {
            $peak_hours[] = $row;
        }
        $metrics['peak_hours'] = $peak_hours;
        
        // Trend analysis (last 7 days vs previous 7 days)
        $stmt = $conn->prepare("
            SELECT 
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as current_week,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as previous_week
            FROM chatbot_analytics
        ");
        $stmt->execute();
        $trend = $stmt->get_result()->fetch_assoc();
        $growth_rate = $trend['previous_week'] > 0 ? 
            round((($trend['current_week'] - $trend['previous_week']) / $trend['previous_week']) * 100, 2) : 0;
        $metrics['growth_rate'] = $growth_rate;
        
        return [
            'success' => true,
            'data' => $metrics
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Metrics error', 'message' => $e->getMessage()];
    }
}
?>
