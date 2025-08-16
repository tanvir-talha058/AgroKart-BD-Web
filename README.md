# AgroKart BD - Agricultural E-Commerce Platform

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

**AgroKart BD** is a comprehensive agricultural e-commerce platform designed specifically for the Bangladeshi market. It connects farmers directly with consumers, offering fresh, organic agricultural products with seamless online shopping experience.

## 🌟 Features

### 🛒 **E-Commerce Core**

- **Product Catalog**: Browse vegetables, fruits, grains, and dairy products
- **Shopping Cart**: Add/remove products with real-time quantity management
- **Order Management**: Complete order lifecycle from placement to delivery
- **Payment Integration**: Secure payment processing
- **Inventory Management**: Real-time stock tracking and updates

### 👥 **User Management**

- **Multi-Authentication**: Email/password and Google OAuth login
- **User Roles**: Buyer and Seller account types
- **Profile Management**: User profiles with image upload
- **Order History**: Track past purchases and order status

### 📧 **Communication & Notifications**

- **Email Invoices**: Automated PDF invoice generation and email delivery
- **Password Recovery**: Secure OTP-based password reset
- **Order Notifications**: Email updates for order status changes
- **PHPMailer Integration**: Professional email templates

### 🤖 **AI-Powered Chatbot**

- **Smart Product Search**: Natural language product queries
- **Order Tracking**: Real-time order status updates
- **Customer Support**: Automated assistance and live agent escalation
- **Multi-language**: English and Bengali support
- **Voice Commands**: Speech recognition for hands-free interaction

### 📊 **Advanced Features**

- **Hot Deals**: Special promotions and discounted products
- **Bulk Pricing**: Wholesale rates for large quantities
- **Responsive Design**: Mobile-first approach for all devices
- **Admin Dashboard**: Comprehensive backend management
- **Analytics**: User interaction tracking and reporting

## 🚀 **Quick Start**

### Prerequisites

- **PHP 7.4** or higher
- **MySQL 5.7** or higher
- **Apache/Nginx** web server
- **Composer** (for dependency management)

### Installation

1. **Clone the Repository**

   ```bash
   git clone https://github.com/tanvir-talha058/AgroKart-BD-Web.git
   cd AgroKart-BD-Web
   ```

2. **Install Dependencies**

   ```bash
   composer install
   ```

3. **Database Setup**

   ```sql
   CREATE DATABASE agrobd;
   ```

   Import the database schema (contact for SQL file)

4. **Configuration**

   - Copy `includes/db_connect.php` and update database credentials
   - Configure email settings in PHPMailer setup
   - Set up Google OAuth credentials (see [Google OAuth Setup](GOOGLE_OAUTH_SETUP.md))

5. **File Permissions**

   ```bash
   chmod 755 images/profiles/
   chmod 755 images/uploads/
   ```

6. **Launch Application**
   - Place files in your web server directory
   - Access via `http://localhost/AgroKart-BD-Web`

## 📁 **Project Structure**

```
AgroKart-BD-Web/
├── 📁 css/                    # Stylesheets
│   ├── style.css             # Main styles
│   ├── form-style.css        # Form styling
│   ├── cart-style.css        # Shopping cart styles
│   └── chatbot_enhanced.css  # Chatbot interface
├── 📁 js/                     # JavaScript files
│   ├── validation.js         # Form validation
│   ├── dashboard.js          # Dashboard functionality
│   └── chatbot_enhanced.js   # AI chatbot
├── 📁 php/                    # Backend scripts
│   ├── login_process.php     # Authentication logic
│   ├── cart_manager.php      # Shopping cart operations
│   ├── order_process.php     # Order processing
│   ├── google_callback.php   # OAuth integration
│   └── invoice_utils.php     # PDF invoice generation
├── 📁 includes/               # Shared components
│   ├── header.php            # Page header
│   ├── footer.php            # Page footer
│   └── db_connect.php        # Database connection
├── 📁 images/                 # Static assets
│   ├── profiles/             # User profile images
│   └── uploads/              # Product images
├── 📄 index.php              # Homepage
├── 📄 login.php              # Login page
├── 📄 registration.php       # User registration
├── 📄 cart.php               # Shopping cart
├── 📄 checkout.php           # Order checkout
├── 📄 dashboard.php          # User dashboard
└── 📄 composer.json          # Dependencies
```

## 🛠️ **Technology Stack**

### **Backend**

- **PHP 7.4+**: Server-side logic
- **MySQL**: Database management
- **Composer**: Dependency management

### **Frontend**

- **HTML5/CSS3**: Modern web standards
- **JavaScript (ES6+)**: Interactive functionality
- **Bootstrap 5**: Responsive framework
- **Font Awesome**: Icon library

### **Key Libraries**

- **PHPMailer 6.10+**: Email functionality
- **TCPDF 6.10+**: PDF generation
- **Google API Client**: OAuth integration

### **Features**

- **Responsive Design**: Mobile-first approach
- **Progressive Web App**: Enhanced mobile experience
- **SEO Optimized**: Search engine friendly structure
- **Security**: SQL injection prevention, XSS protection

