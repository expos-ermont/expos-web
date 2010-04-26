<?php
/*
	
	Copyright (c) Reece Pegues
	sitetheory.com

	Reece PHP Calendar is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or 
	any later version if you wish.

    You should have received a copy of the GNU General Public License
    along with this file; if not, write to the Free Software
    Foundation Inc, 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
	
*/


/* ##################################################################
  cal_error()
   Used to print nice error messages
###################################################################*/
function cal_error($s){
	return "<br><span class='failure'>$s</span><br>";
}


function cal_get_ws_color($ws_color, &$ws_style, &$ws_class, &$txt_color, &$border_color){
	$txt_color = '#fff';
	if ($ws_color>0 && $ws_color<=12){
		$ws_style = "";
		$ws_class = "og-wsname-color-$ws_color";
	} else if ($ws_color>12) {
		$ws_style = "";
		$txt_color = '#003562';
		$ws_class = "og-wsname-color-$ws_color";
	}else {
		$ws_style = "color: #000000;background-color: #C5C7C1;border-color: #C5C7C1;";
		$txt_color = '#000000';
		$ws_class = "";	
	}
	
	if ($ws_color == 0) $border_color = '#AAAAAA';
	else {
		switch ($ws_color % 12) {
			case 1: $border_color = '#5A6986'; break;
			case 2: $border_color = '#206CE1'; break;
			case 3: $border_color = '#0000CC'; break;
			case 4: $border_color = '#5229A3'; break;
			case 5: $border_color = '#854F61'; break;
			case 6: $border_color = '#CC0000'; break;
			case 7: $border_color = '#EC7000'; break;
			case 8: $border_color = '#B36D00'; break;
			case 9: $border_color = '#AB8B00'; break;
			case 10: $border_color = '#636330'; break;
			case 11: $border_color = '#64992C'; break;
			case 0: $border_color = '#006633'; break;
			default: $border_color = '#333333'; break;
		}
		if ($ws_color <= 12) $border_color = darkerHtmlColor($border_color, 25);
	}
	
}


/* ##################################################################
  cal_getlink()
   returns the link with added GET querys from $link_tail.
###################################################################*/
function cal_getlink($link){
	global $cal_link_tail;
	// if we use a different filename, replace index.php with the one we use
	/*if(CAL_URL_FILENAME!="index.php"){
		$link = str_replace("index.php",CAL_URL_FILENAME,$link);
	}*/
	$link .= '&c=event';	
	$link = str_replace('?action=','?a=',$link);
	$link = str_replace('&action=','&a=',$link);	
	if($cal_link_tail=="") return $link;
	else if(eregi("\?",$link)) return $link ."&amp;". $cal_link_tail;
	else return $link ."?". $cal_link_tail;	
}


/* ##################################################################
  cal_add_to_links()
   adds GET querys to every link used on the page.
   Made to help simplify adding querys for things like
   skins, usernames, passwords, and of course for modularity.
   useage:  add_to_links("skin=".$skin_name);
   
   Basically, you can use this whole program as a module in something like
   php-nuke or postnuke by adding variables they need to every link's query.
###################################################################*/
function cal_add_to_links($add){
	global $cal_link_tail;
	if((!isset($link_tail )) || $link_tail == "") $link_tail = $add;
	else $link_tail .= "&amp;".$add;
}





/* ##################################################################
  cal_month_name()
   Returns the full month name when given a number.
   (number is modded 12)
###################################################################*/
function cal_month_name($month){
	$month = ($month - 1) % 12 + 1;
	switch($month) {
		case 1:  return lang('CAL_JANUARY');
		case 2:  return lang('CAL_FEBRUARY');
		case 3:  return lang('CAL_MARCH');
		case 4:  return lang('CAL_APRIL');
		case 5:  return lang('CAL_MAY');
		case 6:  return lang('CAL_JUNE');
		case 7:  return lang('CAL_JULY');
		case 8:  return lang('CAL_AUGUST');
		case 9:  return lang('CAL_SEPTEMBER');
		case 10: return lang('CAL_OCTOBER');
		case 11: return lang('CAL_NOVEMBER');
		case 12: return lang('CAL_DECEMBER');
	}
}



/* ##################################################################
  cal_month_short()
   same as month_name() above except it returns an abreviation.
###################################################################*/
function cal_month_short($month){
	$month = ($month - 1) % 12 + 1;
	switch($month) {
		case 1:  return substr(lang('CAL_JANUARY'),0,3);
		case 2:  return substr(lang('CAL_FEBRUARY'),0,3);
		case 3:  return substr(lang('CAL_MARCH'),0,3);
		case 4:  return substr(lang('CAL_APRIL'),0,3);
		case 5:  return substr(lang('CAL_MAY'),0,3);
		case 6:  return substr(lang('CAL_JUNE'),0,3);
		case 7:  return substr(lang('CAL_JULY'),0,3);
		case 8:  return substr(lang('CAL_AUGUST'),0,3);
		case 9:  return substr(lang('CAL_SEPTEMBER'),0,3);
		case 10: return substr(lang('CAL_OCTOBER'),0,3);
		case 11: return substr(lang('CAL_NOVEMBER'),0,3);
		case 12: return substr(lang('CAL_DECEMBER'),0,3);
	}
}



/* ##################################################################
  cal_top()
   returns the header information,
   css stylesheet file links (acording to which skin is loaded) etc
###################################################################*/
function cal_top(){
	
	// print the beginning of the calendar module
	$output = '
		<script type="text/javascript">
		
			function cal_toggle(id){
				obj = document.getElementById(id);
				if(obj.style.display=="none"){
					obj.style.display = "block";
				}else{
					obj.style.display = "none";
				}
			}
			function cal_hide(id){
				document.getElementById(id).style.display = "none";
			}
			function cal_show(id){
				document.getElementById(id).style.display = "block";
			}
		</script>
		<!-- overlib -->
		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
		<!-- start main calendar tables -->';
	return $output;	
}





/* ##################################################################
  cal_easter_orthodox()
	Takes any Gregorian date and returns the Gregorian
	date of Orthodox Easter for that year.
###################################################################*/
function cal_easter_orthodox($year){
	$year = date("Y", mktime(0,0,1,1,1,$year));
	$r1 = $year % 19;
	$r2 = $year % 4;
	$r3 = $year % 7;
	$ra = 19 * $r1 + 16;
	$r4 = $ra % 30;
	$rb = 2 * $r2 + 4 * $r3 + 6 * $r4;
	$r5 = $rb % 7;
	$rc = $r4 + $r5;
	//Orthodox Easter for this year will fall $rc days after April 3
	return date("Y-m-d",strtotime("3 April $year + $rc days"));
}



?>