<?php
/**
 * Authentication Controller
 * Handles LDAP and local authentication
 */

class AuthController {

    public function showLogin(): void {
        view('auth.login');
    }

    public function login(): void {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            flash('error', 'Please enter username and password');
            redirect('/login');
            return;
        }

        // Try LDAP authentication first if enabled
        if (LDAP_ENABLED) {
            $ldapUser = $this->authenticateLDAP($username, $password);
            if ($ldapUser) {
                $this->createSession($ldapUser);
                $this->logLogin($ldapUser['id']);
                flash('success', 'Welcome back, ' . $ldapUser['first_name'] . '!');
                redirect('/');
                return;
            }
        }

        // Fall back to local authentication
        $user = $this->authenticateLocal($username, $password);
        if ($user) {
            $this->createSession($user);
            $this->logLogin($user['id']);
            flash('success', 'Welcome back, ' . $user['first_name'] . '!');
            redirect('/');
            return;
        }

        flash('error', 'Invalid username or password');
        $_SESSION['old'] = ['username' => $username];
        redirect('/login');
    }

    public function logout(): void {
        if (isset($_SESSION['user'])) {
            $this->logLogout($_SESSION['user']['id']);
        }

        session_destroy();
        session_start();

        flash('success', 'You have been logged out');
        redirect('/login');
    }

    private function authenticateLDAP(string $username, string $password): ?array {
        if (!extension_loaded('ldap')) {
            error_log('LDAP extension not loaded');
            return null;
        }

        try {
            $ldapConn = @ldap_connect(LDAP_HOST, LDAP_PORT);
            if (!$ldapConn) {
                error_log('Failed to connect to LDAP server');
                return null;
            }

            ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

            // Bind with service account first
            $serviceBind = @ldap_bind($ldapConn, LDAP_BIND_DN, LDAP_BIND_PASSWORD);
            if (!$serviceBind) {
                error_log('LDAP service account bind failed: ' . ldap_error($ldapConn));
                ldap_close($ldapConn);
                return null;
            }

            // Search for user
            $filter = sprintf(LDAP_USER_FILTER, ldap_escape($username, '', LDAP_ESCAPE_FILTER));
            $search = @ldap_search($ldapConn, LDAP_BASE_DN, $filter, [
                'sAMAccountName', 'mail', 'givenName', 'sn', 'memberOf', 'distinguishedName'
            ]);

            if (!$search) {
                ldap_close($ldapConn);
                return null;
            }

            $entries = ldap_get_entries($ldapConn, $search);
            if ($entries['count'] === 0) {
                ldap_close($ldapConn);
                return null;
            }

            $userDn = $entries[0]['distinguishedname'][0];

            // Try to bind as the user
            $userBind = @ldap_bind($ldapConn, $userDn, $password);
            if (!$userBind) {
                ldap_close($ldapConn);
                return null;
            }

            // User authenticated - determine role from groups
            $groups = $entries[0]['memberof'] ?? [];
            $role = $this->determineRoleFromGroups($groups);

            $email = $entries[0]['mail'][0] ?? $username . '@municipality.gov.za';
            $firstName = $entries[0]['givenname'][0] ?? $username;
            $lastName = $entries[0]['sn'][0] ?? '';

            ldap_close($ldapConn);

            // Sync user to local database
            return $this->syncLDAPUser($username, $email, $firstName, $lastName, $role, $userDn);

        } catch (Exception $e) {
            error_log('LDAP authentication error: ' . $e->getMessage());
            return null;
        }
    }

    private function determineRoleFromGroups(array $groups): string {
        foreach ($groups as $group) {
            if (!is_string($group)) continue;

            if (stripos($group, LDAP_GROUP_ADMIN) !== false) {
                return ROLE_ADMIN;
            }
            if (stripos($group, LDAP_GROUP_DIRECTOR) !== false) {
                return ROLE_DIRECTOR;
            }
            if (stripos($group, LDAP_GROUP_MANAGER) !== false) {
                return ROLE_MANAGER;
            }
            if (stripos($group, LDAP_GROUP_ASSESSOR) !== false) {
                return ROLE_ASSESSOR;
            }
        }
        return ROLE_EMPLOYEE;
    }

    private function syncLDAPUser(string $username, string $email, string $firstName, string $lastName, string $role, string $ldapDn): array {
        $db = db();

        // Check if user exists
        $user = $db->fetch(
            "SELECT * FROM users WHERE username = ? OR email = ?",
            [$username, $email]
        );

        if ($user) {
            // Update existing user
            $db->update('users', [
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'ldap_dn' => $ldapDn,
                'is_ldap_user' => 1,
                'last_login' => date('Y-m-d H:i:s')
            ], 'id = ?', [$user['id']]);

            $user['first_name'] = $firstName;
            $user['last_name'] = $lastName;
            $user['email'] = $email;
        } else {
            // Create new user
            $userId = $db->insert('users', [
                'username' => $username,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'role' => $role,
                'ldap_dn' => $ldapDn,
                'is_ldap_user' => 1,
                'is_active' => 1,
                'last_login' => date('Y-m-d H:i:s')
            ]);

            $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        }

        return $user;
    }

    private function authenticateLocal(string $username, string $password): ?array {
        $db = db();

        $user = $db->fetch(
            "SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1",
            [$username, $username]
        );

        if (!$user) {
            error_log("Auth failed: User not found for username: $username");
            // Debug: Check total users
            $count = $db->fetch("SELECT COUNT(*) as cnt FROM users");
            error_log("Total users in database: " . ($count['cnt'] ?? 0));
            return null;
        }

        error_log("Auth: Found user {$user['username']}, checking password...");

        if (!password_verify($password, $user['password_hash'])) {
            error_log("Auth failed: Password mismatch for user {$user['username']}");
            return null;
        }

        // Update last login
        $db->update('users', [
            'last_login' => date('Y-m-d H:i:s')
        ], 'id = ?', [$user['id']]);

        return $user;
    }

    private function createSession(array $user): void {
        // Get directorate info if assigned
        $directorate = null;
        if ($user['directorate_id']) {
            $directorate = db()->fetch(
                "SELECT id, name, code FROM directorates WHERE id = ?",
                [$user['directorate_id']]
            );
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role' => $user['role'],
            'directorate_id' => $user['directorate_id'],
            'directorate' => $directorate,
            'is_ldap_user' => $user['is_ldap_user'] ?? false
        ];

        // Regenerate session ID for security
        session_regenerate_id(true);
    }

    private function logLogin(int $userId): void {
        db()->insert('audit_log', [
            'user_id' => $userId,
            'action' => 'login',
            'table_name' => 'users',
            'record_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }

    private function logLogout(int $userId): void {
        db()->insert('audit_log', [
            'user_id' => $userId,
            'action' => 'logout',
            'table_name' => 'users',
            'record_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
}
