# ChakaNoks Supply Chain Management System (SCMS)

A comprehensive, web-based Supply Chain Management System designed to integrate inventory, purchasing, and supplier management for multi-branch operations with support for franchising and expansion.

## 🎯 System Overview

The ChakaNoks SCMS is a centralized platform that provides real-time visibility and control over supply chain operations across multiple branches. It supports inventory management, purchase order processing, logistics coordination, and franchise management with role-based access control and comprehensive reporting.

## ✨ Core Features

### 🏪 Inventory Management
- **Real-time tracking** across all branches
- **Automatic stock alerts** for low inventory levels
- **Barcode scanning** for fast stock updates
- **Perishable goods expiry tracking**
- **Multi-category inventory support** (Produce, Meat, Dairy, Frozen, Dry Goods, Beverages)

### 📋 Purchase Order & Supplier Management
- **Centralized supplier database** with contact details and terms
- **Automated purchase request creation** from branches
- **Approval workflow** (Branch → Central Office → Supplier)
- **Order tracking** with delivery status updates
- **Supplier performance monitoring**

### 🚚 Logistics & Distribution
- **Delivery scheduling and tracking**
- **Route optimization** for deliveries to branches
- **Transfer requests** between branches
- **Real-time delivery monitoring**

### 🏢 Central Office Dashboard
- **Consolidated reports** for all branches
- **Purchase request approval/denial**
- **Supplier performance analysis**
- **Cost, wastage, and demand analysis**
- **Executive summary and KPIs**

### 🤝 Franchising Management
- **Franchise application processing**
- **Supply allocation** for franchise partners
- **Royalty and payment tracking**
- **Performance monitoring**
- **Training program management**

### 🔒 Security & User Management
- **Role-based access control** (Admin, Manager, Staff, Viewer)
- **Secure login** with activity logs
- **Data backup and recovery**
- **System health monitoring**

## 🏗️ System Architecture

### Dashboard Structure
1. **Main Dashboard** (`dashboard.html`) - Overview and navigation hub
2. **Inventory Management** (`inventory-dashboard.html`) - Stock control and tracking
3. **Purchase Orders** (`purchase-dashboard.html`) - Order management and supplier relations
4. **Logistics & Distribution** (`logistics-dashboard.html`) - Delivery and route optimization
5. **Central Office** (`central-office-dashboard.html`) - Executive overview and approvals
6. **Franchise Management** (`franchise-dashboard.html`) - Partner management and support
7. **Security & Users** (`security-dashboard.html`) - Access control and system security

### Technology Stack
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Styling**: Custom CSS with responsive design
- **Icons**: Font Awesome 6.0
- **Architecture**: Single-page application with modular components

## 🚀 Installation & Setup

### Prerequisites
- Web server (Apache, Nginx, or XAMPP)
- Modern web browser with JavaScript enabled
- No database setup required (uses local storage for demo)

### Installation Steps
1. **Clone or download** the project files to your web server directory
2. **Ensure all files** are in the same directory:
   - `dashboard.html` (Main dashboard)
   - `inventory-dashboard.html`
   - `purchase-dashboard.html`
   - `logistics-dashboard.html`
   - `central-office-dashboard.html`
   - `franchise-dashboard.html`
   - `security-dashboard.html`
   - `style.css` (Main stylesheet)
   - `script.js` (Main dashboard functionality)
   - `inventory-script.js`
   - `purchase-script.js`
   - `logistics-script.js`
   - `central-office-script.js`
   - `franchise-script.js`
   - `security-script.js`

3. **Access the system** by opening `dashboard.html` in your web browser
4. **Navigate between dashboards** using the sidebar navigation

## 📱 User Roles & Access

### 🔐 Administrator
- Full system access
- User management and role assignment
- System configuration
- Data export/import capabilities

### 👔 Manager
- Branch management
- Inventory control
- Reports generation
- Limited user management

### 👷 Staff
- Basic operations
- Data entry
- View reports
- Limited access to sensitive functions

### 👁️ Viewer
- Read-only access
- Basic reporting
- No modification capabilities

## 🎨 Dashboard Features

