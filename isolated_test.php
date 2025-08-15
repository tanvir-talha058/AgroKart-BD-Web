<?php
// Isolated test exactly like index.php
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
  <link rel="stylesheet" href="css/chatbot.css">
  <link rel="icon" type="image/x-icon" href="../images/AGrO.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <header class="navbar-enhanced">
    <div class="nav-container">
      <div class="nav-logo">
        <a href="index.php" class="logo-link">
          <div class="logo-wrapper">
            <span class="logo-title">AgroKart</span>
            <span class="logo-subtitle">BD</span>
          </div>
        </a>
      </div>
    </div>
  </header>

  <!-- Chatbot Container -->
  <div id="chatbot-container"></div>

  <div style="padding: 40px; min-height: 100vh; background: #f5f5f5;">
    <div style="max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
      <h1 style="color: #2c5f2d; margin-bottom: 20px;">🤖 AgroKart BD - Chatbot Test</h1>
      
      <div style="background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4CAF50;">
        <h3 style="margin: 0 0 10px 0; color: #2c5f2d;">Expected Result:</h3>
        <ul style="margin: 0; color: #2c5f2d;">
          <li>✅ A green circular chat button should appear in the bottom-right corner</li>
          <li>✅ The button should have a chat icon (💬)</li>
          <li>✅ Click the button to open a chat window</li>
          <li>✅ The chat window should be functional</li>
        </ul>
      </div>
      
      <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h3 style="margin: 0 0 15px 0; color: #333;">🔧 Debug Information:</h3>
        <div id="debug-output" style="font-family: monospace; font-size: 14px; color: #555;">
          Loading debug information...
        </div>
      </div>
      
      <div style="background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;">
        <h3 style="margin: 0 0 10px 0; color: #856404;">⚠️ If you don't see the chat button:</h3>
        <p style="margin: 0; color: #856404;">Check the debug information above and the browser console for any error messages.</p>
      </div>
      
      <div style="text-align: center; margin-top: 30px;">
        <button onclick="forceCreateChatbot()" style="background: #4CAF50; color: white; border: none; padding: 15px 30px; border-radius: 25px; font-size: 16px; cursor: pointer; box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);">
          🚀 Force Create Chatbot
        </button>
      </div>
    </div>
  </div>

  <!-- Chatbot Script -->
  <script src="js/chatbot.js"></script>
  
  <script>
    let debugStartTime = Date.now();
    
    function updateDebug(message) {
      const debugOutput = document.getElementById('debug-output');
      const timestamp = ((Date.now() - debugStartTime) / 1000).toFixed(2);
      debugOutput.innerHTML += `[${timestamp}s] ${message}<br>`;
    }
    
    updateDebug('🟢 Page loaded, starting debug...');
    
    // Check 1: Files accessibility
    updateDebug('🔍 Checking if JavaScript file is accessible...');
    
    // Check 2: Container existence
    setTimeout(() => {
      const container = document.getElementById('chatbot-container');
      if (container) {
        updateDebug('✅ Chatbot container found');
      } else {
        updateDebug('❌ Chatbot container NOT found');
      }
    }, 100);
    
    // Check 3: Class definition
    setTimeout(() => {
      if (typeof AgroKartChatbot !== 'undefined') {
        updateDebug('✅ AgroKartChatbot class loaded successfully');
      } else {
        updateDebug('❌ AgroKartChatbot class NOT loaded');
      }
    }, 200);
    
    // Check 4: Chatbot instance
    setTimeout(() => {
      if (window.chatbot) {
        updateDebug('✅ Chatbot instance created successfully');
        
        // Check 5: HTML injection
        const container = document.getElementById('chatbot-container');
        if (container && container.innerHTML.length > 0) {
          updateDebug('✅ Chatbot HTML injected (length: ' + container.innerHTML.length + ')');
          
          // Check 6: Button element
          const button = document.getElementById('chatbotToggle');
          if (button) {
            updateDebug('✅ Chat button element found');
            
            // Check 7: CSS styling
            const buttonStyles = window.getComputedStyle(button);
            const containerStyles = window.getComputedStyle(container);
            updateDebug('📊 Button display: ' + buttonStyles.display);
            updateDebug('📊 Container position: ' + containerStyles.position);
            updateDebug('📊 Container z-index: ' + containerStyles.zIndex);
            
            if (buttonStyles.display !== 'none' && containerStyles.position === 'fixed') {
              updateDebug('🎉 Chatbot should be visible!');
            } else {
              updateDebug('⚠️ Chatbot might have styling issues');
            }
          } else {
            updateDebug('❌ Chat button element NOT found');
          }
        } else {
          updateDebug('❌ Chatbot HTML NOT injected');
        }
      } else {
        updateDebug('❌ Chatbot instance NOT created');
      }
    }, 2000);
    
    // Force create function
    function forceCreateChatbot() {
      updateDebug('🚀 Force creating chatbot...');
      
      try {
        if (typeof AgroKartChatbot !== 'undefined') {
          window.forcedChatbot = new AgroKartChatbot();
          updateDebug('✅ Forced chatbot created successfully');
        } else {
          updateDebug('❌ Cannot create chatbot - class not available');
        }
      } catch (error) {
        updateDebug('❌ Error creating forced chatbot: ' + error.message);
      }
    }
    
    // Error monitoring
    window.addEventListener('error', (e) => {
      updateDebug('❌ JavaScript Error: ' + e.message + ' at ' + e.filename + ':' + e.lineno);
    });
    
    // Console logging
    console.log('Isolated test page loaded');
  </script>
</body>
</html>
