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

//THIS FILE HANDLES THE DISPLAY OF FORMS.  FUNCTIONS CAN BE CALLED FROM ANYWHERE (INTENDED FOR PAGEWORKS MODULE)

global $xoopsConfig;
// load the formulize language constants if they haven't been loaded already
	if ( file_exists(XOOPS_ROOT_PATH."/modules/formulize/language/".$xoopsConfig['language']."/main.php") ) {
		include_once XOOPS_ROOT_PATH."/modules/formulize/language/".$xoopsConfig['language']."/main.php";
	} else {
		include_once XOOPS_ROOT_PATH."/modules/formulize/language/english/main.php";
	}


// this function gets the element that is linked from a form to its parent form
// returns the ele_ids from form table
// note: no enforcement of only one link to a parent form.  You can screw up your framework structure and this function will dutifully return several links to the same parent form
function getParentLinks($fid, $frid) {

	global $xoopsDB;

	$check1 = q("SELECT fl_key1, fl_key2 FROM " . $xoopsDB->prefix("formulize_framework_links") . " WHERE fl_form1_id='$fid' AND fl_frame_id = '$frid' AND fl_unified_display = '1' AND fl_relationship = '3'");
	$check2 = q("SELECT fl_key1, fl_key2 FROM " . $xoopsDB->prefix("formulize_framework_links") . " WHERE fl_form2_id='$fid' AND fl_frame_id = '$frid' AND fl_unified_display = '1' AND fl_relationship = '2'");
	foreach($check1 as $c) {
		$source[] = $c['fl_key2'];
		$self[] = $c['fl_key1'];
	}
	foreach($check2 as $c) {
		$source[] = $c['fl_key1'];
		$self[] = $c['fl_key2'];
	}

	$to_return['source'] = $source;
	$to_return['self'] = $self;

	return $to_return;

}


// this function returns the captions and values that are in the DB for an existing entry
// $elements is used to specify a shortlist of elements to display.  Used in conjunction with the array option for $formform
function getEntryValues($entry, $formulize_mgr, $groups, $fid="", $elements="", $mid, $uid, $owner) {

	global $xoopsDB;

	if(!$mid) { $mid = getFormulizeModId(); }

	if(!$fid) {
		$fidq = q("SELECT id_form FROM " . $xoopsDB->prefix("formulize_form") . " WHERE id_req='$entry' LIMIT 0,1");
		$fid = $fidq[0]['id_form'];
	} else { // validate that $entry belongs to $fid, and if not, then return no previous values (this would be a mistake if it ever happened, or may indicate a hacking attempt)
		$fidq = q("SELECT * FROM " . $xoopsDB->prefix("formulize_form") . " WHERE id_req='$entry' AND id_form='$fid' LIMIT 0,1");
		if($fidq[0]['id_form'] == "") { return ""; }
	}

	$element_query = "";
	if(is_array($elements)) {
		$start = 1;
		foreach($elements as $element) {
			if($start) {
				$captionq = q("SELECT ele_caption FROM " . $xoopsDB->prefix("formulize") . " WHERE ele_id = '$element'"); 
				$caption = eregi_replace ("'", "`", $captionq[0]['ele_caption']);
				$caption = eregi_replace ("&quot;", "`", $caption);
				$caption = eregi_replace ("&#039;", "`", $caption);
				$element_query = " AND (ele_caption='$caption'";
				$start = 0;
			} else {
				$captionq = q("SELECT ele_caption FROM " . $xoopsDB->prefix("formulize") . " WHERE ele_id = '$element'"); 
				$caption = eregi_replace ("'", "`", $captionq[0]['ele_caption']);
				$caption = eregi_replace ("&quot;", "`", $caption);
				$caption = eregi_replace ("&#039;", "`", $caption);
				$element_query .= " OR ele_caption='$caption'";
			}
		}
		$element_query .= ")";
	}

	$viewquery = q("SELECT ele_caption, ele_value FROM " . $xoopsDB->prefix("formulize_form") . " WHERE id_req=$entry $element_query");

	// build query for display groups
	foreach($groups as $thisgroup) {
		$gq .= " OR ele_display LIKE '%,$thisgroup,%'";
	}

	// exclude private elements unless the user has view_private_elements permission
	$private_filter = "";
	$gperm_handler =& xoops_gethandler('groupperm');
	$view_private_elements = $gperm_handler->checkRight("view_private_elements", $fid, $groups, $mid);

	if(!$view_private_elements AND $uid != $owner) { 
		$private_filter = " AND ele_private=0";
	} 

	$allowedquery = q("SELECT ele_caption FROM " . $xoopsDB->prefix("formulize") . " WHERE id_form=$fid AND (ele_display=1 $gq) $private_filter"); 
	foreach($allowedquery as $onecap) {
		$onecap['ele_caption'] = str_replace("'", "`", $onecap['ele_caption']);
		$onecap['ele_caption'] = str_replace("&quot;", "`", $onecap['ele_caption']);
		$onecap['ele_caption'] = str_replace("&#039;", "`", $onecap['ele_caption']);
		$allowedcaps[] = $onecap['ele_caption'];
	}

	foreach($viewquery as $vq) {
		// check that this caption is an allowed caption before recording the value
		if(in_array($vq["ele_caption"], $allowedcaps)) {
		$prevEntry['captions'][] = $vq["ele_caption"];
		$prevEntry['values'][] = $vq["ele_value"];
	}
	}
	return $prevEntry;
	
}


