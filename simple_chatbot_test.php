<?php
// Simple test page to check if chatbot loads
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Chatbot Test</title>
    <link rel="stylesheet" href="css/chatbot.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f0f0f0;
        }
        .test-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
        }
        .status {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🤖 Chatbot Test Page</h1>
        <p>This is a minimal test to check if the chatbot works.</p>
        
        <div id="testResults"></div>
        
        <p><strong>Instructions:</strong></p>
        <ol>
            <li>Look for the green chat button in the bottom-right corner</li>
            <li>Click it to open the chatbot</li>
            <li>Type "hello" to test the response</li>
        </ol>
        
        <script>
            // Test function to check if chatbot loads
            function checkChatbot() {
                const results = document.getElementById('testResults');
                let html = '';
                
                // Check if chatbot files are loaded
                if (typeof AgroKartChatbot !== 'undefined') {
                    html += '<div class="status success">✅ Chatbot script loaded successfully</div>';
                } else {
                    html += '<div class="status error">❌ Chatbot script failed to load</div>';
                }
                
                // Check if instance exists
                if (window.chatbot) {
                    html += '<div class="status success">✅ Chatbot instance created</div>';
                } else {
                    html += '<div class="status error">❌ Chatbot instance not found</div>';
                }
                
                // Check if DOM elements exist
                const toggle = document.getElementById('chatbotToggle');
                if (toggle) {
                    html += '<div class="status success">✅ Chatbot button found</div>';
                } else {
                    html += '<div class="status error">❌ Chatbot button not found</div>';
                }
                
                results.innerHTML = html;
            }
            
            // Check every 2 seconds for 10 seconds
            let checks = 0;
            const interval = setInterval(() => {
                checkChatbot();
                checks++;
                if (checks >= 5) {
                    clearInterval(interval);
                }
            }, 2000);
            
            // Initial check
            checkChatbot();
        </script>
    </div>

    <!-- Load chatbot script -->
    <script src="js/chatbot.js"></script>
</body>
</html>
