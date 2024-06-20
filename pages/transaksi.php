<?php
session_start();
include "koneksi.php";

// Fetch event data once
$eventData = [];
$eventQuery = "SELECT * FROM event";
$eventResult = $koneksi->query($eventQuery);
while ($eventRow = $eventResult->fetch_array()) {
    $eventData[$eventRow['id_event']] = $eventRow['diskon'];
}

// Determine the sorting order
$order = isset($_POST['order']) ? $_POST['order'] : 'DESC';
$idu = $_SESSION['iduser'];

// Main query
$query = $_SESSION['role'] == 'admin' ? "
    SELECT transaksi.*, keranjang.*, baju.*, ukuran_baju.ukuran, user.nama AS nama_user
    FROM transaksi
    JOIN keranjang ON transaksi.id_keranjang = keranjang.id_keranjang
    JOIN baju ON keranjang.id_baju = baju.id_baju
    JOIN ukuran_baju ON ukuran_baju.id = keranjang.id_ukuranbaju
    JOIN user ON keranjang.iduser = user.iduser
    ORDER BY transaksi.tanggal_transaksi $order" : "
    SELECT transaksi.*, keranjang.*, baju.*, ukuran_baju.ukuran, user.nama AS nama_user
    FROM transaksi
    JOIN keranjang ON transaksi.id_keranjang = keranjang.id_keranjang
    JOIN baju ON keranjang.id_baju = baju.id_baju
    JOIN ukuran_baju ON ukuran_baju.id = keranjang.id_ukuranbaju
    JOIN user ON keranjang.iduser = user.iduser
    WHERE keranjang.iduser = $idu
    ORDER BY transaksi.tanggal_transaksi $order";

