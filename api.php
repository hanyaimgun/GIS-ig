<?php
header('Content-Type: application/json');

$host = "sql100.infinityfree.com";
$user = "if0_41346413";
$pass = "walasig123";
$db   = "if0_41346413_db_gis";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Koneksi database gagal']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $nis = $_POST['nis'];
    $tahun = $_POST['tahun'];
    $waSiswa = $_POST['waSiswa'];
    $alamat = $_POST['alamat'];
    $ayah = $_POST['ayah'];
    $ibu = $_POST['ibu'];
    $waOrtu = $_POST['waOrtu'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $foto = $_POST['foto']; // Menerima Base64 string

    $sql = "INSERT INTO siswa (nama, nis, tahun_masuk, alamat, latitude, longitude, wa_siswa, wa_ortu, nama_ayah, nama_ibu, foto) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisddsssss", $nama, $nis, $tahun, $alamat, $lat, $lng, $waSiswa, $waOrtu, $ayah, $ibu, $foto);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
}
?>