function displayForm($formframe, $entry="", $mainform="", $done_dest="", $button_text="", $settings="", $titleOverride="", $overrideValue="", $overrideMulti="", $overrideSubMulti="", $viewallforms=0, $profileForm=0, $printall=0) {  // nmc 2007.03.24 - added $printall
//syntax:
//displayform($formframe, $entry, $mainform)
//$formframe is the id of the form OR title of the form OR name of the framework.  Can also be an array.  If it is an array, then flag 'formframe' is the $formframe variable, and flag 'elements' is an array of all the elements that are to be displayed.
//the array option is intended for displaying only part of a form at a time
//$entry is the numeric entry to display in the form -- if $entry is the word 'proxy' then it is meant to force a new form entry when the form is a single-entry form that the user already may have an entry in
//$mainform is the starting form to use, if this is a framework (can be specified by form id or by handle)
//$done_dest is the URL to go to after the form has been submitted
//Steps:
//1. identify form or framework
//2. if framework, check for unified display options
//3. if entry specified, then get data for that entry
//4. drawform with data if necessary

	global $xoopsDB, $xoopsUser, $myts;
	include_once XOOPS_ROOT_PATH.'/modules/formulize/include/functions.php';

	$GLOBALS['sfidsDrawn'] = array();

	global $sfidsDrawn;

	$groups = $xoopsUser ? $xoopsUser->getGroups() : XOOPS_GROUP_ANONYMOUS;

	$original_entry = $entry; // flag used to tell whether the function was called with an actual entry specified, ie: we're supposed to be editing this entry, versus the entry being set by coming back form a sub_form or other situation.

	$mid = getFormulizeModId();

	$currentURL = getCurrentURL();

	// identify form or framework

	$elements_allowed = "";
	if(is_array($formframe)) {
		$elements_allowed = $formframe['elements'];
		$formframetemp = $formframe['formframe'];
		unset($formframe);
		$formframe = $formframetemp;
	}

	list($fid, $frid) = getFormFramework($formframe, $mainform);

	if($_POST['deletesubsflag']) { // if deletion of sub entries requested
		foreach($_POST as $k=>$v) {
			if(strstr($k, "delbox")) {
				$subs_to_del[] = $v;
			}
		}
		if(count($subs_to_del) > 0) { 
			// get the form ID of the subform, based on the form ids
			$subfid = q("SELECT id_form FROM " . $xoopsDB->prefix("formulize_form") . " WHERE id_req=\"" . intval($subs_to_del[0]) . "\" LIMIT 0,1");
			deleteFormEntries($subs_to_del);
 			sendNotifications($subfid[0]['id_form'], "delete_entry", $subs_to_del, $mid, $groups);
		}
	}

	if($_POST['parent_form']) { // if we're coming back from a subform
		$entry = $_POST['parent_entry'];
		$fid = $_POST['parent_form'];
	}

	if($_POST['go_back_form']) { // we just received a subform submission
		$entry = $_POST['sub_submitted'];
		$fid = $_POST['sub_fid'];
		$go_back['form'] = $_POST['go_back_form'];
		$go_back['entry'] = $_POST['go_back_entry'];
	}

	// set $entry in the case of a form_submission where we were editing an entry (just in case that entry is not what is used to call this function in the first place -- ie: we're on a subform and the mainform has no entry specified, or we're clicking submit over again on a single-entry form where we started with no entry)
	$entrykey = "entry" . $fid;
	if((!$entry OR $entry=="proxy") AND $_POST[$entrykey]) { // $entrykey will only be set when *editing* an entry, not on new saves
		$entry = $_POST[$entrykey];
	}

	$member_handler =& xoops_gethandler('member');
	$gperm_handler = &xoops_gethandler('groupperm');
	if($profileForm === "new") { 
		 // spoof the $groups array based on the settings for the regcode that has been validated by register.php
		$reggroupsq = q("SELECT reg_codes_groups FROM " . XOOPS_DB_PREFIX . "_reg_codes WHERE reg_codes_code=\"" . $GLOBALS['regcode'] . "\"");
		$groups = explode("&8(%$", $reggroupsq[0]['reg_codes_groups']);
		if($groups[0] === "") { unset($groups); } // if a code has no groups associated with it, then kill the null value that will be in position 0 in the groups array.
		$groups[] = XOOPS_GROUP_USERS;
		$groups[] = XOOPS_GROUP_ANONYMOUS;
	}	
	$uid = $xoopsUser ? $xoopsUser->getVar('uid') : '0';

	$single_result = getSingle($fid, $uid, $groups, $member_handler, $gperm_handler, $mid);
	$single = $single_result['flag'];
	// if we're looking at a single entry form with no entry specified and where the user has no entry of their own, or it's an anonymous user, then set the entry based on a cookie if one is present
	// want to do this check here and override $entry prior to the security check since we don't like trusting cookies!
	$cookie_entry = (isset($_COOKIE['entryid_'.$fid]) AND !$entry AND $single AND ($single_result['entry'] == "" OR intval($uid) === 0)) ? $_COOKIE['entryid_'.$fid] : "";
	if($cookie_entry) { 
		// check to make sure the cookie_entry exists...
		$check_cookie_entry = q("SELECT id_req FROM " . $xoopsDB->prefix("formulize_form") . " WHERE id_req=" . intval($cookie_entry));
		if($check_cookie_entry[0]['id_req'] > 0) {
			$entry = $cookie_entry; 
		} else {
			$cookie_entry = "";
		}
	}
	$owner = ($cookie_entry AND $uid) ? $uid : getEntryOwner($entry); // if we're pulling a cookie value and there is a valid UID in effect, then assume this user owns the entry, otherwise, figure out who does own the entry
	$owner_groups =& $member_handler->getGroupsByUser($owner, FALSE);

	if(!$scheck = security_check($fid, $entry, $uid, $owner, $groups, $mid, $gperm_handler, $owner_groups) AND !$viewallforms AND !$profileForm) {
		print "<p>" . _NO_PERM . "</p>";
		return;
	}

	// main security check passed, so let's initialize flags	
	$go_back['url'] = $done_dest;
	if($single AND !$entry AND !$overrideMulti AND $profileForm !== "new") { // only adjust the active entry if we're not already looking at an entry, and there is no overrideMulti which can be used to display a new blank form even on a single entry form -- useful for when multiple anonymous users need to be able to enter information in a form that is "one per user" for registered users. -- the pressence of a cookie on the hard drive of a user will override other settings
		$entry = $single_result['entry'];
		$owner = getEntryOwner($entry);
		unset($owner_groups);
		$owner_groups =& $member_handler->getGroupsByUser($owner, FALSE);
	} 
	if($entry == "proxy") { $entry = ""; } // convert the proxy flag to the actual null value expected for new entry situations (do this after the single check!)
	$editing = is_numeric($entry); // will be true if there is an entry we're looking at already


	// set these arrays for the one form, and they are added to by the framework if it is in effect
	$fids[0] = $fid;
	if($entry) {
		$entries[$fid][0] = $entry;
	} else {
		$entries[$fid][0] = "";
	}
	
	if($frid) { 
		$linkResults = checkForLinks($frid, $fids, $fid, $entries, $gperm_handler, $owner_groups, $mid, $member_handler, $owner); 
		unset($entries);
		unset($fids);

		$fids = $linkResults['fids'];
		$entries = $linkResults['entries'];
		$sub_fids = $linkResults['sub_fids'];
		$sub_entries = $linkResults['sub_entries'];
	}

	// need to handle submission of entries 
	$formulize_mgr =& xoops_getmodulehandler('elements', 'formulize');

	$info_received_msg = 0;
	$info_continue = 0;
	if($entries[$fid][0]) { $info_continue = 1; }
	if($_POST['form_submitted'] AND $profileForm !== "new") {
		$info_received_msg = "1"; // flag for display of info received message
		include_once XOOPS_ROOT_PATH . "/modules/formulize/include/formread.php";
		$temp_entries = handleSubmission($formulize_mgr, $entries, $uid, $owner, $fid, $owner_groups, $groups, "", $elements_allowed, $mid);
		if($single OR $_POST['target_sub'] OR ($entries[$fid][0] AND ($original_entry OR ($_POST[$entrykey] AND !$_POST['back_from_sub']))) OR $overrideMulti OR ($_POST['go_back_form'] AND $overrideSubMulti)) { // if we just did a submission on a single form, or we just edited a multi, then assume the identity of the new entry.  Can be overridden by values passed to this function, to force multi forms to redisplay the just-saved entry.  Back_from_sub is used to override the override, when we're saving after returning from a multi-which is like editing an entry since entries are saved prior to going to a sub. -- Sept 4 2006: adding an entry in a subform forces us to stay on the same page too!
			$entry = $temp_entries[$fid][0];
			$entries = $temp_entries;
			$owner = getEntryOwner($entry);
			unset($owner_groups);
			$owner_groups =& $member_handler->getGroupsByUser($owner, FALSE);
			$info_continue = 1;
		} elseif(!$_POST['target_sub']) { // as long as the form was submitted and we're not going to a sub form, then display the info received message and carry on with a blank form
			if(!$original_entry) { // if we're on a multi-form where the display form function was called without an entry, then clear the entries and behave as if we're doing a new add
				unset($entries);
				unset($sub_entries);
				$entries[$fid][0] = "";
				$sub_entries[$sub_fids[0]][0] = "";
			}
			$info_continue = 2;
		}
		if(count($fids)>1) { // we have a unified one-to-one situation, so write the entries just created to the list of links
			writeLinks($temp_entries, $fids);
		}
	}

	// special use of $settings added August 2 2006 -- jwe -- break out of form if $settings so indicates
	// used to allow saving of information when you don't want the form itself to reappear
	if($settings == "{RETURNAFTERSAVE}" AND $_POST['form_submitted']) { return "returning_after_save"; }

      // need to add code here to switch some things around if we're on a subform for the first time (add)
	// note: double nested sub forms will not work currently, since on the way back to the intermediate level, the go_back values will not be set correctly
	// target_sub is only set when adding a sub entry, and adding sub entries is now down by the subform ui
      //if($_POST['target_sub'] OR $_POST['goto_sfid']) {
	if($_POST['goto_sfid']) {
		$info_continue = 0;
		if($_POST['goto_sfid']) {
			$new_fid = $_POST['goto_sfid'];
		} else {
			$new_fid = $_POST['target_sub'];
		}
		$go_back['form'] = $fid;
		$go_back['entry'] = $temp_entries[$fid][0];
		unset($entries);
		unset($fids);
		unset($sub_fids);
		unset($sub_entries);
		$fid = $new_fid;
		$fids[0] = $new_fid;
		if($_POST['target_sub']) { // if we're adding a new entry
			$entries[$new_fid][0] = "";
		} else { // if we're going to an existing entry
			$entries[$new_fid][0] = $_POST['goto_sub'];
		}
		$entry = $entries[$new_fid][0];
		$single_result = getSingle($fid, $uid, $groups, $member_handler, $gperm_handler, $mid);
		$single = $single_result['flag'];
		if($single AND !$entry) {
			$entry = $single_result['entry'];
			unset($entries);
			$entries[$fid][0] = $entry;
		}
		unset($owner);
		$owner = getEntryOwner($entries[$new_fid][0]); 
		$editing = is_numeric($entry); 
		unset($owner_groups);
		$owner_groups =& $member_handler->getGroupsByUser($owner, FALSE);
// DON'T UNDERSTAND WHY WE'RE CHECKING FOR LINKS WHEN A SUBFORM IS LOADED (NO SUPPORT INTENDED FOR DOUBLE NESTED SUBFORMS)
/*
		$linkResults = checkForLinks($frid, $fids, $fid, $entries, $gperm_handler, $owner_groups, $mid, $member_handler, $owner); 
		unset($entries);
		unset($fids);
		$fids = $linkResults['fids'];
		$entries = $linkResults['entries'];
		$sub_fids = $linkResults['sub_fids'];
		$sub_entries = $linkResults['sub_entries'];
*/
		$info_received_msg = 0;// never display this message when a subform is displayed the first time.	
		if($entry) { $info_continue = 1; }
		if(!$scheck = security_check($fid, $entries[$fid][0], $uid, $owner, $groups, $mid, $gperm_handler, $owner_groups) AND !$viewallforms) {
			print "<p>" . _NO_PERM . "</p>";
			return;
		}
      }

	// set the alldoneoverride if necessary -- August 22 2006
	$config_handler =& xoops_gethandler('config');
	$formulizeConfig = $config_handler->getConfigsByCat(0, $mid);
	// remove the all done button if the config option says 'no', and we're on a single-entry form, or we're looking at an existing entry, or we're on an overridden Multi-entry form
	$allDoneOverride = (!$formulizeConfig['all_done_singles'] AND !$profileForm AND (($single OR $overrideMulti OR $entry) AND !$_POST['target_sub'] AND !$_POST['goto_sfid'] AND !$_POST['deletesubsflag'] AND !$_POST['parent_form'])) ? true : false;
	if($allDoneOverride AND $_POST['form_submitted']) {
		drawGoBackForm($go_back, $currentURL, $settings, $entry);
		print "<script type=\"text/javascript\">window.document.go_parent.submit();</script>\n";
	}
	// we cannot have the back logic above invoked when dealing with a subform, but if the override is supposed to be in place, then we need to invoke it
	if(!$allDoneOverride AND !$formulizeConfig['all_done_singles'] AND !$profileForm AND ($_POST['target_sub'] OR $_POST['goto_sfid'] OR $_POST['deletesubsflag'] OR $_POST['parent_form']) AND ($single OR $entry OR $overrideMulti)) {
		$allDoneOverride = true;
	}


	include_once XOOPS_ROOT_PATH."/class/xoopsformloader.php";
	include_once XOOPS_ROOT_PATH . "/include/functions.php";

/*	if($uid==1) {
	print "Forms: ";
	print_r($fids);
	print "<br>Entries: ";
	print_r($entries);
	print "<br>Subforms: ";
	print_r($sub_fids);
	print "<br>Subentries: ";
	print_r($sub_entries); // debug block - ONLY VISIBLE TO USER 1 RIGHT NOW 
	} */
	$title = "";
	foreach($fids as $this_fid) {

		if(!$scheck = security_check($this_fid, $entries[$this_fid][0], $uid, $owner, $groups, $mid, $gperm_handler, $owner_groups) AND !$viewallforms) {
			continue;
		}

      	unset($prevEntry);
      	if($entries[$this_fid]) { 	// if there is an entry, then get the data for that entry
      		$prevEntry = getEntryValues($entries[$this_fid][0], $formulize_mgr, $groups, $this_fid, $elements_allowed, $mid, $uid, $owner); 
      	}

      	// display the form

      	//get the form title: (do only once)
		$firstform = 0;
		if(!$form) {

			$firstform = 1; 	      	
			$title = trans(getFormTitle($this_fid));
	      	$form = new XoopsThemeForm($title, 'formulize', "$currentURL", "post", true);
			$form->setExtra("enctype='multipart/form-data'"); // imp�ratif!

			$update_own_entry = $gperm_handler->checkRight("update_own_entry", $fid, $groups, $mid);
			$update_other_entries = $gperm_handler->checkRight("update_other_entries", $fid, $groups, $mid);
			if($single == "group" AND $update_own_entry AND $entry == $single_result['entry']) {
				$update_other_entries = true;
			}

			if(is_array($settings)) { $form = writeHiddenSettings($settings, $form); }
			$form->addElement (new XoopsFormHidden ('ventry', $settings['ventry'])); // necessary to trigger the proper reloading of the form page, until Done is called and that form does not have this flag.

			// include who the entry belongs to and the date
			// include acknowledgement that information has been updated if we have just done a submit
			// form_meta includes: last_update, created, last_update_by, created_by

			if(!$profileForm AND $titleOverride != "all") {
      			// build the break HTML and then add the break to the form
				if(strstr($currentURL, "printview.php")) {
					$breakHTML = "<center>";
				} else {
	      			$breakHTML = "<center><p><b>";
      				if($info_received_msg) { $breakHTML .= _formulize_INFO_SAVED . "&nbsp;"; }
      				if($info_continue == 1) {
      					$breakHTML .= _formulize_INFO_CONTINUE1 . "</b></p>";
      				} elseif($info_continue == 2) {
	      				$breakHTML .=  _formulize_INFO_CONTINUE2 . "</b></p>";
      				} else {
      					$breakHTML .=  _formulize_INFO_MAKENEW . "</b></p>";
      				}
				}
      			$breakHTML .= "</center><table cellpadding=5 width=100%><tr><td width=50% style=\"vertical-align: bottom;\">";

      			$breakHTML .= "<p><b>" . _formulize_FD_ABOUT . "</b><br>";
      			
      			if($entries[$this_fid][0]) {
      				if(!$member_handler) { $member_handler =& xoops_gethandler('member'); }
      				$form_meta = getMetaData($entries[$this_fid][0], $member_handler);
      				$breakHTML .= _formulize_FD_CREATED . $form_meta['created_by'] . " " . _formulize_TEMP_ON . " " . $form_meta['created'] . "<br>" . _formulize_FD_MODIFIED . $form_meta['last_update_by'] . " " . _formulize_TEMP_ON . " " . $form_meta['last_update'] . "</p>";
      			} else {
      				$breakHTML .= _formulize_FD_NEWENTRY . "</p>";
      			}

      			$breakHTML .= "</td><td width=50% style=\"vertical-align: bottom;\">"; //<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p></td><td>";
				if(strstr($currentURL, "printview.php") OR (!$gperm_handler->checkRight("add_own_entry", $fid, $groups, $mid) AND !$gperm_handler->checkRight("add_proxy_entries", $fid, $groups, $mid))) {
					$breakHTML .= "<p>";
				} else {
					// get save and button button options
					$save_button_text = "";
					$done_button_text = "";
					if(is_array($button_text)) {
						$save_button_text = $button_text[1];
						$done_button_text = $button_text[0];						
					} else { 
						$done_button_text = $button_text;						
					}
					if(!$done_button_text AND !$allDoneOverride) {
						$done_button_text = _formulize_INFO_DONE1 . _formulize_DONE . _formulize_INFO_DONE2;
					} elseif($done_button_text != "{NOBUTTON}" AND !$allDoneOverride) {
						$done_button_text = _formulize_INFO_DONE1 . $done_button_text . _formulize_INFO_DONE2;
					// check to see if the user is allowed to modify the existing entry, and if they're not, then we have to draw in the all done button so they have a way of getting back where they're going
					} elseif(($entry AND (($owner == $uid AND $update_own_entry) OR ($owner != $uid AND $update_other_entries))) OR !$entry) {
						$done_button_text = "";
					} else {
						$done_button_text = _formulize_INFO_DONE1 . _formulize_DONE . _formulize_INFO_DONE2;					
					}


					$nosave = false;
					if(!$save_button_text AND (($entry AND (($owner == $uid AND $update_own_entry) OR ($owner != $uid AND $update_other_entries))) OR !$entry)) {
						$save_button_text = _formulize_INFO_SAVEBUTTON;
					} elseif($save_button_text != "{NOBUTTON}" AND (($entry AND (($owner == $uid AND $update_own_entry) OR ($owner != $uid AND $update_other_entries))) OR !$entry)) {
						$save_button_text = _formulize_INFO_SAVE1 . $save_button_text . _formulize_INFO_SAVE2;
					} else {
						$save_button_text = _formulize_INFO_NOSAVE;
						$nosave = true;
					}
            			$breakHTML .= "<p>" . $save_button_text;
					if($done_button_text) {
             				$breakHTML .= "<br>" . $done_button_text;
            			}
				}
      			$breakHTML .= "</p></td></tr></table>";
      			$form->insertBreak($breakHTML, "even");
			} elseif($profileForm) {
				// if we have a profile form, put the profile fields at the top of the form, populated based on the DB values from the _users table
				$form = addProfileFields($form, $profileForm);
			}

			drawJavascript($nosave);

		}

		if($titleOverride=="1" AND !$firstform) { // set onetooneTitle flag to 1 when function invoked to force drawing of the form title over again
			$title = trans(getFormTitle($this_fid));
			$form->insertBreak("<table><th>$title</th></table>","");
		}

		// if this form has a parent, then determine the $parentLinks
		if($go_back['form'] AND !$parentLinks[$this_fid]) {
			$parentLinks[$this_fid] = getParentLinks($this_fid, $frid);
		}
																														
      	$form = compileElements($this_fid, $form, $formulize_mgr, $prevEntry, $entries[$this_fid][0], $go_back, $parentLinks[$this_fid], $owner_groups, $groups, $overrideValue, $elements_allowed, $profileForm, $frid, $mid, $uid, $sub_entries, $sub_fids, $member_handler, $gperm_handler, $title);

	} // end of for each fids

	if(!is_object($form)) { exit("Error: the form cannot be displayed.  Does the current group have permission to access the form?"); }

     	// DRAW IN THE SPECIAL UI FOR A SUBFORM LINK (ONE TO MANY)		
    	if(count($sub_fids) > 0) { // if there are subforms, then draw them in...only once we have a bonafide entry in place already
      	// draw in special params for this form
		$form->addElement (new XoopsFormHidden ('target_sub', ''));
		$form->addElement (new XoopsFormHidden ('numsubents', 1));
		$form->addElement (new XoopsFormHidden ('del_subs', ''));
		$form->addElement (new XoopsFormHidden ('goto_sub', ''));
		$form->addElement (new XoopsFormHidden ('goto_sfid', ''));
		foreach($sub_fids as $sfid) {
			// only draw in the subform UI if the subform hasn't been drawn in previously, courtesy of a subform element in the form.
			// Subform elements are recommended since they provide 1. specific placement, 2. custom captions, 3. direct choice of form elements to include
			if(in_array($sfid, $sfidsDrawn) OR $elements_allowed OR (!$scheck = security_check($sfid, "", $uid, $owner, $groups, $mid, $gperm_handler, $owner_groups) AND !$viewallforms)) { // no entry passed so this will simply check whether they have permission for the form or not
				continue;
			}
			$subUICols = drawSubLinks($sfid, $sub_entries, $uid, $groups, $member_handler, $frid, $gperm_handler, $mid, $fid, $entry);
			unset($subLinkUI);
			$subLinkUI = new XoopsFormLabel($subUICols['c1'], $subUICols['c2']);
			$form->addElement($subLinkUI);
    		}
     	} 


	// draw in proxy box if necessary (only if they have permission and only on new entries, not on edits)
	if($gperm_handler->checkRight("add_proxy_entries", $fid, $groups, $mid) AND !$entries[$fid][0]) {
		if(!$member_handler) { $member_handler =& xoops_gethandler('member'); }
		$form = addProxyList($form, $groups, $member_handler, $gperm_handler, $fid, $mid);
	}

	// add flag to indicate that the form has been submitted
	$form->addElement (new XoopsFormHidden ('form_submitted', "1"));
	if($go_back['form']) { // if this is set, then we're doing a subform, so put in a flag to prevent the parent from being drawn again on submission
		$form->addElement (new XoopsFormHidden ('sub_fid', $fid));
		$form->addElement (new XoopsFormHidden ('sub_submitted', $entries[$fid][0]));
		$form->addElement (new XoopsFormHidden ('go_back_form', $go_back['form']));
		$form->addElement (new XoopsFormHidden ('go_back_entry', $go_back['entry']));
	}
	
	// draw in the submitbutton if necessary
	if($entry) { // existing entry, if it's their own and they can update their own, or someone else's and they can update someone else's
		if(($owner == $uid AND $update_own_entry) OR ($owner != $uid AND $update_other_entries)) {
			$form = addSubmitButton($form, _formulize_SAVE, $go_back, $currentURL, $button_text, $settings, $temp_entries[$this_fid][0], $fids, $formframe, $mainform, $entry, $profileForm, $elements_allowed, $allDoneOverride, $printall); //nmc 2007.03.24 - added $printall
		} else {
			$form = addSubmitButton($form, '', $go_back, $currentURL, $button_text, $settings, $temp_entries[$this_fid][0], $fids, $formframe, $mainform, $entry, $profileForm, $elements_allowed, false, $printall); //nmc 2007.03.24 - added $printall
		}
	} else { // new entry
		if($gperm_handler->checkRight("add_own_entry", $fid, $groups, $mid) OR $gperm_handler->checkRight("add_proxy_entries", $fid, $groups, $mid)) {
			$form = addSubmitButton($form, _formulize_SAVE, $go_back, $currentURL, $button_text, $settings, $temp_entries[$this_fid][0], $fids, $formframe, $mainform, $entry, $profileForm, $elements_allowed, $allDoneOverride, $printall); //nmc 2007.03.24 - added $printall
		} else {
			$form = addSubmitButton($form, '', $go_back, $currentURL, $button_text, $settings, $temp_entries[$this_fid][0], $fids, $formframe, $mainform, $entry, $profileForm, $elements_allowed, false, $printall); //nmc 2007.03.24 - added $printall
		}
	}

	// saving message
	print "<div id=savingmessage style=\"display: none; position: absolute; width: 100%; right: 0px; text-align: center; padding-top: 50px;\">\n";
	if ( file_exists(XOOPS_ROOT_PATH."/modules/formulize/images/saving-".$xoopsConfig['language'].".gif") ) {
		print "<img src=\"" . XOOPS_URL . "/modules/formulize/images/saving-" . $xoopsConfig['language'] . ".gif\">\n";
	} else {
		print "<img src=\"" . XOOPS_URL . "/modules/formulize/images/saving-english.gif\">\n";
	}
	print "</div>\n<div id=formulizeform>".$form->render()."</div>";
	// if we're in Drupal, include the main XOOPS js file, so the calendar will work if present...
	// assumption is that the calendar javascript has already been included by the datebox due to no
	// $xoopsTpl being in effect in Drupal -- this assumption will fail if Drupal is displaying a pageworks
	// page that uses the $xoopsTpl, for instance.  (Date select box file itself checks for $xoopsTpl)
	global $user;
	if(is_object($user)) {
		print "<script type=\"text/javascript\" src=\"" . XOOPS_URL . "/include/xoops.js\"></script>\n";
	}
}

