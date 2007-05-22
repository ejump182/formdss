<?php
###############################################################################
##     Formulize - ad hoc form creation and reporting module for XOOPS       ##
##                    Copyright (c) 2006 Freeform Solutions                  ##
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
##  Author of this file: Freeform Solutions                                  ##
##  Project: Formulize                                                       ##
###############################################################################

require_once XOOPS_ROOT_PATH.'/kernel/object.php';
include_once XOOPS_ROOT_PATH.'/modules/formulize/include/functions.php';

class formulizeFramework extends XoopsObject {
	function formulizeFramework($frid=""){

		// validate $id_form
		global $xoopsDB;
		$notAFramework = false;
		if(!is_numeric($frid)) {
			// set empty defaults
			$notAFramework = true;
		} else {
			$frame_elements_q = q("SELECT * FROM " . $xoopsDB->prefix("formulize_framework_elements") . " WHERE fe_frame_id=$frid");
			if(!isset($frame_elements_q[0])) {
				$notAFramework = true;
			} else {
				$handles = array();
				$element_ids = array();
				foreach($frame_elements_q as $row=>$value) {
					$handles[$value['fe_element_id']] = $value['fe_handle'];
					$element_ids[$value['fe_handle']] = $value['fe_element_id'];
				}
			}
			$frame_links_q = q("SELECT * FROM " . $xoopsDB->prefix("formulize_framework_links") . " WHERE fl_frame_id=\"" . mysql_real_escape_string($frid). "\"");
			if(!isset($frame_links_q[0])) {
				$notAFramework = true;
			} else {
				$links = array();
				$fids = array();
				foreach($frame_links_q as $row=>$value) {
					$links[] = new formulizeFrameworkLink($value['fl_id']);
					// note that you cannot query the framework_forms table to learn what forms are in a framework, since we keep entries in that table after links have been deleted, since forms might rejoin a framework and we don't want to lose their information.  The links table is the only authoritative source of information about what forms make up a framework.
					$fids[] = $value['fl_form1_id'];
					$fids[] = $value['fl_form2_id'];
				}
				$fids = array_unique($fids);
			}
			$frame_name_q = q("SELECT * FROM " . $xoopsDB->prefix("formulize_frameworks") . " WHERE frame_id=$frid");
			if(!isset($frame_name_q[0])) {
				$notAFramework = true;
			}
			$frame_form_handles_q = q("SELECT * FROM " . $xoopsDB->prefix("formulize_framework_forms") . " WHERE ff_frame_id=$frid");
			if(!isset($frame_form_handles_q[0])) {
				$notAFramework = true;
			} else {
				$formHandles = array();
				foreach($frame_form_handles_q as $row=>$value) {
					if($fidKey = array_search($value['ff_form_id'], $fids)) { // find this form in the fids array, and use that fid as the key to access this form's handle.  Remember, not all forms in this table are actually in the Framework, so we have to check.
						$formHandles[$fids[$fidKey]] = $value['ff_handle'];
					}
				}
			}
					
		}
		if($notAFramework) { list($frid, $fids, $name, $handles, $element_ids, $links, $formHandles) = $this->initializeNull(); }

		$this->XoopsObject();
		//initVar params: key, data_type, value, req, max, opt
		$this->initVar("frid", XOBJ_DTYPE_INT, $frid, false);
		$this->initVar("fids", XOBJ_DTYPE_ARRAY, serialize($fids));
		$this->initVar("name", XOBJ_DTYPE_TXTBOX, $frame_name_q[0]['frame_name'], true, 255);
		$this->initVar("element_ids", XOBJ_DTYPE_ARRAY, serialize($element_ids));
		$this->initVar("handles", XOBJ_DTYPE_ARRAY, serialize($handles));
		$this->initVar("links", XOBJ_DTYPE_ARRAY, serialize($links));
		$this->initVar("formHandles", XOBJ_DTYPE_ARRAY, serialize($formHandles));
	}

