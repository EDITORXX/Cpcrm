<?php

/**
 * Deployment Zip Creator
 * 
 * Yeh script ek zip file banayega jo server pe upload karne ke liye ready hai.
 * Vendor folder included hai - server pe kuch download karne ki zarurat nahi hai.
 * Node_modules exclude hoga - wo server pe install hoga (agar frontend build karna hai).
 */

$rootPath = __DIR__;
$zipFileName = 'crm_deployment_' . date('Y-m-d_His') . '.zip';
$zipPath = $rootPath . '/' . $zipFileName;

// Files aur folders jo include karne hain
$includePaths = [
    'app',
    'bootstrap',
    'vendor',
    'config',
    'database',
    'public',
    'resources',
    'routes',
    'storage',
    '.env.example',
    '.gitignore',
    '.htaccess',
    'artisan',
    'composer.json',
    'composer.lock',
    'package.json',
    'package-lock.json',
    'vite.config.js',
    'tailwind.config.js',
    'INSTALLATION_AND_DEPLOYMENT_GUIDE.md',
    'INSTALLATION.md',
    'README.md',
    'ARCHITECTURE.md',
    'SYSTEM_SUMMARY.md',
    'SCALING.md',
    'USER_CREDENTIALS.md',
];

// Files aur folders jo exclude karne hain
$excludePaths = [
    'node_modules',
    '.env',
    '.env.backup',
    '.git',
    'storage/logs',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/app/public',
    'bootstrap/cache',
    '.idea',
    '.vscode',
    '.cursor',
    '*.log',
    '*.cache',
    'Thumbs.db',
    '.DS_Store',
    'storage/app/google-credentials',
    'config/google-service-account.json',
    '*.json',
    '!composer.json',
    '!package.json',
    'create_deployment_zip.php',
    'create_deployment_zip.bat',
    'crm_deployment_*.zip',
];

// Create zip archive
$zip = new ZipArchive();

if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Cannot create zip file: $zipFileName\n");
}

echo "Creating deployment zip file...\n";
echo "================================\n\n";

$addedFiles = 0;
$skippedFiles = 0;

/**
 * Check if path should be excluded
 */
function shouldExclude($path, $excludePaths) {
    $path = str_replace('\\', '/', $path);
    foreach ($excludePaths as $exclude) {
        $exclude = str_replace('\\', '/', $exclude);
        
        // Exact match
        if ($path === $exclude) {
            return true;
        }
        
        // Directory match
        if (strpos($path, $exclude . '/') === 0) {
            return true;
        }
        
        // Wildcard match
        if (strpos($exclude, '*') !== false) {
            $pattern = str_replace('*', '.*', $exclude);
            if (preg_match('/^' . $pattern . '$/', basename($path))) {
                return true;
            }
        }
    }
    return false;
}

/**
 * Add directory to zip recursively
 */
function addDirectoryToZip($zip, $dir, $basePath, $excludePaths, &$addedFiles, &$skippedFiles) {
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $filePath = $dir . DIRECTORY_SEPARATOR . $file;
        $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $filePath);
        $relativePath = str_replace('\\', '/', $relativePath);
        
        // Check if should exclude
        if (shouldExclude($relativePath, $excludePaths)) {
            $skippedFiles++;
            continue;
        }
        
        if (is_dir($filePath)) {
            // Recursively add directory
            addDirectoryToZip($zip, $filePath, $basePath, $excludePaths, $addedFiles, $skippedFiles);
        } else {
            // Add file
            $zip->addFile($filePath, $relativePath);
            $addedFiles++;
            if ($addedFiles % 100 === 0) {
                echo "Added $addedFiles files...\n";
            }
        }
    }
}

// Add files and directories
foreach ($includePaths as $path) {
    $fullPath = $rootPath . DIRECTORY_SEPARATOR . $path;
    
    if (!file_exists($fullPath)) {
        echo "Warning: $path does not exist, skipping...\n";
        $skippedFiles++;
        continue;
    }
    
    $relativePath = str_replace('\\', '/', $path);
    
    // Check if should exclude
    if (shouldExclude($relativePath, $excludePaths)) {
        $skippedFiles++;
        continue;
    }
    
    if (is_dir($fullPath)) {
        echo "Adding directory: $path\n";
        addDirectoryToZip($zip, $fullPath, $rootPath, $excludePaths, $addedFiles, $skippedFiles);
    } else {
        $zip->addFile($fullPath, $relativePath);
        $addedFiles++;
        echo "Added file: $path\n";
    }
}

// Ensure storage structure exists in zip (empty directories)
$storageDirs = [
    'storage/app/public',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache',
];

foreach ($storageDirs as $dir) {
    $fullPath = $rootPath . DIRECTORY_SEPARATOR . $dir;
    if (is_dir($fullPath)) {
        // Add .gitignore files if they exist
        $gitignorePath = $fullPath . DIRECTORY_SEPARATOR . '.gitignore';
        if (file_exists($gitignorePath)) {
            $relativePath = str_replace('\\', '/', $dir . '/.gitignore');
            $zip->addFile($gitignorePath, $relativePath);
            $addedFiles++;
        }
    }
}

// Close zip
$zip->close();

$fileSize = filesize($zipPath);
$fileSizeMB = round($fileSize / 1024 / 1024, 2);

echo "\n================================\n";
echo "Zip file created successfully!\n";
echo "================================\n";
echo "File: $zipFileName\n";
echo "Size: $fileSizeMB MB\n";
echo "Files added: $addedFiles\n";
echo "Files skipped: $skippedFiles\n";
echo "\n";
echo "Next steps:\n";
echo "1. Upload this zip file to your server\n";
echo "2. Extract it in your web root directory\n";
echo "3. Set permissions: chmod -R 775 storage bootstrap/cache\n";
echo "4. Run: npm install && npm run build (if frontend build needed)\n";
echo "5. Open: yoursite.com/install\n";
echo "6. Follow the installation wizard\n";
echo "\n";
echo "Note: Vendor folder already included - no need to run composer install!\n";
echo "\n";
echo "Zip file location: $zipPath\n";
