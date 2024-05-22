<?php
session_start();
require 'config.php'; // File untuk koneksi database

// Jika petugas logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
}

// Proses login petugas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM staff WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $petugas = $result->fetch_assoc();
        if (password_verify($password, $petugas['password'])) {
            $_SESSION['id_petugas'] = $petugas['id'];
            header("Location: index.php");
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "Invalid username.";
    }
}

// Proses peminjaman buku oleh siswa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pinjaman'])) {
    $id_buku = $_POST['id_buku'];
    $id_siswa = $_POST['id_siswa'];
    $tanggal_peminjaman = date('Y-m-d');

    $sql = "INSERT INTO peminjaman (id_buku, id_siswa, tanggal_peminjaman) VALUES ('$id_buku', '$id_siswa', '$tanggal_peminjaman')";
    if ($conn->query($sql) === TRUE) {
        $update_copies = "UPDATE books SET copies_available = copies_available - 1 WHERE id = '$id_buku'";
        $conn->query($update_copies);
        echo "Book loaned successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Perpustakaan</title>
</head>
<body>
    <?php if (isset($_SESSION['id_petugas'])): ?>
        <h1>Welcome, Staff!</h1>
        <a href="index.php?logout=true">Logout</a>
        
        <h2>Tambah Buku Baru!</h2>
        <form method="post" action="add_book.php">
            Title: <input type="text" name="judul" required><br>
            Author: <input type="text" name="penulis" required><br>
            Publisher: <input type="text" name="penerbit"><br>
            Year Published: <input type="number" name="tahun_penerbit"><br>
            ISBN: <input type="text" name="isbn"><br>
            <button type="submit">Tambah Buku</button>
        </form>
        
        <h2>Books List</h2>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>judul</th>
                <th>Penulis</th>
                <th>Penerbit</th>
                <th>Tahun Penerbit</th>
                <th>ISBN</th>
            </tr>
            <?php
            $sql = "SELECT * FROM buku";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['judul']}</td>
                        <td>{$row['penulis']}</td>
                        <td>{$row['penerbit']}</td>
                        <td>{$row['tahun_penerbit']}</td>
                        <td>{$row['isbn']}</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No books available</td></tr>";
            }
            ?>
        </table>
    <?php else: ?>
        <h1>Login Untuk Petugas</h1>
        <form method="post">
            Username: <input type="text" name="username" required><br>
            Password: <input type="password" name="password" required><br>
            <button type="submit" name="login">Login</button>
        </form>
    <?php endif; ?>
    
    <h2>Pinjaman Buku</h2>
    <form method="post">
        Book ID: <input type="number" name="id_buku" required><br>
        Student ID: <input type="number" name="id_siswa" required><br>
        <button type="submit" name="pinjaman">Pinjaman Buku</button>
    </form>
</body>
</html>