### Main Dashboard
- **Quick stats overview** with key metrics
- **Branch status monitoring**
- **Recent activity feed**
- **Quick action buttons**

### Inventory Dashboard
- **Real-time stock levels**
- **Low stock alerts**
- **Expiry date tracking**
- **Barcode scanning interface**
- **Stock adjustment tools**

### Purchase Order Dashboard
- **Order creation and management**
- **Supplier database**
- **Approval workflow**
- **Delivery tracking**

### Logistics Dashboard
- **Route optimization**
- **Delivery scheduling**
- **Transfer management**
- **Performance analytics**

### Central Office Dashboard
- **Executive summary**
- **Multi-branch performance**
- **Cost analysis**
- **Wastage monitoring**
- **Demand forecasting**

### Franchise Dashboard
- **Application processing**
- **Partner management**
- **Supply allocation**
- **Royalty tracking**
- **Performance metrics**

### Security Dashboard
- **User management**
- **Role configuration**
- **Activity monitoring**
- **System health**
- **Backup management**

## 🔧 Customization

### Adding New Features
1. **Create new HTML dashboard** following the existing structure
2. **Add corresponding JavaScript file** for functionality
3. **Update navigation** in all dashboard files
4. **Extend CSS** as needed for new components

### Modifying Existing Features
- **HTML structure**: Modify the respective dashboard file
- **Functionality**: Update the corresponding JavaScript file
- **Styling**: Modify `style.css` or add custom CSS

## 📊 Data Management

### Sample Data
The system includes sample data for demonstration purposes:
- Sample inventory items
- Sample purchase orders
- Sample suppliers
- Sample users and roles
- Sample franchise data

### Real Data Integration
To integrate with real data sources:
1. **Replace sample data functions** with API calls
2. **Implement database connectivity**
3. **Add authentication system**
4. **Set up data synchronization**

## 🚨 Security Considerations

### Current Security Features
- Role-based access control
- Activity logging
- Input validation
- XSS protection

### Recommended Enhancements
- **HTTPS implementation**
- **Database encryption**
- **Two-factor authentication**
- **Regular security audits**
- **Backup encryption**

## 📈 Performance Optimization

### Current Optimizations
- Responsive design
- Efficient CSS grid layouts
- Optimized JavaScript functions
- Font Awesome CDN usage

### Additional Recommendations
- **Image optimization**
- **CSS/JS minification**
- **CDN implementation**
- **Database indexing**
- **Caching strategies**

## 🐛 Troubleshooting

### Common Issues
1. **Dashboard not loading**: Check file paths and web server configuration
2. **Styles not applied**: Ensure CSS file is accessible
3. **JavaScript errors**: Check browser console for error messages
4. **Navigation issues**: Verify all HTML files are in the same directory

### Debug Mode
- Open browser developer tools (F12)
- Check Console tab for JavaScript errors
- Check Network tab for file loading issues
- Verify file permissions on web server

## 🔮 Future Enhancements

### Planned Features
- **Mobile application** development
- **Advanced analytics** and reporting
- **AI-powered demand forecasting**
- **Integration with ERP systems**
- **Real-time notifications**
- **Advanced route optimization**

### Technology Upgrades
- **React/Vue.js** frontend framework
- **Node.js** backend implementation
- **MongoDB/PostgreSQL** database
- **Docker** containerization
- **CI/CD** pipeline implementation

## 📞 Support & Contact

### Documentation
- This README file
- Inline code comments
- HTML structure documentation

### Getting Help
1. **Review this README** for common solutions
2. **Check browser console** for error messages
3. **Verify file structure** matches installation requirements
4. **Test with different browsers** to isolate issues

## 📄 License

This project is developed for ChakaNoks and is proprietary software. All rights reserved.

## 🎉 Acknowledgments

- **Font Awesome** for icon library
- **Modern CSS Grid** for responsive layouts
- **ES6+ JavaScript** for modern functionality
- **Web standards** for cross-browser compatibility

---

**ChakaNoks SCMS** - Empowering supply chain excellence across all branches and franchises.

*Last updated: January 2025*
