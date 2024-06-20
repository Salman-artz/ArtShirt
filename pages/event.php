<?php
include 'koneksi.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    if ($action == 'add') {
        $id_event = $_POST['id_event'];
        $tanggal_event = $_POST['tanggal_event'];
        $diskon = $_POST['diskon'];

        $sql = "INSERT INTO event (id_event, tanggal_event, diskon) VALUES ('$id_event', '$tanggal_event', '$diskon')";

        $response = ['success' => false];
        if ($koneksi->query($sql) === TRUE) {
            $response['success'] = true;
        } else {
            if ($koneksi->errno == 1062) { 
                $response['message'] = "Kode Event Sudah Ada, Ganti Liyane.";
            } else {
                $response['message'] = "Error: " . $sql . "<br>" . $koneksi->error;
            }
        }

        echo json_encode($response);
        exit;
    }

    if ($action == 'edit') {
        $id_event = $_POST['id_event'];
        $tanggal_event = $_POST['tanggal_event'];
        $diskon = $_POST['diskon'];

        $sql = "UPDATE event SET tanggal_event='$tanggal_event', diskon='$diskon' WHERE id_event='$id_event'";

        $response = ['success' => false];
        if ($koneksi->query($sql) === TRUE) {
            $response['success'] = true;
        } else {
            $response['message'] = "Error: " . $sql . "<br>" . $koneksi->error;
        }

        echo json_encode($response);
        exit;
    }

    if ($action == 'delete') {
        $id_event = $_POST['id_event'];

        $sql = "DELETE FROM event WHERE id_event='$id_event'";

        $response = ['success' => false];
        if ($koneksi->query($sql) === TRUE) {
            $response['success'] = true;
        } else {
            $response['message'] = "Error: " . $sql . "<br>" . $koneksi->error;
        }

        echo json_encode($response);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Table</title>
    <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> -->
    <style>
        .btnadd {
            margin: 10px 10px 10px 10px;
            float: right;
        }
        .btn {
            margin: 10px 10px 10px 10px;
            float: center;
        }
        .input-group-text {
            font-size: 14px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Events Table</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kode Event</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Diskon</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                            </tr>
                            </thead>
                            <tbody id="event-table-body">
                            <?php
                            include 'koneksi.php';
                            $sql = "SELECT id_event, tanggal_event, diskon FROM event";
                            $result = $koneksi->query($sql);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class=""><?php echo $row['id_event'] ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <span class="text-secondary text-xs font-weight-bold"><?php echo $row['tanggal_event'] ?></span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-secondary text-xs font-weight-bold"><?php echo $row['diskon'] ?>%</span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <button class="btn btn-warning" onclick="showEditModal('<?php echo $row['id_event'] ?>', '<?php echo $row['tanggal_event'] ?>', <?php echo $row['diskon'] ?>)">Edit</button>
                                            <button class="btn btn-danger" onclick="showDeleteModal('<?php echo $row['id_event'] ?>')">Delete</button>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-primary btnadd" onclick="showAddModal()">Add Event</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Tambah Event</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="add-event-form">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="add_id_event">Kode Event:</label>
                        <input type="text" class="form-control" id="add_id_event" name="id_event" maxlength="10" required>
                    </div>
                    <div class="form-group">
                        <label for="add_tanggal_event">Tanggal Event:</label>
                        <input type="date" class="form-control" id="add_tanggal_event" name="tanggal_event" required>
                    </div>
                    <div class="form-group">
                        <label for="add_diskon">Diskon:</label>
                        <div class="input-group">
                                <input type="number" class="form-control" id="add_diskon" name="diskon" required>
                                <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                            </div>
                    </div>
                    <button type="button" class="btn btn-secondary " data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btnadd">Add</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editEventModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Event</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-event-form">
                    <input type="hidden" name="action" value="edit">
                    <div class="form-group">
                        <label for="edit_id_event">Kode Event:</label>
                        <input type="text" class="form-control" id="edit_id_event" name="id_event" maxlength="10" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_tanggal_event">Tanggal Event:</label>
                        <input type="date" class="form-control" id="edit_tanggal_event" name="tanggal_event" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_diskon">Diskon:</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="edit_diskon" name="diskon" required>
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary " data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btnadd">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Hapus Event</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Yakin Hapus Event
            </div>
            <div class="modal-footer">
                <!-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button> -->
                <button type="button" class="btn btn-danger btnadd" id="confirm-delete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    const eventTableBody = document.getElementById('event-table-body');
    const addEventModal = $('#addEventModal');
    const editEventModal = $('#editEventModal');
    const deleteModal = $('#deleteModal');
    let deleteEventKode = '';

    function showAddModal() {
        $('#add-event-form')[0].reset();
        addEventModal.modal('show');
    }

    function showEditModal(id_event, tanggal_event, diskon) {
        $('#edit_id_event').val(id_event);
        $('#edit_tanggal_event').val(tanggal_event);
        $('#edit_diskon').val(diskon);
        editEventModal.modal('show');
    }

    function showDeleteModal(id_event) {
        deleteEventKode = id_event;
        deleteModal.modal('show');
    }

    function fetchEvents() {
        $.get('event.php', function(data) {
            eventTableBody.innerHTML = '';
            data.forEach(event => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${event.id_event}</td>
                    <td>${event.tanggal_event}</td>
                    <td>${event.diskon}</td>
                    <td class="text-center">
                        <button class="btn btn-secondary" onclick="showEditModal('${event.id_event}', '${event.tanggal_event}', ${event.diskon})">Edit</button>
                        <button class="btn btn-danger" onclick="showDeleteModal('${event.id_event}')">Delete</button>
                    </td>
                `;
                eventTableBody.appendChild(row);
            });
        }, 'json');
    }
    $('#add-event-form').on('submit', function(event) {
        event.preventDefault();
        const formData = $(this).serialize();

        $.post('event.php', formData, function(data) {
            if (data.success) {
                alert('Tambah Event Berhasil');
                location.reload();
            } else {
                if (data.message === "Kode Event Sudah Ada.") {
                    alert('Error: Kode Event Sudah Ada.');
                } else {
                    alert('An error occurred: ' + data.message);
                }
            }
        }, 'json');
    });

    $('#edit-event-form').on('submit', function(event) {
        event.preventDefault();
        const formData = $(this).serialize();

        $.post('event.php', formData, function(data) {
            if (data.success) {
                alert('Update Event Berhasi');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        }, 'json');
    });

    $('#confirm-delete').on('click', function() {
        $.post('event.php', { action: 'delete', id_event: deleteEventKode }, function(data) {
            if (data.success) {
                alert('Hapus Event Berhasil');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        }, 'json');
    });

    $(document).ready(function() {
        fetchEvents();
    });
</script>
</body>
</html>
