<?php
/**
 * Admin Controller
 * Handles user management, directorates, and system settings
 */

class AdminController {

    public function index(): void {
        redirect('/admin/users');
    }

    public function users(): void {
        $db = db();

        $users = $db->fetchAll("
            SELECT u.*, d.name as directorate_name, d.code as directorate_code,
                   dep.name as department_name
            FROM users u
            LEFT JOIN directorates d ON d.id = u.directorate_id
            LEFT JOIN departments dep ON dep.id = u.department_id
            ORDER BY u.created_at DESC
        ");

        $data = [
            'title' => 'User Management',
            'users' => $users
        ];

        view('admin.users', $data);
    }

    public function createUser(): void {
        $db = db();

        $directorates = $db->fetchAll("SELECT id, name, code FROM directorates WHERE is_active = 1 ORDER BY name");
        $departments = $db->fetchAll("SELECT id, name, directorate_id FROM departments WHERE is_active = 1 ORDER BY name");

        $data = [
            'title' => 'Create User',
            'directorates' => $directorates,
            'departments' => $departments
        ];

        view('admin.create-user', $data);
    }

    public function storeUser(): void {
        $db = db();

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $role = $_POST['role'] ?? 'employee';
        $directorateId = $_POST['directorate_id'] ?: null;
        $departmentId = $_POST['department_id'] ?: null;

        // Validate
        if (empty($username) || empty($email) || empty($password)) {
            flash('error', 'Username, email and password are required.');
            redirect('/admin/users/create');
            return;
        }

        // Check if username exists
        $existing = $db->fetch("SELECT id FROM users WHERE username = ?", [$username]);
        if ($existing) {
            flash('error', 'Username already exists.');
            redirect('/admin/users/create');
            return;
        }

        // Create user
        $db->query("
            INSERT INTO users (username, email, password_hash, first_name, last_name, role, directorate_id, department_id, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ", [$username, $email, password_hash($password, PASSWORD_DEFAULT), $firstName, $lastName, $role, $directorateId, $departmentId]);

        flash('success', 'User created successfully.');
        redirect('/admin/users');
    }

    public function showUser(string $id): void {
        $db = db();

        $user = $db->fetch("
            SELECT u.*, d.name as directorate_name, dep.name as department_name
            FROM users u
            LEFT JOIN directorates d ON d.id = u.directorate_id
            LEFT JOIN departments dep ON dep.id = u.department_id
            WHERE u.id = ?
        ", [$id]);

        if (!$user) {
            http_response_code(404);
            view('errors.404');
            return;
        }

        // Get user's KPIs
        $kpis = $db->fetchAll("
            SELECT k.*, so.objective_name
            FROM kpis k
            JOIN idp_strategic_objectives so ON so.id = k.strategic_objective_id
            WHERE k.responsible_user_id = ?
            ORDER BY k.kpi_code
        ", [$id]);

        $data = [
            'title' => $user['first_name'] . ' ' . $user['last_name'],
            'user' => $user,
            'kpis' => $kpis
        ];

        view('admin.show-user', $data);
    }

    public function editUser(string $id): void {
        $db = db();

        $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$id]);

        if (!$user) {
            http_response_code(404);
            view('errors.404');
            return;
        }

        $directorates = $db->fetchAll("SELECT id, name, code FROM directorates WHERE is_active = 1 ORDER BY name");
        $departments = $db->fetchAll("SELECT id, name, directorate_id FROM departments WHERE is_active = 1 ORDER BY name");

        $data = [
            'title' => 'Edit User',
            'user' => $user,
            'directorates' => $directorates,
            'departments' => $departments
        ];

        view('admin.edit-user', $data);
    }

    public function updateUser(string $id): void {
        $db = db();

        $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            flash('error', 'User not found.');
            redirect('/admin/users');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $role = $_POST['role'] ?? 'employee';
        $directorateId = $_POST['directorate_id'] ?: null;
        $departmentId = $_POST['department_id'] ?: null;
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $newPassword = $_POST['new_password'] ?? '';

        // Update user
        if (!empty($newPassword)) {
            $db->query("
                UPDATE users
                SET email = ?, first_name = ?, last_name = ?, role = ?,
                    directorate_id = ?, department_id = ?, is_active = ?,
                    password_hash = ?, updated_at = NOW()
                WHERE id = ?
            ", [$email, $firstName, $lastName, $role, $directorateId, $departmentId, $isActive, password_hash($newPassword, PASSWORD_DEFAULT), $id]);
        } else {
            $db->query("
                UPDATE users
                SET email = ?, first_name = ?, last_name = ?, role = ?,
                    directorate_id = ?, department_id = ?, is_active = ?, updated_at = NOW()
                WHERE id = ?
            ", [$email, $firstName, $lastName, $role, $directorateId, $departmentId, $isActive, $id]);
        }

        flash('success', 'User updated successfully.');
        redirect('/admin/users/' . $id);
    }

    public function deleteUser(string $id): void {
        $db = db();

        // Don't allow deleting yourself
        if ($id == user()['id']) {
            flash('error', 'You cannot delete your own account.');
            redirect('/admin/users');
            return;
        }

        $db->query("UPDATE users SET is_active = 0 WHERE id = ?", [$id]);

        flash('success', 'User deactivated successfully.');
        redirect('/admin/users');
    }

    public function directorates(): void {
        $db = db();

        $directorates = $db->fetchAll("
            SELECT d.*, u.first_name, u.last_name,
                   COUNT(DISTINCT dep.id) as department_count,
                   COUNT(DISTINCT usr.id) as user_count
            FROM directorates d
            LEFT JOIN users u ON u.id = d.head_user_id
            LEFT JOIN departments dep ON dep.directorate_id = d.id
            LEFT JOIN users usr ON usr.directorate_id = d.id
            GROUP BY d.id
            ORDER BY d.name
        ");

        $data = [
            'title' => 'Directorates',
            'directorates' => $directorates
        ];

        view('admin.directorates', $data);
    }

    public function financialYears(): void {
        $db = db();

        $years = $db->fetchAll("SELECT * FROM financial_years ORDER BY start_date DESC");

        $data = [
            'title' => 'Financial Years',
            'years' => $years
        ];

        view('admin.financial-years', $data);
    }

    public function settings(): void {
        $data = [
            'title' => 'System Settings'
        ];

        view('admin.settings', $data);
    }

    public function updateSettings(): void {
        // Settings would typically be stored in database
        flash('success', 'Settings updated successfully.');
        redirect('/admin/settings');
    }
}
