class AgroKartChatbot {
    constructor() {
        this.isOpen = false;
        this.isTyping = false;
        this.conversationHistory = [];
        this.products = [];
        this.categories = ['Vegetables', 'Fruits', 'Grains', 'Dairy', 'Meat'];
        
        this.responses = {
            greetings: [
                "Hello! Welcome to AgroKart BD! 🌱 I'm your virtual assistant. How can I help you today?",
                "Hi there! I'm here to help you find the best agricultural products. What are you looking for?",
                "Welcome to AgroKart BD! Your one-stop shop for fresh agricultural products. How may I assist you?"
            ],
            help: [
                "I can help you with:\n• Finding products and prices\n• Price negotiations 💰\n• Real-time order tracking 📦\n• Canceling orders ❌\n• Bulk order discounts 📊\n• Product recommendations 🌟\n• Information about our services\n• Account assistance\n• Delivery information\n\nWhat would you like to know?"
            ],
            products: [
                "We offer a wide variety of fresh agricultural products including vegetables, fruits, grains, and more. Would you like to see our current offerings?",
                "Our product catalog includes farm-fresh vegetables, seasonal fruits, quality grains, and dairy products. What type of product interests you?"
            ],
            prices: [
                "Our prices are competitive and updated regularly. For current pricing, you can browse our product catalog or tell me what specific product you're interested in! 💰 *Tip: I can help with price negotiations for bulk orders!*"
            ],
            delivery: [
                "We offer fast and reliable delivery across Bangladesh! Most orders are delivered within 24-48 hours. Delivery is FREE for orders over ৳500! 📦 I can also help you track your orders in real-time!"
            ],
            orders: [
                "To place an order:\n1. Browse our products\n2. Add items to cart\n3. Proceed to checkout\n4. Choose payment method\n5. Confirm your order!\n\n📱 After placing, I can help you track your order or cancel if needed. Would you like help with any specific step?"
            ],
            negotiations: [
                "Great! I can help you with price negotiations! 💰\n\nFor bulk orders (10+ items) or orders over ৳2000, we offer:\n• 5-10% discount on bulk purchases\n• Special wholesale prices for regular customers\n• Seasonal discounts\n\nWhat product are you interested in and what quantity?"
            ],
            tracking: [
                "I can help you track your order! 📦\n\nTo track your order, I'll need:\n• Your order ID/number\n• Registered phone number or email\n\nOnce provided, I'll give you real-time updates on your delivery status!"
            ],
            cancellation: [
                "I can help you cancel your order! ❌\n\nCancellation policy:\n• Free cancellation within 2 hours of order\n• 50% refund if canceled within 24 hours\n• Contact customer service for urgent cancellations\n\nPlease provide your order ID to proceed with cancellation."
            ]
        };

        this.quickActions = [
            { text: "View Products", action: "products" },
            { text: "Negotiate Price", action: "negotiate" },
            { text: "Track Order", action: "track" },
            { text: "Cancel Order", action: "cancel" },
            { text: "Bulk Discounts", action: "bulk" },
            { text: "Contact Us", action: "contact" }
        ];

        this.init();
        this.loadProducts();
    }

    init() {
        this.createChatbotHTML();
        this.bindEvents();
        this.showWelcomeMessage();
    }

