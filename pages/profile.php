<?php
include 'koneksi.php';
session_start();

if ($_GET['status']=="update") {
    $id = $_SESSION['iduser'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $alamat = $_POST['alamat'];
    $notelp = $_POST['notelp'];

    $sql = "UPDATE user SET 
                nama = '$nama', 
                email = '$email', 
                password = '$password', 
                alamat = '$alamat', 
                notelp = '$notelp' 
            WHERE iduser = $id";

    if ($koneksi->query($sql) === TRUE) {
        echo "<script>alert('Update Berhasil'); document.location.href = 'index.php?page=profile';</script>";
    } else {
        echo "Error updating record: " . $koneksi->error;
    }

    $koneksi->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>
    Argon Dashboard 2 by Creative Tim
  </title>
  <style>
         .form-group {
            margin-bottom: 15px;
        }
    </style>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <!-- Nucleo Icons -->
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- CSS Files -->
  <link id="pagestyle" href="../assets/css/argon-dashboard.css?v=2.0.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
</div>
    <div class="card shadow-lg mx-4 card-profile-bottom">
      <div class="card-body p-2">
        <div class="row gx-4">
          <div class="col-auto">
            <div class="avatar avatar-xl position-relative">
              <img src="../assets/img/haha.jpeg" alt="profile_image" class="w-100 border-radius-lg shadow-sm">
            </div>
          </div>
          <div class="col-auto my-auto">
            <div class="h-100">
              <h5 class="mb-1 "style="text-transform: uppercase;">
                <?php echo $_SESSION['nama'];?>
              </h5>
              <p class="mb-0 font-weight-bold text-sm"style="text-transform: uppercase;">
              <?php echo $_SESSION['role'];?>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
     <?php
     include 'koneksi.php' ;
$id = $_SESSION['iduser'];
$a = "SELECT * FROM user WHERE iduser = $id";
$result = $koneksi->query($a);
while ($aa = $result->fetch_array()) {
?> 
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header pb-0">
                <div class="d-flex justify-content-end">
                    <a href="#" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</a>
                </div>
            </div>
            <div class="card-body">
              <p class="text-uppercase text-sm">User Information</p>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="example-text-input" class="form-control-label">Nama</label>
                    <input class="form-control" type="text" value="<?php echo $aa['nama']?>" disabled>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="example-text-input" class="form-control-label">Email</label>
                    <input class="form-control" type="email" value="<?php echo $aa['email']?>" disabled>
                  </div>
                </div>
                <div class="col-md-6">
                <div class="form-group">
                    <label for="example-text-input" class="form-control-label">Password</label>
                    <input id="password" class="form-control" type="password" value="<?php echo $aa['password']?>" disabled>
                </div>
                <div class="form-group">
                <input type="checkbox" id="show-password" onclick="togglePassword()">
                <label for="show-password">Lihat Password</label>
            </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="example-text-input" class="form-control-label">Role</label>
                    <input class="form-control" type="text" value="<?php echo $aa['role']?>"disabled>
                  </div>
                </div>
              </div>
              <hr class="horizontal dark">
              <p class="text-uppercase text-sm">Contact Information</p>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label for="example-text-input" class="form-control-label">Alamat</label>
                    <input class="form-control" type="text" value="<?php echo $aa['alamat']?>"disabled>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="example-text-input" class="form-control-label">Nomor Telepon</label>
                    <input class="form-control" type="number" value="<?php echo $aa['notelp']?>"disabled>
                  </div>
                </div>
              </div>
          </div>
        </div>
        <?php
}
?> 




<?php
include 'koneksi.php';
$id = $_SESSION['iduser'];
$a = "SELECT * FROM user WHERE iduser = $id";
$result = $koneksi->query($a);
while ($aa = $result->fetch_array()) {
?>

<!-- Modal Structure -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="index.php?page=profile&status=update">
        <div class="modal-body">
          <p class="text-uppercase text-sm">User Information</p>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="nama" class="form-control-label">Nama</label>
                <input class="form-control" id="nama" name="nama" type="text" value="<?php echo $aa['nama']?>" >
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="email" class="form-control-label">Email</label>
                <input class="form-control" id="email" name="email" type="email" value="<?php echo $aa['email']?>" >
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="password1" class="form-control-label">Password</label>
                <input id="password1" name="password" class="form-control" type="password" value="<?php echo $aa['password']?>" >
              </div>
              <div class="form-group">
                <input type="checkbox" id="show-password1" onclick="togglePassword1()">
                <label for="show-password1">Lihat Password</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="role" class="form-control-label">Role</label>
                <input class="form-control" id="role" type="text" value="<?php echo $aa['role']?>" disabled>
              </div>
            </div>
          </div>
          <hr class="horizontal dark">
          <p class="text-uppercase text-sm">Contact Information</p>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="alamat" class="form-control-label">Alamat</label>
                <input class="form-control" id="alamat" name="alamat" type="text" value="<?php echo $aa['alamat']?>" >
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="notelp" class="form-control-label">Nomor Telepon</label>
                <input class="form-control" id="notelp" name="notelp" type="number" value="<?php echo $aa['notelp']?>" >
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
function togglePassword1() {
  var passwordField = document.getElementById("password1");
  var showPasswordCheckbox = document.getElementById("show-password1");
  if (showPasswordCheckbox.checked) {
    passwordField.type = "text";
  } else {
    passwordField.type = "password";
  }
}
</script>
<?php
}
?>


  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <script>
        function togglePassword() {
            var passwordField = document.getElementById("password");
            if (passwordField.type === "password") {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        }
    </script>
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/argon-dashboard.min.js?v=2.0.0"></script>
</body>

</html>