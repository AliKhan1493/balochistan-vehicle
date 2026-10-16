<?php
/**
 * Global Configuration & Security Helper
 * Balochistan Vehicle Verification System
 */

// Prevent directory listing and secure error handling
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

define('ROOT_PATH', __DIR__);
define('DATA_PATH', ROOT_PATH . '/data');
define('CONFIG_FILE', DATA_PATH . '/config.json');
define('SESSION_FILE', DATA_PATH . '/session.json');
define('COOKIE_FILE', DATA_PATH . '/cookies.txt');

// Ensure data directory exists and is protected
if (!is_dir(DATA_PATH)) {
    mkdir(DATA_PATH, 0750, true);
}

// Ensure .htaccess inside data folder to block web direct downloads
$htaccess = DATA_PATH . '/.htaccess';
if (!file_exists($htaccess)) {
    file_put_contents($htaccess, "Require all denied\nDeny from all\n");
}

// Default configuration template
function getDefaultConfig() {
    return [
        'upstream_url'        => getenv('UPSTREAM_URL') ?: 'https://etanb.com',
        'upstream_email'      => getenv('UPSTREAM_EMAIL') ?: '',
        'upstream_password'   => getenv('UPSTREAM_PASSWORD') ?: '',
        'user_agent'          => getenv('USER_AGENT') ?: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36',
        'manual_cookies'      => getenv('MANUAL_COOKIES') ?: '',
        'auto_relogin'        => true,
        'rate_limit_per_min'  => 30,
        'app_title'           => 'Balochistan Excise Vehicle Verification',
    ];
}

// Load config
function loadConfig() {
    $default = getDefaultConfig();
    $data = [];
    if (file_exists(CONFIG_FILE)) {
        $raw = file_get_contents(CONFIG_FILE);
        $parsed = json_decode($raw, true);
        if (is_array($parsed)) {
            $data = $parsed;
        }
    }
    $merged = array_merge($default, $data);
    if (getenv('UPSTREAM_EMAIL')) $merged['upstream_email'] = getenv('UPSTREAM_EMAIL');
    if (getenv('UPSTREAM_PASSWORD')) $merged['upstream_password'] = getenv('UPSTREAM_PASSWORD');
    if (getenv('UPSTREAM_URL')) $merged['upstream_url'] = getenv('UPSTREAM_URL');
    if (getenv('MANUAL_COOKIES')) $merged['manual_cookies'] = getenv('MANUAL_COOKIES');
    return $merged;
}

// Save config
function saveConfig(array $config) {
    file_put_contents(CONFIG_FILE, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

// Session data handler
function loadSessionData() {
    if (!file_exists(SESSION_FILE)) {
        return [
            'status' => 'inactive',
            'last_checked' => 0,
            'last_login' => 0,
            'cookies' => '',
            'message' => 'No active session recorded.'
        ];
    }
    $raw = file_get_contents(SESSION_FILE);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function saveSessionData(array $session) {
    file_put_contents(SESSION_FILE, json_encode($session, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
