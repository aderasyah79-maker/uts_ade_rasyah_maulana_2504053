<?php
    session_start();
    include('koneksi.php');

    $username = $_POST['username'];
    $password = $_POST['password'];

    $hasil = mysqli_query($koneksi, "select * from akun_petugas a, petugas p where a.username = '$username' and a.password = md5('$password') and a.id_petugas = p.id_petugas");
    if(mysqli_num_rows($hasil)>0){
        while($data = mysqli_fetch_assoc($hasil)){
            $_SESSION['nama'] = $data['nama_petugas'];
        }
        header("location:main.php");
        exit;
    }

    $hasil_admin = mysqli_query($koneksi, "select * from akun_admin b, admin c where b.username = '$username' and b.password = md5('$password') and b.id_admin = c.id_admin");
    if(mysqli_num_rows($hasil_admin)>0){
        while($data = mysqli_fetch_assoc($hasil_admin)){
            $_SESSION['nama'] = $data['nama_admin'];
            $_SESSION['role'] = 'admin';
        }
        header("location:main_admin.php");
        exit;
    }
    
    header("location:login.php");
    exit;
?>