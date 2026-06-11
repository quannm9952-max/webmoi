<?php
require_once "../config/database.php"; 
require_once "User.php";              

// Kết nối DB
$db = new Database();
$conn = $db->getConnection();

// Kiểm tra kết nối
if (!$conn) {
    die("❌ Kết nối DB thất bại");
}

// Khởi tạo model
$userModel = new User($conn);

// ================= TEST CREATE =================
echo "<h3>TEST CREATE</h3>";

$data = [
    "id_vai_tro" => 2,
    "ho_ten" => "Test Model",
    "email" => "modeltest@gmail.com",
    "so_dien_thoai" => "0999999999",
    "mat_khau" => "",
    "dia_chi" => "HCM"
];

if (!$userModel->existsByEmail($data['email'])) {
    $result = $userModel->create($data);
    echo $result ? "✅ CREATE OK<br>" : "❌ CREATE FAIL<br>";
} else {
    echo "⚠️ Email đã tồn tại<br>";
}


// ================= TEST FIND BY EMAIL =================
echo "<h3>TEST findByEmail</h3>";

$user = $userModel->findByEmail($data['email']);

if ($user) {
    echo "✅ FOUND USER<br>";
    print_r($user);
} else {
    echo "❌ NOT FOUND<br>";
}


// ================= TEST EXISTS =================
echo "<h3>TEST existsByEmail</h3>";

$exists = $userModel->existsByEmail($data['email']);
echo $exists ? "✅ EMAIL EXISTS<br>" : "❌ EMAIL NOT FOUND<br>";


// ================= TEST PASSWORD =================
echo "<h3>TEST PASSWORD</h3>";

if ($user && password_verify("123456", $user['mat_khau'])) {
    echo "✅ PASSWORD OK<br>";
} else {
    echo "❌ PASSWORD FAIL<br>";
}


// ================= TEST FIND BY ID =================
echo "<h3>TEST findById</h3>";

if ($user) {
    $userById = $userModel->findById($user['id_nguoi_dung']);
    print_r($userById);
}
?>