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
include_once ("admin_header.php");
include_once '../../../include/cp_header.php';

if(!isset($HTTP_POST_VARS['op'])){
	$op = isset ($HTTP_GET_VARS['op']) ? $HTTP_GET_VARS['op'] : '';
}else {
	$op = $HTTP_POST_VARS['op'];
}
if(!isset($HTTP_POST_VARS['title'])){
	$title = isset ($HTTP_GET_VARS['title']) ? $HTTP_GET_VARS['title'] : '';
}else {
	$title = $HTTP_POST_VARS['title'];
}


	$sql=sprintf("SELECT id_form FROM ".$xoopsDB->prefix("form_id")." WHERE desc_form='%s'",$title);
	$res = mysql_query ( $sql ) or die('Erreur SQL !<br>'.$requete.'<br>'.mysql_error());

if ( $res ) {
  while ( $row = mysql_fetch_row ( $res ) ) {
    $id_form = $row[0];
  }
}
 
if( $_POST['op'] != 'save' ){
	xoops_cp_header();

	echo '
	<form action="index.php?title='.$title.'" method="post">

	<table class="outer" cellspacing="1" width="98%">
	<th><center><font size=5>'._AM_FORM.$title.'<font></center></th>
	</table>';

	echo '<table class="outer" cellspacing="1" width="98%">
	<th><center>'._AM_ELE_CREATE.'</center></th>
	<tr><td class="even"><li><a href="elements.php?title='.$title.'&op=edit&amp;ele_type=text">'._AM_ELE_TEXT.'</a></td></tr>
	<tr><td class="even"><li><a href="elements.php?title='.$title.'&op=edit&amp;ele_type=textarea">'._AM_ELE_TAREA.'</a></td></tr>
	<tr><td class="even"><li><a href="elements.php?title='.$title.'&op=edit&amp;ele_type=areamodif">'._AM_ELE_MODIF.'</a></td></tr> 
	<tr><td class="even"><li><a href="elements.php?title='.$title.'&op=edit&amp;ele_type=ib">'._AM_ELE_MODIF_ONE.'</a></td></tr> 
	<tr><td class="even"><li><a href="elements.php?title='.$title.'&op=edit&amp;ele_type=select">'._AM_ELE_SELECT.'</a></td></tr>
	<tr><td class="even"><li><a href="elements.php?title='.$title.'&op=edit&amp;ele_type=checkbox">'._AM_ELE_CHECK.'</a></td></tr>
	<tr><td class="even"><li><a href="elements.php?title='.$title.'&op=edit&amp;ele_type=radio">'._AM_ELE_RADIO.'</a></td></tr>
	<tr><td class="even"><li><a href="elements.php?title='.$title.'&op=edit&amp;ele_type=yn">'._AM_ELE_YN.'</a></td></tr>
	<tr><td class="even"><li><a href="elements.php?title='.$title.'&op=edit&amp;ele_type=date">'._AM_ELE_DATE.'</a></td></tr>
	<tr><td class="even"><li><a href="elements.php?title='.$title.'&op=edit&amp;ele_type=sep">'._AM_ELE_SEP.'</a></td></tr>';
	// upload not yet enabled in formulize (redisplay of file info not supported, upload itself not tested)
	//<tr><td class="even"><li><a href="elements.php?title='.$title.'&op=edit&amp;ele_type=upload">'._AM_ELE_UPLOAD.'</a></td></tr>
	echo '</table>';

	echo ' <table class="outer" cellspacing="1" width="98%">
		<tr>
			<th>'._AM_ELE_CAPTION.'</th>';
			//<th>'._AM_ELE_DEFAULT.'</th>
			echo '<th>'._AM_ELE_REQ.'</th>
			<th>'._AM_ELE_ORDER.'</th>
			<th>'._AM_ELE_DISPLAY.'</th>
			<th colspan="3">&nbsp;</th>
		</tr>
	';
	$criteria = new Criteria(1,1);
	$criteria->setSort('ele_order');
	$criteria->setOrder('ASC');
	$elements =& $formulize_mgr->getObjects($criteria,$id_form);
	foreach( $elements as $i ){
		$id = $i->getVar('ele_id');
		$ele_value = $i->getVar('ele_value');
		$ele_value[0] = stripslashes ($ele_value[0]);
		$renderer =& new formulizeElementRenderer($i);
		$ele_value =& $renderer->constructElement('ele_value['.$id.']', true);
		$req = $i->getVar('ele_req');
		$check_req = new XoopsFormCheckBox('', 'ele_req['.$id.']', $req);
		$check_req->addOption(1, ' ');
		//if( $ele_type == 'checkbox' || $ele_type == 'radio' || $ele_type == 'yn' || $ele_type == 'select' || $ele_type == 'date' || $ele_type== 'areamodif' || $ele_type == 'upload' || $ele_type == 'areamodif' || $ele_type == 'sep'){
			$check_req->setExtra('disabled="disabled"'); 
		//}
		$order = $i->getVar('ele_order');
		$text_order = new XoopsFormText('', 'ele_order['.$id.']', 3, 3, $order); // switched to 3 wide, jwe 01/06/05
		$display = $i->getVar('ele_display');

		// added - start - August 25 2005 - jpc
        $multiGroupDisplay = false;
		if(substr($display, 0, 1) == ",")
        {
			$multiGroupDisplay = true;
            
	        $fs_member_handler =& xoops_gethandler('member');
	        $fs_xoops_groups =& $fs_member_handler->getGroups();

	        $displayGroupList = explode(",", $display);
            
            $check_display = '';

            foreach($displayGroupList as $groupList)
            {
				if($groupList != "")
                {
		            if($check_display != '')
                    	$check_display .= "\n";

					$group_display = $fs_member_handler->getGroup($groupList);
					$check_display .= $group_display->getVar('name');
				}                               
            }

            $check_display = '<a class=info href="" onclick="return false;" alt="' . 
            	$check_display . '" title="' . $check_display . '">' . 
                _AM_FORM_DISPLAY_MULTIPLE . '</a>';
        }
        else
        {
		// added - end - August 25 2005 - jpc

		$check_display = new XoopsFormCheckBox('', 'ele_display['.$id.']', $display);
		$check_display->addOption(1, ' ');

		// added - start - August 25 2005 - jpc
        }
		// added - end - August 25 2005 - jpc
        
		$hidden_id = new XoopsFormHidden('ele_id[]', $id);
		if(is_array($ele_value))$ele_value[0] = addslashes ($ele_value[0]);

		echo '<tr>';
		echo '<td class="even">'.$i->getVar('ele_caption')."</td>\n";
/*		if(is_object($ele_value)) {
			echo '<td class="even">'.$ele_value[0]."</td>\n";
		} else {
			echo '<td class="even">'.$ele_value->render()."</td>\n";
		}*/
		echo '<td class="even" align="center">'.$check_req->render()."</td>\n";
		echo '<td class="even" align="center">'.$text_order->render()."</td>\n";

		// added - start - August 25 2005 - jpc
		if($multiGroupDisplay == true)
        {
			echo '<td class="even" align="center">'.$check_display."</td>\n";
		}
        else
        {
		// added - end - August 25 2005 - jpc

		echo '<td class="even" align="center">'.$check_display->render().$hidden_id->render()."</td>\n";

		// added - start - August 25 2005 - jpc
		}
		// added - end - August 25 2005 - jpc
                
		echo '<td class="even" align="center"><a href="elements.php?title='.$title.'&op=edit&amp;ele_id='.$id.'">'._EDIT.'</a></td>';
		echo '<td class="even" align="center"><a href="elements.php?title='.$title.'&op=edit&amp;ele_id='.$id.'&clone=1">'._CLONE.'</a></td>';
		echo '<td class="even" align="center"><a href="elements.php?title='.$title.'&op=delete&amp;ele_id='.$id.'">'._DELETE.'</a></td>';
		echo '</tr>';
	}
	
	$submit = new XoopsFormButton('', 'submit', _AM_SAVE_CHANGES, 'submit');
	echo '
		<tr>
			<td class="foot" colspan="2"></td>
			<td class="foot" colspan="2" align="center">'.$submit->render().'</td>
			<td class="foot" colspan="3"></td>
		</tr>
	</table>
	';
	$hidden_op = new XoopsFormHidden('op', 'save');
	echo $hidden_op->render();
	echo '</form>';
}else{
        xoops_cp_header();
	extract($_POST);
	$error = '';
	foreach( $ele_id as $id ){
		$element =& $formulize_mgr->get($id);
// required field not yet available for all types of elements, so we don't check for it here.
//		$req = !empty($ele_req[$id]) ? 1 : 0;
//		$element->setVar('ele_req', $req);
		$order = !empty($ele_order[$id]) ? intval($ele_order[$id]) : 0;
		$element->setVar('ele_order', $order);
		$display = !empty($ele_display[$id]) ? 1 : 0;
		$element->setVar('ele_display', $display);


// COMMENTED ALL THE CODE BELOW THAT REWRITES ALL SETTINGS FOR AN ELEMENT, SINCE ALL WE WANT TO REWRITE IS THE SORT ORDER AND DISPLAY SETTINGS, AS CONTROLED ABOVE
// jwe 12/21/04
/*		$type = $element->getVar('ele_type');
		$value = $element->getVar('ele_value');
		if ($type == 'areamodif') $ele_value = $element->getVar('ele_value');
		$ele_value[0] = eregi_replace("'", "`", $ele_value[0]);
		$ele_value[0] = stripslashes($ele_value[0]);
		switch($type){
			case 'text':
				$value[2] = $ele_value[$id];
			break;
			case 'textarea':
				$value[0] = $ele_value[$id];
			break;
			case 'select':
				$new_vars = array();
				$opt_count = 1;
				if( is_array($ele_value[$id]) ){
					while( $j = each($value[2]) ){
						if( in_array($opt_count, $ele_value[$id]) ){
							$new_vars[$j['key']] = 1;
						}else{
							$new_vars[$j['key']] = 0;
						}
					$opt_count++;
					}
				}else{
					if( count($value[2]) > 1 ){
						while( $j = each($value[2]) ){
							if( $opt_count == $ele_value[$id] ){
								$new_vars[$j['key']] = 1;
							}else{
								$new_vars[$j['key']] = 0;
							}
						$opt_count++;
						}
					}else{
						while( $j = each($value[2]) ){
							if( !empty($ele_value[$id]) ){
								$new_vars = array($j['key']=>1);
							}else{
								$new_vars = array($j['key']=>0);
							}
						}
					}
				}
				
				$value[2] = $new_vars;
			break;
			case 'checkbox':
// 				$myts =& MyTextSanitizer::getInstance();
				$new_vars = array();
				$opt_count = 1;
				if( is_array($ele_value[$id]) ){
					while( $j = each($value) ){
						if( in_array($opt_count, $ele_value[$id]) ){
							$new_vars[$j['key']] = 1;
						}else{
							$new_vars[$j['key']] = 0;
						}
					$opt_count++;
					}
				}else{
					if( count($value) > 1 ){
						while( $j = each($value) ){
							$new_vars[$j['key']] = 0;
						}
					}else{
						while( $j = each($value) ){
							if( !empty($ele_value[$id]) ){
								$new_vars = array($j['key']=>1);
							}else{
								$new_vars = array($j['key']=>0);
							}
						}
					}
				}
				$value = $new_vars;
			break;
			case 'radio':
			case 'yn':
				$new_vars = array();
				$i = 1;
				while( $j = each($value) ){
					if( $ele_value[$id] == $i ){
						$new_vars[$j['key']] = 1;
					}else{
						$new_vars[$j['key']] = 0;
					}
					$i++;
				}
				$value = $new_vars;
			break;
			//Marie le 20/04/04
			case 'date':
				$value[0] = $ele_value[$id];
			break; 
			case 'areamodif':
				$value[0] = $ele_value[0];
			break;
			case 'sep':
				$value[2] = $ele_value[$id];
			break;
			case 'upload':
				$value[0] = $ele_value[$id];
				$value[1] = $ele_value[$id+1];
				$value[2] = $ele_value[$id+2];
			break;
			default:
			break;
		}
		$element->setVar('ele_value', $value);
		$element->setVar('id_form', $id_form);*/
// END OF COMMENTED CODE


		if( !$formulize_mgr->insert($element) ){
			$error .= $element->getHtmlErrors();
		}
	}
	if( empty($error) ){
//		redirect_header("index.php?title=$title", 0, _AM_DBUPDATED);
		redirect_header("index.php?title=$title", 0, _AM_SAVING_CHANGES);
	}else{
		xoops_cp_header();
		echo error;
	}
}

	echo '<center><table><tr><td valign=top><center><a href="../index.php?title='.$title.'" target="_blank">' . _AM_VIEW_FORM . ' <br><img src="../images/kfind.png"></a></center></td>';
	echo '<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
	echo '<td valign=top><center><a href="../admin/formindex.php">' . _AM_GOTO_MAIN . ' <br><img src="../images/formulize.gif" height=35></a></center></td>';
	echo '<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
	echo '<td valign=top><center><a href="../admin/mailindex.php?title='.$title.'">' . _AM_GOTO_PARAMS . ' <br><img src="../images/xfmail.png"></a><br>' . _AM_PARAMS_EXTRA . '</center></td>';
	echo '</tr></table></center>';

	//echo '<br><br>lien a ins�rer : &lt;a href&nbsp;="'.XOOPS_URL.'/modules/formulize/index.php?title='.$title.'">'.$title.'&lt;/a><br><br>';   


include 'footer.php';
xoops_cp_footer();
?>