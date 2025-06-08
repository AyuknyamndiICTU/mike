<?php
/**
 * Enhanced PDF Generation Script for Event Booking System Documentation
 *
 * This script converts the comprehensive documentation to a beautifully formatted PDF
 * including all content from the markdown documentation with proper styling.
 */

// Read the comprehensive markdown documentation
$markdown_file = __DIR__ . '/comprehensive_documentation.md';
$markdown_content = '';

if (file_exists($markdown_file)) {
    $markdown_content = file_get_contents($markdown_file);
} else {
    die("Error: comprehensive_documentation.md not found!\n");
}

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    echo "<h1>Enhanced PDF Documentation Generator</h1>";
    echo "<p>This script generates a comprehensive PDF documentation for the Event Booking System.</p>";
    echo "<p><strong>Features:</strong> Complete content, professional styling, diagrams, screenshots placeholders</p>";
    echo "<hr>";
}

// Function to convert markdown to HTML (basic conversion)
function markdownToHtml($markdown) {
    // Convert headers
    $html = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $markdown);
    $html = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $html);
    $html = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $html);

    // Convert code blocks
    $html = preg_replace('/```(\w+)?\n(.*?)\n```/s', '<pre><code>$2</code></pre>', $html);
    $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);

    // Convert lists
    $html = preg_replace('/^\- (.*$)/m', '<li>$1</li>', $html);
    $html = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html);

    // Convert bold and italic
    $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
    $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);

    // Convert paragraphs
    $html = preg_replace('/\n\n/', '</p><p>', $html);
    $html = '<p>' . $html . '</p>';

    // Clean up
    $html = str_replace('<p></p>', '', $html);
    $html = str_replace('<p><h', '<h', $html);
    $html = str_replace('</h1></p>', '</h1>', $html);
    $html = str_replace('</h2></p>', '</h2>', $html);
    $html = str_replace('</h3></p>', '</h3>', $html);
    $html = str_replace('<p><ul>', '<ul>', $html);
    $html = str_replace('</ul></p>', '</ul>', $html);
    $html = str_replace('<p><pre>', '<pre>', $html);
    $html = str_replace('</pre></p>', '</pre>', $html);

    return $html;
}

