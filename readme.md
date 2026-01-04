# 📱 Expense Tracker Pro - Mobile App Development Proposal

![Expense Tracker](https://img.shields.io/badge/Version-1.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.0+-purple)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-yellow)
![Responsive](https://img.shields.io/badge/Responsive-✓-success)
![License](https://img.shields.io/badge/License-MIT-green)

A professional expense tracking web application with integrated mobile app development proposal system. This application showcases custom app development services with direct WhatsApp integration for client inquiries.

## 🎯 Features

### 📊 **Expense Management**
- Real-time expense tracking and categorization
- Interactive charts and analytics dashboard
- Multi-platform expense management
- PDF/CSV/JSON export functionality
- Cloud sync and data backup

### 📱 **App Development Proposal System**
- Professional mobile app development showcase
- WhatsApp API integration for instant client contact
- Package-based pricing (Basic, Pro, Enterprise)
- Development timeline visualization
- Technology stack display

### 📞 **Client Acquisition Features**
- **Direct WhatsApp Integration**: Click-to-chat functionality
- **Package Inquiry Links**: Pre-filled messages for each pricing tier
- **Mobile-Optimized Proposal**: Responsive design for all devices
- **Downloadable Proposal**: HTML proposal with all contact details
- **Multi-channel Contact**: WhatsApp, Email, Phone options

## 🚀 Quick Start

### Installation
```bash
# Clone the repository
git clone https://github.com/yourusername/expense-tracker.git
cd expense-tracker

# Import database
mysql -u username -p database_name < database/schema.sql

# Configure database
cp config/database.example.php config/database.php
# Edit config/database.php with your credentials
```

### Database Setup
```sql
-- Run in your MySQL database
CREATE DATABASE expense_tracker;
USE expense_tracker;

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    icon VARCHAR(10) DEFAULT '📊',
    color VARCHAR(7) DEFAULT '#6b7280',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Expenses table
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    amount DECIMAL(10,2) NOT NULL,
    category_id INT NOT NULL,
    description VARCHAR(255),
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Sample data
INSERT INTO categories (name, icon, color) VALUES
('Food & Dining', '🍔', '#ef4444'),
('Transport', '🚗', '#3b82f6'),
('Shopping', '🛍️', '#8b5cf6'),
('Entertainment', '🎬', '#ec4899'),
('Bills & Utilities', '💡', '#f59e0b');

-- Create logs directory for proposals
mkdir logs
chmod 755 logs
```

## 📁 Project Structure

```
expense-tracker/
├── 📁 api/
│   ├── add_expense.php
│   ├── delete_expense.php
│   ├── get_expenses.php
│   ├── get_stats.php
│   ├── get_chart_data.php
│   ├── get_insights.php
│   └── store_lead.php          # Lead storage for app inquiries
│
├── 📁 assets/
│   ├── css/
│   │   └── custom.css
│   └── js/
│       └── main.js
│
├── 📁 config/
│   └── database.php
│
├── 📁 logs/                     # Proposal download logs
│   ├── downloads.log
│   └── app_leads.csv
│
├── 📄 index.php                # Main application
├── 📄 README.md
└── 📄 LICENSE
```

## 💼 App Development Proposal Features

### Developer Profile Display
- Full Stack Developer credentials
- MERN Stack expertise
- Application development portfolio
- Direct contact information

### Pricing Packages
| Package | Price (PKR) | Price (USD) | Timeline | Features |
|---------|-------------|-------------|----------|----------|
| **Basic** | Rs 75,000 | $250 | 1-2 weeks | Single platform, basic tracking |
| **Pro** | Rs 150,000 | $500 | 2-3 weeks | Multi-platform, advanced analytics |
| **Enterprise** | Custom | Custom | Custom | Custom features, priority support |

### WhatsApp Integration
All contact points use WhatsApp API for instant communication:


**Pre-filled messages for each package:**
- Basic Package: Inquiry about Rs 75,000 package
- Pro Package: Discussion about Rs 150,000 package
- Enterprise: Custom requirements discussion

## 📱 Mobile Responsive Design

### Breakpoints
- **Mobile (< 640px)**: Stacked layout, compact design
- **Tablet (640px-1024px)**: Adaptive columns
- **Desktop (> 1024px)**: Full three-column layout

### Touch Optimization
- Minimum 44px touch targets
- Active state feedback
- Smooth scrolling
- iOS-friendly form inputs

## 🛠️ Technology Stack

### Frontend
- **HTML5**: Semantic markup
- **Tailwind CSS**: Utility-first styling
- **JavaScript (ES6+)**: Modern JavaScript
- **Chart.js**: Data visualization
- **Lucide Icons**: Beautiful icon set

### Backend
- **PHP 8.0+**: Server-side processing
- **MySQL**: Database management
- **RESTful API**: Clean API design

### Integration
- **WhatsApp Business API**: Direct client communication
- **Email Integration**: Contact form processing
- **File Generation**: HTML proposal downloads

## 🔧 Configuration

### Database Configuration
```php
// config/database.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'expense_tracker');
define('DB_USER', 'your_username');
define('DB_PASSWORD', 'your_password');
```
## 📈 Usage

### 1. Expense Tracking
- Add expenses with categories
- View real-time analytics
- Export data in multiple formats
- Track spending trends

### 2. Client Acquisition
- Click "Download App" in footer
- View app development proposal
- Choose package and click WhatsApp inquiry
- Download detailed proposal PDF
- Schedule consultation via WhatsApp

### 3. Lead Management
All inquiries are logged in:
- `logs/downloads.log`: Download activity
- `logs/app_leads.csv`: Lead information (if form submitted)

## 🚀 Deployment

### Requirements
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- SSL certificate (for WhatsApp API)

### Deployment Steps
```bash
# 1. Upload files to server
scp -r expense-tracker/ user@yourserver:/var/www/html/

# 2. Set permissions
chmod -R 755 /var/www/html/expense-tracker
chmod -R 777 /var/www/html/expense-tracker/logs

# 3. Configure database
mysql -u root -p < database/schema.sql

# 4. Set up virtual host (Apache)
<VirtualHost *:80>
    ServerName expense-tracker.yourdomain.com
    DocumentRoot /var/www/html/expense-tracker
    # SSL configuration for WhatsApp API
</VirtualHost>
```

### SSL Configuration
WhatsApp API requires HTTPS. Obtain SSL certificate:
```bash
# Using Let's Encrypt
sudo certbot --apache -d expense-tracker.yourdomain.com
```

## 🔒 Security

### Best Practices
1. **Input Validation**: All user inputs are sanitized
2. **SQL Injection Protection**: Prepared statements
3. **XSS Prevention**: Output escaping
4. **CSRF Protection**: Form token validation
5. **File Upload Security**: Restricted file types

### Security Headers
```apache
# .htaccess configuration
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
```

## 📊 Analytics & Monitoring

### Log Files
- `logs/downloads.log`: Tracks all proposal downloads
- `logs/app_leads.csv`: Stores lead information
- `logs/error.log`: Application errors

### Performance Monitoring
- Database query optimization
- Cached chart data
- Lazy loading for images
- Minified assets for production

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Add comments for complex logic
- Update documentation for new features
- Test on multiple devices

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

```
MIT License

Copyright (c) 2024 Usama Bashir

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

## 👨‍💻 Developer

**Usama Bashir**
- Full Stack Developer (Primary)
- MERN Stack Developer (Secondary)
- Application Development (Tertiary/Hobby)
- 📞 +92 319 6977218
- 📧 usamapubg50@gmail.com
- 💬 WhatsApp: +92 319 6977218

## 📞 Support

For support or app development inquiries:
1. **WhatsApp**: +92 319 6977218
2. **Email**: usamapubg50@gmail.com
3. **Direct Call**: +92 319 6977218

## 🌟 Acknowledgments

- Icons by [Lucide](https://lucide.dev/)
- Charts by [Chart.js](https://www.chartjs.org/)
- Styling with [Tailwind CSS](https://tailwindcss.com/)
- Inspiration from modern SaaS applications

---

<div align="center">

### Transform Your Expense Tracking into a Mobile App Today!

**Start your project with a free consultation**

[![WhatsApp](https://img.shields.io/badge/WhatsApp-25D366?style=for-the-badge&logo=whatsapp&logoColor=white)](https://api.whatsapp.com/send?phone=923196977218)
[![Email](https://img.shields.io/badge/Email-D14836?style=for-the-badge&logo=gmail&logoColor=white)](mailto:usamapubg50@gmail.com)

</div>