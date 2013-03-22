<?php
	session_start();

	if($_SESSION["userid"]!=''):	
	$dbuser = 'root'; //set the db user 
	$dbpass = '';	  //set the db password
	$dbname = '';	  //set the name of the databse to be cleared up
	
	$dbconn = mysql_connect("localhost",$dbuser,$dbpass) or die("Cannot query 7 ".mysql_error());
   	$dbselect = mysql_select_db($dbname,$dbconn);    
	
	$arr_table_truncate = array("file_submission","prog_child_care","prog_dental_health","prog_environmental_health","prog_family_planning","prog_filariasis","prog_leprosy","prog_m2_bhs","prog_malaria","prog_maternal_care","prog_mortality","prog_mort_bhs","prog_natality","prog_schistosomiasis","prog_tuberculosis","users"); 
	
	if($_GET["resp"]=='Y'):
		foreach($arr_table_truncate as $key=>$tbl_name){
			if($tbl_name=='users'):
				$db_truncate  = mysql_query("DELETE FROM users WHERE user_id!='1'") or die("Cannot query 14: ".mysql_error()); //delete all users except the default alison account
			else:
				$db_truncate  = mysql_query("TRUNCATE table $tbl_name") or die("Cannot query 14: ".mysql_error());
			endif;
			
			if($db_truncate):
				echo "Table ".$tbl_name." is now empty.<br>";
			endif;
		}
		
	endif;
	
	else:
		echo "Unauthorized access to this page is not allowed.";
	endif;	
?>