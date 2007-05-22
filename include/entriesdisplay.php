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

// THIS FILE CONTAINS FUNCTIONS FOR DISPLAYING A SUMMARY OF ENTRIES IN A FORM OR FRAMEWORK, AND DOING SEARCHES AND OTHER OPERATIONS ON THE DATA

// Basic order of operations:
// 1. determine if report is requested
// 2. if report is requested, get report data
// 3. if no report is requested, get scope data and header data for this form
// 4. check to see if search/sort/scope/column/etc changes sent from form
// 5. add/override existing settings with other settings -- add to hidden fields in the form if necessary (queue it up)
// 6. prepare list of available reports and view settings
// 7. draw top UI
// 8. draw notice about changes being made and ready to be applied if applicable?
// 9. draw results.


global $xoopsConfig;
// load the formulize language constants if they haven't been loaded already
	if ( file_exists(XOOPS_ROOT_PATH."/modules/formulize/language/".$xoopsConfig['language']."/main.php") ) {
		include_once XOOPS_ROOT_PATH."/modules/formulize/language/".$xoopsConfig['language']."/main.php";
	} else {
		include_once XOOPS_ROOT_PATH."/modules/formulize/language/english/main.php";
	}

// main function
// $screen will be a screen object if present
function displayEntries($formframe, $mainform="", $loadview="", $loadOnlyView=0, $viewallforms=0, $screen=null) {

	global $xoopsDB, $xoopsUser;
	include_once XOOPS_ROOT_PATH.'/modules/formulize/include/functions.php';

	// Set some required variables
	$mid = getFormulizeModId();
	list($fid, $frid) = getFormFramework($formframe, $mainform);
	$gperm_handler = &xoops_gethandler('groupperm');
	$member_handler =& xoops_gethandler('member');
	$groups = $xoopsUser ? $xoopsUser->getGroups() : XOOPS_GROUP_ANONYMOUS;
	$uid = $xoopsUser ? $xoopsUser->getVar('uid') : "0";

	if(!$scheck = security_check($fid, "", $uid, "", $groups, $mid, $gperm_handler, "")) {
		print "<p>" . _NO_PERM . "</p>";
		return;
	}

	// must wrap security check in only the conditions in which it is needed, so we don't interfere with saving data in a form (which independently checks the security token)
	if(($_POST['delconfirmed'] OR $_POST['cloneconfirmed'] OR $_POST['delview'] OR $_POST['saveid'])) {
		if(isset($GLOBALS['xoopsSecurity'])) {
			$formulize_LOESecurityPassed = $GLOBALS['xoopsSecurity']->check();
		} else { // if there is no security token, then assume true -- necessary for old versions of XOOPS.
			$formulize_LOESecurityPassed = true;
		}
	}

	// check for group and global permissions
	$view_globalscope = $gperm_handler->checkRight("view_globalscope", $fid, $groups, $mid);
	$view_groupscope = $gperm_handler->checkRight("view_groupscope", $fid, $groups, $mid);

	// Question:  do we need to add check here to make sure that $loadview is an available report (move function call from the generateViews function) and if it is not, then nullify
	// we may want to be able to pass in any old report, it's kind of like a way to override the publishing process.  Problem is unpublished reports or reports that aren't actually published to the user won't show up in the list of views.
	// [update: loaded views do not include the list of views, they have no interface at all except quick searches and quick sorts.  Since the intention is clearly for them to be accessed through pageworks, we will leave the permission control up to the application designer for now]

	$currentURL = getCurrentURL();

	// get title
	$displaytitle = getFormTitle($fid);

	// get default info and info passed to page....

	// clear any default search text that has been passed (because the user didn't actually search for anything)
	foreach($_POST as $k=>$v) {
		if(substr($k, 0, 7) == "search_" AND $v==_formulize_DE_SEARCH_HELP) {
			unset($_POST[$k]);
			break; // assume this is only sent once, since the help text only appears in the first column
		}
	}

	// check for deletion request and then delete entries
	if($_POST['delconfirmed'] AND $formulize_LOESecurityPassed) { // only gets set by clicking on the delete selected button
		foreach($_POST as $k=>$v) {
			if(substr($k, 0, 7) == "delete_" AND $v != "") {
				$thisentry = substr($k, 7);
				$GLOBALS['formulize_deletionRequested'] = true;
				// new syntax for deleteEntry, Sept 18 2005 -- used to handle deleting all unified display entries that are linked to this entry.  
				if($frid) {
					deleteEntry($thisentry, $frid, $fid, $gperm_handler, $member_handler, $mid);
				} else {
					deleteEntry($thisentry);
				}
			}
		}
	}

	// check for cloning request and if present then clone entries
	if($_POST['cloneconfirmed'] AND $formulize_LOESecurityPassed) {
//		print $_POST['cloneconfirmed'];
		foreach($_POST as $k=>$v) {
			if(substr($k, 0, 7) == "delete_" AND $v != "") {
				$thisentry = substr($k, 7);
				cloneEntry($thisentry, $frid, $fid, $_POST['cloneconfirmed']); // cloneconfirmed is the number of copies required  
			}
		}
	}


	// handle deletion of view...reset currentView
	if($_POST['delview'] AND $formulize_LOESecurityPassed) {
		if(substr($_POST['delviewid'], 1, 4) == "old_") {
			$sql = "DELETE FROM " . $xoopsDB->prefix("formulize_reports") . " WHERE report_id='" . substr($_POST['delviewid'], 5) . "'";
		} else {
			$sql = "DELETE FROM " . $xoopsDB->prefix("formulize_saved_views") . " WHERE sv_id='" . substr($_POST['delviewid'], 1) . "'";
		}
		if(!$res = $xoopsDB->query($sql)) {
			exit("Error deleting report: " . $_POST['delviewid']);
		}
		unset($_POST['currentview']);
		$_POST['resetview'] = 1;
	}
	// if resetview is set, then unset POST and then set currentview to resetview
	// intended for when a user switches from a locked view back to a basic view.  In that case we want all settings to be cleared and everything to work like the basic view, rather than remembering, for instance, that the previous view had a calculation or a search of something.
	// users who view reports (views) that aren't locked can switch back to a basic view and retain settings.  This is so they can make changes to a view and then save the updates.  It is also a little confusing to switch from a predefined view to a basic one but have the predefined view's settings still hanging around.
	// recommendation to users should be to lock the controls for all published views.
	// (this routine also invoked when a view has been deleted)
	$resetview = false;
	if($_POST['resetview']) {
		$resetview = $_POST['currentview'];
		foreach($_POST as $k=>$v) {
			unset($_POST[$k]);
		}
		$_POST['currentview'] = $resetview;
	}

	// handle saving of the view if that has been requested
	if($_POST['saveid'] AND $formulize_LOESecurityPassed) {
		// gather all values
		//$_POST['currentview'] -- from save (they might have updated/changed the scope)
		//possible situations:
		// user replaced a report, so we need to set that report as the name of the dropdown, value is currentview
		// user made a new report, so we need to set that report as the name and the value is currentview
		// so name of the report gets sent to $loadedView, which also gets assigned to settings array
		// report is either newid or newname if newid is "new"
		// newscope goes to $_POST['currentview']
		//$_POST['oldcols'] -- from page
		//$_POST['asearch'] -- from page
		//$_POST['calc_cols'] -- from page
		//$_POST['calc_calcs'] -- from page
		//$_POST['calc_blanks'] -- from page
		//$_POST['calc_grouping'] -- from page
		//$_POST['sort'] -- from page
		//$_POST['order'] -- from page
		//$_POST['hlist'] -- passed from page
		//$_POST['hcalc'] -- passed from page
		//$_POST['lockcontrols'] -- passed from save
		//and quicksearches -- passed with the page
		// pubgroups -- passed from save
		
		$_POST['currentview'] = $_POST['savescope'];
		$saveid = $_POST['saveid'];
		$_POST['lockcontrols'] = $_POST['savelock'];
		$savegroups = $_POST['savegroups'];

		// put name into loadview
		if($saveid != "new") {
			if(strstr($saveid, "old_")) { // legacy
				$sname = q("SELECT report_name FROM " . $xoopsDB->prefix("formulize_reports") . " WHERE report_id = \"" . substr($saveid, 5) . "\"");
				$savename = $sname[0]['report_name'];
			} else {
				$sname = q("SELECT sv_name, sv_owner_uid FROM " . $xoopsDB->prefix("formulize_saved_views") . " WHERE sv_id = \"" . substr($saveid, 1) . "\"");
				$savename = $sname[0]['sv_name'];
				if($sname[0]['sv_owner_uid'] == $uid) {
					$loadedView = $saveid;
				} else {
					$loadedView =  "p" . substr($saveid, 1);
				}
			}
		} else {
			$savename = $_POST['savename'];
		}

		// flatten quicksearches -- one value in the array for every column in the view
		$allcols = explode(",", $_POST['oldcols']);
		foreach($allcols as $thiscol) {
			$allquicksearches[] = $_POST['search_' . $thiscol];
		}
		$qsearches = implode("&*=%4#", $allquicksearches);

		$savename = mysql_real_escape_string($savename);
		$savesearches = mysql_real_escape_string($_POST['asearch']);
		//print $_POST['asearch'] . "<br>";
		//print "$savesearches<br>";
		$qsearches = mysql_real_escape_string($qsearches);

		if($frid) { 
			$saveformframe = $frid;
			$savemainform = $fid;
		} else {
			$saveformframe = $fid;
			$savemainform = "";
		}

		if($saveid == "new" OR strstr($saveid, "old_")) {
			if ($saveid == "new") {
				$owneruid = $uid;
				$moduid = $uid;
			} else {
				// get existing uid
				$olduid = q("SELECT report_uid FROM " . $xoopsDB->prefix("formulize_reports") . " WHERE report_id = '" . substr($saveid, 5) . "'");
				$owneruid = $olduid[0]['report_uid'];
				$moduid = $uid;
			}
			$savesql = "INSERT INTO " . $xoopsDB->prefix("formulize_saved_views") . " (sv_name, sv_pubgroups, sv_owner_uid, sv_mod_uid, sv_formframe, sv_mainform, sv_lockcontrols, sv_hidelist, sv_hidecalc, sv_asearch, sv_sort, sv_order, sv_oldcols, sv_currentview, sv_calc_cols, sv_calc_calcs, sv_calc_blanks, sv_calc_grouping, sv_quicksearches) VALUES (\"$savename\", \"$savegroups\", \"$owneruid\", \"$moduid\", \"$saveformframe\", \"$savemainform\", \"{$_POST['savelock']}\", \"{$_POST['hlist']}\", \"{$_POST['hcalc']}\", \"$savesearches\", \"{$_POST['sort']}\", \"{$_POST['order']}\", \"{$_POST['oldcols']}\", \"{$_POST['savescope']}\", \"{$_POST['calc_cols']}\", \"{$_POST['calc_calcs']}\", \"{$_POST['calc_blanks']}\", \"{$_POST['calc_grouping']}\", \"$qsearches\")";
		} else {
			// print "UPDATE " . $xoopsDB->prefix("formulize_saved_views") . " SET sv_pubgroups=\"$savegroups\", sv_mod_uid=\"$uid\", sv_lockcontrols=\"{$_POST['savelock']}\", sv_hidelist=\"{$_POST['hlist']}\", sv_hidecalc=\"{$_POST['hcalc']}\", sv_asearch=\"$savesearches\", sv_sort=\"{$_POST['sort']}\", sv_order=\"{$_POST['order']}\", sv_oldcols=\"{$_POST['oldcols']}\", sv_currentview=\"{$_POST['savescope']}\", sv_calc_cols=\"{$_POST['calc_cols']}\", sv_calc_calcs=\"{$_POST['calc_calcs']}\", sv_calc_blanks=\"{$_POST['calc_blanks']}\", sv_calc_grouping=\"{$_POST['calc_grouping']}\", sv_quicksearches=\"$qsearches\" WHERE sv_id = \"" . substr($saveid, 1) . "\"";
			$savesql = "UPDATE " . $xoopsDB->prefix("formulize_saved_views") . " SET sv_pubgroups=\"$savegroups\", sv_mod_uid=\"$uid\", sv_lockcontrols=\"{$_POST['savelock']}\", sv_hidelist=\"{$_POST['hlist']}\", sv_hidecalc=\"{$_POST['hcalc']}\", sv_asearch=\"$savesearches\", sv_sort=\"{$_POST['sort']}\", sv_order=\"{$_POST['order']}\", sv_oldcols=\"{$_POST['oldcols']}\", sv_currentview=\"{$_POST['savescope']}\", sv_calc_cols=\"{$_POST['calc_cols']}\", sv_calc_calcs=\"{$_POST['calc_calcs']}\", sv_calc_blanks=\"{$_POST['calc_blanks']}\", sv_calc_grouping=\"{$_POST['calc_grouping']}\", sv_quicksearches=\"$qsearches\" WHERE sv_id = \"" . substr($saveid, 1) . "\"";
		}

		// save the report
		if(!$result = $xoopsDB->query($savesql)) {
			exit("Error:  unable to save the current view settings.  SQL dump: $savesql");
		}
		if($saveid == "new" OR strstr($saveid, "old_")) {
			if($owneruid == $uid) {
				$loadedView = "s" . $xoopsDB->getInsertId();
			} else {
				$loadedView = "p" . $xoopsDB->getInsertId();
			}
		}
		$settings['loadedview'] = $loadedView;

		// delete legacy report if necessary
		if(strstr($saveid, "old_")) {
			$dellegacysql = "DELETE FROM " . $xoopsDB->prefix("formulize_reports") . " WHERE report_id=\"" . substr($saveid, 5) . "\"";
			if(!$result = $xoopsDB->query($dellegacysql)) {
				exit("Error:  unable to delete legacy report: " . substr($saveid, 5));
			}
		}

	}



	$forceLoadView = false;
	if($screen) {
		$loadview = $screen->getVar('defaultview'); // flag the screen default for loading
		if($loadview == "mine" OR $loadview == "group" OR $loadview == "all") {
			$currentView = $loadview; // if the default is a standard view, then use that instead and don't load anything
			unset($loadview);
		} elseif($_POST['userClickedReset']) { // only set if the user actually clicked that button, and in that case, we want to be sure we load the default as specified for the screen
			$forceLoadView = true; 
		}
	}
		
	// set currentView to group if they have groupscope permission (overridden below by value sent from form)
	// override with loadview if that is specified
	if($loadview AND ((!$_POST['currentview'] AND $_POST['advscope'] == "") OR $forceLoadView)) {
		if(substr($loadview, 0, 4) == "old_") { // this is a legacy view
			$loadview = "p" . $loadview;
		} elseif(is_numeric($loadview)) { // new view id
			$loadview = "p" . $loadview;
		} else { // new view name -- loading view by name -- note if two reports have the same name, then the first one created will be returned
			$viewnameq = q("SELECT sv_id FROM " . $xoopsDB->prefix("formulize_saved_views") . " WHERE sv_name='$loadview' ORDER BY sv_id");
			$loadview = "p" . $viewnameq[0]['sv_id'];
		}
		$_POST['currentview'] = $loadview;
		$_POST['loadreport'] = 1;
	} elseif($view_groupscope AND !$currentView) {
		$currentView = "group";
	} elseif(!$currentView) {
		$currentView = "mine";
	}
		

	// debug block to show key settings being passed back to the page
/*
	if($uid == 1) {
	print "delview: " . $_POST['delview'] . "<br>";
	print "advscope: " . $_POST['advscope'] . "<br>";
	print "asearch: " . $_POST['asearch'] . "<br>";
	print "Hidelist: " . $_POST['hlist'] . "<br>";
	print "Hidecalc: " . $_POST['hcalc'] . "<br>";
	print "Lock Controls: " . $_POST['lockcontrols'] . "<br>";
	print "Sort: " . $_POST['sort'] . "<br>";
	print "Order: " . $_POST['order'] . "<br>";
	print	"Cols: " . $_POST['oldcols'] . "<br>";
	print "Curview: " . $_POST['currentview'] . "<br>";
	print "Calculation columns: " . $_POST['calc_cols'] . "<br>";
	print "Calculation calcs: " . $_POST['calc_calcs'] . "<br>";
	print "Calculation blanks: " . $_POST['calc_blanks'] . "<br>";
	print "Calculation grouping: " . $_POST['calc_grouping'] . "<br>";
	foreach($_POST as $k=>$v) {
		if(substr($k, 0, 7) == "search_" AND $v != "") {
			print "$k: $v<br>";
		}
	}
	}
*/


	// get control settings passed from form 

	// handling change in view, and loading reports/saved views if necessary
	if($_POST['loadreport']) {
		if(substr($_POST['currentview'], 1, 4) == "old_") { // legacy report
			// load old report values and then assign them to the correct $_POST keys in order to present the view
			$loadedView = $_POST['currentview'];
			$settings['loadedview'] = $loadedView;
			// kill the quicksearches
			foreach($_POST as $k=>$v) {
				if(substr($k, 0, 7) == "search_" AND $v != "") {
					unset($_POST[$k]);
				}
			}

			list($_POST['currentview'], $_POST['oldcols'], $_POST['asearch'], $_POST['calc_cols'], $_POST['calc_calcs'], $_POST['calc_blanks'], $_POST['calc_grouping'], $_POST['sort'], $_POST['order'], $_POST['hlist'], $_POST['hcalc'], $_POST['lockcontrols']) = loadOldReport(substr($_POST['currentview'], 5), $fid, $view_groupscope);

		} elseif(is_numeric(substr($_POST['currentview'], 1))) { // saved or published view
			$loadedView = $_POST['currentview'];
			$settings['loadedview'] = $loadedView;
			// kill the quicksearches
			foreach($_POST as $k=>$v) {
				if(substr($k, 0, 7) == "search_" AND $v != "") {
					unset($_POST[$k]);
				}
			}
			list($_POST['currentview'], $_POST['oldcols'], $_POST['asearch'], $_POST['calc_cols'], $_POST['calc_calcs'], $_POST['calc_blanks'], $_POST['calc_grouping'], $_POST['sort'], $_POST['order'], $_POST['hlist'], $_POST['hcalc'], $_POST['lockcontrols'], $quicksearches) = loadReport(substr($_POST['currentview'], 1));
			// explode quicksearches into the search_ values
			$allqsearches = explode("&*=%4#", $quicksearches);
			$colsforsearches = explode(",", $_POST['oldcols']);
			for($i=0;$i<count($allqsearches);$i++) {
				if($allqsearches[$i] != "") {
					$_POST["search_" . $colsforsearches[$i]] = $allqsearches[$i];
				}
			}
		}
		
		/*print "<br>Currentview: " . $_POST['currentview'] . "<br>Oldcols: ";
		print $_POST['oldcols'] . "<br>asearch: ";
		print $_POST['asearch'] . "<br>calc_cols: ";
		print $_POST['calc_cols'] . "<br>calc_calcs: ";
		print $_POST['calc_calcs'] . "<br>calc_blanks: ";
		print $_POST['calc_blanks'] . "<br>calc_grouping: ";
		print $_POST['calc_grouping'] . "<br>sort: ";
		print $_POST['sort'] . "<br>order: ";
		print $_POST['order'] . "<br>"; 
		print $_POST['hlist'] . "<br>"; 
		print $_POST['hcalc'] . "<br>"; 
		print $_POST['lockcontrols'] . "<br>"; */
		
		$currentView = $_POST['currentview']; 
	} elseif($_POST['advscope'] AND strstr($_POST['advscope'], ",")) { // looking for comma sort of means that we're checking that a valid advanced scope is being sent
		$currentView = $_POST['advscope'];
	} elseif($_POST['currentview']) { // could have been unset by deletion of a view or something else, so we must check to make sure it exists before we override the default that was determined above
		$currentView = $_POST['currentview'];
	} elseif($loadview) {
		$currentView = $loadview;
	}

	// get columns for this form/framework or use columns sent from interface
	// ele_ids for a form, handles for a framework, includes handles of all unified display forms
	if($_POST['oldcols']) {
		$showcols = explode(",", $_POST['oldcols']); 
	} else { // or use the defaults
		$showcols = getDefaultCols($fid, $frid);
	}

	if($_POST['newcols']) {
		$temp_showcols = $_POST['newcols'];
		$showcols = explode(",", $temp_showcols);
		if($frid) { // convert ids to form handles for a framework
			$temp_handles = convertIds($showcols, $frid);
			unset($showcols);
			$showcols = $temp_handles;
		}
	}

	$showcols = removeNotAllowedCols($fid, $frid, $showcols, $groups);


	// clear quick searches for any columns not included now
	foreach($_POST as $k=>$v) {
		if(substr($k, 0, 7) == "search_" AND !in_array(substr($k, 7), $showcols)) {
			unset($_POST[$k]);
		}
	}

	// Create settings array to pass to form page or to other functions

	$settings['title'] = $displaytitle;

	// get export options
	if($_POST['xport']) {
		$settings['xport'] = $_POST['xport'];
		if($_POST['xport'] == "custom") {
			$settings['xport_cust'] = $_POST['xport_cust'];
		}
	}

	// generate the available views

	// pubstart used to indicate to the delete button where the list of published views begins in the current view drop down (since you cannot delete published views)
	list($settings['viewoptions'], $settings['pubstart'], $settings['endstandard'], $settings['pickgroups'], $settings['loadviewname'], $settings['curviewid'], $settings['publishedviewnames']) = generateViews($fid, $uid, $groups, $frid, $currentView, $loadedView, $view_groupscope, $view_globalscope, $_POST['curviewid'], $loadOnlyView, $screen, $_POST['lastloaded']);

	// this param only used in case of loading of reports via passing in the report id or name through $loadview
	if($_POST['loadviewname']) { $settings['loadviewname'] = $_POST['loadviewname']; }

	// if a view was loaded, then update the lastloaded value, otherwise preserve the previous value
	if($settings['curviewid']) { 
		$settings['lastloaded'] = $settings['curviewid']; 
	} else {
		$settings['lastloaded'] = $_POST['lastloaded'];
	}


	$settings['currentview'] = $currentView;

	$settings['currentURL'] = $currentURL; 

	$settings['columns'] = $showcols;
	
	if($frid) {
		$settings['columnids'] = convertHandles($showcols, $frid);
	} else {
		$settings['columnids'] = $showcols;
	}
	
	$settings['hlist'] = $_POST['hlist'];
	$settings['hcalc'] = $_POST['hcalc'];

	// determine if the controls should really be locked...
	if($_POST['lockcontrols']) { // if a view locks the controls
		// only lock the controls when the user is not a member of the currentview groups AND has no globalscope
		// OR if they are a member of the currentview groups AND has no groupscope or no globalscope
		switch($currentView) {
			case "mine":
				$settings['lockcontrols'] = "";
				break;
			case "all":
				if($view_globalscope) {
					$settings['lockcontrols'] = "";
				} else {
					$settings['lockcontrols'] = "1";
				}
				break;
			case "group":
				if($view_groupscope OR $view_globalscope) {
					$settings['lockcontrols'] = "";
				} else {
					$settings['lockcontrols'] = "1";
				}
				break;
			default:
				$viewgroups = explode(",", trim($currentView, ","));
				$groupsWithAccess = $gperm_handler->getGroupIds("view_form", $fid, $mid);
				$diff = array_diff($viewgroups, $groupsWithAccess);
				if(!isset($diff[0]) AND $view_groupscope) { // if the scopegroups are completely included in the user's groups that have access to the form, and they have groupscope (ie: they would be allowed to see all these entries anyway)
					$settings['lockcontrols'] = "";
				} elseif($view_globalscope) { // if they have global scope
					$settings['lockcontrols'] = "";
				} else { // no globalscope and even if they're a member of the scope for this view, they don't have groupscope
					$settings['lockcontrols'] = "1";
				}		
		}
	} else {
		$settings['lockcontrols'] = "";
	}

	$settings['asearch'] = $_POST['asearch'];
	if($_POST['asearch']) {
		$as_array = explode("/,%^&2", $_POST['asearch']);
		foreach($as_array as $k=>$one_as) {
			$settings['as_' . $k] = $one_as;
		}
	}

/*	if($_POST['newcols']) {
		$settings['oldcols'] = $_POST['newcols'];
	} else {
		$settings['oldcols'] = $_POST['oldcols'];
	}
*/
	$settings['oldcols'] = implode(",", $showcols);

	$settings['ventry'] = $_POST['ventry'];

	// get sort and order options

	$settings['sort'] = $_POST['sort'];
	$settings['order'] = $_POST['order'];

	//get all submitted search text
	foreach($_POST as $k=>$v) {
		if(substr($k, 0, 7) == "search_" AND $v != "") {
			$thiscol = substr($k, 7);
			$searches[$thiscol] = $v;
			$temp_key = "search_" . $thiscol;
			$settings[$temp_key] = $v;
		}
	}

	// get all requested calculations...assign to settings array.
	$settings['calc_cols'] = $_POST['calc_cols'];	
	$settings['calc_calcs'] = $_POST['calc_calcs'];
	$settings['calc_blanks'] = $_POST['calc_blanks'];
	$settings['calc_grouping'] = $_POST['calc_grouping'];

	// gather id of the cached data, if any
	$settings['formulize_cacheddata'] = strip_tags($_POST['formulize_cacheddata']);

	if($_POST['ventry']) { // user clicked on a view this entry link
		include_once XOOPS_ROOT_PATH . '/modules/formulize/include/formdisplay.php';

		if($_POST['ventry'] == "addnew" OR $_POST['ventry'] == "single") {
			$this_ent = "";
		} elseif($_POST['ventry'] == "proxy") {
			$this_ent = "proxy";
		} else {
			$this_ent = $_POST['ventry'];
		}

		if($_POST['ventry'] != "single") {
			if($frid) {
				displayForm($frid, $this_ent, $fid, $currentURL, "", $settings, "", "", "", "", $viewallforms); // "" is the done text
				return;
			} else {
				displayForm($fid, $this_ent, "", $currentURL, "", $settings, "", "", "", "", $viewallforms); // "" is the done text
				return;
			}
		} else { // if a single entry was requested for a form that can have multiple entries, then specifically override the multiple entry UI (which causes a blank form to appear on save)
			if($frid) {
				displayForm($frid, $this_ent, $fid, $currentURL, "", $settings, "", "", "1", "", $viewallforms); // "" is the done text
				return;
			} else {
				displayForm($fid, $this_ent, "", $currentURL, "", $settings, "", "", "1", "", $viewallforms); // "" is the done text
				return;
			}
		}
	
	} 

	// process a clicked custom button
	// must do this before gathering the data!
	$messageText = "";
	if(isset($_POST['caid']) AND $screen) {
		$customButtonDetails = $screen->getVar('customactions');
		if(is_numeric($_POST['caid']) AND isset($customButtonDetails[$_POST['caid']])) {
			list($caCode, $caElements, $caActions, $caValues, $caMessageText, $caApplyTo) = processCustomButton($_POST['caid'], $customButtonDetails[$_POST['caid']]); // just processing to get the info so we can process the click.  Actual output of this button happens lower down
			$messageText = processClickedCustomButton($caElements, $caValues, $caActions, $caMessageText, $caApplyTo);
		}
	}

	include_once XOOPS_ROOT_PATH . "/modules/formulize/include/extract.php";
	$scope = buildScope($currentView, $member_handler, $gperm_handler, $uid, $groups, $fid, $mid);
	// create $data and $wq (writable query)
	list($data, $wq, $regeneratePageNumbers) = formulize_gatherDataSet($settings, $searches, strip_tags($_POST['sort']), strip_tags($_POST['order']), $frid, $fid, $scope, $screen, $currentURL);
	$formulize_LOEPageNav = formulize_LOEbuildPageNav($data, $screen, $regeneratePageNumbers);

	$formulize_buttonCodeArray = array();
	list($formulize_buttonCodeArray) = drawInterface($settings, $fid, $frid, $groups, $mid, $gperm_handler, $loadview, $loadOnlyView, $screen, $searches, $formulize_LOEPageNav, $messageText);

	// if there is messageText and no custom top template, and no messageText variable in the bottom template, then we have to output the message text here
	if($screen AND $messageText) {
		if(trim($screen->getVar('toptemplate')) == "" AND !strstr($screen->getVar('bottomtemplate'), 'messageText')) {
			print "<p><center><b>$messageText</b></center></p>\n";
		}
	}

	drawEntries($fid, $showcols, strip_tags($_POST['sort']), strip_tags($_POST['order']), $searches, $frid, $scope, "", $currentURL, $gperm_handler, $uid, $mid, $groups, $settings, $member_handler, $screen, $data, $wq, $regeneratePageNumbers); // , $loadview); // -- loadview not passed any longer since the lockcontrols indicator is used to handle whether things should appear or not.

	
	if($screen) {
		formulize_screenLOETemplate($screen, "bottom", $formulize_buttonCodeArray, $settings);
	} else {
		print $formulize_LOEPageNav; // redraw page numbers if there is no screen in effect
	}
	if(isset($formulize_buttonCodeArray['submitButton'])) { // if a custom top template was in effect, this will have been sent back, so now we display it at the very bottom of the form so it doesn't take up a visible amount of space above (the submitButton is invisible, but does take up space)
		print "<p>" . $formulize_buttonCodeArray['submitButton'] . "</p>";
	}

	print "</form>\n"; // end of the form started in drawInterface

	print "</div>\n"; // end of the listofentries div, used to call up the working message when the page is reloading, started in drawInterface

}

