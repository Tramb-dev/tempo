<?php session_start(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>TEMPO</title>
<meta name="Keywords" content="strep project, tempo, circadian timing system, neurodegenerative, metabolic diseases, cell cycling, therapeutic index">
<meta name="description" content="Coordinated progress at European level in medicine and Improve human health The circadian timing system determines the occurrence of important events in the time course of cardiovascular, malignant, cerebrovascular, neurodegenerative or metabolic diseases, which are leading causes of human mortality and morbidity in Europe. Excess or defective cell cycling, apoptosis and/or DNA repair are mechanisms that are shared by these diseases. Both of these rhythmic systems also control the pharmacology determinants of many drugs that are potentially toxic. TEMPO addresses the issue of the interactions between pharmacogenomics determinants of drug activity and two essential biological cycles for chronic disease processes, i.e. the circadian clock and the cell cycle. TEMPO will offer the first proof of principle that tailored dynamic drug delivery schedules based on these interactions critically improve therapeutic index.">
<meta name="robots" content="All">
<meta http-equiv="content-language" content="french">
<meta name="classification" content="script php">
<meta name="author" content="peel.fr">
<meta name="publisher" content="peel.fr">
<meta http-equiv="expires" content="0">
<meta http-equiv="Pragma" content="no-cache">
<meta name="robots" content="index, follow, all">
<meta name ="search engines" content="AltaVista, AOLNet, Infoseek, Excite, Hotbot, Lycos, Magellan, LookSmart, CNET, voila, google, yahoo, alltheweb, msn, netscape, nomade, tiscali">
<meta name="Updated" content="daily">
<meta name="revisit-after" content="10 days">
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<link rel="stylesheet" media="screen" type="text/css" title="Design" href="css/base.css" />
</head>

<body>
<?php include_once('main/start.php'); ?>
<div align="center"><a href="index.php"><img src="img/banniere.jpg" /></a></div>

<div id="header">
<?php
//Manage the personnal box
if (Tempo::$session->is_logged())
{
	echo '<a href="account.php">My account</a> - ';
	echo '<a href="logout.php">Logout</a>';
}
else
{
	echo '<a href="login.php">Login</a>';
}
?>
</div>
<h1>Welcome on the Tempo Database.</h1><br />

<div class="menu">
	<a href="index.php">Index</a><br /><br />
    <a href="post_exp.php">Enter data</a><br /><br />
    <a href="data.php">Consult data</a><br /><br />
    <a href="doc.php">Documentation</a><br /><br />
    <a href="members.php">See members</a>
</div>
