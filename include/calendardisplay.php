<?php

###############################################################################
##     Formulize - ad hoc form creation and reporting module for XOOPS       ##
##                    Copyright (c) 2004 Freeform Solutions                  ##
###############################################################################
##                    XOOPS - PHP Content Management System                  ##
##                       Copyright (c) 2000 XOOPS.org                        ##
##                          <http://www.xoops.org/>                          ##
###############################################################################
##  This program is free software; you can redistribute it and/or modify     ##
##  it under the terms of the GNU General Public License as published by     ##
##  the Free Software Foundation; either version 2 of the License, or        ##
##  (at your option) any later version.                                      ##
##                                                                           ##
##  You may not change or alter any portion of this comment or credits       ##
##  of supporting developers from this source code or any supporting         ##
##  source code which is considered copyrighted (c) material of the          ##
##  original comment or credit authors.                                      ##
##                                                                           ##
##  This program is distributed in the hope that it will be useful,          ##
##  but WITHOUT ANY WARRANTY; without even the implied warranty of           ##
##  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            ##
##  GNU General Public License for more details.                             ##
##                                                                           ##
##  You should have received a copy of the GNU General Public License        ##
##  along with this program; if not, write to the Free Software              ##
##  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA ##
###############################################################################
##  Author of this file: Freeform Solutions 					     ##
##  Project: Formulize                                                       ##
###############################################################################

// this file contains the logic for the displayCalendar function and related functions.

global $xoopsConfig;
// load the formulize language constants if they haven't been loaded already
	if ( file_exists(XOOPS_ROOT_PATH."/modules/formulize/language/".$xoopsConfig['language']."/main.php") ) {
		include_once XOOPS_ROOT_PATH."/modules/formulize/language/".$xoopsConfig['language']."/main.php";
	} else {
		include_once XOOPS_ROOT_PATH."/modules/formulize/language/english/main.php";
	}

// main function
// formframes - array of forms and/or frameworks in use
// mainforms - array of mainforms for each framework in use (must have empty places corresponding to any plain form IDs in formframes
// viewHandles - array of the element ids or element handles (if in a framework) corresponding to the element where the data to be printed on the calendar comes from
// dateHandles - array similar to viewHandles, but for the element that contains the data information to base the placement of the data in the calendar on
// filters -- array of filters that can be passed directly to the data extraction, to support viewing only a subset of the data at any one time.
// viewPrefixs - array of text to prepend to the beginning of the viewHandles text when displaying an entry on the calendar (meant for situations like "Booking Deadline: September Conference")
// hidden -- array of values to be put into hidden elements in the form (so they are preserved when the user changes months)
// $scopes - array of the scopes to use when querying for the data to display in the calendar.  Valid values are: mine, group, all, or list of group ids formatted with starting and ending commas, plus commas in between, ie: ,1,3,
// $type - the kind of calendar to display, ie: month, week, year...??  
// $start - the starting view for the calendar.  if a month, this is the year and month, ie: 2005-07.  (not sure what else it could/should be, only supporting month views to start with.)
//
// The idea behind all these arrays is that you can hook up your calendar to multiple sets of data.
// So, key 0 in each array represents all the settings for that set of data.  
// key 1 and key 2 and so on in each array, represent other sets of data.
// ie: formframes[2] and viewHandles[2]...that's the form or framework used for dataset 2, and the handle for the element in that dataset which should be displayed on the calendar.
// Note about scopes: scopes must be converted to the format described for the $scope param for the getData function

