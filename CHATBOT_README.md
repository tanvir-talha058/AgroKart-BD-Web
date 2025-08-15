# 🤖 Modern AgroKart Chatbot

A cutting-edge AI-powered chatbot for agricultural e-commerce with advanced features including voice interaction, image recognition, real-time order tracking, and intelligent customer support.

## 🌟 Key Features

### 1. AI-Powered Intelligence
- **Natural Language Processing**: Context-aware conversation handling
- **Intent Recognition**: Automatically detects user intentions (orders, complaints, inquiries)
- **Sentiment Analysis**: Monitors customer mood and adapts responses accordingly
- **Smart Responses**: Dynamic response generation based on context and user history

### 2. Voice Interaction
- **Speech Recognition**: Voice commands in Bengali and English
- **Text-to-Speech**: Audio responses for accessibility
- **Hands-free Operation**: Complete voice-controlled chatbot interaction
- **Voice Commands**: Natural voice queries like "আমি সবজি কিনতে চাই" or "Track my order"

### 3. Image Recognition & Analysis
- **Product Identification**: Upload product images for instant recognition
- **Price Discovery**: Get real-time pricing from uploaded images
- **Nutritional Information**: Detailed nutritional facts for identified products
- **Recipe Suggestions**: Cooking recommendations based on recognized items
- **Quality Assessment**: Image quality scoring and enhancement suggestions

### 4. Advanced Order Management
- **Real-time GPS Tracking**: Live location updates of delivery vehicles
- **Smart Cancellation**: Intelligent refund calculations based on order progress
- **Delivery Predictions**: AI-powered estimated delivery times
- **Status Notifications**: Proactive updates via SMS/Email
- **Flexible Rescheduling**: Easy delivery time modifications

### 5. Intelligent Price Negotiations
- **Bulk Discount Calculator**: Automated pricing for large orders
- **Customer Loyalty Rewards**: Personalized discounts based on history
- **Market Price Comparisons**: Real-time competitive pricing
- **Seasonal Adjustments**: Dynamic pricing based on market conditions
- **Negotiation Analytics**: Track successful negotiation patterns

### 6. Multi-language Support
- **Bengali & English**: Complete bilingual support
- **Instant Language Switching**: One-click language toggle
- **Cultural Context**: Culturally appropriate responses and greetings
- **Localized Content**: Region-specific product information and pricing

### 7. Customer Experience Enhancement
- **Personalized Recommendations**: AI-driven product suggestions
- **Purchase History Analysis**: Smart reordering suggestions
- **Seasonal Alerts**: Timely notifications about product availability
- **Quality Feedback Loop**: Continuous improvement based on ratings
- **Accessibility Features**: Screen reader support and keyboard navigation

### 8. Live Agent Integration
- **Seamless Escalation**: One-click transfer to human agents
- **Context Preservation**: Complete conversation history transfer
- **Agent Availability**: Real-time agent status and queue management
- **Specialized Routing**: Direct connection to relevant expert agents
- **Follow-up Management**: Automated post-interaction surveys

## 🚀 Getting Started

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser with JavaScript enabled
- HTTPS enabled for voice features

### Installation

1. **Clone or Download Files**
   ```bash
   # Copy the chatbot files to your AgroKart directory
   js/modern-chatbot.js
   css/modern-chatbot.css
   php/enhanced_chatbot_api.php
   php/image_recognition_api.php
   php/analytics_api.php
   php/rating_api.php
   ```

2. **Update Header Include**
   ```php
   <!-- Add to your header.php -->
   <link rel="stylesheet" href="css/modern-chatbot.css">
   <script src="js/modern-chatbot.js"></script>
   ```

3. **Database Setup**
   ```sql
   -- Tables are created automatically by the APIs
   -- No manual setup required
   ```

4. **Initialize Chatbot**
   ```javascript
   document.addEventListener('DOMContentLoaded', function() {
       window.modernChatbot = new ModernAgroKartChatbot();
   });
   ```

## 🎯 Usage Examples

### Basic Interaction
```javascript
// Customer asks for products
User: "Show me fresh vegetables"
Bot: "🥬 Here are our fresh vegetables available today:
     1. Tomatoes - ৳45/kg
     2. Carrots - ৳50/kg
     3. Onions - ৳40/kg
     Would you like to see more details or add to cart?"
```

### Voice Commands
```javascript
// Voice interaction examples
"আমি টমেটো কিনতে চাই" → Product search for tomatoes
"Track my order number 123" → Order tracking
"Connect me to agent" → Live agent escalation
"Switch to English" → Language change
```

### Image Recognition
```javascript
// Upload vegetable image
User: [Uploads tomato image]
Bot: "🔍 I can see this is Fresh Tomatoes!
     💰 Current price: ৳45/kg
     📦 Stock: 250kg available
     🥗 Recipe suggestion: Tomato Curry, Fresh Salad
     Would you like to add to cart?"
```

### Order Tracking
```javascript
// Real-time tracking
User: "Where is my order #ORD456?"
Bot: "📦 Order #ORD456 Status:
     ✅ Order Confirmed - 2:30 PM
     ✅ Preparing Order - 2:45 PM
     🔄 Out for Delivery - 3:15 PM
     📍 Current Location: Gulshan Avenue
     🚚 ETA: 4:00 PM"
```

### Price Negotiation
```javascript
// Bulk pricing
User: "I need 50kg rice, any discount?"
Bot: "💰 Bulk Pricing for 50kg Rice:
     • Regular price: ৳55/kg × 50 = ৳2,750
     • Bulk discount: 15% off
     • Your price: ৳46.75/kg × 50 = ৳2,337.50
     • Total savings: ৳412.50
     
     Accept this offer?"
```

