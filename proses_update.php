<?php
    include('koneksi.php');

    $id_pengukuran = $_POST['id_pengukuran'];
    $id_stasiun = $_POST['id_stasiun'];
    $tinggi = $_POST['tinggi'];
    $periode = $_POST['periode'];
    $suhu = $_POST['suhu'];
    $id_petugas = $_POST['id_petugas'];

    $query = "update pengukuran set id_stasiun = '$id_stasiun', tinggi = '$tinggi', periode = '$periode', suhu = '$suhu', id_petugas = '$id_petugas'
    where id_pengukuran ='$id_pengukuran'";

    if(mysqli_query($koneksi, $query)){
        header('location:main_admin.php');
    }else{
        header('location:edit_pengukuran.php');
    }
?>