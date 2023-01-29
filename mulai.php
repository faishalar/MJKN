<?php
include 'tambah.php';
//include 'task.php';
setlocale(LC_ALL, 'IND');
$tanggal            = date("Y-m-d");
$hariKerja          = strtoupper(strftime('%A'));
$peserta            = mysqli_query($koneksi, "SELECT DISTINCT reg_periksa.no_reg,reg_periksa.no_rawat AS no_rawat ,reg_periksa.tgl_registrasi, reg_periksa.jam_reg, reg_periksa.kd_dokter,dokter.nm_dokter AS nm_dokter, pasien.no_peserta AS no_peserta, pasien.no_tlp AS no_tlp, pasien.no_ktp AS no_ktp, reg_periksa.kd_poli ,poliklinik.nm_poli,reg_periksa.stts_daftar,reg_periksa.no_rkm_medis,reg_periksa.kd_pj, maping_poli_bpjs.kd_poli_bpjs AS kd_poli_bpjs, maping_dokter_dpjpvclaim.kd_dokter_bpjs, jadwal.jam_mulai, jadwal.jam_selesai, jadwal.kuota FROM reg_periksa INNER JOIN jadwal ON reg_periksa.kd_dokter = jadwal.kd_dokter INNER JOIN maping_dokter_dpjpvclaim ON reg_periksa.kd_dokter = maping_dokter_dpjpvclaim.kd_dokter INNER JOIN maping_poli_bpjs ON reg_periksa.kd_poli = maping_poli_bpjs.kd_poli_rs INNER JOIN dokter ON reg_periksa.kd_dokter=dokter.kd_dokter INNER JOIN poliklinik ON reg_periksa.kd_poli=poliklinik.kd_poli INNER JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis INNER JOIN penjab ON penjab.kd_pj=reg_periksa.kd_pj WHERE reg_periksa.tgl_registrasi = '" . $tanggal . "' AND jadwal.hari_kerja='" . $hariKerja . "'");
//$umum               = mysqli_query($koneksi, "SELECT DISTINCT reg_periksa.no_reg,reg_periksa.no_rawat AS no_rawat ,reg_periksa.tgl_registrasi, reg_periksa.jam_reg, reg_periksa.kd_dokter,dokter.nm_dokter AS nm_dokter, pasien.no_peserta AS no_peserta, pasien.no_tlp AS no_tlp, pasien.no_ktp AS no_ktp, reg_periksa.kd_poli ,poliklinik.nm_poli,reg_periksa.stts_daftar,reg_periksa.no_rkm_medis,reg_periksa.kd_pj, maping_poli_bpjs.kd_poli_bpjs AS kd_poli_bpjs, maping_dokter_dpjpvclaim.kd_dokter_bpjs, jadwal.jam_mulai, jadwal.jam_selesai, jadwal.kuota FROM reg_periksa INNER JOIN jadwal ON reg_periksa.kd_dokter = jadwal.kd_dokter INNER JOIN maping_dokter_dpjpvclaim ON reg_periksa.kd_dokter = maping_dokter_dpjpvclaim.kd_dokter INNER JOIN maping_poli_bpjs ON reg_periksa.kd_poli = maping_poli_bpjs.kd_poli_rs INNER JOIN dokter ON reg_periksa.kd_dokter=dokter.kd_dokter INNER JOIN poliklinik ON reg_periksa.kd_poli=poliklinik.kd_poli INNER JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis INNER JOIN penjab ON penjab.kd_pj=reg_periksa.kd_pj WHERE reg_periksa.tgl_registrasi = '" . $tanggal . "' AND penjab.png_jawab = 'UMUM' AND jadwal.hari_kerja='" . $hariKerja . "'");

