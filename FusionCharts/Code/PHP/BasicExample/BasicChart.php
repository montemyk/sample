<?php
//We've included ../Includes/FusionCharts.php, which contains functions
//to help us easily embed the charts.
include("../Includes/FusionCharts.php");
?>
<HTML>
<HEAD>
	<TITLE>
	FusionCharts - Simple Column 3D Chart
	</TITLE>
	<style type="text/css">
	<!--
	body {
		font-family: Arial, Helvetica, sans-serif;
		font-size: 12px;
	}
	-->
	</style>
</HEAD>
<BODY>

<CENTER>
<h2>FusionCharts Examples</h2>
<h4>Basic example using pre-built Data.xml</h4>
<?php
	
	//This page demonstrates the ease of generating charts using FusionCharts.
	//For this chart, we've used a pre-defined Data.xml (contained in /Data/ folder)
	//Ideally, you would NOT use a physical data file. Instead you'll have 
	//your own PHP scripts virtually relay the XML data document. Such examples are also present.
	//For a head-start, we've kept this example very simple.
	
	
	//Create the chart - Column 3D Chart with data from Data/Data.xml
	echo renderChartHTML("../../FusionCharts/Column3D.swf", "Data/Data.xml", "", "myFirst", 600, 300, false);
	
?>
<BR><BR>
<a href='../NoChart.html' target="_blank">Unable to see the chart above?</a>
</CENTER>
</BODY>
</HTML>