<?php
	session_start();
	unset($_SESSION);
	session_destroy();
	header('Location: http://www.domainedesdieux.com/myevents/index.php');
  	exit();
?>