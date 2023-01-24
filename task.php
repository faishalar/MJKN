<?php
include 'tambah.php';

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