// return the available current view settings based on the user's permissions
function generateViews($fid, $uid, $groups, $frid="0", $currentView, $loadedView="", $view_groupscope, $view_globalscope, $prevview="", $loadOnlyView=0, $screen, $lastLoaded) {
	global $xoopsDB;

	$limitViews = false;
	$screenLimitViews = array();
	$forceLastLoaded = false;
	if($screen) {
		$screenLimitViews = $screen->getVar('limitviews');
		if(!in_array("allviews", $screenLimitViews)) {
			$limitViews = true;
			
			// IF LIMIT VIEWS IS IN EFFECT, THEN CHECK FOR BASIC VIEWS BEING ENABLED, AND IF THEY ARE NOT, THEN WE NEED TO SET THE CURRENT VIEW LIST TO THE LASTLOADED
			// Excuses....This is a future todo item.  Very complex UI issues, in that user could change options, then switch to other view, then switch back and their options are missing now
			// Right now, without basic views enabled, the first view in the list comes up if an option is changed (since the basic scope cannot be reflected in the available views), so that's just confusing
			// Could have 'custom' option show up at top of list instead, just to indicate to the user that things are not the options originally loaded from that view
			
			if((!in_array("mine", $screenLimitViews) AND !in_array("group", $screenLimitViews) AND !in_array("all", $screenLimitViews)) AND !$_POST['loadreport'] ) { // if the basic views are not present, and the user hasn't specifically changed the current view list
				$forceLastLoaded = true;
			} else {
				$forceLastLoaded = false;
			}
			
		}
	}


	$options =  !$limitViews ? "<option value=\"\">" . _formulize_DE_STANDARD_VIEWS . "</option>\n" : "";
	$vcounter=0;

	if($loadOnlyView AND $loadedView AND !$limitViews) {
		$vcounter++;
		$options .= "<option value=\"\">&nbsp;&nbsp;" . _formulize_DE_NO_STANDARD_VIEWS . "</option>\n";
	}

		
	if($currentView == "mine" AND !$loadOnlyView AND (!$limitViews OR in_array("mine", $screenLimitViews))) {
		$options .= "<option value=mine selected>&nbsp;&nbsp;" . _formulize_DE_MINE . "</option>\n";
		$vcounter++;	
	} elseif(!$loadOnlyView AND (!$limitViews OR in_array("mine", $screenLimitViews))) {
		$vcounter++;
		$options .= "<option value=mine>&nbsp;&nbsp;" . _formulize_DE_MINE . "</option>\n";
	}



	if($currentView == "group" AND $view_groupscope AND !$loadOnlyView AND (!$limitViews OR in_array("group", $screenLimitViews))) {
		$options .= "<option value=group selected>&nbsp;&nbsp;" . _formulize_DE_GROUP . "</option>\n";
		$vcounter++;
	} elseif($view_groupscope AND !$loadOnlyView AND (!$limitViews OR in_array("group", $screenLimitViews))) {
		$vcounter++;
		$options .= "<option value=group>&nbsp;&nbsp;" . _formulize_DE_GROUP . "</option>\n";
	} 

	if($currentView == "all" AND $view_globalscope AND !$loadOnlyView AND (!$limitViews OR in_array("all", $screenLimitViews))) {
		$options .= "<option value=all selected>&nbsp;&nbsp;" . _formulize_DE_ALL . "</option>\n";
		$vcounter++;
	} elseif($view_globalscope AND !$loadOnlyView AND (!$limitViews OR in_array("all", $screenLimitViews))) {
		$vcounter++;
		$options .= "<option value=all>&nbsp;&nbsp;" . _formulize_DE_ALL . "</option>\n";
	} 

	// check for pressence of advanced scope
	if(strstr($currentView, ",") AND !$loadedView AND !$limitViews) { 
		$vcounter++;
		$groupNames = groupNameList(trim($currentView, ","));
		$options .= "<option value=$currentView selected>&nbsp;&nbsp;" . _formulize_DE_AS_ENTRIESBY . printSmart($groupNames) . "</option>\n";
	} elseif(($view_globalscope OR $view_groupscope) AND !$loadOnlyView AND !$limitViews) {
		$vcounter++;	
		$pickgroups = $vcounter;
		$options .= "<option value=\"\">&nbsp;&nbsp;" . _formulize_DE_AS_PICKGROUPS . "</option>\n";
	}


	// check for available reports/views
	list($s_reports, $p_reports, $ns_reports, $np_reports) = availReports($uid, $groups, $fid, $frid);
	$lastStandardView = $vcounter;

	if(!$limitViews) { // cannot pick saved views in the screen UI so these will never be available if views are being limited
		if((count($s_reports)>0 OR count($ns_reports)>0) AND !$limitViews) { // we have saved reports...
			$options .= "<option value=\"\">" . _formulize_DE_SAVED_VIEWS . "</option>\n";
			$vcounter++;
		}
		for($i=0;$i<count($s_reports);$i++) {
			if($loadedView == "sold_" . $s_reports[$i]['report_id'] OR $prevview == "sold_" . $s_reports[$i]['report_id']) {
				$vcounter++;
				$options .= "<option value=$currentView selected>&nbsp;&nbsp;" . stripslashes($s_reports[$i]['report_name']) . "</option>\n"; // " (id: " . $s_reports[$i]['report_id'] . ")</option>\n";
				$loadviewname = $s_reports[$i]['report_name'];
				$curviewid = "sold_" . $s_reports[$i]['report_id'];
			} else {
				$vcounter++;
				$options .= "<option value=sold_" . $s_reports[$i]['report_id'] . ">&nbsp;&nbsp;" . stripslashes($s_reports[$i]['report_name']) . "</option>\n"; // " (id: " . $s_reports[$i]['report_id'] . ")</option>\n";
			}
		}
		for($i=0;$i<count($ns_reports);$i++) {
			if($loadedView == "s" . $ns_reports[$i]['sv_id'] OR $prevview == "s" . $ns_reports[$i]['sv_id']) {
				$vcounter++;
				$options .= "<option value=$currentView selected>&nbsp;&nbsp;" . stripslashes($ns_reports[$i]['sv_name']) . "</option>\n"; // " (id: " . $ns_reports[$i]['sv_id'] . ")</option>\n";
				$loadviewname = $ns_reports[$i]['sv_name'];
				$curviewid = "s" . $ns_reports[$i]['sv_id'];
			} else {
				$vcounter++;
				$options .= "<option value=s" . $ns_reports[$i]['sv_id'] . ">&nbsp;&nbsp;" . stripslashes($ns_reports[$i]['sv_name']) . "</option>\n"; // " (id: " . $ns_reports[$i]['sv_id'] . ")</option>\n";
			}
		}
	}	
	

	if((count($p_reports)>0 OR count($np_reports)>0) AND !$limitViews) { // we have saved reports...
		$options .= "<option value=\"\">" . _formulize_DE_PUB_VIEWS . "</option>\n";
		$vcounter++;
	}
	$firstPublishedView = $vcounter + 1;
	if(!$limitViews) { // old reports are not selectable in the screen UI so will never be in the limit list
		for($i=0;$i<count($p_reports);$i++) {
			if($loadedView == "pold_" . $p_reports[$i]['report_id'] OR $prevview == "pold_" . $p_reports[$i]['report_id']) {
				$vcounter++;
				$options .= "<option value=$currentView selected>&nbsp;&nbsp;" . stripslashes($p_reports[$i]['report_name']) . "</option>\n"; // " (id: " . $p_reports[$i]['report_id'] . ")</option>\n";
				$loadviewname = $p_reports[$i]['report_name'];
				$curviewid = "pold_" . $p_reports[$i]['report_id'];
			} else {
				$vcounter++;
				$options .= "<option value=pold_" . $p_reports[$i]['report_id'] . ">&nbsp;&nbsp;" . stripslashes($p_reports[$i]['report_name']) . "</option>\n"; // " (id: " . $p_reports[$i]['report_id'] . ")</option>\n";
			}
		}
	}
	$publishedViewNames = array();
	for($i=0;$i<count($np_reports);$i++) {
		if(!$limitViews OR in_array($np_reports[$i]['sv_id'], $screenLimitViews)) {
			if($loadedView == "p" . $np_reports[$i]['sv_id'] OR $prevview == "p" . $np_reports[$i]['sv_id'] OR ($forceLastLoaded AND $lastLoaded == "p" . $np_reports[$i]['sv_id'])) {
				$vcounter++;
				$options .= "<option value=$currentView selected>&nbsp;&nbsp;" . stripslashes($np_reports[$i]['sv_name']) . "</option>\n"; // " (id: " . $np_reports[$i]['sv_id'] . ")</option>\n";
				$loadviewname = $np_reports[$i]['sv_name'];
				$curviewid = "p" . $np_reports[$i]['sv_id'];
			} else {
				$vcounter++;
				$options .= "<option value=p" . $np_reports[$i]['sv_id'] . ">&nbsp;&nbsp;" . stripslashes($np_reports[$i]['sv_name']) . "</option>\n"; // " (id: " . $np_reports[$i]['sv_id'] . ")</option>\n";
			}
			$publishedViewNames["p" . $np_reports[$i]['sv_id']] = stripslashes($np_reports[$i]['sv_name']); // used by the screen system to create a variable for each view name, and only the last loaded view is set to true.
		}
	}
	$to_return[0] = $options;
	$to_return[1] = $firstPublishedView;
	$to_return[2] = $lastStandardView;
	$to_return[3] = $pickgroups;
	$to_return[4] = $loadviewname;
	$to_return[5] = $curviewid;
	$to_return[6] = $publishedViewNames;
	return $to_return;

}

