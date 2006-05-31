<?php
###############################################################################
##     Formulize - ad hoc form creation and reporting module for XOOPS       ##
##                    Copyright (c) 2004 Freeform Solutions                  ##
##                Portions copyright (c) 2003 NS Tai (aka tuff)              ##
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
##  Author of this file: Freeform Solutions and NS Tai (aka tuff) and others ##
##  URL: http://www.brandycoke.com/                                          ##
##  Project: Formulize                                                       ##
###############################################################################
if( !preg_match("/elements.php/", $_SERVER['PHP_SELF']) ){
	exit("Access Denied");
}
if( empty($addopt) && !empty($ele_id) ){
	$ele_value = $element->getVar('ele_value');
}
$ele_size = !empty($ele_value[0]) ? $ele_value[0] : 1;
$size = new XoopsFormText(_AM_ELE_SIZE, 'ele_value[0]', 3, 2, $ele_size);
$allow_multi = empty($ele_value[1]) ? 0 : 1;
$multiple = new XoopsFormRadioYN(_AM_ELE_MULTIPLE, 'ele_value[1]', $allow_multi);

$options = array();
$opt_count = 0;
if( empty($addopt) && !empty($ele_id) ){
	$keys = array_keys($ele_value[2]);
	for( $i=0; $i<count($keys); $i++ ){
		$v = $myts->makeTboxData4PreviewInForm($keys[$i]);
		$options[] = addOption('ele_value[2]['.$opt_count.']', 'checked['.$opt_count.']', $v, 'check', $ele_value[2][$keys[$i]]);
		$opt_count++;
	}

	// added check below to add in blank rows that are unaccounted for above.
	// above code adds in all the options the user has typed in.  If there are blank rows on the form, this code will add in the appropriate amount, based on the 'rowcount' hidden element.
	// This code added by jwe 01/05/05
	// note, I believe this conditional will never evaluate as TRUE, but I am not certain, so I'm not going to remove it in case it evaluates TRUE and the enclosed code is ever needed
	if($opt_count < $_POST['rowcount']) {
		for($i=$opt_count;$i<$_POST['rowcount'];$i++) {
			$options[] = addOption('ele_value[2]['.$i.']', 'checked['.$i.']');
		}
		$opt_count = $_POST['rowcount']; // make the opt_count equal to the number of rows, since we've now brought the number back up to where it should be.
	}
	$rowcount = new XoopsFormHidden("rowcount", $opt_count);
	$form->addElement($rowcount);



/*	while( $var = each($ele_value[2]) ){
		$v = $myts->makeTboxData4PreviewInForm($var['key']);
		$t1 = new XoopsFormCheckBox('', 'checked['.$opt_count.']', $var['value']);
		$t1->addOption(1, ' ');
		$t2 = new XoopsFormText('', 'ele_value[2]['.$opt_count.']', 40, 255, $v);
		$t3 = new XoopsFormElementTray('');
		$t3->addElement($t1);
		$t3->addElement($t2);
//  		$t3 = new XoopsFormLabel('', $t1->render().$t2->render());
		$options[] = $t3;
		$opt_count++;
	}	*/
}else{
	if( !empty($ele_value[2]) ){
		while( $v = each($ele_value[2]) ){
			$v['value'] = $myts->makeTboxData4PreviewInForm($v['value']);
			if( !empty($v['value']) ){
		/*		$t1 = new XoopsFormCheckBox('', 'checked['.$opt_count.']', $checked[$v['key']]);
				$t1->addOption(1, ' ');
				$t2 = new XoopsFormText('', 'ele_value[2]['.$opt_count.']', 40, 255, $v['value']);
// 				$t3 = new XoopsFormElementTray('');
// 				$t3->addElement($t1);
// 				$t3->addElement($t2);
 				$t3 = new XoopsFormLabel('', $t1->render().$t2->render());
				$options[] = $t3;	*/
				
				$options[] = addOption('ele_value[2]['.$opt_count.']', 'checked['.$opt_count.']', $v['value'], 'check', $checked[$v['key']]);
				$opt_count++;
			}
		}
	}

	// added check below to add in blank rows that are unaccounted for above.
	// above code adds in all the options the user has typed in.  If there are blank rows on the form, this code will add in the appropriate amount, based on the 'rowcount' hidden element.
	// This code added by jwe 01/05/05
	if($opt_count < $_POST['rowcount']) {
		for($i=$opt_count;$i<$_POST['rowcount'];$i++) {
			$options[] = addOption('ele_value[2]['.$i.']', 'checked['.$i.']');
		}
		$opt_count = $_POST['rowcount']; // make the opt_count equal to the number of rows, since we've now brought the number back up to where it should be.
	}


	$addopt = empty($addopt) ? 2 : $addopt;
	for( $i=0; $i<$addopt; $i++ ){
		$options[] = addOption('ele_value[2]['.$opt_count.']', 'checked['.$opt_count.']');
		$opt_count++;
	}
	// these two lines part of the jwe added code
	$rowcount = new XoopsFormHidden("rowcount", $opt_count);
	$form->addElement($rowcount);

}

