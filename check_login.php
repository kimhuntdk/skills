<?php
session_start();
date_default_timezone_set( "Asia/Bangkok" );
require_once( "inc/db_connect.php" );
$mysqli = connect();
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
// ตรวจสอบการส่งแบบฟอร์ม
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // เก็บค่าที่ผู้ใช้ป้อนในฟอร์ม
    $username = test_input($_POST["email"]);
    $password = test_input($_POST["password"]);
	if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
		exit("รูปแบบอีเมลไม่ถูกต้อง/check format eamil");
	}
       // สร้างคำสั่ง SQL เพื่อค้นหาผู้ใช้จากชื่อผู้ใช้
		$stmt = $mysqli->prepare("SELECT * FROM skills_member WHERE email = ?");
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$result = $stmt->get_result();
    // ตรวจสอบว่าพบผู้ใช้งานในฐานข้อมูลหรือไม่
    if ($result->num_rows > 0) {
        // ดึงข้อมูลผู้ใช้งาน
        $row = $result->fetch_assoc();

		if($row['status'] != 'active'){
              exit("โปรดไปยืนยันในเมลที่ลงเทียนก่อน ถึงจะเข้าสู่ระบบได้/Please go to confirm in the mail that lit the candle first. to be able to log in");

		 }  
        // ตรวจสอบรหัสผ่านที่ป้อนเข้ามา
        if (password_verify($password, $row["password"])) {
            $_SESSION["eng_ning_ses"] = $username; // เก็บชื่อผู้ใช้ในเซสชัน
            header("Location: home.php"); // เปลี่ยนเส้นทางไปยังหน้า Dashboard หลังจากเข้าสู่ระบบสำเร็จ
            exit();
        } else {
			exit('error: check usernaem or password');
		}
	}
}else{
	exit('error');
}


?>
