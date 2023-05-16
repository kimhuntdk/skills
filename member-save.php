<meta charset="utf-8">
<?php
require_once("SendGmailSMTP/PHPMailer/PHPMailerAutoload.php");
date_default_timezone_set( "Asia/Bangkok" );
require_once( "inc/db_connect.php" );
$mysqli = connect();
// ตรวจสอบว่าผู้ใช้กด submit แล้วหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าที่ผู้ใช้กรอก
    //echo 1;
    $email = test_input($_POST["email"]);
    $password = test_input($_POST["password"]);
    $confirm_password = test_input($_POST["confirm_password"]);
    $email = strtolower($email);
    
    // ตรวจสอบความถูกต้องของข้อมูลที่รับเข้ามา
    if (validatePassword($password) && validateConfirmPassword($password, $confirm_password) && validateEmail($email)) {
       // เข้ารหัสรหัสผ่าน
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmtc = $mysqli->prepare("SELECT email FROM skills_member WHERE email = ?");
        $stmtc->bind_param("s", $email);
        $stmtc->execute();
        $stmtc->store_result();
        if ($stmtc->num_rows > 0) {
            exit("อีเมล์นี้ถูกลงทะเบียนแล้ว/This email has already been registered.");
        } 
        
        $stmtc->close();
        // บันทึกข้อมูลในฐานข้อมูล หรือทำอย่างอื่นตามที่ต้องการ
        // เตรียมคำสั่ง SQL สำหรับเพิ่มข้อมูลในตาราง users โดยใช้ Prepared Statements
        $verificationKey = bin2hex(openssl_random_pseudo_bytes(32));
        $inactive= "inactive";

        $stmt = $mysqli->prepare("INSERT INTO english_exam_member (email, password, verificationKey, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $email, $hashed_password,$verificationKey, $inactive);
       // $sql = $stmt->sqlstate;
       // echo "คำสั่ง SQL: " . $sql;   

       // แสดงค่าของตัวแปรที่ผูกไว้
// echo "ค่าตัวแปร email: " . $email . "<br>";
// echo "ค่าตัวแปร hashed_password: " . $hashed_password . "<br>";
// echo "ค่าตัวแปร verificationKey: " . $verificationKey . "<br>";
// echo "ค่าตัวแปร inactive: " . $inactive . "<br>";
        // ทำการเพิ่มข้อมูลลงในฐานข้อมูล
        if ($stmt->execute()) {
            //echo 3;
            // บันทึกข้อมูลใน session หรือทำอย่างอื่นตามที่ต้องการ
            $url_user = "https://gradis.msu.ac.th/english-training/verify.php?id=".base64_encode($verificationKey);
            //$url_user = "http://localhost/2023/english-training/verify.php?id=".base64_encode($verificationKey);
            // $url_ = "https://edusoftlearning.com";
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = "tls"; //ตรงส่วนนี้ผมไม่แน่ใจ ลองเปลี่ยนไปมาใช้งานได้
            $mail->Host = "smtp.gmail.com";
            $mail->Port = 587;  //ตรงส่วนนี้ผมไม่แน่ใจ ลองเปลี่ยนไปมาใช้งานได้
            $mail->isHTML();
            $mail->CharSet = "utf-8"; //ตั้งเป็น UTF-8 เพื่อให้อ่านภาษาไทยได้
            $mail->Username = "graduate@msu.ac.th"; //ให้ใส่ Gmail ของคุณเต็มๆเลย
            $mail->Password = "Grad@123456789"; // ใส่รหัสผ่าน
            $mail->SetFrom = ('graduate@msu.ac.th'); //ตั้ง email เพื่อใช้เป็นเมล์อ้างอิงในการส่ง ใส่หรือไม่ใส่ก็ได้ เพราะผมก็ไม่รู้ว่ามันแาดงให้เห็นตรงไหน
            $mail->FromName = "บัณฑิตวิทยาลัย มมส"; //ชื่อที่ใช้ในการส่ง
            $mail->Subject = "[English Training Program]";  //หัวเรื่อง emal ที่ส่ง
            $mail->Body = "เรียน $email
        <br>เรื่อง ยืนยันการสมัครการใช้งานระบบ มหาวิทยาลัยมหาสารคาม <br>
        ระบบ สมัครสอบอบรมภาษาอังกฤษ (ED) บัณฑิตวิทยาลัย มหาวิทยาลัยมหาสารคาม 
        Subject Confirmation of application for use of the system Mahasarakham University
        English Language Training (ED) Application System, Graduate School Mahasarakham University Confirm your subscription to use the system. Please click here to confirm the registration of the system.
          ยืนยันการสมัครการใช้งานระบบ
          กรุณาคลิกยืนยีนการลงทะเบียนการใช้งานระบบ  $url_user
        
        <br><br>
        จึงเรียนมาเพื่อโปรดทราบ <br>
        งานระบบสารสนเทศ บัณฑิตวิทยาลัย มหาวิทยาลัยมหาสารคาม <br>
        สอบถามเพิ่มเติมได้ที่ 043-754412 ต่อ 1636 <br>
         email : graduate@msu.ac.th  
         
        <br><br>
        
        
        "; //รายละเอียดที่ส่ง
          //  $mail->AddAddress('graduate@msu.ac.th','เจ้าหน้าที่ผู้รับผิดชอบจัดอบรมภาษาอักฤษ'); //อีเมล์และชื่อผู้รับ
            //$mail->AddAddress('graduate@msu.ac.th','เจ้าหน้าที่'); //อีเมล์และชื่อผู้รับ
            $mail->AddAddress($email,'ผู้สมัครเข้าอบรม'); //อีเมล์ผู้สมัคร
            //$mail->addCC("sarinya.k@msu.ac.th");
            $mail->addBCC("jakkrid.b@msu.ac.th");
            //ส่วนของการแนบไฟล์ ซึ่งทดสอบแล้วแนบได้จริงทั้งไฟล์ .rar , .jpg , png ซึ่งคงมีหลายนามสกุลที่แนบได้
            //$mail->AddAttachment("files/1.rar");
            //ตรวจสอบว่าส่งผ่านหรือไม่
            $mail->Send();
         
            echo"<script>alert('ลงทะเบียนสำเร็จ! กรุณายืนยันการลงทะเบียนผ่านอีเมล์/Successful registration! Please confirm your registration via email.'); window.location='login.php';</script>";
        } else {
            echo "เกิดข้อผิดพลาดในการเพิ่มข้อมูล: " . $mysqli->error;
        }

        // ปิดการเชื่อมต่อฐานข้อมูล
        $stmt->close();
        $mysqli->close();
        // แสดงข้อความสำเร็จหรือทำ redirection ไปหน้าอื่น
        // echo "ลงทะเบียนสำเร็จ!";
    } else {
        // กรณีข้อมูลไม่ถูกต้อง
      
        echo"<script>alert('กรุณากรอกข้อมูลให้ถูกต้อง/Please enter correct information..'); window.location='login.php';</script>";
    }
}

// ฟังก์ชันสำหรับตรวจสอบความถูกต้องของข้อมูล
function validateUsername($username) {
    // ตรวจสอบว่าชื่อผู้ใช้มีความยาวอย่างน้อย 4 ตัวอักษร
    if (strlen($username) >= 4) {
        return true;
    } else {
        return false;
    }
}

function validatePassword($password) {
    // ตรวจสอบว่ารหัสผ่านมีความยาวอย่างน้อย 6 ตัวอักษร
    if (strlen($password) >= 6) {
        return true;
    } else {
        return false;
    }
}

function validateConfirmPassword($password, $confirm_password) {
    // ตรวจสอบว่ารหัสผ่านและรหัสผ่านที่ยืนยันตรงกัน
    if ($password === $confirm_password) {
        return true;
    } else {
        return false;
    }
}

function validateEmail($email) {
    // ตรวจสอบความถูกต้องของอีเมล์โดยใช้ฟังก์ชัน filter_var()
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    } else {
        return false;
    }
}

// ฟังก์ชันสำหรับตรวจสอบและกรองข้อมูลที่รับเข้ามา
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
