<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4 mb-5 bg-light">
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
    </table>
    </div>
</body>
</html>