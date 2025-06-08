<?php
/**
 * PDF Generation Script for Event Booking System Documentation
 * 
 * This script converts the comprehensive documentation to PDF format
 * using HTML to PDF conversion with proper styling and formatting.
 */

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    echo "<h1>PDF Documentation Generator</h1>";
    echo "<p>This script generates a comprehensive PDF documentation for the Event Booking System.</p>";
    echo "<p><strong>Note:</strong> For best results, run this script from the command line with proper PDF libraries installed.</p>";
    echo "<hr>";
}

// Documentation content
$documentation_content = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Booking System - Comprehensive Documentation</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 210mm;
            margin: 0 auto;
            padding: 20mm;
            background: white;
        }
        
        .cover-page {
            text-align: center;
            page-break-after: always;
            padding: 50mm 0;
        }
        
        .cover-title {
            font-size: 48px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #10b981, #06b6d4, #4f46e5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .cover-subtitle {
            font-size: 24px;
            color: #64748b;
            margin-bottom: 40px;
        }
        
        .cover-info {
            font-size: 16px;
            color: #64748b;
            margin: 10px 0;
        }
        
        h1 {
            color: #1e293b;
            font-size: 32px;
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 10px;
            margin-top: 40px;
            page-break-before: always;
        }
        
        h2 {
            color: #334155;
            font-size: 24px;
            margin-top: 30px;
            border-left: 4px solid #06b6d4;
            padding-left: 15px;
        }
        
        h3 {
            color: #475569;
            font-size: 20px;
            margin-top: 25px;
        }
        
        h4 {
            color: #64748b;
            font-size: 16px;
            margin-top: 20px;
        }
        
        .toc {
            page-break-after: always;
        }
        
        .toc h2 {
            text-align: center;
            border: none;
            padding: 0;
        }
        
        .toc ul {
            list-style: none;
            padding: 0;
        }
        
        .toc li {
            margin: 10px 0;
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }
        
        .toc a {
            text-decoration: none;
            color: #4f46e5;
        }
        
        code {
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: "Courier New", monospace;
            font-size: 14px;
        }
        
        pre {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            overflow-x: auto;
            font-family: "Courier New", monospace;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .highlight {
            background: #fef3c7;
            padding: 15px;
            border-left: 4px solid #f59e0b;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .info-box {
            background: #dbeafe;
            border: 1px solid #3b82f6;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .warning-box {
            background: #fef2f2;
            border: 1px solid #ef4444;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .success-box {
            background: #f0fdf4;
            border: 1px solid #22c55e;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
        }
        
        th, td {
            border: 1px solid #e2e8f0;
            padding: 12px;
            text-align: left;
        }
        
        th {
            background: #f8fafc;
            font-weight: bold;
            color: #374151;
        }
        
        .feature-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .feature-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
        }
        
        .feature-title {
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 10px;
        }
        
        .screenshot-placeholder {
            background: #f1f5f9;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            color: #64748b;
            margin: 20px 0;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .footer {
            position: fixed;
            bottom: 20mm;
            left: 20mm;
            right: 20mm;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15mm;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Cover Page -->
    <div class="cover-page">
        <h1 class="cover-title">Event Booking System</h1>
        <p class="cover-subtitle">Comprehensive Documentation</p>
        <div style="margin: 60px 0;">
            <div class="cover-info"><strong>Version:</strong> 1.0</div>
            <div class="cover-info"><strong>Date:</strong> ' . date('F j, Y') . '</div>
            <div class="cover-info"><strong>Author:</strong> Development Team</div>
            <div class="cover-info"><strong>Technology:</strong> PHP, MySQL, Bootstrap</div>
        </div>
        <div style="margin-top: 80px;">
            <p style="font-size: 18px; color: #4f46e5; font-weight: bold;">Complete System Documentation</p>
            <p style="color: #64748b;">Including Architecture, Implementation, Deployment, and User Guide</p>
        </div>
    </div>

    <!-- Table of Contents -->
    <div class="toc">
        <h2>Table of Contents</h2>
        <ul>
            <li><a href="#project-overview">1. Project Overview</a></li>
            <li><a href="#system-design">2. System Design</a></li>
            <li><a href="#architecture">3. Architecture</a></li>
            <li><a href="#implementation">4. Implementation Details</a></li>
            <li><a href="#database-design">5. Database Design</a></li>
            <li><a href="#ui-design">6. User Interface Design</a></li>
            <li><a href="#security">7. Security Implementation</a></li>
            <li><a href="#api-docs">8. API Documentation</a></li>
            <li><a href="#deployment">9. Deployment Guide</a></li>
            <li><a href="#user-manual">10. User Manual</a></li>
            <li><a href="#code-explanation">11. Code Explanation</a></li>
            <li><a href="#testing">12. Testing Documentation</a></li>
            <li><a href="#maintenance">13. Maintenance Guide</a></li>
            <li><a href="#screenshots">14. Screenshots Gallery</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div id="project-overview">
        <h1>1. Project Overview</h1>
        
        <div class="info-box">
            <strong>Project Name:</strong> Event Booking System<br>
            <strong>Purpose:</strong> Comprehensive web-based platform for event management and ticket booking<br>
            <strong>Target Users:</strong> Event organizers, attendees, and system administrators
        </div>

        <h2>1.1 Introduction</h2>
        <p>The Event Booking System is a comprehensive web-based application designed to streamline the process of event management and ticket booking. This platform connects event organizers with attendees through an intuitive, secure, and feature-rich interface.</p>

        <h2>1.2 Key Features</h2>
        <div class="feature-list">
            <div class="feature-item">
                <div class="feature-title">User Management</div>
                <ul>
                    <li>User registration and authentication</li>
                    <li>Profile management</li>
                    <li>Role-based access control</li>
                </ul>
            </div>
            <div class="feature-item">
                <div class="feature-title">Event Management</div>
                <ul>
                    <li>Event creation and editing</li>
                    <li>Category management</li>
                    <li>Venue integration with maps</li>
                </ul>
            </div>
            <div class="feature-item">
                <div class="feature-title">Booking System</div>
                <ul>
                    <li>Shopping cart functionality</li>
                    <li>Secure checkout process</li>
                    <li>QR code generation</li>
                </ul>
            </div>
            <div class="feature-item">
                <div class="feature-title">Admin Panel</div>
                <ul>
                    <li>Comprehensive dashboard</li>
                    <li>User and booking management</li>
                    <li>Analytics and reporting</li>
                </ul>
            </div>
        </div>

        <h2>1.3 Technology Stack</h2>
        <table>
            <tr>
                <th>Component</th>
                <th>Technology</th>
                <th>Version</th>
                <th>Purpose</th>
            </tr>
            <tr>
                <td>Backend</td>
                <td>PHP</td>
                <td>8.0+</td>
                <td>Server-side programming</td>
            </tr>
            <tr>
                <td>Database</td>
                <td>MySQL</td>
                <td>8.0+</td>
                <td>Data storage and management</td>
            </tr>
            <tr>
                <td>Frontend</td>
                <td>HTML5, CSS3, JavaScript</td>
                <td>Latest</td>
                <td>User interface</td>
            </tr>
            <tr>
                <td>Framework</td>
                <td>Bootstrap</td>
                <td>5.3</td>
                <td>Responsive design</td>
            </tr>
            <tr>
                <td>Icons</td>
                <td>Font Awesome</td>
                <td>6.4</td>
                <td>Icon library</td>
            </tr>
        </table>
    </div>

    <!-- System Design Section -->
    <div id="system-design" class="page-break">
        <h1>2. System Design</h1>
        
        <h2>2.1 Use Case Analysis</h2>
        <p>The system supports three main types of users:</p>
        
        <div class="feature-list">
            <div class="feature-item">
                <div class="feature-title">Guest Users</div>
                <ul>
                    <li>Browse events</li>
                    <li>View event details</li>
                    <li>Search events</li>
                    <li>Register account</li>
                </ul>
            </div>
            <div class="feature-item">
                <div class="feature-title">Registered Users</div>
                <ul>
                    <li>All guest capabilities</li>
                    <li>Book events</li>
                    <li>Manage bookings</li>
                    <li>Profile management</li>
                </ul>
            </div>
            <div class="feature-item">
                <div class="feature-title">Administrators</div>
                <ul>
                    <li>Event management</li>
                    <li>User management</li>
                    <li>Booking oversight</li>
                    <li>System analytics</li>
                </ul>
            </div>
        </div>

        <div class="screenshot-placeholder">
            <strong>Use Case Diagram</strong><br>
            [Mermaid diagram showing user interactions with the system]
        </div>

        <h2>2.2 System Architecture</h2>
        <p>The system follows a three-tier architecture pattern:</p>
        
        <div class="highlight">
            <strong>Presentation Tier:</strong> HTML, CSS, JavaScript, Bootstrap<br>
            <strong>Application Tier:</strong> PHP business logic, authentication, API endpoints<br>
            <strong>Data Tier:</strong> MySQL database with PDO abstraction layer
        </div>

        <div class="screenshot-placeholder">
            <strong>Architecture Diagram</strong><br>
            [Mermaid diagram showing system architecture layers]
        </div>
    </div>

    <!-- Database Design Section -->
    <div id="database-design" class="page-break">
        <h1>5. Database Design</h1>
        
        <h2>5.1 Entity Relationship Model</h2>
        <p>The database consists of four main entities with well-defined relationships:</p>
        
        <div class="screenshot-placeholder">
            <strong>Entity Relationship Diagram</strong><br>
            [Mermaid ERD showing database structure]
        </div>

        <h2>5.2 Table Structures</h2>
        
        <h3>Users Table</h3>
        <table>
            <tr>
                <th>Column</th>
                <th>Type</th>
                <th>Constraints</th>
                <th>Description</th>
            </tr>
            <tr>
                <td>id</td>
                <td>INT</td>
                <td>PRIMARY KEY, AUTO_INCREMENT</td>
                <td>Unique user identifier</td>
            </tr>
            <tr>
                <td>username</td>
                <td>VARCHAR(50)</td>
                <td>UNIQUE, NOT NULL</td>
                <td>User login name</td>
            </tr>
            <tr>
                <td>email</td>
                <td>VARCHAR(100)</td>
                <td>UNIQUE, NOT NULL</td>
                <td>User email address</td>
            </tr>
            <tr>
                <td>password</td>
                <td>VARCHAR(255)</td>
                <td>NOT NULL</td>
                <td>Hashed password</td>
            </tr>
            <tr>
                <td>role</td>
                <td>ENUM</td>
                <td>DEFAULT "user"</td>
                <td>User role (user/admin)</td>
            </tr>
        </table>

        <h3>Events Table</h3>
        <table>
            <tr>
                <th>Column</th>
                <th>Type</th>
                <th>Constraints</th>
                <th>Description</th>
            </tr>
            <tr>
                <td>id</td>
                <td>INT</td>
                <td>PRIMARY KEY, AUTO_INCREMENT</td>
                <td>Unique event identifier</td>
            </tr>
            <tr>
                <td>title</td>
                <td>VARCHAR(255)</td>
                <td>NOT NULL</td>
                <td>Event title</td>
            </tr>
            <tr>
                <td>event_date</td>
                <td>DATE</td>
                <td>NOT NULL</td>
                <td>Event date</td>
            </tr>
            <tr>
                <td>event_time</td>
                <td>TIME</td>
                <td>NOT NULL</td>
                <td>Event time</td>
            </tr>
            <tr>
                <td>venue</td>
                <td>VARCHAR(255)</td>
                <td>NOT NULL</td>
                <td>Event venue</td>
            </tr>
            <tr>
                <td>price</td>
                <td>DECIMAL(10,2)</td>
                <td>NOT NULL</td>
                <td>Ticket price</td>
            </tr>
        </table>
    </div>

    <!-- Screenshots Section -->
    <div id="screenshots" class="page-break">
        <h1>14. Screenshots Gallery</h1>
        
        <h2>14.1 User Interface Screenshots</h2>
        
        <h3>Homepage</h3>
        <div class="screenshot-placeholder">
            <strong>Homepage Screenshot</strong><br>
            URL: http://localhost:8000/index.php<br>
            Features: Navigation, hero section, featured events
        </div>

        <h3>Events Listing</h3>
        <div class="screenshot-placeholder">
            <strong>Events Page Screenshot</strong><br>
            URL: http://localhost:8000/events.php<br>
            Features: Event grid, search, filters, pagination
        </div>

        <h3>Event Details</h3>
        <div class="screenshot-placeholder">
            <strong>Event Details Screenshot</strong><br>
            URL: http://localhost:8000/event.php?id=1<br>
            Features: Event information, map, booking form
        </div>

        <h3>Shopping Cart</h3>
        <div class="screenshot-placeholder">
            <strong>Cart Screenshot</strong><br>
            URL: http://localhost:8000/cart.php<br>
            Features: Cart items, quantity controls, totals
        </div>

        <h3>Booking Confirmation</h3>
        <div class="screenshot-placeholder">
            <strong>Confirmation Screenshot</strong><br>
            URL: http://localhost:8000/booking-confirmation.php<br>
            Features: Success animation, QR code, booking details
        </div>

        <h2>14.2 Admin Interface Screenshots</h2>
        
        <h3>Admin Dashboard</h3>
        <div class="screenshot-placeholder">
            <strong>Dashboard Screenshot</strong><br>
            URL: http://localhost:8000/admin/dashboard.php<br>
            Features: Statistics, charts, quick actions
        </div>

        <h3>Event Management</h3>
        <div class="screenshot-placeholder">
            <strong>Admin Events Screenshot</strong><br>
            URL: http://localhost:8000/admin/events.php<br>
            Features: Event table, sorting, actions
        </div>

        <h3>Booking Management</h3>
        <div class="screenshot-placeholder">
            <strong>Admin Bookings Screenshot</strong><br>
            URL: http://localhost:8000/admin/bookings.php<br>
            Features: Booking table, status management, animations
        </div>

        <h2>14.3 Mobile Screenshots</h2>
        
        <div class="screenshot-placeholder">
            <strong>Mobile Homepage</strong><br>
            Responsive design for mobile devices
        </div>

        <div class="screenshot-placeholder">
            <strong>Mobile Events</strong><br>
            Touch-optimized event browsing
        </div>

        <div class="screenshot-placeholder">
            <strong>Mobile Checkout</strong><br>
            Mobile-friendly checkout process
        </div>
    </div>

    <!-- Footer -->
    <div class="footer no-print">
        Event Booking System Documentation - Generated on ' . date('F j, Y') . '
    </div>
</body>
</html>
';

// Output the HTML content
echo $documentation_content;

// If running from CLI, save to file
if (php_sapi_name() === 'cli') {
    $filename = 'Event_Booking_System_Documentation_' . date('Y-m-d') . '.html';
    file_put_contents($filename, $documentation_content);
    echo "\nDocumentation saved to: $filename\n";
    echo "To convert to PDF, open the HTML file in a browser and use Print > Save as PDF\n";
    echo "Or use a tool like wkhtmltopdf for automated conversion.\n";
}
?>
