# PHELS - Public Health Emergency Logistics System

A comprehensive web-based logistics management system designed to optimize medical supply distribution during public health emergencies. The system integrates simulated IoT monitoring, AI forecasting, and GIS mapping for real-time tracking, proactive planning, and emergency coordination.

## 🌟 Key Features

### Core Functionality
- **Centralized Dashboard** - Overview widgets and interactive charts for Emergency Managers
- **Comprehensive Inventory Management** - Full CRUD operations for medical items with expiry tracking
- **Real-Time Storage Monitoring** - Simulated IoT sensors with automated alerts
- **Automated Expiry Alerts** - Configurable warning system for expiring medical supplies
- **GPS-Based Shipment Tracking** - Real-time shipment monitoring with simulated GPS movement
- **Route Optimization** - Intelligent route planning for emergency deliveries
- **Emergency Operations Center** - Action logging and integrated chat system
- **Role-Based Access Control** - Secure access management for different user roles

### Technical Features
- **Free OpenStreetMap Integration** - No API keys required, unlimited usage
- **Simulated IoT Data** - Real-time sensor monitoring simulation
- **Interactive Charts** - Data visualization with Chart.js
- **Responsive Design** - Mobile-friendly Bootstrap 5 interface
- **Real-time Updates** - AJAX-powered live data refresh

## 🛠️ Technology Stack

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Modern styling with Bootstrap 5
- **JavaScript (ES6+)** - Interactive functionality
- **Bootstrap 5** - Responsive UI framework
- **Chart.js** - Data visualization
- **Leaflet.js** - OpenStreetMap integration

### Backend
- **PHP 7.4+** - Server-side logic
- **MySQL 5.7+** - Database management
- **PDO** - Secure database operations
- **Session Management** - User authentication

### Mapping & GIS
- **OpenStreetMap** - Free, open-source mapping data
- **Leaflet.js** - Lightweight mapping library
- **No API Keys Required** - Unlimited usage, no costs

### Server
- **XAMPP** - Apache + MySQL + PHP stack
- **Apache 2.4+** - Web server
- **MySQL 5.7+** - Database server

## 📋 Prerequisites

- **XAMPP** (Apache + MySQL + PHP) installed and running
- **PHP 7.4** or higher
- **MySQL 5.7** or higher
- **Modern web browser** with JavaScript enabled
- **Internet connection** for OpenStreetMap tiles

## 🚀 Installation & Setup

