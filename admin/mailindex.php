<?php
###############################################################################
##             Formulaire - Information submitting module for XOOPS          ##
##                    Copyright (c) 2003 NS Tai (aka tuff)                   ##
##                       <http://www.brandycoke.com/>                        ##
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
##  Author of this file: NS Tai (aka tuff)                                   ##
##  URL: http://www.brandycoke.com/                                          ##
##  Project: Formulaire                                                      ##
###############################################################################
include("admin_header.php");
include_once '../../../include/cp_header.php';
if ( file_exists("../language/".$xoopsConfig['language']."/main.php") ) {
	include "../language/".$xoopsConfig['language']."/main.php";
} else {
	include "../language/english/main.php";
}

if(!isset($HTTP_POST_VARS['title'])){
	$title = isset ($HTTP_GET_VARS['title']) ? $HTTP_GET_VARS['title'] : '';
}else {
	$title = $HTTP_POST_VARS['title'];
}
if(!isset($HTTP_POST_VARS['op'])){
	$op = isset ($HTTP_GET_VARS['op']) ? $HTTP_GET_VARS['op'] : '';
}else {
	$op = $HTTP_POST_VARS['op'];
}


if(!isset($_POST['op'])){ $_POST['op']=" ";}

// query modified to call in new fields -- jwe 7/25/04. 7/28/04

if ( isset ($title)) {
	$sql=sprintf("SELECT id_form,admin,groupe,email,expe,singleentry,groupscope,headerlist,showviewentries,maxentries FROM ".$xoopsDB->prefix("form_id")." WHERE desc_form='%s'",$title);
	$res = mysql_query ( $sql ) or die('Erreur SQL !<br>'.$requete.'<br>'.mysql_error());

	if ( $res ) {
	  while ( $row = mysql_fetch_array ( $res ) ) {
		//print"<br>";
		//print_r($row);
		//print"<br>";

	    $id_form = $row['id_form'];
	    $admin = $row['admin'];
	    $groupe = $row['groupe'];
	    $email = $row['email'];
	    $expe = $row['expe'];
	    // added new entries -- jwe 7/25/04
	    $singleentry = $row['singleentry'];
	    $groupscope = $row['groupscope'];
	    $headerlist = $row['headerlist'];
	    $showviewentries = $row['showviewentries'];
	    $maxentries = $row['maxentries'];
	  }
	}

}

$m = 0;
//include "../include/functions.php";
include_once XOOPS_ROOT_PATH."/class/xoopstree.php";
include_once XOOPS_ROOT_PATH."/class/xoopslists.php";
include_once XOOPS_ROOT_PATH."/include/xoopscodes.php";
include_once XOOPS_ROOT_PATH."/class/module.errorhandler.php";
$myts =& MyTextSanitizer::getInstance();
$eh = new ErrorHandler;

	xoops_cp_header();

