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

// This file handles the saving and publishing of views

// put javascript function here:
function saveJavascript($pubflag) {

?>

<script type='text/javascript'>
<!--

function newSaveView(formObj) {
	for (var i=0; i < formObj.savethis.options.length; i++) {
		if (formObj.savethis.options[i].selected) {
			window.document.saveoptions.viewselection.value = formObj.savethis.options[i].value;
			window.document.saveoptions.submit();
		}
	}
}

function saveSettings(formObj) {
	for (var i=0; i < formObj.savethis.options.length; i++) {
		if (formObj.savethis.options[i].selected) {
			var newid = formObj.savethis.options[i].value;
			if(newid == "new") {
				var newname = prompt("<? print _formulize_DE_SAVE_NEWPROMPT; ?>", "");
				if(!newname) {
					return false;
				} 
			} 
			i=formObj.savethis.options.length;
		}
	}
	if(formObj.scope.length) { // there's more than one option
		for (var i=0; i < formObj.scope.length; i++) {
			if (formObj.scope[i].checked) {
				var newscope = formObj.scope[i].value;
			}
		}
	} else {
		var newscope = formObj.scope.value;
	}

<?
// do some PHP to control whether we draw in certain parts of this javascript function or not
if($pubflag) {
?>
	var pubgroups;
	if(formObj.pubgrouplist.options[0].selected) {
		pubgroups = "";
	} else {
		var start=1;
		for (var i=1; i < formObj.pubgrouplist.options.length; i++) {
			if (formObj.pubgrouplist.options[i].selected) {
				if(start) {
					pubgroups = formObj.pubgrouplist.options[i].value;
					start = 0;
				} else {
					pubgroups = pubgroups + "," + formObj.pubgrouplist.options[i].value;
				}
			}
		}
	}

	for (var i=0; i < formObj.lockcontrols.length; i++) {
		if (formObj.lockcontrols[i].checked) {
			var locksetting = formObj.lockcontrols[i].value;
		}
	}


	window.opener.document.controls.savegroups.value = pubgroups;
	window.opener.document.controls.savelock.value = locksetting;
<?
}
?>
	window.opener.document.controls.savescope.value = newscope;
	window.opener.document.controls.saveid.value = newid;
	if(newid == "new") { 
		window.opener.document.controls.savename.value = newname;
	}
	window.opener.document.controls.submit();
	window.self.close();
}


-->
</script>

<?

}

require_once "../../../mainfile.php";

global $xoopsConfig, $xoopsDB;
// load the formulize language constants if they haven't been loaded already
	if ( file_exists(XOOPS_ROOT_PATH."/modules/formulize/language/".$xoopsConfig['language']."/main.php") ) {
		include_once XOOPS_ROOT_PATH."/modules/formulize/language/".$xoopsConfig['language']."/main.php";
	} else {
		include_once XOOPS_ROOT_PATH."/modules/formulize/language/english/main.php";
	}


global $xoopsDB, $xoopsUser;
include_once XOOPS_ROOT_PATH.'/modules/formulize/include/functions.php';

	// Set some required variables
	$mid = getFormulizeModId();
	$fid="";
	if(!$fid = $_GET['fid']) {
		$fid = $_POST['fid'];
	}
	$frid = "";
	if(!$frid = $_GET['frid']) {
		$frid = $_POST['frid'];	
	}

	$gperm_handler = &xoops_gethandler('groupperm');
	$member_handler =& xoops_gethandler('member');
	$groups = $xoopsUser ? $xoopsUser->getGroups() : XOOPS_GROUP_ANONYMOUS;
	$uid = $xoopsUser->getVar('uid');


	if(!$scheck = security_check($fid, "", $uid, "", $groups, $mid, $gperm_handler, "")) {
		print "<p>" . _NO_PERM . "</p>";
		exit;
	}

	$lastloaded = $_GET['lastloaded'];
	if(!$_POST['viewselection']) {
		$viewselection = $lastloaded;
	} else {
		$viewselection = $_POST['viewselection'];
	}

	$getcols = $_GET['cols'];
	$cols = explode(",", $_GET['cols']); // what is this for?
	$currentview = $_GET['currentview'];
	if(strstr($_GET['currentview'], ",")) { 
		$specificgroups = explode(",", trim($_GET['currentview'], ",")); 
		$groupNames = groupNameList(trim($_GET['currentview'], ","));
	}
	$view_groupscope = $gperm_handler->checkRight("view_groupscope", $fid, $groups, $mid);
	$view_globalscope = $gperm_handler->checkRight("view_globalscope", $fid, $groups, $mid);
	$publish_reports = $gperm_handler->checkRight("publish_reports", $fid, $groups, $mid);
	$publish_globalscope = $gperm_handler->checkRight("publish_globalscope", $fid, $groups, $mid);
	

