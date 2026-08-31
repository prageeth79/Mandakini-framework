<?php
/**
 * Application Configuration
 * 
 * This file contains all configuration settings for the Mandakini application.
 * Update these settings according to your environment and requirements.
 */

/**
 * Environment Detection
 * The application can run in different environments: development, staging, production.
 * un comment the following lines to enable environment detection based on server variables.
// public/config.php

// Detect environment from server variable or default to development
$env = $_SERVER['APP_ENV'] ?? 'development';

if ($env === 'production') {
    // Production configuration (remote database, no debug)
    $config = [
        'userClass' => \app\models\User::class,
        'DEFAULT_APP_NAME' => 'Mandakini',
        'debug' => false,
        'db' => [
            'dsn' => 'mysql:host=prod-db.company.com;dbname=mandakini_prod',
            'username' => 'prod_user',
            'password' => $_SERVER['DB_PASSWORD'] ?? '',  // From environment
        ]
    ];
} elseif ($env === 'staging') {
    // Staging configuration (test server, limited debug)
    $config = [
        'userClass' => \app\models\User::class,
        'DEFAULT_APP_NAME' => 'Mandakini Staging',
        'debug' => false,
        'db' => [
            'dsn' => 'mysql:host=staging-db.company.com;dbname=mandakini_stage',
            'username' => 'stage_user',
            'password' => $_SERVER['DB_PASSWORD'] ?? '',
        ]
    ];
} else {
    // Development configuration (localhost, full debug)
    $config = [
        'userClass' => \app\models\User::class,
        'DEFAULT_APP_NAME' => 'Mandakini Dev',
        'debug' => true,
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=mandakini',
            'username' => 'root',
            'password' => '',
        ]
    ];
}

return $config;
 */

$config = [
    /**
     * User Model Class
     * Specifies which model class handles user authentication and session
     */
    'userClass' => \app\models\User::class,
    
    /**
     * Application Name
     * Used in views and email templates
     */
    'DEFAULT_APP_NAME' => 'Mandakini Framework 2026',
    
    /**
     * Debug Mode
     * Set to true during development to see detailed error messages
     * Set to false in production to hide sensitive error information
     */
    'debug' => true,
    'ENV' => 'production', // Change to 'production' in production environment, 'development' in development environment    
    
    /**
     * Database Configuration
     * Supports: MySQL, PostgreSQL, SQL Server, Oracle
     */
    'db' => [
        /**
         * Data Source Name (DSN)
         * Format: database:host=localhost;port=3306;dbname=database_name
         * 
         * Examples:
         * MySQL:      'mysql:host=localhost;port=3306;dbname=app'
         * PostgreSQL: 'pgsql:host=localhost;port=5432;dbname=app'
         * SQL Server: 'sqlsrv:Server=localhost;Database=app'
         * Oracle:     'oci:dbname=app'
         */
        'dsn' => 'mysql:host=localhost;port=3306;dbname=test',
        
        /**
         * Database Username
         * The user account for database connection
         */
        'username' => 'root',
        
        /**
         * Database Password
         * Leave empty for local development without password
         * Use strong passwords in production
         */
        'password' => '',        
    ]
];

return $config;