// this function draws in the interface parts of a display entries widget
function drawInterface($settings, $fid, $frid, $groups, $mid, $gperm_handler, $loadview="", $loadOnlyView=0, $screen, $searches, $pageNav, $messageText) {

	global $xoopsDB;
	// unpack the $settings
	foreach($settings as $k=>$v) {
		${$k} = $v;
	}
	
	
	
	// get single/multi entry status of this form...
	$singleMulti = q("SELECT singleentry FROM " . $xoopsDB->prefix("formulize_id") . " WHERE id_form = $fid");
		
	// flatten columns array and convert handles to ids so that we can send them to the change columns popup
	$colids = implode(",", $columnids); // part of $settings
	$flatcols = implode(",", $columns); // part of $settings (will be IDs if no framework in effect)

	$useWorking = true;
	$useDefaultInterface = true;
	$useSearch = 1;
	if($screen) {
		$useWorking = !$screen->getVar('useworkingmsg') ? false : true;
		$useDefaultInterface = $screen->getVar('toptemplate') != "" ? false : true;
		$title = $screen->getVar('title'); // otherwise, title of the form is in the settings array for when no screen is in use
		$useSearch = ($screen->getVar('usesearch') AND !$screen->getVar('listtemplate')) ? 1 : 0;
	}
	
	if(strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
		$submitButton = "<input type=submit name=submitx style=\"width:0px; height:0px;\" value='' ></input>\n";
	} else {
		$submitButton =  "<input type=submit name=submitx style=\"visibility: hidden;\" value='' ></input>\n";
	}

	// establish text and code for buttons, whether a screen is in effect or not
	$screenButtonText = array();
	$screenButtonText['changeColsButton'] = _formulize_DE_CHANGECOLS;
	$screenButtonText['calcButton'] = _formulize_DE_CALCS;
	$screenButtonText['advSearchButton'] = _formulize_DE_ADVSEARCH;
	$screenButtonText['exportButton'] = _formulize_DE_EXPORT;
	$screenButtonText['exportCalcsButton'] = _formulize_DE_EXPORT_CALCS;
	$screenButtonText['importButton'] = _formulize_DE_IMPORTDATA;
	$screenButtonText['notifButton'] = _formulize_DE_NOTBUTTON;
	$screenButtonText['cloneButton'] = _formulize_DE_CLONESEL;
	$screenButtonText['deleteButton'] = _formulize_DE_DELETESEL;
	$screenButtonText['selectAllButton'] = _formulize_DE_SELALL;
	$screenButtonText['clearSelectButton'] = _formulize_DE_CLEARALL;
	$screenButtonText['resetViewButton'] = _formulize_DE_RESETVIEW;
	$screenButtonText['saveViewButton'] = _formulize_DE_SAVE;
	$screenButtonText['deleteViewButton'] = _formulize_DE_DELETE;
	$screenButtonText['currentViewList'] = _formulize_DE_CURRENT_VIEW;
	$screenButtonText['saveButton'] = _formulize_SAVE;
	$screenButtonText['addButton'] = $singleMulti[0]['singleentry'] == "" ? _formulize_DE_ADDENTRY : _formulize_DE_UPDATEENTRY;
	$screenButtonText['addMultiButton'] = _formulize_DE_ADD_MULTIPLE_ENTRY;
	$screenButtonText['addProxyButton'] = _formulize_DE_PROXYENTRY;
	if($screen) {
		$screenButtonText['addButton'] = $screen->getVar('useaddupdate');
		$screenButtonText['addMultiButton'] = $screen->getVar('useaddmultiple');
		$screenButtonText['addProxyButton'] = $screen->getVar('useaddproxy');
		$screenButtonText['exportButton'] = $screen->getVar('useexport');
		$screenButtonText['importButton'] = $screen->getVar('useimport');
		$screenButtonText['notifButton'] = $screen->getVar('usenotifications');
		$screenButtonText['currentViewList'] = $screen->getVar('usecurrentviewlist');
		$screenButtonText['saveButton'] = count($screen->getVar('decolumns')) > 0 ? $screen->getVar('desavetext') : "";
		if($screen->getVar('listtemplate') == "") {
			// only display the following buttons if there is no customized list in effect
			$screenButtonText['changeColsButton'] = $screen->getVar('usechangecols');
			$screenButtonText['calcButton'] = $screen->getVar('usecalcs');
			$screenButtonText['advSearchButton'] = $screen->getVar('useadvsearch');
			$screenButtonText['exportCalcsButton'] = $screen->getVar('useexportcalcs');
			// only include clone and delete if the checkboxes are in effect (2 means do not use checkboxes)
			if($screen->getVar('usecheckboxes') != 2) {
				$screenButtonText['cloneButton'] = $screen->getVar('useclone');
				$screenButtonText['deleteButton'] = $screen->getVar('usedelete');
				$screenButtonText['selectAllButton'] = $screen->getVar('useselectall');
				$screenButtonText['clearSelectButton'] = $screen->getVar('useclearall');
			} else {
				$screenButtonText['cloneButton'] = "";
				$screenButtonText['deleteButton'] = "";
				$screenButtonText['selectAllButton'] = "";
				$screenButtonText['clearSelectButton'] = "";
			}
			// only include the reset, save, deleteview buttons if the current view list is in effect
			if($screen->getVar('usecurrentviewlist')) {
				$screenButtonText['resetViewButton'] = $screen->getVar('usereset');
				$screenButtonText['saveViewButton'] = $screen->getVar('usesave');
				$screenButtonText['deleteViewButton'] = $screen->getVar('usedeleteview');
			} else {
				$screenButtonText['resetViewButton'] = "";
				$screenButtonText['saveViewButton'] = "";
				$screenButtonText['deleteViewButton'] = "";
			}
		} else {
			$screenButtonText['changeColsButton'] = "";
			$screenButtonText['calcButton'] = "";
			$screenButtonText['advSearchButton'] = "";
			$screenButtonText['exportCalcsButton'] = "";
		}
	} 
	if($delete_other_reports = $gperm_handler->checkRight("delete_other_reports", $fid, $groups, $mid)) { $pubstart = 10000; }
	$onActionButtonCounter = 0;
	$atLeastOneActionButton = false;
	foreach($screenButtonText as $scrButton=>$scrText) {
		$buttonCodeArray[$scrButton] = formulize_screenLOEButton($scrButton, $scrText, $settings, $fid, $frid, $colids, $flatcols, $pubstart, $loadOnlyView, $calc_cols, $calc_calcs, $calc_blanks, $calc_grouping, $singleMulti[0]['singleentry'], $lastloaded, $currentview, $endstandard, $pickgroups, $viewoptions, $loadviewname);
		if($buttonCodeArray[$scrButton] AND $onActionButtonCounter < 14) { // first 14 items in the array should be the action buttons only
			$atLeastOneActionButton = true;
		}
		$onActionButtonCounter++;
	}
	if($hlist) { // if we're on the calc side, then the export button should be the export calcs one
		$buttonCodeArray['exportButton'] = $buttonCodeArray['exportCalcsButton'];
	}
	$buttonCodeArray['pageNavControls'] = $pageNav; // put this unique UI element into the buttonCodeArray for use elsewhere if necessary

	if($useDefaultInterface) {

		// if search is not used, generate the search boxes, check to see which ones are not referenced in either template, and then draw those in as hidden
		if(!$useSearch OR ($calc_cols AND !$hcalc)) {
			print "<div style=\"display: none;\"><table>"; // enclose in a table, since drawSearches puts in <tr><td> tags
			drawSearches($searches, $settings['columns'], $useCheckboxes, $useViewEntryLinks);
			print "</table></div>";
		}	
	
		print "<table cellpadding=10><tr><td style=\"vertical-align: top;\" width=100%>";
		
		print "<h1>" . trans($title) . "</h1>";
	
		if($loadview AND $lockcontrols) {
			print "<h3>" . $loadviewname . "</h3></td><td>";
			print "<input type=hidden name=currentview id=currentview value=\"$currentview\">\n<input type=hidden name=loadviewname id=loadviewname value=\"$loadviewname\">$submitButton";
		} else {
			print "</td>";
			if(!$settings['lockcontrols']) {
	
				// need to establish these here because they are used in conditions lower down
				$add_own_entry = $gperm_handler->checkRight("add_own_entry", $fid, $groups, $mid);
				$proxy = $gperm_handler->checkRight("add_proxy_entries", $fid, $groups, $mid);
				
				print "<td rowspan=3 style=\"vertical-align: bottom;\">";	      
		
				print "<table><tr><td style=\"vertical-align: bottom;\">";
		
				print "<p>$submitButton<br>";
				if($atLeastOneActionButton) {
					print "<b>" . _formulize_DE_ACTIONS . "</b>";
				}
				print "\n";
					
				if( $thisButtonCode = $buttonCodeArray['changeColsButton']) { print "<br>$thisButtonCode"; }
				if( $thisButtonCode = $buttonCodeArray['calcButton']) { print "<br>$thisButtonCode"; }
				if( $thisButtonCode = $buttonCodeArray['advSearchButton']) { print "<br>$thisButtonCode"; }
				if( $thisButtonCode = $buttonCodeArray['exportButton']) { print "<br>$thisButtonCode"; }
							
				if($import_data = $gperm_handler->checkRight("import_data", $fid, $groups, $mid) AND !$frid AND $thisButtonCode = $buttonCodeArray['importButton']) { // cannot import into a framework currently
					print "<br>$thisButtonCode";
				}
		
				print "</p></td><td style=\"vertical-align: bottom;\"><p style=\"text-align: center;\">";
		
				if($add_own_entry AND $singleMulti[0]['singleentry'] == "") {
					if( $thisButtonCode = $buttonCodeArray['cloneButton']) { print "$thisButtonCode<br>"; }
				}
				$del_own = $gperm_handler->checkRight("delete_own_entry", $fid, $groups, $mid);
				$del_others = $gperm_handler->checkRight("delete_other_entries", $fid, $groups, $mid);
				if(($del_own OR $del_others) AND !$settings['lockcontrols']) {
					if( $thisButtonCode = $buttonCodeArray['deleteButton']) { print "$thisButtonCode<br>"; }
				}
				if(($add_own_entry AND $singleMulti[0]['singleentry'] == "") OR (($del_own OR $del_others) AND !$settings['lockcontrols'])) {
					if( $thisButtonCode = $buttonCodeArray['selectAllButton']) { print "$thisButtonCode"; }
					if( $thisButtonCode = $buttonCodeArray['clearSelectButton']) { print "<br>$thisButtonCode<br>"; }
				}
	
				if( $thisButtonCode = $buttonCodeArray['notifButton']) { print "$thisButtonCode"; } 
				if( $thisButtonCode = $buttonCodeArray['resetViewButton']) { print "<br>$thisButtonCode"; }
				// there is a create reports permission, but we are currently allowing everyone to save their own views regardless of that permission.  The publishing permissions do kick in on the save popup.
				if( $thisButtonCode = $buttonCodeArray['saveViewButton']) { print "<br>$thisButtonCode"; }
				// you can always create and delete your own reports right now (delete_own_reports perm has no effect).  If can delete other reports, then set $pubstart to 10000 -- which is done above -- (ie: can delete published as well as your own, because the javascript will consider everything beyond the start of 'your saved views' to be saved instead of published (published be thought to never begin))
				if( $thisButtonCode = $buttonCodeArray['deleteViewButton']) { print "<br>$thisButtonCode"; }
				
				print "</p>";
				print "</td></tr></table></td></tr>\n";
			} else { // if lockcontrols set, then write in explanation...
				print "<td></td></tr></table>";
				print "<table><tr><td style=\"vertical-align: bottom;\">";
				print "<input type=hidden name=curviewid id=curviewid value=$curviewid>";
				print "<p>$submitButton<br>" . _formulize_DE_WARNLOCK . "</p>";
				print "</td></tr>";
			} // end of if controls are locked

			// cell for add entry buttons
			print "<tr><td style=\"vertical-align: top;\">\n";

			if(!$settings['lockcontrols']) {
				// added October 18 2006 -- moved add entry buttons to left side to emphasize them more
				print "<table><tr><td style=\"vertical-align: bottom;\"><p>\n";
	
				$addButton = $buttonCodeArray['addButton'];
				$addMultiButton = $buttonCodeArray['addMultiButton'];
				$addProxyButton = $buttonCodeArray['addProxyButton'];
			
				if($add_own_entry AND $singleMulti[0]['singleentry'] == "" AND ($addButton OR $addMultiButton)) {
					print "<b>" . _formulize_DE_FILLINFORM . "</b>\n";
					if( $addButton) { print "<br>$addButton"; } // this will include proxy box if necessary
					if( $addMultiButton) { print "<br>$addMultiButton"; }
				} elseif($add_own_entry AND $proxy AND ($addButton OR $addProxyButton)) { // this is a single entry form, so add in the update and proxy buttons if they have proxy, otherwise, just add in update button
					print "<b>" . _formulize_DE_FILLINFORM . "</b>\n";
					if( $addButton) { print "<br>$addButton"; }
					if( $addProxyButton) { print "<br>$addProxyButton"; }
				} elseif($add_own_entry AND $addButton) {
					print "<b>" . _formulize_DE_FILLINFORM . "</b>\n";
					if( $addButton) { print "<br>$addButton"; }
				} elseif($proxy AND $addProxyButton) {
					print "<b>" . _formulize_DE_FILLINFORM . "</b>\n";
					if( $addProxyButton) { print "<br>$addProxyButton"; }
				}
				print "<br><br></p></td></tr></table>\n";
			}
	
			print "</td></tr><tr><td style=\"vertical-align: bottom;\">";
	
			if ($currentViewList = $buttonCodeArray['currentViewList']) { print $currentViewList; }
	
		} // end of if there's a loadview or not
		
		// regardless of if a view is loaded and/or controls are locked, always print the page navigation controls
		if ($pageNavControls = $buttonCodeArray['pageNavControls']) { print $pageNavControls; }
		
		print "</td></tr></table>";
	} else {
		// IF THERE IS A CUSTOM TOP TEMPLATE IN EFFECT, DO SOMETHING COMPLETELY DIFFERENT
	
		if(!$screen->getVar('usecurrentviewlist') OR (!strstr($screen->getVar('toptemplate'), 'currentViewList') AND !strstr($screen->getVar('toptemplate'), 'currentViewList'))) { print "<input type=hidden name=currentview id=currentview value=\"$currentview\"></input>\n"; } // print it even if the text is blank, it will be a hidden value in this case
				
		// if search is not used, generate the search boxes and make them available in the template
		// also setup searches when calculations are in effect, or there's a custom list template
		// (essentially, whenever the search boxes would not be drawn in for whatever reason)
		if(!$useSearch OR ($calc_cols AND !$hcalc) OR $screen->getVar('listtemplate')) {
			$quickSearchBoxes = drawSearches($searches, $settings['columns'], $useCheckboxes, $useViewEntryLinks, 0, true); // true means we will receive back the code instead of having it output to the screen
			$hiddenQuickSearches = array();
			foreach($quickSearchBoxes as $handle=>$qscode) {
				if(strstr($screen->getVar('toptemplate'), 'quickSearch' . $handle) OR strstr($screen->getVar('bottomtemplate'), 'quickSearch' . $handle)) {
					$buttonCodeArray['quickSearch' . $handle] = $qscode; // set variables for use in the template
				} else {
					$hiddenQuickSearches[] = $qscode;
				}
			}
			if(count($hiddenQuickSearches) > 0) {			
				print "<div style=\"display: none;\"><table>"; // enclose in a table, since drawSearches puts in <tr><td> tags
				foreach($hiddenQuickSearches as $qscode) {
					print $qscode. "\n";
				}
				print "</table></div>";
			}
		}	
	
		formulize_screenLOETemplate($screen, "top", $buttonCodeArray, $settings, $messageText);
		$buttonCodeArray['submitButton'] = $submitButton; // send this back so that we can put it at the bottom of the page if necessary
		
	}
	

	print "<input type=hidden name=newcols id=newcols value=\"\">\n";
	print "<input type=hidden name=oldcols id=oldcols value='$flatcols'>\n";
	print "<input type=hidden name=ventry id=ventry value=\"\">\n";
	print "<input type=hidden name=delconfirmed id=delconfirmed value=\"\">\n";
	print "<input type=hidden name=cloneconfirmed id=cloneconfirmed value=\"\">\n";
	print "<input type=hidden name=xport id=xport value=\"\">\n";
	print "<input type=hidden name=xport_cust id=xport_cust value=\"\">\n";
	print "<input type=hidden name=loadreport id=loadreport value=\"\">\n";
	print "<input type=hidden name=lastloaded id=lastloaded value=\"$lastloaded\">\n";
	print "<input type=hidden name=saveviewname id=saveviewname value=\"\">\n";
	print "<input type=hidden name=saveviewoptions id=saveviewoptions value=\"\">\n";

	// setup HTML to receive custom button values -- javascript function sets these based on which button is clicked
	print "<input type=hidden name=caid id=caid value=\"\">\n";
	print "<input type=hidden name=caentries id=caentries value=\"\">\n";

	// hidden fields used by UI in the Entries section
	print "<input type=hidden name=sort id=sort value=\"$sort\">\n";
	print "<input type=hidden name=order id=order value=\"$order\">\n";

	print "<input type=hidden name=hlist id=hlist value=\"$hlist\">\n";
	print "<input type=hidden name=hcalc id=hcalc value=\"$hcalc\">\n";
	print "<input type=hidden name=lockcontrols id=lockcontrols value=\"$lockcontrols\">\n";
	print "<input type=hidden name=resetview id=resetview value=\"\">\n";

	// hidden fields used by calculations
	print "<input type=hidden name=calc_cols id=calc_cols value=\"$calc_cols\">\n";
	print "<input type=hidden name=calc_calcs id=calc_calcs value=\"$calc_calcs\">\n";
	print "<input type=hidden name=calc_blanks id=calc_blanks value=\"$calc_blanks\">\n";
	print "<input type=hidden name=calc_grouping id=calc_grouping value=\"$calc_grouping\">\n";

	// advanced search
	$asearch = str_replace("\"", "&quot;", $asearch);
	print "<input type=hidden name=asearch id=asearch value=\"" . stripslashes($asearch) . "\">\n";

	// advanced scope
	print "<input type=hidden name=advscope id=advscope value=\"\">\n";

	// delete view
	print "<input type=hidden name=delview id=delview value=\"\">\n";
	print "<input type=hidden name=delviewid id=delviewid value=\"$loadedview\">\n";

	// related to saving a new view
	print "<input type=hidden name=saveid id=saveid value=\"\">\n";
	print "<input type=hidden name=savename id=savename value=\"\">\n";
	print "<input type=hidden name=savegroups id=savegroups value=\"\">\n";
	print "<input type=hidden name=savelock id=savelock value=\"\">\n";
	print "<input type=hidden name=savescope id=savescope value=\"\">\n";

	interfaceJavascript($fid, $frid, $currentview, $useWorking); // must be called after form is drawn, so that the javascript which clears ventry can operate correctly (clearing is necessary to avoid displaying the form after clicking the Back button on the form and then clicking a button or doing an operation that causes a posting of the controls form).

	$returnArray = array();
	$returnArray[0] = $buttonCodeArray; // send this back so it's available in the bottom template if necessary.  MUST USE NUMERICAL KEYS FOR list TO WORK ON RECEIVING END.
	return $returnArray;

}