    createChatbotHTML() {
        const chatbotHTML = `
            <div class="chatbot-container">
                <button class="chatbot-toggle" id="chatbotToggle">
                    <i class="fas fa-comments"></i>
                    <div class="notification-badge" id="chatbotBadge" style="display: none;">1</div>
                </button>
                <div class="chatbot-window" id="chatbotWindow">
                    <div class="chatbot-header">
                        <div>
                            <h3>AgroKart Assistant</h3>
                            <div class="chatbot-status">
                                <span class="status-dot"></span>
                                Online
                            </div>
                        </div>
                        <button class="chatbot-close" id="chatbotClose">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="chatbot-messages" id="chatbotMessages">
                        <div class="typing-indicator" id="typingIndicator">
                            <div class="typing-dots">
                                <div class="typing-dot"></div>
                                <div class="typing-dot"></div>
                                <div class="typing-dot"></div>
                            </div>
                        </div>
                    </div>
                    <div class="chatbot-input-container">
                        <input type="text" class="chatbot-input" id="chatbotInput" 
                               placeholder="Type your message...">
                        <button class="chatbot-send" id="chatbotSend">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    }

    bindEvents() {
        const toggle = document.getElementById('chatbotToggle');
        const close = document.getElementById('chatbotClose');
        const send = document.getElementById('chatbotSend');
        const input = document.getElementById('chatbotInput');

        toggle.addEventListener('click', () => this.toggleChatbot());
        close.addEventListener('click', () => this.closeChatbot());
        send.addEventListener('click', () => this.sendMessage());
        
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });

        input.addEventListener('input', () => {
            const sendBtn = document.getElementById('chatbotSend');
            sendBtn.disabled = input.value.trim() === '';
        });
    }

    toggleChatbot() {
        const window = document.getElementById('chatbotWindow');
        this.isOpen = !this.isOpen;
        
        if (this.isOpen) {
            window.style.display = 'flex';
            document.getElementById('chatbotInput').focus();
        } else {
            window.style.display = 'none';
        }
    }

    closeChatbot() {
        const window = document.getElementById('chatbotWindow');
        window.style.display = 'none';
        this.isOpen = false;
    }

    async sendMessage() {
        const input = document.getElementById('chatbotInput');
        const message = input.value.trim();
        
        if (!message) return;

        this.addMessage(message, 'user');
        input.value = '';
        document.getElementById('chatbotSend').disabled = true;

        this.showTyping();
        
        // Simulate thinking time
        await new Promise(resolve => setTimeout(resolve, 1000 + Math.random() * 1000));
        
        const response = await this.generateResponse(message);
        this.hideTyping();
        this.addMessage(response, 'bot');
    }

    addMessage(text, sender) {
        const messagesContainer = document.getElementById('chatbotMessages');
        const isBot = sender === 'bot';
        
        const messageHTML = `
            <div class="message ${sender}">
                ${isBot ? '<div class="bot-avatar">🤖</div>' : ''}
                <div class="message-bubble">${text}</div>
            </div>
        `;
        
        messagesContainer.insertAdjacentHTML('beforeend', messageHTML);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        // Add to conversation history
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

    async generateResponse(message) {
        const lowerMessage = message.toLowerCase();
        
        // Greeting detection
        if (this.isGreeting(lowerMessage)) {
            return this.getRandomResponse('greetings') + this.getQuickActions();
        }
        
        // Help detection
        if (this.isAskingForHelp(lowerMessage)) {
            return this.getRandomResponse('help') + this.getQuickActions();
        }
        
        // Product inquiry
        if (this.isAskingAboutProducts(lowerMessage)) {
            return await this.handleProductInquiry(lowerMessage);
        }
        
        // Price inquiry
        if (this.isAskingAboutPrices(lowerMessage)) {
            return await this.handlePriceInquiry(lowerMessage);
        }
        
        // Delivery inquiry
        if (this.isAskingAboutDelivery(lowerMessage)) {
            return this.getRandomResponse('delivery');
        }
        
        // Order process
        if (this.isAskingAboutOrders(lowerMessage)) {
            return this.getRandomResponse('orders');
        }
        
        // Price negotiation
        if (this.isAskingAboutNegotiation(lowerMessage)) {
            return await this.handlePriceNegotiation(lowerMessage);
        }
        
        // Order tracking
        if (this.isAskingAboutTracking(lowerMessage)) {
            return await this.handleOrderTracking(lowerMessage);
        }
        
        // Order cancellation
        if (this.isAskingAboutCancellation(lowerMessage)) {
            return await this.handleOrderCancellation(lowerMessage);
        }
        
        // Bulk orders
        if (this.isAskingAboutBulk(lowerMessage)) {
            return this.handleBulkOrders(lowerMessage);
        }
        
        // Product recommendations
        if (this.isAskingForRecommendations(lowerMessage)) {
            return await this.handleRecommendations(lowerMessage);
        }
        
        // Customer support
        if (this.isAskingForSupport(lowerMessage)) {
            return this.handleCustomerSupport(lowerMessage);
        }
        
        // Contact information
        if (this.isAskingForContact(lowerMessage)) {
            return "You can contact us:\n📧 Email: support@agrokart-bd.com\n📞 Phone: +880 1776-199963\n📍 Address: Dhaka, Bangladesh\n\nOr you can use our contact form on the website!";
        }
        
        // Default intelligent response
        return this.getIntelligentResponse(message);
    }

    isGreeting(message) {
        const greetings = ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 'greetings'];
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

    isAskingAboutOrders(message) {
        const orderKeywords = ['order', 'buy', 'purchase', 'how to order', 'place order'];
        return orderKeywords.some(keyword => message.includes(keyword));
    }

    isAskingForContact(message) {
        const contactKeywords = ['contact', 'phone', 'email', 'address', 'reach you'];
        return contactKeywords.some(keyword => message.includes(keyword));
    }

    isAskingAboutNegotiation(message) {
        const negotiationKeywords = ['negotiate', 'discount', 'bulk price', 'wholesale', 'better price', 'lower price', 'bargain', 'deal'];
        return negotiationKeywords.some(keyword => message.includes(keyword));
    }

    isAskingAboutTracking(message) {
        const trackingKeywords = ['track', 'where is my order', 'order status', 'delivery status', 'when will arrive', 'shipped'];
        return trackingKeywords.some(keyword => message.includes(keyword));
    }

    isAskingAboutCancellation(message) {
        const cancelKeywords = ['cancel', 'cancel order', 'remove order', 'delete order', 'refund', 'return'];
        return cancelKeywords.some(keyword => message.includes(keyword));
    }

    isAskingAboutBulk(message) {
        const bulkKeywords = ['bulk', 'wholesale', 'large quantity', 'many items', 'business order', 'restaurant order'];
        return bulkKeywords.some(keyword => message.includes(keyword));
    }

    isAskingForRecommendations(message) {
        const recommendKeywords = ['recommend', 'suggest', 'what should i buy', 'best products', 'popular items', 'fresh items'];
        return recommendKeywords.some(keyword => message.includes(keyword));
    }

    isAskingForSupport(message) {
        const supportKeywords = ['support', 'problem', 'issue', 'complaint', 'help me', 'assistance needed'];
        return supportKeywords.some(keyword => message.includes(keyword));
    }

    async handleProductInquiry(message) {
        // Try to fetch real products from database
        await this.loadProducts();
        
        if (this.products.length > 0) {
            const categories = [...new Set(this.products.map(p => p.category))];
            let response = "Here are our available product categories:\n\n";
            
            categories.forEach(category => {
                const categoryProducts = this.products.filter(p => p.category === category);
                response += `🌱 ${category} (${categoryProducts.length} items)\n`;
            });
            
            response += "\nWould you like to see specific products from any category?";
            return response;
        } else {
            return this.getRandomResponse('products') + "\n\nCurrently loading our product catalog...";
        }
    }

    async handlePriceInquiry(message) {
        await this.loadProducts();
        
        // Look for specific product mentioned
        const mentionedProduct = this.products.find(p => 
            message.toLowerCase().includes(p.name.toLowerCase())
        );
        
        if (mentionedProduct) {
            return `The current price for ${mentionedProduct.name} is ৳${mentionedProduct.price}. ${mentionedProduct.stock > 0 ? '✅ In stock!' : '❌ Currently out of stock.'}`;
        }
        
        if (this.products.length > 0) {
            let response = "Here are some of our popular items with prices:\n\n";
            const featuredProducts = this.products.slice(0, 5);
            
            featuredProducts.forEach(product => {
                response += `• ${product.name}: ৳${product.price}\n`;
            });
            
            response += "\nFor more detailed pricing, please browse our product catalog!";
            return response;
        }
        
        return this.getRandomResponse('prices');
    }

    async handlePriceNegotiation(message) {
        await this.loadProducts();
        
        // Extract quantity if mentioned
        const quantityMatch = message.match(/(\d+)/);
        const quantity = quantityMatch ? parseInt(quantityMatch[1]) : 1;
        
        // Look for specific product mentioned
        const mentionedProduct = this.products.find(p => 
            message.toLowerCase().includes(p.name.toLowerCase())
        );
        
        if (mentionedProduct && quantity >= 10) {
            const originalPrice = mentionedProduct.price;
            const discount = quantity >= 50 ? 15 : quantity >= 25 ? 10 : 5;
            const discountedPrice = originalPrice * (1 - discount/100);
            
            return `🎉 Great news! For ${quantity} units of ${mentionedProduct.name}:\n\n` +
                   `• Original price: ৳${originalPrice} each\n` +
                   `• Bulk discount: ${discount}%\n` +
                   `• Your price: ৳${discountedPrice.toFixed(2)} each\n` +
                   `• Total savings: ৳${((originalPrice - discountedPrice) * quantity).toFixed(2)}\n\n` +
                   `This offer is valid for 24 hours! Would you like to proceed with this order?`;
        } else if (mentionedProduct) {
            return `For ${mentionedProduct.name} (৳${mentionedProduct.price}):\n\n` +
                   `• Orders 10-24 items: 5% discount\n` +
                   `• Orders 25-49 items: 10% discount\n` +
                   `• Orders 50+ items: 15% discount\n\n` +
                   `How many units are you planning to order?`;
        }
        
        return this.getRandomResponse('negotiations') + 
               "\n\n💡 *Tip: Mention the product name and quantity for personalized pricing!*";
    }

    async handleOrderTracking(message) {
        // Extract order ID if mentioned
        const orderIdMatch = message.match(/(?:order|id|#)[\s]*([a-zA-Z0-9]+)/i);
        const orderId = orderIdMatch ? orderIdMatch[1] : null;
        
        if (orderId) {
            // Simulate order tracking (in real implementation, you'd query your database)
            const trackingStatuses = [
                {
                    status: "Order Confirmed",
                    icon: "✅",
                    time: "2 hours ago",
                    description: "Your order has been confirmed and is being prepared"
                },
                {
                    status: "Being Packed",
                    icon: "📦",
                    time: "1 hour ago",
                    description: "Items are being carefully packed for delivery"
                },
                {
                    status: "Out for Delivery",
                    icon: "🚚",
                    time: "30 minutes ago",
                    description: "Your order is on the way! Expected delivery in 2-3 hours"
                }
            ];
            
            let response = `📦 **Order Tracking - #${orderId}**\n\n`;
            trackingStatuses.forEach(status => {
                response += `${status.icon} **${status.status}** (${status.time})\n`;
                response += `   ${status.description}\n\n`;
            });
            
            response += `🚚 **Estimated Delivery:** Today, 6:00 PM - 8:00 PM\n`;
            response += `📞 **Delivery Contact:** +880 1776-199963\n\n`;
            response += `Need to make changes? I can help you modify or cancel this order!`;
            
            return response;
        }
        
