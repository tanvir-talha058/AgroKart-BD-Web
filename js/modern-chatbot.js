class ModernAgroKartChatbot {
    constructor() {
        this.isOpen = false;
        this.isTyping = false;
        this.conversationHistory = [];
        this.products = [];
        this.categories = ['Vegetables', 'Fruits', 'Grains', 'Dairy', 'Meat'];
        this.currentLanguage = 'en'; // Default to English
        this.voiceEnabled = false;
        this.recognition = null;
        this.synthesis = null;
        this.userPreferences = {};
        this.sessionId = this.generateSessionId();
        this.isConnectedToAgent = false;
        this.customerMood = 'neutral';
        this.unreadMessages = 0;
        this.isRecording = false;
        
        // Initialize speech recognition and synthesis
        this.initVoiceFeatures();
        
        // Enhanced response templates with multi-language support
        this.responses = {
            greetings: {
                en: [
                    "Hello! Welcome to AgroKart BD! 🌱 I'm your AI assistant. How can I help you today?",
                    "Hi there! I'm here to help you find the best agricultural products. What are you looking for?",
                    "Welcome to AgroKart BD! Your one-stop shop for fresh agricultural products. How may I assist you?",
                    "Good day! I'm your virtual farming assistant ready to help with all your agricultural needs! 🚜"
                ],
                bn: [
                    "হ্যালো! এগ্রোকার্ট বিডিতে স্বাগতম! 🌱 আমি আপনার AI সহায়ক। আজ আমি আপনাকে কীভাবে সাহায্য করতে পারি?",
                    "নমস্কার! আমি এখানে আছি আপনাকে সেরা কৃষি পণ্য খুঁজে পেতে সাহায্য করতে। আপনি কী খুঁজছেন?",
                    "এগ্রোকার্ট বিডিতে স্বাগতম! তাজা কৃষি পণ্যের জন্য আপনার এক-স্টপ শপ। আমি কীভাবে সহায়তা করতে পারি?"
                ]
            },
            help: {
                en: [
                    "I can help you with:\n• 🔍 Smart product search & recommendations\n• 💰 Price negotiations & bulk discounts\n• 📦 Real-time order tracking with GPS\n• ❌ Easy order cancellation & refunds\n• 📊 Analytics & purchase history\n• 🌟 Personalized recommendations\n• 🎤 Voice commands (say 'enable voice')\n• 📷 Image upload for product identification\n• 🔄 Language switching (English/Bengali)\n• 👨‍💼 Connect with live agent\n• 📱 SMS/Email notifications\n\nWhat would you like to explore?"
                ],
                bn: [
                    "আমি আপনাকে সাহায্য করতে পারি:\n• 🔍 স্মার্ট পণ্য অনুসন্ধান ও সুপারিশ\n• 💰 দাম দর-কষাকষি ও বাল্ক ছাড়\n• 📦 GPS সহ রিয়েল-টাইম অর্ডার ট্র্যাকিং\n• ❌ সহজ অর্ডার বাতিল ও রিফান্ড\n• 📊 বিশ্লেষণ ও ক্রয়ের ইতিহাস\n• 🌟 ব্যক্তিগত সুপারিশ\n• 🎤 ভয়েস কমান্ড ('ভয়েস চালু করুন' বলুন)\n• 📷 পণ্য সনাক্তকরণের জন্য ছবি আপলোড\n• 🔄 ভাষা পরিবর্তন (ইংরেজি/বাংলা)\n• 👨‍💼 লাইভ এজেন্টের সাথে সংযোগ\n• 📱 SMS/ইমেইল বিজ্ঞপ্তি\n\nআপনি কী অন্বেষণ করতে চান?"
                ]
            },
            products: {
                en: [
                    "We offer a wide variety of fresh agricultural products including vegetables, fruits, grains, and more. Would you like to see our current offerings?",
                    "Our product catalog includes farm-fresh vegetables, seasonal fruits, quality grains, and dairy products. What type of product interests you?",
                    "🌟 Featured today: Organic vegetables, premium rice varieties, and seasonal fruits! Use image search by uploading a photo!"
                ],
                bn: [
                    "আমরা সবজি, ফল, শস্য এবং আরও অনেক কিছু সহ বিভিন্ন ধরনের তাজা কৃষি পণ্য অফার করি। আপনি কি আমাদের বর্তমান পণ্যগুলি দেখতে চান?",
                    "আমাদের পণ্য ক্যাটালগে খামার-তাজা সবজি, মৌসুমী ফল, গুণমানের শস্য এবং দুগ্ধজাত পণ্য রয়েছে। কোন ধরনের পণ্য আপনার আগ্রহের?"
                ]
            },
            prices: {
                en: [
                    "Our prices are competitive and updated in real-time! 💰 I can help with:\n• Live price comparisons\n• Bulk discount calculations\n• Seasonal pricing alerts\n• Price negotiation for large orders\n\n*Tip: Upload a product image for instant price checking!*"
                ],
                bn: [
                    "আমাদের দাম প্রতিযোগিতামূলক এবং রিয়েল-টাইমে আপডেট হয়! 💰 আমি সাহায্য করতে পারি:\n• লাইভ দাম তুলনা\n• বাল্ক ছাড় গণনা\n• মৌসুমী দামের সতর্কতা\n• বড় অর্ডারের জন্য দাম দর-কষাকষি"
                ]
            },
            delivery: {
                en: [
                    "🚚 Smart Delivery Features:\n• GPS tracking in real-time\n• 2-4 hour express delivery in Dhaka\n• FREE delivery on orders over ৳500\n• SMS/Email delivery updates\n• Contactless delivery available\n• Delivery time prediction with AI\n\nWant to track an existing order?"
                ],
                bn: [
                    "🚚 স্মার্ট ডেলিভারি বৈশিষ্ট্য:\n• রিয়েল-টাইমে GPS ট্র্যাকিং\n• ঢাকায় ২-৪ ঘন্টা এক্সপ্রেস ডেলিভারি\n• ৳৫০০+ অর্ডারে বিনামূল্যে ডেলিভারি\n• SMS/ইমেইল ডেলিভারি আপডেট\n• যোগাযোগবিহীন ডেলিভারি উপলব্ধ"
                ]
            }
        };

        this.quickActions = [
            { text: "🛍️ View Products", action: "products", textBn: "🛍️ পণ্য দেখুন" },
            { text: "💰 Negotiate Price", action: "negotiate", textBn: "💰 দাম দর-কষাকষি" },
            { text: "📦 Track Order", action: "track", textBn: "📦 অর্ডার ট্র্যাক" },
            { text: "❌ Cancel Order", action: "cancel", textBn: "❌ অর্ডার বাতিল" },
            { text: "🎤 Voice Mode", action: "voice", textBn: "🎤 ভয়েস মোড" },
            { text: "👨‍💼 Live Agent", action: "agent", textBn: "👨‍💼 লাইভ এজেন্ট" },
            { text: "🌐 বাংলা", action: "language", textBn: "🌐 English" }
        ];

        this.init();
        this.loadProducts();
        this.loadUserPreferences();
    }

    // Initialize voice recognition and synthesis
    initVoiceFeatures() {
        try {
            // Speech Recognition
            if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                this.recognition = new SpeechRecognition();
                this.recognition.continuous = false;
                this.recognition.interimResults = false;
                this.recognition.lang = 'en-US';
                
                this.recognition.onresult = (event) => {
                    const transcript = event.results[0][0].transcript;
                    this.handleVoiceInput(transcript);
                };
                
                this.recognition.onerror = (event) => {
                    console.log('Speech recognition error:', event.error);
                    this.showVoiceError();
                };
                
                this.recognition.onend = () => {
                    this.stopVoiceRecording();
                };
            }

            // Speech Synthesis
            if ('speechSynthesis' in window) {
                this.synthesis = window.speechSynthesis;
            }
        } catch (error) {
            console.log('Voice features not supported in this browser');
        }
    }

    // Generate unique session ID
    generateSessionId() {
        return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    // Load user preferences from localStorage
    loadUserPreferences() {
        try {
            const saved = localStorage.getItem('agroKartChatPreferences');
            if (saved) {
                this.userPreferences = JSON.parse(saved);
                this.currentLanguage = this.userPreferences.language || 'en';
                this.voiceEnabled = this.userPreferences.voiceEnabled || false;
            }
        } catch (error) {
            console.log('Could not load user preferences');
        }
    }

    // Save user preferences
    saveUserPreferences() {
        try {
            this.userPreferences.language = this.currentLanguage;
            this.userPreferences.voiceEnabled = this.voiceEnabled;
            localStorage.setItem('agroKartChatPreferences', JSON.stringify(this.userPreferences));
        } catch (error) {
            console.log('Could not save user preferences');
        }
    }

    init() {
        this.createChatbotHTML();
        this.bindEvents();
        this.showWelcomeMessage();
        this.setupPushNotifications();
    }

    createChatbotHTML() {
        const chatbotHTML = `
            <div class="chatbot-container">
                <button class="chatbot-toggle" id="chatbotToggle">
                    <i class="fas fa-robot"></i>
                    <div class="notification-badge" id="chatbotBadge" style="display: none;">
                        <span id="badgeCount">1</span>
                    </div>
                    <div class="pulse-ring"></div>
                </button>
                <div class="chatbot-window" id="chatbotWindow">
                    <div class="chatbot-header">
                        <div class="header-info">
                            <div class="avatar-container">
                                <img src="images/AGrO.png" alt="AgroKart AI" class="bot-avatar">
                                <div class="status-indicator ${this.isConnectedToAgent ? 'agent' : 'online'}"></div>
                            </div>
                            <div class="header-text">
                                <h3>${this.isConnectedToAgent ? 'Live Agent' : 'AgroKart AI'}</h3>
                                <div class="chatbot-status">
                                    <span class="status-dot ${this.isConnectedToAgent ? 'agent' : 'online'}"></span>
                                    <span id="statusText">${this.isConnectedToAgent ? 'Agent Available' : 'AI Online'}</span>
                                </div>
                            </div>
                        </div>
                        <div class="header-controls">
                            <button class="control-btn" id="voiceToggle" title="${this.voiceEnabled ? 'Disable' : 'Enable'} Voice">
                                <i class="fas fa-${this.voiceEnabled ? 'microphone' : 'microphone-slash'}"></i>
                            </button>
                            <button class="control-btn" id="languageToggle" title="Switch Language">
                                <span class="lang-text">${this.currentLanguage === 'en' ? 'বাং' : 'EN'}</span>
                            </button>
                            <button class="control-btn" id="chatbotMinimize" title="Minimize">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button class="control-btn" id="chatbotClose" title="Close">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Quick Actions Panel -->
                    <div class="quick-actions-panel" id="quickActionsPanel">
                        <div class="quick-actions-scroll">
                            ${this.generateQuickActionsHTML()}
                        </div>
                    </div>
                    
                    <div class="chatbot-messages" id="chatbotMessages">
                        <div class="typing-indicator" id="typingIndicator">
                            <div class="typing-avatar">
                                <img src="images/AGrO.png" alt="Bot">
                            </div>
                            <div class="typing-bubble">
                                <div class="typing-dots">
                                    <div class="typing-dot"></div>
                                    <div class="typing-dot"></div>
                                    <div class="typing-dot"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Enhanced Input Area -->
                    <div class="chatbot-input-container">
                        <div class="input-features">
                            <button class="feature-btn" id="uploadBtn" title="Upload Image">
                                <i class="fas fa-camera"></i>
                            </button>
                            <button class="feature-btn" id="emojiBtn" title="Emojis">
                                <i class="fas fa-smile"></i>
                            </button>
                            <button class="feature-btn" id="micBtn" title="Voice Input">
                                <i class="fas fa-microphone"></i>
                            </button>
                        </div>
                        <div class="input-wrapper">
                            <input type="text" class="chatbot-input" id="chatbotInput" 
                                   placeholder="${this.currentLanguage === 'en' ? 'Type your message...' : 'আপনার বার্তা টাইপ করুন...'}">
                            <input type="file" id="fileInput" accept="image/*" style="display: none;">
                        </div>
                        <button class="chatbot-send" id="chatbotSend">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                    
                    <!-- Voice Recording Indicator -->
                    <div class="voice-recording" id="voiceRecording" style="display: none;">
                        <div class="voice-animation">
                            <div class="voice-wave"></div>
                            <div class="voice-wave"></div>
                            <div class="voice-wave"></div>
                        </div>
                        <span>Listening...</span>
                        <button class="stop-recording" id="stopRecording">
                            <i class="fas fa-stop"></i>
                        </button>
                    </div>
                    
                    <!-- Customer Satisfaction Popup -->
                    <div class="satisfaction-popup" id="satisfactionPopup" style="display: none;">
                        <h4>How was your experience?</h4>
                        <div class="rating-stars">
                            <span class="star" data-rating="1">⭐</span>
                            <span class="star" data-rating="2">⭐</span>
                            <span class="star" data-rating="3">⭐</span>
                            <span class="star" data-rating="4">⭐</span>
                            <span class="star" data-rating="5">⭐</span>
                        </div>
                        <button class="skip-rating">Skip</button>
                    </div>
                </div>
            </div>
        `;
        
        // Try to use the provided container, otherwise append to body
        const container = document.getElementById('chatbot-container');
        if (container) {
            container.innerHTML = chatbotHTML;
        } else {
            document.body.insertAdjacentHTML('beforeend', chatbotHTML);
        }
    }

    generateQuickActionsHTML() {
        return this.quickActions.map(action => {
            const text = this.currentLanguage === 'en' ? action.text : action.textBn;
            return `<button class="quick-action-btn" data-action="${action.action}">${text}</button>`;
        }).join('');
    }

    bindEvents() {
        // Toggle chatbot
        document.getElementById('chatbotToggle').addEventListener('click', () => this.toggleChatbot());
        
        // Close chatbot
        document.getElementById('chatbotClose').addEventListener('click', () => this.closeChatbot());
        
        // Minimize chatbot
        document.getElementById('chatbotMinimize').addEventListener('click', () => this.minimizeChatbot());
        
        // Send message
        document.getElementById('chatbotSend').addEventListener('click', () => this.sendMessage());
        document.getElementById('chatbotInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.sendMessage();
        });

        // Voice features
        document.getElementById('voiceToggle').addEventListener('click', () => this.toggleVoice());
        document.getElementById('micBtn').addEventListener('click', () => this.startVoiceRecording());
        document.getElementById('stopRecording').addEventListener('click', () => this.stopVoiceRecording());

        // Language toggle
        document.getElementById('languageToggle').addEventListener('click', () => this.toggleLanguage());

        // File upload
        document.getElementById('uploadBtn').addEventListener('click', () => this.triggerFileUpload());
        document.getElementById('fileInput').addEventListener('change', (e) => this.handleFileUpload(e));

        // Quick actions
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('quick-action-btn')) {
                this.handleQuickAction(e.target.dataset.action);
            }
        });

        // Rating system
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('star')) {
                this.handleRating(e.target.dataset.rating);
            }
            if (e.target.classList.contains('skip-rating')) {
                this.hideSatisfactionPopup();
            }
        });
    }

    toggleChatbot() {
        this.isOpen = !this.isOpen;
        const window = document.getElementById('chatbotWindow');
        const badge = document.getElementById('chatbotBadge');
        
        if (this.isOpen) {
            window.style.display = 'flex';
            badge.style.display = 'none';
            this.unreadMessages = 0;
            this.updateQuickActions();
        } else {
            window.style.display = 'none';
        }
    }

    closeChatbot() {
        this.isOpen = false;
        document.getElementById('chatbotWindow').style.display = 'none';
        this.showSatisfactionPopup();
    }

    minimizeChatbot() {
        const window = document.getElementById('chatbotWindow');
        window.style.height = window.style.height === '60px' ? '500px' : '60px';
    }

    async sendMessage() {
        const input = document.getElementById('chatbotInput');
        const message = input.value.trim();
        
        if (!message) return;
        
        input.value = '';
        this.addMessage(message, 'user');
        
        // Analyze sentiment
        this.analyzeSentiment(message);
        
        this.showTyping();
        
        try {
            const response = await this.generateResponse(message);
            this.hideTyping();
            this.addMessage(response, 'bot');
            
            // Text-to-speech if enabled
            if (this.voiceEnabled) {
                this.speakText(response);
            }
        } catch (error) {
            this.hideTyping();
            this.addMessage("I'm sorry, I encountered an error. Please try again.", 'bot');
        }
    }

    addMessage(text, sender) {
        const messagesContainer = document.getElementById('chatbotMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message`;
        
        if (sender === 'bot') {
            messageDiv.innerHTML = `
                <div class="message-avatar">
                    <img src="images/AGrO.png" alt="Bot">
                </div>
                <div class="message-content">
                    <div class="message-bubble">
                        ${this.formatMessage(text)}
                    </div>
                    <div class="message-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                </div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="message-content">
                    <div class="message-bubble">
                        ${this.formatMessage(text)}
                    </div>
                    <div class="message-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                </div>
            `;
        }
        
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        this.conversationHistory.push({ text, sender, timestamp: new Date() });
        
        if (!this.isOpen && sender === 'bot') {
            this.showNotification();
        }
    }

    formatMessage(text) {
        // Convert URLs to links
        text = text.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank">$1</a>');
        
        // Convert newlines to breaks
        text = text.replace(/\n/g, '<br>');
        
        // Add emoji support
        return text;
    }

    showTyping() {
        this.isTyping = true;
        document.getElementById('typingIndicator').style.display = 'flex';
        const messagesContainer = document.getElementById('chatbotMessages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    hideTyping() {
        this.isTyping = false;
        document.getElementById('typingIndicator').style.display = 'none';
    }

    async generateResponse(message) {
        const lowerMessage = message.toLowerCase();
        
        // Store message for analytics
        this.trackInteraction(message);
        
        // Check for specific intents
        if (this.isGreeting(lowerMessage)) {
            return this.getLocalizedResponse('greetings') + this.getQuickActionsText();
        }
        
        if (this.isAskingForHelp(lowerMessage)) {
            return this.getLocalizedResponse('help');
        }
        
        if (this.isAskingAboutProducts(lowerMessage)) {
            return await this.handleProductInquiry(lowerMessage);
        }
        
        if (this.isAskingAboutPrices(lowerMessage)) {
            return this.getLocalizedResponse('prices');
        }
        
        if (this.isAskingAboutDelivery(lowerMessage)) {
            return this.getLocalizedResponse('delivery');
        }
        
        // Order tracking
        if (this.isAskingAboutTracking(lowerMessage)) {
            return await this.handleOrderTracking(message);
        }
        
        // Order cancellation
        if (this.isAskingAboutCancellation(lowerMessage)) {
            return await this.handleOrderCancellation(message);
        }
        
        // Price negotiation
        if (this.isAskingAboutNegotiation(lowerMessage)) {
            return await this.handlePriceNegotiation(message);
        }
        
        // Live agent request
        if (this.isRequestingAgent(lowerMessage)) {
            return this.handleAgentRequest();
        }
        
        // Default AI response
        return this.getIntelligentResponse(message);
    }

    // Intent detection methods
    isGreeting(message) {
        const greetings = ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 'greetings', 'hola', 'namaste'];
        return greetings.some(greeting => message.includes(greeting));
    }

    isAskingForHelp(message) {
        const helpKeywords = ['help', 'assist', 'support', 'what can you do', 'how can you help'];
        return helpKeywords.some(keyword => message.includes(keyword));
    }

    isAskingAboutProducts(message) {
        const productKeywords = ['product', 'item', 'vegetable', 'fruit', 'grain', 'what do you have', 'catalog', 'available'];
        return productKeywords.some(keyword => message.includes(keyword));
    }

    isAskingAboutPrices(message) {
        const priceKeywords = ['price', 'cost', 'how much', 'rate', 'charge', 'fee'];
        return priceKeywords.some(keyword => message.includes(keyword));
    }

    isAskingAboutDelivery(message) {
        const deliveryKeywords = ['delivery', 'shipping', 'transport', 'when will i get', 'how long'];
        return deliveryKeywords.some(keyword => message.includes(keyword));
    }

    isAskingAboutTracking(message) {
        const trackKeywords = ['track', 'status', 'where is my order', 'order status'];
        return trackKeywords.some(keyword => message.includes(keyword));
    }

    isAskingAboutCancellation(message) {
        const cancelKeywords = ['cancel', 'refund', 'return', 'cancel order'];
        return cancelKeywords.some(keyword => message.includes(keyword));
    }

    isAskingAboutNegotiation(message) {
        const negotiateKeywords = ['negotiate', 'discount', 'bulk', 'wholesale', 'better price'];
        return negotiateKeywords.some(keyword => message.includes(keyword));
    }

    isRequestingAgent(message) {
        const agentKeywords = ['agent', 'human', 'representative', 'talk to person', 'live chat'];
        return agentKeywords.some(keyword => message.includes(keyword));
    }

    // Response handlers
    getLocalizedResponse(category) {
        const responses = this.responses[category][this.currentLanguage];
        return responses[Math.floor(Math.random() * responses.length)];
    }

    getQuickActionsText() {
        const text = this.currentLanguage === 'en' ? 
            '\n\n🚀 Quick Actions:' : 
            '\n\n🚀 দ্রুত কার্যক্রম:';
        return text;
    }

    async handleProductInquiry(message) {
        try {
            // Call product API
            const response = await fetch('php/chatbot_products_api.php?action=search&query=' + encodeURIComponent(message));
            const data = await response.json();
            
            if (data.success) {
                const products = data.data.slice(0, 3); // Show top 3 products
                let responseText = this.currentLanguage === 'en' ? 
                    "Here are some products you might be interested in:\n\n" :
                    "আপনার আগ্রহের কিছু পণ্য এখানে রয়েছে:\n\n";
                
                products.forEach((product, index) => {
                    responseText += `${index + 1}. ${product.name} - ৳${product.price}/${product.unit}\n`;
                });
                
                return responseText + '\n' + this.getQuickActionsText();
            }
        } catch (error) {
            console.error('Product inquiry error:', error);
        }
        
        return this.getLocalizedResponse('products');
    }

    async handleOrderTracking(message) {
        // Extract order ID from message
        const orderIdMatch = message.match(/#?(\w+)/);
        if (!orderIdMatch) {
            return this.currentLanguage === 'en' ? 
                "Please provide your order ID to track your order. Example: #ORD123" :
                "আপনার অর্ডার ট্র্যাক করতে অর্ডার আইডি প্রদান করুন। উদাহরণ: #ORD123";
        }
        
        const orderId = orderIdMatch[1];
        
        try {
            const response = await fetch(`php/chatbot_api.php?action=track_order&order_id=${orderId}`);
            const data = await response.json();
            
            if (data.success) {
                const tracking = data.data;
                let responseText = `📦 Order #${orderId} Status:\n\n`;
                
                tracking.stages.forEach(stage => {
                    const status = stage.status === 'completed' ? '✅' : stage.status === 'current' ? '🔄' : '⏳';
                    responseText += `${status} ${stage.name}`;
                    if (stage.time) {
                        responseText += ` - ${new Date(stage.time).toLocaleString()}`;
                    }
                    responseText += '\n';
                });
                
                if (tracking.estimated_delivery) {
                    responseText += `\n🚚 Estimated Delivery: ${new Date(tracking.estimated_delivery).toLocaleString()}`;
                }
                
                return responseText;
            } else {
                return "Sorry, I couldn't find an order with that ID. Please check and try again.";
            }
        } catch (error) {
            return "I'm having trouble accessing order information right now. Please try again later.";
        }
    }

    async handleOrderCancellation(message) {
        const orderIdMatch = message.match(/#?(\w+)/);
        if (!orderIdMatch) {
            return "Please provide your order ID to cancel. Example: #ORD123";
        }
        
        const orderId = orderIdMatch[1];
        
        try {
            const response = await fetch(`php/chatbot_api.php?action=cancel_order&order_id=${orderId}`);
            const data = await response.json();
            
            if (data.success) {
                const cancellation = data.data;
                if (cancellation.can_cancel) {
                    return `Order #${orderId} can be cancelled.\n\n` +
                           `💰 Refund Amount: ৳${cancellation.refund_amount}\n` +
                           `📈 Refund Percentage: ${cancellation.refund_percentage}%\n\n` +
                           `Reply "CONFIRM CANCEL ${orderId}" to proceed.`;
                }
            }
        } catch (error) {
            return "I'm having trouble processing the cancellation request. Please contact customer service.";
        }
    }

    async handlePriceNegotiation(message) {
        return "💰 I'd be happy to help with price negotiations!\n\n" +
               "For bulk orders (10+ items) or orders over ৳2000, we offer:\n" +
               "• 5-15% discount based on quantity\n" +
               "• Special wholesale prices\n" +
               "• Seasonal promotions\n\n" +
               "What product and quantity are you interested in?";
    }

    handleAgentRequest() {
        this.isConnectedToAgent = true;
        this.updateHeaderForAgent();
        return "🔄 Connecting you to a live agent...\n\n" +
               "A customer service representative will be with you shortly. " +
               "Average wait time: 2-3 minutes.\n\n" +
               "While you wait, feel free to ask me any questions!";
    }

    getIntelligentResponse(message) {
        // Simple AI-like responses based on keywords
        const responses = {
            en: [
                "That's interesting! Can you tell me more about what you're looking for?",
                "I understand. Let me help you with that. What specific information do you need?",
                "Thanks for your question! I'm here to help. Could you provide more details?",
                "I'd be happy to assist you with that. What would you like to know?"
            ],
            bn: [
                "এটি আকর্ষণীয়! আপনি কী খুঁজছেন সে সম্পর্কে আরও বলতে পারেন?",
                "আমি বুঝতে পারছি। আমি আপনাকে সাহায্য করতে দিন। আপনার কোন নির্দিষ্ট তথ্য প্রয়োজন?",
                "আপনার প্রশ্নের জন্য ধন্যবাদ! আমি সাহায্য করতে এখানে আছি। আরও বিস্তারিত বলতে পারেন?"
            ]
        };
        
        const responseArray = responses[this.currentLanguage];
        return responseArray[Math.floor(Math.random() * responseArray.length)];
    }

    // Voice features
    toggleVoice() {
        this.voiceEnabled = !this.voiceEnabled;
        this.saveUserPreferences();
        
        const button = document.getElementById('voiceToggle');
        const icon = button.querySelector('i');
        
        if (this.voiceEnabled) {
            icon.className = 'fas fa-microphone';
            button.title = 'Disable Voice';
        } else {
            icon.className = 'fas fa-microphone-slash';
            button.title = 'Enable Voice';
        }
    }

    startVoiceRecording() {
        if (!this.recognition) {
            this.addMessage("Voice recognition not supported in this browser.", 'bot');
            return;
        }
        
        this.isRecording = true;
        document.getElementById('voiceRecording').style.display = 'flex';
        
        try {
            this.recognition.lang = this.currentLanguage === 'en' ? 'en-US' : 'bn-BD';
            this.recognition.start();
        } catch (error) {
            this.showVoiceError();
        }
    }

    stopVoiceRecording() {
        this.isRecording = false;
        document.getElementById('voiceRecording').style.display = 'none';
        
        if (this.recognition) {
            this.recognition.stop();
        }
    }

    handleVoiceInput(transcript) {
        document.getElementById('chatbotInput').value = transcript;
        this.sendMessage();
        this.stopVoiceRecording();
    }

    speakText(text) {
        if (!this.synthesis) return;
        
        // Remove HTML tags and format for speech
        const cleanText = text.replace(/<[^>]*>/g, '').replace(/\n/g, '. ');
        
        const utterance = new SpeechSynthesisUtterance(cleanText);
        utterance.lang = this.currentLanguage === 'en' ? 'en-US' : 'bn-BD';
        utterance.rate = 0.8;
        utterance.volume = 0.8;
        
        this.synthesis.speak(utterance);
    }

    showVoiceError() {
        this.addMessage("Sorry, I couldn't understand that. Please try again or type your message.", 'bot');
        this.stopVoiceRecording();
    }

    // Language switching
    toggleLanguage() {
        this.currentLanguage = this.currentLanguage === 'en' ? 'bn' : 'en';
        this.saveUserPreferences();
        this.updateLanguageUI();
    }

    updateLanguageUI() {
        // Update placeholder
        const input = document.getElementById('chatbotInput');
        input.placeholder = this.currentLanguage === 'en' ? 'Type your message...' : 'আপনার বার্তা টাইপ করুন...';
        
        // Update language toggle button
        const langBtn = document.getElementById('languageToggle');
        langBtn.querySelector('.lang-text').textContent = this.currentLanguage === 'en' ? 'বাং' : 'EN';
        
        // Update quick actions
        this.updateQuickActions();
    }

    updateQuickActions() {
        const panel = document.getElementById('quickActionsPanel');
        panel.innerHTML = `<div class="quick-actions-scroll">${this.generateQuickActionsHTML()}</div>`;
    }

    // File upload
    triggerFileUpload() {
        document.getElementById('fileInput').click();
    }

    async handleFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        // Show uploading message
        this.addMessage("📷 Image uploaded! Analyzing...", 'user');
        this.showTyping();
        
        try {
            const formData = new FormData();
            formData.append('image', file);
            
            const response = await fetch('php/image_recognition_api.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            this.hideTyping();
            
            if (data.success) {
                this.addMessage(`🔍 I can see this is ${data.product_name}!\n\n💰 Current price: ৳${data.price}/${data.unit}\n📦 Stock: ${data.stock} available\n\nWould you like to add this to your cart?`, 'bot');
            } else {
                this.addMessage("I couldn't identify the product in the image. Could you tell me what it is?", 'bot');
            }
        } catch (error) {
            this.hideTyping();
            this.addMessage("Sorry, I had trouble processing the image. Please try again.", 'bot');
        }
        
        // Reset file input
        event.target.value = '';
    }

    // Quick actions handler
    handleQuickAction(action) {
        switch (action) {
            case 'products':
                this.sendMessage('Show me your products');
                break;
            case 'negotiate':
                this.sendMessage('I want to negotiate prices');
                break;
            case 'track':
                this.sendMessage('Track my order');
                break;
            case 'cancel':
                this.sendMessage('Cancel my order');
                break;
            case 'voice':
                this.toggleVoice();
                break;
            case 'agent':
                this.sendMessage('Connect me to a live agent');
                break;
            case 'language':
                this.toggleLanguage();
                break;
        }
    }

    // Utility methods
    showWelcomeMessage() {
        setTimeout(() => {
            this.addMessage(this.getLocalizedResponse('greetings'), 'bot');
        }, 1000);
    }

    showNotification() {
        this.unreadMessages++;
        const badge = document.getElementById('chatbotBadge');
        const count = document.getElementById('badgeCount');
        
        badge.style.display = 'block';
        count.textContent = this.unreadMessages;
        
        // Browser notification if permission granted
        if (Notification.permission === 'granted') {
            new Notification('AgroKart Assistant', {
                body: 'You have a new message!',
                icon: 'images/AGrO.png'
            });
        }
    }

    setupPushNotifications() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    analyzeSentiment(message) {
        // Simple sentiment analysis
        const positive = ['good', 'great', 'excellent', 'love', 'happy', 'satisfied'];
        const negative = ['bad', 'terrible', 'hate', 'angry', 'frustrated', 'disappointed'];
        
        const lowerMessage = message.toLowerCase();
        
        if (positive.some(word => lowerMessage.includes(word))) {
            this.customerMood = 'positive';
        } else if (negative.some(word => lowerMessage.includes(word))) {
            this.customerMood = 'negative';
            // Offer extra help for frustrated customers
            setTimeout(() => {
                this.addMessage("I notice you might be frustrated. Would you like me to connect you with a live agent for better assistance? 🤝", 'bot');
            }, 2000);
        } else {
            this.customerMood = 'neutral';
        }
    }

    trackInteraction(message) {
        // Track user interactions for analytics
        const interaction = {
            sessionId: this.sessionId,
            message: message,
            timestamp: new Date(),
            language: this.currentLanguage,
            mood: this.customerMood
        };
        
        // Send to analytics API (implement as needed)
        try {
            fetch('php/analytics_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(interaction)
            });
        } catch (error) {
            console.log('Analytics tracking error:', error);
        }
    }

    updateHeaderForAgent() {
        const headerInfo = document.querySelector('.header-info h3');
        const statusText = document.getElementById('statusText');
        const statusDot = document.querySelector('.status-dot');
        const statusIndicator = document.querySelector('.status-indicator');
        
        headerInfo.textContent = 'Live Agent';
        statusText.textContent = 'Agent Available';
        statusDot.className = 'status-dot agent';
        statusIndicator.className = 'status-indicator agent';
    }

    showSatisfactionPopup() {
        // Show after conversation ends
        if (this.conversationHistory.length > 3) {
            setTimeout(() => {
                document.getElementById('satisfactionPopup').style.display = 'block';
            }, 2000);
        }
    }

    hideSatisfactionPopup() {
        document.getElementById('satisfactionPopup').style.display = 'none';
    }

    handleRating(rating) {
        this.hideSatisfactionPopup();
        
        // Send rating to server
        try {
            fetch('php/rating_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    sessionId: this.sessionId,
                    rating: rating,
                    timestamp: new Date()
                })
            });
        } catch (error) {
            console.log('Rating submission error:', error);
        }
        
        this.addMessage(`Thank you for rating us ${rating} stars! Your feedback helps us improve. 🌟`, 'bot');
    }

    async loadProducts() {
        try {
            const response = await fetch('php/chatbot_products_api.php?action=get_all');
            const data = await response.json();
            if (data.success) {
                this.products = data.data;
            }
        } catch (error) {
            console.log('Could not load products:', error);
        }
    }
}

// Initialize the chatbot when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.modernChatbot = new ModernAgroKartChatbot();
});
