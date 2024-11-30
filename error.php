<?php

include 'create_log_file.php';


$logFile = 'php-error.log';

// Check if the log file exists
if (!file_exists($logFile)) {
    // Create the log file
    $handle = fopen($logFile, 'w');
    if ($handle) {
        fclose($handle);

        // Set appropriate permissions
        chmod($logFile, 0644);

        // Change the ownership to the user and group running the web server (e.g., www-data for Apache on Debian/Ubuntu)
        chown($logFile, 'www-data');
        chgrp($logFile, 'www-data');

        echo "Log file created at $logFile with permissions set to 644 and ownership set to www-data.";
    } else {
        echo "Failed to create the log file at $logFile.";
    }
} else {
    echo "Log file already exists at $logFile.";
}
?>