// THIS FUNCTION ADDS THE SPECIAL PROFILE FIELDS TO THE TOP OF A PROFILE FORM
function addProfileFields($form, $profileForm) {
	// add... 
	// username
	// full name
	// e-mail
	// timezone
	// password

	global $xoopsUser, $xoopsConfig, $xoopsConfigUser;
	$config_handler =& xoops_gethandler('config');
	$xoopsConfigUser =& $config_handler->getConfigsByCat(XOOPS_CONF_USER);
	$user_handler =& xoops_gethandler('user');
	$thisUser = $user_handler->get($profileForm);

	// initialize $thisUser
	if($thisUser) {
		$thisUser_name = $thisUser->getVar('name', 'E');
		$thisUser_uname = $thisUser->getVar('uname');
		$thisUser_timezone_offset = $thisUser->getVar('timezone_offset');
		$thisUser_email = $thisUser->getVar('email');
		$thisUser_uid = $thisUser->getVar('uid');
		$thisUser_viewemail = $thisUser->user_viewemail();
		$thisUser_umode = $thisUser->getVar('umode');
		$thisUser_uorder = $thisUser->getVar('uorder');
		$thisUser_notify_method = $thisUser->getVar('notify_method');
		$thisUser_notify_mode = $thisUser->getVar('notify_mode');
		$thisUser_user_sig = $thisUser->getVar('user_sig', 'E');
		$thisUser_attachsig = $thisUser->getVar('attachsig');
	} else { // anon user
		$thisUser_name = $GLOBALS['name']; //urldecode($_GET['name']);
		$thisUser_uname = $GLOBALS['uname']; //urldecode($_GET['uname']);
		$thisUser_timezone_offset = isset($GLOBALS['timezone_offset']) ? $GLOBALS['timezone_offset'] : $xoopsConfig['default_TZ']; // isset($_GET['timezone_offset']) ? urldecode($_GET['timezone_offset']) : $xoopsConfig['default_TZ'];
		$thisUser_email = $GLOBALS['email']; //urldecode($_GET['email']);
		$thisUser_viewemail = $GLOBALS['user_viewemail']; //urldecode($_GET['viewemail']);
		$thisUser_uid = 0;
		$agree_disc = $GLOBALS['agree_disc'];
	}

		include_once XOOPS_ROOT_PATH . "/language/" . $xoopsConfig['language'] . "/user.php";

	$form->insertBreak(_formulize_ACTDETAILS, "head");

	// following borrowed from edituser.php
	if($profileForm == "new") {
		// 'new' should ONLY be coming from the modified register.php file that the registration codes module uses
		// ie: we are assuming registration codes is installed
		$form->addElement(new XoopsFormHidden('userprofile_regcode', $GLOBALS['regcode']));
		$uname_size = $xoopsConfigUser['maxuname'] < 100 ? $xoopsConfigUser['maxuname'] : 100;
		$labelhelptext = _formulize_USERNAME_HELP1; // set it to a variable so we can test for its existence; don't want to print this stuff if there's no translation
		$labeltext = $labelhelptext == "" ? _US_NICKNAME : _US_NICKNAME . _formulize_USERNAME_HELP1 . $xoopsConfigUser['minuname'] . _formulize_USERNAME_HELP2 . $uname_size . _formulize_USERNAME_HELP3;
		$uname_label = new XoopsFormText($labeltext, 'userprofile_uname', $uname_size, $uname_size, $thisUser_uname);
		$form->addElement($uname_label, 1);
	} else {
		$uname_label = new XoopsFormLabel(_US_NICKNAME, $thisUser_uname);
		$form->addElement($uname_label);
	}
	$passlabel = $profileForm == "new" ? _formulize_TYPEPASSTWICE_NEW : _formulize_TYPEPASSTWICE_CHANGE;
	$passlabel .= $xoopsConfigUser['minpass'] . _formulize_PASSWORD_HELP1;
	$pwd_tray = new XoopsFormElementTray(_US_PASSWORD.'<br />'.$passlabel);
	$pwd_text = new XoopsFormPassword('', 'userprofile_password', 10, 32);
	$pwd_text2 = new XoopsFormPassword('', 'userprofile_vpass', 10, 32);
	$pass_required = $profileForm == "new" ? 1 : 0;
	$pwd_tray->addElement($pwd_text, $pass_required);
	$pwd_tray->addElement($pwd_text2, $pass_required);
	$form->addElement($pwd_tray, $pass_required);
	$name_text = new XoopsFormText(_US_REALNAME, 'userprofile_name', 30, 60, $thisUser_name);
	$form->addElement($name_text, 1);
	$email_tray = new XoopsFormElementTray(_US_EMAIL, '<br />');
	if ($xoopsConfigUser['allow_chgmail'] == 1 OR $profileForm == "new") {
      	$email_text = new XoopsFormText('', 'userprofile_email', 30, 60, $thisUser_email);
		$email_tray->addElement($email_text, 1);
	} else {
        	$email_text = new XoopsFormLabel('', $thisUser_email);
		$email_tray->addElement($email_text);
	}
	$email_cbox_value = $thisUser_viewemail ? 1 : 0;
	$email_cbox = new XoopsFormCheckBox('', 'userprofile_user_viewemail', $email_cbox_value);
	$email_cbox->addOption(1, _US_ALLOWVIEWEMAIL);
	$email_tray->addElement($email_cbox);
	$form->addElement($email_tray, 1);
	$timezone_select = new XoopsFormSelectTimezone(_US_TIMEZONE, 'userprofile_timezone_offset', $thisUser_timezone_offset);
	$form->addElement($timezone_select);

	if($profileForm != "new") {
      	$umode_select = new XoopsFormSelect(_formulize_CDISPLAYMODE, 'userprofile_umode', $thisUser_umode);
      	$umode_select->addOptionArray(array('nest'=>_NESTED, 'flat'=>_FLAT, 'thread'=>_THREADED));
      	$form->addElement($umode_select);
      	$uorder_select = new XoopsFormSelect(_formulize_CSORTORDER, 'userprofile_uorder', $thisUser_uorder);
      	$uorder_select->addOptionArray(array(XOOPS_COMMENT_OLD1ST => _OLDESTFIRST, XOOPS_COMMENT_NEW1ST => _NEWESTFIRST));
      	$form->addElement($uorder_select);
      	include_once XOOPS_ROOT_PATH . "/language/" . $xoopsConfig['language'] . '/notification.php';
      	include_once XOOPS_ROOT_PATH . '/include/notification_constants.php';
      	$notify_method_select = new XoopsFormSelect(_NOT_NOTIFYMETHOD, 'userprofile_notify_method', $thisUser_notify_method);
      	$notify_method_select->addOptionArray(array(XOOPS_NOTIFICATION_METHOD_DISABLE=>_NOT_METHOD_DISABLE, XOOPS_NOTIFICATION_METHOD_PM=>_NOT_METHOD_PM, XOOPS_NOTIFICATION_METHOD_EMAIL=>_NOT_METHOD_EMAIL));
      	$form->addElement($notify_method_select);
      	$notify_mode_select = new XoopsFormSelect(_NOT_NOTIFYMODE, 'userprofile_notify_mode', $thisUser_notify_mode);
      	$notify_mode_select->addOptionArray(array(XOOPS_NOTIFICATION_MODE_SENDALWAYS=>_NOT_MODE_SENDALWAYS, XOOPS_NOTIFICATION_MODE_SENDONCETHENDELETE=>_NOT_MODE_SENDONCE, XOOPS_NOTIFICATION_MODE_SENDONCETHENWAIT=>_NOT_MODE_SENDONCEPERLOGIN));
      	$form->addElement($notify_mode_select);
      	$sig_tray = new XoopsFormElementTray(_US_SIGNATURE, '<br />');
      	include_once XOOPS_ROOT_PATH . '/include/xoopscodes.php';
      	$sig_tarea = new XoopsFormDhtmlTextArea('', 'userprofile_user_sig', $thisUser_user_sig);
      	$sig_tray->addElement($sig_tarea);
      	$sig_cbox_value = $thisUser_attachsig ? 1 : 0;
      	$sig_cbox = new XoopsFormCheckBox('', 'userprofile_attachsig', $sig_cbox_value);
      	$sig_cbox->addOption(1, _US_SHOWSIG);
      	$sig_tray->addElement($sig_cbox);
      	$form->addElement($sig_tray);
	} else { // display only on new account creation...
		if ($xoopsConfigUser['reg_dispdsclmr'] != 0 && $xoopsConfigUser['reg_disclaimer'] != '') {
			$disc_tray = new XoopsFormElementTray(_US_DISCLAIMER, '<br />');
			$disc_text = new XoopsFormTextarea('', 'disclaimer', trans($xoopsConfigUser['reg_disclaimer']), 8);
			$disc_text->setExtra('readonly="readonly"');
			$disc_tray->addElement($disc_text);
			$agree_chk = new XoopsFormCheckBox('', 'userprofile_agree_disc', $agree_disc);
			$agree_chk->addOption(1, "<span style=\"font-size: 14pt;\">" . _US_IAGREE . "</span>");
			$disc_tray->addElement($agree_chk);
			$form->addElement($disc_tray);
		}
		$form->addElement(new XoopsFormHidden("op", "newuser"));
	}

	$uid_check = new XoopsFormHidden("userprofile_uid", $thisUser_uid);
	$form->addElement($uid_check);
	$form->insertBreak(_formulize_PERSONALDETAILS, "head");

	return $form;

} 


