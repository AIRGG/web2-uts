<?php

require 'vendor/autoload.php';

use Shuchkin\SimpleXLSX;

$idx = -1; //index buat posisi sheet
if ($xlsx = SimpleXLSX::parse('myexcel.xlsx')) { // coba buka file excelnya
    $idx = array_search("jadwal", $xlsx->sheetNames()); // klo ada set untuk sheet jadwal ada di index berapa

} else {
    // tampilan pesan ini jika file blm ada atau tidak bisa dibuka
    // die and exit
    die('File belum diupload, Silahkan Tunggu...! <br> <a href="">Refresh</a>');
}

// jika code sudah sampai sini berarti file dapat dibuka dan posisi sudah dibaca

$sheetJadwal = $xlsx->rows($idx); // posisi sheet jadwal

// [START] biar datanya ada nama kolomnya
$header_values = $rows = [];
foreach ($xlsx->rows($idx) as $k => $r) {
    if ($k === 0) {
        $header_values = $r;
        continue;
    }
    $rows[] = array_combine($header_values, $r);
}
// [END] biar datanya ada nama kolomnya

// parameter searching/filtering
$find_hari = isset($_GET["hari"]) ? $_GET["hari"] : '';
$find_dosen = isset($_GET["dosen"]) ? $_GET["dosen"] : '';
$find_kelas = isset($_GET["kelas"]) ? $_GET["kelas"] : '';

// variable data yang akan digenerate jadi table
$data = [];

// variable pendukung
$some_filter = [];

// fungsi untuk cek data
function cek_data($cek, $val, $key)
{
    if ($cek == '') return false; // jika yg mau dicek kosong lansgung return false
    $test = strpos(strtolower($val[$key]), $cek); // jadiiin lower dulu trus dicek ada diindex ke berapa
    if ($test > -1 || $test) return true; // kalau match return true
    else return false; // gk ada yg match return false
}

// fungsi untuk filtering
function filter_dosen_kelas($val, $no, $hari)
{
    global $data, $find_dosen, $find_kelas, $some_filter;

    if ($find_dosen == '' && $find_kelas == '') {
        // jika dosen dan kelas kosong, lansung aja, bisa jadi dia filter by hari
        array_push($data, $val);
    } else {
        // jika gk kosong cek valuenya
        $cek_dosen = cek_data($find_dosen, $val, 'Dosen');
        $cek_kelas = cek_data($find_kelas, $val, 'Kelas');

        if ($cek_dosen && $cek_kelas) {
            if (!array_key_exists($hari, $some_filter) || $val['Hari'] != '') {
                $some_filter[$hari] = ($val['Hari'] == '') ? 0 : 1;
            }
            if ($val['No.'] == '' || $val['Hari'] == '') {
                $val['No.'] = ($some_filter[$hari] == 0) ? $no : '';
                $val['Hari'] = ($some_filter[$hari] == 0) ? $hari : '';
                $some_filter[$hari] = 1;
            }
            array_push($data, $val);
        }
    }
}

$no = ''; // temp no & hari | behaviour
$hari = ''; // temp no & hari | behaviour
for ($i = 0; $i < sizeof($rows); $i++) { // loop untuk proses data dari excel ke variable 'data'
    $val = $rows[$i];
    if ($val['No.'] != '') $no = $val['No.'];
    if ($val['Hari'] != '') $hari = $val['Hari'];

    $cek_hari = cek_data($find_hari, $val, 'Hari');

    if ($find_hari == '') {
        filter_dosen_kelas($val, $no, $hari);
    }

    if ($cek_hari) {
        $tmpRows = [];
        for ($x = $i; $x < $i + 12; $x++) { // ambil 12 data hari ini, dari jam pagi sampe jam 7 malem 
            $val = $rows[$x];
            array_push($tmpRows, $val);
        }
        $i += 11; // ubah posisi hari ke hari selanjutnya
        $no = '';
        $hari = '';
        for ($x = 0; $x < sizeof($tmpRows); $x++) {
            $val = $tmpRows[$x];
            if ($val['No.'] != '') $no = $val['No.'];
            if ($val['Hari'] != '') $hari = $val['Hari'];
            filter_dosen_kelas($val, $no, $hari);
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
    <title>Document</title>
    <style>
        .flex {
            display: flex;
        }

        .flex>div {
            margin-right: 10px
        }
    </style>
</head>

<body>
    <h1>Jadwal Matkul</h1>
    <form action="" method="get">
        <div class="flex">
            <div>
                <label>Hari:</label><br>
                <input type="text" name="hari" value="<?= isset($_GET["hari"]) ? $_GET["hari"] : ''  ?>"><br><br>
            </div>
            <div>
                <label>Dosen:</label><br>
                <input type="text" name="dosen" value="<?= isset($_GET["dosen"]) ? $_GET["dosen"] : ''  ?>"><br><br>
            </div>
            <div>
                <label>Kelas:</label><br>
                <input type="text" name="kelas" value="<?= isset($_GET["kelas"]) ? $_GET["kelas"] : ''  ?>"><br><br>
            </div>
            <div>
                <label></label><br>
                <input type="submit">
                <a href="/web2-uts">Refresh</a>
                <br><br>
                
            </div>
        </div>
    </form>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <?php foreach ($header_values as $ky => $vy) : ?>
                <th><?= $vy ?></th>
            <?php endforeach ?>
        </thead>
        <?php foreach ($data as $kx => $vx) : ?>
            <tr>
                <?php foreach ($vx as $ky => $vy) : ?>
                    <td>
                        <?= $vy ?>
                    </td>
                <?php endforeach ?>
            </tr>
        <?php endforeach ?>
    </table>
</body>

</html>