<?php
error_reporting(E_ALL ^ E_DEPRECATED);
require_once('Database.php');
if ((isset($_POST['email']) && isset($_POST['password'])) && (!empty($_POST['email']) && !empty($_POST['password']))) {
	$Email = ($_POST['email']);
	$Password = ($_POST['password']);
	$DB = new Database();
	$validation = $DB->loginAsUser($Email, $Password);
	if ($validation == "error") {
        echo "<script>alert('Invalid login credentials. Please try again.'); window.location.href='../Login.php';</script>";
		// header('Location: Login.php');
	} elseif ($validation == "invalid") {
		echo "<script>alert('Invalid login credentials. Please try again.'); window.location.href='../Login.php';</script>";
		// header('Location: Login.php');
	} else {
		session_start();
		$_SESSION["UserID"] = $validation;
		header('Location: ..admin/');
	}
} 
?>
