<?php
session_start();

// Simulated user database
$users = [
    'admin' => [
        'password' => 'admin123',
        'role' => 'admin',
        'display_name' => 'Administrator'
    ],
    'user' => [
        'password' => 'user123',
        'role' => 'user',
        'display_name' => 'Regular User'
    ]
];

function authenticate($username, $password, $remember = false) {
    global $users;
    
    if (isset($users[$username])) {
        if ($users[$username]['password'] === $password) {
            // Set session
            $_SESSION['user'] = [
                'username' => $username,
                'role' => $users[$username]['role'],
                'display_name' => $users[$username]['display_name'],
                'last_activity' => time()
            ];
            
            // Set cookie if "Remember me" is checked
            if ($remember) {
                $cookie_value = json_encode([
                    'username' => $username,
                    'token' => md5($username . $password . time())
                ]);
                setcookie('remember_me', $cookie_value, time() + (30 * 24 * 3600), '/');
            }
            
            return true;
        }
    }
    return false;
}

function checkAutoLogin() {
    global $users;
    
    // Check session first
    if (isset($_SESSION['user']) && time() - $_SESSION['user']['last_activity'] < 60) {
        $_SESSION['user']['last_activity'] = time();
        return true;
    }
    
    // Check remember me cookie
    if (isset($_COOKIE['remember_me'])) {
        $cookie_data = json_decode($_COOKIE['remember_me'], true);
        if (isset($users[$cookie_data['username']])) {
            $_SESSION['user'] = [
                'username' => $cookie_data['username'],
                'role' => $users[$cookie_data['username']]['role'],
                'display_name' => $users[$cookie_data['username']]['display_name'],
                'last_activity' => time()
            ];
            return true;
        }
    }
    
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function getUserRole() {
    return $_SESSION['user']['role'] ?? null;
}

function getDisplayName() {
    return $_SESSION['user']['display_name'] ?? 'Guest';
}

function updateDisplayName($new_name) {
    if (isset($_SESSION['user'])) {
        $_SESSION['user']['display_name'] = $new_name;
    }
}

function getTheme() {
    return $_COOKIE['theme'] ?? 'light';
}

function setTheme($theme) {
    setcookie('theme', $theme, time() + (365 * 24 * 3600), '/');
}

function incrementVisitCount() {
    $count = $_COOKIE['visit_count'] ?? 0;
    $count++;
    setcookie('visit_count', $count, time() + (365 * 24 * 3600), '/');
    return $count;
}
?>