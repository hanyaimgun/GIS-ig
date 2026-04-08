<?php
// Konfigurasi Database
$host = "sql100.infinityfree.com";
$user = "if0_41346413";
$pass = "walasig123";
$db   = "if0_41346413_db_gis";

$conn = new mysqli($host, $user, $pass, $db);

// Ambil data siswa untuk peta
$result = $conn->query("SELECT * FROM siswa");
$students = [];
while($row = $result->fetch_assoc()) {
    $students[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GIS Walikelas - Database Terhubung</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; }
        #map { height: 400px; width: 100%; border-radius: 1.5rem; border: 4px solid white; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .glass-effect { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); }
        button:disabled { opacity: 0.5; cursor: not-allowed; filter: grayscale(1); }
    </style>
</head>
<body class="min-h-screen pb-20">

    <header class="glass-effect sticky top-0 z-50 border-b px-6 py-4">
        <div class="max-w-md mx-auto flex justify-between items-center">
            <h1 class="text-xl font-black text-blue-600">GIS-IG</h1>
            <div id="roleIndicator" class="bg-blue-100 text-blue-700 px-4 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-tighter">Mode: Siswa</div>
        </div>
    </header>

    <div class="max-w-md mx-auto px-6 mt-6">
        <!-- Navigation -->
        <div class="flex bg-gray-200/50 rounded-2xl p-1 mb-6">
            <button onclick="switchView('siswa')" id="btnSiswa" class="flex-1 py-3 rounded-xl text-sm font-bold bg-white shadow-sm">SISWA</button>
            <button onclick="checkWalasAccess()" id="btnWalas" class="flex-1 py-3 rounded-xl text-sm font-bold text-gray-500">WALAS</button>
        </div>

        <!-- Form Siswa -->
        <div id="viewSiswa" class="space-y-6">
            <form id="formSiswa" class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-gray-100 space-y-4">
                <div class="text-center">
                    <label for="fotoInput" class="inline-block cursor-pointer relative group">
                        <div id="fotoPreviewContainer" class="w-24 h-24 rounded-full bg-gray-100 border-2 border-dashed border-gray-300 overflow-hidden flex items-center justify-center">
                            <img id="fotoPreview" class="hidden w-full h-full object-cover">
                            <span id="placeholderText" class="text-[10px] font-bold text-gray-400">FOTO</span>
                        </div>
                        <input type="file" id="fotoInput" class="hidden" accept="image/*" required>
                    </label>
                    <p class="text-[10px] mt-2 text-gray-400 uppercase font-bold tracking-widest">Unggah Foto (Max 1MB)</p>
                </div>

                <input type="text" id="nama" placeholder="Nama Lengkap" class="w-full p-4 rounded-2xl bg-gray-50 border-none outline-none text-sm" required>
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" id="nis" placeholder="NIS" class="w-full p-4 rounded-2xl bg-gray-50 border-none outline-none text-sm" required>
                    <input type="number" id="tahun" placeholder="Angkatan" class="w-full p-4 rounded-2xl bg-gray-50 border-none outline-none text-sm" required>
                </div>
                <input type="tel" id="waSiswa" placeholder="WhatsApp Siswa" class="w-full p-4 rounded-2xl bg-gray-50 border-none outline-none text-sm" required>
                <textarea id="alamat" placeholder="Alamat Rumah Lengkap" class="w-full p-4 rounded-2xl bg-gray-50 border-none outline-none text-sm h-20" required></textarea>
                
                <div class="bg-indigo-50 p-4 rounded-3xl space-y-2">
                    <p class="text-[9px] font-black text-indigo-400 uppercase ml-2">Data Orang Tua</p>
                    <input type="text" id="ayah" placeholder="Nama Ayah" class="w-full p-3 rounded-xl border-none text-sm">
                    <input type="text" id="ibu" placeholder="Nama Ibu" class="w-full p-3 rounded-xl border-none text-sm">
                    <input type="tel" id="waOrtu" placeholder="WA Orang Tua" class="w-full p-3 rounded-xl border-none text-sm">
                </div>

                <div id="gpsStatusCard" class="bg-gray-100 p-4 rounded-3xl text-center">
                    <p id="gpsStatusText" class="text-xs font-bold text-gray-500 italic">Menunggu Akses Lokasi...</p>
                </div>

                <button type="submit" id="btnSimpan" disabled class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold shadow-lg shadow-blue-100">Simpan Data Rumah</button>
            </form>
            
            <div class="p-4 bg-yellow-50 border border-yellow-100 rounded-2xl">
                <p class="text-[10px] text-yellow-700 leading-relaxed">
                    <b>Catatan:</b> Silahkan upload file foto asli ke <a href="https://drive.google.com/drive/folders/1Ciff68Lw0aj-3QtVtB6eyapAECLFnkIx" class="underline font-bold" target="_blank">Folder Google Drive Ini</a> sebagai backup dokumentasi sekolah.
                </p>
            </div>
        </div>

        <!-- View Walas -->
        <div id="viewWalas" class="hidden space-y-4">
            <div id="map"></div>
            <div id="listSiswa" class="space-y-2"></div>
        </div>
    </div>

    <!-- Modal Password -->
    <div id="modalLogin" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-[100] flex items-center justify-center p-6">
        <div class="bg-white p-8 rounded-[2rem] w-full max-w-xs">
            <h3 class="font-bold text-center mb-4">Akses Khusus Guru</h3>
            <input type="password" id="passWalas" class="w-full p-4 bg-gray-100 rounded-xl text-center mb-4" placeholder="KODE">
            <button onclick="verifyWalas()" class="w-full bg-black text-white py-3 rounded-xl font-bold">Masuk</button>
        </div>
    </div>

    <script>
        const dbSiswa = <?php echo json_encode($students); ?>;
        let userCoords = null;
        let currentFotoBase64 = null;
        let map;

        // GPS Logic
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(
                (p) => {
                    userCoords = { lat: p.coords.latitude, lng: p.coords.longitude };
                    document.getElementById('gpsStatusText').innerText = "✅ Lokasi Siap (Akurasi Tinggi)";
                    document.getElementById('gpsStatusCard').className = "bg-green-100 p-4 rounded-3xl text-center text-green-700";
                    document.getElementById('btnSimpan').disabled = false;
                },
                () => { document.getElementById('gpsStatusText').innerText = "❌ GPS Wajib Aktif!"; },
                { enableHighAccuracy: true }
            );
        }

        // Preview Foto
        document.getElementById('fotoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if(file) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    currentFotoBase64 = ev.target.result;
                    document.getElementById('fotoPreview').src = currentFotoBase64;
                    document.getElementById('fotoPreview').classList.remove('hidden');
                    document.getElementById('placeholderText').classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });

        function switchView(v) {
            document.getElementById('viewSiswa').classList.toggle('hidden', v !== 'siswa');
            document.getElementById('viewWalas').classList.toggle('hidden', v === 'siswa');
            if(v === 'walas') { 
                setTimeout(initMap, 200);
                renderList();
            }
        }

        function checkWalasAccess() { document.getElementById('modalLogin').classList.remove('hidden'); }
        function verifyWalas() {
            if(document.getElementById('passWalas').value === "KMZWAY87AA") {
                document.getElementById('modalLogin').classList.add('hidden');
                switchView('walas');
            } else { alert("Salah!"); }
        }

        function initMap() {
            if(!map) {
                map = L.map('map').setView([-6.2, 106.8], 12);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            }
            dbSiswa.forEach(s => {
                L.marker([s.latitude, s.longitude]).addTo(map).bindPopup(`<b>${s.nama}</b><br>${s.alamat}`);
            });
        }

        function renderList() {
            document.getElementById('listSiswa').innerHTML = dbSiswa.map(s => `
                <div class="bg-white p-4 rounded-2xl flex items-center border border-gray-100">
                    <img src="${s.foto}" class="w-10 h-10 rounded-full object-cover mr-3 bg-gray-100">
                    <div class="flex-1 text-xs">
                        <p class="font-bold">${s.nama}</p>
                        <p class="text-gray-500">${s.nis} - ${s.tahun_masuk}</p>
                    </div>
                    <a href="https://wa.me/${s.wa_siswa}" class="text-blue-600 font-bold text-[10px]">CHAT</a>
                </div>
            `).join('');
        }

        // Simpan ke Database via API
        document.getElementById('formSiswa').onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData();
            formData.append('nama', document.getElementById('nama').value);
            formData.append('nis', document.getElementById('nis').value);
            formData.append('tahun', document.getElementById('tahun').value);
            formData.append('waSiswa', document.getElementById('waSiswa').value);
            formData.append('alamat', document.getElementById('alamat').value);
            formData.append('ayah', document.getElementById('ayah').value);
            formData.append('ibu', document.getElementById('ibu').value);
            formData.append('waOrtu', document.getElementById('waOrtu').value);
            formData.append('lat', userCoords.lat);
            formData.append('lng', userCoords.lng);
            formData.append('foto', currentFotoBase64);

            const res = await fetch('api.php', { method: 'POST', body: formData });
            const data = await res.json();
            if(data.status === 'success') {
                alert("Data Tersimpan di Database!");
                location.reload();
            } else {
                alert("Gagal menyimpan: " + data.message);
            }
        };
    </script>
</body>
</html>