### Step 1: XAMPP Setup
1. Download and install [XAMPP](https://www.apachefriends.org/)
2. Start Apache and MySQL services
3. Ensure both services show green status in XAMPP Control Panel

### Step 2: Project Setup
1. Clone or download this project to `C:\xampp\htdocs\PHELS Project\`
2. Ensure all files are in the correct directory structure

### Step 3: Database Setup
1. Open your browser and navigate to `http://localhost/phpmyadmin`
2. Create a new database called `phels_db`
3. Import the `database_schema.sql` file:
   - Click on the `phels_db` database
   - Go to "Import" tab
   - Choose the `database_schema.sql` file
   - Click "Go" to import

### Step 4: Configuration
1. Open `config.php` in your code editor
2. Verify database connection settings:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'phels_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
3. Update `APP_URL` if your local path differs:
   ```php
   define('APP_URL', 'http://localhost/PHELS%20Project');
   ```

### Step 5: Access the System
1. Navigate to `http://localhost/PHELS%20Project/`
2. Login with default credentials:
   - **Username:** `admin`
   - **Password:** `admin123`

## 🔐 Default Login Credentials

| Role | Username | Password | Access Level |
|------|----------|----------|--------------|
| Emergency Manager | `admin` | `admin123` | Full system access |
| Field Worker | `fieldworker` | `field123` | Limited access |
| Hospital Staff | `hospital` | `hosp123` | Inventory & alerts |
| Pharma Company | `pharma` | `pharma123` | Inventory management |

## 📱 Usage Guide

### Dashboard
- **Overview Statistics** - Key metrics at a glance
- **Interactive Map** - Storage unit locations with OpenStreetMap
- **Recent Activity** - Latest alerts, shipments, and actions
- **Inventory Charts** - Visual representation of medical supplies

### Inventory Management
- **Add Items** - Create new medical supply entries
- **Edit Items** - Update existing inventory information
- **Delete Items** - Remove obsolete supplies
- **Search & Filter** - Find specific items quickly
- **Expiry Tracking** - Monitor expiration dates

### Storage Monitoring
- **Register Units** - Add new storage facilities
- **Sensor Data** - View simulated IoT readings
- **Threshold Alerts** - Configure warning levels
- **Status Monitoring** - Real-time facility status

### Shipment Tracking
- **Create Shipments** - Plan new deliveries
- **Live Tracking** - Monitor shipment progress
- **Route Optimization** - Plan efficient delivery routes
- **Status Updates** - Track delivery progress

### Alert System
- **Expiry Warnings** - Automated expiration alerts
- **Storage Alerts** - Threshold breach notifications
- **System Alerts** - General system notifications
- **Alert Management** - Mark alerts as resolved

### Operations Center
- **Action Logging** - Record emergency actions
- **Live Feed** - Real-time activity monitoring
- **Chat System** - Team communication
- **Event Tracking** - Emergency response coordination

## 🗺️ OpenStreetMap Integration

### Benefits
- **Completely Free** - No API costs or usage limits
- **Open Source** - Community-driven mapping data
- **Global Coverage** - Worldwide mapping support
- **No Registration** - Immediate access, no signup required

### Features
- **Interactive Maps** - Zoom, pan, and explore
- **Custom Markers** - Location-specific information
- **Route Visualization** - Path planning and display
- **Real-time Updates** - Dynamic map content

### Technical Implementation
- **Leaflet.js Library** - Lightweight, mobile-friendly
- **OpenStreetMap Tiles** - High-quality map data
- **Custom Icons** - Status-based visual indicators
- **Responsive Design** - Works on all devices

## 🔧 Customization

### Adding New Features
1. Create new PHP files in the root directory
2. Add navigation links to the sidebar
3. Implement role-based access control
4. Update database schema if needed

### Modifying Maps
1. Edit JavaScript code in relevant files
2. Customize marker icons and popups
3. Add new map layers or overlays
4. Implement custom routing algorithms

### Styling Changes
1. Modify CSS in individual files
2. Update Bootstrap classes for layout changes
3. Customize color schemes and themes
4. Add custom animations or transitions

## 🚨 Troubleshooting

### Common Issues

#### Database Connection Error
- Verify XAMPP is running
- Check database credentials in `config.php`
- Ensure `phels_db` database exists
- Verify MySQL service is active

#### Maps Not Loading
- Check internet connection
- Verify OpenStreetMap tile access
- Check browser console for JavaScript errors
- Ensure Leaflet.js is loading correctly

#### Login Issues
- Verify database import was successful
- Check user table has default accounts
- Clear browser cookies and cache
- Verify PHP session configuration

#### Page Not Found (404)
- Check file paths and permissions
- Verify Apache configuration
- Ensure `.htaccess` is properly configured
- Check XAMPP document root settings

### Performance Optimization
- **Database Indexing** - Add indexes for frequently queried fields
- **Caching** - Implement Redis or Memcached for session data
- **Image Optimization** - Compress map tiles and assets
- **CDN Usage** - Use CDN for external libraries

## 🔒 Security Considerations

### Current Security Features
- **Password Hashing** - Secure password storage
- **Prepared Statements** - SQL injection prevention
- **Session Security** - Secure session management
- **Role-Based Access** - Controlled feature access
- **Input Validation** - Data sanitization

### Recommended Enhancements
- **HTTPS** - Enable SSL/TLS encryption
- **Rate Limiting** - Prevent brute force attacks
- **Two-Factor Authentication** - Enhanced login security
- **Audit Logging** - Track system access
- **Regular Updates** - Keep dependencies current

## 📊 Database Schema

### Core Tables
- **users** - User accounts and authentication
- **medical_items** - Inventory management
- **storage_units** - Facility information
- **storage_logs** - Sensor data and monitoring
- **shipments** - Delivery tracking
- **shipment_items** - Shipment contents
- **alerts** - System notifications
- **action_logs** - User activity tracking
- **chat_messages** - Team communication

### Key Relationships
- Users have specific roles and permissions
- Medical items are stored in storage units
- Shipments contain multiple medical items
- Alerts are generated based on various conditions
- Actions are logged with user attribution

## 🚀 Future Enhancements

### Planned Features
- **Mobile App** - Native iOS/Android applications
- **Advanced Analytics** - Machine learning insights
- **Real IoT Integration** - Actual sensor hardware support
- **Multi-language Support** - International deployment
- **API Development** - Third-party integrations

### Scalability Improvements
- **Microservices Architecture** - Service-based design
- **Load Balancing** - Multiple server support
- **Database Clustering** - High availability setup
- **Caching Layer** - Performance optimization
- **Cloud Deployment** - AWS/Azure integration

## 📞 Support & Contributing

### Getting Help
1. Check this README for common solutions
2. Review error logs in XAMPP
3. Verify system requirements
4. Test with different browsers

### Contributing
1. Fork the project repository
2. Create feature branches
3. Submit pull requests
4. Follow coding standards
5. Test thoroughly before submission

### Reporting Issues
- Include error messages and screenshots
- Specify browser and operating system
- Describe steps to reproduce
- Provide system configuration details

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🙏 Acknowledgments

- **OpenStreetMap Contributors** - Free mapping data
- **Leaflet.js Team** - Lightweight mapping library
- **Bootstrap Team** - Responsive UI framework
- **Chart.js Contributors** - Data visualization library
- **PHP Community** - Server-side technology

---

**PHELS** - Empowering emergency response through intelligent logistics management.

*Built with ❤️ for public health and safety.*
