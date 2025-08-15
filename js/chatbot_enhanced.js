class AgroKartChatbot {
    constructor() {
        this.isOpen = false;
        this.isTyping = false;
        this.conversationHistory = [];
        this.products = [];
        this.categories = ['Vegetables', 'Fruits', 'Grains', 'Dairy', 'Meat'];
        this.currentLanguage = 'en';
        this.voiceEnabled = false;
        this.recognition = null;
        this.synthesis = null;
        this.userPreferences = {};
        this.sessionId = this.generateSessionId();
        this.isConnectedToAgent = false;
        this.customerMood = 'neutral';
        this.unreadMessages = 0;
        this.showingFeatureMenu = false;
        
        // Initialize speech recognition and synthesis
        this.initVoiceFeatures();
        
        // Enhanced response templates with multi-language support
        this.responses = {
            greetings: {
                en: [
                    "Hello! Welcome to AgroKart BD! 🌱 I'm your AI assistant. How can I help you today?",
                    "Hi there! I'm here to help you with all your agricultural product needs!",
                    "Welcome to AgroKart BD! Your one-stop shop for fresh agricultural products. How may I assist you?",
                    "Good day! I'm your virtual farming assistant ready to help with all your agricultural needs! 🚜"
                ],
                bn: [
                    "হ্যালো! এগ্রোকার্ট বিডিতে স্বাগতম! 🌱 আমি আপনার AI সহায়ক। আজ আমি আপনাকে কীভাবে সাহায্য করতে পারি?",
                    "নমস্কার! আমি এখানে আছি আপনাকে সেরা কৃষি পণ্য খুঁজে পেতে সাহায্য করতে।",
                    "এগ্রোকার্ট বিডিতে স্বাগতম! তাজা কৃষি পণ্যের জন্য আপনার এক-স্টপ শপ।"
                ]
            },
            help: {
                en: [
                    "I can help you with all customer features:\n\n• 🛍️ Browse & search products\n• 💖 Manage your wishlist\n• ⚖️ Compare products side-by-side\n• 🏆 Check loyalty points & benefits\n• 🎯 Get personalized recommendations\n• ⚡ Quick reorder from history\n• 🔔 Set product notifications\n• 📦 Track your orders\n• 💰 Get bulk pricing\n• 👁️ View recently seen products\n\nUse the menu buttons below or just tell me what you need!"
                ],
                bn: [
                    "আমি সব গ্রাহক বৈশিষ্ট্যে আপনাকে সাহায্য করতে পারি:\n\n• 🛍️ পণ্য ব্রাউজ ও অনুসন্ধান\n• 💖 আপনার উইশলিস্ট পরিচালনা\n• ⚖️ পণ্য তুলনা করুন\n• 🏆 লয়ালটি পয়েন্ট ও সুবিধা\n• 🎯 ব্যক্তিগত সুপারিশ\n• ⚡ ইতিহাস থেকে দ্রুত অর্ডার\n• 🔔 পণ্য বিজ্ঞপ্তি সেট করুন\n• 📦 আপনার অর্ডার ট্র্যাক করুন\n\nনিচের মেনু বোতাম ব্যবহার করুন বা আমাকে বলুন আপনার কী প্রয়োজন!"
                ]
            }
        };

        // Primary Quick Actions - Main Customer Features
        this.quickActions = [
            { text: "🛍️ Browse Products", action: "products", textBn: "🛍️ পণ্য ব্রাউজ" },
            { text: "💖 My Wishlist", action: "wishlist", textBn: "💖 উইশলিস্ট" },
            { text: "⚖️ Compare Products", action: "compare", textBn: "⚖️ তুলনা" },
            { text: "🏆 Loyalty Program", action: "loyalty", textBn: "🏆 লয়ালটি" },
            { text: "🎯 Recommendations", action: "recommendations", textBn: "🎯 সুপারিশ" },
            { text: "⚡ Quick Reorder", action: "reorder", textBn: "⚡ দ্রুত অর্ডার" },
            { text: "🔔 Notifications", action: "notifications", textBn: "🔔 বিজ্ঞপ্তি" },
            { text: "📦 Track Order", action: "track", textBn: "📦 ট্র্যাক" }
        ];

        // Secondary actions for additional features
        this.secondaryActions = [
            { text: "💰 Bulk Pricing", action: "bulk_pricing", textBn: "💰 বাল্ক মূল্য" },
            { text: "👁️ Recently Viewed", action: "recent_viewed", textBn: "👁️ সম্প্রতি দেখা" },
            { text: "🔍 Smart Search", action: "smart_search", textBn: "🔍 স্মার্ট অনুসন্ধান" },
            { text: "❌ Cancel Order", action: "cancel", textBn: "❌ অর্ডার বাতিল" },
            { text: "📊 Order History", action: "order_history", textBn: "📊 অর্ডার ইতিহাস" },
            { text: "💬 Live Chat", action: "live_chat", textBn: "💬 লাইভ চ্যাট" },
            { text: "🎤 Voice Mode", action: "voice", textBn: "🎤 ভয়েস" },
            { text: "🌐 বাংলা", action: "language", textBn: "🌐 English" }
        ];

        this.init();
        this.loadProducts();
        this.loadUserPreferences();
    }

    // Initialize voice recognition and synthesis
    initVoiceFeatures() {
        try {
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
            }

            if ('speechSynthesis' in window) {
                this.synthesis = window.speechSynthesis;
            }
        } catch (error) {
            console.log('Voice features not supported in this browser');
        }
    }

    generateSessionId() {
        return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

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
        // Use setTimeout to ensure DOM elements are rendered before binding events
        setTimeout(() => {
            this.bindEvents();
            this.showWelcomeMessage();
        }, 100);
    }

    createChatbotHTML() {
        const chatbotHTML = `
            <!-- Chatbot Toggle Button -->
            <div class="chatbot-toggle" id="chatbotToggle">
                <div class="chatbot-icon">
                    <img src="images/AGrO.png" alt="AgroKart Assistant">
                </div>
                <div class="chatbot-notification" id="chatbotNotification" style="display: none;">
                    <span id="notificationCount">1</span>
                </div>
            </div>

            <!-- Enhanced Chatbot Container -->
            <div class="chatbot-container" id="chatbotContainer">
                <div class="chatbot-header">
                    <div class="chatbot-avatar">
                        <img src="images/AGrO.png" alt="AgroKart Assistant">
                        <div class="status-indicator online"></div>
                    </div>
                    <div class="chatbot-info">
                        <h4>AgroKart Assistant 🌱</h4>
                        <p class="status-text">
                            ${this.currentLanguage === 'en' ? 'Online • Ready to help with all features!' : 'অনলাইন • সব বৈশিষ্ট্যে সাহায্যের জন্য প্রস্তুত!'}
                        </p>
                    </div>
                    <div class="chatbot-controls">
                        <button class="control-btn" id="featureMenuBtn" title="Feature Menu">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button class="control-btn" id="settingsBtn" title="Settings">
                            <i class="fas fa-cog"></i>
                        </button>
                        <button class="control-btn" id="chatbotClose" title="Close">
                            <i class="fas fa-times"></i>
                        </button>
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
                
                <!-- Feature Menu Panel -->
                <div class="feature-menu-panel" id="featureMenuPanel" style="display: none;">
                    <div class="feature-menu-header">
                        <h4>${this.currentLanguage === 'en' ? '🌟 Customer Features' : '🌟 গ্রাহক বৈশিষ্ট্য'}</h4>
                        <button class="close-menu-btn" id="closeFeatureMenu">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="feature-grid" id="featureGrid">
                        ${this.generateFeatureGrid()}
                    </div>
                    <div class="secondary-features">
                        <h5>${this.currentLanguage === 'en' ? 'More Options:' : 'আরও বিকল্প:'}</h5>
                        <div class="secondary-grid" id="secondaryGrid">
                            ${this.generateSecondaryGrid()}
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions Panel -->
                <div class="quick-actions" id="quickActions">
                    ${this.generateQuickActionsHTML()}
                </div>
                
                <!-- Input Area -->
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
                               placeholder="${this.currentLanguage === 'en' ? 'Type your message or click a feature button...' : 'আপনার বার্তা টাইপ করুন বা ফিচার বোতাম ক্লিক করুন...'}">
                        <input type="file" id="fileInput" accept="image/*" style="display: none;">
                    </div>
                    <button class="chatbot-send" id="chatbotSend">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    }

    generateFeatureGrid() {
        return this.quickActions.map(action => {
            const text = this.currentLanguage === 'en' ? action.text : action.textBn;
            return `
                <div class="feature-card" data-action="${action.action}">
                    <div class="feature-icon">${text.split(' ')[0]}</div>
                    <div class="feature-text">${text.substring(text.indexOf(' ') + 1)}</div>
                </div>
            `;
        }).join('');
    }

    generateSecondaryGrid() {
        return this.secondaryActions.map(action => {
            const text = this.currentLanguage === 'en' ? action.text : action.textBn;
            return `
                <button class="secondary-feature-btn" data-action="${action.action}">
                    ${text}
                </button>
            `;
        }).join('');
    }

    generateQuickActionsHTML() {
        return this.quickActions.slice(0, 4).map(action => {
            const text = this.currentLanguage === 'en' ? action.text : action.textBn;
            return `<button class="quick-action-btn" data-action="${action.action}">${text}</button>`;
        }).join('');
    }

    bindEvents() {
        const toggle = document.getElementById('chatbotToggle');
        const close = document.getElementById('chatbotClose');
        const send = document.getElementById('chatbotSend');
        const input = document.getElementById('chatbotInput');
        const featureMenuBtn = document.getElementById('featureMenuBtn');
        const closeFeatureMenu = document.getElementById('closeFeatureMenu');

        // Check if all elements exist before binding events
        if (!toggle || !close || !send || !input || !featureMenuBtn) {
            console.error('Chatbot elements not found! Retrying in 500ms...');
            setTimeout(() => this.bindEvents(), 500);
            return;
        }

        toggle.addEventListener('click', () => this.toggleChatbot());
        close.addEventListener('click', () => this.closeChatbot());
        send.addEventListener('click', () => this.sendMessage());
        featureMenuBtn.addEventListener('click', () => this.toggleFeatureMenu());
        
        if (closeFeatureMenu) {
            closeFeatureMenu.addEventListener('click', () => this.hideFeatureMenu());
        }
        
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });

        // Bind quick action events
        document.addEventListener('click', (e) => {
            if (e.target.matches('.quick-action-btn, .feature-card, .secondary-feature-btn')) {
                const action = e.target.dataset.action || e.target.closest('[data-action]').dataset.action;
                this.handleFeatureAction(action);
            }
        });

        // Voice controls
        const micBtn = document.getElementById('micBtn');
        const uploadBtn = document.getElementById('uploadBtn');
        if (micBtn) micBtn.addEventListener('click', () => this.toggleVoiceInput());
        if (uploadBtn) uploadBtn.addEventListener('click', () => this.handleImageUpload());
        
        console.log('Chatbot events successfully bound!');
    }

    toggleChatbot() {
        const container = document.getElementById('chatbotContainer');
        this.isOpen = !this.isOpen;
        
        if (this.isOpen) {
            container.style.display = 'flex';
            container.classList.add('chatbot-open');
            this.hideNotification();
        } else {
            container.classList.remove('chatbot-open');
            setTimeout(() => {
                container.style.display = 'none';
            }, 300);
        }
    }

    closeChatbot() {
        this.isOpen = false;
        const container = document.getElementById('chatbotContainer');
        container.classList.remove('chatbot-open');
        setTimeout(() => {
            container.style.display = 'none';
        }, 300);
    }

    toggleFeatureMenu() {
        const panel = document.getElementById('featureMenuPanel');
        const isVisible = panel.style.display !== 'none';
        
        if (isVisible) {
            this.hideFeatureMenu();
        } else {
            this.showFeatureMenu();
        }
    }

    showFeatureMenu() {
        const panel = document.getElementById('featureMenuPanel');
        const messages = document.getElementById('chatbotMessages');
        
        panel.style.display = 'block';
        messages.style.height = '200px';
        this.showingFeatureMenu = true;
        
        this.addMessage(
            this.currentLanguage === 'en' 
                ? "🌟 **Feature Menu Opened!**\n\nClick on any feature card above to access that functionality directly. I can help you with all these customer features!" 
                : "🌟 **ফিচার মেনু খোলা হয়েছে!**\n\nযেকোনো ফিচার কার্ডে ক্লিক করুন সরাসরি সেই কার্যকারিতা অ্যাক্সেস করতে।", 
            'bot'
        );
    }

    hideFeatureMenu() {
        const panel = document.getElementById('featureMenuPanel');
        const messages = document.getElementById('chatbotMessages');
        
        panel.style.display = 'none';
        messages.style.height = '400px';
        this.showingFeatureMenu = false;
    }

    async handleFeatureAction(action) {
        this.hideFeatureMenu();
        
        switch (action) {
            case 'products':
                this.handleProductsBrowse();
                break;
            case 'wishlist':
                this.handleWishlist();
                break;
            case 'compare':
                this.handleProductComparison();
                break;
            case 'loyalty':
                this.handleLoyaltyProgram();
                break;
            case 'recommendations':
                this.handleRecommendations();
                break;
            case 'reorder':
                this.handleQuickReorder();
                break;
            case 'notifications':
                this.handleNotifications();
                break;
            case 'track':
                this.handleOrderTracking();
                break;
            case 'bulk_pricing':
                this.handleBulkPricing();
                break;
            case 'recent_viewed':
                this.handleRecentlyViewed();
                break;
            case 'smart_search':
                this.handleSmartSearch();
                break;
            case 'cancel':
                this.handleOrderCancellation();
                break;
            case 'order_history':
                this.handleOrderHistory();
                break;
            case 'live_chat':
                this.handleLiveChat();
                break;
            case 'voice':
                this.toggleVoiceMode();
                break;
            case 'language':
                this.toggleLanguage();
                break;
            default:
                this.addMessage("Feature coming soon! 🚀", 'bot');
        }
    }

    // Feature Handlers
    handleProductsBrowse() {
        this.addMessage(
            "🛍️ **Browse Products**\n\n" +
            "I can help you find products! Try:\n\n" +
            "• 'Show me vegetables'\n" +
            "• 'Do you have tomatoes?'\n" +
            "• 'Latest fruits available'\n" +
            "• 'Products under ৳50'\n\n" +
            "Or you can [Browse All Products](products.php) directly!",
            'bot'
        );
    }

    handleWishlist() {
        this.addMessage(
            "💖 **My Wishlist**\n\n" +
            "Access your saved products:\n\n" +
            "🔗 [View My Wishlist](wishlist.php)\n\n" +
            "You can also ask me:\n" +
            "• 'Show my wishlist items'\n" +
            "• 'Add [product] to wishlist'\n" +
            "• 'Remove [product] from wishlist'",
            'bot'
        );
    }

    handleProductComparison() {
        this.addMessage(
            "⚖️ **Product Comparison**\n\n" +
            "Compare products side-by-side:\n\n" +
            "🔗 [Open Comparison Tool](product_comparison.php)\n\n" +
            "You can compare:\n" +
            "• Prices and features\n" +
            "• Quality ratings\n" +
            "• Customer reviews\n" +
            "• Nutritional information",
            'bot'
        );
    }

    handleLoyaltyProgram() {
        this.addMessage(
            "🏆 **Loyalty Program**\n\n" +
            "Check your points and benefits:\n\n" +
            "🔗 [View Loyalty Dashboard](loyalty_program.php)\n\n" +
            "**Current Tiers:**\n" +
            "🥉 Bronze: 1x points\n" +
            "🥈 Silver: 1.2x points + Free shipping\n" +
            "🥇 Gold: 1.5x points + Priority support\n" +
            "💎 Platinum: 2x points + Exclusive deals",
            'bot'
        );
    }

    handleRecommendations() {
        this.addMessage(
            "🎯 **Personal Recommendations**\n\n" +
            "I'll find products perfect for you:\n\n" +
            "• Based on your purchase history\n" +
            "• Seasonal suggestions\n" +
            "• Trending products\n" +
            "• Similar customer preferences\n\n" +
            "Tell me what you're looking for or browse by category!",
            'bot'
        );
    }

    handleQuickReorder() {
        this.addMessage(
            "⚡ **Quick Reorder**\n\n" +
            "Reorder your favorites quickly:\n\n" +
            "🔗 [Quick Reorder Page](quick_reorder.php)\n\n" +
            "Features:\n" +
            "• Frequently bought items\n" +
            "• Last order replication\n" +
            "• Smart suggestions\n" +
            "• Bulk operations",
            'bot'
        );
    }

    handleNotifications() {
        this.addMessage(
            "🔔 **Notifications**\n\n" +
            "Manage your alerts:\n\n" +
            "🔗 [Notification Settings](notifications.php)\n\n" +
            "Available alerts:\n" +
            "• Stock availability\n" +
            "• Price drops\n" +
            "• New products\n" +
            "• Order updates\n" +
            "• Delivery status",
            'bot'
        );
    }

    async handleOrderTracking() {
        this.addMessage("📦 **Order Tracking**\n\nPlease provide your order ID (e.g., AGR12345) to track your order:", 'bot');
        
        // Wait for user input
        this.waitingForOrderId = true;
    }

    handleBulkPricing() {
        this.addMessage(
            "💰 **Bulk Pricing**\n\n" +
            "Get discounts on large orders:\n\n" +
            "📊 **Discount Tiers:**\n" +
            "• 20+ items: 5% off\n" +
            "• 50+ items: 10% off\n" +
            "• 100+ items: 15% off\n\n" +
            "Tell me the product and quantity for a quote!",
            'bot'
        );
    }

    handleRecentlyViewed() {
        this.addMessage(
            "👁️ **Recently Viewed Products**\n\n" +
            "Quick access to products you've seen:\n\n" +
            "• Last 10 viewed items\n" +
            "• Easy re-navigation\n" +
            "• Price change notifications\n" +
            "• Add to cart from history\n\n" +
            "Check your profile dashboard for the full list!",
            'bot'
        );
    }

    handleSmartSearch() {
        this.addMessage(
            "🔍 **Smart Search**\n\n" +
            "I can help you find products intelligently:\n\n" +
            "• Natural language search\n" +
            "• Image-based search\n" +
            "• Filter by price, category, rating\n" +
            "• Voice search (if enabled)\n\n" +
            "What would you like to search for?",
            'bot'
        );
        
        this.waitingForSearch = true;
    }

    handleOrderCancellation() {
        this.addMessage(
            "❌ **Order Cancellation**\n\n" +
            "I can help cancel your order if it's still pending.\n\n" +
            "Please provide your order ID to proceed with cancellation:",
            'bot'
        );
        
        this.waitingForCancelOrderId = true;
    }

    handleOrderHistory() {
        this.addMessage(
            "📊 **Order History**\n\n" +
            "View all your past orders:\n\n" +
            "🔗 [My Orders](my_orders.php)\n\n" +
            "Features:\n" +
            "• Complete purchase history\n" +
            "• Order status tracking\n" +
            "• Reorder options\n" +
            "• Download invoices\n" +
            "• Return requests",
            'bot'
        );
    }

    handleLiveChat() {
        this.addMessage(
            "💬 **Live Chat Support**\n\n" +
            "Connect with our customer service team:\n\n" +
            "📞 **Contact Options:**\n" +
            "• Phone: +880 1776-199963\n" +
            "• Email: support@agrokart-bd.com\n" +
            "• WhatsApp: +880 1776-199963\n\n" +
            "**Live Support Hours:**\n" +
            "🕘 9:00 AM - 9:00 PM (Daily)",
            'bot'
        );
    }

    toggleVoiceMode() {
        this.voiceEnabled = !this.voiceEnabled;
        this.saveUserPreferences();
        
        const message = this.voiceEnabled 
            ? "🎤 **Voice Mode Enabled!**\n\nYou can now use voice commands. Click the microphone button to start speaking."
            : "🔇 **Voice Mode Disabled**\n\nVoice commands are now turned off.";
            
        this.addMessage(message, 'bot');
    }

    toggleLanguage() {
        this.currentLanguage = this.currentLanguage === 'en' ? 'bn' : 'en';
        this.saveUserPreferences();
        
        const message = this.currentLanguage === 'en'
            ? "🌐 **Language switched to English**\n\nI'll now respond in English."
            : "🌐 **ভাষা বাংলায় পরিবর্তিত**\n\nআমি এখন বাংলায় উত্তর দেব।";
            
        this.addMessage(message, 'bot');
        
        // Update placeholders and UI text
        this.updateLanguageUI();
    }

    updateLanguageUI() {
        const input = document.getElementById('chatbotInput');
        const statusText = document.querySelector('.status-text');
        
        if (input) {
            input.placeholder = this.currentLanguage === 'en' 
                ? 'Type your message or click a feature button...'
                : 'আপনার বার্তা টাইপ করুন বা ফিচার বোতাম ক্লিক করুন...';
        }
        
        if (statusText) {
            statusText.textContent = this.currentLanguage === 'en'
                ? 'Online • Ready to help with all features!'
                : 'অনলাইন • সব বৈশিষ্ট্যে সাহায্যের জন্য প্রস্তুত!';
        }
        
        // Update feature grid
        const featureGrid = document.getElementById('featureGrid');
        const secondaryGrid = document.getElementById('secondaryGrid');
        
        if (featureGrid) featureGrid.innerHTML = this.generateFeatureGrid();
        if (secondaryGrid) secondaryGrid.innerHTML = this.generateSecondaryGrid();
    }

    async sendMessage() {
        const input = document.getElementById('chatbotInput');
        const message = input.value.trim();
        
        if (!message) return;

        this.addMessage(message, 'user');
        input.value = '';

        // Handle special waiting states
        if (this.waitingForOrderId) {
            await this.processOrderTracking(message);
            this.waitingForOrderId = false;
            return;
        }
        
        if (this.waitingForCancelOrderId) {
            await this.processOrderCancellation(message);
            this.waitingForCancelOrderId = false;
            return;
        }
        
        if (this.waitingForSearch) {
            await this.processSmartSearch(message);
            this.waitingForSearch = false;
            return;
        }

        this.showTyping();
        
        await new Promise(resolve => setTimeout(resolve, 1000 + Math.random() * 1000));
        
        const response = await this.generateResponse(message);
        this.hideTyping();
        this.addMessage(response, 'bot');
    }

    async generateResponse(message) {
        // First, try the smart chat API for intelligent product detection
        try {
            const smartResponse = await this.callSmartChatAPI(message);
            if (smartResponse && smartResponse.success) {
                return this.formatSmartChatResponse(smartResponse.data);
            }
        } catch (error) {
            console.log('Smart chat API not available, falling back to basic responses');
        }
        
        const lowerMessage = message.toLowerCase();
        
        // Greeting detection
        if (this.isGreeting(lowerMessage)) {
            return this.getRandomResponse('greetings') + this.getFeaturePrompt();
        }
        
        // Help detection
        if (this.isAskingForHelp(lowerMessage)) {
            return this.getRandomResponse('help');
        }
        
        // Default intelligent response with feature suggestions
        return this.getIntelligentResponse(message);
    }

    async callSmartChatAPI(message) {
        try {
            const response = await fetch('php/chatbot_api.php?action=smart_chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `message=${encodeURIComponent(message)}`
            });
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            return await response.json();
        } catch (error) {
            console.error('Smart chat API error:', error);
            return null;
        }
    }

    formatSmartChatResponse(data) {
        let response = data.response || "I'm here to help!";
        
        if (data.detected_products && data.detected_products.length > 0) {
            response += "\n\n" + this.formatProductCards(data.detected_products);
        }
        
        if (data.message_type) {
            response += this.getContextualActions(data.message_type);
        }
        
        return response;
    }

    formatProductCards(products) {
        let cards = "🛍️ **Available Products:**\n\n";
        
        products.forEach(product => {
            cards += `🌾 **${product.name}**\n`;
            cards += `💰 **Price:** ${product.price_formatted} per ${product.unit}\n`;
            cards += `📦 **Stock:** ${product.availability_text}\n`;
            
            if (product.can_order) {
                cards += `🛒 **[Order Now](${product.order_link})** | `;
                cards += `💖 **[Add to Wishlist](wishlist.php?add=${product.id})**\n`;
                
                if (product.stock_status === 'low_stock') {
                    cards += `⚠️ **${product.stock_message}**\n`;
                }
            } else {
                cards += `🔔 **[Get Notified](notifications.php?product=${product.id})** when back in stock\n`;
            }
            
            cards += "\n";
        });
        
        return cards;
    }

    getContextualActions(messageType) {
        switch (messageType) {
            case 'question_stock':
                return "\n\n💡 **Quick Actions:**\n🔍 Search similar products\n🔔 Set stock alerts\n📊 View product comparison";
            case 'question_price':
                return "\n\n💡 **Quick Actions:**\n🏷️ Check bulk discounts\n📈 View price history\n⚖️ Compare prices";
            case 'question_delivery':
                return "\n\n💡 **Quick Actions:**\n📦 Track existing order\n🚚 Check delivery areas\n⏰ Schedule delivery";
            default:
                return "\n\n💡 **Quick Actions:**\n🛒 Browse products\n🔍 Search catalog\n📞 Contact support";
        }
    }

    async processOrderTracking(orderId) {
        this.showTyping();
        
        try {
            const response = await fetch('php/chatbot_api.php?action=track_order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `order_id=${encodeURIComponent(orderId)}`
            });
            
            const data = await response.json();
            this.hideTyping();
            
            if (data.success) {
                const order = data.data;
                this.addMessage(
                    `📦 **Order Tracking - ${order.order_id}**\n\n` +
                    `**Status:** ${order.status}\n` +
                    `**Amount:** ৳${order.total_amount}\n` +
                    `**Order Date:** ${order.order_date}\n` +
                    `**Delivery Address:** ${order.shipping_address}\n` +
                    `**Estimated Delivery:** ${order.estimated_delivery}\n\n` +
                    `Your order is being processed! 🚚`,
                    'bot'
                );
            } else {
                this.addMessage(
                    `❌ **Order Not Found**\n\n` +
                    `I couldn't find order "${orderId}". Please check:\n` +
                    `• Order ID spelling\n` +
                    `• Include "AGR" prefix if missing\n` +
                    `• Contact support if issue persists`,
                    'bot'
                );
            }
        } catch (error) {
            this.hideTyping();
            this.addMessage("Sorry, I couldn't track your order right now. Please try again later.", 'bot');
        }
    }

    async processOrderCancellation(orderId) {
        this.showTyping();
        
        try {
            const response = await fetch('php/chatbot_api.php?action=cancel_order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `order_id=${encodeURIComponent(orderId)}`
            });
            
            const data = await response.json();
            this.hideTyping();
            
            if (data.success) {
                this.addMessage(
                    `✅ **Order Cancelled Successfully**\n\n` +
                    `**Order ID:** ${data.data.order_id}\n` +
                    `**Refund Status:** ${data.data.refund_status}\n\n` +
                    `We're sorry to see you cancel your order. Is there anything we can help you with instead?`,
                    'bot'
                );
            } else {
                this.addMessage(
                    `❌ **Cannot Cancel Order**\n\n` +
                    `${data.error}\n\n` +
                    `Please contact our support team for assistance:\n` +
                    `📞 +880 1776-199963`,
                    'bot'
                );
            }
        } catch (error) {
            this.hideTyping();
            this.addMessage("Sorry, I couldn't process the cancellation right now. Please contact support.", 'bot');
        }
    }

    async processSmartSearch(query) {
        this.showTyping();
        
        try {
            const response = await fetch('php/chatbot_api.php?action=search_products', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `query=${encodeURIComponent(query)}`
            });
            
            const data = await response.json();
            this.hideTyping();
            
            if (data.success && data.data.products.length > 0) {
                this.addMessage(
                    `🔍 **Search Results for "${query}"**\n\n` +
                    `Found ${data.data.total_found} products:\n\n` +
                    this.formatProductCards(data.data.products),
                    'bot'
                );
            } else {
                this.addMessage(
                    `🔍 **No Products Found**\n\n` +
                    `I couldn't find any products matching "${query}".\n\n` +
                    `Try searching for:\n` +
                    `• Different keywords\n` +
                    `• Product categories\n` +
                    `• Brand names\n\n` +
                    `Or browse our [full catalog](products.php)!`,
                    'bot'
                );
            }
        } catch (error) {
            this.hideTyping();
            this.addMessage("Sorry, I couldn't search right now. Please try again later.", 'bot');
        }
    }

    getFeaturePrompt() {
        return this.currentLanguage === 'en'
            ? "\n\n🌟 Click the **Feature Menu** button (⚏) in the header to access all customer features, or tell me what you need help with!"
            : "\n\n🌟 সব গ্রাহক বৈশিষ্ট্য অ্যাক্সেস করতে হেডারে **ফিচার মেনু** বোতাম (⚏) ক্লিক করুন, বা আমাকে বলুন আপনার কী সাহায্য দরকার!";
    }

    getRandomResponse(category) {
        const responses = this.responses[category][this.currentLanguage];
        return responses[Math.floor(Math.random() * responses.length)];
    }

    getIntelligentResponse(message) {
        return this.currentLanguage === 'en'
            ? `I understand you're asking about "${message}". I can help you with all our customer features!\n\n🌟 Use the **Feature Menu** (⚏) to access:\n• Product browsing & search\n• Wishlist management\n• Product comparison\n• Loyalty rewards\n• Quick reorder\n• Order tracking\n• And much more!\n\nWhat would you like to explore?`
            : `আমি বুঝতে পারছি আপনি "${message}" সম্পর্কে জিজ্ঞাসা করছেন। আমি আমাদের সব গ্রাহক বৈশিষ্ট্যে আপনাকে সাহায্য করতে পারি!\n\n🌟 **ফিচার মেনু** (⚏) ব্যবহার করুন:\n• পণ্য ব্রাউজিং ও অনুসন্ধান\n• উইশলিস্ট ব্যবস্থাপনা\n• পণ্য তুলনা\n• লয়ালটি পুরস্কার\n• দ্রুত পুনর্অর্ডার\n• অর্ডার ট্র্যাকিং\n• আরও অনেক কিছু!\n\nআপনি কী অন্বেষণ করতে চান?`;
    }

    isGreeting(message) {
        const greetings = ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 'greetings', 'salam'];
        return greetings.some(greeting => message.includes(greeting));
    }

    isAskingForHelp(message) {
        const helpKeywords = ['help', 'assist', 'support', 'what can you do', 'how can you help', 'menu', 'features'];
        return helpKeywords.some(keyword => message.includes(keyword));
    }

    addMessage(text, sender) {
        const messagesContainer = document.getElementById('chatbotMessages');
        const isBot = sender === 'bot';
        
        const messageHTML = `
            <div class="message ${sender}">
                ${isBot ? '<div class="bot-avatar"><img src="images/AGrO.png" alt="Bot"></div>' : ''}
                <div class="message-bubble">${text}</div>
            </div>
        `;
        
        messagesContainer.insertAdjacentHTML('beforeend', messageHTML);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        this.conversationHistory.push({ sender, message: text, timestamp: new Date() });
    }

    showTyping() {
        const indicator = document.getElementById('typingIndicator');
        indicator.style.display = 'flex';
        this.isTyping = true;
        
        const messagesContainer = document.getElementById('chatbotMessages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    hideTyping() {
        const indicator = document.getElementById('typingIndicator');
        indicator.style.display = 'none';
        this.isTyping = false;
    }

    showWelcomeMessage() {
        setTimeout(() => {
            this.addMessage(
                this.getRandomResponse('greetings') + this.getFeaturePrompt(),
                'bot'
            );
        }, 1000);
    }

    hideNotification() {
        const notification = document.getElementById('chatbotNotification');
        if (notification) {
            notification.style.display = 'none';
        }
    }

    toggleVoiceInput() {
        if (!this.recognition) {
            this.addMessage("Voice input is not supported in your browser.", 'bot');
            return;
        }

        if (this.voiceEnabled) {
            this.recognition.start();
        } else {
            this.addMessage("Please enable voice mode first by clicking the Voice Mode button in the feature menu.", 'bot');
        }
    }

    handleImageUpload() {
        const fileInput = document.getElementById('fileInput');
        fileInput.click();
        
        fileInput.onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
                this.addMessage("📷 Image uploaded! I'll analyze it for product identification...", 'user');
                this.addMessage(
                    "🔍 **Image Analysis**\n\n" +
                    "I can see your uploaded image! While I'm working on full image recognition, " +
                    "you can describe what you're looking for and I'll help you find similar products.\n\n" +
                    "What type of product are you looking for?",
                    'bot'
                );
            }
        };
    }

    loadProducts() {
        // Load products for search functionality
        this.products = []; // This would be populated from your database
    }
}

// Note: Chatbot is now initialized from header.php to avoid conflicts
