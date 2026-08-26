<?php
/**
 * Application Configuration
 * 
 * This file contains all configuration settings for the Mandakini application.
 * Update these settings according to your environment and requirements.
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
    'appName' => 'Mandakini',
    
    /**
     * Debug Mode
     * Set to true during development to see detailed error messages
     * Set to false in production to hide sensitive error information
     */
    'debug' => true,
    
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