// add the submit button to a form
function addSubmitButton($form, $subButtonText, $go_back="", $currentURL, $button_text, $settings, $entry, $fids, $formframe, $mainform, $cur_entry, $profileForm, $elements_allowed="", $allDoneOverride=false, $printall=0) { //nmc 2007.03.24 - added $printall

	if(strstr($currentURL, "printview.php")) { // don't do anything if we're on the print view
		return $form;
	} else {

	drawGoBackForm($go_back, $currentURL, $settings, $entry);

	if(!$button_text OR ($button_text == "{NOBUTTON}" AND $go_back['form'] > 0)) { // presence of a goback form (ie: parent form) overrides {NOBUTTON} -- assumption is the save button will not also be overridden at the same time
		$button_text = _formulize_DONE; 
	} elseif(is_array($button_text)) {
		if(!$button_text[0]) { 
			$done_text_temp = _formulize_DONE; 
		} else {
			$done_text_temp = $button_text[0];
		}
		if(!$button_text[1]) { 
			$save_text_temp = _formulize_SAVE; 
		} else {
			$save_text_temp = $button_text[1];
		}
	}

	// override -- the "no-all-done-button" config option turns off the all done button and changes save into a save-and-leave button

	// need to grab the $nosubforms variable created by the multiple page function, so we know to put the printable view button (and nothing else) on the screen for multipage forms
	global $nosubforms;
	if(!$profileForm AND ($save_text_temp != "{NOBUTTON}" OR $nosubforms)) { // do not use printable button for profile forms, or forms where there is no Save button (ie: a non-standard saving process is in use and access to the normal printable option may be prohibited)
		$printbutton = new XoopsFormButton('', 'printbutton', _formulize_PRINTVIEW, 'button');
		$printbutton->setExtra("onclick='javascript:PrintPop();'");
		$rendered_buttons = $printbutton->render(); // nmc 2007.03.24 - added
		if ($printall) {																					// nmc 2007.03.24 - added
			$printallbutton = new XoopsFormButton('', 'printallbutton', _formulize_PRINTALLVIEW, 'button');	// nmc 2007.03.24 - added
			$printallbutton->setExtra("onclick='javascript:PrintAllPop();'");								// nmc 2007.03.24 - added
			$rendered_buttons .= "&nbsp;&nbsp;&nbsp;" . $printallbutton->render();							// nmc 2007.03.24 - added
			}
		$buttontray = new XoopsFormElementTray($rendered_buttons, "&nbsp;"); // nmc 2007.03.24 - amended [nb: FormElementTray 'caption' is actually either 1 or 2 buttons]
	} else {
		$buttontray = new XoopsFormElementTray("", "&nbsp;");
	}
	if($subButtonText == _formulize_SAVE) { // _formulize_SAVE is passed only when the save button is allowed to be drawn
		if($save_text_temp) { $subButtonText = $save_text_temp; }
		if($subButtonText != "{NOBUTTON}") {
			$saveButton = new XoopsFormButton('', 'submitx', $subButtonText, 'button'); // doesn't use name submit since that conflicts with the submit javascript function
			$saveButton->setExtra("onclick=javascript:validateAndSubmit();");
			$buttontray->addElement($saveButton);
		}
	}
	
	if((($button_text != "{NOBUTTON}" AND !$done_text_temp) OR (isset($done_text_temp) AND $done_text_temp != "{NOBUTTON}")) AND !$allDoneOverride) { 
		if($done_text_temp) { $button_text = $done_text_temp; }
		$donebutton = new XoopsFormButton('', 'donebutton', $button_text, 'button');
		$donebutton->setExtra("onclick=javascript:verifyDone();");
		$buttontray->addElement($donebutton); 
	}

	if(!$profileForm) { // do not use printable button for profile forms
		$newcurrentURL= XOOPS_URL . "/modules/formulize/printview.php";
		print "<form name='printview' action='".$newcurrentURL."' method=post target=_blank>\n";
		print "<input type=hidden name=lastentry value=".$cur_entry.">";
		if($go_back['form']) { // we're on a sub, so display this form only
			print "<input type=hidden name=formframe value=".$fids[0].">";	
		} else { // otherwise, display like normal
			print "<input type=hidden name=formframe value='".$formframe."'>";	
			print "<input type=hidden name=mainform value='".$mainform."'>";
		}
		if(is_array($elements_allowed)) {
			$ele_allowed = implode(",",$elements_allowed);
			print "<input type=hidden name=elements_allowed value='".$ele_allowed."'>";
		}
		print "</form>";
		//added by Cory Aug 27, 2005 to make forms printable
	}

	$trayElements = $buttontray->getElements();
	if(count($trayElements) > 0 OR $nosubforms) {
		$form->addElement($buttontray);
	}
	return $form;
	}
}