## 🛠️ API Endpoints

### Enhanced Chatbot API
```php
// php/enhanced_chatbot_api.php
GET/POST ?action=track_order&order_id=123
GET/POST ?action=cancel_order&order_id=123&confirm=true
GET/POST ?action=get_bulk_pricing&product_id=1&quantity=50
GET/POST ?action=get_recommendations&user_id=123
GET/POST ?action=connect_agent&session_id=abc123
```

### Image Recognition API
```php
// php/image_recognition_api.php
POST /image_recognition_api.php
Content-Type: multipart/form-data
Body: image file
```

### Analytics API
```php
// php/analytics_api.php
POST ?action=track_interaction
GET ?action=get_dashboard
POST ?action=save_rating
GET ?action=get_popular_queries
```

## 📊 Analytics & Metrics

### Performance Tracking
- **Response Time**: Average chatbot response time
- **Resolution Rate**: Percentage of successfully resolved queries
- **User Satisfaction**: Star ratings and feedback scores
- **Popular Queries**: Most frequently asked questions
- **Language Preferences**: Usage distribution by language

### Business Intelligence
- **Conversion Rate**: Chat interactions leading to purchases
- **Agent Escalation Rate**: Percentage requiring human intervention
- **Customer Sentiment**: Mood analysis and trend tracking
- **Peak Usage Times**: Optimal staffing insights
- **Feature Adoption**: Most used chatbot features

## 🔧 Customization

### Response Templates
```javascript
// Modify responses in modern-chatbot.js
this.responses = {
    greetings: {
        en: ["Custom English greeting"],
        bn: ["কাস্টম বাংলা অভিবাদন"]
    }
};
```

### Styling
```css
/* Customize appearance in modern-chatbot.css */
.chatbot-toggle {
    background: your-brand-color;
}
```

### Features
```javascript
// Enable/disable features
this.voiceEnabled = true;
this.imageRecognitionEnabled = true;
this.multilanguageEnabled = true;
```

## 🔒 Security Features

- **Input Sanitization**: All user inputs are properly sanitized
- **SQL Injection Prevention**: Prepared statements used throughout
- **XSS Protection**: Output encoding for all dynamic content
- **Rate Limiting**: Prevents abuse and spam
- **Session Management**: Secure session handling
- **File Upload Security**: Image validation and virus scanning

## 🌍 Accessibility

- **Screen Reader Support**: ARIA labels and semantic HTML
- **Keyboard Navigation**: Full keyboard accessibility
- **High Contrast Mode**: Support for users with visual impairments
- **Reduced Motion**: Respects user motion preferences
- **Voice Alternative**: Text alternatives for all voice features

## 📱 Mobile Responsiveness

- **Touch-friendly Interface**: Optimized for mobile devices
- **Responsive Design**: Adapts to all screen sizes
- **Offline Capability**: Basic functionality without internet
- **Progressive Web App**: Installable as mobile app
- **Performance Optimized**: Fast loading on slow connections

## 🚀 Performance Optimization

- **Lazy Loading**: Components loaded on demand
- **Caching Strategy**: Intelligent response caching
- **Compression**: Optimized assets and API responses
- **CDN Integration**: Fast global content delivery
- **Database Optimization**: Efficient queries and indexing

## 🧪 Testing

### Unit Tests
```bash
# Test chatbot functionality
npm test

# Test API endpoints
php test_apis.php

# Test database functions
php test_database.php
```

### Manual Testing Checklist
- [ ] Voice recognition works in both languages
- [ ] Image upload and recognition functional
- [ ] Order tracking displays real-time data
- [ ] Price negotiation calculations correct
- [ ] Live agent handoff seamless
- [ ] Mobile responsiveness verified
- [ ] Accessibility features working

## 📈 Future Enhancements

### Planned Features
1. **AI Chatbot Training**: Machine learning model improvements
2. **Video Call Support**: Face-to-face agent interactions
3. **AR Product Visualization**: Augmented reality product preview
4. **Blockchain Integration**: Transparent supply chain tracking
5. **IoT Integration**: Smart farming device connectivity
6. **Weather Integration**: Crop and delivery weather alerts

### Integration Roadmap
- **WhatsApp Business API**: Extend chatbot to WhatsApp
- **Facebook Messenger**: Social media integration
- **SMS Chatbot**: Text message support
- **Telegram Bot**: Additional messaging platform
- **Voice Assistants**: Google Assistant/Alexa integration

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Implement your enhancement
4. Test thoroughly
5. Submit a pull request

## 📞 Support

- **Email**: support@agrokart-bd.com
- **Phone**: +880 1776-199963
- **Documentation**: Check this README and inline comments
- **Issues**: Report bugs via GitHub issues

## 📄 License

This project is proprietary software developed for AgroKart BD. All rights reserved.

## 🙏 Acknowledgments

- **OpenAI**: Inspiration for conversational AI
- **Web Speech API**: Browser voice recognition capabilities
- **Font Awesome**: Icons and visual elements
- **Bootstrap**: Responsive design framework
- **PHP Community**: Server-side development resources

---

## 🔥 Quick Demo

Want to see the chatbot in action? Open `modern_chatbot_demo.html` in your browser for a full-featured demonstration of all capabilities!

**Demo Features:**
- ✅ Live voice interaction
- ✅ Image recognition simulation
- ✅ Real-time order tracking
- ✅ Price negotiation calculator
- ✅ Multi-language switching
- ✅ Agent escalation workflow

Experience the future of agricultural e-commerce customer support! 🌱🤖
