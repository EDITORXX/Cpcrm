<?php
// Server-side icon generator using GD library
// This will create icon files directly on the server

header('Content-Type: text/html; charset=utf-8');

// Check if GD library is available
if (!extension_loaded('gd')) {
    die('GD library is not installed. Please install php-gd extension.');
}

function createIcon($size, $filename) {
    // Create image
    $img = imagecreatetruecolor($size, $size);
    
    // Colors
    $green = imagecolorallocate($img, 32, 90, 68); // #205A44
    $white = imagecolorallocate($img, 255, 255, 255);
    
    // Fill background
    imagefilledrectangle($img, 0, 0, $size, $size, $green);
    
    // Calculate font sizes
    $baseFontSize = (int)($size * 0.31);
    $crmFontSize = (int)($size * 0.21);
    $lineHeight = max(2, (int)($size * 0.02));
    
    // Draw "BASE" text
    $font = 5; // Built-in font (you can use imageloadfont for custom fonts)
    $baseText = 'BASE';
    $crmText = 'CRM';
    
    // Calculate text positions
    $baseX = ($size - imagefontwidth($font) * strlen($baseText)) / 2;
    $baseY = $size * 0.35;
    $crmX = ($size - imagefontwidth($font) * strlen($crmText)) / 2;
    $crmY = $size * 0.65;
    
    // Draw text using built-in font (simple approach)
    imagestring($img, $font, $baseX, $baseY, $baseText, $white);
    
    // Draw line
    $lineWidth = $size * 0.6;
    $lineX = ($size - $lineWidth) / 2;
    $lineY = $size * 0.52;
    imagefilledrectangle($img, $lineX, $lineY, $lineX + $lineWidth, $lineY + $lineHeight, $white);
    
    // Draw "CRM" text
    imagestring($img, $font, $crmX, $crmY, $crmText, $white);
    
    // Save image
    imagepng($img, $filename);
    imagedestroy($img);
    
    return file_exists($filename);
}

$results = [];

// Create icon-192.png
if (createIcon(192, __DIR__ . '/icon-192.png')) {
    $results[] = '✅ icon-192.png created successfully';
} else {
    $results[] = '❌ Failed to create icon-192.png';
}

// Create icon-512.png
if (createIcon(512, __DIR__ . '/icon-512.png')) {
    $results[] = '✅ icon-512.png created successfully';
} else {
    $results[] = '❌ Failed to create icon-512.png';
}

// Create favicon.ico (simple 32x32 version)
if (createIcon(32, __DIR__ . '/favicon-temp.png')) {
    // Convert PNG to ICO (simplified - just rename for now)
    // For proper ICO, you'd need a library, but PNG works for most browsers
    $results[] = '✅ favicon created (as PNG)';
} else {
    $results[] = '❌ Failed to create favicon';
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Icon Generator - Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #205A44;
        }
        .result {
            padding: 10px;
            margin: 10px 0;
            border-radius: 6px;
            background: #f9fafb;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background: #205A44;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
        a:hover {
            background: #063A1C;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Icon Generation Results</h1>
        <?php foreach ($results as $result): ?>
            <div class="result <?php echo strpos($result, '✅') !== false ? 'success' : 'error'; ?>">
                <?php echo $result; ?>
            </div>
        <?php endforeach; ?>
        
        <a href="/pwa-test">Go to PWA Test Page</a>
        <a href="/" style="margin-left: 10px;">Go to Home Page</a>
    </div>
</body>
</html>