// this function draws in the hidden form that handles the All Done logic that sends user off the form
function drawGoBackForm($go_back, $currentURL, $settings, $entry) {
	if($go_back['url'] == "" AND !isset($go_back['form'])) { // there are no back instructions at all, then make the done button go to the front page of whatever is going on in pageworks
		print "<form name=go_parent action=\"$currentURL\" method=post>"; //onsubmit=\"javascript:verifyDone();\" method=post>";
		if(is_array($settings)) { writeHiddenSettings($settings); }
		print "<input type=hidden name=lastentry value=$entry>";
		print "</form>";
	}
	if($go_back['form']) { // parent form overrides specified back URL
		print "<form name=go_parent action=\"$currentURL\" method=post>"; //onsubmit=\"javascript:verifyDone();\" method=post>";
		print "<input type=hidden name=parent_form value=" . $go_back['form'] . ">";
		print "<input type=hidden name=parent_entry value=" . $go_back['entry'] . ">";
		print "<input type=hidden name=ventry value=" . $settings['ventry'] . ">";
		if(is_array($settings)) { writeHiddenSettings($settings); }
		print "<input type=hidden name=lastentry value=$entry>";
		print "</form>";
	} elseif($go_back['url']) {
		print "<form name=go_parent action=\"" . $go_back['url'] . "\" method=post>"; //onsubmit=\"javascript:verifyDone();\" method=post>";
		if(is_array($settings)) { writeHiddenSettings($settings); }		
		print "<input type=hidden name=lastentry value=$entry>";
		print "</form>";
	} 
}

// this function draws in the UI for sub links
function drawSubLinks($sfid, $sub_entries, $uid, $groups, $member_handler, $frid, $gperm_handler, $mid, $fid, $entry, $customCaption="", $customElements="") {

	global $xoopsDB, $nosubforms;
	$GLOBALS['framework'] = $frid;

	include_once XOOPS_ROOT_PATH . "/modules/formulize/include/extract.php";

	// check for adding of a sub entry, and handle accordingly -- added September 4 2006
	if($_POST['target_sub']) {
		$elementq = q("SELECT fl_key1, fl_key2, fl_common_value FROM " . $xoopsDB->prefix("formulize_framework_links") . " WHERE fl_frame_id=" . intval($frid) . " AND fl_form2_id=" . intval($fid) . " AND fl_form1_id=" . intval($_POST['target_sub']));
		if(count($elementq) == 0) {
			$elementq = q("SELECT fl_key2, fl_key1, fl_common_value FROM " . $xoopsDB->prefix("formulize_framework_links") . " WHERE fl_frame_id=" . intval($frid) . " AND fl_form1_id=" . intval($fid) . " AND fl_form2_id=" . intval($_POST['target_sub']));
			$element_to_write = $elementq[0]['fl_key2'];
			$value_source = $elementq[0]['fl_key1'];
		} else {
			$element_to_write = $elementq[0]['fl_key1'];
			$value_source = $elementq[0]['fl_key2'];
		}
		// need to handle things differently depending on whether it's a common value or a linked selectbox type of link
		// uid links need to result in a "new" value in the displayElement boxes -- odd things will happen if people start adding linked values to entries that aren't theirs!
		if($element_to_write != 0) {
			if($elementq[0]['fl_common_value']) {
				// grab the value from the parent element -- assume that it is a textbox of some kind!
				$value_to_write = $_POST['ele_'.$value_source];
			} else {
				// have to create a proper linked selectbox value to write 
				// not tested throughly.  Only meant to work with textbox elements on mainform linked to selectboxes on subform.  Only one selection in the selectbox allowed.
				$caption_to_write = q("SELECT ele_caption FROM " . $xoopsDB->prefix("formulize") . " WHERE ele_id = " . intval($value_source));
				$caption_to_write[0]['ele_caption'] = str_replace("'", "`", $caption_to_write[0]['ele_caption']);
				$caption_to_write[0]['ele_caption'] = str_replace("&quot;", "`", $caption_to_write[0]['ele_caption']);
				$caption_to_write[0]['ele_caption'] = str_replace("&#039;", "`", $caption_to_write[0]['ele_caption']);
				$ele_id_to_write = q("SELECT ele_id FROM " . $xoopsDB->prefix("formulize_form") . " WHERE id_form = " . intval($fid) . " AND id_req=" . intval($entry) . " AND ele_caption = '" . $caption_to_write[0]['ele_caption'] . "'");
				$value_to_write = $fid . "#*=:*" . $caption_to_write[0]['ele_caption'] . "#*=:*" . $ele_id_to_write[0]['ele_id'];
			}
			$sub_entry_new = "";
			include_once XOOPS_ROOT_PATH . "/modules/pageworks/include/functions.php";
			for($i=0;$i<$_POST['numsubents'];$i++) {
				$sub_entry_written[] = writeElementValue($_POST['target_sub'], $element_to_write, "new", $value_to_write, "append", "", true); // append has no effect when no prevValue given (prevValue is the second last param).  Last param is override that allows writing to linked selectboxes.  We have to have prepped the value first!
			}
		} else {
			$sub_entry_new = "new";
			$sub_entry_written = "";
		}
	}	

	// need to do a number of checks here, including looking for single status on subform, and not drawing in add another if there is an entry for a single
			
	$sub_single_result = getSingle($sfid, $uid, $groups, $member_handler, $gperm_handler, $mid);
	$sub_single = $sub_single_result['flag'];
	if($sub_single) {
		unset($sub_entries);
		$sub_entries[$sfid][0] = $sub_single_result['entry'];
	}

	if(!is_array($sub_entries[$sfid])) { $sub_entries[$sfid] = array(); }

	if($sub_entry_new AND !$sub_single) {
		for($i=0;$i<$_POST['numsubents'];$i++) {
			array_unshift($sub_entries[$sfid], $sub_entry_new);
		}
	}

	if(is_array($sub_entry_written) AND !$sub_single) {
		foreach($sub_entry_written as $sew) {
			array_unshift($sub_entries[$sfid], $sew);
		}
	}

	if(!$customCaption) {
		// get the title of this subform
		$subtitle = q("SELECT desc_form FROM " . $xoopsDB->prefix("formulize_id") . " WHERE id_form = $sfid");
		$col_one = "<p>" . trans($subtitle[0]['desc_form']) . "</p><p style=\"font-weight: normal;\">" . _formulize_ADD_HELP;
	} else {
		$col_one = "<p>" . trans($customCaption) . "</p><p style=\"font-weight: normal;\">" . _formulize_ADD_HELP;
	}

	if(intval($sub_entries[$sfid][0]) != 0 OR $sub_entry_new OR is_array($sub_entry_written)) {
		if(!$nosubforms) { $col_one .= "<br>" . _formulize_ADD_HELP2; }
		$col_one .= "<br>" . _formulize_ADD_HELP3;
	} 

	// list the entries, including links to them and delete checkboxes
	
	// get the headerlist for the subform and convert it into handles
	// note big assumption/restriction that we are only using the first header found (ie: only specify one header for a sub form!)
	// setup the array of elements to draw
	if(is_array($customElements)) {
		$keys = array_keys($subHeaderList);
		foreach($customElements as $custEle) {
			$elementsToDraw[] = $custEle;
			$headerq = q("SELECT ele_caption, ele_colhead FROM " . $xoopsDB->prefix("formulize") . " WHERE ele_id=" . intval($custEle));
			$headersToDraw[] = $headerq[0]['ele_colhead'] ? $headerq[0]['ele_colhead'] : $headerq[0]['ele_caption'];
		}
	} else {
		$subHeaderList = getHeaderList($sfid);
		$subHeaderList1 = getHeaderList($sfid, true);
		$headersToDraw[] = trans($subHeaderList[0]);
		$headersToDraw[] = trans($subHeaderList[1]);
		$headersToDraw[] = trans($subHeaderList[2]);
		$elementsToDraw[] = $subHeaderList1[0];
		$elementsToDraw[] = $subHeaderList1[1];
		$elementsToDraw[] = $subHeaderList1[2];
	}

	$need_delete = 0;
	$drawnHeadersOnce = false;
	$col_two = "<table>";
	foreach($sub_entries[$sfid] as $sub_ent) {
		if($sub_ent != "") {
			if(!$drawnHeadersOnce) {
				$col_two .= "<tr><td></td>\n";
				foreach($headersToDraw as $thishead) {
					if($thishead) { $col_two .= "<td><p><b>$thishead</b></p></td>\n"; }
				}
				$col_two .= "</tr>\n";
				$drawnHeadersOnce = true;
			}
			$col_two .= "<tr>\n<td>";
			// check to see if we draw a delete box or not
			if($sub_ent !== "new") {
				$deleteSelf = $gperm_handler->checkRight("delete_own_entry", $sfid, $groups, $mid);
				$deleteOther = $gperm_handler->checkRight("delete_other_entries", $sfid, $groups, $mid);
				$sub_owner = getEntryOwner($sub_ent);
				//print "sub_owner: $sub_owner<br>uid: $uid<br>deleteself: $deleteSelf<br>";
				if(($sub_owner == $uid AND $deleteSelf) OR ($sub_owner != $uid AND $deleteOther)) {
					$need_delete = 1;
					$col_two .= "<input type=checkbox name=delbox$sub_ent value=$sub_ent></input>";
				} 
			}
			$col_two .= "</td>\n";
			include_once XOOPS_ROOT_PATH . "/modules/formulize/include/elementdisplay.php";
			foreach($elementsToDraw as $thisele) {
				if($thisele) { 
					ob_start();
					displayElement("", $thisele, $sub_ent); 
					$col_two_temp = ob_get_contents();
					ob_end_clean();
					if($col_two_temp) { // only draw in a cell if there actually is an element rendered
						$col_two .= "<td>$col_two_temp</td>\n";
					}
				}
			}
			if(!$nosubforms) { $col_two .= "<td><input type=button name=view".$sub_ent." value='"._formulize_SUBFORM_VIEW."' onclick=\"javascript:goSub('$sub_ent', '$sfid');return false;\"></input></td>\n"; }
			$col_two .= "</tr>\n";
		}
	}

	$col_two .= "</table>";

	if(count($sub_entries[$sfid]) == 1 AND $sub_entries[$sfid][0] === "" AND $sub_single) {
		$col_two .= "<p><input type=button name=addsub value='". _formulize_ADD_ONE . "' onclick=\"javascript:add_sub('$sfid', 1);\"></p>";
	} elseif(!$sub_single) {
		$col_two .=  "<p><input type=button name=addsub value='". _formulize_ADD . "' onclick=\"javascript:add_sub('$sfid', window.document.formulize.addsubentries.value);\"><input type=text name=addsubentries id=addsubentries value=1 size=2 maxlength=2>" . _formulize_ADD_ENTRIES . "</p>";
	} 
	if(((count($sub_entries[$sfid])>0 AND $sub_entries[$sfid][0] != "") OR $sub_entry_new OR is_array($sub_entry_written)) AND $need_delete) {
		$col_one .= "<br>" . _formulize_ADD_HELP4 . "<input type=hidden name=deletesubsflag value=''></p><p><input type=button name=deletesubs value='" . _formulize_DELETE_CHECKED . "' onclick=\"javascript:sub_del();\">";
	}
	$col_one .= "</p>";
	$to_return['c1'] = $col_one;
	$to_return['c2'] = $col_two;
	return $to_return;

}


