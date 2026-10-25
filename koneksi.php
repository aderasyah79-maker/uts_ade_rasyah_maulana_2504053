<?php
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'uts';

    $koneksi = mysqli_connect($host, $username, $password, $database);

    if(!$koneksi){
        echo 'gagal terhubung';
    }
?>