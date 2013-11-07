<?php

	function connect_db(){

	$dbname = 'stats_new';
	$dbuser = 'root';
	$dbpass = 'root';

	$dbconn = mysql_connect("localhost",$dbuser,$dbpass) or die("Cannot query 7 ".mysql_error());
   	$dbselect = mysql_select_db($dbname,$dbconn);
	
	}

?>