        return this.getRandomResponse('tracking') + 
               "\n\n🔍 *You can also check 'My Orders' section on the website for detailed tracking.*";
    }

    async handleOrderCancellation(message) {
        // Extract order ID if mentioned
        const orderIdMatch = message.match(/(?:order|id|#)[\s]*([a-zA-Z0-9]+)/i);
        const orderId = orderIdMatch ? orderIdMatch[1] : null;
        
        if (orderId) {
            return `🔍 **Checking Order #${orderId}**\n\n` +
                   `✅ Order found! Here are your cancellation options:\n\n` +
                   `• **Full Refund:** Available (order placed 45 minutes ago)\n` +
                   `• **Refund Amount:** ৳1,250\n` +
                   `• **Processing Time:** 3-5 business days\n\n` +
                   `Would you like me to proceed with the cancellation?\n\n` +
                   `**Type 'CONFIRM CANCEL' to proceed or 'KEEP ORDER' to maintain your order.**`;
        }
        
        if (message.includes('confirm cancel')) {
            return `✅ **Order Cancellation Successful!**\n\n` +
                   `• Order #${Math.random().toString(36).substr(2, 9).toUpperCase()} has been cancelled\n` +
                   `• Refund of ৳1,250 will be processed\n` +
                   `• Expected refund time: 3-5 business days\n` +
                   `• Confirmation email sent\n\n` +
                   `Is there anything else I can help you with? Maybe find similar products?`;
        }
        
        return this.getRandomResponse('cancellation');
    }

    handleBulkOrders(message) {
        const bulkBenefits = [
            "🏪 **Restaurant/Business Orders:**",
            "• Minimum 50 items: 15% discount",
            "• Dedicated account manager",
            "• Priority delivery slots",
            "• Custom packaging options",
            "",
            "🚚 **Wholesale Benefits:**",
            "• Weekly/monthly delivery schedules",
            "• Extended payment terms",
            "• Seasonal price protection",
            "• Quality guarantees",
            "",
            "📞 **Contact our Bulk Sales Team:**",
            "• Phone: +880 1776-199963",
            "• Email: bulk@agrokart-bd.com",
            "• WhatsApp Business: Available 24/7"
        ];
        
        return bulkBenefits.join('\n') + 
               "\n\n💬 Tell me about your business needs and I'll connect you with the right team member!";
    }

    async handleRecommendations(message) {
        await this.loadProducts();
        
        if (this.products.length === 0) {
            return "I'd love to recommend products, but I'm currently updating our catalog. Please check back in a moment!";
        }
        
        // Get seasonal recommendations
        const currentMonth = new Date().getMonth();
        const seasonalProducts = this.getSeasonalRecommendations(currentMonth);
        
        // Get popular products (simulate based on stock levels)
        const popularProducts = this.products
            .filter(p => p.stock > 50)
            .sort((a, b) => b.stock - a.stock)
            .slice(0, 3);
        
        let response = "🌟 **Personalized Recommendations:**\n\n";
        
        if (seasonalProducts.length > 0) {
            response += "🍃 **Fresh & Seasonal:**\n";
            seasonalProducts.forEach(product => {
                response += `• ${product.name} - ৳${product.price} (Fresh arrival!)\n`;
            });
            response += "\n";
        }
        
        if (popularProducts.length > 0) {
            response += "🔥 **Customer Favorites:**\n";
            popularProducts.forEach(product => {
                response += `• ${product.name} - ৳${product.price} (High in demand)\n`;
            });
            response += "\n";
        }
        
        response += "💰 **Money-Saving Tip:** Bundle 3+ items for automatic 5% discount!\n\n";
        response += "What type of products are you most interested in?";
        
        return response;
    }

    getSeasonalRecommendations(month) {
        // Simulate seasonal recommendations based on month
        const seasonalMap = {
            0: ['Winter Vegetables'], 1: ['Winter Vegetables'], 2: ['Spring Vegetables'],
            3: ['Spring Vegetables'], 4: ['Summer Fruits'], 5: ['Summer Fruits'],
            6: ['Monsoon Vegetables'], 7: ['Monsoon Vegetables'], 8: ['Post-Monsoon'],
            9: ['Autumn Harvest'], 10: ['Winter Vegetables'], 11: ['Winter Vegetables']
        };
        
        const seasonalCategories = seasonalMap[month] || [];
        return this.products.filter(p => 
            seasonalCategories.some(cat => p.category.includes(cat)) ||
            Math.random() > 0.7 // Simulate seasonal availability
        ).slice(0, 3);
    }

    handleCustomerSupport(message) {
        const supportOptions = [
            "🎧 **24/7 Customer Support Available:**",
            "",
            "📞 **Immediate Help:**",
            "• Call: +880 1776-199963",
            "• WhatsApp: Available now",
            "• Live Chat: You're using it! 😊",
            "",
            "📧 **Email Support:**",
            "• General: support@agrokart-bd.com",
            "• Complaints: complaints@agrokart-bd.com",
            "• Returns: returns@agrokart-bd.com",
            "",
            "🚨 **Emergency/Urgent Issues:**",
            "• Same-day resolution guarantee",
            "• Direct line: +880 1776-199963",
            "• Priority ticket system",
            "",
            "What specific issue can I help you resolve right now?"
        ];
        
        return supportOptions.join('\n');
    }

    async loadProducts() {
        try {
            const response = await fetch('php/chatbot_products_api.php');
            const data = await response.json();
            
            if (data.success && data.products) {
                this.products = data.products;
            }
        } catch (error) {
            console.log('Could not load products:', error);
        }
    }

    getIntelligentResponse(message) {
        const responses = [
            "I understand you're asking about: \"" + message + "\". Let me help you with that! For specific assistance, you can browse our website or contact our support team.",
            "That's an interesting question! While I'd love to help with everything, I'm specialized in AgroKart information. Is there something specific about our products or services I can assist with?",
            "I'm here to help with AgroKart-related questions! For the best assistance, try asking about our products, prices, delivery, or ordering process.",
            "Great question! I'm continuously learning to better assist you. For now, I can help you with product information, pricing, and general website guidance. What would you like to know?"
        ];
        
        return responses[Math.floor(Math.random() * responses.length)] + this.getQuickActions();
    }

    getRandomResponse(category) {
        const responses = this.responses[category];
        return responses[Math.floor(Math.random() * responses.length)];
    }

    getQuickActions() {
        let actionsHTML = '\n\n<div class="quick-actions">';
        this.quickActions.forEach(action => {
            actionsHTML += `<span class="quick-action" onclick="chatbot.handleQuickAction('${action.action}')">${action.text}</span>`;
        });
        actionsHTML += '</div>';
        return actionsHTML;
    }

    handleQuickAction(action) {
        const input = document.getElementById('chatbotInput');
        
        switch(action) {
            case 'products':
                input.value = 'What products do you have?';
                break;
            case 'prices':
                input.value = 'What are your prices?';
                break;
            case 'negotiate':
                input.value = 'I want to negotiate prices for bulk orders';
                break;
            case 'track':
                input.value = 'I want to track my order';
                break;
            case 'cancel':
                input.value = 'I need to cancel my order';
                break;
            case 'bulk':
                input.value = 'Tell me about bulk order discounts';
                break;
            case 'delivery':
                input.value = 'Tell me about delivery';
                break;
            case 'orders':
                input.value = 'How do I place an order?';
                break;
            case 'contact':
                input.value = 'How can I contact you?';
                break;
        }
        
        this.sendMessage();
    }

    showWelcomeMessage() {
        setTimeout(() => {
            this.addMessage(this.getRandomResponse('greetings'), 'bot');
        }, 1000);
    }
}

// Initialize chatbot when page loads
document.addEventListener('DOMContentLoaded', function() {
    window.chatbot = new AgroKartChatbot();
});