// THIS FUNCTION DRAWS IN THE RESULTS OF THE QUERY
function drawEntries($fid, $cols, $sort="", $order="", $searches="", $frid="", $scope, $standalone="", $currentURL, $gperm_handler, $uid, $mid, $groups, $settings, $member_handler, $screen, $data, $wq, $regeneratePageNumbers) { // , $loadview="") { // -- loadview removed from this function sept 24 2005

	global $xoopsDB;
	
	$useScrollBox = true;
	$useHeadings = true;
	$repeatHeaders = 5;
	$columnWidth = 0;
	$textWidth = 35;
	$useCheckboxes = 0;
	$useViewEntryLinks = 1;
	$useSearch = 1;
	$deColumns = array();
	$useSearchCalcMsgs = 1;
	$listTemplate = false;
	$inlineButtons = array();
	$hiddenColumns = array();
	$formulize_LOEPageSize = 10;
	if($screen) {
		$useScrollBox = $screen->getVar('usescrollbox');
		$useHeadings = $screen->getVar('useheadings');
		$repeatHeaders = $screen->getVar('repeatheaders');
		$columnWidth = $screen->getVar('columnwidth');
		$textWidth = $screen->getVar('textwidth');
		if($textWidth == 0) { $textWidth = 10000; }
		$useCheckboxes = $screen->getVar('usecheckboxes');
		$useViewEntryLinks = $screen->getVar('useviewentrylinks');
		$useSearch = $screen->getVar('usesearch');
		$hiddenColumns = $screen->getVar('hiddencolumns');
		$deColumns = $screen->getVar('decolumns');
		$useSearchCalcMsgs = $screen->getVar('usesearchcalcmsgs');
		$listTemplate = $screen->getVar('listtemplate');
		foreach($screen->getVar('customactions') as $caid=>$thisCustomAction) {
			if($thisCustomAction['appearinline'] == 1) {
				$inlineButtons[$caid] = $thisCustomAction;
			}
		}
		// process a clicked custom button if it was an inline button
		// Since this is done above now, we don't need this code here...?
		/*
		if(isset($_POST['caid'])) {
			if(is_numeric($_POST['caid']) AND isset($inlineButtons[$_POST['caid']])) {
				list($caCode, $caElements, $caActions, $caValues, $caMessageText, $caApplyTo) = processCustomButton($_POST['caid'], $inlineButtons[$_POST['caid']]); // just processing to get the info so we can process the click.  Actual output of this button happens lower down
				$messageText = processClickedCustomButton($caElements, $caValues, $caActions, $caMessageText, $caApplyTo);
				// output the message text to the screen if it's not used in the custom templates somewhere
				if($messageText AND !strstr($screen->getVar('toptemplate'), '\$messageText') AND !strstr($screen->getVar('bottomtemplate'), '\$messageText')) {
					print "<p><center><b>$messageText</b></center></p>\n";
				}
			}
		}
		*/
		$formulize_LOEPageSize = $screen->getVar('entriesperpage');
	}		
		
	// get the headers
	$headers = getHeaders($cols, $frid);
	
	$filename = "";
	if($settings['xport']) {
		$filename = prepExport($headers, $cols, $data, $settings['xport'], $settings['xport_cust'], $settings['title'], false, $fid, $groups);
		$linktext = $_POST['xport'] == "update" ? _formulize_DE_CLICKSAVE_TEMPLATE : _formulize_DE_CLICKSAVE;
		print "<center><p><a href='$filename' target=\"_blank\">$linktext</a></p></center>";
		print "<br>";
	}

	if($useScrollBox) {
		print "<style>\n";
	
		print ".scrollbox {\n";
		//print "	height: 530px;\n"; // don't scroll height anymore since we are paging results! (finally!!)
		print "	width: 775px;\n";
		print "	overflow: scroll;\n";
		print "}\n";
	
		print "</style>\n";
	
		print "<div class=scrollbox id=resbox>\n";
	}

	// perform calculations...
	// calc_cols is the columns requested (separated by / -- ele_id for each, so needs conversion to handle if framework in effect, also metadata is indicated with uid, proxyid, creation_date, mod_date)
	// calc_calcs is the calcs for each column, columns separated by / and calcs for a column separated by ,. possible calcs are sum, avg, min, max, count, per
	// calc_blanks is the blank setting for each calculation, setup the same way as the calcs, possible settings are all,  noblanks, onlyblanks
	// calc_grouping is the grouping option.  same format as calcs.  possible values are ele_ids or the uid, proxyid, creation_date and mod_date metadata terms

	// 1. extract data from four settings into arrays
	// 2. loop through the array and perform all the requested calculations
	
	if($settings['calc_cols'] AND !$settings['hcalc']) {
		$ccols = explode("/", $settings['calc_cols']);
		$ccalcs = explode("/", $settings['calc_calcs']);
		// need to add in proper handling of long calculation results, like grouping percent breakdowns that result in many, many rows.
		foreach($ccalcs as $onecalc) {
			$thesecalcs = explode(",", $onecalc);
			if(!is_array($thesecalcs)) { $thesecalcs[0] = ""; }
			$totalalcs = $totalcalcs + count($thesecalcs);
		}
		$cblanks = explode("/", $settings['calc_blanks']);
		$cgrouping = explode("/", $settings['calc_grouping']);
		$cresults = performCalcs($ccols, $ccalcs, $cblanks, $cgrouping, $data, $frid);
//		print "<p><input type=button style=\"width: 140px;\" name=cancelcalcs1 value='" . _formulize_DE_CANCELCALCS . "' onclick=\"javascript:cancelCalcs();\"></input></p>\n";
//		print "<div";
//		if($totalcalcs>4) { print " class=scrollbox"; }
//		print " id=calculations>
		$calc_cols = $settings['calc_cols'];
		$calc_calcs = $settings['calc_calcs'];
		$calc_blanks = $settings['calc_blanks'];
		$calc_grouping = $settings['calc_grouping'];

		print "<table class=outer><tr><th colspan=2>" . _formulize_DE_CALCHEAD . "</th></tr>\n";
		if(!$settings['lockcontrols'] AND ($useSearchCalcMsgs == 1 OR $useSearchCalcMsgs == 3)) { // AND !$loadview) { // -- loadview removed from this function sept 24 2005
			print "<tr><td class=head colspan=2><input type=button style=\"width: 140px;\" name=mod_calculations value='" . _formulize_DE_MODCALCS . "' onclick=\"javascript:showPop('" . XOOPS_URL . "/modules/formulize/include/pickcalcs.php?fid=$fid&frid=$frid&calc_cols=$calc_cols&calc_calcs=$calc_calcs&calc_blanks=$calc_blanks&calc_grouping=$calc_grouping');\"></input>&nbsp;&nbsp;<input type=button style=\"width: 140px;\" name=cancelcalcs value='" . _formulize_DE_CANCELCALCS . "' onclick=\"javascript:cancelCalcs();\"></input>&nbsp;&nbsp<input type=button style=\"width: 140px;\" name=showlist value='" . _formulize_DE_SHOWLIST . "' onclick=\"javascript:showList();\"></input></td></tr>";
		}
		$exportFilename = $settings['xport'] == "calcs" ? $filename : "";
		printResults($cresults[0], $cresults[1], $cresults[2], $frid, $exportFilename, $settings['title']); // 0 is the masterresults, 1 is the blanksettings, 2 is grouping settings -- exportFilename is the name of the file that we need to create and into which we need to dump a copy of the calcs

		print "</table>\n";

		// NOT NECESSARY NOW BECAUSE WE DRAW IN THE SEARCH BOXES IN THE TOP AREA IF WE'RE VIEWING CALCULATIONS (?)
		// draw in the search boxes, but hidden, just to preserve the values of the quicksearches.
		//print "<div style=\"display: none;\"><table>"; // enclose in a table, since drawSearches puts in <tr><td> tags
		//drawSearches($searches, $cols, $useCheckboxes, $useViewEntryLinks);
		//print "</table></div>";

	}

	// MASTER HIDELIST CONDITIONAL...
	if(!$settings['hlist'] AND !$listTemplate) {

		print "<table class=outer>";
	
		$count_colspan = count($cols)+1;
		if($useViewEntryLinks OR $useCheckboxes != 2) {
			$count_colspan_calcs = $count_colspan;
		} else {
			$count_colspan_calcs = $count_colspan - 1;
		}
		$count_colspan_calcs = $count_colspan_calcs + count($inlineButtons); // add to the column count for each inline custom button
		if(!$screen) { print "<tr><th colspan=$count_colspan_calcs>" . _formulize_DE_DATAHEADING . "</th></tr>\n"; }
	
		if($settings['calc_cols'] AND !$settings['lockcontrols'] AND ($useSearchCalcMsgs == 1 OR $useSearchCalcMsgs == 3)) { // AND !$loadview) { // -- loadview removed from this function sept 24 2005
			$calc_cols = $settings['calc_cols'];
			$calc_calcs = $settings['calc_calcs'];
			$calc_blanks = $settings['calc_blanks'];
			$calc_grouping = $settings['calc_grouping'];
			print "<tr><td class=head colspan=$count_colspan_calcs><input type=button style=\"width: 140px;\" name=mod_calculations value='" . _formulize_DE_MODCALCS . "' onclick=\"javascript:showPop('" . XOOPS_URL . "/modules/formulize/include/pickcalcs.php?fid=$fid&frid=$frid&calc_cols=$calc_cols&calc_calcs=$calc_calcs&calc_blanks=$calc_blanks&calc_grouping=$calc_grouping');\"></input>&nbsp;&nbsp;<input type=button style=\"width: 140px;\" name=cancelcalcs value='" . _formulize_DE_CANCELCALCS . "' onclick=\"javascript:cancelCalcs();\"></input>&nbsp;&nbsp;<input type=button style=\"width: 140px;\" name=hidelist value='" . _formulize_DE_HIDELIST . "' onclick=\"javascript:hideList();\"></input></td></tr>";
		}
	
		// draw advanced search notification
		if($settings['as_0'] AND ($useSearchCalcMsgs == 1 OR $useSearchCalcMsgs == 2)) {
			$writable_q = writableQuery($wq);
			$minus1colspan = $count_colspan-1+count($inlineButtons);
			if(!$asearch_parse_error) {
				print "<tr>";
				if($useViewEntryLinks OR $useCheckboxes != 2) { // only include this column if necessary
					print "<td class=head></td>";
				}
				print "<td colspan=$minus1colspan class=head>" . _formulize_DE_ADVSEARCH . ": $writable_q";
			} else {
				print "<tr>";
				if($useViewEntryLinks OR $useCheckboxes != 2) {
					print "<td class=head></td>";
				}
				print "<td colspan=$minus1colspan class=head><span style=\"font-weight: normal;\">" . _formulize_DE_ADVSEARCH_ERROR . "</span>";
			}
			if(!$settings['lockcontrols']) { // AND !$loadview) { // -- loadview removed from this function sept 24 2005
				print "<br><input type=button style=\"width: 140px;\" name=advsearch value='" . _formulize_DE_MOD_ADVSEARCH . "' onclick=\"javascript:showPop('" . XOOPS_URL . "/modules/formulize/include/advsearch.php?fid=$fid&frid=$frid";
				foreach($settings as $k=>$v) {
					if(substr($k, 0, 3) == "as_") {
						$v = str_replace("'", "&#39;", $v);
						$v = stripslashes($v);
						print "&$k=" . urlencode($v);
					}
				}
			print "');\"></input>&nbsp;&nbsp;<input type=button style=\"width: 140px;\" name=cancelasearch value='" . _formulize_DE_CANCELASEARCH . "' onclick=\"javascript:killSearch();\"></input>";
			}
			print "</td></tr>\n";
		}
	
	
		if($useHeadings) { drawHeaders($headers, $cols, $sort, $order, $useCheckboxes, $useViewEntryLinks, count($inlineButtons), $settings['lockcontrols']); }
		if($useSearch) {
			drawSearches($searches, $cols, $useCheckboxes, $useViewEntryLinks, count($inlineButtons));
		} 
	
		// get form handles in use
		$mainFormHandle = key($data[key($data)]);
	
		if(count($data) == 0) { // kill an empty dataset so there's no rows drawn
			unset($data);
		} 
	
		$headcounter = 0;
		$blankentries = 0;
		$GLOBALS['formulize_displayElement_LOE_Used'] = false;
		$formulize_LOEPageStart = (isset($_POST['formulize_LOEPageStart']) AND !$regeneratePageNumbers) ? intval($_POST['formulize_LOEPageStart']) : 0;
		$actualPageSize = $formulize_LOEPageSize ? $formulize_LOEPageStart + $formulize_LOEPageSize : count($data);
		if(isset($data)) {
			for($entryCounter=$formulize_LOEPageStart;$entryCounter<$actualPageSize;$entryCounter++) {
				$entry = $data[$entryCounter];
				$id=$entryCounter;
						
				// check to make sure this isn't an unset entry (ie: one that was blanked by the extraction layer just prior to sending back results
				// Since the extraction layer is unsetting entries to blank them, this condition should never be met?
				// If this condition is ever met, it may very well screw up the paging of results!
				// NOTE: this condition is met on the last page of a paged set of results, unless the last page as exactly the same number of entries on it as the limit of entries per page
				if($entry != "") { 
		
					if($headcounter == $repeatHeaders AND $repeatHeaders > 0) { 
						if($useHeadings) { drawHeaders($headers, $cols, $sort, $order, $useCheckboxes, $useViewEntryLinks, count($inlineButtons), $settings['lockcontrols']); }
						$headcounter = 0;
					}
					$headcounter++;		
			
					print "<tr>\n";
					if($class=="even") {
						$class="odd";
					} else {
						$class="even";
					}
					unset($linkids);
					$linkids = internalRecordIds($entry, $mainFormHandle);
					
					// commented below is an attempt to make metadata appear in tooltip boxes, but formatting is not available and the box is of a fixed width and "dotdotdots" itself -- member_handler not currently used by drawEntries so long as this is commented (and it is not added elsewhere)
					//$metaData = getMetaData($linkids[0], $member_handler);
					//$metaToPrint = "<br>" . _formulize_FD_CREATED . $metaData['created_by'] . " " . _formulize_TEMP_ON . " " . $metaData['created'] . "<br>" . _formulize_FD_MODIFIED . $metaData['last_update_by'] . " " . _formulize_TEMP_ON . " " . $metaData['last_update'];
					$metaToPrint = "";
					// draw in the margin column where the links and metadata goes
					if($useViewEntryLinks OR $useCheckboxes != 2) {
						print "<td class=head>\n";
					}
			
					if(!$settings['lockcontrols']) { //  AND !$loadview) { // -- loadview removed from this function sept 24 2005
						if($useViewEntryLinks) {
							print "<p><center><a href='" . $currentURL;
							if(strstr($currentURL, "?")) { // if params are already part of the URL...
								print "&";
							} else {
								print "?";
							}
							print "ve=" . $linkids[0] . "' onclick=\"javascript:goDetails('" . $linkids[0] . "');return false;\"><img src='" . XOOPS_URL . "/modules/formulize/images/detail.gif' border=0 alt=\"" . _formulize_DE_VIEWDETAILS . "$metaToPrint\" title=\"" . _formulize_DE_VIEWDETAILS . "$metaToPrint\"></a>";
						}
						if($useCheckboxes != 2) { // two means none
							// put in the delete checkboxes -- check for perms delete_own_entry, delete_other_entries
							$owner = getEntryOwner($linkids[0]);
							// check to see if we should draw in the delete checkbox or not
							if(($owner == $uid AND $gperm_handler->checkRight("delete_own_entry", $fid, $groups, $mid)) OR ($owner != $uid AND $gperm_handler->checkRight("delete_other_entries", $fid, $groups, $mid)) OR $useCheckboxes == 1) { // 1 means all
								if($useViewEntryLinks) {
									print "<br>";
								} else {
									print "<p><center>";
								}
								print "<input type=checkbox title='" . _formulize_DE_DELBOXDESC . "' name='delete_" . $linkids[0] . "' id='delete_" . $linkids[0] . "' value='delete_" . $linkids[0] . "'>";
							}
						}
						if($useViewEntryLinks OR $useCheckboxes != 2) { // at least one of the above was used
							print "</center></p>\n";
						}
					} // end of IF NO LOCKCONTROLS
					if($useViewEntryLinks OR $useCheckboxes != 2) {
						print "</td>\n";
					}	
			
					$column_counter = 0;
					
					if($columnWidth) {
						$columnWidthParam = "style=\"width: $columnWidth" . "px\"";
					} else {
						$columnWidthParam = "";
					}
			
					for($i=0;$i<count($cols);$i++) {
						$col = $cols[$i];
						$colid = $settings['columnids'][$i];
					
						print "<td $columnWidthParam class=$class>\n";
						if($col == "uid") {
							$value = "<a href=\"" . XOOPS_URL . "/userinfo.php?uid=" . display($entry, "uid") . "\" target=_blank>" . displayMeta($entry, "uid-name") . "</a>";
						} elseif($col=="proxyid") {
							$value = "<a href=\"" . XOOPS_URL . "/userinfo.php?uid=" . display($entry, "proxyid") . "\" target=_blank>" . displayMeta($entry, "proxyid-name") . "</a>";
						} else {
							$value = display($entry, $col);
						}
						if(in_array($colid, $deColumns)) { // if we're supposed to display this column as an element...
							include_once XOOPS_ROOT_PATH . "/modules/formulize/include/elementdisplay.php";
							if($frid) { // need to work out which form this column belongs to, and use that form's entry ID.  Need to loop through the entry to find all possible internal IDs, since a subform situation would lead to multiple values appearing in a single cell, so multiple displayElement calls would be made each with their own internal ID.
								foreach($entry as $entryFormHandle=>$entryFormData) {
									foreach($entryFormData as $internalID=>$entryElements) {
										$deThisIntId = false;
										foreach($entryElements as $entryHandle=>$values) {
											if($entryHandle == $col) { // we found the element that we're trying to display
												if($deThisIntId) { print "\n<br />\n"; } // could be a subform so we'd display multiple values
												displayElement("", $colid, $internalID);
												$deThisIntId = true;
											}
										}
									}
								}
							} else { // display based on the mainform entry id
								displayElement("", $colid, $linkids[0]); // works for mainform only!  To work on elements from a framework, we need to figure out the form the element is from, and the entry ID in that form
							}
							$GLOBALS['formulize_displayElement_LOE_Used'] = true;
						} elseif(is_array($value)) {
							$start = 1;
							foreach($value as $v) {
								if($start) {
									print checkForLink($v, $col, $frid, $textWidth); //printSmart(trans($v));
									$start = 0;
								} else {
									print ",<br>\n";
									print checkForLink($v, $col, $frid, $textWidth); // printSmart(trans($v));
								}
							}
						} elseif($col != "uid" AND $col!= "proxyid") {
							print checkForLink($value, $col, $frid, $textWidth); // printSmart(trans($value));
						} else { // don't use printsmart for the special uid/proxyid cells
							print $value;
						}
						// print out a hidden element if necessary
						if(in_array($colid, $hiddenColumns)) {
							print "\n<input type=\"hidden\" name=\"hiddencolumn_".$linkids[0]."_$col\" value=\"" . htmlspecialchars(display($entry, $col)) . "\"></input>\n";
						}
						
						
						print "</td>\n";
						$column_counter++;
					}
					
					// handle inline custom buttons
					
					foreach($inlineButtons as $caid=>$thisCustomAction) {
						list($caCode) = processCustomButton($caid, $thisCustomAction, $linkids[0]); // only bother with the code, since we already processed any clicked button above
						print "<td $columnWidthParam class=$class>\n";
						print "<center>$caCode</center>\n";
						print "</td>\n";
					}
					
					print "</tr>\n";
				
				} else { // this is a blank entry
					$blankentries++;
				} // end of not "" check
			
			} // end of for loop that draws all data
		} // end of if there is any data to draw
	
		// if(count($data)>20 AND $useHeadings) { drawHeaders($headers, $cols, $sort, $order, $useCheckboxes, $useViewEntryLinks, count($inlineButtons), $settings['lockcontrols']); }
	
		print "</table>";

	} elseif($listTemplate AND !$settings['hlist']) {

		// USING A CUSTOM LIST TEMPLATE SO DO EVERYTHING DIFFERENTLY
		// print str_replace("\n", "<br />", $listTemplate); // debug code
		$mainFormHandle = key($data[key($data)]);
		foreach($data as $entry) {
			$ids = internalRecordIds($entry, $mainFormHandle);
			foreach($inlineButtons as $caid=>$thisCustomAction) {
				list($caCode) = processCustomButton($caid, $thisCustomAction, $ids[0]); // only bother with the code, since we already processed any clicked button above
				${$thisCustomAction['handle']} = $caCode; // assign the button code that was returned
			}
			ob_start();
			eval(html_entity_decode($listTemplate));
			$evalResult = ob_get_clean();
			if($evalResult != "") {
				print $evalResult;
			} else {
				print "<p>" . _AM_FORMULIZE_SCREEN_LOE_TEMPLATE_ERROR . "</p>";
				break;
			}
		}

	}// END OF MASTER HIDELIST CONDITIONAL
	if(!$data OR count($data) == $blankentries) { // if no data was returned, or the dataset was empty...
		print "<p><b>" . _formulize_DE_NODATAFOUND . "</b></p>\n";
	}	
	
	if($useScrollBox) {
		print "</div>";
	}
}