// add the proxy list to a form
function addProxyList($form, $groups, $member_handler, $gperm_handler, $fid, $mid) {

	global $xoopsDB;

			$proxylist = new XoopsFormSelect(_AM_SELECT_PROXY, 'proxyuser', 0, 5, TRUE); // made multi May 3 05
			$proxylist->addOption('noproxy', _formulize_PICKAPROXY);
			
			$add_groups = $gperm_handler->getGroupIds("add_own_entry", $fid, $mid);
			// May 5, 2006 -- limit to the user's own groups unless the user has global scope
			if(!$globalscope = $gperm_handler->checkRight("view_globalscope", $fid, $groups, $mid)) {
				$add_groups = array_intersect($add_groups, $groups);
			}
			$all_add_users = array();
			foreach($add_groups as $grp) {
				$add_users = $member_handler->getUsersByGroup($grp);
				$all_add_users = array_merge((array)$add_users, $all_add_users);
				unset($add_users);
			}
		
			$unique_users = array_unique($all_add_users);

			foreach($unique_users as $uid) {
				$uqueryforrealnames = "SELECT name FROM " . $xoopsDB->prefix("users") . " WHERE uid=$uid";
				$uresqforrealnames = $xoopsDB->query($uqueryforrealnames);
				$urowqforrealnames = $xoopsDB->fetchRow($uresqforrealnames);
				$punames[] = $urowqforrealnames[0];
				//print "username: $urowqforrealnames[0]<br>"; // debug code
			}

			// alphabetize the proxy list added 11/2/04
			array_multisort($punames, $unique_users);

			for($i=0;$i<count($unique_users);$i++)
			{
				$proxylist->addOption($unique_users[$i], $punames[$i]);
			}

			$proxylist->setValue('noproxy');
						
			$form->addElement($proxylist);
			return $form;
}


