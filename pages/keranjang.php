<?php
include "koneksi.php";

if (isset($_GET['checkout'])=="out") {
    // Mengarahkan ke form_transaksi.php setelah memilih item untuk checkout
    $checkout_items = $_POST['checkout_items'];

    $_SESSION['checkout_items'] = $checkout_items;

    header("Location: index.php?page=form_transaksi ");
    exit;
}

elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $change = (int)$_POST['change'];

    // Fetch current quantity and stock from ukuran_baju table
    $sql = "SELECT keranjang.jumlah, ukuran_baju.stok 
            FROM keranjang 
            JOIN ukuran_baju ON keranjang.id_ukuranbaju = ukuran_baju.id 
            WHERE keranjang.id_keranjang = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($current_quantity, $stock);
    $stmt->fetch();
    $stmt->close();

    $new_quantity = $current_quantity + $change;
    if ($new_quantity < 1) {
        $new_quantity = 1; // Ensure quantity does not go below 1
    } elseif ($new_quantity > $stock) {
        echo json_encode(['success' => false, 'message' => 'Quantity exceeds stock limit']);
        exit;
    }

    // Update the quantity in the database
    $sql = "UPDATE keranjang SET jumlah = ? WHERE id_keranjang = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param('ii', $new_quantity, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'new_quantity' => $new_quantity]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating quantity']);
    }

    $stmt->close();
    $koneksi->close();
    exit;
}
if (isset($_GET['idk'])&isset($_GET['status'])=="hapus") {
    $idk = $_GET['idk'];
        $sql= "DELETE FROM keranjang where id_keranjang='$idk'";
        $query= $koneksi->query($sql);
        if($query===TRUE){
            echo "<script>alert('Keranjang Terhapus'); document.location.href = '?page=keranjang';</script>";
    } else {
        echo "<script>alert('Keranjang Tidak Terhapus, Req Error!!!'); document.location.href = '?page=keranjang';</script>";
    }
}
session_start();
$idu = $_SESSION['iduser'];

if (isset($_GET['checkout'])=="out") {
    // Mengarahkan ke form_transaksi.php setelah memilih item untuk checkout
    $checkout_items = $_POST['checkout_items'];

    $_SESSION['checkout_items'] = $checkout_items;

    header("Location: form_transaksi.php");
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <title>Keranjang Belanja</title>
    <style>
    .span{
        font-size: 18px;
        text-align: center;
        font-family: Arial, Helvetica, sans-serif;
    }
    .quantity {
        display: inline-block;
        width: 50px;
        height: 50px;
        line-height: 30px;
        text-align: center;
        vertical-align: middle;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 18px;
        padding-bottom: 15px;
        padding-top: 15px;
    }

    .quantity-btn {
        width: 30px;
        height: 30px;
        padding: 0;
        font-size: 30px;
        line-height: 30px;
        text-align: center;
        display: inline-block;
        vertical-align text-middle;
    }
    .btnc{
        float:right;
        margin: 10px 10px 10px 10px;
    }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Cart Table</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <form action="?page=keranjang&checkout=out" method="POST">
                            <table class="table align-items-center mb-0">
                                <thead>
                                <tr>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7"><input type="checkbox" id="select-all"> Pilih Semua</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Produk</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ukuran</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Jumlah</th>
                                    <th class="text-secondary opacity-7"></th>
                                    
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                include "koneksi.php";
                                $idu = $_SESSION['iduser'];
                                $a = "SELECT keranjang.*, baju.gambar, baju.nama_baju, ukuran_baju.ukuran, ukuran_baju.stok
                                      FROM keranjang 
                                      JOIN baju ON baju.id_baju = keranjang.id_baju 
                                      JOIN ukuran_baju ON ukuran_baju.id = keranjang.id_ukuranbaju 
                                      WHERE keranjang.iduser = ? 
                                      AND keranjang.id_keranjang NOT IN (SELECT id_keranjang FROM transaksi)";
                                $stmt = $koneksi->prepare($a);
                                $stmt->bind_param('i', $idu);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                while ($aa = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td class="align-middle text-center">
                                            <input type="checkbox" name="checkout_items[]" value="<?php echo $aa['id_keranjang'] ?>">
                                        </td>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <img src="<?php echo $aa['gambar'] ?>" class="rounded" alt="" width="50px" height="60px">
                                                    <h6 class="text-uppercase"><?php echo $aa['nama_baju'] ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <span class="badge badge-md bg-gradient-success span"><?php echo $aa['ukuran'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-secondary change-quantity quantity-btn" data-id="<?php echo $aa['id_keranjang'] ?>" data-change="-1">-</button>
                                            <span class="text-secondary text-xs font-weight-bold quantity" id="quantity-<?php echo $aa['id_keranjang'] ?>"><b><?php echo $aa['jumlah'] ?></b></span>
                                            <button class="btn btn-sm btn-secondary change-quantity quantity-btn" data-id="<?php echo $aa['id_keranjang'] ?>" data-change="1">+</button>
                                        </td>
                                        <td class="align-middle">
                                            <a href="?page=keranjang&status=hapus&idk=<?php echo $aa['id_keranjang']?>" class="text-danger font-weight-bold text-xs">Hapus</a>
                                        </td>
                                        
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary btnc">Checkout</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Optional JavaScript; choose one of the two! -->
    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).on('click', '.change-quantity', function (e) {
            e.preventDefault();
            var button = $(this);
            var id = button.data('id');
            var change = button.data('change');
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    id: id,
                    change: change
                },
                success: function (response) {
                    var data = JSON.parse(response);
                    if (data.success) {
                        location.reload(); // Refresh the page on successful update
                    } else {
                        alert(data.message); // Show alert message if there is an error
                    }
                }
            });
        });

        // Select All functionality
        $('#select-all').click(function() {
            $('input[name="checkout_items[]"]').prop('checked', this.checked);
        });
    </script>
</body>
</html>