if( $_POST['op'] != 'upform' && $op != 'addform'){

	$sql="SELECT groupid,name FROM ".$xoopsDB->prefix("groups");
	$res = mysql_query ( $sql );
	if ( $res ) {
	$tab[$m] = 0;
	$tab2[$m] = "";
	$m++;
	  while ( $row = mysql_fetch_array ( $res ) ) {
	    $tab[$m] = $row['groupid'];
	    $tab2[$m] = $row['name'];
	    $m++;
	  }
	}

	if ($title != '') {
			echo '
		<form action="mailindex.php?title='.$title.'" method="post">
	
		<table class="outer" cellspacing="1" width="100%">
		<th><center><font size=5>'._AM_FORM.$title.'<font></center></th>
		</table>';
		
/*		// Affichage des droits du formulaire
		echo '<tr><td class="head"><center>'._FORM_DROIT.'</center></td>
		<td class="even"><select name="auto" size="4">';
		for($i=0;$i<$m;$i++) {
			echo ' <option value='.$tab[$i].''; 
			if($title != '' && $tab[$i]==$groupe) {echo " SELECTED";}  
			echo '>';
			echo $tab2[$i];
			echo '</option>';
		}		
		echo '</select></td></tr>';*/
		
		echo '<table class="outer" cellspacing="1" width="100%">
		<th colspan="4">'._FORM_MODIF.'</th>';
		echo '
		<tr><td class="head" ><center>'._FORM_EMAIL.'</center></td>
		<td class="even"><input maxlength="255" size="30" id="email" name="email" type="text" value='.$email.'></td></tr>';
	}
	else {
		echo '
		<form action="mailindex.php?op=addform" method="post">
	
		<table class="outer" cellspacing="1" width="100%">
		<th><center><font size=5>'._AM_FORM.'</font></center></th>
		</table>
		
		<table class="outer" cellspacing="1" width="100%">
		<th colspan="4">'._FORM_CREAT.'</th>
		
		<tr><td class="head" ><center>'._FORM_TITLE.'</center></td>
		<td class="even"><input maxlength="255" size="30" id="newtitle" name="newtitle" type="text"></td></tr>';
	
/*	// Affichage des droits du formulaire
		echo '<tr><td class="head"><center>'._FORM_DROIT.'</center></td>
			<td class="even"><select name="auto" size="4">';
		for($i=0;$i<$m;$i++) {
			echo '        <option value='.$tab[$i].''; 
			if($title != '' && $tab[$i]==$groupe) {echo " SELECTED";}  
			echo '>';
					echo $tab2[$i];
			echo '</option>';
		}		
		echo '</select></td></tr>';*/
			
		echo '
		<tr><td class="head" ><center>'._FORM_EMAIL.'</center></td>
		<td class="even"><input maxlength="255" size="30" id="email" name="email" type="text"></td></tr>';

	}		

echo '	<tr><td class="head"><center>'._FORM_GROUP.'</center></td><td class="even"><select name="groupe" size="4">';
	
for($i=0;$i<$m;$i++) {
	echo '        <option value='.$tab[$i].''; 
	if($title != '' && $tab[$i]==$groupe) {echo " SELECTED";}  
	echo '>';
			echo $tab2[$i];
	echo '	      </option>';
}
echo '        
	</select></td>
	</tr>
	<tr>
	<td class="head"><center>'._FORM_ADMIN.'</center></td><td class="even">';
	if ($title != '' && $admin == 'on') {echo '
	<input name="admin" type="checkbox" id="admin" checked></td>';}
	else {echo '
	<input name="admin" type="checkbox" id="admin" ></td>';}
	
	echo '</tr>
	<tr>
	<td class="head"><center>'._FORM_EXPE.'</center></td><td class="even">';
	if ($title != '' && $expe == 'on') {echo '
	<input name="expe" type="checkbox" id="expe" checked></td>';}
	else {echo '
	<input name="expe" type="checkbox" id="expe" ></td>';}

// *******
// added new form params to enable new features (single-entry and group-scope) -- jwe 7/25/04
// *******

	echo '<tr>
	<td class="head"><center>'._FORM_SHOWVIEWENTRIES.'</center></td><td class="even">';
	if ($title != '' && $showviewentries == '') {echo '
	<input name="showviewentries" type="checkbox" id="showviewentries"></td>';}
	else {echo '
	<input name="showviewentries" type="checkbox" id="showviewentries" checked></td>';}

echo '</tr>';

	echo '<tr>
	<td class="head"><center>'._FORM_SINGLEENTRY.'</center></td><td class="even">';
	if ($title != '' && $singleentry == 'on') {echo '
	<input name="singleentry" type="checkbox" id="singleentry" checked></td>';}
	else {echo '
	<input name="singleentry" type="checkbox" id="singleentry" ></td>';}

echo '</tr>
<tr>
	<td class="head"><center>'._FORM_GROUPSCOPE.'</center></td><td class="even">';
	if ($title != '' && $groupscope == 'on') {echo '
	<input name="groupscope" type="checkbox" id="groupscope" checked></td>';}
	else {echo '
	<input name="groupscope" type="checkbox" id="groupscope" ></td>';}

echo '</tr>';

echo '</tr>';

	echo '<tr>
	<td class="head"><center>'._FORM_MAXENTRIES.'</center></td><td class="even">';
	if ($title != '' && $maxentries) {echo '
	<input name="maxentries" type="textbox" id="maxentries" size=5 value=' . $maxentries . '></td>';}
	else {echo '
	<input name="maxentries" type="textbox" id="maxentries" size=5 value=0></td>';}

echo '</tr>';


if($title != '')
{
echo '<tr>
	<td class="head"><center>'._FORM_HEADERLIST.'</center></td><td class="even">';
	if ($headerlist) {
		// split out the entries in the headerlist so they can mark the populate the listbox
		$headlistarray = explode("*=+*:", $headerlist);
		//print_r($headlistarray);
		//print"88";
	}	

	echo '<select name="headerlist[]" size="4" multiple>';

	$getform_id ="SELECT id_form FROM ".$xoopsDB->prefix("form_id")." WHERE desc_form=\"$title\"";
	$resultgetform = mysql_query($getform_id);
	$resgetformrow = mysql_fetch_row($resultgetform);
	$thisformid = $resgetformrow[0];

	// get a list of captions in the form (fancy SQL Join query)
	// then draw them into the selection box
	$sqljwe="SELECT ele_caption FROM ".$xoopsDB->prefix("form")." WHERE id_form = \"$thisformid\" ORDER BY ele_order";
	$resjwe = mysql_query ( $sqljwe );
	if ( $resjwe ) {
		$loopiteration = 0;
		while ( $rowjwe = mysql_fetch_row ( $resjwe ) ) {
			echo "<option value=\"" . $rowjwe[0] . "\""; 
			if($title != '' && in_array($rowjwe[0], $headlistarray)) {echo " SELECTED";}  
			echo '>';
			echo $rowjwe[0];
			echo '</option>';
			$loopiteration++;
		}
	}

	/*//debug code for testing submissions...
	echo '<option value="one">one</option>';
	echo '<option value="two">two</option>';
	echo '<option value="three">three</option>';*/

	echo '</select></td></tr>';
}// end of IF that controls drawing of headerlist box.

if(!$title) // if there is no title, ie: new form, then show default perm box...
{
echo '<tr>
	<td class="head"><center>'._FORM_DEFAULTADMIN.'</center></td><td class="even">';
echo '<select name="defaultadmin[]" size="4" multiple>';
	
for($i=1;$i<$m;$i++) { // start at 1 since the first entry is a blank line.
	echo '        <option value='.$tab[$i].''; 
	echo '>';
			echo $tab2[$i];
	echo '	      </option>';

}
	echo '</select></td></tr>';
}// end of IF that controls drawing of permission box.






//================== end of jwe mod


echo '</table>
	<table class="outer" cellspacing="1" width="100%">
	';

	$submit = new XoopsFormButton('', 'submit', _SUBMIT, 'submit');
	echo '
			<td class="foot" colspan="7">'.$submit->render().'
		</tr>
	</table>
	';

	if ($title != '') { 	
		$hidden_op = new XoopsFormHidden('op', 'upform');
		echo $hidden_op->render();
	}
	
	echo '</form>';

}


function upform($title)
{
	global $xoopsDB, $HTTP_POST_VARS, $myts, $eh;
	$admin = $myts->makeTboxData4Save($HTTP_POST_VARS["admin"]);
	$groupe = $myts->makeTboxData4Save($HTTP_POST_VARS["groupe"]);
	$email = $myts->makeTboxData4Save($HTTP_POST_VARS["email"]);
	$expe = $myts->makeTboxData4Save($HTTP_POST_VARS["expe"]);
	
	// added to handle new params -- jwe 7/25/07
	$singleentry = $myts->makeTboxData4Save($HTTP_POST_VARS["singleentry"]);
	$groupscope = $myts->makeTboxData4Save($HTTP_POST_VARS["groupscope"]);
	$headerlist = $myts->makeTboxData4Save($HTTP_POST_VARS["headerlist"]);

	foreach($headerlist as $arrayelement)
	{
		$cheaderlist = $cheaderlist . "*=+*:" . $arrayelement;
	}


	$showviewentries = $myts->makeTboxData4Save($HTTP_POST_VARS["showviewentries"]);
	$maxentries = $HTTP_POST_VARS["maxentries"];
	
	// ========= end added code - jwe

	if((!empty($email)) && (!eregi("^[_a-z0-9.-]+@[a-z0-9.-]{2,}[.][a-z]{2,3}$",$email))){
		redirect_header("mailindex.php?title=$title", 2, _MD_ERROREMAIL);
	}
	if (empty($email) && empty($admin) && $groupe=="0" && empty($expe)) {
		redirect_header("mailindex.php?title=$title", 2, _MD_ERRORMAIL);
	}
	// sql updated with new fields -- jwe 7/25/04 , 7/28/04
	$sql = sprintf("UPDATE %s SET admin='%s', groupe='%s', email='%s', expe='%s', singleentry='%s', groupscope='%s', headerlist='%s', showviewentries='%s', maxentries='%s' WHERE desc_form='%s'", $xoopsDB->prefix("form_id"), $admin, $groupe, $email, $expe, $singleentry, $groupscope, $cheaderlist, $showviewentries, $maxentries, $title);
	$xoopsDB->query($sql) or $eh->show("0013");
	redirect_header("formindex.php",1,_FORMULAIRE_FORMTITRE);
}


function addform()
{
	global $xoopsDB, $HTTP_POST_VARS, $myts, $eh;
	$title = $myts->makeTboxData4Save($HTTP_POST_VARS["newtitle"]);
	$admin = $myts->makeTboxData4Save($HTTP_POST_VARS["admin"]);
	$groupe = $myts->makeTboxData4Save($HTTP_POST_VARS["groupe"]);
	$email = $myts->makeTboxData4Save($HTTP_POST_VARS["email"]);
	$expe = $myts->makeTboxData4Save($HTTP_POST_VARS["expe"]);
	
	// added to handle new params -- jwe 7/25/07
	$singleentry = $myts->makeTboxData4Save($HTTP_POST_VARS["singleentry"]);
	$groupscope = $myts->makeTboxData4Save($HTTP_POST_VARS["groupscope"]);
	
	$showviewentries = $myts->makeTboxData4Save($HTTP_POST_VARS["showviewentries"]);
	$maxentries = $HTTP_POST_VARS["maxentries"];

	// ============ end of added code - jwe

	if (empty($title)) {
		redirect_header("formindex.php", 2, _MD_ERRORTITLE);
	}
	if((!empty($email)) && (!eregi("^[_a-z0-9.-]+@[a-z0-9.-]{2,}[.][a-z]{2,3}$",$email))){
		redirect_header("formindex.php", 2, _MD_ERROREMAIL);
	}
	if (empty($email) && empty($admin) && $groupe=="0" && empty($expe)) {
		redirect_header("formindex.php", 2, _MD_ERRORMAIL);
	}
	$title = stripslashes($title);
	$title = eregi_replace ("'", "`", $title);
	$title = eregi_replace ('"', "`", $title);
	$title = eregi_replace ('&', "_", $title);

	// updated to handle new params -- jwe 7/25/07 , 7/28/04
	$sql = sprintf("INSERT INTO %s (desc_form, admin, groupe, email, expe, singleentry, groupscope, showviewentries, maxentries) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $xoopsDB->prefix("form_id"), $title, $admin, $groupe, $email, $expe, $singleentry, $groupscope, $showviewentries, $maxentries);
	$xoopsDB->queryF($sql) or $eh->show("error insertion 1 dans addform");

	$sql2 = sprintf("INSERT INTO %s (itemname,itemurl) VALUES ('%s', '%s')", $xoopsDB->prefix("form_menu"), $title, XOOPS_URL.'/modules/formulaire/index.php?title='.$title.'');
	$xoopsDB->queryF($sql2) or $eh->show("error insertion 2 dans addform");
	
	// grab and write default perms... -- jwe 7/28/04
	$defaultadmin = $myts->makeTboxData4Save($HTTP_POST_VARS["defaultadmin"]);

	//get the new form id
	$getfidq = "SELECT id_form FROM " . $xoopsDB->prefix("form_id") . " WHERE desc_form = \"$title\"";
$resgetfidq = mysql_query($getfidq);	
	$rowgetfidq = mysql_fetch_row($resgetfidq);
	//print "query: $getfidq<br>result row: ";
	//print_r($rowgetfidq);	
	$id_form = $rowgetfidq[0];

	//get the module id
	$res4 = $xoopsDB->query("SELECT mid FROM ".$xoopsDB->prefix("modules")." WHERE dirname='formulaire'");
	if ($res4) {
		while ($row = mysql_fetch_row($res4)) {
			$module_id = $row[0];
		}
	}

	array($permstowrite);
	$permstowrite[0] = "view";
	$permstowrite[1] = "add";
	$permstowrite[2] = "admin";

	foreach($defaultadmin as $agid)
	{
		foreach($permstowrite as $thisperm)
		{
		$setpermq = "INSERT INTO ".$xoopsDB->prefix("group_permission")." (gperm_groupid, gperm_itemid, gperm_modid, gperm_name) VALUES (\"$agid\", \"$id_form\", \"$module_id\", \"$thisperm\")";
		$result = mysql_query($setpermq);
		if(!$result)
		{	
			die("Perms NOT successfully written to DB!<br>query failed:  $setpermq");
		}
		} // end of write each perm
	} // end of loop for each group 

	redirect_header("index.php?title=$title",1,_FORMULAIRE_FORMCREA);
}


if(!isset($op)){$op=" ";}
switch ($op) {
case "upform":
	upform($title);
	break;
case "addform":
	addform();
	break;
}

include 'footer.php';
xoops_cp_footer();

?>




