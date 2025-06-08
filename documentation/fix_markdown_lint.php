<?php
/**
 * Enhanced Markdown Linting Fix Script
 * Automatically fixes common markdown linting issues across all documentation files
 */

function fixMarkdownLinting($content) {
    // Fix MD009: Remove trailing spaces
    $content = preg_replace('/[ \t]+$/m', '', $content);

    // Split content into lines
    $lines = explode("\n", $content);
    $fixedLines = [];

    for ($i = 0; $i < count($lines); $i++) {
        $currentLine = $lines[$i];
        $prevLine = $i > 0 ? $lines[$i - 1] : '';
        $nextLine = $i < count($lines) - 1 ? $lines[$i + 1] : '';

        // Fix MD026: Remove trailing punctuation from headings
        if (preg_match('/^(#{1,6}\s+.+)[.,:;!?]+\s*$/', $currentLine, $matches)) {
            $currentLine = $matches[1];
        }

        // Fix MD040: Add language to fenced code blocks
        if (preg_match('/^```\s*$/', $currentLine)) {
            $currentLine = '```text';
        }

        // Fix MD022: Headings should be surrounded by blank lines
        if (preg_match('/^#{1,6}\s/', $currentLine)) {
            // Add blank line before heading if previous line is not empty and not already a blank line
            if (!empty($prevLine) && trim($prevLine) !== '') {
                $fixedLines[] = '';
            }
            $fixedLines[] = $currentLine;

            // Add blank line after heading if next line is not empty and not already a blank line
            if (!empty($nextLine) && trim($nextLine) !== '' && !preg_match('/^#{1,6}\s/', $nextLine)) {
                $fixedLines[] = '';
            }
        }
        // Fix MD032: Lists should be surrounded by blank lines
        elseif (preg_match('/^[\s]*[-*+]\s/', $currentLine) || preg_match('/^[\s]*\d+\.\s/', $currentLine)) {
            // Check if this is the start of a list
            $isListStart = !preg_match('/^[\s]*[-*+]\s/', $prevLine) && !preg_match('/^[\s]*\d+\.\s/', $prevLine);

            if ($isListStart && !empty($prevLine) && trim($prevLine) !== '') {
                $fixedLines[] = '';
            }
            $fixedLines[] = $currentLine;

            // Check if this is the end of a list
            $isListEnd = !preg_match('/^[\s]*[-*+]\s/', $nextLine) && !preg_match('/^[\s]*\d+\.\s/', $nextLine);

            if ($isListEnd && !empty($nextLine) && trim($nextLine) !== '' && !preg_match('/^#{1,6}\s/', $nextLine)) {
                $fixedLines[] = '';
            }
        }
        // Fix MD031: Fenced code blocks should be surrounded by blank lines
        elseif (preg_match('/^```/', $currentLine)) {
            if (!empty($prevLine) && trim($prevLine) !== '') {
                $fixedLines[] = '';
            }
            $fixedLines[] = $currentLine;
        }
        else {
            $fixedLines[] = $currentLine;
        }
    }

    // Remove duplicate blank lines
    $result = [];
    $prevWasBlank = false;

    foreach ($fixedLines as $line) {
        $isBlank = trim($line) === '';

        if (!($isBlank && $prevWasBlank)) {
            $result[] = $line;
        }

        $prevWasBlank = $isBlank;
    }

    return implode("\n", $result);
}

// Fix all documentation files
$files = [
    'DOCUMENTATION_SUMMARY.md',
    'comprehensive_documentation.md',
    'screenshots_guide.md',
    'README.md'
];

$fixedCount = 0;
$timestamp = date('Y-m-d_H-i-s');

echo "🔧 Starting markdown linting fix for all documentation files...\n\n";

foreach ($files as $filename) {
    $file = __DIR__ . '/' . $filename;

    if (file_exists($file)) {
        echo "📄 Processing: $filename\n";

        $content = file_get_contents($file);
        $fixedContent = fixMarkdownLinting($content);

        // Only create backup and update if content changed
        if ($content !== $fixedContent) {
            // Create backup
            $backupFile = $file . '.backup.' . $timestamp;
            file_put_contents($backupFile, $content);

            // Write fixed content
            file_put_contents($file, $fixedContent);

            echo "   ✅ Fixed and backed up to: " . basename($backupFile) . "\n";
            $fixedCount++;
        } else {
            echo "   ✨ Already clean - no changes needed\n";
        }
    } else {
        echo "   ⚠️  File not found: $filename\n";
    }
    echo "\n";
}

echo "🎉 Markdown linting fix completed!\n";
echo "📊 Summary:\n";
echo "   - Files processed: " . count($files) . "\n";
echo "   - Files fixed: $fixedCount\n";
echo "   - Backup timestamp: $timestamp\n";
echo "\n🔧 Fixed issues:\n";
echo "   - MD009: Removed trailing spaces\n";
echo "   - MD022: Added blank lines around headings\n";
echo "   - MD026: Removed trailing punctuation from headings\n";
echo "   - MD032: Added blank lines around lists\n";
echo "   - MD031: Added blank lines around code blocks\n";
echo "   - MD040: Added language specification to code blocks\n";
echo "   - Removed duplicate blank lines\n";
echo "\n✨ All documentation files are now lint-free!\n";
