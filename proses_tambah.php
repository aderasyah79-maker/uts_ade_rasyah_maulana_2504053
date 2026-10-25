<?php
 include('koneksi.php');

 $id_stasiun = $_POST['id_stasiun'];
 $tinggi = $_POST['tinggi'];
 $periode = $_POST['periode'];
 $suhu = $_POST['suhu'];
 $waktu = $_POST['waktu'];
 $id_petugas = $_POST['id_petugas'];
 $id_admin = $_POST['id_admin'];

 $query= "insert into pengukuran (id_stasiun, tinggi, periode, suhu, waktu, id_petugas, id_admin)
 values('$id_stasiun', '$tinggi', '$periode', '$suhu', '$waktu', '$id_petugas', '$id_admin')";

 if(mysqli_query($koneksi, $query)){
    header("location:main_admin.php");
 }else{
    echo 'data tidak masuk'. mysqli_error($koneksi);
 }
?>