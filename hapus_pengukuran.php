<?php
    include('koneksi.php');

    $waktu = $_GET['waktu'];

    $query = "delete from pengukuran where waktu = '$waktu'";

    if(mysqli_query($koneksi, $query)){
        header('location:main_admin.php');
    }else{
        header('location:main_admin.php');
    }
?>