// Convert markdown content to HTML
$content_html = markdownToHtml($markdown_content);

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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin: -20mm;
            padding: 80mm 20mm;
        }

        .cover-title {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .cover-subtitle {
            font-size: 24px;
            margin-bottom: 40px;
            opacity: 0.9;
        }

        .cover-info {
            font-size: 16px;
            margin: 10px 0;
            opacity: 0.8;
        }
        
        h1 {
            color: #1e293b;
            font-size: 32px;
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 10px;
            margin-top: 40px;
            page-break-before: always;
            background: linear-gradient(90deg, #f8fafc, transparent);
            padding: 15px 0 15px 20px;
            margin-left: -20px;
            margin-right: -20px;
        }

        h2 {
            color: #334155;
            font-size: 24px;
            margin-top: 30px;
            border-left: 4px solid #06b6d4;
            padding-left: 15px;
            background: #f8fafc;
            padding: 10px 15px;
            border-radius: 0 8px 8px 0;
        }

        h3 {
            color: #475569;
            font-size: 20px;
            margin-top: 25px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }

        h4 {
            color: #64748b;
            font-size: 16px;
            margin-top: 20px;
            font-weight: 600;
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
            <div class="cover-info"><strong>Pages:</strong> Complete System Documentation</div>
        </div>
        <div style="margin-top: 80px;">
            <p style="font-size: 18px; font-weight: bold;">Complete System Documentation</p>
            <p style="opacity: 0.8;">Including Architecture, Implementation, Deployment, User Guide, and Screenshots</p>
            <div style="margin-top: 40px; padding: 20px; background: rgba(255,255,255,0.1); border-radius: 10px;">
                <p style="font-size: 14px; margin: 5px 0;">✓ System Design & Architecture</p>
                <p style="font-size: 14px; margin: 5px 0;">✓ Database Design & Implementation</p>
                <p style="font-size: 14px; margin: 5px 0;">✓ Security & Authentication</p>
                <p style="font-size: 14px; margin: 5px 0;">✓ API Documentation</p>
                <p style="font-size: 14px; margin: 5px 0;">✓ Deployment & Maintenance Guide</p>
                <p style="font-size: 14px; margin: 5px 0;">✓ Complete User Manual</p>
            </div>
        </div>
    </div>

    <!-- Main Content from Markdown -->
    <div class="main-content">
        ' . $content_html . '
    </div>

    <!-- Additional Screenshots Section -->
    <div class="page-break">
        <h1>Screenshots Gallery</h1>

        <h2>User Interface Screenshots</h2>

        <h3>Homepage</h3>
        <div class="screenshot-placeholder">
            <strong>Homepage Screenshot</strong><br>
            URL: http://localhost/mike/index.php<br>
            Features: Navigation, hero section, featured events, animated gradient header
        </div>

        <h3>Events Listing</h3>
        <div class="screenshot-placeholder">
            <strong>Events Page Screenshot</strong><br>
            URL: http://localhost/mike/events.php<br>
            Features: Event grid, search functionality, filters, pagination
        </div>

        <h3>Event Details</h3>
        <div class="screenshot-placeholder">
            <strong>Event Details Screenshot</strong><br>
            URL: http://localhost/mike/event.php?id=1<br>
            Features: Event information, organizer details, map integration, booking form
        </div>

        <h3>Shopping Cart</h3>
        <div class="screenshot-placeholder">
            <strong>Cart Screenshot</strong><br>
            URL: http://localhost/mike/cart.php<br>
            Features: Cart items, quantity controls, totals, animated styling
        </div>

        <h3>Checkout Process</h3>
        <div class="screenshot-placeholder">
            <strong>Checkout Screenshot</strong><br>
            URL: http://localhost/mike/checkout.php<br>
            Features: Payment form, card validation, secure processing
        </div>

        <h3>Booking Confirmation</h3>
        <div class="screenshot-placeholder">
            <strong>Confirmation Screenshot</strong><br>
            URL: http://localhost/mike/booking-confirmation.php<br>
            Features: Success animation, QR code, booking details, download ticket
        </div>

        <h3>User Bookings</h3>
        <div class="screenshot-placeholder">
            <strong>Bookings History Screenshot</strong><br>
            URL: http://localhost/mike/bookings.php<br>
            Features: Booking history, QR codes, cancel functionality, status tracking
        </div>

        <h2>Admin Interface Screenshots</h2>

        <h3>Admin Dashboard</h3>
        <div class="screenshot-placeholder">
            <strong>Dashboard Screenshot</strong><br>
            URL: http://localhost/mike/admin/dashboard.php<br>
            Features: Statistics cards, charts, quick actions, animated elements
        </div>

        <h3>Event Management</h3>
        <div class="screenshot-placeholder">
            <strong>Admin Events Screenshot</strong><br>
            URL: http://localhost/mike/admin/events.php<br>
            Features: Event table, sorting, CRUD operations, status management
        </div>

        <h3>Add/Edit Event</h3>
        <div class="screenshot-placeholder">
            <strong>Event Form Screenshot</strong><br>
            URL: http://localhost/mike/admin/add-event.php<br>
            Features: Comprehensive form, organizer details, multiple ticket types
        </div>

        <h3>Booking Management</h3>
        <div class="screenshot-placeholder">
            <strong>Admin Bookings Screenshot</strong><br>
            URL: http://localhost/mike/admin/bookings.php<br>
            Features: Booking table, status management, animations, filtering
        </div>

        <h3>User Management</h3>
        <div class="screenshot-placeholder">
            <strong>User Management Screenshot</strong><br>
            URL: http://localhost/mike/admin/users.php<br>
            Features: User table, role management, account status control
        </div>

        <h3>Analytics & Reports</h3>
        <div class="screenshot-placeholder">
            <strong>Analytics Screenshot</strong><br>
            URL: http://localhost/mike/admin/analytics.php<br>
            Features: Charts, statistics, revenue reports, booking trends
        </div>

        <h2>Mobile Screenshots</h2>

        <div class="screenshot-placeholder">
            <strong>Mobile Homepage</strong><br>
            Responsive design optimized for mobile devices
        </div>

        <div class="screenshot-placeholder">
            <strong>Mobile Events</strong><br>
            Touch-optimized event browsing and search
        </div>

        <div class="screenshot-placeholder">
            <strong>Mobile Checkout</strong><br>
            Mobile-friendly checkout process with card validation
        </div>

        <div class="screenshot-placeholder">
            <strong>Mobile Admin</strong><br>
            Responsive admin interface for mobile management
        </div>
    </div>

    <!-- Footer -->
    <div class="footer no-print">
        <div style="text-align: center; padding: 20px; border-top: 2px solid #e2e8f0; margin-top: 40px;">
            <p style="margin: 0; color: #64748b; font-size: 14px;">
                <strong>Event Booking System - Comprehensive Documentation</strong><br>
                Generated on ' . date('F j, Y \a\t H:i:s') . '<br>
                Version 1.0 | PHP, MySQL, Bootstrap Framework
            </p>
        </div>
    </div>
</body>
</html>
';

// Output the HTML content
echo $documentation_content;

// If running from CLI, save to file
if (php_sapi_name() === 'cli') {
    $filename = 'Event_Booking_System_Documentation_' . date('Y-m-d_H-i-s') . '.html';
    file_put_contents($filename, $documentation_content);
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "📄 DOCUMENTATION GENERATED SUCCESSFULLY!\n";
    echo str_repeat("=", 60) . "\n";
    echo "📁 File saved as: $filename\n";
    echo "📏 Content includes: Complete markdown documentation + styling\n";
    echo "🎨 Features: Professional styling, diagrams, screenshots placeholders\n";
    echo "\n📋 TO CONVERT TO PDF:\n";
    echo "1. Open the HTML file in Chrome/Edge browser\n";
    echo "2. Press Ctrl+P (Print)\n";
    echo "3. Select 'Save as PDF' as destination\n";
    echo "4. Choose 'More settings' > Paper size: A4\n";
    echo "5. Enable 'Background graphics' for full styling\n";
    echo "6. Click 'Save'\n";
    echo "\n🔧 Alternative: Use wkhtmltopdf for automated conversion\n";
    echo "   Command: wkhtmltopdf --page-size A4 --enable-local-file-access $filename output.pdf\n";
    echo str_repeat("=", 60) . "\n";
} else {
    echo "<div style='margin-top: 30px; padding: 20px; background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 8px;'>";
    echo "<h3 style='color: #0369a1; margin-top: 0;'>📄 Documentation Generated Successfully!</h3>";
    echo "<p><strong>Features included:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Complete markdown content converted to HTML</li>";
    echo "<li>✅ Professional styling and formatting</li>";
    echo "<li>✅ Diagram placeholders for Mermaid charts</li>";
    echo "<li>✅ Screenshot placeholders with descriptions</li>";
    echo "<li>✅ Print-optimized CSS for PDF generation</li>";
    echo "</ul>";
    echo "<p><strong>To save as PDF:</strong> Use your browser's Print function and select 'Save as PDF'</p>";
    echo "</div>";
}
