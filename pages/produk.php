<?php
// Masukkan file koneksi.php di bagian atas
include 'koneksi.php';

// Proses tambah produk jika status adalah 'tambah'
if ($_GET['status'] == 'tambah') {
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $nm = $_POST['nama'];

    // Tempat penyimpanan sementara untuk upload gambar
    $file_name = $_FILES['gambar']['name'];
    $file_tmp = $_FILES['gambar']['tmp_name'];
    $file_type = $_FILES['gambar']['type'];
    $file_error = $_FILES['gambar']['error'];
    $file_size = $_FILES['gambar']['size'];

    $file_name = preg_replace('/\s+/', '_', $file_name); // Nama file gambar
    $lokasi_file = "fotoproduk/" . $file_name;

    // Validasi tipe file gambar dan ukuran
    if ($file_type == "image/jpeg" || $file_type == "image/png" || $file_type == "image/jpg" || $file_type == "image/gif") {
        if ($file_size <= 2000000) {
            if (move_uploaded_file($file_tmp, $lokasi_file)) {
                $sql = "INSERT INTO baju (gambar, harga, deskripsi, nama_baju) VALUES ('$lokasi_file', '$harga', '$deskripsi', '$nm')";
                $result = $koneksi->query($sql);
                if ($result) {
                    echo "<script>alert('Data Berhasil di Masukkan!'); document.location.href = 'index.php?page=produk';</script>";
                    exit();
                } else {
                    echo "Error: " . $sql . "<br>" . $koneksi->error;
                }
            } else {
                echo "<script>alert('Gagal Upload File!'); document.location.href = 'index.php?page=tambahproduk';</script>";
            }
        } else {
            echo "<script>alert('File Terlalu Besar!'); document.location.href = 'index.php?page=tambahproduk';</script>";
        }
    } else {
        echo "<script>alert('File Bukan Gambar!'); document.location.href = 'index.php?page=tambahproduk';</script>";
    }
}
if ($_GET['status'] == 'tambahstok') {
    $id_baju = $_POST['id_baju'];
    $ukuran = $_POST['ukuran'];
    $stok = $_POST['stok'];

    // Update stok
    $query = "UPDATE ukuran_baju SET stok = stok + ? WHERE baju_id = ? AND ukuran = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param('iis', $stok, $id_baju, $ukuran);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $message = "Stok berhasil ditambahkan.";
    } else {
        $message = "Gagal menambahkan stok.";
    }

    $stmt->close();
    $koneksi->close();

    // Redirect back to the form with a message
    header("Location: index.php?page=produk&message=" . urlencode($message));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Baju</title>
    <!-- Bootstrap CSS -->
    <!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> -->
    <!-- Custom CSS -->
    <style>
        .card-title {
            font-size: 18px;
            font-weight: bold;
            color: black;
            text-transform: uppercase;
        }

        .text-black-bold-italic {
            font-weight: bold;
            font-style: italic;
            color: black;
        }
    </style>
</head>

<body>

    <div class="container-fluid py-4">
        <?php
        if ($_SESSION['role'] == 'admin') {
        ?>
        <div class="text-center">
            <button class="btn btn-success btn-md" data-toggle="modal" data-target="#tambahProdukModal">Tambah Baju</button>
            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modalForm">Tambah Stok Baju</button>

        </div>
        <?php
        }
        ?>
        <div class="row">
            <?php
            // Ambil data baju dari database
            $sql = "SELECT * FROM baju";
            $result = $koneksi->query($sql);
            while ($row = $result->fetch_array()) {
            ?>
            <div class="col-md-3">
                <div class="ibox">
                    <div class="card" style="width: 250px;">
                        <img src="<?php echo $row['gambar']; ?>" class="card-img-top" alt="" width="150px"
                            height="250px">
                        <div class="card-body">
                            <h5 class="card-title"
                                style="text-transform: uppercase;"><?php echo $row['nama_baju']; ?></h5>
                            <p class="card-text"><?php echo $row['deskripsi']; ?><br>
                                <span class="price-text">Price : <span
                                        class="text-black-bold-italic">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></span></span>
                            </p>

                            <a href="?page=detailproduk&id=<?php echo $row['id_baju']; ?>"
                                class="btn btn-primary">Detail</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            }
            ?>
        </div>
    </div>

    <!-- Modal untuk menambah produk -->
    <div class="modal fade" id="tambahProdukModal" tabindex="-1" role="dialog" aria-labelledby="tambahProdukModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tambahProdukModalLabel">Tambah Produk Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="index.php?page=produk&status=tambah" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nama" class="control-label">Nama</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label for="harga" class="control-label">Harga</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp.</span>
                            </div>
                            <input type="number" class="form-control" id="harga" name="harga" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="deskripsi" class="control-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="gambar" class="control-label">Foto Produk</label>
                        <input type="file" class="form-control-file" id="gambar" name="gambar" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal Tambah Stok -->
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="modalFormLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFormLabel">Tambah Stok Baju</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" class="form-horizontal" action="?page=produk&status=tambahstok" enctype="multipart/form-data">
                    <!-- Isi form Anda -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Nama Baju</label>
                        <div class="col-sm-10">
                            <select class="form-control m-b" name="id_baju" id="id_baju">
                                <?php
                                include "koneksi.php";
                                $a = "SELECT id_baju, nama_baju FROM baju";
                                $b = $koneksi->query($a);
                                while ($ab = $b->fetch_array()) {
                                ?>
                                    <option value="<?php echo $ab['id_baju']; ?>"><?php echo $ab['nama_baju']; ?></option>
                                <?php
                                } ?>
                            </select>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Ukuran</label>
                        <div class="col-sm-10">
                            <select class="form-control m-b" name="ukuran" id="ukuran">
                                <?php
                                $ukuran = ['L','X', 'XL', 'XXL', 'XXXL', 'XXXXL', 'XXXXXL'];
                                foreach ($ukuran as $u) {
                                    echo "<option value='$u'>$u</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Stok</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control" name="stok" placeholder="Masukkan stok">
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


    <!-- Bootstrap JS dan jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>