$add_opt = addOptionsTray();
$options[] = $add_opt;

$opt_tray = new XoopsFormElementTray(_AM_ELE_OPT, '<br />');
$opt_tray->setDescription(_AM_ELE_OPT_DESC._AM_ELE_OPT_DESC1);
for( $i=0; $i<count($options); $i++ ){
	$opt_tray->addElement($options[$i]);
}

// create the $formlink, the part of the selectbox form that links to another field in another form to populate the select box. -- jwe 7/29/04
array($formids);
array($formnames);
array($totalcaptionlist);
array($totalvaluelist);
$captionlistindex = 0;

$formlist = "SELECT id_form, desc_form FROM " . $xoopsDB->prefix("formulize_id") . " ORDER BY desc_form";
$resformlist = mysql_query($formlist);
if($resformlist)
{
	while ($rowformlist = mysql_fetch_row($resformlist)) // loop through each form
	{
		$fieldnames = "SELECT ele_caption FROM " . $xoopsDB->prefix("formulize") . " WHERE id_form=$rowformlist[0] ORDER BY ele_order";
		$resfieldnames = mysql_query($fieldnames);
		
		while ($rowfieldnames = mysql_fetch_row($resfieldnames)) // loop through each caption in the current form
		{
			$totalcaptionlist[$captionlistindex] = $rowformlist[1] . ": " . $rowfieldnames[0];  // write formname: caption to the master array that will be passed to the select box.
			$totalvaluelist[$captionlistindex] = $rowformlist[0] . "#*=:*" . $rowfieldnames[0];
			if($ele_value[2] == $totalvaluelist[$captionlistindex]) // if this is the selected entry...
			{
				$defaultlinkselection = $captionlistindex;
			}
			$captionlistindex++;
		}
	}
}
// make the select box and add all the options... -- jwe 7/29/04
$formlink = new XoopsFormSelect(_AM_ELE_FORMLINK, 'formlink', '' , 1, false);
$formlink->addOption("none", _AM_FORMLINK_NONE);
for($i=0;$i<$captionlistindex;$i++)
{
	$formlink->addOption($totalvaluelist[$i], $totalcaptionlist[$i]);
}
if($defaultlinkselection)
{
	$formlink->setValue($totalvaluelist[$defaultlinkselection]);
}
$formlink->setDescription(_AM_ELE_FORMLINK_DESC);


$form->addElement($size, 1);
$form->addElement($multiple);
$form->addElement($opt_tray);
// added another form element, the dynamic link to another form's field to populate the selectbox.  -- jwe 7/29/04
$form->addElement($formlink);


// print_r($options);
// 	echo '<br />';

?>