	function initializeNull() {
		$ret[] = 0; // frid
		$ret[] = array(); //fids
		$ret[] = ""; // name
		$ret[] = array(); // handles 
		$ret[] = array(); // element_ids
		$ret[] = array(); // links
		$ret[] = array(); // formHandles
		return $ret;
	}

}

class formulizeFrameworkLink extends XoopsObject {
	function formulizeFrameworkLink($lid=""){
		
		// validate $lid
		global $xoopsDB;
		if(!is_numeric($lid)) {
			// set empty defaults
			$frid = "";
			$form1 = "";
			$form2 = "";
			$key1 = "";
			$key2 = "";
			$common = "";
			$relationship = "";
		} else {		
			$link_q = q("SELECT * FROM " . $xoopsDB->prefix("formulize_framework_links") . " WHERE fl_id=\"" . mysql_real_escape_string($lid). "\"");
			if(!isset($link_q[0])) {
				// set empty defaults
				$frid = "";
				$form1 = "";
				$form2 = "";
				$key1 = "";
				$key2 = "";
				$common = "";
				$relationship = "";
			} else {
				$frid = $link_q[0]['fl_frame_id'];
				$form1 = $link_q[0]['fl_form1_id'];
				$form2 = $link_q[0]['fl_form2_id'];
				$key1 = $link_q[0]['fl_key1'];
				$key2 = $link_q[0]['fl_key2'];
				$common = $link_q[0]['fl_common_value'];
				$relationship = $link_q[0]['fl_relationship'];
			}
		}

		$this->XoopsObject();
		//initVar params: key, data_type, value, req, max, opt
		$this->initVar("frid", XOBJ_DTYPE_INT, $frid, true);
		$this->initVar("form1", XOBJ_DTYPE_INT, $form1, true);
		$this->initVar("form2", XOBJ_DTYPE_INT, $form2, true);
		$this->initVar("key1", XOBJ_DTYPE_INT, $key1, true);
		$this->initVar("key2", XOBJ_DTYPE_INT, $key2, true);
		$this->initVar("common", XOBJ_DTYPE_INT, $common, true);
		$this->initVar("relationship", XOBJ_DTYPE_INT, $relationship, true);
	}
}

class formulizeFrameworksHandler {
	var $db;
	function formulizeFrameworksHandler(&$db) {
		$this->db =& $db;
	}
	function &getInstance(&$db) {
		static $instance;
		if (!isset($instance)) {
			$instance = new formulizeFrameworksHandler($db);
		}
		return $instance;
	}
	function &create() {
		return new formulizeFramework();
	}

	function get($frid) {
		$frid = intval($frid);
		static $cachedFrameworks = array();
		if(isset($cachedFrameworks[$frid])) {
			return $cachedFrameworks[$frid];
		}
		if($frid > 0) {
			$cachedFrameworks[$frid] = new formulizeFramework($frid);
			return $cachedFrameworks[$frid];
		}
		return false;
	}

	function getFrameworksByForm($fid) {
		static $cachedResults = array();
		if($cachedResults[$fid]) { return $cachedResults[$fid]; }
		$ret = array();
		$sql = 'SELECT * FROM '.$this->db->prefix("formulize_framework_forms").' WHERE ff_form_id='.intval($fid);

		$result = $this->db->query($sql);

		$foundFrameworks = array();
		while( $myrow = $this->db->fetchArray($result) ){
			if(!isset($foundFrameworks[$myrow['ff_frame_id']])) { // check for duplicate entries, although there shouldn't be any given how the framework system logic is written
				$foundFrameworks[$myrow['ff_frame_id']] = true;
				$framework = new formulizeFramework($myrow['ff_frame_id']);
				$ret[$framework->getVar('frid')] =& $framework;
				unset($framework);
			}
		}
		$cachedResults[$fid] = $ret;
		return $ret;

	}

}
?>