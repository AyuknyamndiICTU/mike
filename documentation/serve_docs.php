<?php
/**
 * Simple Documentation Server
 * Serves the documentation files with proper styling and navigation
 */

// Get the requested file
$file = $_GET['file'] ?? 'index';
$base_path = __DIR__;

// Security: Only allow specific files
$allowed_files = [
    'index' => 'DOCUMENTATION_SUMMARY.md',
    'comprehensive' => 'comprehensive_documentation.md',
    'screenshots' => 'screenshots_guide.md',
    'readme' => 'README.md'
];

if (!isset($allowed_files[$file])) {
    $file = 'index';
}

$file_path = $base_path . '/' . $allowed_files[$file];

// Function to convert markdown to HTML (basic)
function markdownToHtml($markdown) {
    // Convert headers
    $html = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $markdown);
    $html = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $html);
    $html = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $html);
    
    // Convert code blocks
    $html = preg_replace('/```(\w+)?\n(.*?)\n```/s', '<pre><code class="language-$1">$2</code></pre>', $html);
    $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);
    
    // Convert lists
    $html = preg_replace('/^\- (.*$)/m', '<li>$1</li>', $html);
    $html = preg_replace('/^(\d+)\. (.*$)/m', '<li>$2</li>', $html);
    
    // Convert bold and italic
    $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
    $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);
    
    // Convert links
    $html = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $html);
    
    // Convert paragraphs
    $html = preg_replace('/\n\n/', '</p><p>', $html);
    $html = '<p>' . $html . '</p>';
    
    // Clean up
    $html = str_replace('<p></p>', '', $html);
    $html = str_replace('<p><h', '<h', $html);
    $html = str_replace('</h1></p>', '</h1>', $html);
    $html = str_replace('</h2></p>', '</h2>', $html);
    $html = str_replace('</h3></p>', '</h3>', $html);
    $html = str_replace('<p><pre>', '<pre>', $html);
    $html = str_replace('</pre></p>', '</pre>', $html);
    
    // Fix lists
    $html = preg_replace('/<p>(<li>.*?<\/li>)<\/p>/s', '<ul>$1</ul>', $html);
    $html = str_replace('<p><ul>', '<ul>', $html);
    $html = str_replace('</ul></p>', '</ul>', $html);
    
    return $html;
}

// Read and convert the file
$content = '';
$title = 'Documentation';

if (file_exists($file_path)) {
    $markdown = file_get_contents($file_path);
    $content = markdownToHtml($markdown);
    
    // Extract title from first header
    if (preg_match('/^# (.+)$/m', $markdown, $matches)) {
        $title = $matches[1];
    }
} else {
    $content = '<h1>File Not Found</h1><p>The requested documentation file could not be found.</p>';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - Event Booking System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8fafc;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .header p {
            text-align: center;
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .nav {
            background: white;
            padding: 1rem 0;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }
        
        .nav a {
            text-decoration: none;
            color: #4a5568;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.2s;
            font-weight: 500;
        }
        
        .nav a:hover, .nav a.active {
            background: #667eea;
            color: white;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            min-height: calc(100vh - 200px);
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        
        .content h1 {
            color: #2d3748;
            font-size: 2.5rem;
            margin: 2rem 0 1rem 0;
            border-bottom: 3px solid #667eea;
            padding-bottom: 0.5rem;
        }
        
        .content h2 {
            color: #4a5568;
            font-size: 2rem;
            margin: 2rem 0 1rem 0;
            border-left: 4px solid #667eea;
            padding-left: 1rem;
        }
        
        .content h3 {
            color: #718096;
            font-size: 1.5rem;
            margin: 1.5rem 0 0.5rem 0;
        }
        
        .content p {
            margin: 1rem 0;
            line-height: 1.8;
        }
        
        .content ul, .content ol {
            margin: 1rem 0;
            padding-left: 2rem;
        }
        
        .content li {
            margin: 0.5rem 0;
        }
        
        .content code {
            background: #f7fafc;
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            border: 1px solid #e2e8f0;
        }
        
        .content pre {
            background: #2d3748;
            color: #e2e8f0;
            padding: 1.5rem;
            border-radius: 8px;
            overflow-x: auto;
            margin: 1.5rem 0;
            font-family: 'Courier New', monospace;
        }
        
        .content pre code {
            background: none;
            border: none;
            padding: 0;
            color: inherit;
        }
        
        .content table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .content th, .content td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .content th {
            background: #f7fafc;
            font-weight: 600;
            color: #4a5568;
        }
        
        .content a {
            color: #667eea;
            text-decoration: none;
        }
        
        .content a:hover {
            text-decoration: underline;
        }
        
        .footer {
            background: #2d3748;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 2rem;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .nav-container {
                padding: 0 1rem;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Event Booking System</h1>
        <p>Comprehensive Documentation Portal</p>
    </div>
    
    <nav class="nav">
        <div class="nav-container">
            <a href="?file=index" <?php echo $file === 'index' ? 'class="active"' : ''; ?>>📋 Summary</a>
            <a href="?file=comprehensive" <?php echo $file === 'comprehensive' ? 'class="active"' : ''; ?>>📚 Full Documentation</a>
            <a href="?file=screenshots" <?php echo $file === 'screenshots' ? 'class="active"' : ''; ?>>📸 Screenshots Guide</a>
            <a href="?file=readme" <?php echo $file === 'readme' ? 'class="active"' : ''; ?>>📖 README</a>
            <a href="../" target="_blank">🏠 Back to System</a>
            <a href="generate_pdf.php" target="_blank">📄 Generate PDF</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="content">
            <?php echo $content; ?>
        </div>
    </div>
    
    <div class="footer">
        <p>&copy; 2024 Event Booking System Documentation | Generated with ❤️</p>
    </div>
</body>
</html>