// main body of page goes here...
include_once XOOPS_ROOT_PATH . "/class/xoopsformloader.php";

print "<HTML>";
print "<head>";
print "<title>" . _formulize_DE_SAVEVIEW . "</title>\n";


print "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"" . XOOPS_URL . "/xoops.css\" />\n";
$themecss = xoops_getcss();
//$themecss = substr($themecss, 0, -6);
//$themecss .= ".css";
print "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"$themecss\" />\n";

print "<head>";
print "<body><center>"; 
print "<table width=100%><tr><td width=5%></td><td width=90%>";


$saveform = new xoopsThemeForm(_formulize_DE_SAVEVIEW, 'saveoptions', XOOPS_URL."/modules/formulize/include/save.php?fid=$fid&frid=$frid&lastloaded=$lastloaded&cols=$getcols&currentview=$currentview");

// need to build the list of available reports that can be saved.
// available are all their own saved reports, plus all published IF they have can update others turned on.

list($s_reports, $p_reports, $ns_reports, $np_reports) = availReports($uid, $groups, $fid, $frid);

// we are ignoring update_own_reports permission for now -- everyone can update their own reports
if($update_other_reports = $gperm_handler->checkRight("update_other_reports", $fid, $groups, $mid)) {
	// figure out which published reports belong to others, and include them in the list of reports we can update
	foreach($p_reports as $details) {
		if($details['report_uid'] != $uid) { $other_p_reports[] = $details; }
	}
	foreach($np_reports as $details) {
		if($details['sv_uid'] != $uid) { $other_np_reports[] = $details; }
	}
}

list($saveoptions, $defaultSave) = makeSaveList($s_reports, $ns_reports, $other_p_reports, $other_np_reports, $lastloaded, $viewselection);

$savelist = new xoopsFormSelect(_formulize_DE_SAVE_USECURRENT, 'savethis', $defaultSave);
$savelist->setExtra("onchange=\"javascript:newSaveView(this.form);;\"");
$savelist->addOptionArray($saveoptions);

//$scopeoptions = new xoopsFormElementTray(_formulize_DE_SAVE_SCOPE, "<br>");
$scope = new xoopsFormRadio(_formulize_DE_SAVE_SCOPE, 'scope', $_GET['currentview']);
//if($view_groupscope) { $scope2 = new xoopsFormRadio('', 'scope', $_GET['currentview']); }
//if($view_globalscope) { $scope3 = new xoopsFormRadio('', 'scope', $_GET['currentview']); }
//if($specificgroups) { $scope4 = new xoopsFormRadio('', 'scope', $_GET['currentview']); }

if($publish_reports OR $publish_globalscope) {
	$s1 = _formulize_DE_SAVE_SCOPE1;
	$s2 = _formulize_DE_SAVE_SCOPE2;
	$s3 = _formulize_DE_SAVE_SCOPE3;
	$s4 = _formulize_DE_SAVE_SCOPE4;
} else {
	$s1 = _formulize_DE_SAVE_SCOPE1_SELF;
	$s2 = _formulize_DE_SAVE_SCOPE2_SELF;
	$s3 = _formulize_DE_SAVE_SCOPE3_SELF;
	$s4 = _formulize_DE_SAVE_SCOPE4_SELF;
}
if($view_groupscope OR $view_globalscope OR $specificgroups) {
	$scope->addOption("mine", $s1 . "<br>");
} else {
	$scope->addOption("mine", $s1);
}
if($view_groupscope AND ($view_globalscope OR $specificgroups)) {
	$scope->addOption("group", $s2 . "<br>"); 
} elseif($view_groupscope) { 
	$scope->addOption("group", $s2); 
}
if($view_globalscope AND $specificgroups) { 
	$scope->addOption("all", $s3 . "<br>"); 
} elseif($view_globalscope) {
	$scope->addOption("all", $s3); 
}
if($specificgroups) { $scope->addOption($_GET['currentview'], $s4 . printSmart($groupNames)); }

