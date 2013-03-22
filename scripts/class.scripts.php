<?php

			/* 1. check the file if it is in CSV format 2. check if the file has period code correctly, 3. check if the CSV filename has program 	code on it, 4. check if the file size is not too large,  */
	class scripts{
	
		function scripts(){
			$this->author = "Alison Perez";
			$this->email = "perez.alison@gmail.com";
			$this->desc = "holder of layout widgets";	
			
			$this->arr_buwan = array("1"=>"January","2"=>"February","3"=>"March","4"=>"April","5"=>"May","6"=>"June","7"=>"July","8"=>"August","9"=>"September","10"=>"October","11"=>"November","12"=>"December");
			$this->arr_program = array("mc"=>"Maternal Care","childcare"=>"Child Care","morbidity"=>"Morbidity Diseases","fp"=>"Family Planning");
			$this->arr_program_db = array("mc"=>"prog_maternal_care","childcare"=>"prog_child_care","morbidity"=>"prog_m2_bhs","fp"=>"prog_family_planning");
			
			$this->directory = "./csv/";
			$this->temp_directory = "./temp/";
		}
	
	
		function check_csv($csv_file){
			$error_msg = '';
//			print_r($_POST);
//			print_r($csv_file);
			
			$arr_file = explode('.',$csv_file["name"]);
			$arr_file_type = explode('_',$arr_file[0]);

			list($month, $taon) = explode('-',$arr_file_type[1]);
			


			if($csv_file["size"] <= 0):
				$error_msg .= "<br> - No file was uploaded";
			endif;
			
			$error_msg .= $this->check_extension($csv_file);
			$error_msg .= $this->check_file_size($csv_file,$_POST["MAX_FILE_SIZE"]);
			$error_msg .= $this->check_program_id($_POST["sel_program"],$arr_file_type[2]);
			
			if($_POST["sel_year"]!=$taon):
				$error_msg .= "<br><font class='warning_msg'> - The selected year does not match the year of the file submitted.</font>"; 
			endif; 
			
			if($_POST["sel_buwan"]!=$month):
				$error_msg .= "<br><font class='warning_msg'> - The selected month does not match the month of the file submitted.</font>"; 			
			endif;
			
			if($error_msg!=''):
				echo "<font class='warning_msg'>Cannot upload the file due to:";
				echo $error_msg;			
				echo "</font>";
			else:
				
			endif;
				return $error_msg;
		}
	
		function check_extension($filename){
			$arr_filename = explode('.',$filename["name"]);
			if($arr_filename[1]!='csv'):
				return "<br> - The file uploaded is not in csv format.";
			endif;
		}
		
		function check_file_size($filename,$max_filesize){
			if($filename["size"] > $max_filesize):
				return "<br> - The file uploaded has exceeeded 50KB.";			
			endif;
		}
		
		function check_program_id($selected_prog_id,$submit_prog_id){
			if($selected_prog_id != $submit_prog_id):
				return "<br> - The selected program does not match the file submitted."; 
			endif;
		
		}
		
		function check_month($selected_month, $submitted_month){
			if($selected_month == $submit_month):
			else:
				return "<br> - The selected month does not match the month of the file submitted"; 
			endif;					
		}
		
		function check_year($selected_year, $submitted_year){ 
			if($selected_year == $submit_year):
			else:
				return "<br> - The selected year does not match the year of the file submitted"; 
			endif;					
		}
		
		
		
		function process_file($csv_file){
			/* 
				1. Upload the file as 
			*/
			
			$new_file = './csv/'.$csv_file["name"];
	
			if(!file_exists($new_file)):
			
				if(move_uploaded_file($csv_file["tmp_name"],$new_file)):

					echo "<font class='warning_msg'>File was successfully been uploaded.</font>";
					return 1;		
				else:
					echo "<font class='warning_msg'>File was not uploaded.</font>";
					return 0;
				endif;
			
			else:
				echo "<font class='warning_msg'>The file ".$csv_file["name"]." already exists! Delete the file using table on the side before uploading the same file again.</font>";
				return 0;
			endif;
		}
		
		function parse_file_save($csv_file){
			$file_name = './csv/'.$csv_file["name"];
			
			if(file_exists($file_name)): 
				$file_handle = @fopen($file_name,'r');
				while(!feof($file_handle)){
					$lines[] = fgets($file_handle,4096);
				}

				//print_r($lines);
				$arr_csv_data = explode(',',$lines[0]);
				$arr_csv_data[0] = str_replace("'","",$arr_csv_data[0]);
				fclose($handle);

				$q_check = mysql_query("SELECT submission_id, file_name FROM file_submission WHERE file_name='$csv_file[name]'") or die("Cannot query 130: ".mysql_error());
				
				if(mysql_num_rows($q_check)==0):
					$q_insert = mysql_query("INSERT INTO file_submission SET facility_id='$arr_csv_data[0]',month='$_POST[sel_buwan]',year='$_POST[sel_year]',program_id='$_POST[sel_program]',file_name='$csv_file[name]',user_id_submitted='$_SESSION[userid]',date_submitted=NOW(),status='Pending'") or die("Cannot query 133: ".mysql_error());
					
					if($q_insert):
						echo " The submitted file was queued and will be reviewed. If you wish to re-submit again, please delete the file first using the table on the side.";
					endif;
				else:
					$q_update = mysql_query("UPDATE file_submission SET date_submitted=NOW() WHERE file_name='$csv_file[name]'") or die("Cannot query 139: ".mysql_error());
				
				endif;
				
			else:
				echo "Cannot save the file. File does not exists";
			endif;
			
		}
		
		function check_login(){
			$q_user = mysql_query("SELECT * FROM users WHERE user_login='$_POST[txt_username]' AND user_password='$_POST[txt_pwd]'") or die("Cannot query 142: ".mysql_error());
			
			if(mysql_num_rows($q_user)!=0):
				$arr_user = mysql_fetch_array($q_user);

				$_SESSION["userid"] = $arr_user[user_id];
				$_SESSION["facility_code"] = $arr_user[facility_code];
				$_SESSION["facility_name"] = $arr_user[facility_name];
				$_SESSION["user_type"] =  $arr_user[user_type];
				$_SESSION["user_name"] = $arr_user[user_fname].' '.$arr_user[user_lname];
				
				
				if($_SESSION["user_type"]!="admin"):
					// 1. get the facility id (DOH), 2. get the facility id (machine generated), 3. get the municipality id
					$q_municipality = mysql_query("SELECT psgc_citymuncode FROM m_lib_health_facility WHERE doh_class_id='$_SESSION[facility_code]'") or die("Cannot query 165: ".mysql_error());
					list($psgc_citymuncode) = mysql_fetch_array($q_municipality);

					$_SESSION["psgc_muncity"] = $psgc_citymuncode;

				endif;
			else:
				echo "User account not found.";
			endif;
		}
		
		function process_csv_submission($get_args){
			//print_r($get_args);
			if(isset($get_args["action"]) && $get_args["resp"]==''):
			echo "<br>";
			echo "<font class='warning_msg'>Are you sure you want to ".$get_args["action"]." the file  of ".$this->arr_program[$get_args["program"]]." for ".$this->arr_buwan[$get_args["month"]]." ".$get_args["year"]."? ";
			echo "<a href='$_SERVER[PHP_SELF]?id=upload&sub_id=$get_args[sub_id]&action=$get_args[action]&month=$get_args[month]&year=$get_args[year]&program=$get_args[program]&resp=Y'>Yes</a>&nbsp;&nbsp;&nbsp;";
			echo "<a href='$_SERVER[PHP_SELF]?id=upload&sub_id=$get_args[sub_id]&action=$get_args[action]&month=$get_args[month]&year=$get_args[year]&program=$get_args[program]&resp=N'>No</a>";
			endif;
			echo "</font>";

			
			$tbl_name = $this->arr_program_db[$get_args["program"]];
				
			if($get_args["action"]=='approve' && $get_args["resp"]=='Y'):


				$file_name = $this->directory.$this->get_file_name($get_args["sub_id"]);
				if(file_exists($file_name)):
				$file_handle = @fopen($file_name,'r');
					while(!feof($file_handle)){
						$lines[] = fgets($file_handle,4096);
//print_r($lines);
						if(!feof($file_handle))
							$this->insert_db($lines,$get_args,$tbl_name);
					}
				else:
					echo "The file does not exist in the server. Please delete the entry in table then re-upload the file again.";
				endif;
				

				
			elseif($get_args["action"]=='deny' || $_POST["haction"]=='deny'): 
				if($get_args["resp"]=='Y'): 
					echo "<form action='$_SERVER[PHP_SELF]?id=upload' method='POST'>";
					echo "<font class='warning_msg'>Please specify Reason for Denying<font class='warning_msg'>&nbsp;&nbsp;<input type='text' name='txt_deny'  size='50' />";
					echo "<input type='submit' name='submit_reason' value='Save' />";
					echo "<input type='hidden' name='hmonth' value='$_GET[month]' />";
					echo "<input type='hidden' name='hyear' value='$_GET[year]' />";
					echo "<input type='hidden' name='hsub_id' value='$_GET[sub_id]'>";
					echo "<input type='hidden' name='haction' value='$_GET[action]'>";
					echo "</form>";
				endif;
				
				if($_POST["submit_reason"]):
					//print_r($_POST);
					$q_deny = mysql_query("UPDATE file_submission SET status='Denied',reason_deny='$_POST[txt_deny]' WHERE submission_id='$_POST[hsub_id]' AND month='$_POST[hmonth]' AND year='$_POST[hyear]'") or die("Cannot query 204: ".mysql_error());
				endif;
				
				
			elseif($get_args["action"]=='delete' && $get_args["resp"]=='Y'):
				$q_get_file_name = mysql_query("SELECT facility_id, file_name, month, year FROM file_submission WHERE submission_id='$_GET[sub_id]' AND year='$_GET[year]' AND month='$_GET[month]'") or die("Cannot query 211: ".mysql_error());
				
				list($facility_id,$file_name,$month,$year) = mysql_fetch_array($q_get_file_name);				
				
				$month = sprintf("%02s",$month);

				if(unlink($this->directory.$file_name)):
					$q_del_file = mysql_query("DELETE FROM file_submission WHERE submission_id='$_GET[sub_id]' AND year='$_GET[year]' AND month='$_GET[month]'") or die("Cannot query 215: ".mysql_error());

					$q_del_record_prog = mysql_query("DELETE FROM ".$tbl_name." WHERE HFHUDCODE='$facility_id' AND MONTH='$month' AND YEAR='$year'") or die("Cannot query 218: ".mysql_error());		
								
					echo "The record and its corresponding file (".$file_name.") was successfully been deleted.";
				else:
					echo "The record was not deleted.";
				endif;
				
			elseif($get_args["action"]=='view'  && $get_args["resp"]=='Y'):
				$q_get_file_name = mysql_query("SELECT facility_id, file_name, month, year, program_id FROM file_submission WHERE submission_id='$_GET[sub_id]' AND year='$_GET[year]' AND month='$_GET[month]'") or die("Cannot query 229: ".mysql_error());
				
				list($facility_id,$file_name,$month,$year,$prog_id) = mysql_fetch_array($q_get_file_name);
				$this->create_temp_view_file($file_name,$prog_id);
				
			else:
			
			endif;
		}
		
		
		function insert_db($csv_content,$get_args,$tbl_name){
			
			$x = 0;			
			$q_fields = mysql_query("SHOW COLUMNS FROM ".$tbl_name) or die("Cannot query 204: ".mysql_error());
			//echo count($csv_content).'/';
			//echo mysql_num_rows($q_fields);
			
			switch($get_args["program"]){
				case 'mc':
				
					$arr_content = explode(',',$csv_content[0]);
					foreach($arr_content as $key=>$value){
						$arr_content[$key] = str_replace("'","",$arr_content[$key]);
					}
				
					//echo count($arr_content);
					if(count($arr_content)==mysql_num_rows($q_fields)): //print_r($arr_content);
						$q_search = mysql_query("SELECT HFHUDCODE, MONTH, YEAR FROM prog_maternal_care WHERE HFHUDCODE='$arr_content[0]' AND MONTH='$arr_content[5]' AND YEAR='$arr_content[6]'") or die("Cannot query 222: ".mysql_error());

						if(mysql_num_rows($q_search)==0):
						
						$q_insert = mysql_query("INSERT INTO ".$tbl_name." SET HFHUDCODE='$arr_content[0]',REGCODE='$arr_content[1]',PROVCODE='$arr_content[2]',CITYCODE='$arr_content[3]',BGYCODE='$arr_content[4]',MONTH='$arr_content[5]',YEAR='$arr_content[6]',PC1='$arr_content[7]',PC2='$arr_content[8]',PC3='$arr_content[9]',PC4='$arr_content[10]',PC5='$arr_content[11]',PP1='$arr_content[12]',PP2='$arr_content[13]',PP3='$arr_content[14]',PP4='$arr_content[15]',FINAL='$arr_content[16]'") or die("Cannot query 206: ".mysql_error());			
						else:
							echo "The approved file was already stored in the database. ";
						endif;
						
					else:	
						echo "The data sets in the file do not match the contents of the database. Please delete and re-upload the right file.";
					
					endif;
					
					break;

				case 'childcare': //uses falling through to execute both child care and fp codes
					/*$arr_content = explode(',',$csv_content[0]);
					$arr_field = array();
					foreach($arr_content as $key=>$value){
						$arr_content[$key] = str_replace("'","",$arr_content[$key]);
					}

					
					if(count($arr_content)==mysql_num_rows($q_fields)):
						
						while($r_field = mysql_fetch_array($q_fields)){ 
							array_push($arr_field,$r_field[0]);
						}									

						$q_record = mysql_query("SELECT * FROM $tbl_name WHERE HFHUDCODE='$arr_content[0]' AND REGCODE='$arr_content[1]' AND PROVCODE='$arr_content[2]' AND CITYCODE='$arr_content[3]' AND BGYCODE='$arr_content[4]' AND MONTH='$arr_content[5]' AND YEAR='$arr_content[6]'") or die("Cannot query 286: ".mysql_error());
						
						if(mysql_num_rows($q_record)==0):
							$q_insert = mysql_query("INSERT INTO $tbl_name SET HFHUDCODE='$arr_content[0]',REGCODE='$arr_content[1]', PROVCODE='$arr_content[2]',CITYCODE='$arr_content[3]',BGYCODE='$arr_content[4]',MONTH='$arr_content[5]',YEAR='$arr_content[6]'");
							
							for($i=7;$i<count($arr_content);$i++){
								$q_update = mysql_query("UPDATE $tbl_name SET $arr_field[$i]='$arr_content[$i]' WHERE HFHUDCODE='$arr_content[0]' AND REGCODE='$arr_content[1]' AND PROVCODE='$arr_content[2]' AND CITYCODE='$arr_content[3]' AND BGYCODE='$arr_content[4]' AND MONTH='$arr_content[5]' AND YEAR='$arr_content[6]'") or die("Cannot query 290: ".mysql_error());
							}
						else:
						
						endif;


					else:	
						echo "The data sets in the file do not match the contents of the database. Please delete and re-upload the right file.";					
					endif;				

					break;
				    */
				case 'fp':
					$arr_content = explode(',',$csv_content[0]);
					$arr_field = array();
					foreach($arr_content as $key=>$value){
						$arr_content[$key] = str_replace("'","",$arr_content[$key]);
					}
					
					if(count($arr_content)==mysql_num_rows($q_fields)):
						
						while($r_field = mysql_fetch_array($q_fields)){ 
							array_push($arr_field,$r_field[0]);
						}									

						$q_record = mysql_query("SELECT * FROM $tbl_name WHERE HFHUDCODE='$arr_content[0]' AND REGCODE='$arr_content[1]' AND PROVCODE='$arr_content[2]' AND CITYCODE='$arr_content[3]' AND BGYCODE='$arr_content[4]' AND MONTH='$arr_content[5]' AND YEAR='$arr_content[6]'") or die("Cannot query 286: ".mysql_error());
						
						if(mysql_num_rows($q_record)==0):
							$q_insert = mysql_query("INSERT INTO $tbl_name SET HFHUDCODE='$arr_content[0]',REGCODE='$arr_content[1]', PROVCODE='$arr_content[2]',CITYCODE='$arr_content[3]',BGYCODE='$arr_content[4]',MONTH='$arr_content[5]',YEAR='$arr_content[6]'");
							
							for($i=7;$i<count($arr_content);$i++){
								$q_update = mysql_query("UPDATE $tbl_name SET $arr_field[$i]='$arr_content[$i]' WHERE HFHUDCODE='$arr_content[0]' AND REGCODE='$arr_content[1]' AND PROVCODE='$arr_content[2]' AND CITYCODE='$arr_content[3]' AND BGYCODE='$arr_content[4]' AND MONTH='$arr_content[5]' AND YEAR='$arr_content[6]'") or die("Cannot query 324: ".mysql_error());
							}
						else:
						
						endif;


					else:	
						echo "The data sets in the file do not match the contents of the database. Please delete and re-upload the right file.";					
					endif;				
					
					break;
				
				case 'morbidity':
					$arr_field = array();
					
					$field_count = mysql_num_rows($q_fields);

					while($r_field = mysql_fetch_array($q_fields)){ 
						array_push($arr_field,$r_field[0]);
					}									

					foreach($csv_content as $key=>$value){
										
					$arr_content = explode(',',$value);
					foreach($arr_content as $key=>$value){
						$arr_content[$key] = str_replace("'","",$arr_content[$key]);
					}
				
					if(count($arr_content)==$field_count):			
						$q_record = mysql_query("SELECT * FROM $tbl_name WHERE HFHUDCODE='$arr_content[0]' AND REGCODE='$arr_content[1]' AND PROVCODE='$arr_content[2]' AND CITYCODE='$arr_content[3]' AND BGYCODE='$arr_content[4]' AND MONTH='$arr_content[5]' AND YEAR='$arr_content[6]' AND ICD10_CODE='$arr_content[7]'") or die("Cannot query 286: ".mysql_error());
						
						if(mysql_num_rows($q_record)==0):
							$q_insert_morb = mysql_query("INSERT INTO $tbl_name SET HFHUDCODE='$arr_content[0]',REGCODE='$arr_content[1]', PROVCODE='$arr_content[2]',CITYCODE='$arr_content[3]',BGYCODE='$arr_content[4]',MONTH='$arr_content[5]',YEAR='$arr_content[6]',ICD10_CODE='$arr_content[7]'") or die("Cannot query 355: ".mysql_error());
							if($q_insert_morb):
							
							for($i=8;$i<count($arr_content);$i++){
								$q_update = mysql_query("UPDATE $tbl_name SET $arr_field[$i]='$arr_content[$i]' WHERE HFHUDCODE='$arr_content[0]' AND REGCODE='$arr_content[1]' AND PROVCODE='$arr_content[2]' AND CITYCODE='$arr_content[3]' AND BGYCODE='$arr_content[4]' AND MONTH='$arr_content[5]' AND YEAR='$arr_content[6]' AND ICD10_CODE='$arr_content[7]'") or die("Cannot query 324: ".mysql_error());
							}
							
							endif;
						else:
						
						endif;


					else:	
						echo "The data sets in the file do not match the contents of the database. Please delete and re-upload the right file.";					
					endif;				
					
					
					}
					break;
				
				default:
					break;
			}
			
			if($q_insert || $q_update):
				$q_update_file = mysql_query("UPDATE file_submission SET date_approved=NOW(), status='Approved', user_id_approved='$_SESSION[userid]' WHERE submission_id='$get_args[sub_id]' AND month='$get_args[month]' AND year='$get_args[year]'") or die("Cannot query 237: ".mysql_error());
				if($q_update_file):
					echo "The file was successfully been stored in the database.";
					$x++;
				else:
				endif;	
			else:
				echo "The file was not stored in the database.";
			endif;
			
		}
		
		function get_file_name($sub_id){
			$q_get_filename = mysql_query("SELECT file_name FROM file_submission WHERE submission_id='$sub_id'") or die("Cannot query 197 ".mysql_error());
			
			list($file_name) = mysql_fetch_array($q_get_filename);
			return $file_name;
		}
		
		function process_user($function){
			//print_r($_POST);	
			
			if(empty($_POST[txt_fname]) && empty($_POST[txt_lname]) && empty($_POST[txt_login]) && empty($_POST[txt_pwd])):
				echo "<script language='Javascript'>";
				echo "window.alert('Cannot save user details. Please complete the form.')";
				echo "</script>";
//				echo "Cannot save user details. Please complete the form.";
			else:
				if(isset($_POST["h_userid"]) && $function=='update'):
					$q_select = mysql_query("SELECT user_id FROM users WHERE user_login='$_POST[txt_login]' AND user_id!='$_POST[h_userid]'") or die("Cannot query 301: ".mysql_error()); 
				else:
					$q_select = mysql_query("SELECT user_id FROM users WHERE user_login='$_POST[txt_login]'") or die("Cannot query 301: ".mysql_error()); 				
				endif;
				
				if(mysql_num_rows($q_select)!=0):
					echo "<script language='Javascript'>";
					echo "window.alert('Login name already exists. Please use a different login name.')";
					echo "</script>";					
					//echo "Login name already exists. Please use a different login name.";
				else:
					$q_hf = mysql_query("SELECT facility_name FROM m_lib_health_facility WHERE doh_class_id='$_POST[sel_hf]'") or die("Cannot query 305: ".mysql_error());
					
					list($hf) = mysql_fetch_array($q_hf);

					if(isset($_POST["h_userid"]) && $function=='update'):
						$q_insert_user = mysql_query("UPDATE users SET user_login='$_POST[txt_login]',user_password='$_POST[txt_pwd]',user_fname='$_POST[txt_fname]',user_lname='$_POST[txt_lname]',facility_code='$_POST[sel_hf]',psgc_citymuncode='$_POST[sel_mun]',facility_name='$hf',user_type='$_POST[sel_user_type]',user_email='$_POST[txt_email]' WHERE user_id='$_POST[h_userid])'") or die("Cannot query 321 ".mysql_error());
					else:					
						$q_insert_user = mysql_query("INSERT INTO users SET user_login='$_POST[txt_login]',user_password='$_POST[txt_pwd]',user_fname='$_POST[txt_fname]',user_lname='$_POST[txt_lname]',facility_code='$_POST[sel_hf]',psgc_citymuncode='$_POST[sel_mun]',facility_name='$hf',user_type='$_POST[sel_user_type]',user_email='$_POST[txt_email]'") or die("Cannot query 309 ".mysql_error());
					endif;
					
					
					if($q_insert_user):
							echo "<script language='Javascript'>";
							echo "window.alert('User details were successfully been saved!')";
							echo "</script>";					
							//echo "User details was successfully been saved!";
					else:
							echo "<script language='Javascript'>";
							echo "window.alert('User details were not saved!')";
							echo "</script>";										
							//echo "User details was not saved!";					
					endif;
				endif;
			endif;	
		}									


	function show_download_file(){
			$arr_hf = array();
			
			if($_SESSION["user_type"]=='admin'):
			  	$q_file_submission = mysql_query("SELECT a.facility_id, b.facility_name, a.file_name, a.month, a.year, a.program_id,a.date_submitted,a.submission_id FROM file_submission a, m_lib_health_facility b  WHERE a.facility_id=b.doh_class_id ORDER by a.year DESC,a.month DESC, b.facility_name ASC, a.program_id ASC") or die("Cannot query 452: ".mysql_error());
			elseif($_SESSION["user_type"]=='manager'):
	  			$q_file_submission = mysql_query("SELECT a.facility_id, b.facility_name, a.file_name, a.month, a.year, a.program_id,a.date_submitted,a.submission_id FROM file_submission a, m_lib_health_facility b  WHERE a.facility_id=b.doh_class_id AND a.facility_id='$_SESSION[facility_code]' ORDER by a.year DESC, a.month DESC, a.program_id ASC") or die("Cannot query 454: ".mysql_error()); 			
			
			elseif($_SESSION["user_type"]=='municipality_user'):
				$q_hf = mysql_query("SELECT doh_class_id FROM m_lib_health_facility WHERE psgc_citymuncode='$_SESSION[psgc_muncity]'") or die("Cannot query 237: ".mysql_error());
				
				while(list($doh_class_id)=mysql_fetch_array($q_hf)){
					$doh_class_id = "'".$doh_class_id."'";
					array_push($arr_hf,$doh_class_id);
				}
				$str_hf = implode(",",$arr_hf);
				
				$q_file_submission = mysql_query("SELECT a.facility_id, b.facility_name, a.file_name, a.month, a.year, a.program_id,a.date_submitted,a.submission_id FROM file_submission a, m_lib_health_facility b  WHERE a.facility_id=b.doh_class_id AND a.facility_id IN ($str_hf) ORDER by  a.year DESC,a.month DESC, a.program_id ASC") or die("Cannot query 454: ".mysql_error()); 			
				
				
			else:
			  	$q_file_submission = mysql_query("SELECT a.facility_id, b.facility_name, a.file_name, a.month, a.year, a.program_id,a.date_submitted,a.submission_id FROM file_submission a, m_lib_health_facility b  WHERE a.facility_id=b.doh_class_id ORDER by a.year DESC,a.month DESC, b.facility_name ASC, a.program_id ASC") or die("Cannot query 452: ".mysql_error());
			endif;
	
		if(mysql_num_rows($q_file_submission)!=0):
			echo "<table>";
			echo "<tr class='textbox-design'><td colspan='6'>LIST OF UPLOADED CSV FILES</td></tr>";
			echo "<tr class='col-header'>";
			echo "<td>&nbsp;Facility Name&nbsp;</td>";
			echo "<td>&nbsp;Month&nbsp;</td>";		
			echo "<td>&nbsp;Year&nbsp;</td>";	
			echo "<td>&nbsp;Program&nbsp;</td>";				
			echo "<td>&nbsp;Download File&nbsp;</td>";								
			echo "<td>&nbsp;Download EFHSIS Format&nbsp;</td>";			
			echo "</tr>";
		
			while(list($facility_id,$facility_name,$file_name,$month,$year,$program,$date_submitted,$sub_id)=mysql_fetch_array($q_file_submission)){
				echo "<tr class='col-contents'>";
				echo "<td>$facility_name</td>";
				echo "<td>".$this->arr_buwan[$month]."</td>";
				echo "<td>$year</td>";
				echo "<td>$program</td>";									
				echo "<td><a href='$_SERVER[PHP_SELF]?id=download&file_id=$sub_id&type=csv'><img src='./images/download.png' height='25' width='25' /></a></td>";			
				echo "<td><a href='$_SERVER[PHP_SELF]?id=download&file_id=$sub_id&type=zip'><img src='./images/convert.png' height='25' width='25' /></a></td>";
				echo "</tr>";		
			}			
			echo "</table>";
	
		else:

		endif;
	
	}
	
	function download_csv(){
		$q_file_name = mysql_query("SELECT file_name FROM file_submission WHERE submission_id='$_GET[file_id]'") or die("Cannot query 486: ".mysql_error());

		list($filename) = mysql_fetch_array($q_file_name);		
		
		$file_path = $this->directory.$filename;

//		echo $file;
		
		if ($fd = fopen ($file_path, "r")) {
			$fsize = filesize($file_path);
			$path_parts = pathinfo($file_path);

			header("Content-type: application/csv");
			header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
			header("Content-length: $fsize");
			header("Cache-control: private"); //use this to open files directly
			while(!feof($fd)) {
				$buffer = fread($fd, 2048);
				echo $buffer;
			}
		}
		fclose ($fd);
		exit;		
				
	}


	function create_temp_csv(){
		
		$q_file = mysql_query("SELECT file_name,program_id FROM file_submission WHERE submission_id='$_GET[file_id]'") or die("Cannot query 521: ".mysql_error());		
		list($file_name,$prog_id) = mysql_fetch_array($q_file);
		$prog_label = substr($this->arr_program_db[$prog_id],5);
		$new_name = $prog_label.'.csv';

		if(copy($this->directory.$file_name,$this->temp_directory.$new_name)):
			chdir($this->temp_directory);
			$this->create_zip(array($new_name),$prog_label.'.zip');
			rename($prog_label.'.zip',$prog_label.'.doh');
			
			$file_path = $prog_label.'.doh';
			
			if ($fd = fopen ($file_path, "r")) {
				$fsize = filesize($file_path);
				$path_parts = pathinfo($file_path);

				header("Content-type: application/zip");
				header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
				header("Content-length: $fsize");
				header("Cache-control: public"); //use this to open files directly
				while(!feof($fd)) {
					$buffer = fread($fd, 2048);
					echo $buffer;
				}
			}
			fclose ($fd);
			exit;					
			
		else:
			echo 'CSV File Not copied to the temp folder!';
		endif;	
			
	}

	function create_zip($files = array(),$destination = '',$overwrite = false) { 
		//echo $destination;
		//if the zip file already exists and overwrite is false, return false
		if(file_exists($destination) && !$overwrite) { return false; }
		//vars
		$valid_files = array();
		//if files were passed in...
		if(is_array($files)) { 
			//cycle through each file
			foreach($files as $file) { 
				//make sure the file exists
				if(file_exists($file)) {
					$valid_files[] = $file;
				}
			}
		}
		//if we have good files...
		if(count($valid_files)) {
			//create the archive
			$zip = new ZipArchive();
			if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
				return false;
			}
			//add the files
			foreach($valid_files as $file) {
				$zip->addFile($file,$file);
			}
			//debug
			//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
			
			//close the zip -- done!
			$zip->close();

			//check to make sure the file exists
			return file_exists($destination);
		}
		else
		{ 
			return false;
		}
	}


	function create_temp_view_file($file_name,$prog_id){
		
		//$q_file = mysql_query("SELECT file_name,program_id FROM file_submission WHERE submission_id='$_GET[file_id]'") or die("Cannot query 629: ".mysql_error());		
		//list($file_name,$prog_id) = mysql_fetch_array($q_file);

		if(copy($this->directory.$file_name,$this->temp_directory.$file_name)):
			chdir($this->temp_directory);
			$file_path = $file_name;
						
			if ($fd = fopen ($file_path, "r")) {
				$fsize = filesize($file_path);
				$path_parts = pathinfo($file_path);
				
				echo $this->arr_program_db[$prog_id];
			
/*				header("Content-type: application/csv");
				header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
				header("Content-length: $fsize");
				header("Cache-control: public"); //use this to open files directly
				while(!feof($fd)) {
					$buffer = fread($fd, 2048);
					echo $buffer;
				} */
			}
			fclose ($fd);
			exit;					
			
		else:
			echo 'CSV File Not copied to the temp folder!';
		endif;	
			
	}

	} //end class	
?>