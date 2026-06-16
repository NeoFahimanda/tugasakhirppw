<?php
// Memanggil file koneksi database
require_once 'Database.php';

class User
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Fungsi untuk Registrasi (Daftar Akun Baru)
    public function register($username, $password, $name)
    {
        // Mencegah error karakter aneh (SQL Injection dasar)
        $username = $this->db->real_escape_string($username);
        $name = $this->db->real_escape_string($name);

        // Enkripsi password menggunakan md5 (Sesuai Materi Modul Lanjut)
        $hashed_password = md5($password);

        $query = "INSERT INTO users (username, password, name) VALUES ('$username', '$hashed_password', '$name')";

        if ($this->db->query($query)) {
            return true; // Berhasil
        }
        return false; // Gagal (biasanya karena username sudah dipakai, karena kita set UNIQUE di database)
    }

    // Fungsi untuk Login
    public function login($username, $password)
    {
        $username = $this->db->real_escape_string($username);
        $hashed_password = md5($password);

        $query = "SELECT * FROM users WHERE username = '$username' AND password = '$hashed_password'";
        $result = $this->db->query($query);

        // Jika data ditemukan (username dan password cocok)
        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();

            // Set Session (Sesuai Materi Modul 11-12)
            session_start();
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['name'] = $user_data['name'];

            return true;
        }
        return false;
    }

    // Fungsi Ambil Data Profil Berdasarkan ID
    public function getUserById($id)
    {
        $query = "SELECT * FROM users WHERE id = '$id'";
        $result = $this->db->query($query);
        return $result->fetch_assoc();
    }

    // Fungsi Update Profil (Nama, Bio, Foto, Header)
    public function updateProfile($id, $name, $bio, $profile_pic, $header_pic)
    {
        $name = $this->db->real_escape_string($name);
        $bio = $this->db->real_escape_string($bio);

        $query = "UPDATE users SET name='$name', bio='$bio', profile_pic='$profile_pic', header_pic='$header_pic' WHERE id='$id'";
        return $this->db->query($query);
    }

    // Fungsi Ambil Data Profil Berdasarkan Username (URL GET)
    public function getUserByUsername($username)
    {
        $username = $this->db->real_escape_string($username);
        $query = "SELECT * FROM users WHERE username = '$username'";
        $result = $this->db->query($query);
        return $result->fetch_assoc();
    }

    // Fungsi mengambil daftar User dengan total akumulasi Likes terbanyak (Materi Agregasi SQL)
    public function getTopLikedUsers($limit = 3)
    {
        $query = "SELECT users.id, users.name, users.username, users.profile_pic, COUNT(likes.id) as total_likes
                  FROM users
                  JOIN posts ON users.id = posts.user_id
                  JOIN likes ON posts.id = likes.post_id
                  GROUP BY users.id
                  ORDER BY total_likes DESC
                  LIMIT $limit";

        $result = $this->db->query($query);

        $top_users = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $top_users[] = $row;
            }
        }
        return $top_users;
    }

    // Fungsi untuk mendapatkan semua user untuk kontak chat, diurutkan berdasarkan chat terakhir dengan user aktif
    public function getChatContacts($current_user_id)
    {
        $current_user_id = (int)$current_user_id;
        $query = "SELECT u.id, u.name, u.username, u.profile_pic,
                         (SELECT MAX(created_at) FROM messages 
                          WHERE (sender_id = u.id AND receiver_id = '$current_user_id') 
                             OR (sender_id = '$current_user_id' AND receiver_id = u.id)) as last_message_time
                  FROM users u
                  WHERE u.id != '$current_user_id'
                  ORDER BY last_message_time DESC, u.name ASC";

        $result = $this->db->query($query);
        $contacts = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $contacts[] = $row;
            }
        }
        return $contacts;
    }
}
