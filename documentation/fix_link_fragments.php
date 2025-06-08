<?php
/**
 * Link Fragment Fix Script
 * Fixes MD051 issues by generating proper anchor links for table of contents
 */

function generateAnchor($heading) {
    // Remove markdown heading markers
    $text = preg_replace('/^#+\s*/', '', $heading);
    
    // Convert to lowercase
    $text = strtolower($text);
    
    // Replace spaces and special characters with hyphens
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    
    // Remove leading/trailing hyphens
    $text = trim($text, '-');
    
    return $text;
}

function fixLinkFragments($content) {
    $lines = explode("\n", $content);
    $headings = [];
    $fixedLines = [];
    
    // First pass: collect all headings and their anchors
    foreach ($lines as $line) {
        if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $matches)) {
            $level = strlen($matches[1]);
            $text = trim($matches[2]);
            $anchor = generateAnchor($text);
            $headings[] = [
                'level' => $level,
                'text' => $text,
                'anchor' => $anchor,
                'original' => $line
            ];
        }
    }
    
    // Second pass: fix the content
    $inToc = false;
    foreach ($lines as $line) {
        // Detect table of contents section
        if (preg_match('/^##\s+Table of Contents/i', $line)) {
            $inToc = true;
            $fixedLines[] = $line;
            continue;
        }
        
        // End of TOC when we hit another section
        if ($inToc && preg_match('/^#{1,2}\s+(?!Table of Contents)/i', $line)) {
            $inToc = false;
        }
        
        // Fix TOC links
        if ($inToc && preg_match('/^\d+\.\s+\[([^\]]+)\]\(#[^)]+\)/', $line, $matches)) {
            $linkText = $matches[1];
            
            // Find matching heading
            foreach ($headings as $heading) {
                if ($heading['level'] == 2 && stripos($heading['text'], $linkText) !== false) {
                    $number = preg_match('/^(\d+)\./', $line, $numMatch) ? $numMatch[1] : '';
                    $fixedLine = "$number. [$linkText](#{$heading['anchor']})";
                    $fixedLines[] = $fixedLine;
                    continue 2;
                }
            }
            // If no match found, keep original
            $fixedLines[] = $line;
        } else {
            $fixedLines[] = $line;
        }
    }
    
    return implode("\n", $fixedLines);
}

// Fix the comprehensive documentation file
$file = __DIR__ . '/comprehensive_documentation.md';

if (file_exists($file)) {
    echo "🔧 Fixing link fragments in comprehensive_documentation.md...\n";
    
    $content = file_get_contents($file);
    $fixedContent = fixLinkFragments($content);
    
    // Only update if content changed
    if ($content !== $fixedContent) {
        // Create backup
        $backupFile = $file . '.linkfix.backup.' . date('Y-m-d_H-i-s');
        file_put_contents($backupFile, $content);
        
        // Write fixed content
        file_put_contents($file, $fixedContent);
        
        echo "✅ Link fragments fixed!\n";
        echo "📁 Backup created: " . basename($backupFile) . "\n";
        echo "🔗 Fixed MD051: Link fragments now match actual headings\n";
    } else {
        echo "✨ No changes needed - link fragments already correct\n";
    }
} else {
    echo "❌ File not found: $file\n";
}
?>
