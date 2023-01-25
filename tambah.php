<?php

include 'lz/LZContext.php';
include 'lz/LZData.php';
include 'lz/LZReverseDictionary.php';
include 'lz/LZString.php';
include 'lz/LZUtil.php';
include 'lz/LZUtil16.php';

//koneksi
$server             = "152330211826.ip-dynamic.com";
$user               = "public";
$pass               = "public";
$port               = "3319";
$database           = "arofah";

$koneksi = mysqli_connect($server, $user, $pass, $database, $port);

if (!$koneksi) {
  die("koneksi gagal " . mysqli_connect_error());
}

//variabel
$userkey            = "d889e4645a2248573ed5f2aaa7d71aff";
$userkey2           = "43664a011b84a7db9cfa7de2e945b7ef";
$cons_id            = "21422";
$secret_key         = "8lKD5EB371";

date_default_timezone_set("Asia/Jakarta");
$timestamp          = strtotime(date("Y/m/d H:i:s"));
$data               = $cons_id . "&" . $timestamp;
$signature          = hash_hmac('sha256', $data, $secret_key, true);
$encodeSignature    = base64_encode($signature);
$urlencodeSignature = urlencode($encodeSignature);

function stringDecrypt($key, $string)
{


  $encrypt_method = 'AES-256-CBC';

  // hash
  $key_hash = hex2bin(hash('sha256', $key));

  // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
  $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);

  $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);

  return $output;
}

// function lzstring decompress 
// download libraries lzstring : https://github.com/nullpunkt/lz-string-php
function decompress($string)
{
  return LZString::decompressFromEncodedURIComponent($string);
}

function tampil($queri)
{
  global $koneksi;
  $data = mysqli_query($koneksi, $queri);
  $tampil = mysqli_fetch_assoc($data);
  return $tampil;
}
//fungsi kirim task id
function task($kodebooking, $task, $waktu)
{
    global $cons_id, $encodeSignature, $userkey2, $timestamp;
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://apijkn.bpjs-kesehatan.go.id/antreanrs/antrean/updatewaktu",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "{\n   \"kodebooking\": \"" . $kodebooking . "\",\n   \"taskid\": " . $task . ",\n   \"waktu\": " . $waktu . "\n}",
        CURLOPT_COOKIE => "f1e479b49e3d92b89fb03f04060944b8=122e518c1aa89359c0ae6774797d11b4; TS01188d9a=016d4ab601450910722fc349319fcca0c8d18a7151633ef931048533d2e3970af0020a44b8dfed81317856d3e7cf57916b6ab40e65d39b3dbab24fdc83c0cb7881dd35e435; 07502599ebdcc4a5587c6faa185bc5e0=cfe2180e237102ac7d2066472ff81827",
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "user_key:" . $userkey2,
            "x-cons-id:" . $cons_id,
            "x-signature:" . $encodeSignature,
            "x-timestamp:" . $timestamp
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        //echo $response;
    }

    $data = json_decode($response, true);
    $tampilan = $data["metadata"]["message"];
    $tampilan2 = $data["metadata"]["code"];

    return "Hasil " . $tampilan2 . " " . $tampilan;
}