//$scopeoptions->addElement($scope1);
//if($scope2) { $scopeoptions->addElement($scope2); }
//if($scope3) { $scopeoptions->addElement($scope3); }
//if($scope4) { $scopeoptions->addElement($scope4); }

$saveform->addElement($savelist);
$saveform->addElement($scope);

// add in list of groups if they have publishing options
// 1. get list of groups they can publish to
// 2. get publishing option for the lastloaded, if any
// 3. set defaults in list to the defaults

$publishgroups['donotpub'] = _formulize_DE_SAVE_NOPUB;

if($publish_reports) {
	foreach($groups as $key=>$groupid) {
		if($groupid != 2) {
			$thisgroup = $member_handler->getGroup($groupid);
			$publishgroups[$groupid] = $thisgroup->getVar('name');
		}
	}
}
if($publish_globalscope) {
	$allgroups = $member_handler->getGroups();
	foreach($allgroups as $id=>$details) {
		if($details->getVar('groupid') != 2) {
			$publishgroups[$details->getVar('groupid')] = $details->getVar('name');
		}
	}
}

if(count($publishgroups) > 1 ) { $pubflag = 1; }

// write in publishing options if any...
if($pubflag) {
	// get lastloaded publishing option
	if(strstr($viewselection, "old_") AND $viewselection != "new" AND $viewselection != "x1" AND $viewselection != "x2") { // legacy view
		$lastpub = q("SELECT report_groupids, report_ispublished FROM " . $xoopsDB->prefix("form_reports") . " WHERE report_id = '" . substr($viewselection, 5). "'");
		$lastpubgroups = explode("&*=%4#", $lastpub[0]['report_groupids']);
		if(strstr($lastpub[0]['report_ispublished'], "3") OR strstr($lastpub[0]['report_ispublished'], "2")) {
			$currentlock = 1;
		} else {
			$currentlock = 0;
		}
	} elseif($viewselection AND $viewselection != "new" AND $viewselection != "x1" AND $viewselection != "x2") { // new view
		$lastpub = q("SELECT sv_pubgroups, sv_lockcontrols FROM " . $xoopsDB->prefix("formulize_saved_views") . " WHERE sv_id = '" . substr($viewselection, 1) . "'");
		$lastpubgroups = explode(",", $lastpub[0]['sv_pubgroups']);
		$currentlock = $lastpub[0]['sv_lockcontrols'];
	} else {
		$lastpubgroups[0] = "donotpub";
		$currentlock = 1; // default to locked
	}

	$overlap = array_intersect($lastpubgroups, array_keys($publishgroups));
	if(count($overlap) == 0) { // ie: no default is actually part of the available groups
		$lastpubgroups[0] = "donotpub";
	}

	$size = count($publishgroups);
	if($size > 7) { $size = 7; }
	$pubgrouplist = new xoopsFormSelect(_formulize_DE_SAVE_PUBGROUPS, 'pubgrouplist', $lastpubgroups, $size, true);
	$pubgrouplist->addOptionArray($publishgroups);

	$lockcontrols = new xoopsFormElementTray(_formulize_DE_SAVE_LOCKCONTROLS, "<br>");
	$yes = new xoopsFormRadio('', 'lockcontrols', $currentlock); 
	$yes->addOption("1", _YES);
	$no = new xoopsFormRadio('', 'lockcontrols', $currentlock);
	$no->addOption("0", _NO);
	$lockcontrols->addElement($yes);
	$lockcontrols->addElement($no);

	$saveform->addElement($pubgrouplist);
	$saveform->addElement($lockcontrols);

}

$viewselection = new xoopsFormHidden("viewselection", "");
$saveform->addElement($viewselection);


$subButton = new xoopsFormButton('', 'submitx', _formulize_DE_SAVE_BUTTON, 'button');
$subButton->setExtra("onclick=\"javascript:saveSettings(this.form);\"");
$saveform->addElement($subButton);

if($pubflag) {
 	$saveform->insertBreak(_formulize_DE_SAVE_LOCKCONTROLS_HELP1 . " " . _formulize_DE_SAVE_LOCKCONTROLS_HELP2, "head");
}

saveJavascript($pubflag);

print $saveform->render();

print "</td><td width=5%></td></tr></table>";
print "</center></body>";
print "</HTML>";


