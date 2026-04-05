<?php
    require_once 'config.php';
$msg = '';
    // add the visitor
    if($_SERVER['REQUEST_METHOD']==='POST'){
        $action = $_POST['action'];
        $name = trim($_POST['name']);
        $mobile = trim($_POST['mobile']);

        $image_path ='uploads/'.time().'_'.$_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'],$image_path);
        try {
            if($action === 'add'){
                $stmt = $pdo->prepare("insert into visitors (name, mobile, image_path) values (?,?,?)");
                $stmt->execute([$name, $mobile, $image_path]);
                $msg = "<div class ='alert alert-sucess' >Visitor Registered Successfully!</div>";
                $name = $mobile = '';
            }
        } catch (PDOException $e) {
             $msg = "<div class ='alert alert-danger' >Error:Mobile number might already exist.</div>";
        }

    }
    $display_visitors = $pdo->query("select * from visitors order by created_at desc")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4 mb-5 bg-light">
    <?=$msg?>
    <div class="card p-4 mb-5 shadow-sm">
        <h4 class="mb-3">Visitor Registration</h4>
        <form method="post" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control">
                </div> 
                <div class="col-md-3">
                    <label for="mobile">Mobile Number</label>
                    <input type="text" name="mobile" id="mobile" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="name">Photo(optional)</label>
                    <input type="file" name="image" id="image" class="form-control">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" name="action" value="add" class="btn btn-success w-100">Add</button>
                </div>
            </div>
        </form>
    </div>
    <div class="table-responsive">
    <table class="table table-bordered table-striped mt-3">
        <thead class="table-dark">
            <tr>
                <th>Photo</th>
                <th>Name</th>
                <th>Mobile</th>
                <th>Registered</th>
                <th>Action</th> 
            </tr>
        </thead>
        <tbody>
            <?php foreach ($display_visitors as $v) { ?>
                <tr>
                    <td>
                        <?php if($v['image_path'] && file_exists($v['image_path'])):?>
                            <img src="<?=htmlspecialchars($v['image_path'])?>" width= "40" class="rounded-circle" style="object-fit:cover;">
                        <?php else:?>
                            <span class="text-muted small">No Image</span>
                            <?php endif;?>
                       </td>
                    <td><?=htmlspecialchars($v['name'])?></td>
                    <td><?=htmlspecialchars($v['mobile'])?></td>
                    <td><?=date('d-m-Y H:i', strtotime($v['created_at']))?></td>
                    <td>
                        <a href="?edit=<?= $v['id']?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="?delete=<?= $v['id']?>" class="btn btn-sm btn-danger">Delete</a>
                </td>
                </tr>
            <?php }?>
        </tbody>
    </table>
    </div>
</body>
</html>