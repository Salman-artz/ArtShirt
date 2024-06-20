<?php
// Include the database connection file
include "koneksi.php";

// Check if id_baju is set in the GET request
if (isset($_GET['id'])) {
    $id_baju = $_GET['id'];

    // Prepare the SQL statement to fetch the product details
    $sql = "SELECT * FROM baju WHERE id_baju = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("i", $id_baju);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a product is found
    if ($result->num_rows > 0) {
        // Fetch the product details
        $row = $result->fetch_assoc();
        $nama_baju = $row['nama_baju'];
        $deskripsi = $row['deskripsi'];
        $gambar = $row['gambar'];
        $harga = $row['harga'];
    } else {
        echo "Product not found.";
        exit();
    }

    // Prepare the SQL statement to fetch the sizes and stock
    $sql_sizes = "SELECT id, ukuran, stok FROM ukuran_baju WHERE baju_id = ? AND stok > 0";
    $stmt_sizes = $koneksi->prepare($sql_sizes);
    $stmt_sizes->bind_param("i", $id_baju);
    $stmt_sizes->execute();
    $result_sizes = $stmt_sizes->get_result();

    // Store sizes and stock in an array
    $sizes = [];
    while ($row_size = $result_sizes->fetch_assoc()) {
        $sizes[] = $row_size;
    }
}
if (isset($_GET['id_baju']) AND isset($_GET['status'])=='hapus') {
    $id_baju = $_GET['id_baju'];
    $sql= "DELETE FROM baju where id_baju='$id_baju'";
    $query= $koneksi->query($sql);
    if($query===TRUE){
        echo "<script>alert('Data Terhapus'); document.location.href = '?page=produk';</script>";
} else {
    echo "<script>alert('Data Tidak Terhapus, Req Error!!!'); document.location.href = '?page=produk';</script>";
}
}
if (isset($_GET['id']) AND isset($_GET['status'])=='update') {
    $id = $_GET['id']; // Ambil ID baju yang akan diupdate dari parameter URL

    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $nm = $_POST['nama'];

    // Cek apakah ada file gambar yang diunggah baru
    if ($_FILES['gambar']['name'] != '') {
        // Ada file gambar baru yang diunggah
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
                    // Update data dengan gambar baru
                    $sql = "UPDATE baju SET gambar='$lokasi_file', harga='$harga', deskripsi='$deskripsi', nama_baju='$nm' WHERE id_baju='$id'";
                    $result = $koneksi->query($sql);
                    if ($result) {
                        // Redirect ke halaman produk setelah berhasil diupdate
                        echo "<script>alert('Data Berhasil di Update!'); document.location.href = 'index.php?page=produk';</script>";
                        exit();
                    } else {
                        echo "Error: " . $sql . "<br>" . $koneksi->error;
                    }
                } else {
                    echo "<script>alert('Gagal Upload File!'); document.location.href = 'index.php?page=editproduk&id=$id';</script>";
                }
            } else {
                echo "<script>alert('File Terlalu Besar!'); document.location.href = 'index.php?page=editproduk&id=$id';</script>";
            }
        } else {
            echo "<script>alert('File Bukan Gambar!'); document.location.href = 'index.php?page=editproduk&id=$id';</script>";
        }
    } else {
        // Tidak ada file gambar yang diunggah baru, update tanpa mengubah gambar
        $sql = "UPDATE baju SET harga ='$harga', deskripsi='$deskripsi', nama_baju='$nm' WHERE id_baju='$id'";
        $result = $koneksi->query($sql);
        if ($result) {
            echo "<script>alert('Data Berhasil di Update!'); document.location.href = 'index.php?page=produk';</script>";
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $koneksi->error;
        }
    }
}
if (isset($_GET['idp']) AND isset($_GET['status'])=='tambahkeranjang') {
    $idp = $_GET['idp']; 
    $ids = $_SESSION['iduser'];
    $idk = $_POST['size'];
    $jumlah = $_POST['quantity'];
    $sql = "INSERT INTO keranjang  (iduser,id_baju,id_ukuranbaju,jumlah) VALUES ('$ids', '$idp','$idk', '$jumlah')";
        $result = $koneksi->query($sql);
        if ($result) {
            echo "<script>alert('PRODUK DITAMBAHKAN DI KERANJANG!');document.location.href = 'index.php?page=detailproduk&id= $idp';</script>";
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $koneksi->error;
        }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <!-- Bootstrap CSS -->
    <!-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet"> -->
    <style>
        .product-detail {
            margin-top: 50px;
        }
        .product-image {
            width: 500px;
            height: 500px;
        }
        .size-table {
            margin-top: 20px;
        }
        .size-selection {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .card-body {
            background-color: #fff;
        }
        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container product-detail">
    <?php
        if ($_SESSION['role'] == 'admin') {
        ?>
        <div class="text-center">
            <button class="btn btn-warning btn-md" data-toggle="modal" data-target="#editProdukModal">Edit Baju</button>
            <a href="?page=detailproduk&id_baju=<?php echo $id_baju; ?>&status=hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')" class="btn btn-danger">Hapus Produk</a>
            <!-- <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modalForm">Tambah Stok Baju</button> -->

        </div>
        <?php
        }
        ?>
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <img src="<?php echo htmlspecialchars($gambar); ?>" alt="<?php echo htmlspecialchars($nama_baju); ?>" class="img-fluid product-image">
                            </div>
                            <div class="col-md-6">
                                <h1><?php echo htmlspecialchars($nama_baju); ?></h1>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars($deskripsi); ?></p>
                                <p><strong>Price:</strong> Rp <?php echo number_format($harga, 2, ',', '.'); ?></p>
                                <?php if (!empty($sizes)): ?>
                                    <div class="size-selection">
                                        <h4>Available Sizes</h4>
                                        <form id="cartForm" action="index.php?page=detailproduk&status=tambahkeranjang&idp=<?php echo $_GET['id']?>" method="POST">
                                            <input type="hidden" name="id_baju" value="<?php echo $id_baju; ?>">
                                            <div class="form-group">
                                                <label for="size">Select Size:</label>
                                                <select class="form-control" id="size" name="size" required>
                                                    <?php foreach ($sizes as $size): ?>
                                                        <option value="<?php echo htmlspecialchars($size['id']); ?>" data-stock="<?php echo htmlspecialchars($size['stok']); ?>">
                                                            <?php echo htmlspecialchars($size['ukuran']) . " (Stock: " . htmlspecialchars($size['stok']) . ")"; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="quantity">Quantity:</label>
                                                <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                                            </div>
                                            <div class="button-group">
                                                <button type="submit" id="addToCartButton" class="btn btn-primary">Add to Cart</button>
                                                <a href="?page=produk" class="btn btn-secondary">Back to Produk</a>
                                            </div>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <p class="text-danger mt-3">Sorry, no sizes are available for this product.</p>
                                    <a href="?page=produk" class="btn btn-secondary">Back to Produk</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- MODAL EDIT PRODUL -->
    <div class="modal fade" id="editProdukModal" tabindex="-1" role="dialog" aria-labelledby="editProdukModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editProdukModalLabel">Edit Produk</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="index.php?page=detailproduk&status=update&id=<?php echo $_GET['id']?>" enctype="multipart/form-data">
<?php
$id = $_GET['id'];
$a = "SELECT * FROM baju WHERE id_baju = $id";
$result = $koneksi->query($a);
while ($row = $result->fetch_array()) {
?>
                    <div class="form-group">
                        <label for="nama" class="control-label">Nama</label>
                        <input type="text" class="form-control" id="nama" name="nama" value="<?php echo $row['nama_baju']?>"required>
                    </div>
                    <div class="form-group">
                        <label for="harga" class="control-label">Harga</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp.</span>
                            </div>
                            <input type="number" class="form-control" id="harga" name="harga" value="<?php echo $row['harga']?>"required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="deskripsi" class="control-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required><?php echo $row['deskripsi']?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="gambar" class="control-label">Foto Produk</label>
                        <input type="file" class="form-control-file" id="gambar" name="gambar" value="<?php echo $row['gambar']?>">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
<?php
}
?>
                </form>
            </div>
        </div>
    </div>
</div>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#cartForm').on('submit', function(e) {
                var selectedOption = $('#size option:selected');
                var stock = parseInt(selectedOption.data('stock'));
                var quantity = parseInt($('#quantity').val());

                if (quantity > stock) {
                    alert('Quantity exceeds available stock!');
                    e.preventDefault();
                }
            });

            // Check if no sizes are available and disable the Add to Cart button
            if ($('#size option').length === 0) {
                $('#addToCartButton').hide();
            }
        });
    </script>

</body>
</html>