// this function draws in the search box row
function drawSearches($searches, $cols, $useBoxes, $useLinks, $numberOfButtons, $returnOnly=false) {
	$quickSearchBoxes = array();
	if(!$returnOnly) { print "<tr>"; }
	if($useBoxes != 2 OR $useLinks) {
		if(!$returnOnly) { print "<td class=head>&nbsp;</td>\n"; }
	}
	for($i=0;$i<count($cols);$i++) {
		if(!$returnOnly) { print "<td class=head>\n"; }
		$search_text = str_replace("\"", "&quot;", $searches[$cols[$i]]);
		$boxid = "";
		$clear_help_javascript = "";
		if(count($searches) == 0 AND !$returnOnly) {
			if($i==0) { 
				$search_text = _formulize_DE_SEARCH_HELP; 
				$boxid = "id=firstbox";
			}
			$clear_help_javascript = "onfocus=\"javascript:clearSearchHelp(this.form, '" . _formulize_DE_SEARCH_HELP . "');\"";
		}
		$quickSearchBoxes[$cols[$i]] = "<input type=text $boxid name='search_" . $cols[$i] . "' value=\"" . stripslashes($search_text) . "\" $clear_help_javascript onchange=\"javascript:window.document.controls.ventry.value = '';\"></input>\n";
		if(!$returnOnly) {
			print $quickSearchBoxes[$cols[$i]];
			print "</td>\n";
		}
	}
	if(!$returnOnly) {
		for($i=0;$i<$numberOfButtons;$i++) {
			print "<td class=head>&nbsp;</td>\n";
		}
		print "</tr>\n";
	}
	return $quickSearchBoxes;
}

// this function writes in the headers for the columns in the results box
function drawHeaders($headers, $cols, $sort, $order, $useBoxes=null, $useLinks=null, $numberOfButtons) { //, $lockcontrols) {

	print "<tr>";
	if($useBoxes != 2 OR $useLinks) {
		print "<td class=head>&nbsp;</td>\n";
	}
	for($i=0;$i<count($headers);$i++) {
       	print "<td class=head>\n";
		if($cols[$i] == $sort) {
			if($order == "SORT_DESC") {
				$imagename = "desc.gif";
			} else {
				$imagename = "asc.gif";
			}
			print "<img src='" . XOOPS_URL . "/modules/formulize/images/$imagename' align=left>";
		}
//		if(!$lockcontrols) {
			print "<a href=\"\" alt=\"" . _formulize_DE_SORTTHISCOL . "\" title=\"" . _formulize_DE_SORTTHISCOL . "\" onclick=\"javascript:sort_data('" . $cols[$i] . "');return false;\">";
//		}
		print printSmart(trans($headers[$i]));
//		if(!$lockcontrols) {
			print "</a>\n";
//		}
	     	print "</td>\n";
	}
	for($i=0;$i<$numberOfButtons;$i++) {
		print "<td class=head>&nbsp;</td>\n";
	}
	print "</tr>\n";
}


// this function takes handles and returns the ids formatted for sending to change columns
// assume handles are unique within a framework (which they are supposed to be!)
function convertHandles($handles, $frid) {
	global $xoopsDB;
	if(!is_array($handles)) { 
		$temp = $handles;
		unset($handles);
		$handles[0] = $temp;
	}
	foreach($handles as $handle) {
		if($handle == "uid" OR $handle=="proxyid" OR $handle=="creation_date" OR $handle=="mod_date" OR $handle=="creator_email") {
			$ids[] = $handle;
		} else {
			$id = q("SELECT fe_element_id FROM " . $xoopsDB->prefix("formulize_framework_elements") . " WHERE fe_frame_id='$frid' AND fe_handle='$handle'");
			$ids[] = $id[0]['fe_element_id'];
		}
	}
	return $ids;
}

// this function takes ids and converts them to handles
function convertIds($ids, $frid) {
	global $xoopsDB;
	if(!is_array($ids)) { 
		$temp = $ids;
		unset($ids);
		$ids[0] = $temp;
	}
	foreach($ids as $id) {
		if($id == "uid" OR $id=="proxyid" OR $id=="creation_date" OR $id=="mod_date" OR $id=="creator_email") {
			$handles[] = $id;
		} else {
			$handle = q("SELECT fe_handle FROM " . $xoopsDB->prefix("formulize_framework_elements") . " WHERE fe_frame_id='$frid' AND fe_element_id='$id'");
			$handles[] = $handle[0]['fe_handle'];
		}
	}
	return $handles;
}
 


// this function returns the ele_ids of form elements to show, or the handles of the form elements to show for a framework
function getDefaultCols($fid, $frid="") {
	global $xoopsDB, $xoopsUser;

	if($frid) { // expand the headerlist to include the other forms
		$fids[0] = $fid;
		$check_results = checkForLinks($frid, $fids, $fid, "", "", "", "", "", "", "0");
		$fids = $check_results['fids'];
		$sub_fids = $check_results['sub_fids'];
		$gperm_handler = &xoops_gethandler('groupperm');
		$groups = $xoopsUser ? $xoopsUser->getGroups() : XOOPS_GROUP_ANONYMOUS;
		$uid = $xoopsUser ? $xoopsUser->getVar('uid') : "0";
		$mid = getFormulizeModId();
		foreach($fids as $this_fid) {
			if(security_check($this_fid, "", $uid, "", $groups, $mid, $gperm_handler)) {
				$ele_ids[$this_fid] = getHeaderList($this_fid, true);
				//$ele_ids[$this_fid] = convertHeadersToIds($headers, $this_fid); // was taking $headers formerly generated from prev line
			}
		}
		foreach($sub_fids as $this_fid) {
			if(security_check($this_fid, "", $uid, "", $groups, $mid, $gperm_handler)) {
				$ele_ids[$this_fid] = getHeaderList($this_fid, true);
				//$ele_ids[$this_fid] = convertHeadersToIds($headers, $this_fid); // was taking $headers formerly generated from prev line
			}
		}

		array_unique($ele_ids);
		if($frid) { // get the handles
	  		include_once XOOPS_ROOT_PATH . "/modules/formulize/include/extract.php";
			foreach($ele_ids as $this_fid=>$ids) {
				foreach($ids as $id) {
					$handles[] = handleFromId($id, $this_fid, $frid);
				}
			}
			return $handles;
		}
	} else {
		$ele_ids = getHeaderList($fid, true);
		//$ele_ids = convertHeadersToIds($headers, $fid); // was taking $headers formerly generated from prev line
		return $ele_ids;
	}



} 



//THIS FUNCTION PERFORMS THE REQUESTED CALCULATIONS, AND RETURNS AN html FORMATTED CHUNK FOR DISPLAY ON THE SCREEN
function performCalcs($cols, $calcs, $blanks, $grouping, $data, $frid)  {
	
	// figure out all the handles that we need to grab for calculating
	// plus the calcs requested for each, plus the blank options plus the grouping handle
	for($i=0;$i<count($cols);$i++) {
		$handles[$i] = calcHandle($cols[$i], $frid);
		unset($ex_calcs);
		unset($ex_blanks);
		unset($ex_grouping);
		if(strstr($calcs[$i], ",")) {
			$ex_calcs = explode(",", $calcs[$i]);
		} else {
			$ex_calcs[0] = $calcs[$i];
		}
		if(strstr($blanks[$i], ",")) {
			$ex_blanks = explode(",", $blanks[$i]);
		} else {
			$ex_blanks[0] = $blanks[$i];
		}
		if(strstr($grouping[$i], ",")) {
			$ex_grouping = explode(",", $grouping[$i]);
		} else {
			$ex_grouping[0] = $grouping[$i];
		}
		for($z=0;$z<count($ex_calcs);$z++) {
			$c[$i][$z] = $ex_calcs[$z];
			$b[$i][$z] = $ex_blanks[$z];
			$g[$i][$z] = calcHandle($ex_grouping[$z], $frid);
		}
	}

/*	print_r($handles);
	print "<br>";
	print_r($c);
	print "<br>";
	print_r($b);
	print "<br>";
	print_r($g);
*/
	// loop through all the data.  For each entry, store it as necessary for every calculation that needs to happen.

	foreach($data as $entry) {
		for($i=0;$i<count($handles);$i++)  {
			$tempvalue = display($entry, $handles[$i]);
			$thisvalue = convertUids($tempvalue, $handles[$i]); // also converts blanks to [blank]
			for($z=0;$z<count($c[$i]);$z++) {
				$blankSettings[$handles[$i]][$c[$i][$z]] = $b[$i][$z];				
				$groupingSettings[$handles[$i]][$c[$i][$z]] = $g[$i][$z];
				if(($b[$i][$z] == "onlyblanks" AND ($tempvalue == "" OR $tempvalue == "0")) OR ($b[$i][$z] == "noblanks" AND ($tempvalue != "" AND $tempvalue != "0")) OR $b[$i][$z] == "all") {
					if($g[$i][$z] == "none" OR $g[$i][$z] == "") { 
						if(is_array($thisvalue)) {
							foreach($thisvalue as $onevalue) {
								$masterCalcs[$handles[$i]][$c[$i][$z]][0][] = $onevalue;
							}
						} else {
							$masterCalcs[$handles[$i]][$c[$i][$z]][0][] = $thisvalue;
						}
						$groupDataCount[$handles[$i]][$c[$i][$z]][0]++; 	// count the master $data array so we have an alternate divisor to use for percentage breakdown calculations if necessary -- added August 21 2006
					} else {
						$thisgroup = display($entry, $g[$i][$z]);
						$thisgroup = convertUids($thisgroup, $g[$i][$z]);
						if(is_array($thisgroup)) {
							foreach($thisgroup as $onegroup) {
								if(is_array($thisvalue)) {
									foreach($thisvalue as $onevalue) {
										$masterCalcs[$handles[$i]][$c[$i][$z]][$onegroup][] = $onevalue;
									}
								} else {
									$masterCalcs[$handles[$i]][$c[$i][$z]][$onegroup][] = $thisvalue;
								}
								$groupDataCount[$handles[$i]][$c[$i][$z]][$onegroup]++; 	// count the master $data array so we have an alternate divisor to use for percentage breakdown calculations if necessary -- added August 21 2006
							}	
						} else {
							if(is_array($thisvalue)) {
								foreach($thisvalue as $onevalue) {
									$masterCalcs[$handles[$i]][$c[$i][$z]][$thisgroup][] = $onevalue;
								}
							} else {
								$masterCalcs[$handles[$i]][$c[$i][$z]][$thisgroup][] = $thisvalue;
							}
							$groupDataCount[$handles[$i]][$c[$i][$z]][$thisgroup]++; 	// count the master $data array so we have an alternate divisor to use for percentage breakdown calculations if necessary -- added August 21 2006
						}
					}
				}
			}
		}
	}

	unset($data); // clears memory?
	unset($cols);
	unset($calcs);
	unset($blanks);
	unset($grouping);
	// loop through the masterCalc array and perform each required calculation
	// masterCalcs array is basically in this format:  array[handle/question in form][calculation requested on handle][grouping option]
	// you can have several groups for a question (one key for each grouped option)

	foreach($masterCalcs as $handle=>$thesecalcs) {
		foreach($thesecalcs as $thiscalc=>$thesegroups) {
			foreach($thesegroups as $thisgroup=>$values) {
				//print_r($values);
				switch($thiscalc) {
					case "sum":
						$total = array_sum($values);
						$masterResults[$handle][$thiscalc][$thisgroup] = _formulize_DE_CALC_SUM . ": $total";
						break;
					case "avg":
						$total = array_sum($values);
						$count = count($values);
						$mean = round($total/$count, 2);
						sort($values, SORT_NUMERIC);
						if($count%2 == 0 AND $count>1) {
							$median = $values[($count/2)] . ", " . $values[($count/2)-1];						
						} elseif($count>2) {
							$median = $values[($count/2)-0.5];						
						} else {
							$median = $values[($count)-1];						
						}
						//print_r($values);
						$breakdown = array_count_values($values);
						//print_r($breakdown);
						arsort($breakdown);
						$mode_keys = array_keys($breakdown);
						$mode = "" . $mode_keys[0] . "";
						$index = 0;
						foreach($breakdown as $val) {
							if(!$index) { 
								$index++;
								$prevval = $val;
							} else {
								if($prevval == $val) {
									$mode .= ", " . $mode_keys[$index];
									$index++;
									$prevval = $val;
								} else {
									break;
								}
							}
						}
						$masterResults[$handle][$thiscalc][$thisgroup] = _formulize_DE_CALC_MEAN . ": $mean<br>" . _formulize_DE_CALC_MEDIAN . ": $median<br>" . _formulize_DE_CALC_MODE . ": $mode";
						break;
					case "min":
						sort($values, SORT_NUMERIC);
						$min = $values[0];						
						$masterResults[$handle][$thiscalc][$thisgroup] = _formulize_DE_CALC_MIN . ": $min";
						break;
					case "max":
						$count = count($values);
						sort($values, SORT_NUMERIC);
						$max = $values[$count-1];						
						$masterResults[$handle][$thiscalc][$thisgroup] = _formulize_DE_CALC_MAX . ": $max";										
						break;
					case "count":
						$count = count($values);
						$breakdown = array_count_values($values);
						$count_unique = count(array_keys($breakdown));
						$masterResults[$handle][$thiscalc][$thisgroup] = _formulize_DE_CALC_NUMENTRIES . ": $count<br>" . _formulize_DE_CALC_NUMUNIQUE . ": $count_unique";
						break;
					case "per":
						$datacount = $groupDataCount[$handle][$thiscalc][$thisgroup];
						$count = count($values);
						$breakdown = array_count_values($values);
						arsort($breakdown);
						if($count == $datacount) {
							$typeout = "<table cellpadding=3>\n<tr><td style=\"vertical-align: top;\"><u>" . _formulize_DE_PER_ITEM . "</u></td><td style=\"vertical-align: top;\"><u>" . _formulize_DE_PER_COUNT . "</u></td><td style=\"vertical-align: top;\"><u>" . _formulize_DE_PER_PERCENT . "</u></td></tr>\n";
						} else {
							$typeout = "<table cellpadding=3>\n<tr><td style=\"vertical-align: top;\"><u>" . _formulize_DE_PER_ITEM . "</u></td><td style=\"vertical-align: top;\"><u>" . _formulize_DE_PER_COUNT . "</u></td><td style=\"vertical-align: top;\"><u>" . _formulize_DE_PER_PERCENTRESPONSES . "</u></td><td style=\"vertical-align: top;\"><u>" . _formulize_DE_PER_PERCENTENTRIES . "</u></td></tr>\n";
						}
						$icountTotal = 0;
						foreach($breakdown as $item=>$icount) {
							$icountTotal += $icount;
							$percentage = round(($icount/$count)*100, 2);
							if($count == $datacount) {
								$typeout .= "<tr><td style=\"vertical-align: top;\">$item</td><td style=\"vertical-align: top;\">$icount</td><td style=\"vertical-align: top;\">$percentage%</td></tr>\n";		
							} else {
								$percentageData = round(($icount/$datacount)*100, 2);
								$typeout .= "<tr><td style=\"vertical-align: top;\">$item</td><td style=\"vertical-align: top;\">$icount</td><td style=\"vertical-align: top;\">$percentage%</td><td style=\"vertical-align: top;\">$percentageData%</td></tr>\n";		
							}
						}
						// add total line -- added May 31 2006 -- jwe
						if($count == $datacount) {
							$typeout .="<tr><td style=\"vertical-align: top;\"><hr>" . _formulize_DE_PER_TOTAL . "</td><td style=\"vertical-align: top;\"><hr>$icountTotal</td><td style=\"vertical-align: top;\"><hr>100%</td></tr>\n";
						} else {
							$typeout .="<tr><td style=\"vertical-align: top;\"><hr>" . _formulize_DE_PER_TOTAL . "</td><td style=\"vertical-align: top;\"><hr>$icountTotal " . _formulize_DE_PER_TOTALRESPONSES . "<br>$datacount " . _formulize_DE_PER_TOTALENTRIES . "</td><td style=\"vertical-align: top;\"><hr>100%</td><td style=\"vertical-align: top;\"><hr>" . round($icountTotal/$datacount, 2) . " " . _formulize_DE_PER_RESPONSESPERENTRY . "</td></tr>\n";
						}
						$typeout .= "</table>";
						$masterResults[$handle][$thiscalc][$thisgroup] = $typeout;
						break;
				}
			}
		}
	}
	$to_return[0] = $masterResults;
	$to_return[1] = $blankSettings;
	$to_return[2] = $groupingSettings;
	return $to_return;
}