function displayCalendar($formframes, $mainforms="", $viewHandles, $dateHandles, $filters, $viewPrefixes, $scopes, $hidden, $type="month", $start="") {



	global $xoopsDB, $xoopsUser;
	include_once XOOPS_ROOT_PATH.'/modules/formulize/include/functions.php';

    global $xoopsTpl;

	// Set some required variables
	$mid = getFormulizeModId();
	for($i=0;$i<count($formframes);$i++) {
		unset($fid);
		unset($frid);
		if($mainforms[$i]) {
			list($fid, $frid) = getFormFramework($formframes[$i], $mainforms[$i]);
		} else {
			list($fid, $frid) = getFormFramework($formframes[$i]);
		}
		$fids[] = $fid;
		$frids[] = $frid;
	}


	$gperm_handler = &xoops_gethandler('groupperm');
	$member_handler =& xoops_gethandler('member');
	$groups = $xoopsUser ? $xoopsUser->getGroups() : XOOPS_GROUP_ANONYMOUS;
	$uid = $xoopsUser->getVar('uid');


	foreach($fids as $thisFid) { // check that the user is allowed to see all the fids
		if(!$scheck = security_check($thisFid, "", $uid, "", $groups, $mid, $gperm_handler, "")) {
			print "<p>" . _NO_PERM . "</p>";
			return;
		}
	}                  

	$currentURL = getCurrentURL();

	// get the current view, ie: the month
	if($_POST['calview']) { // if we're recieving a view from a form submission...
		$settings['calview'] = $_POST['calview'];
	} else {
		if(!$start) {
			// nothing passed from form, and no default value specified, so use current date
			$today = getDate();
			if($today['mon']<10) {$today['mon'] = "0" . $today['mon']; }
			$settings['calview'] = $today['year'] . "-" . $today['mon'];
		} else {
			$settings['calview'] = $start;
		}
	}
	$settings['calfrid'] = $_POST['calfrid'];
	$settings['calfid'] = $_POST['calfid'];
	$settings['calhidden'] = $hidden;
	
	// check to see if a switch to a form has been requested
	$settings['ventry'] = $_POST['ventry'];
	if($settings['ventry']) {
		if($_POST['ventry'] == "addnew") {
			$this_ent = "";
			$dateOverride = $_POST['adddate'];
		} elseif($_POST['ventry'] == "proxy") { // support for proxies not currently written
			$this_ent = "proxy";
		} else {
			$this_ent = $_POST['ventry'];
		}
		if($frid) {
			displayForm($_POST['calfrid'], $this_ent, $_POST['calfid'], $currentURL, "", $settings, "", $dateOverride); // "" is the done text
			return;
		} else {
			displayForm($_POST['calfid'], $this_ent, "", $currentURL, "", $settings, "", $dateOverride); // "" is the done text
			return;
		}
	}

	// get the data for all the fids
	// 1. convert the scopes for each one
	// 2. do the extraction (filter by calview)

	include_once XOOPS_ROOT_PATH . "/modules/formulize/include/extract.php";
	for($i=0;$i<count($fids);$i++) {
		$scope="";
		if($scopes[$i]) {
			$scope = buildScope($scopes[$i], $member_handler, $uid, $groups);
		}
		if(!$frids[$i]) {
			$caption = q("SELECT ele_caption FROM " . DBPRE . "form WHERE ele_id = '" . $dateHandles[$i] . "'"); 
			$ffcaption = eregi_replace ("&#039;", "`", $caption[0]['ele_caption']);
			$ffcaption = eregi_replace ("&quot;", "`", $ffcaption);
			$ffcaption = str_replace ("'", "`", $ffcaption);
			$filterDH = $ffcaption;
		} else {
			$filterDH = $dateHandles[$i];
		}
		$filter = $filterDH . "/**/" . $settings['calview'];
		$filter .= "][" . $filters[$i];
		$data[$i] = getData($frids[$i], $fids[$i], $filter); //, "AND", $scope);
		$data[$i] = resultSort($data[$i], $dateHandles[$i]);
	}
	 
	// need the formatting magic to go here, to whip it all into a nice calendar
	// basic display of data is below
	// demonstrates linking to a form for updating/viewing that entry
	// demonstrates altering the calview setting to change months

	// need to do something a little more complex for adding a new entry, since we have to know for which fid/frid pair the add operation is being requested.
	// probably best to leave out adding for now and leave it as a future feature.  It can always be custom added within a pageworks page if necessary for a particular calendar


	$rights = $gperm_handler->checkRight("add_own_entry", $fid, $groups, $mid);


	// information to pass to the template
	global $calendarData;
    
	// initialize language constants
	global $arrayMonthNames;
	global $arrayWeekNames;
	global $dateMonthStartDay;

	$arrayMonthNames = array(_formulize_CAL_MONTH_01, _formulize_CAL_MONTH_02, _formulize_CAL_MONTH_03, _formulize_CAL_MONTH_04, _formulize_CAL_MONTH_05, _formulize_CAL_MONTH_06, _formulize_CAL_MONTH_07, _formulize_CAL_MONTH_08, _formulize_CAL_MONTH_09, _formulize_CAL_MONTH_10, _formulize_CAL_MONTH_11, _formulize_CAL_MONTH_12); 
	if($type == "mini_month")
    {    
		$arrayWeekNames = array(_formulize_CAL_WEEK_1_3ABRV, _formulize_CAL_WEEK_2_3ABRV, _formulize_CAL_WEEK_3_3ABRV, _formulize_CAL_WEEK_4_3ABRV, _formulize_CAL_WEEK_5_3ABRV, _formulize_CAL_WEEK_6_3ABRV, _formulize_CAL_WEEK_7_3ABRV);
	}
    else
    {    
	$arrayWeekNames = array(_formulize_CAL_WEEK_1, _formulize_CAL_WEEK_2, _formulize_CAL_WEEK_3, _formulize_CAL_WEEK_4, _formulize_CAL_WEEK_5, _formulize_CAL_WEEK_6, _formulize_CAL_WEEK_7);
	}

	// convert string date into parts 
	$arrayDate = getdate(strtotime($settings['calview'] . "-01"));
	$dateMonth = $arrayDate["mon"]; 
	$dateDay = $arrayDate["mday"];
	$dateYear = $arrayDate["year"];

	// get the number of days in the month.
	$dateMonthDays = days_in_month($dateMonth, $dateYear);

	// get the month's first week start day.
	$dateMonthStartDay = $arrayDate["wday"]; 

    // get the number of weeks.
    $dateMonthWeeks = week_in_month($dateMonthDays) + 1;


    // intialize MONTH template information
    // each cell is an array:
    // [0] - is control information, where each entry is an array:
    //     [0] - day number
    //     [1] - send date
    // [1] - is an array containing all items, where each item is also an array:
    //     [0] - $ids[0]
    //     [1] - $frids[$i]
    //     [2] - $fids[$i]
    //     [3] - $textToDisplay

	if($type == "month"
		|| $type == "mini_month"
		|| $type == "micro_month")
    {    
	    // initialize grid: convert the data set into a grid of 7 columns for  
	    //  days and a row for each week
	    $displayDay = "";
	    for($intWeeks = 0; $intWeeks < $dateMonthWeeks; $intWeeks++)
	    {
	        $calendarData[$intWeeks] = array();
	        for($intDays = 0; $intDays < 7; $intDays++)
	        {
	            // check to see if the processing day is the start day.
	            if($intWeeks == 0
	                && $displayDay == "")
	            {
	                if($intDays == $dateMonthStartDay)
	                {
	                    $displayDay = 1; 
	                }
	            }
	            else
	            {
	                if($displayDay != "")
	                {
	                    $displayDay++;
	                
	                    if($displayDay > $dateMonthDays)
	                    {
	                        $displayDay = "";
	                    }
	                }
	            }

	            $calendarData[$intWeeks][$intDays] = array();
	            $calendarData[$intWeeks][$intDays][0][0] = $displayDay;
	            $calendarData[$intWeeks][$intDays][0][1] = $dateYear . "-" . $dateMonth . "-" . (($displayDay < 10) ? "0" . $displayDay : $displayDay);
	            //$calendarData[$intWeeks][$intDays][1] = array();
	        }
	    }    

		// Initialize template variables
	    $xoopsTpl->assign('previousMonth', ((($dateMonth - 1) < 1) ? ($dateYear - 1) . "-12" : $dateYear . "-" . ((($dateMonth - 1) < 10) ? "0" . ($dateMonth - 1) : ($dateMonth - 1))));
	    $xoopsTpl->assign('nextMonth', ((($dateMonth + 1) > 12) ? ($dateYear + 1) . "-01" : $dateYear . "-" . ((($dateMonth + 1) < 10) ? "0" . ($dateMonth + 1) : ($dateMonth + 1))));

	    $monthSelector = array();
	    $numberOfMonths = count($arrayMonthNames);
	    for($intMonth = 0; $intMonth < $numberOfMonths; $intMonth++)
	    {
	        $monthName = $arrayMonthNames[$intMonth];
	        $monthSelector[((($intMonth + 1) < 10) ? "0" . ($intMonth + 1) : ($intMonth + 1))] = $monthName; 
	    }
	    $xoopsTpl->assign('monthSelector', $monthSelector);

	    $yearSelector = array();
	    $startYear = $dateYear - 4;
	    $endYear = $dateYear + 3;
	    for($intYear = $startYear; $intYear <= $endYear; $intYear++)
	    {
	        $yearSelector[] = $intYear;    
	    }
	    $xoopsTpl->assign('yearSelector', $yearSelector);
	}
    

	// process data set(s)
	for($i=0;$i<count($data);$i++) {
		foreach($data[$i] as $id=>$entry) {
            if(!$frids[$i]) {
				$formhandle = getFormHandleFromEntry($entry, $viewHandles[$i]);
			} else {
				$formhandle = $fids[$i];
			}
			$ids = internalRecordIds($entry, $formhandle);
			
            $currentDate = display($entry, $dateHandles[$i]);

	        if($viewPrefixes[$i]) {
	            $textToDisplay = $viewPrefixes[$i] . display($entry, $viewHandles[$i]);
	        } else {
	            $textToDisplay = display($entry, $viewHandles[$i]);
	        }


			// Layout	

	        if($type == "month"
	        	|| $type == "mini_month"
	        	|| $type == "micro_month")
	        {    
	            $arrayDate = getdate(strtotime($currentDate));
	            $dateDay = $arrayDate["mday"];
	            $dateMonthWeekDay = $arrayDate["wday"];

	            $dateMonthWeek = week_in_month($dateDay);

	            // debug
	            //print "$dateDay :: $dateMonthWeek :: $dateMonthWeekDay :: $item";
	            
	            $calendarDataItem = array();
                $calendarDataItem[0] = $ids[0];
	            $calendarDataItem[1] = $frids[$i];
	            $calendarDataItem[2] = $fids[$i];
	            $calendarDataItem[3] = $textToDisplay;

	            $calendarData[$dateMonthWeek][$dateMonthWeekDay][1][] = $calendarDataItem;
	        }

			// Layout	
		}
	}

    
	// Layout	

    // Initialize common template variables
	$xoopsTpl->assign('cal_type', $type);

	$xoopsTpl->assign('rights', $rights);    
	$xoopsTpl->assign('frids', $frids[0]);    
	$xoopsTpl->assign('fids', $fids[0]);    

	$xoopsTpl->assign('addItem', _formulize_CAL_ADD_ITEM);

	$xoopsTpl->assign('rowStyleEven', true);
    
	$xoopsTpl->assign('MonthNames', $arrayMonthNames);
	$xoopsTpl->assign('WeekNames', $arrayWeekNames);

	$xoopsTpl->assign('dateMonthZeroIndex', $dateMonth - 1);
	$xoopsTpl->assign('dateMonth', $dateMonth);
	$xoopsTpl->assign('dateYear', $dateYear);

	$xoopsTpl->assign('currentURL', $currentURL);
	$xoopsTpl->assign('hidden', $hidden);
	$xoopsTpl->assign('calview', $calview);

	$xoopsTpl->assign('calendarData', $calendarData);
    
	// force template to be drawn
    $xoopsTpl->display("db:calendar_" . $type . ".html");

    // Layout
}



/* 
 * days_in_month($month, $year) 
 * Returns the number of days in a given month and year, taking into account leap years. 
 * 
 * $month: numeric month (integers 1-12) 
 * $year: numeric year (any integer) 
 * 
 * Prec: $month is an integer between 1 and 12, inclusive, and $year is an integer. 
 * Post: none 
 */ 
// corrected by ben at sparkyb dot net 
function days_in_month($month, $year) 
{ 
	// calculate number of days in a month 
	return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31); 
}


function week_in_month($day)
{
    global $dateMonthStartDay;
    
	$value = (int)((($dateMonthStartDay + 1) + $day) / 7);

    return $value;    
}
?>