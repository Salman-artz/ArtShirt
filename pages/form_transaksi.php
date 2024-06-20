<?php
// form_transaksi.php

include "koneksi.php";

session_start();
$idu = $_SESSION['iduser'];
$id_keranjang = $_SESSION['checkout_items'];
$metode_pembayaran = $_POST['metode_pembayaran'];
$status_transaksi = "Menunggu";
$kode_event = $_POST['kode_event'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Set timezone to Jakarta
    date_default_timezone_set('Asia/Jakarta');
    $today = date('Y-m-d');

    // Validate kode_event if not empty
    if (!empty($kode_event)) {
        $stmt = $koneksi->prepare("SELECT tanggal_event FROM event WHERE id_event = ?");
        $stmt->bind_param('s', $kode_event);
        $stmt->execute();
        $stmt->bind_result($tanggal_event);
        $stmt->fetch();
        $stmt->close();

        // If the event date doesn't match today's date, set kode_event to empty
        if ($tanggal_event !== $today) {
            $kode_event = '';
        }
    }

    $koneksi->begin_transaction(); // Mulai transaksi

    try {
        foreach ($id_keranjang as $id) {
            // Ambil id_ukuranbaju dan jumlah dari keranjang
            $stmt_keranjang = $koneksi->prepare("SELECT id_ukuranbaju, jumlah FROM keranjang WHERE id_keranjang = ?");
            $stmt_keranjang->bind_param('i', $id);
            $stmt_keranjang->execute();
            $stmt_keranjang->bind_result($id_ukuranbaju, $jumlah);
            $stmt_keranjang->fetch();
            $stmt_keranjang->close();

            // Ambil stok dari ukuran_baju yang sesuai
            $stmt_stok = $koneksi->prepare("SELECT stok FROM ukuran_baju WHERE id = ?");
            $stmt_stok->bind_param('i', $id_ukuranbaju);
            $stmt_stok->execute();
            $stmt_stok->bind_result($stok);
            $stmt_stok->fetch();
            $stmt_stok->close();

            // Cek apakah stok mencukupi
            if ($jumlah > $stok) {
                // Jika stok tidak mencukupi, rollback transaksi dan beri pesan error
                $koneksi->rollback();
                echo "<script>alert('Transaksi ditolak karena stok produk tidak mencukupi pesanan yang anda minta'); window.location.href = 'index.php?page=keranjang';</script>";
                exit;
            }

            // Insert ke tabel transaksi
            if (empty($kode_event)) {
                // Jika kode_event kosong
                $stmt_transaksi = $koneksi->prepare("INSERT INTO transaksi (id_keranjang, tanggal_transaksi, metode_pembayaran, status_transaksi) VALUES (?, NOW(), ?, ?)");
                $stmt_transaksi->bind_param('iss', $id, $metode_pembayaran, $status_transaksi);
            } else {
                // Jika kode_event tidak kosong
                $stmt_transaksi = $koneksi->prepare("INSERT INTO transaksi (id_keranjang, kode_event, tanggal_transaksi, metode_pembayaran, status_transaksi) VALUES (?, ?, NOW(), ?, ?)");
                $stmt_transaksi->bind_param('isss', $id, $kode_event, $metode_pembayaran, $status_transaksi);
            }

            $stmt_transaksi->execute();
            $stmt_transaksi->close();
        }

        $koneksi->commit(); // Commit transaksi
        echo "<script>alert('Transaksi berhasil ditambahkan!'); window.location.href = 'index.php?page=transaksi';</script>";
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        $koneksi->rollback();
        echo "<script>alert('Error adding transaction: " . $e->getMessage() . "'); window.location.href = 'index.php?page=keranjang';</script>";
    }

    $koneksi->close();
    exit;
}

// Mengambil item keranjang untuk ditampilkan dalam form
$sql = "SELECT keranjang.id_keranjang AS id, baju.nama_baju AS name, ukuran_baju.ukuran AS size,
 keranjang.jumlah FROM keranjang
JOIN baju ON baju.id_baju = keranjang.id_baju
JOIN ukuran_baju ON ukuran_baju.id = keranjang.id_ukuranbaju
WHERE keranjang.id_keranjang IN (" . implode(",", $_SESSION['checkout_items']) . ")";
$result = $koneksi->query($sql);

$cartItems = [];
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
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
    <title>Tambah Transaksi</title>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-6 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Add Transaction</h5>
                    </div>
                    <div class="card-body">
                        <form action="form_transaksi.php" method="POST">
                            <div class="mb-3">
                                <label for="id_keranjang" class="form-label">Cart Items</label>
                                <div id="cart-ids">
                                    <?php foreach ($cartItems as $item): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="id_keranjang[]" value="<?php echo $item['id']; ?>" checked>
                                            <label class="form-check-label"><?php echo $item['name'] . ' (' . $item['size'] . ')'.'  Jumlah : ' . $item['jumlah'] . ''; ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="kode_event" class="form-label">Kode Event</label>
                                <input type="text" class="form-control" id="kode_event" name="kode_event">
                            </div>
                            <div class="mb-3">
                                <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
                                <select class="form-select" id="metode_pembayaran" name="metode_pembayaran" required>
                                    <option value="COD">COD</option>
                                    <option value="TRANSFER BRI">TRANSFER BRI (007430768920)</option>
                                    <option value="TRANSFER DANA">TRANSFER DANA (082142296293)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Transaction</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Select/Deselect all checkboxes
            $('#select-all').click(function() {
                $('input[name="id_keranjang[]"]').prop('checked', this.checked);
            });
        });
    </script>
</body>
</html>
