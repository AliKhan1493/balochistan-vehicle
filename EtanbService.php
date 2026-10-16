<?php
/**
 * EtanbService - Balochistan Excise upstream proxy service
 * Handles authenticated requests, session persistence, auto-refresh and parsing.
 */

require_once __DIR__ . '/config.php';

class EtanbService {
    private array $config;
    private string $cookieFile;

    public function __construct() {
        $this->config = loadConfig();
        $this->cookieFile = COOKIE_FILE;
    }

    /**
     * Search vehicle history by vehicle number and location.
     */
    public function searchVehicle(string $vehicleNo, string $location = '1'): array {
        $vehicleNo = trim(strtoupper($vehicleNo));
        if (empty($vehicleNo)) {
            return [
                'success' => false,
                'error'   => 'Please provide a valid vehicle registration number (e.g. QTA-1234).'
            ];
        }

        // Validate vehicle format (safe characters)
        if (!preg_match('/^[A-Z0-9\-\s]{2,15}$/', $vehicleNo)) {
            return [
                'success' => false,
                'error'   => 'Invalid vehicle number format. Only alphanumeric characters and hyphens allowed.'
            ];
        }

        // 1. First attempt to query the endpoint
        $queryResult = $this->queryHistoryEndpoint($vehicleNo, $location);

        // 2. Check if session was expired
        if ($queryResult['is_session_expired']) {
            // Attempt auto-refresh / relogin
            if (!empty($this->config['upstream_email']) && !empty($this->config['upstream_password'])) {
                $relogin = $this->attemptLogin();
                if ($relogin['success']) {
                    // Retry query with refreshed session
                    $queryResult = $this->queryHistoryEndpoint($vehicleNo, $location);
                } else {
                    return [
                        'success' => false,
                        'error'   => 'Upstream session expired and automatic login could not complete: ' . $relogin['message'],
                        'session_status' => 'expired'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'error'   => 'Upstream session has expired. Please contact the administrator to renew the session.',
                    'session_status' => 'expired'
                ];
            }
        }

        if (!$queryResult['http_success']) {
            return [
                'success' => false,
                'error'   => $queryResult['error'] ?? 'Failed to communicate with Balochistan Excise portal.'
            ];
        }

        // Parse and sanitize the response
        $parsed = $this->parseResponse($queryResult['body']);
        return [
            'success'     => true,
            'vehicle_no'  => $vehicleNo,
            'location'    => $location,
            'data'        => $parsed,
            'raw_preview' => $queryResult['body']
        ];
    }

    /**
     * Execute HTTP request to /history/store
     */
    private function queryHistoryEndpoint(string $vehicleNo, string $location): array {
        $baseUrl = rtrim($this->config['upstream_url'], '/');
        $url = $baseUrl . '/history/store?vehicle_no=' . urlencode($vehicleNo) . '&location=' . urlencode($location);

        $headers = [
            'Accept: */*',
            'Accept-Language: en-US,en;q=0.9',
            'Referer: ' . $baseUrl . '/history',
            'X-Requested-With: XMLHttpRequest',
            'DNT: 1',
            'sec-ch-ua-mobile: ?0',
            'sec-fetch-dest: empty',
            'sec-fetch-mode: cors',
            'sec-fetch-site: same-origin'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Do not silently follow redirects to detect 302 login
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->config['user_agent']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        // Combine cookies from both cookieFile and manual config
        $cookieHeader = $this->getCombinedCookies();
        if (!empty($cookieHeader)) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookieHeader);
        }
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);

