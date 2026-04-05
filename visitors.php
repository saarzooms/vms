<?php
require_once 'auth.php';
requirePermission(canRegisterVisitor());

$msg = '';
$edit_mode = false;
$v_id = $v_name = $v_mobile = $v_image = '';

$is_search = false;
$display_visitors = [];

// --- 1. HANDLE DELETE (Admin Only) ---
if (isset($_GET['delete']) && canEditOrDelete()) {
    $del_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT image_path FROM visitors WHERE id = ?");
    $stmt->execute([$del_id]);
    $del_visitor = $stmt->fetch();
    
    if ($del_visitor && $del_visitor['image_path'] && file_exists($del_visitor['image_path'])) {
        unlink($del_visitor['image_path']);
    }
    
    $pdo->prepare("DELETE FROM visitors WHERE id = ?")->execute([$del_id]);
    header("Location: visitors.php?msg=deleted");
    exit;
}

// --- 2. HANDLE FORM SUBMISSIONS (Add, Update, or Search) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $name = trim($_POST['name']);
    $mobile = trim($_POST['mobile']);

    if ($action === 'search') {
        // --- STRICT SEARCH LOGIC ---
        if (empty($name) && empty($mobile)) {
            $msg = "<div class='alert alert-warning'>Please enter a name or mobile number to search.</div>";
        } else {
            $is_search = true;
            $conditions = [];
            $params = [];

            if (!empty($name)) {
                $conditions[] = "name LIKE ?";
                $params[] = "%$name%";
            }
            if (!empty($mobile)) {
                $conditions[] = "mobile LIKE ?";
                $params[] = "%$mobile%";
            }

            // FIXED: Changed " OR " to " AND " so both conditions must be met if both are provided
            $sql = "SELECT * FROM visitors WHERE " . implode(" AND ", $conditions) . " ORDER BY name ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $display_visitors = $stmt->fetchAll();
            
            if (empty($display_visitors)) {
                $msg = "<div class='alert alert-info'>No matching visitors found.</div>";
            }
        }
    } else {
        // --- ADD / EDIT LOGIC ---
        $image_path = $_POST['existing_image'] ?? null;
        if (!empty($_FILES['image']['name'])) {
            $image_path = 'uploads/' . time() . '_' . $_FILES['image']['name'];
            move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
        }

        try {
            if ($action === 'add' && canRegisterVisitor()) {
                $stmt = $pdo->prepare("INSERT INTO visitors (name, mobile, image_path) VALUES (?, ?, ?)");
                $stmt->execute([$name, $mobile, $image_path]);
                $msg = "<div class='alert alert-success'>Visitor Registered Successfully!</div>";
                $name = $mobile = ''; 
            } elseif ($action === 'edit' && canEditOrDelete()) {
                $id = $_POST['id'];
                $stmt = $pdo->prepare("UPDATE visitors SET name = ?, mobile = ?, image_path = ? WHERE id = ?");
                $stmt->execute([$name, $mobile, $image_path, $id]);
                header("Location: visitors.php?msg=updated");
                exit;
            }
        } catch(PDOException $e) {
            $msg = "<div class='alert alert-danger'>Error: Mobile number might already exist.</div>";
        }
    }
}

// --- 3. TRIGGER EDIT MODE ---
if (isset($_GET['edit']) && canEditOrDelete()) {
    $edit_mode = true;
    $stmt = $pdo->prepare("SELECT * FROM visitors WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $visitor_to_edit = $stmt->fetch();
    if ($visitor_to_edit) {
        $v_id = $visitor_to_edit['id'];
        $v_name = $visitor_to_edit['name'];
        $v_mobile = $visitor_to_edit['mobile'];
        $v_image = $visitor_to_edit['image_path'];
    }
}

// --- 4. FETCH ALL IF NOT SEARCHING ---
if (!$is_search) {
    $display_visitors = $pdo->query("SELECT * FROM visitors ORDER BY created_at DESC")->fetchAll();
}

// --- Display URL messages ---
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') $msg = "<div class='alert alert-success'>Visitor Deleted Successfully!</div>";
    if ($_GET['msg'] === 'updated') $msg = "<div class='alert alert-success'>Visitor Updated Successfully!</div>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Visitors</h2>
        <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    
    <?= $msg ?>

    <div class="card p-4 mb-5 shadow-sm <?= $edit_mode ? 'border-primary' : '' ?>">
        <h4 class="mb-3 <?= $edit_mode ? 'text-primary' : '' ?>">
            <?= $edit_mode ? "Edit Visitor Details" : "Visitor Registration & Search" ?>
        </h4>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $v_id ?>">
            <input type="hidden" name="existing_image" value="<?= htmlspecialchars($v_image) ?>">

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($v_name ?? ($name ?? '')) ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Mobile Number</label>
                    <input type="text" name="mobile" class="form-control" value="<?= htmlspecialchars($v_mobile ?? ($mobile ?? '')) ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label"><?= $edit_mode ? 'Update Photo' : 'Photo (Optional)' ?></label>
                    <input type="file" name="image" accept="image/*" class="form-control">
                </div>
                
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <?php if ($edit_mode): ?>
                        <button type="submit" name="action" value="edit" class="btn btn-primary w-100">Update</button>
                        <a href="visitors.php" class="btn btn-outline-secondary w-100">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="action" value="add" class="btn btn-success w-100">Add</button>
                        <button type="submit" name="action" value="search" class="btn btn-info w-100">Search</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4 class="mb-0">
            <?= $is_search ? "Search Results (" . count($display_visitors) . ")" : "All Registered Visitors" ?>
        </h4>
        <?php if ($is_search): ?>
            <a href="visitors.php" class="btn btn-sm btn-outline-danger">Clear Search</a>
        <?php endif; ?>
    </div>
    
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Registered On</th>
                    <th>Actions</th> 
                </tr>
            </thead>
            <tbody>
                <?php foreach($display_visitors as $v): ?>
                <tr>
                    <td>
                        <?php if($v['image_path'] && file_exists($v['image_path'])): ?>
                            <img src="<?= htmlspecialchars($v['image_path']) ?>" width="40" height="40" class="rounded-circle" style="object-fit: cover;">
                        <?php else: ?>
                            <span class="text-muted small">No Image</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($v['name']) ?></td>
                    <td><?= htmlspecialchars($v['mobile']) ?></td>
                    <td><?= date('Y-m-d', strtotime($v['created_at'])) ?></td>
                    <td>
                        <a href="add_visit.php?search_mobile=<?= urlencode($v['mobile']) ?>" class="btn btn-sm btn-success">Add Visit</a>
                        
                        <?php if(canEditOrDelete()): ?>
                            <a href="?edit=<?= $v['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="?delete=<?= $v['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to completely delete this visitor?');">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if(empty($display_visitors)): ?>
                    <tr><td colspan="5" class="text-center">No visitors found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>