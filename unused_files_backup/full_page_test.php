<?php
// Test page that mimics index.php structure
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroKart BD - Chatbot Test</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/form-style.css">
    <link rel="stylesheet" href="css/cart-style.css">
    <link rel="stylesheet" href="css/chatbot.css">
    <link rel="icon" type="image/x-icon" href="images/AGrO.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div style="padding: 20px; min-height: 100vh; background: #f5f5f5;">
        <h1>AgroKart BD - Homepage with Chatbot</h1>
        <p>This page tests the chatbot in the same environment as the main site.</p>
        <p><strong>Expected:</strong> Green chat button in bottom-right corner</p>
        
        <div style="background: white; padding: 20px; margin: 20px 0; border-radius: 8px;">
            <h3>Debug Information:</h3>
            <div id="debug-info">Loading...</div>
        </div>
        
        <!-- Sample content to fill the page -->
        <div style="height: 800px; background: white; margin: 20px 0; padding: 20px; border-radius: 8px;">
            <h2>Sample Content</h2>
            <p>This content simulates the main page. Scroll down to see more...</p>
            <br><br><br><br><br><br><br><br><br><br>
            <p>More content here...</p>
            <br><br><br><br><br><br><br><br><br><br>
            <p>The chat button should remain fixed in the bottom-right corner.</p>
        </div>
    </div>
    
    <!-- Chatbot Container -->
    <div id="chatbot-container"></div>
    
    <!-- Chatbot Script -->
    <script src="js/chatbot.js"></script>
    
    <script>
        // Debug script
        setTimeout(() => {
            const debugInfo = document.getElementById('debug-info');
            let info = '';
            
            // Check container
            const container = document.getElementById('chatbot-container');
            info += container ? '✅ Container found<br>' : '❌ Container missing<br>';
            
            // Check content
            if (container && container.innerHTML) {
                info += '✅ Chatbot HTML injected<br>';
                
                // Check button
                const button = document.getElementById('chatbotToggle');
                info += button ? '✅ Toggle button found<br>' : '❌ Toggle button missing<br>';
                
                if (button) {
                    const styles = window.getComputedStyle(button);
                    info += `Button display: ${styles.display}<br>`;
                    info += `Button position: ${styles.position}<br>`;
                    
                    const containerStyles = window.getComputedStyle(container);
                    info += `Container position: ${containerStyles.position}<br>`;
                    info += `Container z-index: ${containerStyles.zIndex}<br>`;
                }
            } else {
                info += '❌ Chatbot HTML not injected<br>';
            }
            
            // Check chatbot object
            info += window.chatbot ? '✅ Chatbot object exists<br>' : '❌ Chatbot object missing<br>';
            
            debugInfo.innerHTML = info;
        }, 2000);
        
        // Error monitoring
        window.addEventListener('error', (e) => {
            console.error('Error:', e.message, e.filename, e.lineno);
        });
    </script>
</body>
</html>
