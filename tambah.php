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
  $tampung = [];
  while ($kolom = mysqli_fetch_assoc($data)) {
    $tampung[] = $kolom;
  }
  return $tampung;
}