//THIS FUNCTION TAKES A MASTER RESULT SET AND DRAWS IT ON THE SCREEN
function printResults($masterResults, $blankSettings, $groupingSettings, $frid, $filename="", $title="") {

	$output = "";
     	foreach($masterResults as $handle=>$calcs) {
		$output .= "<tr><td class=head colspan=2>\n";
		$output .= printSmart(trans(getCalcHandleText($handle, $frid)), 100);
		$output .= "\n</td></tr>\n";
     		foreach($calcs as $calc=>$groups) {
			$countGroups = count($groups);
     			$output .= "<tr><td class=even rowspan=$countGroups>\n";
			switch($calc) {
				case "sum":
					$calc_name = _formulize_DE_CALC_SUM;
					break;
				case "avg":
					$calc_name = _formulize_DE_CALC_AVG;
					break;
				case "min":
					$calc_name = _formulize_DE_CALC_MIN;
					break;
				case "max":
					$calc_name = _formulize_DE_CALC_MAX;
					break;
				case "count":
					$calc_name = _formulize_DE_CALC_COUNT;
					break;
				case "per":
					$calc_name = _formulize_DE_CALC_PER;
					break;
			}
			$output .= "<p><b>$calc_name</b></p>\n";
			switch($blankSettings[$handle][$calc]) {
				case "all":
					$bsetting = _formulize_DE_INCLBLANKS;
					break;
				case "noblanks":
					$bsetting = _formulize_DE_EXCLBLANKS;
					break;
				case "onlyblanks":
					$bsetting = _formulize_DE_INCLONLYBLANKS;
					break;
			}
			$output .= "<p>$bsetting</p>\n</td>\n";
			$start = 1;
     			foreach($groups as $group=>$result) {
				if(!$start) { $output .= "<tr>\n"; }
				$start=0;
				$output .= "<td class=odd>\n";
				if(count($groups)>1) {
					$output .= "<p><b>" . printSmart(trans(getCalcHandleText($groupingSettings[$handle][$calc], $frid))) . ": " . printSmart($group) . "</b></p>\n";
				} 
     				$output .= "<p>$result</p>\n</td></tr>\n";
     			}
     		}
     	}
	print $output;
	// addition of calculation download, August 22 2006
	if($filename) {
		// get the current CSS values for head, even and odd
		global $xoopsConfig;
		$head = "";
		$odd = "";
		$even = "";
		if(file_exists(XOOPS_ROOT_PATH . "/themes/" . $xoopsConfig['theme_set'] . "/style.css")) {
			include XOOPS_ROOT_PATH . "/modules/formulize/class/class.csstidy.php";
			$css = new csstidy();
			$css->set_cfg('merge_selectors',0);
			$css->parse_from_url(XOOPS_URL . "/themes/" . $xoopsConfig['theme_set'] . "/style.css");
			$parsed_css = $css->css;
			// parsed_css seems to have only one key when looking at the default template
			foreach($parsed_css as $thiscss) {
				$head = $thiscss['.head']['background-color'];
				$even = $thiscss['.even']['background-color'];
				$odd = $thiscss['.odd']['background-color'];
			}
		} 
		unset($css);
		// if we couldn't find any values, use these:
		$head = $head ? $head : "#c2cdd6";
		$even = $even ? $even : "#dee3e7";
		$odd = $odd ? $odd : "#E9E9E9";

		// create the file
		$outputfile = "<HTML>
<head>
<meta name=\"generator\" content=\"Formulize -- form creation and data management for XOOPS\" />
<title>" . _formulize_DE_EXPORTCALC_TITLE . " '$title'</title>
<style type=\"text/css\">
.outer {border: 1px solid silver;}
.head { background-color: $head; padding: 5px; font-weight: bold; }
.even { background-color: $even; padding: 5px; }		
.odd { background-color: $odd; padding: 5px; }
body {color: black; background: white; margin-top: 30px; margin-bottom: 30px; margin-left: 30px; margin-right: 30px; padding: 0; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10pt;}
td { vertical-align: top; }
</style>
</head>
<body>
<h1>" . _formulize_DE_EXPORTCALC_TITLE . " '$title'</h1>
<table class=outer>
$output
</table>
</body>
</html>";		
		// output the file
		$exfilename = strrchr($filename, "/");
		$wpath = XOOPS_ROOT_PATH."/modules/formulize/export$exfilename";
		$exportfile = fopen($wpath, "w");
		fwrite ($exportfile, $outputfile);
		fclose ($exportfile);
	}			
}



// this function converts a UID to a full name, or user name, if the handle is uid or proxyid
// also converts blanks to [blank]
function convertUids($value, $handle) {
	if(!is_numeric($value) AND $value == "") { $value = "[blank]"; }
	if($handle != "uid" AND $handle != "proxyid") { return $value; }
	global $xoopsDB;
	$name_q = q("SELECT name, uname FROM " . $xoopsDB->prefix("users") . " WHERE uid='$value'");
	$name = $name_q[0]['name'];
	if($name) {
		return $name;
	} else {
		return $name_q[0]['uname'];
	}
}

// this function returns the handle corresponding to a given column or grouping value in the requested calculations data, or advanced search query
function calcHandle($value, $frid) {
	if(!$frid OR ($value == "uid" OR $value == "proxyid" OR $value == "creation_date" OR $value == "mod_date" OR $value == "creator_email")) {
		$handle = $value;
	} else {
		$thandle = convertIds($value, $frid); // convert id to handle if this is a framework (unless it's a metadata value)
		$handle = $thandle[0];	
	}
	return $handle;				
}



// this function evaluates a basic part of an advanced search.
// accounts for all the values of a multiple value field, such as a checkbox
// operators: ==, !=, >, <, <=, >=, LIKE, NOT LIKE
function evalAdvSearch($entry, $handle, $op, $term) {
	$result = 0;
	$term = str_replace("\'", "'", $term); // seems that apostrophes are the only things that arrive at this point still escaped.
	$values = display($entry, $handle);
	if($handle == "uid" OR $handle=="proxyid") {
		$values = convertUids($values, $handle);
	} 
	if ($term == "{USER}") {
		global $xoopsUser;
		$term = $xoopsUser->getVar('name');
		if(!$term) { $term = $xoopsUser->getVar('uname'); }
	}
 	if (ereg_replace("[^A-Z{}]","", $term) == "{TODAY}") {
		$number = ereg_replace("[^0-9+-]","", $term);
		$term = date("Y-m-d",mktime(0, 0, 0, date("m") , date("d")+$number, date("Y")));
	}
//	code below replaced with the above check by dpicella which accounts for +/- number after {TODAY, ie: {TODAY+10}
//	if ($term == "{TODAY}") {
//		$term = date("Y-m-d");
//	}
	if ($term == "{BLANK}") {
		$term = "";
	}
	switch($op) {
		case "==":
			if(is_array($values)) {
				foreach($values as $value) {
					if($value == $term) { 
						$result = 1; 
						break;
					}
				}
			} else {
				if($values == $term) { $result = 1; }
			}
			break;
		case "!=":
			if(is_array($values)) {
				foreach($values as $value) {
					if($value != $term) { 
						$result = 1; 
						break;
					}
				}
			} else {
				if($values != $term) { $result = 1; }
			}
			break;
		case ">":
			if(is_array($values)) {
				foreach($values as $value) {
					if($value > $term) { 
						$result = 1; 
						break;
					}
				}
			} else {
				if($values > $term) { $result = 1; }
			}
			break;
		case "<":
			if(is_array($values)) {
				foreach($values as $value) {
					if($value < $term) { 
						$result = 1; 
						break;
					}
				}
			} else {
				if($values < $term) { $result = 1; }
			}
			break;
		case "<=":
			if(is_array($values)) {
				foreach($values as $value) {
					if($value <= $term) { 
						$result = 1; 
						break;
					}
				}
			} else {
				if($values <= $term) { $result = 1; }
			}
			break;
		case ">=":
			if(is_array($values)) {
				foreach($values as $value) {
					if($value >= $term) { 
						$result = 1; 
						break;
					}
				}
			} else {
				if($values >= $term) { $result = 1; }
			}
			break;
		case "LIKE":
			if(is_array($values)) {
				foreach($values as $value) {
					if(strstr($value, $term)) { 
						$result = 1; 
						break;
					}
				}
			} else {
				if(strstr($values, $term)) { $result = 1; }
			}
			break;
		case "NOT LIKE":
			if(is_array($values)) {
				foreach($values as $value) {
					if(!strstr($value, $term)) { 
						$result = 1; 
						break;
					}
				}
			} else {
				if(!strstr($values, $term)) { $result = 1; }
			}
			break;
	}
	return $result;
}


// this function includes the javascript necessary make the interface operate properly
// note the mandatory clearing of the ventry value upon loading of the page.  Necessary to make the back button work right (otherwise ventry setting is retained from the previous loading of the page and the form is displayed after the next submission of the controls form)
function interfaceJavascript($fid, $frid, $currentview, $useWorking) {
?>
<script type='text/javascript'>

window.document.controls.ventry.value = '';
window.document.controls.loadreport.value = '';

function warnLock() {
	alert('<?php print _formulize_DE_WARNLOCK; ?>');
	return false;
}

function clearSearchHelp(formObj, defaultHelp) {
	if(formObj.firstbox.value == defaultHelp) {
		formObj.firstbox.value = "";
	}
}

function showPop(url) {

	window.document.controls.ventry.value = '';
	if (window.popup == null) {
		popup = window.open(url,'popup','toolbar=no,scrollbars=yes,resizable=yes,width=800,height=450,screenX=0,screenY=0,top=0,left=0');
      } else {
		if (window.popup.closed) {
			popup = window.open(url,'popup','toolbar=no,scrollbars=yes,resizable=yes,width=800,height=450,screenX=0,screenY=0,top=0,left=0');
            } else {
			window.popup.location = url;              
		}
	}
	window.popup.focus();

}


function confirmDel() {
	var answer = confirm ('<?php print _formulize_DE_CONFIRMDEL; ?>');
	if (answer) {
		window.document.controls.delconfirmed.value = 1;
		window.document.controls.ventry.value = '';
		showLoading();
	} else {
		return false;
	}
}

function confirmClone() {
	var clonenumber = prompt("<?php print _formulize_DE_CLONE_PROMPT; ?>", "1");
	if(eval(clonenumber) > 0) {
		window.document.controls.cloneconfirmed.value = clonenumber;
		window.document.controls.ventry.value = '';
		showLoading();
	} else {
		return false;
	}
}


function sort_data(col) {
	if(window.document.controls.sort.value == col) {
		var ord = window.document.controls.order.value;
		if(ord == 'SORT_DESC') {
			window.document.controls.order.value = 'SORT_ASC';
		} else {
			window.document.controls.order.value = 'SORT_DESC';
		}
	} else {
		window.document.controls.order.value = 'SORT_ASC';
	}
	window.document.controls.sort.value = col;
	window.document.controls.ventry.value = '';
	showLoading();
}


function runExport(type) {
	window.document.controls.xport.value = type;
	window.document.controls.ventry.value = '';
	showLoading();

}

/* ---------------------------------------
   The selectall and clearall functions are based on a function by
   Vincent Puglia, GrassBlade Software
   site:   http://members.aol.com/grassblad
   
   NOTE: MUST RETROFIT THIS SO IN ADDITION TO CHECKING TYPE, WE ARE CHECKING FOR 'delete_' in the name, so we can have other checkbox elements in the screen templates!
------------------------------------------- */

function selectAll(formObj) 
{
   for (var i=0;i < formObj.length;i++) 
   {
      fldObj = formObj.elements[i];
      if (fldObj.type == 'checkbox')
      { 
         fldObj.checked = true; 
      }
   }
}

function clearAll(formObj)
{
   for (var i=0;i < formObj.length;i++) 
   {
      fldObj = formObj.elements[i];
      if (fldObj.type == 'checkbox')
      { 
         fldObj.checked = false; 
      }
   }
}

function delete_view(formObj, pubstart, endstandard) {

	for (var i=0; i < formObj.currentview.options.length; i++) {
		if (formObj.currentview.options[i].selected) {
			if( i > endstandard && i < pubstart && formObj.currentview.options[i].value != "") {
				var answer = confirm ('<?php print _formulize_DE_CONF_DELVIEW; ?>');
				if (answer) {
					window.document.controls.delview.value = 1;
					window.document.controls.ventry.value = '';
					showLoading();
				} else {
					return false;
				}
			} else {
				if(formObj.currentview.options[i].value != "") {
					alert('<?php print _formulize_DE_DELETE_ALERT; ?>');
				}
				return false;
			}
		}
	}

}

function change_view(formObj, pickgroups, endstandard) {
	for (var i=0; i < formObj.currentview.options.length; i++) {
		if (formObj.currentview.options[i].selected) {
			if(i == pickgroups && pickgroups != 0) {
				<?php print "showPop('" . XOOPS_URL . "/modules/formulize/include/changescope.php?fid=$fid&frid=$frid&scope=$currentview');"; ?>				
				return false;
			} else {
				if ( formObj.currentview.options[i].value == "" ) {
					return false;
				} else {
					window.document.controls.loadreport.value = 1;
					if(i <= endstandard && window.document.controls.lockcontrols.value == 1) {
						window.document.controls.resetview.value = 1;
						window.document.controls.curviewid.value = "";
					}
					window.document.controls.lockcontrols.value = 0;
					window.document.controls.ventry.value = '';
					showLoading();
				}
			}
		}
	}
}

function addNew(flag) {
	if(flag=='proxy') {
		window.document.controls.ventry.value = 'proxy';
	} else if(flag=='single') {
		window.document.controls.ventry.value = 'single';
	} else {
		window.document.controls.ventry.value = 'addnew';
	}
	window.document.controls.submit();
}

function goDetails(viewentry) {
	window.document.controls.ventry.value = viewentry;
	window.document.controls.submit();
}

function cancelCalcs() {
	window.document.controls.calc_cols.value = '';
	window.document.controls.calc_calcs.value = '';
	window.document.controls.calc_blanks.value = '';
	window.document.controls.calc_grouping.value = '';
	window.document.controls.hlist.value = 0;
	window.document.controls.hcalc.value = 1;
	window.document.controls.ventry.value = '';
	showLoading();
}

function customButtonProcess(caid, entries) {
	window.document.controls.caid.value = caid;
	window.document.controls.caentries.value = entries;
	showLoading();
}


function hideList() {
	window.document.controls.hlist.value = 1;
	window.document.controls.hcalc.value = 0;
	window.document.controls.ventry.value = '';
	showLoading();
}

function showList() {
	window.document.controls.hlist.value = 0;
	window.document.controls.hcalc.value = 1;
	window.document.controls.ventry.value = '';
	showLoading();
}

function killSearch() {
	window.document.controls.asearch.value = '';
	window.document.controls.ventry.value = '';
	showLoading();
}

function showLoading() {
	<?php
		if($useWorking) {
			print "window.document.getElementById('listofentries').style.opacity = 0.5;\n";
			print "window.document.getElementById('workingmessage').style.display = 'block';\n";
			print "window.scrollTo(0,0);\n";
		}
	?>
	window.document.controls.ventry.value = '';
	window.document.controls.submit();
}

function showLoadingReset() {
	<?php
		if($useWorking) {
			print "window.document.getElementById('listofentries').style.opacity = 0.5;\n";
			print "window.document.getElementById('workingmessage').style.display = 'block';\n";
			print "window.scrollTo(0,0);\n";
		}
	?>
	window.document.resetviewform.submit();
}

function pageJump(page) {
	window.document.controls.formulize_LOEPageStart.value = page;
	showLoading();
}

</script>
<?php
}

//THIS FUNCTION READS A LEGACY REPORT (ONE GENERATED IN 1.6rc OR PREVIOUS)
function loadOldReport($id, $fid, $view_groupscope) {
/*	need to create the following for passing back...
	$reportscope - ,1,31,45, list of group ids
	$_POST['oldcols'] - 234,56,781 list of ele_ids for visible columns (handles for a framework, but an old report will never be for a framework)
	$_POST['asearch'] - flat array of search elements, separator: --> /,%^&2 <--, possible elements:
		[field]ele_id[/field], ==, !=, <, >, <=, >=, LIKE, NOT, NOT LIKE, AND, OR, ( and )
	$_POST['calc_cols'] - 234/56/781 - list of ele_ids, or can include uid, proxyid, mod_date, creation_date
	$_POST['calc_calcs'] - sum,avg,min,max,count,per/...next column 
	$_POST['calc_blanks'] - all,noblanks,onlyblanks/...next column
	$_POST['calc_grouping'] - none,uid,proxyid,mod_date,creation_date,orlistofele_ids/...next column
	$_POST['sort'] - ele_id for form, handle for framework
	$_POST['order'] - SORT_ASC, SORT_DESC
*/
	global $xoopsDB;
	$s = "&*=%4#";
	// get all data from DB
	$data = q("SELECT report_ispublished, report_scope, report_fields, report_search_typeArray, report_search_textArray, report_andorArray, report_calc_typeArray, report_sort_orderArray, report_ascdscArray, report_globalandor FROM " . $xoopsDB->prefix("formulize_reports") . " WHERE report_id=$id AND report_id_form=$fid");

	// reportscope
	$scope = explode($s, $data[0]['report_scope']);
	if($scope[0] == "") { 
		if($view_groupscope) {
			$found_scope = "group";
		} else {
			$found_scope = "mine";
		}
	} else {
		foreach($scope as $thisscope) {
			if(substr($thisscope, 0, 1) == "g") {
				$found_scope .= "," . substr($thisscope, 1);
			} else { // the case of only userscopes, need to set the scope to the groups that the user is a member of
				$user_scope[] = $thisscope; // save and include as an advanced search property looking for the user id
				if(!$membership_handler) { $membership_handler =& xoops_gethandler('membership'); }
				unset($uidGroups);
				unset($groupString);
				$uidGroups = $membership_handler->getGroupsByUser($thisscope);
				$uidGroups = array_unique($uidGroups);
				// remove registered users from the $uidGroups -- registered users is equivalent to "all groups" since everyone is a member of it
				foreach($uidGroups as $key=>$thisgroup) {
					if($thisgroup == 2) { unset($uidGroups[$key]); }
				}								
				$groupString = implode(",", $uidGroups);				
				$found_scope .= "," . $groupString;
			}
		}
		$found_scope .= ",";
	}

	$to_return[0] = $found_scope;

	// oldcols
	$tempcols = explode($s, $data[0]['report_fields']);
// This conversion now performed as part of DB query
//	foreach($tempcols as $col) {
//		$cols[] = str_replace("`", "'", $col);
//	}
	$ids = convertHeadersToIds($cols, $fid);
	$to_return[1] = implode(",", $ids);

	// asearch - complicated!
	$s2 = "/,%^&2";
	$gao = $data[0]['report_globalandor'];
	if($gao == "and") { $gao = "AND"; }
	if($gao == "or") { $gao = "OR"; }
	$terms = explode($s, $data[0]['report_search_textArray']);
	$tempops = explode($s, $data[0]['report_search_typeArray']);
	foreach($tempops as $thisop) {
		switch($thisop) {
			case "equals":
				$ops[] = "==";
				break;
			case "not":
				$ops[] = "!=";
				break;
			case "like":
				$ops[] = "LIKE";
				break;
			case "notlike":
				$ops[] = "NOT LIKE";
				break;
			case "greaterthan":
				$ops[] = ">";
				break;
			case "greaterthanequal":
				$ops[] = ">=";
				break;
			case "lessthan":
				$ops[] = "<";
				break;
			case "lessthanequal":
				$ops[] = "<=";
				break;
		}
	}
	$laos = explode($s, $data[0]['report_andorArray']);
	$start = 1;

	// for each found column, we should create:
	// ($field $op $term1 $localandor $field $op $term2....)

	for($i=0;$i<count($ids);$i++) {
		if($terms[$i]) {
			if(!$start) {
				$asearch .= $s2 . $gao . $s2;
			}
			$start = 0; 
			$asearch .= "(";
			unset($allterms);
			$allterms = explode(",", $terms[$i]);
			$start2 = 1;
			foreach($allterms as $thisterm) {
				if(!$start2) {
					if($laos[$i] == "and") { $lao = "AND"; }
					if($laos[$i] == "or") { $lao = "OR"; }
					$asearch .= $s2 . $lao;
				}
				$start2 = 0;
				$asearch .= $s2 . "[field]" . $ids[$i] . "[/field]";
				$asearch .= $s2 . $ops[$i];
				$termtouse = str_replace("[,]", ",", $thisterm);
				$asearch .= $s2 . $termtouse;
			}
			$asearch .= $s2 . ")";
		}
	}
	// add in any user_scope found...
	if(count($user_scope)>0) {
		if($asearch) { 
			$asearch .= $s2 . "AND" . $s2 . "(" . $s2; 
			$needtoclose = 1;
		}
		$start = 1;
		foreach($user_scope as $user) {
			if(!$start) {
				$asearch .= $s2 . "OR" . $s2;
			}
			$start = 0;
			$name = convertUids($user, "uid");
			$asearch .= "[field]uid[/field]" . $s2 . "==" . $s2 . $name; 
		}
		if($needtoclose) { $asearch .= $s2 . ")"; }
	}

	$to_return[2] = $asearch;
		
	// calcs - special separator, and then the standard separator within each column (since multiple calcs can be requested)
	$oldcalcs = explode("!@+*+6-", $data[0]['report_calc_typeArray']);
	unset($cols);
	for($i=0;$i<count($ids);$i++) {
		if($oldcalcs[$i]) {
			$cols[] = $ids[$i];
			unset($localcalcs);
			$thesecalcs = explode($s, $oldcalcs[$i]);
			foreach($thesecalcs as $acalc) {
				if(strstr($acalc, "selected")) {
					if(strstr($acalc, "sum")) {
						$localcalcs[] = "sum";
					}
					if(strstr($acalc, "average")) {
						$localcalcs[] = "avg";
					}
					if(strstr($acalc, "min")) {
						$localcalcs[] = "min";
					}
					if(strstr($acalc, "max")) {
						$localcalcs[] = "max";
					}
					if(strstr($acalc, "count")) {
						$localcalcs[] = "count";
					}
					if(strstr($acalc, "percent")) {
						$localcalcs[] = "per";
					}
				}
			}
			$foundcalcs = implode(",", $localcalcs);
			$calcs[] = $foundcalcs;
			unset($theseblanks);
			unset($thesegrouping);
			for($x=0;$x<count($localcalcs);$x++) {
				$theseblanks[] = "all";
				$thesegrouping[] = "none";
			}
			$tempblanks = implode(",", $theseblanks);
			$blanks[] = $tempblanks;
			$tempgrouping = implode(",", $thesegrouping);
			$grouping[] = $tempgrouping;		
		}
	}
	$to_return[3] = implode("/", $cols);
	$to_return[4] = implode("/", $calcs);
	$to_return[5] = implode("/", $blanks);
	$to_return[6] = implode("/", $grouping);

	// sort and order
	$sorts = explode($s, $data[0]['report_sort_orderArray']);
	$orders = explode($s, $data[0]['report_ascdscArray']);
	for($i=0;$i<count($ids);$i++) {
		if($sorts[$i] == 1) {
			$to_return[7] = $ids[$i];
			if($orders[$i] == "ASC") { 
				$to_return[8] = "SORT_ASC"; 
			} else {
				$to_return[8] = "SORT_DESC";
			}
			break;
		}
	}
	if(!$to_return[7]) { $to_return[7] = ""; }
	if(!$to_return[8]) { $to_return[8] = ""; }

	// hide list, hide calcs
	// if ispub includes a 3 then hide list, show calcs
	if(strstr($data[0]['report_ispublished'], "3")) {
		$to_return[9] = 1;
		$to_return[10] = 0;
	} elseif($to_return[3]) {
		$to_return[9] = 1;
		$to_return[10] = 0;
	} else {
		$to_return[9] = 0;
		$to_return[10] = 1;
	}

	// lock controls
	// if ispub includes a 2 or a 3, then lock controls
	if(strstr($data[0]['report_ispublished'], "3") OR strstr($data[0]['report_ispublished'], "2")) {
		$to_return[11] = 1;
	} else {
		$to_return[11] = 0;
	}

	return $to_return;

}


