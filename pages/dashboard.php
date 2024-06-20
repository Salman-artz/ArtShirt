<?php
include 'koneksi.php';

$today = date('Y-m-d');

// Fetch today's event
$sql = "SELECT * FROM event WHERE tanggal_event = '$today'";
$result = $koneksi->query($sql);

$event = null;
if ($result->num_rows > 0) {
    $event = $result->fetch_assoc();
}

// Fetch the latest 3 products
$sql = "SELECT * FROM baju ORDER BY id_baju DESC LIMIT 3";
$result = $koneksi->query($sql);
$latest_products = [];
while ($row = $result->fetch_assoc()) {
    $latest_products[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Event Dashboard</title>
    <style>
       .dashboard {
            justify-content: center;
            background-color: #00FFFF;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 300px;
            text-align: center;
        }
        .dashboard h1 {
            font-size: 24px;
            color: #333;
        }
        .dashboard p {
            font-size: 18px;
            color: #666;
        }
        .event-id {
            font-size: 20px;
            font-weight: bold;
            color: #007BFF;
        }
        .discount {
            font-size: 20px;
            color: #28a745;
        }
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
    <div class="dashboard">
        <h1>Event Hari Ini</h1>
        <?php if ($event): ?>
            <p class="event-id"><?php echo $event['id_event']; ?></p>
            <p>Tanggal: <?php echo $event['tanggal_event']; ?></p>
            <p class="discount">Diskon: <?php echo $event['diskon']; ?>%</p>
            <small>*Masukkan kode event saat transaksi</small>
        <?php else: ?>
            <p>No event today.</p>
        <?php endif; ?>
    </div>
    <hr>
    <hr>
    <div class="row">
        <?php foreach ($latest_products as $product): ?>
            <div class="col-md-3">
                <div class="ibox">
                    <div class="card">
                        <img src="<?php echo $product['gambar']; ?>" class="card-img-top" alt="" width="150px"height="250px">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $product['nama_baju']; ?></h5>
                            <p class="card-text">
                                <!-- <?php echo $product['deskripsi']; ?><br> -->
                                <span class="price-text">Harga: Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></span>
                            </p>
                            <a href="?page=detailproduk&id=<?php echo $product['id_baju']; ?>" class="btn btn-primary">Detail</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
