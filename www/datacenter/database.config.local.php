<?php
/**
 * Local Docker Development Database Configuration
 * This file is for local development only - NOT for production!
 */

// Database credentials for Docker environment
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'ipnz_live');
define('DB_USER', getenv('DB_USER') ?: 'ipnz');
define('DB_PASS', getenv('DB_PASS') ?: 'ipnz_dev_password');