        // Header output to inspect redirects
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return [
                'http_success'        => false,
                'is_session_expired'  => false,
                'error'               => 'Connection error: ' . $curlError,
                'body'                => ''
            ];
        }

        $responseHeaders = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        // Check for session expiry signatures:
        // 1) 302 redirect to /login
        // 2) 401 Unauthorized
        // 3) Body contains login form or HTML login title
        $isExpired = false;
        if ($httpCode === 302 && stripos($responseHeaders, 'Location:') !== false) {
            if (stripos($responseHeaders, 'login') !== false) {
                $isExpired = true;
            }
        } elseif ($httpCode === 401 || $httpCode === 403) {
            $isExpired = true;
        } elseif (stripos($body, '<body class="login">') !== false || stripos($body, 'action="https://etanb.com/login"') !== false) {
            $isExpired = true;
        }

        return [
            'http_success'       => ($httpCode >= 200 && $httpCode < 300) && !$isExpired,
            'is_session_expired' => $isExpired,
            'http_code'          => $httpCode,
            'body'               => $body,
            'headers'            => $responseHeaders
        ];
    }

    /**
     * Automated login to refresh session cookies
     */
    public function attemptLogin(): array {
        $baseUrl = rtrim($this->config['upstream_url'], '/');
        $loginUrl = $baseUrl . '/login';
        $email = $this->config['upstream_email'] ?? '';
        $password = $this->config['upstream_password'] ?? '';

        if (empty($email) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Upstream credentials (email/password) are not configured in settings.'
            ];
        }

        // Step 1: GET /login to extract CSRF token and initial cookies
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $loginUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->config['user_agent']);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $getHeaders = [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
            'Sec-Ch-Ua: "Google Chrome";v="155", "Chromium";v="155", "Not(A:Brand";v="24"',
            'Sec-Ch-Ua-Mobile: ?0',
            'Sec-Ch-Ua-Platform: "Windows"',
            'Sec-Fetch-Dest: document',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: none',
            'Sec-Fetch-User: ?1',
            'Upgrade-Insecure-Requests: 1'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $getHeaders);

        if (!empty($this->config['manual_cookies'])) {
            // Retain any cf_clearance tokens
            curl_setopt($ch, CURLOPT_COOKIE, $this->config['manual_cookies']);
        }

        $loginPageHtml = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($loginPageHtml === false || $httpCode >= 400) {
            return [
                'success' => false,
                'message' => 'Unable to reach login page (HTTP ' . $httpCode . '). Cloudflare may be blocking automated requests.'
            ];
        }

        // Extract _token
        $token = '';
        if (preg_match('/name="_token"\s+value="([^"]+)"/i', $loginPageHtml, $matches)) {
            $token = $matches[1];
        } elseif (preg_match('/<meta\s+name="csrf-token"\s+content="([^"]+)"/i', $loginPageHtml, $matches)) {
            $token = $matches[1];
        }

        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Failed to extract CSRF token from login page. The page may require a browser challenge.'
            ];
        }

        // Step 2: POST /login with credentials
        $postData = http_build_query([
            '_token'   => $token,
            'email'    => $email,
            'password' => $password
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $loginUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // check 302 redirect to /home or dashboard
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->config['user_agent']);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $headers = [
            'Referer: ' . $loginUrl,
            'Origin: ' . $baseUrl,
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
            'Sec-Ch-Ua: "Google Chrome";v="155", "Chromium";v="155", "Not(A:Brand";v="24"',
            'Sec-Ch-Ua-Mobile: ?0',
            'Sec-Ch-Ua-Platform: "Windows"',
            'Sec-Fetch-Dest: document',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: same-origin',
            'Sec-Fetch-User: ?1',
            'Upgrade-Insecure-Requests: 1'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if (!empty($this->config['manual_cookies'])) {
            curl_setopt($ch, CURLOPT_COOKIE, $this->config['manual_cookies']);
        }

        $loginResponse = curl_exec($ch);
        $postHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $resHeaders = substr($loginResponse, 0, $headerSize);
        $resBody = substr($loginResponse, $headerSize);

        // A successful Laravel authentication typically responds with 302 redirecting away from login (e.g. to /home)
        $isLoggedIn = false;
        if ($postHttpCode === 302) {
            if (stripos($resHeaders, 'Location:') !== false && stripos($resHeaders, '/login') === false) {
                $isLoggedIn = true;
            }
        } elseif ($postHttpCode === 200 && stripos($resBody, 'Dashboard') !== false) {
            $isLoggedIn = true;
        }

        if ($isLoggedIn) {
            // Update session tracking
            saveSessionData([
                'status'       => 'active',
                'last_login'   => time(),
                'last_checked' => time(),
                'message'      => 'Session active and authenticated via auto-login.'
            ]);
            return [
                'success' => true,
                'message' => 'Successfully re-authenticated with upstream server.'
            ];
        } else {
            // Check if invalid credentials or cloudflare block
            $errMsg = 'Login attempt rejected by upstream. Please check email/password.';
            if (stripos($resBody, 'These credentials do not match') !== false) {
                $errMsg = 'Invalid username or password on upstream portal.';
            }

            saveSessionData([
                'status'       => 'error',
                'last_login'   => time(),
                'last_checked' => time(),
                'message'      => $errMsg
            ]);

            return [
                'success' => false,
                'message' => $errMsg
            ];
        }
    }

    /**
     * Check if the session is currently active
     */
    public function checkSessionStatus(): array {
        $baseUrl = rtrim($this->config['upstream_url'], '/');
        $checkUrl = $baseUrl . '/history';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $checkUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->config['user_agent']);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        if (!empty($this->config['manual_cookies'])) {
            curl_setopt($ch, CURLOPT_COOKIE, $this->config['manual_cookies']);
        } else {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        }

        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $headers = substr($res, 0, $headerSize);
        $body = substr($res, $headerSize);

        $isActive = ($code === 200) && (stripos($body, 'login') === false || stripos($body, 'Customer Relationship Management') !== false);
        if ($code === 302 && stripos($headers, 'login') !== false) {
            $isActive = false;
        }

        $statusData = [
            'status'       => $isActive ? 'active' : 'expired',
            'http_code'    => $code,
            'last_checked' => time(),
            'message'      => $isActive ? 'Session is authenticated and ready.' : 'Session is inactive or expired.'
        ];
        saveSessionData($statusData);

        return $statusData;
    }

    /**
     * Parse vehicle history response (handles both JSON and HTML tables/divs)
     */
    private function parseResponse(string $raw): array {
        $trimmed = trim($raw);

        // If JSON
        $json = json_decode($trimmed, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            return [
                'type' => 'json',
                'data' => $json
            ];
        }

        // If HTML, extract structured table data if available
        $parsedFields = [];
        if (preg_match_all('/<tr[^>]*>\s*<t[dh][^>]*>(.*?)<\/t[dh]>\s*<td[^>]*>(.*?)<\/td>\s*<\/tr>/is', $raw, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $k = trim(strip_tags($m[1]));
                $v = trim(strip_tags($m[2]));
                if (!empty($k)) {
                    $parsedFields[$k] = $v;
                }
            }
        }

        return [
            'type'        => 'html',
            'fields'      => $parsedFields,
            'html_content'=> $raw
        ];
    }

    /**
     * Merge cookies from cookies.txt and manual_cookies configuration
     */
    private function getCombinedCookies(): string {
        $map = [];

        // 1. Read from cookieFile
        if (file_exists($this->cookieFile)) {
            $lines = file($this->cookieFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines !== false) {
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line) || ($line[0] === '#' && substr($line, 0, 10) !== '#HttpOnly_')) {
                        continue;
                    }
                    $parts = preg_split('/\t+/', $line);
                    if (count($parts) >= 7) {
                        $name = trim($parts[5]);
                        $value = trim($parts[6]);
                        if (!empty($name)) {
                            $map[$name] = $value;
                        }
                    }
                }
            }
        }

        // 2. Read from manual_cookies in config
        if (!empty($this->config['manual_cookies'])) {
            $pairs = explode(';', $this->config['manual_cookies']);
            foreach ($pairs as $p) {
                $p = trim($p);
                if (strpos($p, '=') !== false) {
                    list($k, $v) = explode('=', $p, 2);
                    $k = trim($k);
                    $v = trim($v);
                    if (!empty($k)) {
                        $map[$k] = $v;
                    }
                }
            }
        }

        $items = [];
        foreach ($map as $k => $v) {
            $items[] = "$k=$v";
        }
        return implode('; ', $items);
    }
}
