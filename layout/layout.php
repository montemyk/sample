<?php
	class layout{
	
	function layout(){
		$this->author = "Alison Perez";
		$this->email = "perez.alison@gmail.com";
		$this->desc = "holder of layout widgets";
		
		$this->arr_buwan = array("1"=>"January","2"=>"February","3"=>"March","4"=>"April","5"=>"May","6"=>"June","7"=>"July","8"=>"August","9"=>"September","10"=>"October","11"=>"November","12"=>"December");
		$this->arr_program = array("mc"=>"Maternal Care","childcare"=>"Child Care","morbidity"=>"Morbidity Diseases","fp"=>"Family Planning");
		$this->arr_program_db = array("mc"=>"prog_maternal_care","childcare"=>"prog_child_care","morbidity"=>"prog_m2_bhs","fp"=>"prog_family_planning");
			
	}
	
	function display_banner(){
		
  		echo "<tr><td colspan='2'>";
		echo "<img src='images/stats.gif' width='1500px' height='120px'/>";	
		//echo "<img src='images/stats.gif' />";	
  		echo "</td>";
  		echo "</tr>";
	}
	
	function show_geographic_area(){
		$q_provcity = mysql_query("SELECT code_id,province_code,place_name FROM m_lib_psgc_code WHERE SUBSTRING(municipality_code,-2)='00' ORDER by place_name ASC") or die("Cannot query 9 ".mysql_error());

		if($_SESSION["user_type"]=='admin'):
			$q_municipality = mysql_query("SELECT code_id,municipality_code,place_name FROM m_lib_psgc_code WHERE SUBSTRING(barangay_id,-3)='000' AND province_code='369' AND SUBSTRING(municipality_code,-2)!='00' ORDER by place_name ASC") or die("Cannot query 9 ".mysql_error());
		else:
			$q_municipality = mysql_query("SELECT code_id,municipality_code,place_name FROM m_lib_psgc_code WHERE SUBSTRING(barangay_id,-3)='000' AND province_code='369' AND SUBSTRING(municipality_code,-2)!='00' AND municipality_code='$_SESSION[psgc_muncity]' ORDER by place_name ASC") or die("Cannot query 9 ".mysql_error());			
		endif;

		if(isset($_POST["sel_mun"])):
			if($_SESSION["user_type"]=='manager'):
				$q_rhu = mysql_query("SELECT facility_id, doh_class_id, facility_name FROM m_lib_health_facility WHERE psgc_citymuncode='$_POST[sel_mun]' AND doh_class_id='$_SESSION[facility_code]'") or die("Cannot query 10 ".mysql_error());
			else:
				$q_rhu = mysql_query("SELECT facility_id, doh_class_id, facility_name FROM m_lib_health_facility WHERE psgc_citymuncode='$_POST[sel_mun]'") or die("Cannot query 10 ".mysql_error());
			endif;
		endif;

//		echo "<form name='form_geography' method='POST' action='$_SERVER[PHP_SELF]'>";

		echo "<table align='center'>";
		echo "<tr>";
		echo "<td><select name='sel_prov' size='1'>";

		while($r_provcity=mysql_fetch_array($q_provcity)){
			if($r_provcity["place_name"]=='Tarlac'): 
				echo "<option value='$r_provcity[province_code]' selected='selected'>$r_provcity[place_name]</option>";
			else:
				echo "<option value='$r_provcity[province_id]'>$r_provcity[place_name]</option>";
			endif;
		}

		echo "</select></td>";

		echo "<td>";
		echo "<select name='sel_mun' size='1' onChange='autoSubmit();'>";

		echo "<option value='--'>--SELECT MUNICIPALITY--</option>";

		while($r_mun = mysql_fetch_array($q_municipality)){
			if($_POST["sel_mun"]==$r_mun["municipality_code"]):
				echo "<option value='$r_mun[municipality_code]' SELECTED>$r_mun[place_name]</option>";
			else:
				echo "<option value='$r_mun[municipality_code]'>$r_mun[place_name]</option>";
			endif;
		}

		echo "</select>";
		echo "</td>";
		echo "</tr>";	
		
		echo "<tr class='col-query-header' align='left'>";
		echo "<td colspan='2'>";
		
		if(!empty($_POST["sel_mun"])):
			echo "<table>";


		if(mysql_num_rows($q_rhu)!=0):
			$bilang = 0;
			while($r_rhu = mysql_fetch_array($q_rhu)){
				if(($bilang % 4)==0):
					echo "<tr>";
				endif;
				
				echo "<td valign='top' class='col-header-query'>";
			
				if($_POST["submit_query"]):
				
				if(in_array($r_rhu["facility_id"],$_POST["chk_hf"])):
					echo "<input type='checkbox' name='chk_hf[]' value='$r_rhu[facility_id]' CHECKED>$r_rhu[facility_name]";
					echo "</input>";
				else:
					echo "<input type='checkbox' name='chk_hf[]' value='$r_rhu[facility_id]'>$r_rhu[facility_name]";
					echo "</input>";				
				endif;
				
				else:
					echo "<input type='checkbox' name='chk_hf[]' value='$r_rhu[facility_id]' CHECKED>$r_rhu[facility_name]";
					echo "</input>";				
				
				endif;

				$q_brgy_hf = mysql_query("SELECT barangay_id FROM m_lib_health_facility_barangay WHERE facility_id='$r_rhu[facility_id]'") or die("Cannot query: 62".mysql_error());
				
				echo "<table>";
				if(mysql_num_rows($q_brgy_hf)!=0):
					echo "<tr><td>";
					echo "<table>";
					
					$x = 0; 
					
					while($r_brgy_hf=mysql_fetch_array($q_brgy_hf)){
						if(($x % 3)==0):
							echo "<tr class='table-row-notes'>";
						endif;
							echo "<td  valign='top'>&nbsp;";
							//echo "<input type='checkbox' name='$r_brgy_hf[barangay_id]'>";
							echo $this->get_brgy_name($r_brgy_hf["barangay_id"]);
							//echo "</input>";							
							echo "&nbsp;</td>";

						
						if(($x % 3)==2):
							echo "</tr>";
						 endif;
						 
						 $x++;	
						
					}
					echo "</table>";
					echo "</td></tr>";
				else:
					echo "<tr><td valign='top' class='warning_msg'>No barangays under this facility.</td></tr>";
				endif;
				
				echo "</table>";
	
				echo "</td>";
				
				if(($bilang % 4)==3):
					echo "</tr>";
				endif; 
				
				$bilang += 1;
			}
			
			echo "</tr></table>";

		endif;

		endif;
		echo "</td>";
		echo "</tr>";

		echo "</table>";

//		echo "</form>";
	}


	function get_brgy_name($brgy_id){ 
		$q_brgy = mysql_query("SELECT barangay_name FROM m_lib_barangay WHERE barangay_id='$brgy_id'") or die("Cannot query 97: ".mysql_error());
		list($barangay_name) = mysql_fetch_array($q_brgy);
		
		return $barangay_name;
		
	}

	function display_programs(){
		$arr_program = array("mc"=>"Maternal Care","childcare"=>"Child Care","morbidity"=>"Morbidity Diseases","fp"=>"Family Planning");

		echo "<select name='sel_program' value='1'>";
		foreach($arr_program as $key=>$value){
			echo "<option value='$key'>$value</option>";
		}
		
		echo "</select>";
	}
		
	function ui_upload(){
		$arr_buwan = array("1"=>"January","2"=>"February","3"=>"March","4"=>"April","5"=>"May","6"=>"June","7"=>"July","8"=>"August","9"=>"September","10"=>"October","11"=>"November","12"=>"December");
		
		echo "<form action='$_SERVER[PHP_SELF]?id=upload' method='POST' name='form_csv' enctype='multipart/form-data'>";
		echo "<table>";
		echo "<tr class='col-header'><td colspan='2'>&nbsp;Upload the CSV file (with HF code) generated from the WAH-EMR&nbsp;</td></tr>";
		echo "<tr class='textbox-label'><td>Select the period of submission</td>";
		echo "<td>";
		echo "<select name='sel_buwan' size='1'>";
		
		foreach($arr_buwan as $key=>$value){
			if($key==date('m')):
				echo "<option value='$key' SELECTED>$value</option>";
			else:
				echo "<option value='$key'>$value</option>";
			endif;
		}
		
		echo "</select>";
		echo "&nbsp;&nbsp;<select name='sel_year' size='1'>";
		for($i=2000;$i<(date('Y')+10);$i++){
			if($i==date('Y')):
				echo "<option value='$i' SELECTED>$i</option>";
			else:
				echo "<option value='$i'>$i</option>";
			endif;
		}
		echo "</select>";
		echo "</td>";
		echo "</tr>";
		
		echo "<tr class='textbox-label'>";
		echo "<td>Select Program</td><td>";	
		$this->display_programs();		
		echo "</td>";
		echo "</tr>";		
		
		echo "<tr class='textbox-label'><td>Select CSV file to upload (50 KB max)</td><td>";
		echo "<input type='hidden' name='MAX_FILE_SIZE' value='50000' />";
		echo "<input type='file' name='csv_file'></input>";
		echo "</td></tr>";
		echo "<tr class='textbox-label'>";
		echo "<td colspan='2' align='center'>";
		echo "<input type='submit' name='submit_csv' value='Submit File' />";
		echo "</td>";
		echo "</tr>";		
		echo "</table>";
		echo "</form>";
	}
	
	function show_csv_submission(){ 
		$arr_hf = array();
		
		if($_SESSION["user_type"]=='manager'):
			$q_csv = mysql_query("SELECT * FROM file_submission WHERE facility_id='$_SESSION[facility_code]' ORDER by year DESC,month DESC, date_submitted DESC, program_id ASC") or die("Cannmot query 234: ".mysql_error());
		elseif($_SESSION["user_type"]=='municipality_user'):
			$q_hf = mysql_query("SELECT doh_class_id FROM m_lib_health_facility WHERE psgc_citymuncode='$_SESSION[psgc_muncity]'") or die("Cannot query 237: ".mysql_error());
			while(list($doh_class_id)=mysql_fetch_array($q_hf)){
				$doh_class_id = "'".$doh_class_id."'";
				array_push($arr_hf,$doh_class_id);
			}
			
			$str_hf = implode(",",$arr_hf);
			
			$q_csv = mysql_query("SELECT * FROM file_submission WHERE facility_id IN ($str_hf) ORDER by year DESC,month DESC, date_submitted DESC, program_id ASC") or die("Cannmot query 237: ".mysql_error());		
		else: //admin and viewer
			$q_csv = mysql_query("SELECT * FROM file_submission ORDER by year DESC,month DESC, date_submitted DESC, program_id ASC") or die("Cannmot query 194: ".mysql_error());
		endif;	
		
		
		echo "<table>";
		echo "<tr class='textbox-design'><td colspan='9'>List of Submitted Reports and Status</td></tr>";
		echo "<tr class='col-header'>";
		echo "<td>&nbsp;Health Facility&nbsp;</td>";
		echo "<td>&nbsp;Month&nbsp;</td>";
		echo "<td>&nbsp;Year&nbsp;</td>";
		echo "<td>&nbsp;Program&nbsp;</td>";
		echo "<td>&nbsp;Date Submitted&nbsp;</td>";	
		echo "<td>&nbsp;Date Approved&nbsp;</td>";
		echo "<td>&nbsp;Status&nbsp;</td>";
		echo "<td>&nbsp;Submitted By&nbsp;</td>";
		echo "<td>&nbsp;Action&nbsp;</td>";
		echo "</tr>";
		
		
		if(mysql_num_rows($q_csv)!=0):
			while($r_csv = mysql_fetch_array($q_csv)){
				$q_fac_id = mysql_query("SELECT facility_name FROM m_lib_health_facility WHERE doh_class_id='$r_csv[facility_id]'") or die("Cannot query 270: ".mysql_error());
				list($facility_name) = mysql_fetch_array($q_fac_id);
				
				if($r_csv[status]=='Pending'):				
					echo "<tr class='col-contents_pending'>";							
				elseif($r_csv[status]=='Denied'):
					echo "<tr class='col-contents_denied'>";							
				else:
					echo "<tr class='col-contents'>";
				endif;
				
				echo "<td>".$facility_name."</td>";
				echo "<td>".$this->arr_buwan[$r_csv[month]]."</td>";
				echo "<td>".$r_csv[year]."</td>";				
				echo "<td>".$this->arr_program[$r_csv[program_id]]."</td>";				
				echo "<td>".$r_csv[date_submitted]."</td>";
				echo "<td>".$r_csv[date_approved]."</td>";

				if($r_csv[status]=='Denied'):
					echo "<td>".$r_csv[status]." (".$r_csv[reason_deny].") </td>";					
				else:
					echo "<td>".$r_csv[status]."</td>";	
				endif;

				
				$q_user = mysql_query("SELECT user_fname, user_lname FROM users WHERE user_id='$r_csv[user_id_submitted]'") or die("Cannot query 246: ".mysql_error());
				list($fname,$lname) = mysql_fetch_array($q_user);
				
				echo "<td>";
				echo $lname.', '.$fname;
				echo "</td>";
				
				echo "<td>";
				if($_SESSION["user_type"]=='admin' && $r_csv[status]=='Pending'):
					echo "<a href='$_SERVER[PHP_SELF]?id=upload&sub_id=$r_csv[submission_id]&action=approve&month=$r_csv[month]&year=$r_csv[year]&program=$r_csv[program_id]'><img src='./images/check.jpg' width='25' height='25' alt='Approve' /></a>&nbsp;&nbsp;&nbsp;";
					echo "<a href='$_SERVER[PHP_SELF]?id=upload&sub_id=$r_csv[submission_id]&action=deny&month=$r_csv[month]&year=$r_csv[year]&program=$r_csv[program_id]'><img src='./images/cross.png' width='25' height='25' alt='Deny' /></a>&nbsp;&nbsp;&nbsp;";
				endif;
								
				if($r_csv[status]=='Pending' || $r_csv[status]=='Denied' || $r_csv[status]=='Approved'):
					echo "<a href='$_SERVER[PHP_SELF]?id=upload&sub_id=$r_csv[submission_id]&action=delete&month=$r_csv[month]&year=$r_csv[year]&program=$r_csv[program_id]'><img src='./images/delete.jpg' width='25' height='25' alt='Delete' /></a>&nbsp;&nbsp;&nbsp;";
				else:
				endif;

				echo "<a href='$_SERVER[PHP_SELF]?id=upload&sub_id=$r_csv[submission_id]&action=view&month=$r_csv[month]&year=$r_csv[year]&program=$r_csv[program_id]'><img src='./images/view.png' width='25' height='25' alt='View' /></a>";
				
				echo "</td>";
				
				echo "</tr>";
			}
		endif;
		
		echo "</table>";	
	}
	
	function show_stats(){ 

		if($_POST["submit_query"]):
			//print_r($_POST);
		endif;
		
		  echo "<table>";
		  echo "<form action='$_SERVER[PHP_SELF]' name='form_show_stats' method='POST'>";
		  
		  echo "<tr><td valign='top'>";
		  
		  echo "<table>";
		  echo "<tr><td colspan='2' class='textbox-design'>SET VALUES FOR THE QUERY CATEGORIES</td></tr>";
		  echo "<tr class='textbox-label'>";
		  echo "<td valign='top'>&nbsp;&nbsp;Select Program&nbsp;&nbsp;</td><td>";
		  $this->display_programs();		  
		  echo "</td></tr>";
		  
		  echo "<tr class='textbox-label'><td>";
		  echo "&nbsp;&nbsp;Set Period&nbsp;&nbsp;</td><td>";
		  $this->display_period();
		  echo "</td>";
		  echo "</tr>";
		  
		  
		  echo "<tr><td>";		  
		  echo "<input type='submit' name='submit_query' value='Submit Query' />";
		  echo "</td>";
		  echo "</tr>";
		  		  
		  echo "</table>";
		  
		  echo "</td>";
		  echo "<td>&nbsp;&nbsp;&nbsp;</td>";
		  echo "<td valign='top'>";
		  echo "<table>";
  		  echo "<tr class='textbox-design'><td valign='top'>";
		  echo "Set Demographics</td></tr>";
		  
		  echo "<tr><td>";
		  $this->show_geographic_area();
		  echo "</td></tr>";
		  echo "</table>";		  
  		  echo "</td>";
		  
		  echo "</tr>";


		  echo "</form>";		  		  
		  echo "</table>";
	}

/*		  echo "<table>";
		  
		  echo "<tr><td>";
		  
		  $this->disp_tabular_form();
		  
		  echo "</td></tr>";
		  
		  echo "<tr>";
		  $this->disp_graph_form();
		  echo "</tr>";
		  
		  echo "</table>";
		
		  echo "</td>";
		  echo "</tr>";	 */

	function show_menu(){
		echo "<form action='' method='POST'>";
		echo "<tr bgcolor='99EECC'>";
		echo "<td colspan='2' align='right' valign='top'>";
		echo "<a href='$_SERVER[PHP_SELF]'><img src='./images/btn_home.png' width=100 height=40 /></a>";
		echo "<a href='$_SERVER[PHP_SELF]?id=upload'><img src='./images/btn_upload.png' width=100 height=40 /></a>";
		echo "<a href='$_SERVER[PHP_SELF]?id=stats'><img src='./images/btn_stat.png' width=100 height=40 /></a>";
		echo "<a href='$_SERVER[PHP_SELF]?id=map'><img src='./images/btn_map.png' width=100 height=40 /></a>";
		echo "<a href='$_SERVER[PHP_SELF]?id=download'><img src='./images/btn_download.png' width=100 height=40 /></a>";
		echo "<a href='$_SERVER[PHP_SELF]?id=user' alt='click to change user account details'><img src='./images/btn_account.png' width=100 height=42 /></a>"; 
		
		//echo $_SESSION[user_name].($_SESSION[facility_name]);
		echo "<input type='submit' name='submit_logout' value='Log Out'/>";
		echo "</td>";
		echo "</tr>";
		echo "</form>";
	}
	
	function show_login_form(){ 	
		echo "<tr align='right' bgcolor='99EECC'><td>";
		echo "<table>";
		echo "<form action='$_SERVER[PHP_SELF]' method='POST' name='form_login'>";
		echo "<tr>";
		echo "<td><font color='0055DD' face='verdana'><b>Login Name</font>&nbsp;&nbsp;&nbsp;";
		echo "<input type='text' name='txt_username' size='10' /></td>";
		echo "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
		echo "<td><font color='0055DD' face='verdana'><b>Password</b>&nbsp;&nbsp;&nbsp;";
		echo "<input type='password' name='txt_pwd' size='10' />&nbsp;&nbsp;&nbsp;</td>";			
		
		echo "<td>";
		echo "<input type='submit' name='submit_login' value='Login' />";
		echo "</td>";
		echo "</tr>";
		echo "</form>";
		
		echo "</table>";
		echo "</td></tr>";
	}
	
	function show_user_management_form(){
	
		
		echo "<form action='' method='POST'>";	

		if(isset($_GET["userid"]) && $_GET["action"]=='edit'):
				$q_user = mysql_query("SELECT * FROM users WHERE user_id='$_GET[userid]'");
				if(mysql_num_rows($q_user)!=0):
					list($user_id,$user_login,$user_password,$user_fname,$user_lname,$facility_code,$psgc_citymuncode,$facility_name,$user_type,$user_email) = mysql_fetch_array($q_user);
					echo "<input type='hidden' name='h_userid' value='$user_id' />";	
				endif;
		endif;
				
		echo "<table>";
		echo "<tr><td colspan='2' class='textbox-design'>USER INFORMATION FORM</td></tr>";
		
		echo "<tr><td align='left' colspan='2' class='table-row-notes'>NOTE: Health facility is where the facility where the end-user is associated. Please fill out everything.</td></tr>";
				
		echo "<tr class='textbox-label'><td>First Name</td><td>";
		echo "<input type='text' name='txt_fname' size='20' value='$user_fname' />";
		echo "</td></tr>";
		
		echo "<tr class='textbox-label'><td>Last Name</td><td>";
		echo "<input type='text' name='txt_lname' size='20' value='$user_lname' />";
		echo "</td></tr>";
		
		echo "<tr class='textbox-label'><td>Login Name</td><td>";
		echo "<input type='text' name='txt_login' size='20' value='$user_login' />";
		echo "</td></tr>";
		
		echo "<tr class='textbox-label'><td>Password</td><td>";
		echo "<input type='password' name='txt_pwd' size='20' value='$user_password' />";
		echo "</td></tr>";
		
		echo "<tr class='textbox-label'><td>Municipality</td><td>";
		$q_municipality = mysql_query("SELECT * FROM m_lib_psgc_code WHERE province_code='369' AND SUBSTRING(barangay_id,-3)='000' AND SUBSTRING(municipality_code,-2)!='00' ORDER by place_name ASC") or die("Cannot query 350: ".mysql_error());
		echo "<select name='sel_mun' size='1'>";
			while($r_mun = mysql_fetch_array($q_municipality)){
				if($r_mun["municipality_code"]==$psgc_citymuncode):					
					echo "<option value='$r_mun[municipality_code]' SELECTED>$r_mun[place_name]</option>";				
				else:
					echo "<option value='$r_mun[municipality_code]'>$r_mun[place_name]</option>";				
				endif;
			}
		echo "</select>";
		echo "</td></tr>";
		
		echo "<tr class='textbox-label'><td>Health Facility</td><td>";

		$q_rhu = mysql_query("SELECT facility_id, doh_class_id, facility_name FROM m_lib_health_facility WHERE psgc_provcode='369' ORDER by facility_name") or die("Cannot query 360 ".mysql_error());
		
		echo "<select name='sel_hf' size='1'>";
			while($r_rhu = mysql_fetch_array($q_rhu)){
				if($r_rhu["doh_class_id"]==$facility_code):								
					echo "<option value='$r_rhu[doh_class_id]' SELECTED>$r_rhu[facility_name] ($r_rhu[doh_class_id])</option>";
				else:
					echo "<option value='$r_rhu[doh_class_id]'>$r_rhu[facility_name] ($r_rhu[doh_class_id])</option>";				
				endif;
			}
		echo "</select>";
		echo "</td></tr>";

		
		echo "<tr class='textbox-label'><td>User Type</td><td>";
		echo "<select name='sel_user_type' size='1'>";
		echo "<option value='admin'>Administrator</option>";
		echo "<option value='manager'>Facility Data Manager</option>";
		echo "<option value='municipality_user'>Municipality/City-level User</option>";		
		echo "<option value='viewer'>Viewer</option>";		
		echo "</select>";
		echo "</td></tr>";
				
		echo "<tr class='textbox-label'><td>Email Address</td><td>";
		echo "<input type='text' name='txt_email' size='40' value='$user_email' />";
		echo "</td></tr>";
				
		echo "<tr class='textbox-label'><td colspan='2'>";
		if($_GET["action"]=='edit' && isset($user_id)):
			echo "<input type='submit' name='submit_user' value='Edit User Details' />&nbsp;&nbsp;&nbsp;";
		else:
			echo "<input type='submit' name='submit_user' value='Save User Details' />&nbsp;&nbsp;&nbsp;";
		endif;
		
		echo "<input type='reset' name='submit_user' value='Reset Details' />";
		echo "</td></tr>";
		echo "</table>";
		
		echo "</form>";
	}
	
	function show_user_list(){

	
		$q_user = mysql_query("SELECT user_id, user_fname, user_lname, facility_name, user_type FROM users ORDER BY user_lname ASC, user_fname ASC, facility_name ASC, user_type ASC") or die("Cannot query 391: ".mysql_error());

		if(isset($_GET["userid"]) && $_GET["action"]=='delete' && $_GET["resp"]=='Y'):									
			$q_del_user = mysql_query("DELETE FROM users WHERE user_id='$_GET[userid]'") or die("Cannot query 434: ".mysql_error());				

			if($q_del_user):
				header("location: $_SERVER[PHP_SELF]?id=user");
			endif;
		endif;

		if(mysql_num_rows($q_user)!=0): 
			echo "<table>";
			echo "<tr class='textbox-design'><td colspan='5'>LIST OF REGISTERED USERS</td></tr>";
			echo "<tr class='col-header'><td>&nbsp;&nbsp;Last Name&nbsp;&nbsp;</td><td>&nbsp;&nbsp;First Name&nbsp;&nbsp;</td><td>&nbsp;&nbsp;Health Facility&nbsp;&nbsp;</td><td>&nbsp;&nbsp;User Type&nbsp;&nbsp;&nbsp;</td><td>&nbsp;&nbsp;Action&nbsp;&nbsp;</a></tr>";
			while(list($user_id,$user_fname,$user_lname,$facility_name,$user_type)=mysql_fetch_array($q_user)){
				echo "<tr class='col-contents'>";
				echo "<td>".$user_lname."</td>";
				echo "<td>".$user_fname."</td>";			
				echo "<td>".$facility_name."</td>";
				echo "<td>".$user_type."</td>";
				echo "<td>";
				echo "<a href='$_SERVER[PHP_SELF]?id=user&userid=$user_id&action=edit'>Edit</a>&nbsp;&nbsp;&nbsp;<a href='$_SERVER[PHP_SELF]?id=user&userid=$user_id&action=delete'>Delete</a>";
				
				if(isset($_GET["userid"]) && $_GET["action"]=='delete' && $_GET["userid"]==$user_id && $_GET["resp"]!='Y'):				
					echo "<font class='warning_msg'><br>Are you sure wanted to delete this user?&nbsp;&nbsp;<a href='$_SERVER[PHP_SELF]?id=user&userid=$user_id&action=delete&resp=Y'>Yes</a>&nbsp;&nbsp;&nbsp;<a href='$_SERVER[PHP_SELF]?id=user'>No</a></font>";					
				endif;	

				echo "</td>";
				echo "</tr>";
			}
			
			echo "</table>";
			
		else:
		
		endif;
	
	}
	
	function display_period(){
		echo "<select name='sel_from_month'>";
		for($i=1;$i<=12;$i++){
			$buwan_from = (!empty($_POST["sel_from_month"]))?$_POST["sel_from_month"]:date('m');
			$buwan_to = (!empty($_POST["sel_end_month"]))?$_POST["sel_end_month"]:date('m');
			$taon_from = (!empty($_POST["sel_from_year"]))?$_POST["sel_from_year"]:date('Y');
			$taon_to = (!empty($_POST["sel_end_year"]))?$_POST["sel_end_year"]:date('Y');

			if($buwan_from==$i):			
				echo "<option value='$i' SELECTED>".$this->arr_buwan[$i]."</option>";
			else:
				echo "<option value='$i'>".$this->arr_buwan[$i]."</option>";			
			endif;
		}
		echo "</select>";

		echo "<select name='sel_from_year'>";
		for($i=(date('Y')-20);$i<=(date('Y')+20);$i++){
			if($taon_from==$i):			
				echo "<option value='$i' SELECTED>$i</option>";
			else:
				echo "<option value='$i'>$i</option>";
			endif;
		}
		echo "</select>";

		echo "<b>&nbsp;&nbsp;TO&nbsp;&nbsp;</b>.";
		
		echo "<select name='sel_end_month'>";
		for($i=1;$i<=12;$i++){
			if($buwan_to==$i):
				echo "<option value='$i' SELECTED>".$this->arr_buwan[$i]."</option>";
			else:
				echo "<option value='$i'>".$this->arr_buwan[$i]."</option>";
			endif;
		}
		echo "</select>";

		echo "<select name='sel_end_year'>";
		for($i=(date('Y')-20);$i<=(date('Y')+20);$i++){
			if($taon_to==$i):			
				echo "<option value='$i' SELECTED>$i</option>";
			else:
				echo "<option value='$i'>$i</option>";			
			endif;
		}
		echo "</select>";
	}
	
	
	function disp_tabular_form(){

		$arr_hf = array();		
		if(empty($_POST["submit_query"])):
//			$prevmonth = date('Y-m-01', strtotime("last month"));
//			echo $prevmonth;
			
		else: 

			$this->save_prov_mun_facility();
			$tbl_name = $this->arr_program_db[$_POST["sel_program"]];
			$_SESSION["str_label"] = $this->get_location();
			if(isset($_POST["chk_hf"])):
			
			foreach($_POST["chk_hf"] as $key=>$value){
				$q_hf = mysql_query("SELECT doh_class_id FROM m_lib_health_facility WHERE facility_id='$value'") or die("Cannot query 537: ".mysql_error());
				if(mysql_num_rows($q_hf)!=0):
					list($doh_class_id) = mysql_fetch_array($q_hf);
					array_push($arr_hf,"'".$doh_class_id."'");
				endif;
			}
			
			$str_hf = implode(',',$arr_hf);

			else:
				$str_hf = '';			
			endif;
			
			$arr_period = $this->plot_period();
	
			switch($_POST["sel_program"]){
				
				case 'mc': 
					$this->show_table($arr_period, $tbl_name, $str_hf,'mc');
					break;

				case 'childcare':
					$this->show_table($arr_period, $tbl_name, $str_hf,'childcare');
					break;					

				case 'fp':
					$this->show_table($arr_period, $tbl_name, $str_hf,'fp');
					break;					
				case 'morbidity':
					$this->show_table($arr_period, $tbl_name, $str_hf,'morbidity');
					break;
				default:
				
					break;
			}
					
		endif;
	}
	
	
	
	function show_table($arr_period, $tbl_name, $str_hf, $prog_id){ 
			echo "<table>"; 
			$this->disp_period_header($arr_period);
			$this->disp_contents($arr_period,$str_hf,$tbl_name,$prog_id);
			echo "</table>";
	}
	

	
	
	
	function plot_period(){
		$arr_period = array();

		for($i=$_POST["sel_from_year"];$i<=$_POST["sel_end_year"];$i++){

			if($i==$_POST["sel_from_year"]):
				$start = $_POST["sel_from_month"];
			else:
				$start = 1;	
			endif;
			
			if($i==$_POST["sel_end_year"]):
				$end = $_POST["sel_end_month"];
			else:
				$end = 12;							
			endif;
			
			for($j=$start;$j<=$end;$j++){
				array_push($arr_period,array($j,$i));		
			}			
		}

		return $arr_period;
	}
	
	function disp_period_header($arr_period){ 

		echo "<tr class='col-header'>";
		echo "<td align='center' class='col-header'>Indicators</td>";
		
		foreach($arr_period as $key=>$value){ 
			echo "<td>&nbsp;".$this->arr_buwan[$value[0]].' '.$value[1]."&nbsp;</td>";
		}
		
		echo "<td>&nbsp;Grand Total&nbsp;</td>";
		echo "</tr>";
	}
	
	function disp_contents($arr_period,$str_hf,$tbl_name,$program_id){ 

		$q_indicator = mysql_query("SELECT indicator_id,indicator_code,indicator_desc,prog_id FROM lib_indicator WHERE prog_id='$program_id' ORDER BY indicator_id ASC") or die("Cannot query 613: ".mysql_error());

		if(mysql_num_rows($q_indicator)!=0):
		
			while($r_indicator = mysql_fetch_array($q_indicator)){
				$arr_count = array();
				$gt = 0;
				echo "<tr>";
				echo "<td class='col-header'>".$r_indicator["indicator_desc"]."</td>";
				foreach($arr_period as $key=>$value){ 
						$buwan  = sprintf("%02s",$value[0]);				
						$suma = 0;
						$col_name = $r_indicator["indicator_code"];

						if(!empty($str_hf)): 
							$q_sum_indicator = mysql_query("SELECT SUM($col_name) FROM $tbl_name WHERE MONTH='$buwan' AND YEAR='$value[1]' AND HFHUDCODE IN ($str_hf)") or die("Cannot query 736: ".mysql_error());
						else: //province wide
							//$q_sum_indicator = mysql_query("SELECT SUM(a.$col_name) FROM $tbl_name a, m_lib_health_facility b WHERE a.MONTH='$buwan' AND a.YEAR='$value[1]' AND a.HFHUDCODE=b.doh_class_id AND b.psgc_provcode='369'") or die("Cannot query 638: ".mysql_error());						
							$q_sum_indicator = mysql_query("SELECT SUM($col_name) FROM $tbl_name WHERE MONTH='$buwan' AND YEAR='$value[1]' AND provcode='0369'") or die("Cannot query 638: ".mysql_error());						
						
						endif;
						
//						if(mysql_num_rows($q_sum_indicator)!=0):
							list($suma) = mysql_fetch_array($q_sum_indicator); 
							
							if($suma==''):
								$suma = 0;
							endif; 
							
							$gt += $suma;
							array_push($arr_count,$suma);							
	//					else:
	//						$suma = 0;
	//						array_push($arr_count,0);							
	//					endif;


						echo "<td class='col-contents'>";						
						echo $suma;
						echo "</td>";
				}
				//echo "<td class='col-contents'>".$gt."</td>";
						
				$str_count = implode("*",$arr_count);

				echo "<td class='col-contents'><a href='scripts/disp_graph.php?type=gt&graph_arg=$str_count&from_month=$_POST[sel_from_month]&from_year=$_POST[sel_from_year]&to_month=$_POST[sel_end_month]&to_year=$_POST[sel_end_year]&desc=$r_indicator[indicator_desc]' target='new'>".$gt."</a></td>";				
				echo "</tr>";
			}
			
		else:
			$arr_field = array();

			//set an array for the table fields			
			$q_fields = mysql_query("SHOW COLUMNS FROM $tbl_name") or die("Cannot query 670: ".mysql_error());
			if(mysql_num_rows($q_fields)!=0):
				while($r_field=mysql_fetch_array($q_fields)){
					array_push($arr_field,$r_field[0]); 
				}	
			endif;

			//set the date boundaries for query
			$first_date = $arr_period[0][1].'-'.sprintf("%02s",$arr_period[0][0]).'-01';
			$last_date = $arr_period[(count($arr_period)-1)][1].'-'.sprintf("%02s",$arr_period[(count($arr_period)-1)][0]).'-31';

			//start querying the m2_bhs table, rank and order using the arrays
			if(!empty($str_hf)):
				$q_morb = mysql_query("SELECT ICD10_CODE,  CONCAT(YEAR,'-',MONTH,'-15') as 'date_reported' FROM $tbl_name WHERE CONCAT(YEAR,'-',MONTH,'-15') BETWEEN '$first_date' AND '$last_date' AND HFHUDCODE IN ($str_hf)") or die("Cannot query 674: ".mysql_error());		
			else: //province
				$q_morb = mysql_query("SELECT ICD10_CODE,  CONCAT(YEAR,'-',MONTH,'-15') as 'date_reported' FROM $tbl_name WHERE CONCAT(YEAR,'-',MONTH,'-15') BETWEEN '$first_date' AND '$last_date' AND PROVCODE='0369'") or die("Cannot query 674: ".mysql_error());
			endif;
			
					$arr_icd = array();
					$arr_icd_main = array();
					$arr_icd_main_display = array();
									
				if(mysql_num_rows($q_morb)!=0): $tot = 0;
					while(list($icd,$date_rep) = mysql_fetch_array($q_morb)){
						$arr_icd_inside = array();

						array_push($arr_icd_inside,$icd);
						$gt = 0;
						for($i=8;$i<count($arr_field)-1;$i++){  
							$icd_total = 0;
							if(!empty($str_hf)): 
							$q_total = mysql_query("SELECT SUM($arr_field[$i]) FROM prog_m2_bhs WHERE ICD10_CODE='$icd' AND CONCAT(YEAR,'-',MONTH,'-15') BETWEEN '$first_date' AND '$last_date' AND HFHUDCODE IN ($str_hf)") or die("Cannot query 693: ".mysql_error());							
							else: 
							$q_total = mysql_query("SELECT SUM($arr_field[$i]) FROM prog_m2_bhs WHERE ICD10_CODE='$icd' AND CONCAT(YEAR,'-',MONTH,'-15') BETWEEN '$first_date' AND '$last_date' AND PROVCODE='0369'") or die("Cannot query 693: ".mysql_error());
							endif;

							if($arr_field[$i]!='65ABOVE_M' && $arr_field[$i]!='65ABOVE_F'):
								list($icd_total) = mysql_fetch_array($q_total);	 
								array_push($arr_icd_inside,$icd_total);
								$gt += $icd_total;							
							endif;
						} $tot++;
						array_push($arr_icd_inside,$gt);
						array_push($arr_icd,$arr_icd_inside);
						$arr_icd_main[$icd] = $gt;  
					} 
				endif;

			//print_r($arr_icd_main);  //todo: sort based on the 2nd argument (grand total of ICD10 cases)
			//traverse through the m2_bhs table again then check for the 

			foreach($arr_icd_main as $key=>$value){ //traverse through the ICD10 codes
				$arr_icd_per_month = array(); //would contain the array per month based on the 
				foreach($arr_period as $key2=>$value2){ //traverse through the months / year
					$buwan  = sprintf("%02s",$value2[0]);				
					$suma = 0;
						for($i=8;$i<count($arr_field)-1;$i++){ //traverse age group 
							if(!empty($str_hf)):
								$q_total = mysql_query("SELECT $arr_field[$i] FROM prog_m2_bhs WHERE ICD10_CODE='$key' AND MONTH='$buwan' AND YEAR=$value2[1] AND HFHUDCODE IN ($str_hf)") or die("Cannot query 724: ".mysql_error());							
							else:
								$q_total = mysql_query("SELECT $arr_field[$i] FROM prog_m2_bhs WHERE ICD10_CODE='$key' AND MONTH='$buwan' AND YEAR=$value2[1] AND PROVCODE='0369'") or die("Cannot query 726: ".mysql_error());
							endif;

							if($arr_field[$i]!='65ABOVE_M' && $arr_field[$i]!='65ABOVE_F'):
								list($age_count) = mysql_fetch_array($q_total);
								$suma += $age_count;
							endif;
						}
					array_push($arr_icd_per_month,$suma);
				}

				array_push($arr_icd_main_display,array($key,$arr_icd_per_month,$value)); 
			}
			
			//print_r($arr_field);
			

			for($i=0;$i<count($arr_icd_main_display);$i++){

				for($j=0;$j<count($arr_icd_main_display[$i]);$j++){

				$str_icd = implode('*',$arr_icd_main_display[$i][1]);
				
					if($j==0): 
						$icd10_code = $arr_icd_main_display[$i][$j];
						$q_icd_name = mysql_query("SELECT diagnosis_code,description FROM m_lib_icd10_en WHERE diagnosis_code LIKE '%$icd10_code%' AND sub_level='3'") or die("Cannot query 754: ".mysql_error());
						list($diag_code,$description) = mysql_fetch_array($q_icd_name);
						
						echo "<tr>"; 
						echo "<td class='col-header'>".$description." (".$icd10_code.") </td>";
					elseif($j==1):
						foreach($arr_icd_main_display[$i][$j] as $key=>$value){
							echo "<td class='col-contents'>".$value."</td>";
						}
					elseif($j==2):				
						echo "<td class='col-contents'><a href='scripts/disp_graph.php?type=gt&graph_arg=$str_icd&from_month=$_POST[sel_from_month]&from_year=$_POST[sel_from_year]&to_month=$_POST[sel_end_month]&to_year=$_POST[sel_end_year]&desc=$description' target='new'>".$arr_icd_main_display[$i][$j]."</a></td>";					
						echo "</tr>";
					else:
					
					endif;
				} 
			}
		endif;		
	}
	
	function show_map(){
		echo "<table>";
		echo "<tr class='textbox-design'><td>MAP VIEW</td></tr>";
		echo "<tr >";
		echo "<td>";
		echo "<iframe width='1000' height='500' frameborder='0' scrolling='no' marginheight='0' marginwidth='0' src='https://maps.google.com/maps/ms?msa=0&amp;msid=206041811975214000456.000462968e78d9aae3e0a&amp;ie=UTF8&amp;t=h&amp;ll=7.27241,78.919693&amp;spn=17.104741,84.282293&amp;output=embed'></iframe><br /><small>";
		echo "View <a href='https://maps.google.com/maps/ms?msa=0&amp;msid=206041811975214000456.000462968e78d9aae3e0a&amp;ie=UTF8&amp;t=h&amp;ll=7.27241,78.919693&amp;spn=17.104741,84.282293&amp;source=embed' style='color:#0000FF;text-align:left'>Qualcomm Wireless Reach Philippines Partnership</a> in a larger map</small>";
		
		echo "</tr>";
		echo "</table>";
	}
	
	
	function footer(){
		//echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
		echo "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";		
		echo "<table width='100%'>";
		echo "<tr align='center' class='table-row-notes'><td>Copyright 2012 - <a href='mailto:perez.alison@gmail.com'>WAH Stats Aggregator</a></td></tr>";
		echo "</table>";
	}
	
	function show_swf(){
		echo "<table>";
		echo "<tr><td align='center' class='textbox-label'>What STATS AGGREGATOR do?</td></tr>";
		echo "<tr align='center'><td>";
		echo "<embed src='./swf/interconnect.swf' width='120' height='120' />";
		echo "<embed src='./swf/integrate.swf' width='120' height='120' />";		
		echo "<embed src='./swf/present.swf' width='120' height='120' />";		
		echo "<embed src='./swf/map.swf' width='120' height='120' />";		
		echo "<embed src='./swf/archive.swf' width='120' height='120' />";		
		echo "</td></tr>";	
		echo "</table>";
	}
	
	
	function save_prov_mun_facility(){
		$_SESSION["prov"] = $_POST["sel_prov"];
		$_SESSION["municipality"] = $_POST["sel_mun"];
		$_SESSION["facility"] = $_POST["chk_hf"];

	}
	
	function get_location(){
		$arr_chk = $_SESSION["facility"];
		$arr_facility = array();
		$prov = $municipality = $str_facility = $str_label = '';
			
		$q_prov = mysql_query("SELECT place_name FROM m_lib_psgc_code WHERE SUBSTRING(municipality_code,-2)='00' AND province_code='$_SESSION[prov]'") or die("Cannot query 70: ".mysql_error());
		list($prov) = mysql_fetch_array($q_prov);
		
		$q_municipality = mysql_query("SELECT place_name FROM m_lib_psgc_code WHERE SUBSTRING(barangay_id,-3)='000' AND municipality_code='$_SESSION[municipality]'") or die("Cannot query 73: ".mysql_error());
		list($municipality) = mysql_fetch_array($q_municipality);
		
		
		if(count($arr_chk)!=0):
			foreach($arr_chk as $key=>$value){
				$q_facility = mysql_query("SELECT facility_name FROM m_lib_health_facility WHERE facility_id='$value'") or die("Cannot query 81: ".mysql_error());
				list($facility) = mysql_fetch_array($q_facility);
				
				array_push($arr_facility,$facility);
			}			

			$str_facility = implode($arr_facility,',');
		endif;
	

		
		if($str_facility!=''):
			$str_label = $str_facility.' of ';
		endif;
		
		if($municipality!=''):
			$str_label = $str_label.$municipality.', ';
		else:
			$str_label = $str_label.'All Municipalities of ';
		endif;
		
		if($prov!=''):
			$str_label = $str_label.$prov;
		endif;

		return $str_label;
	}

	function show_stats_box(){
		echo "<table width='650'>";
		echo "<tr><td align='center' class='textbox-label'>STATISTICS FROM SELECTED INDICATORS</td></tr>";
	
		echo "</table>";
	}


	function show_shoutbox(){
		$q_shoutbox = mysql_query("SELECT title, content, date_posted, author FROM announcements ORDER BY date_posted DESC" ) or die("Cannot query 967: ".mysql_error());

		echo "<table width='600' border='1'>";
		echo "<tr><td align='center' class='textbox-label'>ANNOUNCEMENT SHOUTBOX</td></tr>";
		
		if(mysql_num_rows($q_shoutbox)==0):
			echo "<tr><td>";
			echo "No posted announcement.";
			echo "</td></tr>";
		else:
			while(list($title,$content,$date_posted,$author)=mysql_fetch_array($q_shoutbox)){
				echo "<tr><td>";
				echo $title.' posted on '.$date_posted.' by '.$author.'<br>';
				echo "<p align='justify'>".$content."</p>";
				echo "</td></tr>";
			}
		endif;
		

		echo "</table>";
	}


		
	}	
?>