// THIS FUNCTION LOADS A SAVED VIEW
function loadReport($id) {
	global $xoopsDB;
	$thisview = q("SELECT * FROM " . $xoopsDB->prefix("formulize_saved_views") . " WHERE sv_id='$id'");
	$to_return[0] = $thisview[0]['sv_currentview']; 
	$to_return[1] = $thisview[0]['sv_oldcols'];
	$to_return[2] = $thisview[0]['sv_asearch'];
	$to_return[3] = $thisview[0]['sv_calc_cols'];
	$to_return[4] = $thisview[0]['sv_calc_calcs'];
	$to_return[5] = $thisview[0]['sv_calc_blanks'];
	$to_return[6] = $thisview[0]['sv_calc_grouping'];
	$to_return[7] = $thisview[0]['sv_sort'];
	$to_return[8] = $thisview[0]['sv_order'];
	$to_return[9] = $thisview[0]['sv_hidelist'];
	$to_return[10] = $thisview[0]['sv_hidecalc'];
	$to_return[11] = $thisview[0]['sv_lockcontrols'];
	$to_return[12] = $thisview[0]['sv_quicksearches'];
	return $to_return;
}

// remove columns that the user does not have permission to view -- added June 29, 2006 -- jwe
// this function takes a column list (handles or ids) and returns it with all columns removed that the user cannot view according to the display options on the elements
// this function also removes columns that are private if the user does not have view_private_elements permission
function removeNotAllowedCols($fid, $frid, $cols, $groups) {
	
	$all_allowed_cols = array();
	$allowed_cols_in_view = array();
	// metadata columns always allowed!
	$all_allowed_cols[] = "uid";
	$all_allowed_cols[] = "proxyid";
	$all_allowed_cols[] = "creation_date";
	$all_allowed_cols[] = "mod_date";
	$all_allowed_cols[] = "creator_email";
	$all_allowed_cols_raw = getAllColList($fid, $frid, $groups);
	foreach($all_allowed_cols_raw as $form_id=>$values) {
		foreach($values as $id=>$value) {
			if(!in_array($value['ele_id'], $all_allowed_cols)) {	$all_allowed_cols[] = $value['ele_id']; }
		}
	}			
	if($frid) {
		$all_cols_from_view = convertHandles($cols, $frid);		
	} else {
		$all_cols_from_view = $cols;
	}
	$allowed_cols_in_view = array_intersect($all_allowed_cols, $all_cols_from_view);
	$allowed_cols_in_view = array_values($allowed_cols_in_view);
	if($frid) {
		$allowed_cols_in_view = convertIds($allowed_cols_in_view, $frid);
	}
	return $allowed_cols_in_view;
}

// THIS FUNCTION HANDLES INTERPRETTING A LOE SCREEN TEMPLATE
// $type is the top/bottom setting
// $buttonCodeArray is the available buttons that have been pre-compiled by the drawInterface function
function formulize_screenLOETemplate($screen, $type, $buttonCodeArray, $settings, $messageText) {

	// include necessary files
	if(strstr($screen->getVar($type.'template'), 'buildFilter(')) {
		include_once XOOPS_ROOT_PATH . "/modules/formulize/include/calendardisplay.php";
	}

	// setup the button variables
	foreach($buttonCodeArray as $buttonName=>$buttonCode) {
		${$buttonName} = $buttonCode;
	}
	// setup the view name variables, with true only set for the last loaded view
	$viewNumber = 1;
	foreach($settings['publishedviewnames'] as $id=>$thisViewName) {
		$thisViewName = str_replace(" ", "_", $thisViewName);
		if($id == $settings['lastloaded']) {
			${$thisViewName} = true;
			${'view'.$viewNumber} = true;
		} else {
			${$thisViewName} = false;
			$view{'view'.$viewNumber} = false;
		}
		$viewNumber++;
	}

	// setup any custom buttons	
	$atLeastOneCustomButton = false;
	
	$caCode = array();
	foreach($screen->getVar('customactions') as $caid=>$thisCustomAction) {
		if($thisCustomAction['appearinline']) { continue; } // ignore buttons that are meant to appear inline
		$atLeastOneCustomButton = true;
		list($caCode, $caElements, $caActions, $caValues, $caMessageText, $caApplyTo) = processCustomButton($caid, $thisCustomAction);
		${$thisCustomAction['handle']} = $caCode; // assign the button code that was returned
		// processing of custom buttons now happens right up top!
		/*if(isset($_POST['caid']) AND !isset($clickedElements)) { // only process once, since clickedElements will be set after this has run
			if($caid == intval($_POST['caid'])) { // capture information about button that was clicked
				$clickedElements = $caElements;
				$clickedValues = $caValues;
				$clickedActions = $caActions;
				$clickedMessageText = $caMessageText;
				$clickedApplyTo = $caApplyTo;
			}
		}*/
	}
	/*
	// processing of custom buttons now happens right up top! 
	static $handledCustomButtons = false;
	$messageText = "";
	if($atLeastOneCustomButton AND !$handledCustomButtons) { // only process the results once per pageload (that's what the static handlecustombuttons is for)
		$handledCustomButtons = true;
		// process any custom button that was clicked on the last page load
		if(isset($_POST['caid'])) {
			$messageText = processClickedCustomButton($clickedElements, $clickedValues, $clickedActions, $clickedMessageText, $clickedApplyTo);
		}
	}
	*/
	// if there is no save button specified in either of the templates, but one is available, then put it in below the list
	if($type == "bottom" AND $saveButton AND $GLOBALS['formulize_displayElement_LOE_Used'] AND !strstr($screen->getVar('toptemplate'), 'saveButton') AND !strstr($screen->getVar('bottomtemplate'), 'saveButton')) {
		print "<p>$saveButton</p>\n";
	}
	
	$thisTemplate = html_entity_decode($screen->getVar($type.'template'));
	if($thisTemplate != "") {
		ob_start();
		eval($thisTemplate);
		$evalResult = ob_get_clean();
		if($evalResult != "") {
			print $evalResult;
		} else {
			print _AM_FORMULIZE_SCREEN_LOE_TEMPLATE_ERROR;
		}
		
		// if there are no page nav controls in either template the template, then 
		if($type == "top" AND !strstr($screen->getVar('toptemplate'), 'pageNavControls') AND (!strstr($screen->getVar('bottomtemplate'), 'pageNavControls'))) {
			print $pageNavControls;
		}
	}
	
	// output the message text to the screen if it's not used in the custom templates somewhere
	if($type == "top" AND $messageText AND !strstr($screen->getVar('toptemplate'), 'messageText') AND !strstr($screen->getVar('bottomtemplate'), 'messageText')) {
		print "<p><center><b>$messageText</b></center></p>\n";
	}
	
}

// THIS FUNCTION PROCESSES THE REQUESTED BUTTONS AND GENERATES HTML PLUS SENDS BACK INFO ABOUT THAT BUTTON
// $caid is the id of this button, $thisCustomAction is all the settings for this button, $entries is optional and is a comma separated list of entries that should be modified by this button (only takes effect on inline buttons, and possible future types)
function processCustomButton($caid, $thisCustomAction, $entries="") {

	static $nameIdAddOn = 0; // used to give inline buttons unique names and ids
	
	$caElements = array();
	$caActions = array();
	$caValues = array();
	$caCode = array();
	foreach($thisCustomAction as $effectid=>$effectProperties) {
		if(!is_numeric($effectid)) { continue; } // effectid, as second key, could be buttontext, messagetext, etc, so ignore those and focus on actual effects which will have numeric keys
		$caElements[] = $effectProperties['element'];
		$caActions[] = $effectProperties['action'];
		$caValues[] = $effectProperties['value'];
	}
	$nameIdAddOn = $thisCustomAction['appearinline'] ? $nameIdAddOn+1 : "";
	$caCode = "<input type=button style=\"width: 140px;\" name=\"" . $thisCustomAction['handle'] . "$nameIdAddOn\" id=\"" . $thisCustomAction['handle'] . "$nameIdAddOn\" value=\"" . $thisCustomAction['buttontext'] . "\" onclick=\"javascript:customButtonProcess('$caid', '$entries');\">\n";
	
	return array(0=>$caCode, 1=>$caElements, 2=>$caActions, 3=>$caValues, 4=>$thisCustomAction['messagetext'], 5=>$thisCustomAction['applyto']);
}

// THIS FUNCTION PROCESSES CLICKED CUSTOM BUTTONS
function processClickedCustomButton($clickedElements, $clickedValues, $clickedActions, $clickedMessageText, $clickedApplyTo) {

	if(!is_numeric($_POST['caid'])) { return; } // 'caid' might be set in post, but we're not processing anything unless there actually is a value there

	static $gatheredSelectedEntries = false;
	if(!$gatheredSelectedEntries) {
		$GLOBALS['formulize_selectedEntries'] = array();
		foreach($_POST as $k=>$v) { // gather entries list from the selected entries
			if(substr($k, 0, 7) == "delete_" AND $v != "") {
				$GLOBALS['formulize_selectedEntries'][substr($k, 7)] = substr($k, 7); // make sure key and value are the same, so the special function below works inside the custom button's own logic
			}
		}
		$gatheredSelectedEntries = true;
	}


	$caEntries = array();
	// need to handle "all" case by getting list of all entries in form
	if($clickedApplyTo == "selected") {
		$caEntries = $GLOBALS['formulize_selectedEntries'];
	} elseif($clickedApplyTo == "inline") {
		$caEntriesTemp = explode(",", htmlspecialchars(strip_tags($_POST['caentries'])));
		foreach($caEntriesTemp as $id=>$val) {
			$caEntries[$id] = $val; // make sure key and value are the same, so the special function below works inside the custom button's own logic (we need the $entry id to be the key).
		}
	} elseif(strstr($clickedApplyTo, "new_per_selected")) {
		foreach($GLOBALS['formulize_selectedEntries'] as $id=>$val) {
			$caEntries[$id] = "new"; // add one new entry for each box that is checked
		}
	} else {
		// right now new and new_x are handled by this default case.  They both result in the same 'new' value being sent to writeElementValue -- this may have to change if the possible apply to values change as new options are added to the ui
		$caEntries[0] = 'new';
	}
        // process changes to each entry
	foreach($caEntries as $id=>$thisEntry) { // loop through all the entries this button click applies to
		$GLOBALS['formulize_thisEntryId'] = $id; // sent up to global scope so it can be accessed by the gatherHiddenValues function without the user having to type ", $id" in the function call
		$maxIdReq = 0;
		for($i=0;$i<count($clickedElements);$i++) { // loop through all actions for this button
			if($thisEntry == "new" AND $maxIdReq > 0) { $thisEntry = $maxIdReq; } // for multiple effects on the same button, when the button applies to a new entry, reuse the initial id_req that was created during the first effect
			if(strstr($clickedValues[$i], "\$value")) {
				eval($clickedValues[$i]);
				$valueToWrite = $value;
			} else {
				$valueToWrite = $clickedValues[$i];
			}
			$maxIdReq = writeElementValue("", $clickedElements[$i], $thisEntry, $valueToWrite, $clickedActions[$i]);
		}
	}
	return $clickedMessageText;
}

// THIS FUNCTION IS USED ONLY IN LIST OF ENTRIES SCREENS, IN THE VALUES OF CUSTOM BUTTONS
// Use this to gather a specified hidden value for the current entry being processed
// The key of $caEntries above MUST be set to the entry that was selected, or else this will not work
// This function is meant to be called from inside the eval call above where the custom buttons are evaluated
// This function is only meant to work with situations where someone has actually selected an entry (or clicked inline)
function gatherHiddenValue($handle) {
	global $formulize_thisEntryId;
	global $formulize_selectedEntries;
	if(count($formulize_selectedEntries) > 0 ) {
		return htmlspecialchars(strip_tags($_POST["hiddencolumn_" . $formulize_thisEntryId . "_" . $handle]));
	} else {
		return false;
	}
}

// THIS FUNCTION GENERATES HTML FOR ANY BUTTONS THAT ARE REQUESTED
function formulize_screenLOEButton($button, $buttonText, $settings, $fid, $frid, $colids, $flatcols, $pubstart, $loadOnlyView, $calc_cols, $calc_calcs, $calc_blanks, $calc_grouping, $doNotForceSingle, $lastloaded, $currentview, $endstandard, $pickgroups, $viewoptions, $loadviewname) {
	if($buttonText) {
		switch ($button) {
			case "changeColsButton":
				return "<input type=button style=\"width: 140px;\" name=changecols value='" . $buttonText . "' onclick=\"javascript:showPop('" . XOOPS_URL . "/modules/formulize/include/changecols.php?fid=$fid&frid=$frid&cols=$colids');\"></input>";
				break;
			case "calcButton":
				return "<input type=button style=\"width: 140px;\" name=calculations value='" . $buttonText . "' onclick=\"javascript:showPop('" . XOOPS_URL . "/modules/formulize/include/pickcalcs.php?fid=$fid&frid=$frid&calc_cols=$calc_cols&calc_calcs=$calc_calcs&calc_blanks=$calc_blanks&calc_grouping=$calc_grouping');\"></input>";
				break;
			case "advSearchButton":
				$buttonCode = "<input type=button style=\"width: 140px;\" name=advsearch value='" . $buttonText . "' onclick=\"javascript:showPop('" . XOOPS_URL . "/modules/formulize/include/advsearch.php?fid=$fid&frid=$frid";
				foreach($settings as $k=>$v) {
					if(substr($k, 0, 3) == "as_") {
						$v = str_replace("'", "&#39;", $v);
						$v = stripslashes($v);
						$buttonCode .= "&$k=" . urlencode($v);
					}
				}
				$buttonCode .= "');\"></input>";
				return $buttonCode;
				break;
			case "exportButton":
				return "<input type=button style=\"width: 140px;\" name=export value='" . $buttonText . "' onclick=\"javascript:runExport('comma');\"></input>";
				break;
			case "exportCalcsButton":	
				return "<input type=button style=\"width: 140px;\" name=export value='" . $buttonText . "' onclick=\"javascript:runExport('calcs');\"></input>";
				break;
			case "importButton":
				return "<input type=button style=\"width: 140px;\" name=impdata value='" . $buttonText . "' onclick=\"javascript:showPop('" . XOOPS_URL . "/modules/formulize/include/import.php?fid=$fid');\"></input>";
				break;
			case "addButton":
				$addNewParam = $doNotForceSingle ? "" : "'single'"; // force the addNew behaviour to single entry unless this button is being used on a single entry form, in which case we don't need to force anything
				return "<input type=button style=\"width: 140px;\" name=addentry value='" . $buttonText . "' onclick=\"javascript:addNew($addNewParam);\"></input>";
				break;
			case "addMultiButton":
				return "<input type=button style=\"width: 140px;\" name=addentry value='" . $buttonText . "' onclick=\"javascript:addNew();\"></input>";
				break;
			case "addProxyButton":
				return "<input type=button style=\"width: 140px;\" name=addentry value='" . $buttonText . "' onclick=\"javascript:addNew('proxy');\"></input>";
				break;
			case "notifButton":
				return "<input type=button style=\"width: 140px;\" name=notbutton value='". $buttonText . "' onclick=\"javascript:showPop('" . XOOPS_URL . "/modules/formulize/include/setnot.php?fid=$fid');\"></input>";
				break;
			case "cloneButton":
				return "<input type=button style=\"width: 140px;\" name=clonesel value='" . $buttonText . "' onclick=\"javascript:confirmClone();\"></input>";
				break;
			case "deleteButton":
				return "<input type=button style=\"width: 140px;\" name=deletesel value='" . $buttonText . "' onclick=\"javascript:confirmDel();\"></input>";
				break;
			case "selectAllButton":
				return "<input type=button style=\"width: 110px;\" name=sellall value='" . $buttonText . "' onclick=\"javascript:selectAll(this.form);\"></input>";
				break;
			case "clearSelectButton":
				return "<input type=button style=\"width: 110px;\" name=clearall value='" . $buttonText . "' onclick=\"javascript:clearAll(this.form);\"></input>";
				break;
			case "resetViewButton":
				return "<input type=button style=\"width: 140px;\" name=resetviewbutton value='" . $buttonText . "' onclick=\"javascript:showLoadingReset();\"></input>";
				break;
			case "saveViewButton":
				return "<input type=button style=\"width: 140px;\" name=save value='" . $buttonText . "' onclick=\"javascript:showPop('" . XOOPS_URL . "/modules/formulize/include/save.php?fid=$fid&frid=$frid&lastloaded=$lastloaded&cols=$flatcols&currentview=$currentview&loadonlyview=$loadOnlyView');\"></input>";
				break;
			case "deleteViewButton":
				return "<input type=button style=\"width: 140px;\" name=delete value='" . $buttonText . "' onclick=\"javascript:delete_view(this.form, '$pubstart', '$endstandard');\"></input>";
				break;
			case "currentViewList":
				$currentViewList = "<b>" . $buttonText . "</b><br><SELECT style=\"width: 350px;\" name=currentview id=currentview size=1 onchange=\"javascript:change_view(this.form, '$pickgroups', '$endstandard');\">\n";
				$currentViewList .= $viewoptions;
				$currentViewList .= "\n</SELECT>\n";
				if(!$loadviewname AND strstr($currentview, ",") AND !$loadOnlyView) { // if we're on a genuine pick-groups view (not a loaded view)...and the load-only-view override is not in place (which eliminates other viewing options besides the loaded view)
					$currentViewList .= "<br><input type=button style=\"width: 140px;\" name=pickdiffgroup value='" . _formulize_DE_PICKDIFFGROUP . "' onclick=\"javascript:showPop('" . XOOPS_URL . "/modules/formulize/include/changescope.php?fid=$fid&frid=$frid&scope=$currentview');\"></input>";		
				}
				return $currentViewList;
				break;
			case "saveButton":
				return "<input type=button style=\"width: 140px;\" name=deSaveButton value='" . $buttonText . "' onclick=\"javascript:showLoading();\"></input>";
				break;
		}
	} elseif($button == "currentViewList") { // must always set a currentview value in POST even if the list is not visible
		return "<input type=hidden name=currentview value='$currentview'></input>\n";
	} else {
		return false;
	}
}

