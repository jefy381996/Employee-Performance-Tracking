<?php
/**
 * Script to create a proper WordPress plugin ZIP file
 * Run this script to create a WordPress-compatible plugin package
 */

// Configuration
$plugin_name = 'student-result-management';
$version = '2.0';
$zip_filename = $plugin_name . '-v' . $version . '.zip';

// Create temporary directory
$temp_dir = 'temp-' . $plugin_name;
if (!is_dir($temp_dir)) {
    mkdir($temp_dir, 0755, true);
}

// Copy plugin files to temp directory
$files_to_copy = [
    'student-result-management.php',
    'uninstall.php',
    'install-tables.php',
    'license-key-generator.php',
    'README.md',
    'PRIVACY_POLICY.txt',
    'TERMS_OF_SERVICE.txt',
    'CHANGELOG.txt',
    'INSTALLATION.txt',
    'SUPPORT.txt'
];

$directories_to_copy = [
    'includes',
    'assets'
];

echo "Creating WordPress plugin package...\n";

// Copy main files
foreach ($files_to_copy as $file) {
    if (file_exists($plugin_name . '/' . $file)) {
        copy($plugin_name . '/' . $file, $temp_dir . '/' . $file);
        echo "Copied: $file\n";
    } else {
        echo "Warning: $file not found\n";
    }
}

// Copy directories
foreach ($directories_to_copy as $dir) {
    if (is_dir($plugin_name . '/' . $dir)) {
        // Create directory
        if (!is_dir($temp_dir . '/' . $dir)) {
            mkdir($temp_dir . '/' . $dir, 0755, true);
        }
        
        // Copy directory contents recursively
        copyDirectory($plugin_name . '/' . $dir, $temp_dir . '/' . $dir);
        echo "Copied directory: $dir\n";
    } else {
        echo "Warning: Directory $dir not found\n";
    }
}

// Create ZIP file
$zip = new ZipArchive();
if ($zip->open($zip_filename, ZipArchive::CREATE) === TRUE) {
    // Add files to ZIP
    addFolderToZip($zip, $temp_dir, '');
    $zip->close();
    echo "Created ZIP file: $zip_filename\n";
} else {
    echo "Error: Could not create ZIP file\n";
}

// Clean up temp directory
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return;
    }
    
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }
    return rmdir($dir);
}

function copyDirectory($src, $dst) {
    $dir = opendir($src);
    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }
    
    while (($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                copyDirectory($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function addFolderToZip($zip, $folder, $parent_folder = '') {
    $files = scandir($folder);
    
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') {
            continue;
        }
        
        $file_path = $folder . '/' . $file;
        $zip_path = $parent_folder . '/' . $file;
        
        if (is_dir($file_path)) {
            addFolderToZip($zip, $file_path, $zip_path);
        } else {
            $zip->addFile($file_path, $zip_path);
        }
    }
}

// Clean up
deleteDirectory($temp_dir);
echo "Cleanup completed.\n";
echo "Plugin package ready: $zip_filename\n";
echo "You can now upload this ZIP file to WordPress.\n";
?>