//this function takes a formid and compiles all the elements for that form
//elements_allowed is NOT based off the display values.  It is based off of the elements that are specifically designated for the current displayForm function (used to display parts of forms at once)
function compileElements($fid, $form, $formulize_mgr, $prevEntry, $entry, $go_back, $parentLinks, $owner_groups, $groups, $overrideValue="", $elements_allowed="", $profileForm="", $frid, $mid, $uid, $sub_entries, $sub_fids, $member_handler, $gperm_handler, $title) {
	
	global $xoopsDB, $xoopsUser;

	// find hidden elements first..
	$hiddenElements = array();
	if(!$entry) {
		$notAllowedCriteria = new CriteriaCompo();
		$notAllowedCriteria->add(new Criteria('ele_forcehidden', 1));
		$criteria = new CriteriaCompo();
		$criteria->add(new Criteria('ele_display', 0), 'OR');
		$criteria2 = new CriteriaCompo();
		foreach($groups as $thisgroup) {
			$criteria2->add(new Criteria('ele_display', '%,'.$thisgroup.',%', 'NOT LIKE'), 'AND');
		}
		$criteria->add($criteria2, 'OR');
		$notAllowedCriteria->add($criteria, 'AND');
		$notAllowedCriteria->setSort('ele_order');
		$notAllowedCriteria->setOrder('ASC');

		$notAllowedElements =& $formulize_mgr->getObjects2($notAllowedCriteria,$fid);
	
		foreach($notAllowedElements as $ni) {
			// display these elements as hidden elements with the default value
			switch($ni->getVar('ele_type')) {
				case "radio":
					$indexer = 1;
	  				foreach($ni->getVar('ele_value') as $k=>$v) {
  						if($v == 1) {
							$hiddenElements[] = new xoopsFormHidden('ele_'.$ni->getVar('ele_id'), $indexer);
						}
						$indexer++;
					}
					break;
				case "text":
					global $myts;
					$ele_value = $ni->getVar('ele_value');
					$hiddenElements[] = new xoopsFormHidden('ele_'.$ni->getVar('ele_id'), $myts->htmlSpecialChars(getTextboxDefault($ele_value[2])));
					break;
				case "textarea":
					global $myts;
					$ele_value = $ni->getVar('ele_value');
					$hiddenElements[] = new xoopsFormHidden('ele_'.$ni->getVar('ele_id'), $myts->htmlSpecialChars(getTextboxDefault($ele_value[0])));
					break;
			}
		}
	}


	// set criteria for matching on display
	$criteria = new CriteriaCompo();
	$criteria->add(new Criteria('ele_display', 1), 'OR');
	foreach($groups as $thisgroup) {
		$criteria->add(new Criteria('ele_display', '%,'.$thisgroup.',%', 'LIKE'), 'OR');
	}
	$criteria->setSort('ele_order');
	$criteria->setOrder('ASC');
	$elements =& $formulize_mgr->getObjects2($criteria,$fid);
	$count = 0;
	$gridCounter = array();
	$inGrid = 0;
	foreach( $elements as $i ){

		$ele_type = $i->getVar('ele_type');
		if($ele_type == "derived") {
			continue;	
		}

		$this_ele_id = $i->getVar('ele_id');
		if(is_array($elements_allowed)) {
			if(!in_array($this_ele_id, $elements_allowed)) {
				continue;
			}
		}
	
		// check if this element is included in a grid, and if so, skip it
		// $inGrid will be a number indicating how many times we have to skip things
		if($inGrid OR isset($gridCounter[$this_ele_id])) {
			if(!$inGrid) {
				$inGrid = $gridCounter[$this_ele_id];
			}
			$inGrid--;
			continue;
		}


		$private = $i->getVar('ele_private');
		$uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : 0;
		$owner = getEntryOwner($entry);
		if($private AND ($uid != $owner AND $entry != "")) {
			$mid = getFormulizeModId();
			if(!$view_private_elements = $gperm_handler->checkRight("view_private_elements", $fid, $groups, $mid)) { continue; }
		}

		$ele_value = $i->getVar('ele_value');

		if(($prevEntry OR $profileForm === "new") AND $ele_type != 'subform' AND $ele_type != 'grid') { 
			$ele_value = loadValue($prevEntry, $i, $ele_value, $owner_groups, $groups, $entry, $profileForm); // get the value of this element for this entry as stored in the DB -- and unset any defaults if we are looking at an existing entry
		} elseif($go_back['form']) { // if there's a parent form...
			// check here to see if we need to initialize the value of a linked selectbox when it is the key field for a subform
			// although this is setup as a loop through all found parentLinks, only the last one will be used, since ele_value[2] is overwritten each time.
			// assumption is there will only be one parent link for this form
			for($z=0;$z<count($parentLinks['source']);$z++) {					
				if($this_ele_id == $parentLinks['self'][$z]) { // this is the element
					// get the caption of the parent's field
					$pcq = q("SELECT ele_caption FROM " . $xoopsDB->prefix("formulize") . " WHERE id_form='" . $go_back['form'] . "' AND ele_id='" . $parentLinks['source'][$z] . "'");				
					$parentCap = str_replace ("'", "`", $pcq[0]['ele_caption']);
					$parentCap = str_replace ("&quot;", "`", $parentCap);
					$parentCap = str_replace ("&#039;", "`", $parentCap);
					$pvq = q("SELECT ele_id FROM " . $xoopsDB->prefix("formulize_form") . " WHERE id_form='" . $go_back['form'] . "' AND id_req='" . $go_back['entry'] . "' AND ele_caption='$parentCap'");
					$pid = $pvq[0]['ele_id'];

					// NOTE: assuming that there will only be one value in the match, ie: the link field is not a multiple select box!
					// format of value should be $formid#*=:*$formcaption#*=:*$ele_id
					$ele_value[2] = $go_back['form'] . "#*=:*" . $parentCap . "#*=:*" . $pid; 
				}
			}
		} elseif($overrideValue){ // used to force a default setting in a form element, other than the normal default
			if(!is_array($overrideValue)) { //convert a string to an array so that strings don't screw up logic below (which is designed for arrays)
				$temp = $overrideValue;
				unset($overrideValue);
				$overrideValue[0] = $temp;
			}
			// currently only operative for select boxes
			switch($ele_type) {
				case "select":
					foreach($overrideValue as $ov) {
						if(array_key_exists($ov, $ele_value[2])) {
							$ele_value[2][$ov] = 1;
						}	
					}
					break;
				case "date":
                	// debug
                	//var_dump($overrideValue);
					foreach($overrideValue as $ov) {
						//if(ereg ("([0-9]{4})-([0-9]{2})-([0-9]{2})", $ov, $regs)) {
						if(ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $ov, $regs)) {
							$ele_value[0] = $ov;
						}
					}
					break;
			}
		}

		include_once XOOPS_ROOT_PATH . "/modules/formulize/class/elementrenderer.php";

		if($ele_type != "subform" AND $ele_type != 'grid') {
			$renderer =& new formulizeElementRenderer($i);
			$form_ele =& $renderer->constructElement('ele_'.$i->getVar('ele_id'), $ele_value, $entry);
		}

		if (isset ($ele_value[0])) {
			$ele_value[0] = eregi_replace("'", "`", $ele_value[0]);
			$ele_value[0] = eregi_replace("&quot;", "`", $ele_value[0]);
			$ele_value[0] = eregi_replace("&#039;", "`", $ele_value[0]);
			$ele_value[0] = stripslashes($ele_value[0]); 
		} 

		if ($ele_type == 'sep'){
			$ele_value = split ('<*>', $ele_value[0]);		
			foreach ($ele_value as $t){
				if (strpos($t, '<')!=false) {
					$ele_value[0] = $t;
			}	}
			$ele_value = split ('</', $ele_value[0]);			
			$hid = new XoopsFormHidden('ele_'.$i->getVar('ele_id'), $ele_value[0]);
			$form->addElement ($hid);
		}
		if ($ele_type == 'areamodif'){
			$hid2 = new XoopsFormHidden('ele_'.$i->getVar('ele_id'), $ele_value[0]);
			$form->addElement ($hid2);
		}
		if ($ele_type == 'upload'){
			$hid3 = new XoopsFormHidden($ele_value[1], $ele_value[1]);
			$form->addElement ($hid3);
		}
		$req = intval($i->getVar('ele_req'));
		if($ele_type == "subform") {
			$thissfid = $ele_value[0];		
			if($passed = security_check($thissfid) AND in_array($thissfid, $sub_fids)) {
				$GLOBALS['sfidsDrawn'][] = $thissfid;
				$customCaption = $i->getVar('ele_caption');
				$customElements = $ele_value[1] ? explode(",", $ele_value[1]) : "";
				$subUICols = drawSubLinks($thissfid, $sub_entries, $uid, $groups, $member_handler, $frid, $gperm_handler, $mid, $fid, $entry, $customCaption, $customElements);
				$subLinkUI = new XoopsFormLabel($subUICols['c1'], $subUICols['c2']);
				$form->addElement($subLinkUI);
				unset($subLinkUI);
			}
		} elseif($ele_type == "grid") {

			// we are going to have to store some kind of flag/counter with the id number of the starting element in the table, and the number of times we need to ignore things
			// we need to then listen for this up above and skip those elements as they come up.  This is why grids must come before their elements in the form definition

			include_once XOOPS_ROOT_PATH . "/modules/formulize/include/griddisplay.php";
			list($grid_title, $grid_row_caps, $grid_col_caps, $grid_background, $grid_start, $grid_count) = compileGrid($ele_value, $title, $i);
			$gridCounter[$grid_start] = $grid_count;
			ob_start();
			displayGrid($fid, $entry, $grid_row_caps, $grid_col_caps, $grid_title, $grid_background, $grid_start, "", "", true);
			$gridContents = ob_get_contents();
			ob_end_clean();
			$form->insertBreak($gridContents, "head"); // head is the css class of the cell

		} elseif($ele_type != "ib") { // if it's a break, handle it differently...
			$form_ele->setExtra("onchange=\"javascript:formulizechanged=1;\"");
			$form->addElement($form_ele, $req);
		} else {
			$form->insertBreak("<div style=\"font-weight: normal;\">" . trans(stripslashes($form_ele[0])) . "</div>", $form_ele[1]);
		}
		$count++;
		unset($hidden);
		unset($form_ele); // apparently necessary for compatibility with PHP 4.4.0 -- suggested by retspoox, sept 25, 2005
	}

	// add hiddenElements...
	foreach($hiddenElements as $thisHiddenElement) {
		$form->addElement($thisHiddenElement);
	}

	$form->addElement (new XoopsFormHidden ('counter', $count)); // not used by reading logic?
	if($entry) { 
		$form->addElement (new XoopsFormHidden ('entry'.$fid, $entry));
	}
	if($_POST['parent_form']) { // if we just came back from a parent form, then if they click save, we DO NOT want an override condition, even though we are now technically editing an entry that was previously saved when we went to the subform in the first place.  So the override logic looks for this hidden value as an exception.
		$form->addElement (new XoopsFormHidden ('back_from_sub', 1));
	}
	return $form;

}

// $groups is deprecated and not used in this function any longer
// $owner_groups is used when dealing with a usernames or fullnames selectbox
function loadValue($prevEntry, $i, $ele_value, $owner_groups, $groups, $entry, $profileForm="") {
//global $xoopsUser;
//if($xoopsUser->getVar('uid') == 1) {
//print_r($prevEntry);

//}

	global $myts;
	/*
	 * Hack by F�lix <INBOX Solutions> for sedonde
	 * myts == NULL
	 */
	if(!$myts){
		$myts =& MyTextSanitizer::getInstance();
	}
	/*
	 * Hack by F�lix <INBOX Solutions> for sedonde
	 * myts == NULL
	 */
			$type = $i->getVar('ele_type');
			// going direct from the DB since if multi-language is active, getVar will translate the caption
			//$caption = $i->getVar('ele_caption');
			$ele_id = $i->getVar('ele_id');

			// if we're handling a new profile form, check to see if the user has filled in the form already and use that value if necessary
			// This logic could be of general use in handling posted requests, except for it's inability to handle 'other' boxes.  An update may pay off in terms of speed of reloading the page.
			$value = "";
			if($profileForm === "new") {
				$dataFromUser = "";
				foreach($_POST as $k=>$v) {
					if( preg_match('/ele_/', $k)){
						$n = explode("_", $k);
						if($n[1] == $ele_id) { // found the element in $_POST;
							$dataFromUser = prepDataForWrite($i, $v);
							break;
						}
					}
				}
				if($dataFromUser) {
					$value = $dataFromUser;
				}
			} 

			if(!$value) {
	     			global $xoopsDB;
     				$ecq = q("SELECT ele_caption FROM " . $xoopsDB->prefix("formulize") . " WHERE ele_id = '$ele_id'");
     				$caption = $ecq[0]['ele_caption'];
     	
	     			// two lines to mimic how captions are written to the DB...
     				$caption = eregi_replace ("&#039;", "`", $caption);
     				$caption = eregi_replace ("&quot;", "`", $caption);
     				$caption = eregi_replace ("'", "`", $caption);

	     			$key = array_search($caption, $prevEntry['captions']);
     				// if the caption was not found in the existing values for this entry, then return the ele_value, unless we're looking at an existing entry, and then we need to clear defaults first
     				if(!is_numeric($key) AND $key=="") { 
     					if($entry) {
     						switch($type) {
     							case "text":
     								$ele_value[2] = "";
     								break;
	     						case "textarea":
     								$ele_value[0] = "";
     								break;
     						}
     					} 
	     				return $ele_value; 
     				} 
     				$value = $prevEntry['values'][$key];
			}

			/*print_r($ele_value);
			print "<br>After: "; //debug block
			*/
			switch ($type)
			{
				case "text":
					$ele_value[2] = $value;				
					$ele_value[2] = eregi_replace("'", "&#039;", $ele_value[2]);				
					break;
				case "textarea":
				/*
				 * Hack by F�lix<INBOX International>
				 * Adding colorpicker form element
				 */
				case "colorpick":
				/*
				 * End of Hack by F�lix<INBOX International>
				 * Adding colorpicker form element
				 */
					$ele_value[0] = $value;								
					break;
				case "select":
				case "radio":
				case "checkbox":

					// NEED TO ADD IN INITIALIZATION OF LINKED SELECT BOXES FOR SUBFORMS

					// NOTE:  unique delimiter used to identify LINKED select boxes, so they can be handled differently.
					if(strstr($value, "#*=:*")) // if we've got a linked select box, then do everything differently
					{
						$ele_value[2] = $value;
					}
					else
					{

					// put the array into another array (clearing all default values)
					// then we modify our place holder array and then reassign

					if ($type != "select")
					{
						$temparray = $ele_value;
					}
					else
					{
						$temparray = $ele_value[2];
					}					
					$temparraykeys = array_keys($temparray);

					if($temparraykeys[0] == "{FULLNAMES}" OR $temparraykeys[0] == "{USERNAMES}") { // ADDED June 18 2005 to handle pulling in usernames for the user's group(s)
						$ele_value[2]['{SELECTEDNAMES}'] = explode("*=+*:", $value);
						if(count($ele_value[2]['{SELECTEDNAMES}']) > 1) { array_shift($ele_value[2]['{SELECTEDNAMES}']); }
						$ele_value[2]['{OWNERGROUPS}'] = $owner_groups;
						break;
					}

					// need to turn the prevEntry got from the DB into something the same as what is in the form specification so defaults show up right
					// important: this is safe because $value itself is not being sent to the browser!
					// we're comparing the output of these two lines against what is stored in the form specification, which does not have HTML escaped characters, and has extra slashes.  Assumption is that lack of HTML filtering is okay since only admins and trusted users have access to form creation.  Not good, but acceptable for now.
					$value = $myts->undoHtmlSpecialChars($value);
					if(get_magic_quotes_gpc()) { $value = addslashes($value); } 

					$selvalarray = explode("*=+*:", $value);
				
					foreach($temparraykeys as $k)
					{
						if($k == $value) // if there's a straight match (not a multiple selection)
						{
							$temparray[$k] = 1;
						}
						elseif( in_array($k, $selvalarray) ) // or if there's a match within a multiple selection array)
						{
							$temparray[$k] = 1;
						}
						else // otherwise set to zero.
						{
							$temparray[$k] = 0;
						}
					}
					
					if ($type != "select")
					{
						$ele_value = $temparray;
					}
					else
					{
						$ele_value[2] = $temparray;
					}
					} // end of IF we have a linked select box
					break;
				case "yn":
					if($value == 1)
					{
						$ele_value = array("_YES"=>1, "_NO"=>0);
					}
					elseif($value == 2)
					{
						$ele_value = array("_YES"=>0, "_NO"=>1);
					}
					else
					{
						$ele_value = array("_YES"=>0, "_NO"=>0);
					}
					break;
				case "date":

					$ele_value[0] = $value;

					break;
			} // end switch

			/*print_r($ele_value);
			print "<br>"; //debug block
			*/
			return $ele_value;
}

