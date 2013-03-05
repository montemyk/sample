<?php
	session_start();
	include("../FusionCharts/Code/PHP/Includes/FusionCharts.php");
	
	echo "<html>";
	echo "<head>";
	echo "<SCRIPT LANGUAGE='Javascript' SRC='../FusionCharts/JSClass/FusionCharts.js'></SCRIPT>";
	echo "</head>";


	echo "<body>";

	switch($_GET["type"]){
		
		case 'gt':
			$arr_value = explode("*",$_GET["graph_arg"]); 
			if(count($arr_value)>=12):
				$graph_width = 50 * count($arr_value);
			else:
				$graph_width = 600;
			endif;
	
			$arr_period = plot_period();

			$str_xml = "<chart caption='$_GET[desc]' subcaption='$_SESSION[str_label]' yAxisName='Case Count' yAxisMinValue='0' xAxisName='Month' showValues='0'>";

			for($i=0;$i<count($arr_period);$i++){ 
				//echo $arr_period[$i][0].' '.$arr_period[$i][1];
				$str_label =$arr_period[$i][0].'-'.$arr_period[$i][1];
				$str_xml .= "<set label='$str_label' value='$arr_value[$i]' />";
			}

			$str_xml .= "</chart>";
			
		   echo renderChart("../FusionCharts/Charts/Line.swf", "", $str_xml, "productSales", $graph_width, 300, false, false);
		
			break;
			
		default:
		
			break;	
	}		
	
	function plot_period(){
		$arr_period = array();

		for($i=$_GET["from_year"];$i<=$_GET["to_year"];$i++){

			if($i==$_GET["from_year"]):
				$start = $_GET["from_month"];
			else:
				$start = 1;	
			endif;
			
			if($i==$_GET["to_year"]):
				$end = $_GET["to_month"];
			else:
				$end = 12;							
			endif;
			
			for($j=$start;$j<=$end;$j++){
				array_push($arr_period,array($j,$i));		
			}			
		}

		return $arr_period;
	}
	

	
	
	echo "</body>";
	echo "</html>";
?>