$result = $koneksi->query($query);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['status'])) {
    $id_transaksi = $_POST['id_transaksi'];
    
    if ($_GET['status'] == 'edit') {
        $status = $_POST['status'];

        // Update the transaction status in the database
        $updateQuery = "UPDATE transaksi SET status_transaksi = ? WHERE id_transaksi = ?";
        $stmt = $koneksi->prepare($updateQuery);
        $stmt->bind_param("si", $status, $id_transaksi);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Update Transaksi Berhasil.";
        } else {
            $_SESSION['message'] = "Gagal Update Transaksi.";
        }

        $stmt->close();
        header("Location: index.php?page=transaksi");
        exit();
    } if ($_GET['status'] == 'delete') {
        // Mendapatkan id_transaksi dari POST request
        $id_transaksi = $_POST['id_transaksi'];
    
        // Query untuk mendapatkan id_keranjang berdasarkan id_transaksi
        $getKeranjangQuery = "SELECT id_keranjang FROM transaksi WHERE id_transaksi = ?";
        $stmt = $koneksi->prepare($getKeranjangQuery);
        $stmt->bind_param("i", $id_transaksi);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $id_keranjang = $row['id_keranjang'];
        $stmt->close();
    
        if ($id_keranjang) {
            // Delete the transaction from the database
            $deleteQuery1 = "DELETE FROM transaksi WHERE id_transaksi = ?";
            $deleteQuery2 = "DELETE FROM keranjang WHERE id_keranjang = ?";
    
            // Prepare and execute the first delete statement
            $stmt1 = $koneksi->prepare($deleteQuery1);
            $stmt1->bind_param("i", $id_transaksi);
    
            if ($stmt1->execute()) {
                // If the first delete is successful, prepare and execute the second delete statement
                $stmt2 = $koneksi->prepare($deleteQuery2);
                $stmt2->bind_param("i", $id_keranjang);
    
                if ($stmt2->execute()) {
                    $_SESSION['message'] = "Transaksi dan keranjang berhasil dihapus.";
                } else {
                    $_SESSION['message'] = "Gagal menghapus keranjang.";
                }
    
                $stmt2->close();
            } else {
                $_SESSION['message'] = "Gagal menghapus transaksi.";
            }
    
            $stmt1->close();
        } else {
            $_SESSION['message'] = "Keranjang tidak ditemukan untuk transaksi ini.";
        }
    
        header("Location: index.php?page=transaksi");
        exit();
    }
    
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
    .bg-gradient-success {
        background: linear-gradient(90deg, rgba(0,255,0,1) 0%, rgba(0,128,0,1) 100%);
    }

    .span {
        display: inline-block;
        padding: 0.25em 0.4em;
        font-size: 75%;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.375rem;
    }

    tbody.text-center td {
        text-align: center;
    }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Table Transaksi</h6>
                    <!-- <div>
                        <button id="sort-newest" class="btn btn-success btn-sm">Sort Terbaru</button>
                        <button id="sort-oldest" class="btn btn-secondary btn-sm">Sort Lama</button>
                    </div> -->
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <form action="?page=keranjang&checkout=out" method="POST">
                            <table class="table align-items-center mb-0 ">
                                <thead class="text-center">
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Produk</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal dan Jam</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ukuran</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Harga</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Jumlah</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Diskon</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                                    <th class="text-start text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status Transaksi</th>
<?php
if ($_SESSION['role'] == 'admin') {
?>
                                    <th class="text-secondary opacity-7"></th>
<?php
}
?>
                                </tr>
                                </thead>
                                <tbody >
                                <?php
                                while ($row = $result->fetch_array()) {
                                    $total = $row['harga'] * $row['jumlah'];
                                    if (!empty($row['kode_event']) && isset($eventData[$row['kode_event']])) {
                                        $discount = $eventData[$row['kode_event']] / 100;
                                        $total -= $total * $discount;
                                    }
                                    ?>
                                    <tr >
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6><?php echo $row['nama_user'] ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <img src="<?php echo $row['gambar'] ?>" class="rounded" alt="" width="50px" height="60px">
                                                    <h6 class="text-uppercase"><?php echo $row['nama_baju'] ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6><?php echo $row['tanggal_transaksi'] ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <span class="badge badge-sm bg-gradient-success span"><?php echo $row['ukuran'] ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center ">
                                                    <h6>Rp. <?php echo number_format($row['harga'], 0, ',', '.') ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center ">
                                                    <h6>&emsp;&ensp;<?php echo $row['jumlah'] ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6>&emsp;<?php echo isset($eventData[$row['kode_event']]) ? $eventData[$row['kode_event']] : 0 ?>%</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6>&emsp;&ensp;Rp. <?php echo number_format($total, 0, ',', '.') ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center text-start">
                                                    <h6>&emsp;<?php echo $row['status_transaksi'] ?></h6>
                                                </div>
                                            </div>
                                        </td>
<?php
if ($_SESSION['role'] == 'admin') {
?>
                                        <td class="align-middle">
                                            <a href="#" class="text-warning font-weight-bold text-xs edit-btn" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $row['id_transaksi']; ?>" data-nama="<?php echo $row['nama_user']; ?>" data-produk="<?php echo $row['nama_baju']; ?>" data-tanggal="<?php echo $row['tanggal_transaksi']; ?>" data-ukuran="<?php echo $row['ukuran']; ?>" data-harga="<?php echo $row['harga']; ?>" data-jumlah="<?php echo $row['jumlah']; ?>" data-diskon="<?php echo isset($eventData[$row['kode_event']]) ? $eventData[$row['kode_event']] : 0; ?>" data-total="<?php echo $total; ?>" data-status="<?php echo $row['status_transaksi']; ?>">Edit</a>
                                            <a href="#" class="text-danger font-weight-bold text-xs delete-btn" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php echo $row['id_transaksi']; ?>" >Hapus</a>
                                        </td>
<?php
}
?>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="?page=transaksi&status=edit" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_transaksi" id="edit-id">
                        <div class="mb-3">
                            <label for="edit-produk" class="form-label">Produk</label>
                            <input type="text" class="form-control" id="edit-produk" name="produk" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit-tanggal" class="form-label">Tanggal dan Jam</label>
                            <input type="text" class="form-control" id="edit-tanggal" name="tanggal" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit-ukuran" class="form-label">Ukuran</label>
                            <input type="text" class="form-control" id="edit-ukuran" name="ukuran" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit-harga" class="form-label">Harga</label>
                            <input type="text" class="form-control" id="edit-harga" name="harga" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit-jumlah" class="form-label">Jumlah</label>
                            <input type="text" class="form-control" id="edit-jumlah" name="jumlah" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit-diskon" class="form-label">Diskon</label>
                            <input type="text" class="form-control" id="edit-diskon" name="diskon" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit-total" class="form-label">Total</label>
                            <input type="text" class="form-control" id="edit-total" name="total" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit-status" class="form-label">Status Transaksi</label>
                            <select class="form-select" id="edit-status" name="status">
                                <option value="Menunggu">Menunggu</option>
                                <option value="Selesai">Selesai</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus transaksi ini?
                </div>
                <div class="modal-footer">
                    <form action="?page=transaksi&status=delete" method="POST">
                        <input type="hidden" name="id_transaksi" id="delete-id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Optional JavaScript; choose one of the two! -->
    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function () {
        $('#sort-newest').click(function () {
            $.post('transaksi.php', {order: 'DESC'}, function (response) {
                $('#transaction-table-body').html($(response).find('#transaction-table-body').html());
            });
        });

        $('#sort-oldest').click(function () {
            $.post('transaksi.php', {order: 'ASC'}, function (response) {
                $('#transaction-table-body').html($(response).find('#transaction-table-body').html());
            });
        });

        $(document).on('click', '.edit-btn', function() {
            var id = $(this).data('id');
            var produk = $(this).data('produk');
            var tanggal = $(this).data('tanggal');
            var ukuran = $(this).data('ukuran');
            var harga = $(this).data('harga');
            var jumlah = $(this).data('jumlah');
            var diskon = $(this).data('diskon');
            var total = $(this).data('total');
            var status = $(this).data('status');

            $('#edit-id').val(id);
            $('#edit-produk').val(produk);
            $('#edit-tanggal').val(tanggal);
            $('#edit-ukuran').val(ukuran);
            $('#edit-harga').val(harga);
            $('#edit-jumlah').val(jumlah);
            $('#edit-diskon').val(diskon);
            $('#edit-total').val(total);
            $('#edit-status').val(status);
        });

        $(document).on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            $('#delete-id').val(id);
        });
    });
    </script>
</div>
</body>
</html>