// THIS FUNCTION TAKES THE ELE_VALUE SETTINGS FOR A GRID AND RETURNS ALL THE NECESSARY PARAMS READY FOR PASSING TO THE DISPLAYGRID FUNCTION
// ALSO WORKS OUT THE NUMBER OF ELEMENTS THAT CAN BE ENTERED INTO THIS GRID
function compileGrid($ele_value, $title, $element) {

	// 1 is heading
	// 2 is row captions
	// 3 is col captions
	// 4 is shading
	// 5 is first element

	switch($ele_value[0]) {
		case "caption":
			global $myts;
			if(!$myts){
				$myts =& MyTextSanitizer::getInstance();
			}
			// call the text sanitizer, first try to convert HTML chars, and if there were no conversions, then do a textarea conversion to automatically make links clickable
			$ele_caption = trans($element->getVar('ele_caption'));
			$htmlCaption = $myts->undoHtmlSpecialChars($ele_caption);
			if($htmlCaption == $ele_caption) {
				$ele_caption = $myts->displayTarea($ele_caption);
			} else {
				$ele_caption = $htmlCaption;
			}
			$toreturn[] = $ele_caption;
			break;
		case "form":
			$toreturn[] = $title;
			break;
		case "none":
			$toreturn[] = "";
			break;
	}

	$toreturn[] = explode(",", $ele_value[1]);
	$toreturn[] = explode(",", $ele_value[2]);

	$toreturn[] = $ele_value[3];

	$toreturn[] = $ele_value[4];

	// number of cells in this grid
	$toreturn[] = count($toreturn[1]) * count($toreturn[2]);

	return $toreturn;

}


// write the settings passed to this page from the view entries page, so the view can be restored when they go back
function writeHiddenSettings($settings, $form) {
	//unpack settings
	$sort = $settings['sort'];
	$order = $settings['order'];
	$oldcols = $settings['oldcols'];
	$currentview = $settings['currentview'];
	foreach($settings as $k=>$v) {
		if(substr($k, 0, 7) == "search_" AND $v != "") {
			$thiscol = substr($k, 7);
			$searches[$thiscol] = $v;
		}
	}
	//calculations:
	$calc_cols = $settings['calc_cols'];
	$calc_calcs = $settings['calc_calcs'];
	$calc_blanks = $settings['calc_blanks'];
	$calc_grouping = $settings['calc_grouping'];

	$hlist = $settings['hlist'];
	$hcalc = $settings['hcalc'];
	$lockcontrols = $settings['lockcontrols'];
	$asearch = $settings['asearch'];
	$lastloaded = $settings['lastloaded'];	

	// used for calendars...
	$calview = $settings['calview'];
	$calfrid = $settings['calfrid'];
	$calfid = $settings['calfid'];
	// plus there's the calhidden key that is handled below
	// plus there's the page number on the LOE screen that is handled below...

	// write hidden fields
	if($form) { // write as form objects and return form
		$form->addElement (new XoopsFormHidden ('sort', $sort));
		$form->addElement (new XoopsFormHidden ('order', $order));
		$form->addElement (new XoopsFormHidden ('currentview', $currentview));
		$form->addElement (new XoopsFormHidden ('oldcols', $oldcols));
		foreach($searches as $key=>$search) {
			$search_key = "search_" . $key;
			$search = str_replace("'", "&#39;", $search);
			$form->addElement (new XoopsFormHidden ($search_key, stripslashes($search)));
		}
		$form->addElement (new XoopsFormHidden ('calc_cols', $calc_cols));
		$form->addElement (new XoopsFormHidden ('calc_calcs', $calc_calcs));
		$form->addElement (new XoopsFormHidden ('calc_blanks', $calc_blanks));
		$form->addElement (new XoopsFormHidden ('calc_grouping', $calc_grouping));
		$form->addElement (new XoopsFormHidden ('hlist', $hlist));
		$form->addElement (new XoopsFormHidden ('hcalc', $hcalc));
		$form->addElement (new XoopsFormHidden ('lockcontrols', $lockcontrols));
		$form->addElement (new XoopsFormHidden ('lastloaded', $lastloaded));
		$asearch = str_replace("'", "&#39;", $asearch);
		$form->addElement (new XoopsFormHidden ('asearch', stripslashes($asearch)));
		$form->addElement (new XoopsFormHidden ('calview', $calview));
		$form->addElement (new XoopsFormHidden ('calfrid', $calfrid));
		$form->addElement (new XoopsFormHidden ('calfid', $calfid));
		foreach($settings['calhidden'] as $chname=>$chvalue) {
			$form->addElement (new XoopsFormHidden ($chname, $chvalue));
		}
		$form->addElement (new XoopsFormHidden ('formulize_LOEPageStart', $_POST['formulize_LOEPageStart']));
		return $form;
	} else { // write as HTML
		print "<input type=hidden name=sort value='" . $sort . "'>";
		print "<input type=hidden name=order value='" . $order . "'>";
		print "<input type=hidden name=currentview value='" . $currentview . "'>";
		print "<input type=hidden name=oldcols value='" . $oldcols . "'>";
		foreach($searches as $key=>$search) {
			$search_key = "search_" . $key;
			$search = str_replace("\"", "&quot;", $search);
			print "<input type=hidden name=$search_key value=\"" . stripslashes($search) . "\">";
		}
		print "<input type=hidden name=calc_cols value='" . $calc_cols . "'>";
		print "<input type=hidden name=calc_calcs value='" . $calc_calcs . "'>";
		print "<input type=hidden name=calc_blanks value='" . $calc_blanks . "'>";
		print "<input type=hidden name=calc_grouping value='" . $calc_grouping . "'>";
		print "<input type=hidden name=hlist value='" . $hlist . "'>";
		print "<input type=hidden name=hcalc value='" . $hcalc . "'>";
		print "<input type=hidden name=lockcontrols value='" . $lockcontrols . "'>";
		print "<input type=hidden name=lastloaded value='" . $lastloaded . "'>";
		$asearch = str_replace("\"", "&quot;", $asearch);
		print "<input type=hidden name=asearch value=\"" . stripslashes($asearch) . "\">";
		print "<input type=hidden name=calview value='" . $calview . "'>";
		print "<input type=hidden name=calfrid value='" . $calfrid . "'>";
		print "<input type=hidden name=calfid value='" . $calfid . "'>";
		foreach($settings['calhidden'] as $chname=>$chvalue) {
			print "<input type=hidden name=$chname value='" . $chvalue . "'>";
		}
		print "<input type=hidden name=formulize_LOEPageStart value='" . $_POST['formulize_LOEPageStart'] . "'>";
	}
}


// draw in javascript for this form that is relevant to subforms
// $nosave indicates that the user cannot save this entry, so we shouldn't check for formulizechanged
function drawJavascript($nosave) {
print "\n<script type='text/javascript'>\n";

print " var formulizechanged=0;\n";
?>
function showPop(url) {

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

function validateAndSubmit() {
	var validate = xoopsFormValidate_formulize();
	if(validate) {
		if(window.document.formulize.submitx) {
			window.document.formulize.submitx.disabled=true;
		}
		if(window.document.pagebuttons) {
			window.document.pagebuttons.prev.disabled = true;
			window.document.pagebuttons.next.disabled = true;
		}
		window.document.getElementById('formulizeform').style.opacity = 0.5;
		window.document.getElementById('savingmessage').style.display = 'block';
		window.scrollTo(0,0);
		window.document.formulize.submit(); 
	}
}

<?php

print "	function verifyDone() {\n";
//print "		alert(formulizechanged);\n";
if(!$nosave) {
	print "	if(formulizechanged==0) {\n";
}
print "			window.document.go_parent.submit();\n";
if(!$nosave) {
	print "	} else {\n";
	print "		var answer = confirm (\"" . _formulize_CONFIRMNOSAVE . "\");\n";
	print "		if (answer) {\n";
	print "			window.document.go_parent.submit();\n";
	print "		} else {\n";
	print "			return false;\n";
	print "		}\n";
	print "	}\n";
}
print "	}\n";
	
print "	function add_sub(sfid, numents) {\n";
print "		document.formulize.target_sub.value=sfid;\n";
print "		document.formulize.numsubents.value=numents;\n";
//print "		document.formulize.submit();\n";
print "		validateAndSubmit();\n";
print "	}\n";

print "	function sub_del() {\n";
print "		var answer = confirm ('" . _formulize_DEL_ENTRIES . "')\n";
print "		if (answer) {\n";
print "			document.formulize.deletesubsflag.value=1;\n";
print "			validateAndSubmit();\n";
print "		} else {\n";
print "			return false;\n";
print "		}\n";
print "	}\n";

print "	function goSub(ent, fid) {\n";
print "		document.formulize.goto_sub.value = ent;\n";
print "		document.formulize.goto_sfid.value = fid;\n";
//print "		document.formulize.submit();\n";
print "		validateAndSubmit();\n";
print "	}\n";
			
//added by Cory Aug 27, 2005 to make forms printable


print "function PrintPop() {\n";
print "		window.document.printview.submit();\n";
print "}\n";

//added by Cory Aug 27, 2005 to make forms printable

print "function PrintAllPop() {\n";									// nmc 2007.03.24 - added 
print "		window.document.printview.elements_allowed.value='';\n"; // nmc 2007.03.24 - added 
print "		window.document.printview.submit();\n";					// nmc 2007.03.24 - added 
print "}\n";														// nmc 2007.03.24 - added 

print "</script>\n";
}

?>