// THIS FUNCTION TAKES A DATASET AND CACHES IT TO A FILE
function formulize_cacheData($data) {
	
	// if there's not enough memory to serialize $data, don't attempt to cache
	if(function_exists("memory_get_usage")) {
		$memoryLimit = intval(ini_get("memory_limit"));
		$memoryUsed = memory_get_usage() / 1000000;
		if($memoryLimit > 0 AND $memoryUsed > 0) {
			if($memoryUsed > $memoryLimit/1.6) { return ""; }
		}
	}
	
	$currentId = microtime_float();
	$newCacheFile = fopen(XOOPS_ROOT_PATH . "/cache/$currentId" . ".formulize_cached_data", "w");
	fwrite($newCacheFile, serialize($data));
	fclose($newCacheFile);
		
	// garbage collection...delete files older than 1 day
	$oldId = $currentId - 86400; // the timestamp from one day ago
	$formulize_cached_files = php4_scandir(XOOPS_ROOT_PATH . "/cache/",false, true, "formulize_cached_data"); // array of files
	foreach($formulize_cached_files as $thisFile) {
		$fileNameParts = explode(".", $thisFile);
		if($fileNameParts[0] < $oldId){
			unlink(XOOPS_ROOT_PATH . "/cache/$thisFile");
		}
	}
	return $currentId;
}

// THIS FUNCTION READS A CACHED DATASET FROM A FILE
function formulize_readCachedData($cacheId) {
	$cacheData = file_get_contents(XOOPS_ROOT_PATH . "/cache/$cacheId" . ".formulize_cached_data");
	return unserialize($cacheData);
}

// THIS FUNCTION RUNS AN ADVANCED SEARCH FILTER ON A DATASET
function formulize_runAdvancedSearch($query_string, $data) {
	if($query_string) {
		$indexer = 0;
		$asearch_parse_error = 0;
		foreach($data as $entry) {
			ob_start();
			eval($query_string); // a constructed query based on the user's input.  $query_result = 1 if it succeeds and 0 if it fails.
			ob_end_clean();
			if($query_result) {
				$found_data[] = $entry;
			} elseif(!isset($query_result)) {
				$asearch_parse_error = 1;
				break;
			}
			unset($data[$indexer]);
			$indexer++;
		}
		unset($data);
		if(count($found_data)>0) { $data = $found_data; }
	}
	return $data;
}

// THIS FUNCTION HANDLES GATHERING A DATASET FOR DISPLAY IN THE LIST
function formulize_gatherDataSet($settings, $searches, $sort, $order, $frid, $fid, $scope, $screen, $currentURL) {
				 
	// Order of operations for the requested advanced search options
	// 1. unpack the settings necessary for the search
	// 2. loop through the data and store the results, unsetting $data as we go, and then reassigning the found array to $data at the end
	
	// example of as $settings:
/*	global $xoopsUser;
	if($xoopsUser->getVar('uid') == 'j') {
	$settings['as_1'] = "[field]545[/field]";
	$settings['as_2'] = "==";
	$settings['as_3'] = "Ontario";
	$settings['as_4'] = "AND";
	$settings['as_5'] = "(";
	$settings['as_6'] = "[field]557[/field]";
	$settings['as_7'] = "==";
	$settings['as_8'] = "visiting classrooms";
	$settings['as_9'] = "OR";
	$settings['as_10'] = "[field]557[/field]";
	$settings['as_11'] = "==";
	$settings['as_12'] = "advocacy";
	$settings['as_13'] = ")";
	} // end of xoopsuser check
*/
//	545 prov
//	556 are you still interested, yes/no
//	570 where vol with LTS (university name)
//	557 which of following areas... (multi)

	$query_string = "";
	if($settings['as_0']) {
		// build the query string
		// string looks like this:
		//if([query here]) {
		//	$query_result = 1;
		//}
		
		$query_string .= "if(";
		$firstTermNot = false;
		for($i=0;$settings['as_' . $i];$i++) {
			// save query for writing later
			$wq['as_' . $i] = $settings['as_' . $i];
			if(substr($settings['as_' . $i], 0, 7) == "[field]" AND substr($settings['as_' . $i], -8) == "[/field]") { // a field has been found, next two should be part of the query
				$fieldLen = strlen($settings['as_' . $i]);
				$field = substr($settings['as_' . $i], 7, $fieldLen-15); // 15 is the length of [field][/field]
				$field = calcHandle($field, $frid);
				$query_string .= "evalAdvSearch(\$entry, \"$field\", \"";
				$i++;
				$wq['as_' . $i] = $settings['as_' . $i];
				$query_string .= $settings['as_' . $i] . "\", \"";
				$i++;
				$wq['as_' . $i] = $settings['as_' . $i];
				$query_string .= $settings['as_' . $i] . "\")";
			} else {
				if($i==0 AND $settings['as_'.$i] == "NOT") {
					$firstTermNot = true; // must flag initial negations and handle differently
					continue;
				}
				if($firstTermNot == true AND $i==1 AND $settings['as_'.$i] != "(") {
					$firstTermNot = false; // only actually preserve the full negation if the second term is a parenthesis
					$query_string .= " NOT ";
				}
				$query_string .= " " . $settings['as_' . $i] . " ";
			}
		}

		if($firstTermNot) { // if we are looking for the negative of the entire query...
			$query_string .= ") { \$query_result=0; } else { \$query_result=1; }";
		} else {
			$query_string .= ") { \$query_result=1; } else { \$query_result=0; }";
		}
	}

	// build the filter out of the searches array
	$start = 1;
	$filter = "";
	foreach($searches as $key=>$one_search) {
		// $key is handles for frameworks, and ele_ids for non-frameworks.
		if(!$start) { $filter .= "]["; }
		$filter .= $key . "/**/$one_search"; // . mysql_real_escape_string($one_search); // mysql_real_escape_string no longer necessary here since the extraction layer does the necessary dirty work for us
		$start = 0;
	}

// CANNOT PAGE RESULTS YET BECAUSE ADVANCED SEARCHES MUST BE DONE OVER ENTIRE RESULT SET.  ARRRRGGGHHH!!!
/*	if(!$frid) {
		$startEntry = $_POST['startPageEntry'] ? intval($_POST['startPageEntry']) : 0;
		$data = getData($frid, $fid, $filter, "AND", $scope, $sort, $order, $startEntry, $entriesPerPage); // additional options for paging results not enabled due to current inability of paging successfully when filters are set on fields in the non-main form.
		if($sort AND $order) { // because the extraction layer does not return results in order, we need to sort them afterwards (even when sort and order is passed -- extraction layer only uses them to generate the list of entries that should be gathered, but it does not gather them in order)
			$data = resultSort($data, $sort, $order); // sort is ele_id for form, handle for framework
		}
*/
	$regeneratePageNumbers = false;
	// handle magic quotes if necessary
	if(get_magic_quotes_gpc()) {
		$_POST['formulize_previous_filter'] = stripslashes($_POST['formulize_previous_filter']);
		$_POST['formulize_previous_querystring'] = stripslashes($_POST['formulize_previous_querystring']);
	}
	
	if(!$settings['formulize_cacheddata'] OR isset($_POST['lastentry']) OR $GLOBALS['formulize_deletionRequested'] OR $GLOBALS['formulize_writeElementValueWasRun'] OR $GLOBALS['formulize_readElementsWasRun'] OR $filter != $_POST['formulize_previous_filter'] OR $scope != $_POST['formulize_previous_scope']) { // if we have no cached data, or if the user is coming back from modifying an entry, or if there is any new setting that is going to change the entries that are part of the underlying dataset...
		$startTimeTest = microtime_float();
		$data = getData($frid, $fid, $filter, "AND", $scope);
		$endTimeTest = microtime_float();
		$testDur = $endTimeTest - $startTimeTest;
		//print "GetData: $testDur<br>";
		if($sort AND $order) { // because the extraction layer does not return results in order, we need to sort them afterwards 
			$data = resultSort($data, $sort, $order); // sort is ele_id for form, handle for framework
		}
		$formulize_cachedDataId = formulize_cacheData($data);
		if($query_string) { $data = formulize_runAdvancedSearch($query_string, $data); } // must do advanced search after caching the data, so the advanced search results are not contained in the cached data.  Otherwise, we would have to rerun the base extraction every time we wanted to change just the advanced search query.  This way, advanced search changes can just hit the cache, and not the db.
		if(!$formulize_cachedDataId) { // caching failed (most likely because of memory limit)
			// if there is some difference between this page load and the previous one in terms of the underlying query terms, then regenerate the page numbers
			if(($query_string != $_POST['formulize_previous_querystring'] AND $query_string != "") OR $filter != $_POST['formulize_previous_filter'] OR $scope != $_POST['formulize_previous_scope']) {
				$regeneratePageNumbers = true;
			}
		} else { // caching worked, so regenerate the numbers when necessary, unless we're coming back from editing an entry
			if(!isset($_POST['lastentry']) AND (($query_string != $_POST['formulize_previous_querystring'] AND $query_string != "") OR $filter != $_POST['formulize_previous_filter'] OR $scope != $_POST['formulize_previous_scope'])) {
				$regeneratePageNumbers = true;
			}
		}
	} else { // gather cached data 
		$startTimeTest = microtime_float();
		$data = formulize_readCachedData($settings['formulize_cacheddata']);
		$endTimeTest = microtime_float();
		$testDur = $endTimeTest - $startTimeTest;
		//print "Cache: $testDur<br>";
		$formulize_cachedDataId = $settings['formulize_cacheddata'];
		if($sort AND $order AND ($sort != $_POST['formulize_previous_sort'] OR $order != $_POST['formulize_previous_order'])) { // only redo sorting if this is different from the last page load
			$data = resultSort($data, $sort, $order); // sort is ele_id for form, handle for framework
			$formulize_cachedDataId = formulize_cacheData($data);			
		}
		//print "$query_string<br>\n";
		//print $_POST['formulize_previous_querystring'] . "<br>\n";
		$data = formulize_runAdvancedSearch($query_string, $data); // need to reapply advanced search every time, since it's not part of the cached data
		if($query_string != $_POST['formulize_previous_querystring'] AND $query_string != "") {
			$regeneratePageNumbers = true;
		}
	}
	
	// must start drawing interface here, since we need to include those hidden form elements below...
	$drawResetForm = true;
	$useWorking = true;
	if($screen) {
		$drawResetForm = $screen->getVar('usereset') == "" ? false : true;
		$useWorking = !$screen->getVar('useworkingmsg') ? false : true;
	}
	
	if($drawResetForm) {
		$currentviewResetForm = $settings['currentview'];
		print "<form name=resetviewform id=resetviewform action=$currentURL method=post onsubmit=\"javascript:showLoading();\">\n";
		if($screen) { $currentviewResetForm = $screen->getVar('defaultview'); } // override the default set by $settings...must do this here and not above, since this should only apply to the resetview form
		print "<input type=hidden name=currentview value='$currentviewResetForm'>\n";
		print "<input type=hidden name=userClickedReset value=1>\n";
		print "</form>\n";
	}

	if($useWorking) {
		// working message
		global $xoopsConfig;
		print "<div id=workingmessage style=\"display: none; position: absolute; width: 100%; right: 0px; text-align: center; padding-top: 50px;\">\n";
		if ( file_exists(XOOPS_ROOT_PATH."/modules/formulize/images/working-".$xoopsConfig['language'].".gif") ) {
			print "<img src=\"" . XOOPS_URL . "/modules/formulize/images/working-" . $xoopsConfig['language'] . ".gif\">\n";
		} else {
			print "<img src=\"" . XOOPS_URL . "/modules/formulize/images/working-english.gif\">\n";
		}
		print "</div>\n";
	}

	print "<div id=listofentries>\n";

	print "<form name=controls id=controls action=$currentURL method=post onsubmit=\"javascript:showLoading();\">\n";
	if(isset($GLOBALS['xoopsSecurity'])) {
		print $GLOBALS['xoopsSecurity']->getTokenHTML();
	}		
		
	print "<input type=hidden name=formulize_cacheddata id=formulize_cacheddata value=\"$formulize_cachedDataId\">\n"; // set the cached data id that we might want to read on next page load
	print "<input type=hidden name=formulize_previous_filter id=formulize_previous_filter value=\"" . htmlSpecialChars($filter) . "\">\n"; // save the filter to check for a change on next page load
	print "<input type=hidden name=formulize_previous_scope id=formulize_previous_scope value=\"" . htmlSpecialChars($scope) . "\">\n"; // save the scope to check for a change on next page load
	print "<input type=hidden name=formulize_previous_sort id=formulize_previous_sort value=\"$sort\">\n";
	print "<input type=hidden name=formulize_previous_order id=formulize_previous_order value=\"$order\">\n";
	print "<input type=hidden name=formulize_previous_querystring id=formulize_previous_querystring value=\"" . htmlSpecialChars($query_string). "\">\n"; 
	
	$to_return[0] = $data;
	$to_return[1] = $wq;
	$to_return[2] = $regeneratePageNumbers;
	return $to_return;
}

// THIS FUNCTION CALCULATES THE NUMBER OF PAGES AND DRAWS HTML FOR NAVIGATING THEM
function formulize_LOEbuildPageNav($data, $screen, $regeneratePageNumbers) {
	$pageNav = "";
	//print "Passed pagestart: " . $_POST['formulize_LOEPageStart'] . "<br>";
	$pageStart = (isset($_POST['formulize_LOEPageStart']) AND !$regeneratePageNumbers) ? intval($_POST['formulize_LOEPageStart']) : 0; // regenerate essentially causes the user to jump back to page 0 because something about the dataset has fundamentally changed (like a new search term or something)
	//print "Actual pagestart: $pageStart<br>";
	print "\n<input type=hidden name=formulize_LOEPageStart id=formulize_LOEPageStart value=\"$pageStart\">\n"; // will receive via javascript the page number that was clicked, or will cause the current page to reload if anything else happens
	$numberPerPage = is_object($screen) ? $screen->getVar('entriesperpage') : 10;
	if($numberPerPage == 0 OR $_POST['hlist']) { return $pageNav; } // if all entries are supposed to be on one page for this screen, then return no navigation controls.  Also return nothing if the list is hidden.
	$allPageStarts = array();
	$pageNumbers = 0;
	for($i=0;$i<count($data);$i=$i+$numberPerPage) {
		$pageNumbers++;
		$allPageStarts[$pageNumbers] = $i;
	}
	$userPageNumber = $pageStart > 0 ? ($pageStart/$numberPerPage)+1 : 1;
	if($pageNumbers > 1) {
		if($pageNumbers > 9) {
			if($userPageNumber < 6) {
				$firstDisplayPage = 1;
				$lastDisplayPage = 9;
			} elseif($userPageNumber + 4 > $pageNumbers) { // too close to the end
				$firstDisplayPage = $userPageNumber - 4 - ($userPageNumber+4-$pageNumbers); // the previous four, plus the difference by which we're over the end when we add 4
				$lastDisplayPage = $pageNumbers;
			} else { // somewhere in the middle
				$firstDisplayPage = $userPageNumber - 4;
				$lastDisplayPage = $userPageNumber + 4;
			}
		} else {
			$firstDisplayPage = 1;
			$lastDisplayPage = $pageNumbers;
		}
		$pageNav .= "<p><nobr>";
		$pageNav .= "<b>" . _AM_FORMULIZE_LOE_ONPAGE . $userPageNumber . ".</b>&nbsp;&nbsp;[&nbsp;&nbsp;";
		if($firstDisplayPage > 1) {
			$pageNav .= "<a href=\"\" onclick=\"javascript:pageJump('0');return false;\">" . _AM_FORMULIZE_LOE_FIRSTPAGE . "</a>&nbsp;&nbsp;\n";
		}
		//print "$firstDisplayPage<br>$lastDisplayPage<br>";
		for($i=$firstDisplayPage;$i<=$lastDisplayPage;$i++) {
			//print "$i<br>";
			$thisPageStart = ($i*$numberPerPage)-$numberPerPage;
			if($thisPageStart == $pageStart) {
				$pageNav .= "<b>$i</b>\n";
			} else {
				$pageNav .= "<a href=\"\" onclick=\"javascript:pageJump('$thisPageStart');return false;\">$i</a>\n";
			}
			$pageNav .= "&nbsp;&nbsp;";
		}
		if($lastDisplayPage < $pageNumbers) {
			$lastPageStart = ($pageNumbers*$numberPerPage)-$numberPerPage;
			$pageNav .= "<a href=\"\" onclick=\"javascript:pageJump('$lastPageStart');return false;\">" . _AM_FORMULIZE_LOE_LASTPAGE . "</a>&nbsp;&nbsp;\n";
		}
		$pageNav .= "]</nobr></p>";
	}
	return $pageNav;	
}


?>