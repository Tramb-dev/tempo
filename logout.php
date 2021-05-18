<?php
	include 'header.php';
	
	Tempo::$session->logout($_SESSION['sid']);
	Http::redirect('index.php', 0);
?>