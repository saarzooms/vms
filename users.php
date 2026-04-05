<?php
require_once 'auth.php';

// Strict Role Check: ONLY Admin (Owner) can manage users
requirePermission($current_role === 'admin');

// ==========================================
// 1. BACKEND AJAX API HANDLER
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $action = $_POST['ajax_action'];

    try {
        // --- FETCH USERS (With optional Search) ---
        if ($action === 'fetch') {
            $search = trim($_POST['search'] ?? '');
            $query = "SELECT id, username, role FROM users";
            $params = [];
            
            if (!empty($search)) {
                $query .= " WHERE username LIKE ? OR role LIKE ?";
                $params = ["%$search%", "%$search%"];
            }
            $query .= " ORDER BY role ASC, username ASC";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            exit;
        }

        // --- ADD USER ---
        if ($action === 'add') {
            $username = trim($_POST['username']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $role = $_POST['role'];

            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $password, $role]);
            echo json_encode(['status' => 'success', 'message' => 'User added successfully!']);
            exit;
        }

        // --- EDIT USER ---
        if ($action === 'edit') {
            $id = (int)$_POST['user_id'];
            $username = trim($_POST['username']);
            $role = $_POST['role'];
            $password = $_POST['password']; // Optional

            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
                $stmt->execute([$username, $hashed_password, $role, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
                $stmt->execute([$username, $role, $id]);
            }
            echo json_encode(['status' => 'success', 'message' => 'User updated successfully!']);
            exit;
        }

        // --- DELETE USER ---
        if ($action === 'delete') {
            $id = (int)$_POST['user_id'];
            if ($id === $_SESSION['user_id']) {
                echo json_encode(['status' => 'error', 'message' => 'You cannot delete your own account.']);
            } else {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['status' => 'success', 'message' => 'User deleted successfully!']);
            }
            exit;
        }

    } catch (PDOException $e) {
        // Handle Duplicate Username errors
        if ($e->getCode() == 23000) {
            echo json_encode(['status' => 'error', 'message' => 'Username already exists.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error occurred.']);
        }
        exit;
    }
}
// ==========================================
// END OF AJAX API - Start of HTML/UI
// ==========================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4 mb-5 bg-light">

    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <h2>Manage System Users</h2>
        <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <div id="alertBox" class="d-none alert"></div>

    <div class="card p-3 shadow-sm mb-4 bg-white">
        <div class="row align-items-center">
            <div class="col-md-8 d-flex gap-2">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by username or role...">
                <button class="btn btn-info px-4" onclick="loadUsers()">Search</button>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-primary" onclick="openUserModal('add')">+ Add New User</button>
            </div>
        </div>
    </div>

    <div class="card shadow-sm bg-white p-3">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <tr><td colspan="4">Loading users...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalTitle">Add User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="userForm">
                        <input type="hidden" id="user_id" name="user_id">
                        <input type="hidden" id="ajax_action" name="ajax_action" value="add">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Username</label>
                            <input type="text" id="username" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Password</label>
                            <input type="password" id="password" name="password" class="form-control">
                            <small id="passwordHelp" class="text-muted d-none">Leave blank to keep current password.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Role</label>
                            <select id="role" name="role" class="form-select" required>
                                <option value="admin">Admin</option>
                                <option value="manager">Manager</option>
                                <option value="receptionist">Receptionist</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="saveUser()">Save User</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const currentUserId = <?= $_SESSION['user_id'] ?>;
        const userModal = new bootstrap.Modal(document.getElementById('userModal'));

        // Load users on page load
        document.addEventListener('DOMContentLoaded', () => loadUsers());

        // Display Alert Messages
        function showAlert(message, type = 'success') {
            const alertBox = document.getElementById('alertBox');
            alertBox.className = `alert alert-${type} alert-dismissible fade show`;
            alertBox.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            alertBox.classList.remove('d-none');
            setTimeout(() => alertBox.classList.add('d-none'), 4000); // Auto hide after 4s
        }

        // 1. FETCH & SEARCH
        async function loadUsers() {
            const search = document.getElementById('searchInput').value;
            const formData = new FormData();
            formData.append('ajax_action', 'fetch');
            formData.append('search', search);

            const response = await fetch('users.php', { method: 'POST', body: formData });
            const result = await response.json();

            const tbody = document.getElementById('userTableBody');
            tbody.innerHTML = '';

            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-muted">No users found.</td></tr>';
                return;
            }

            result.data.forEach(user => {
                const roleBadge = user.role === 'admin' ? '<span class="badge bg-danger">Admin</span>' :
                                  user.role === 'manager' ? '<span class="badge bg-warning text-dark">Manager</span>' :
                                  '<span class="badge bg-primary">Receptionist</span>';

                let actionBtns = '';
                if (parseInt(user.id) === currentUserId) {
                    actionBtns = `<span class="badge bg-secondary">Current User</span>`;
                } else {
                    actionBtns = `
                        <button class="btn btn-sm btn-outline-primary" onclick="openUserModal('edit', '${user.id}', '${user.username}', '${user.role}')">Edit</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser('${user.id}')">Delete</button>
                    `;
                }

                tbody.innerHTML += `
                    <tr>
                        <td>${user.id}</td>
                        <td class="fw-bold">${user.username}</td>
                        <td>${roleBadge}</td>
                        <td>${actionBtns}</td>
                    </tr>
                `;
            });
        }

        // 2. OPEN MODAL (Handles Add & Edit setup)
        function openUserModal(mode, id = '', username = '', role = 'receptionist') {
            document.getElementById('userForm').reset();
            const passInput = document.getElementById('password');
            const passHelp = document.getElementById('passwordHelp');

            if (mode === 'add') {
                document.getElementById('modalTitle').innerText = 'Add New User';
                document.getElementById('ajax_action').value = 'add';
                passInput.setAttribute('required', 'required');
                passHelp.classList.add('d-none');
            } else {
                document.getElementById('modalTitle').innerText = 'Edit User';
                document.getElementById('ajax_action').value = 'edit';
                document.getElementById('user_id').value = id;
                document.getElementById('username').value = username;
                document.getElementById('role').value = role;
                
                passInput.removeAttribute('required'); // Password optional on edit
                passHelp.classList.remove('d-none');
            }
            userModal.show();
        }

        // 3. SAVE (Add or Edit)
        async function saveUser() {
            const form = document.getElementById('userForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = new FormData(form);
            const response = await fetch('users.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'success') {
                userModal.hide();
                showAlert(result.message, 'success');
                loadUsers(); // Refresh table
            } else {
                showAlert(result.message, 'danger');
            }
        }

        // 4. DELETE
        async function deleteUser(id) {
            if (!confirm('Are you sure you want to permanently delete this user?')) return;

            const formData = new FormData();
            formData.append('ajax_action', 'delete');
            formData.append('user_id', id);

            const response = await fetch('users.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'success') {
                showAlert(result.message, 'success');
                loadUsers(); // Refresh table
            } else {
                showAlert(result.message, 'danger');
            }
        }
    </script>
</body>
</html>