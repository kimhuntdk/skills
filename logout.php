<?php
@ob_start();
@session_start();


unset($_SESSION["eng_ning_ses"]);

session_destroy();
			echo"<script>window.location='login.php';</script>";
?>
