<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.1/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
        const err = () => {
            toastr.error('Something went wrong');
        }
        const suc = () => {
            toastr.info('Stude added successfully');
        }
    </script>
    <style>
        .toast-message {
            color: #000 !important;
        }
    </style>
</head>

<body>
    <?php
    $conn = mysqli_connect("localhost", "root", "", "b76crud");
    $students = $conn->query("SELECT * FROM students ORDER BY `id` DESC");
    $fileLink = basename($_SERVER['PHP_SELF']);

    if (!isset($_GET['update'])) {
        if ($students->num_rows > 5) {
            $_GET['limit'] ?? header("location: $fileLink?page=1&limit=5");
            $limit = $_GET['limit'];
            $page = $_GET['page'];
            $total_page = ceil($students->num_rows / $limit);
            $start_point = ($page - 1) * $limit;
            $students = $conn->query("SELECT * FROM students ORDER BY `id` DESC LIMIT $start_point, $limit");
        }
    }


    $genderList = ["Male", "Female"];
    $cityList = ["Dhaka", "Rajshahi", "Chittagong", "Khulna", "Rongpur", "Bogura", "Barishal", "Others"];

    if (isset($_POST['addStudent'])) {
        $sname = safuda($_POST['sname']);
        $gender = safuda($_POST['gender'] ?? null);
        $city = safuda($_POST['city']);

        if (empty($sname)) {
            $errName = "Please write your name";
        } elseif (!preg_match("/^[A-Za-z. ]*$/", $sname)) {
            $errName = "Invalid name format";
        } else {
            $crrSname = $conn->real_escape_string($sname);
        }

        if (empty($gender)) {
            $errGender = "Please select your gender";
        } elseif (!in_array($gender, $genderList)) {
            $errGender = "Paknami bondho korun";
        } else {
            $crrGender = $conn->real_escape_string($gender);
        }

        if (empty($city)) {
            $errCity = "Please select your city";
        } elseif (!in_array($city, $cityList)) {
            $errCity = "Paknami bondho korun";
        } else {
            $crrCity = $conn->real_escape_string($city);
        }

        if (isset($crrSname) && isset($crrGender) && isset($crrCity)) {
            $insert = $conn->query("INSERT INTO `students` (`name`, `gender`, `city`) VALUES ('$crrSname', '$crrGender', '$crrCity')");
            if (!$insert) {
                echo '<script>err()</script>';
            } else {
                $cp = $_SERVER['PHP_SELF'];
                echo "<script>suc(); setTimeout(()=>{location.href='$cp'}, 2000) </script>";
            }
        }
    }

    function safuda($data)
    {
        $data = htmlspecialchars($data);
        $data = trim($data);
        $data = stripslashes($data);
        return $data;
    }
    ?>
    <div class="container">
        <div class="row py-5">
            <?php if (!isset($_GET['update'])) { ?>

                <div class="col-md-6">
                    <form action="" method="post">
                        <h2 class="mb-3">Add Student</h2>
                        <div class="mb-3">
                            <input type="text" placeholder="Student Name" name="sname" class="form-control <?= (isset($errName) ? "is-invalid" : null) ?>" value="<?= $sname ?? null ?>">
                            <div class="invalid-feedback"><?= $errName ?? null ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-check-inline ps-3">
                                <label for="" class="form-check-label">Gender : </label>
                            </div>
                            <div class="from-check form-check-inline">
                                <input type="radio" class="form-check-input <?= (isset($errGender) ? "is-invalid" : null) ?>" name="gender" value="Male" <?= (isset($gender) && $gender == "Male") ? "checked" : null; ?>>
                                <label for="" class="form-check-label">Male</label>
                            </div>
                            <div class="from-check form-check-inline">
                                <input type="radio" class="form-check-input <?= (isset($errGender) ? "is-invalid" : null) ?>" name="gender" value="Female" <?= (isset($gender) && $gender == "Female") ? "checked" : null; ?>>
                                <label for="" class="form-check-label">Female</label>
                            </div>
                            <input type="hidden" class="is-invalid">
                            <div class="invalid-feedback"><?= $errGender ?? null ?></div>
                        </div>
                        <div class="mb-3">
                            <select name="city" id="" class="form-select <?= (isset($errCity) ? "is-invalid" : null) ?>">
                                <option value="<?= (isset($city)) ? $city : null ?>"><?= (isset($city)) ? $city : "--SELECT CITY--" ?></option>
                                <?php foreach ($cityList as $ct) {
                                    if ($ct != $city) { ?>
                                        <option value="<?= $ct ?>"><?= $ct ?></option>
                                <?php }
                                } ?>
                            </select>
                            <div class="invalid-feedback">
                                <?= $errCity ?? null ?>
                            </div>
                        </div>

                        <input type="submit" value="Add Student" name="addStudent" class="btn btn-primary">
                    </form>
                </div>
                <div class="col-md-6">
                    <table class="table table-striped table-hover">
                        <tr>
                            <th>S.N.</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>City</th>
                            <th>Action</th>
                        </tr>
                        <?php
                        $sn = $start_point + 1;
                        while ($student = $students->fetch_object()) {
                        ?>
                            <tr>
                                <td><?= $sn ?></td>
                                <td><?= $student->name ?></td>
                                <td><?= $student->gender ?></td>
                                <td><?= $student->city ?></td>
                                <td>
                                    <a href='<?= "$fileLink?update= $student->id" ?>' class="btn btn-sm btn-warning">Update</a>
                                    <a href="" class="btn btn-sm btn-danger">DELETE</a>
                                </td>
                            </tr>
                        <?php ++$sn;
                        } ?>
                    </table>
                    <nav aria-label="...">
                        <?php if (isset($_GET['limit'])) {  ?>
                            <ul class="pagination">
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : null ?>">
                                    <a class="page-link" href='<?= "$fileLink?page=" . (($page <= 1) ? 1 : $page - 1) . "&limit=$limit"; ?>'>Previous</a>
                                </li>
                                <?php
                                if ($total_page - 5 >= $page) {
                                    if ($page + 2 <= 4) {
                                        $startPagination = 1;
                                        $endPagination = 5;
                                    } else {
                                        $startPagination = $page - 2;
                                        $endPagination = $page + 2;
                                    }
                                } else {
                                    $startPagination = $total_page - 5;
                                    $endPagination = $total_page;
                                }
                                for ($i = $startPagination; $i <= $endPagination; $i++) {
                                ?>
                                    <li class="page-item <?= ($i == $page) ? 'active' : null ?>" aria-current="page">
                                        <a class="page-link" href='<?= "$fileLink?page=$i&limit=$limit"; ?>'><?= $i ?></a>
                                    </li>
                                <?php } ?>
                                <li class="page-item <?= ($page == $total_page) ? 'disabled' : null ?>">
                                    <a class="page-link" href='<?= "$fileLink?page=" . (($page >= $total_page) ? $total_page : $page + 1) . "&limit=$limit"; ?>'>Next</a>
                                </li>
                            </ul>
                        <?php } ?>
                    </nav>
                </div>
            <?php } else { ?>
                <form action="" method="post">
                    <h2 class="mb-3">Update Student</h2>
                    <div class="mb-3">
                        <input type="text" placeholder="Student Name" name="sname" class="form-control <?= (isset($errName) ? "is-invalid" : null) ?>" value="<?= $sname ?? null ?>">
                        <div class="invalid-feedback"><?= $errName ?? null ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-check-inline ps-3">
                            <label for="" class="form-check-label">Gender : </label>
                        </div>
                        <div class="from-check form-check-inline">
                            <input type="radio" class="form-check-input <?= (isset($errGender) ? "is-invalid" : null) ?>" name="gender" value="Male" <?= (isset($gender) && $gender == "Male") ? "checked" : null; ?>>
                            <label for="" class="form-check-label">Male</label>
                        </div>
                        <div class="from-check form-check-inline">
                            <input type="radio" class="form-check-input <?= (isset($errGender) ? "is-invalid" : null) ?>" name="gender" value="Female" <?= (isset($gender) && $gender == "Female") ? "checked" : null; ?>>
                            <label for="" class="form-check-label">Female</label>
                        </div>
                        <input type="hidden" class="is-invalid">
                        <div class="invalid-feedback"><?= $errGender ?? null ?></div>
                    </div>
                    <div class="mb-3">
                        <select name="city" id="" class="form-select <?= (isset($errCity) ? "is-invalid" : null) ?>">
                            <option value="<?= (isset($city)) ? $city : null ?>"><?= (isset($city)) ? $city : "--SELECT CITY--" ?></option>
                            <?php foreach ($cityList as $ct) {
                                if ($ct != $city) { ?>
                                    <option value="<?= $ct ?>"><?= $ct ?></option>
                            <?php }
                            } ?>
                        </select>
                        <div class="invalid-feedback">
                            <?= $errCity ?? null ?>
                        </div>
                    </div>

                    <input type="submit" value="Update Student" name="addStudent" class="btn btn-primary">
                </form>
            <?php } ?>
        </div>
    </div>
</body>

</html>