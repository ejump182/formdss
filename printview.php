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

require_once "../../mainfile.php";
//include XOOPS_ROOT_PATH.'/header.php';

PRINT "<HTML>";
PRINT "<HEAD>";
print "<link rel='stylesheet' type='text/css' media='all' href='".getcss($xoopsConfig['theme_set'])."'>";
PRINT "</HEAD>";

$formframe = $_POST['formframe'];
$ventry =  $_POST['lastentry'];
$mainform = $_POST['mainform'];

//print "<p> formframe = ".$formframe."</p>";
//print "<p> mainform = ".$mainform."</p>";
//print "<p> ventry = ".$ventry."</p>";

print "<center>"; 
print "<table width=100%><tr><td width=5%></td><td width=90%>";

include_once XOOPS_ROOT_PATH . "/modules/formulize/include/formdisplay.php";
displayForm($formframe, $ventry, $mainform, "", "{NOBUTTON}"); // if it's a single and they don't have group or global scope

print "</td><td width=5%></td></tr></table>";
print "</center></body>";

PRINT "</HTML>";
//include XOOPS_ROOT_PATH.'/footer.php';
