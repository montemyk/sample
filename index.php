<?php
  session_start();
  include("layout/layout.php");
  include("db.php");
  include("scripts/class.scripts.php");
  connect_db();
  $layout = new layout();
  $script = new scripts();

	if($_GET["id"]=='download'): 
		if(!empty($_GET["file_id"])):
			if($_GET["type"]=='csv'):
				$script->download_csv();
			elseif($_GET["type"]=='zip'):
				/* 1. retrieve the file name based on the file_id
				   2. rename the file name such that the format is program_id.csv
				   3. store the file name csv into a temporary directory. Overwrite an existing file if any
				   4. Create a zip file of the program_id.csv
				   5. Rename the .zip file into .doh file 
				   6. Send the .doh file to screen for download
				*/
				$script->create_temp_csv();					
				//$script->create_zip();
			else:
			endif;			
		endif;
	endif;
  
echo "<html>";
echo "<head>";
echo "<link href='design.css' rel='stylesheet' type='text/css' />";
echo "<script>";
?>

function autoSubmit()
{  
	var formObject = document.forms["form_show_stats"];
	if(formObject!=0){
		formObject.submit();
	}
}


<?php

echo "</script>";



echo "</head>";

echo "<body>";
//print_r($_SESSION);


  echo "<table bgcolor='FFFFFF' width='100%'>";

  $layout->display_banner();

  if($_POST["submit_login"]):
	  $script->check_login();
  elseif($_POST["submit_logout"]):
  	session_unset();
  else: 
  endif;
   
  if($_SESSION["userid"]!=''):
   $layout->show_menu();
  
  switch($_GET["id"]){
  
  case "stats":
  	$layout->show_stats();
	echo "<tr><td>";
	$layout->disp_tabular_form();
	echo "</td></tr>";
  	break;
	
  case "upload":
    echo "<tr>";
	echo "<td valign='top'>";
   	$layout->ui_upload();

  	if($_POST["submit_csv"]):
			$error_msg = $script->check_csv($_FILES["csv_file"]);
			if($error_msg==''):
				if($script->process_file($_FILES["csv_file"])):
					$script->parse_file_save($_FILES["csv_file"]);					
				endif;
			endif;
	endif;
	echo "</td><td valign='top'>";
	$script->process_csv_submission($_GET);
   	$layout->show_csv_submission();
	echo "</td></tr>";
	
  	break;
	
  case 'download': 	
	$script->show_download_file();	
  	break;

  case 'map':
	$layout->show_map();
	break;
	
  case 'user': 
   		if($_POST["submit_user"]=='Save User Details'):
			$script->process_user('insert');
		elseif($_POST["submit_user"]=='Edit User Details'):
			$script->process_user('update');
		else:
		
		endif;
				
  		$layout->show_user_management_form();
		$layout->show_user_list();
  		break;		
  default:
	  	$layout->show_stats();
		echo "<tr><td>";
		$layout->disp_tabular_form();
		echo "</td></tr>";
		
		
		//$layout->show_swf();
	
  		break;
  }
  
  else: 
  	$layout->show_login_form(); 

	echo "<table border='1'>";
	echo "<tr>";
	
	echo "<td>";
	$layout->show_swf();
	echo "<br>";
	$layout->show_shoutbox();
	echo "</td>";

	echo "<td valign='top'>";
	$layout->show_stats_box();
	echo "</td>";
	echo "</tr>";

	echo "</table>";
	
  endif;
  
  echo "</table>";
  
  
$layout->footer();

echo "</body>";
echo "</html>";

?>