## 🔧 **Configuration**

### **Database Configuration**

Update `includes/db_connect.php`:

```php
$servername = "localhost";
$username = "your_db_username";
$password = "your_db_password";
$dbname = "agrobd";
```

### **Email Configuration**

Configure PHPMailer in invoice and notification files:

```php
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password';
```

### **Google OAuth Setup**

1. Create project in [Google Cloud Console](https://console.cloud.google.com/)
2. Enable Google+ API
3. Create OAuth 2.0 credentials
4. Update configuration in `php/google_callback.php`

## 📋 **API Endpoints**

| Endpoint                   | Method | Description                         |
| -------------------------- | ------ | ----------------------------------- |
| `/php/cart_manager.php`    | POST   | Cart operations (add/remove/update) |
| `/php/order_process.php`   | POST   | Process new orders                  |
| `/php/login_process.php`   | POST   | User authentication                 |
| `/php/google_callback.php` | GET    | OAuth callback handler              |
| `/php/chatbot_api.php`     | POST   | Chatbot interactions                |

## 🤖 **Chatbot Features**

The integrated AI chatbot provides:

- **Natural Language Processing**: Understands customer queries
- **Product Search**: Find products by description
- **Order Assistance**: Track orders and handle cancellations
- **Price Negotiations**: Bulk pricing calculations
- **Multi-language Support**: English and Bengali
- **Voice Recognition**: Speech-to-text functionality
- **Image Upload**: Product identification via photos

## 📱 **Mobile Responsiveness**

AgroKart BD is designed with a mobile-first approach:

- **Responsive Grid System**: Adapts to all screen sizes
- **Touch-Friendly Interface**: Optimized for mobile interactions
- **Fast Loading**: Optimized images and compressed assets
- **Progressive Web App**: App-like experience on mobile devices

## 🔒 **Security Features**

- **SQL Injection Prevention**: Prepared statements throughout
- **XSS Protection**: Input sanitization and output encoding
- **CSRF Protection**: Token-based form security
- **Secure Authentication**: Password hashing with bcrypt
- **OAuth Integration**: Secure third-party authentication
- **File Upload Security**: Type and size validation

## 🚀 **Performance Optimization**

- **Database Indexing**: Optimized queries for fast responses
- **Image Optimization**: Compressed images for faster loading
- **Caching Strategy**: Session and query result caching
- **Minified Assets**: Compressed CSS and JavaScript
- **CDN Integration**: External libraries from CDN

## 🧪 **Testing**

The project includes various test files for development:

- **Chatbot Testing**: Multiple test interfaces for AI features
- **API Testing**: Endpoint validation tools
- **Authentication Testing**: Login/registration test cases

## 📊 **Analytics & Monitoring**

- **User Interaction Tracking**: Chatbot conversation analytics
- **Order Analytics**: Sales and revenue tracking
- **Performance Monitoring**: Page load time optimization
- **Error Logging**: Comprehensive error tracking system

## 🤝 **Contributing**

We welcome contributions! Please follow these steps:

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/AmazingFeature`)
3. **Commit** your changes (`git commit -m 'Add some AmazingFeature'`)
4. **Push** to the branch (`git push origin feature/AmazingFeature`)
5. **Open** a Pull Request

### **Code Style Guidelines**

- Follow PSR-12 coding standards for PHP
- Use meaningful variable and function names
- Comment complex logic thoroughly
- Maintain consistent indentation (4 spaces)

## 📝 **License**

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 **Authors**

- **Tanvir Talha** - _Lead Developer_ - [@tanvir-talha058](https://github.com/tanvir-talha058)

## 🙏 **Acknowledgments**

- **Bootstrap Team** - For the responsive framework
- **Font Awesome** - For the comprehensive icon library
- **PHPMailer Contributors** - For email functionality
- **TCPDF Team** - For PDF generation capabilities
- **Google** - For OAuth and API services

## 📞 **Support**

For support and queries:

- **GitHub Issues**: [Create an Issue](https://github.com/tanvir-talha058/AgroKart-BD-Web/issues)
- **Email**: tanvir.talha058@gmail.com
- **Website**: [AgroKart BD](https://your-domain.com)

## 🗺️ **Roadmap**

### **Upcoming Features**

- [ ] **Mobile App**: React Native mobile application
- [ ] **Payment Gateway**: Stripe/PayPal integration
- [ ] **Advanced Analytics**: Business intelligence dashboard
- [ ] **API Documentation**: Comprehensive API docs
- [ ] **Multi-vendor Support**: Seller marketplace features
- [ ] **AI Recommendations**: Machine learning product suggestions
- [ ] **Real-time Chat**: WebSocket-based customer support
- [ ] **Inventory Alerts**: Low stock notifications
- [ ] **Multi-currency**: Support for multiple currencies
- [ ] **Advanced Search**: Elasticsearch integration

---

**Made with ❤️ for the Agricultural Community of Bangladesh**

_AgroKart BD - Bridging the gap between farmers and consumers through technology_