// THIS FUNCTION RETURNS THE ARRAY OF OPTIONS FOR THE SAVE BOX, PLUS THE DEFAULT SELECTION
function makeSaveList($s_reports, $ns_reports, $other_p_reports, $other_np_reports, $lastloaded, $viewselection) {

// reports to include in the list are the saved ones, plus the other_p/np ones
// default value is only derived from the actual reports, so if viewselection is anything other than a valid report name, then the initial [save as new] option gets selected
$saveoptions["new"] = _formulize_DE_SAVE_AS;
if($s_reports[0] != "" OR $ns_reports[0] != "") { $saveoptions["x1"] = _formulize_DE_SAVED_VIEWS; }
foreach($s_reports as $report) {
	if($viewselection == "sold_" . $report['report_id'] OR $viewselection == "pold_" . $report['report_id']) { 
//		$saveoptions["sold_" . $report['report_id']] = "&nbsp;&nbsp;" . _formulize_DE_SAVE_UPDATE . $report['report_name'];
		$defaultSave = "sold_" . $report['report_id'];
	} //else {
	$saveoptions["sold_" . $report['report_id']] = "&nbsp;&nbsp;" . _formulize_DE_SAVE_REPLACE . $report['report_name'];
//	}
	if($lastloaded == "sold_" . $report['report_id'] OR $lastloaded == "pold_" . $report['report_id']) {
		$saveoptions["sold_" . $report['report_id']] .= " (" . _formulize_DE_SAVE_LASTLOADED . ")"; 
	}
}
foreach($ns_reports as $report) {
	if($viewselection == "s" . $report['sv_id'] OR $viewselection == "p" . $report['sv_id']) { 
//		$saveoptions["s" . $report['sv_id']] = "&nbsp;&nbsp;" . _formulize_DE_SAVE_UPDATE . $report['sv_name'] . " (" . _formulize_DE_SAVE_LASTLOADED . ")";
		$defaultSave = "s" . $report['sv_id'];
	} //else {
	$saveoptions["s" . $report['sv_id']] = "&nbsp;&nbsp;" . _formulize_DE_SAVE_REPLACE . $report['sv_name'];
//	}
	if($lastloaded == "s" . $report['sv_id'] OR $lastloaded == "p" . $report['sv_id']) {
		$saveoptions["s" . $report['sv_id']] .= " (" . _formulize_DE_SAVE_LASTLOADED . ")"; 
	}
}
if($other_p_reports[0] != "" OR $other_np_reports[0] != "") { $saveoptions["x2"] = _formulize_DE_PUB_VIEWS; }
foreach($other_p_reports as $report) {
	if($viewselection == "sold_" . $report['report_id'] OR $viewselection == "pold_" . $report['report_id']) { 
//		$saveoptions["sold_" . $report['report_id']] = "&nbsp;&nbsp;" . _formulize_DE_SAVE_UPDATE . $report['report_name'] . " (" . _formulize_DE_SAVE_LASTLOADED . ")";
		$defaultSave = "sold_" . $report['report_id'];
	} //else {
	$saveoptions["sold_" . $report['report_id']] = "&nbsp;&nbsp;" . _formulize_DE_SAVE_REPLACE . $report['report_name'];
//	}
	if($lastloaded == "sold_" . $report['report_id'] OR $lastloaded == "pold_" . $report['report_id']) {
		$saveoptions["sold_" . $report['report_id']] .= " (" . _formulize_DE_SAVE_LASTLOADED . ")"; 
	}
}
foreach($other_np_reports as $report) {
	if($viewselection == "s" . $report['sv_id'] OR $viewselection == "p" . $report['sv_id']) { 
//		$saveoptions["s" . $report['sv_id']] = "&nbsp;&nbsp;" . _formulize_DE_SAVE_UPDATE . $report['sv_name'] . " (" . _formulize_DE_SAVE_LASTLOADED . ")";
		$defaultSave = "s" . $report['sv_id'];
	} //else {
	$saveoptions["s" . $report['sv_id']] = "&nbsp;&nbsp;" . _formulize_DE_SAVE_REPLACE . $report['sv_name'];
//	}
	if($lastloaded == "s" . $report['sv_id'] OR $lastloaded == "p" . $report['sv_id']) {
		$saveoptions["s" . $report['sv_id']] .= " (" . _formulize_DE_SAVE_LASTLOADED . ")"; 
	}
}

$to_return[0] = $saveoptions;
$to_return[1] = $defaultSave;
return $to_return;
}


?>