//pasien jkn
while ($data = mysqli_fetch_assoc($peserta)) {
  $Booking            = $data["no_rawat"];
  $kodeBooking        = str_replace("/", "", $Booking);
  $nomorpeserta       = $data["no_peserta"];
  $namaDokter         = $data["nm_dokter"];
  $nik                = $data["no_ktp"];
  $hp                 = $data["no_tlp"];
  $kodePoli           = $data["kd_poli_bpjs"];
  $namaPoli           = $data["nm_poli"];
  $rekamMedis         = $data["no_rkm_medis"];
  $status             = $data["stts_daftar"];
  $statusDaftar       = $status == "Baru" ? "1" : "0";
  $kodeDokterBPJS     = $data["kd_dokter_bpjs"];
  $awal               = $data["jam_mulai"];
  $waktuAwal          = substr($awal, 0, -3);
  $akhir              = $data["jam_selesai"];
  $waktuAkhir         = substr($akhir, 0, -3);
  $nomorAntrian       = $data["no_reg"];
  $urutan             = (int)$nomorAntrian;
  $tRegis             = $data["tgl_registrasi"];
  $jRegis             = $data["jam_reg"];
  $hadir              = strtotime($tRegis . " " . $jRegis) * 1000;
  $kuota              = $data["kuota"];
  $sisa               = (int)$kuota - $urutan;

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://apijkn.bpjs-kesehatan.go.id/vclaim-rest/Rujukan/Peserta/' . $nomorpeserta,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      "user_key:" . $userkey,
      "x-cons-id:" . $cons_id,
      "x-signature:" . $encodeSignature,
      "x-timestamp:" . $timestamp
    ),
  ));

  $response = curl_exec($curl);
  $err = curl_error($curl);

  curl_close($curl);

  if ($err) {
    echo "cURL Error #:" . $err;
  } else {
    //echo $response;
  }
  // function decrypt

  $data        = json_decode($response, true);
  $kunci      = $cons_id . $secret_key . $timestamp;
  $nilairespon  = $data["response"];
  $hasilakhir    = decompress(stringDecrypt($kunci, $nilairespon));
  //echo $hasilakhir;

  $tampilkan = json_decode($hasilakhir, true);
  $code  = $data2["metadata"]["code"];
  $message  = $data2["metadata"]["message"];

  if ($code == "200") {
    $noKunjungan = $tampilkan["rujukan"]["noKunjungan"];
    /*echo $nomorpeserta."<br>";
    echo $noKunjungan."<br>";
    echo $kodeBooking."<br>";
    echo $nik."<br>";
    echo $hp."<br>";
    echo $kodePoli."<br>";
    echo $namaPoli."<br>";
    echo $tanggal."<br>";
    echo $rekamMedis."<br>";
    echo $statusDaftar."<br>";
    echo $kodeDokterBPJS."<br>";
    echo $waktuAwal."-".$waktuAkhir."<br>";
    echo $nomorAntrian."<br>";
    echo $urutan."<br>";
    echo $kuota."<br>";
    echo $sisa."<br>";
    echo $hadir."<br><br>";
    */


    $curl2 = curl_init();

    curl_setopt_array($curl2, array(
      CURLOPT_URL => 'https://apijkn.bpjs-kesehatan.go.id/antreanrs/antrean/add',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => '{
         "kodebooking": "' . $kodeBooking . '",
         "jenispasien": "JKN",
         "nomorkartu": "' . $nomorpeserta . '",
         "nik": "' . $nik . '",
         "nohp": "' . $hp . '",
         "kodepoli": "' . $kodePoli . '",
         "namapoli": "' . $namaPoli . '",
         "pasienbaru": ' . $statusDaftar . ',
         "norm": "' . $rekamMedis . '",
         "tanggalperiksa": "' . $tanggal . '",
         "kodedokter": ' . $kodeDokterBPJS . ',
         "namadokter": "' . $namaDokter . '",
         "jampraktek": "' . $waktuAwal . "-" . $waktuAkhir . '",
         "jeniskunjungan": 1,
         "nomorreferensi": "' . $noKunjungan . '",
         "nomorantrean": "' . $kodePoli . "-" . $nomorAntrian . '",
         "angkaantrean": ' . $urutan . ',
         "estimasidilayani": ' . $hadir . ',
         "sisakuotajkn": ' . $sisa . ',
         "kuotajkn": ' . $kuota . ',
         "sisakuotanonjkn": ' . $sisa . ',
         "kuotanonjkn": ' . $kuota . ',
         "keterangan": "Peserta harap 30 menit lebih awal guna pencatatan administrasi."
      }',
      CURLOPT_HTTPHEADER => array(
        "user_key:" . $userkey2,
        "x-cons-id:" . $cons_id,
        "x-signature:" . $encodeSignature,
        "x-timestamp:" . $timestamp
      ),
    ));

    $response2 = curl_exec($curl2);
    $err2 = curl_error($curl2);

    curl_close($curl2);

    if ($err2) {
      echo "cURL Error #:" . $err2;
    } else {
      //echo $response;
    }
    $data2        = json_decode($response2, true);
    $kunci2      = $cons_id . $secret_key . $timestamp;
    $nilairespon2  = $data2["metadata"]["code"];
    $nilairespon3  = $data2["metadata"]["message"];
    $hasilakhir2    = decompress(stringDecrypt($kunci2, $nilairespon2));


    if ($nilairespon2 == "200") {
      echo "<script>console.log('Mobile JKN : " . $nilairespon2 . " : " . $nilairespon3 . "');</script>";
    } else {
      echo "<script>console.log('Terdapat Duplikasi Input JKN');</script>";
    }
    $tugas3 = tampil("SELECT dikirim FROM mutasi_berkas WHERE dikirim LIKE '" . $tanggal . " %' AND no_rawat= '" . $Booking . "'");
    if (!empty($tugas3)) {
      $waktuTugas3 = strtotime($tugas3["dikirim"]);
      task($kodeBooking, "3", $waktuTugas3);
    }
    $tugas4 = tampil("SELECT diterima FROM mutasi_berkas WHERE diterima LIKE '" . $tanggal . " %' AND no_rawat= '" . $Booking . "'");
    if (!empty($tugas4)) {
      $waktuTugas4 = strtotime($tugas4["diterima"]);
      task($kodeBooking, "4", $waktuTugas4);
    }
    $tugas5 = tampil("SELECT CONCAT (tgl_perawatan,' ',jam_rawat) AS waktu FROM pemeriksaan_ralan WHERE no_rawat= '" . $Booking . "'");
    if (!empty($tugas5)) {
      $waktuTugas5 = strtotime($tugas5["waktu"]);
      task($kodeBooking, "5", $waktuTugas5);
    }
    $tugas6 = tampil("SELECT CONCAT (tgl_peresepan,' ',jam_peresepan) AS waktu FROM resep_obat WHERE no_rawat= '" . $Booking . "'");
    if (!empty($tugas6)) {
      $waktuTugas6 = strtotime($tugas6["waktu"]);
      task($kodeBooking, "6", $waktuTugas6);
    }
    $tugas7 = tampil("SELECT CONCAT (tgl_perawatan,' ',jam) AS waktu FROM resep_obat WHERE no_rawat= '" . $Booking . "'");
    if (!empty($tugas7)) {
      $waktuTugas7 = strtotime($tugas7["waktu"]);
      task($kodeBooking, "7", $waktuTugas7);
    }
    $tugas99 = tampil("SELECT NOW() AS waktu FROM reg_periksa WHERE stts='Batal' AND no_rawat= '" . $Booking . "'");
    if (!empty($tugas99)) {
      $waktuTugas99 = strtotime($tugas99["waktu"]);
      task($kodeBooking, "99", $waktuTugas99);
    }
  } else {
    //NON JKN
    $curl3 = curl_init();

    curl_setopt_array($curl3, array(
      CURLOPT_URL => 'https://apijkn.bpjs-kesehatan.go.id/antreanrs/antrean/add',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => '{
         "kodebooking": "' . $kodeBooking . '",
         "jenispasien": "NON JKN",
         "nomorkartu": "-",
         "nik": "-",
         "nohp": "' . $hp . '",
         "kodepoli": "' . $kodePoli . '",
         "namapoli": "' . $namaPoli . '",
         "pasienbaru": ' . $statusDaftar . ',
         "norm": "' . $rekamMedis . '",
         "tanggalperiksa": "' . $tanggal . '",
         "kodedokter": ' . $kodeDokterBPJS . ',
         "namadokter": "' . $namaDokter . '",
         "jampraktek": "' . $waktuAwal . "-" . $waktuAkhir . '",
         "jeniskunjungan": 3,
         "nomorreferensi": "-",
         "nomorantrean": "' . $kodePoli . "-" . $nomorAntrian . '",
         "angkaantrean": ' . $urutan . ',
         "estimasidilayani": ' . $hadir . ',
         "sisakuotajkn": ' . $sisa . ',
         "kuotajkn": ' . $kuota . ',
         "sisakuotanonjkn": ' . $sisa . ',
         "kuotanonjkn": ' . $kuota . ',
         "keterangan": "Peserta harap 30 menit lebih awal guna pencatatan administrasi."
      }',
      CURLOPT_HTTPHEADER => array(
        "user_key:" . $userkey2,
        "x-cons-id:" . $cons_id,
        "x-signature:" . $encodeSignature,
        "x-timestamp:" . $timestamp
      ),
    ));

    $response3 = curl_exec($curl3);
    $err3 = curl_error($curl3);

    curl_close($curl3);

    if ($err3) {
      echo "cURL Error #:" . $err3;
    } else {
      //echo $response;
    }
    $data3        = json_decode($response3, true);
    $kunci3      = $cons_id . $secret_key . $timestamp;
    $nilairespon4  = $data3["metadata"]["code"];
    $nilairespon5  = $data3["metadata"]["message"];
    $hasilakhir3    = decompress(stringDecrypt($kunci3, $nilairespon4));
    if ($nilairespon4 == "200") {
      echo "<script>console.log('NON JKN : " . $nilairespon4 . " : " . $nilairespon5 . "');</script>";
    } else {
      echo "<script>console.log('Terdapat Duplikasi Input NON JKN');</script>";
    }
    $tugas3 = tampil("SELECT dikirim FROM mutasi_berkas WHERE dikirim LIKE '" . $tanggal . " %' AND no_rawat= '" . $Booking . "'");
    if (!empty($tugas3)) {
      $waktuTugas3 = strtotime($tugas3["dikirim"]);
      task($kodeBooking, "3", $waktuTugas3);
    }
    $tugas4 = tampil("SELECT diterima FROM mutasi_berkas WHERE diterima LIKE '" . $tanggal . " %' AND no_rawat= '" . $Booking . "'");
    if (!empty($tugas4)) {
      $waktuTugas4 = strtotime($tugas4["diterima"]);
      task($kodeBooking, "4", $waktuTugas4);
    }
    $tugas5 = tampil("SELECT CONCAT (tgl_perawatan,' ',jam_rawat) AS waktu FROM pemeriksaan_ralan WHERE no_rawat= '" . $Booking . "'");
    if (!empty($tugas5)) {
      $waktuTugas5 = strtotime($tugas5["waktu"]);
      task($kodeBooking, "5", $waktuTugas5);
    }
    $tugas6 = tampil("SELECT CONCAT (tgl_peresepan,' ',jam_peresepan) AS waktu FROM resep_obat WHERE no_rawat= '" . $Booking . "'");
    if (!empty($tugas6)) {
      $waktuTugas6 = strtotime($tugas6["waktu"]);
      task($kodeBooking, "6", $waktuTugas6);
    }
    $tugas7 = tampil("SELECT CONCAT (tgl_perawatan,' ',jam) AS waktu FROM resep_obat WHERE no_rawat= '" . $Booking . "'");
    if (!empty($tugas7)) {
      $waktuTugas7 = strtotime($tugas7["waktu"]);
      task($kodeBooking, "7", $waktuTugas7);
    }
    $tugas99 = tampil("SELECT NOW() AS waktu FROM reg_periksa WHERE stts='Batal' AND no_rawat= '" . $Booking . "'");
    if (!empty($tugas99)) {
      $waktuTugas99 = strtotime($tugas99["waktu"]);
      task($kodeBooking, "99", $waktuTugas99);
    }
  }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MJKN ADD ANTREAN</title>
    <style>
    .tampung {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .penting {
        color: red;
    }
    </style>
</head>

<body>
    <div class="tampung">
        <div class="iseng">
            <h1>TAMPILAN DEPAN TIDAK DIKOSONGI TAKUT <span class="penting">MIXUE</span> MENGHAMPIRI</h1>
            <center>
                <h4 style="">PROSES BERJALAN DIBELAKANG LAYAR</h4>
            </center>
        </div>
    </div>
    <script>
    console.log("reload");

    function refresh() {
        window.location.reload();
    }
    window.setInterval('refresh()', 60000);
    </script>
</body>

</html>