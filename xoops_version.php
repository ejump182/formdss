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

$modversion['name'] = _MI_FORMULAIRE_NAME;
$modversion['version'] = _MI_VERSION;
$modversion['description'] = _MI_FORMULAIRE_DESC;
$modversion['author'] = "xoops-themes.org";                                            
$modversion['credits'] = "<a href='http://www.xoops-themes.org/'>xoops-themes</a>";
$modversion['help'] = "";
$modversion['license'] = "GPL see LICENSE";
$modversion['official'] = 0;
$modversion['image'] = "images/formulaire.png";
$modversion['dirname'] = "formulaire";

$modversion['sqlfile']['mysql'] = "sql/mysql.sql";

// Tables created by sql file (without prefix!)
$modversion['tables'][0] = "form";
$modversion['tables'][1] = "form_id";
$modversion['tables'][2] = "form_menu";
$modversion['tables'][3] = "form_form";

// Admin things
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = "admin/formindex.php";
$modversion['adminmenu'] = "admin/menu.php";

// Menu -- content in main menu block
$modversion['hasMain'] = 1;

// Templates
$modversion['templates'][1]['file'] = 'formulaire.html';
$modversion['templates'][1]['description'] = '';

//Bloc du milieu 
//$modversion['templates'][2]['file'] = "formulaire_menu.html";
//$modversion['templates'][2]['description'] = "";

//	Module Configs
// $xoopsModuleConfig['t_width']
$modversion['config'][1]['name'] = 't_width';
$modversion['config'][1]['title'] = '_MI_FORMULAIRE_TEXT_WIDTH';
$modversion['config'][1]['description'] = '';
$modversion['config'][1]['formtype'] = 'textbox';
$modversion['config'][1]['valuetype'] = 'int';
$modversion['config'][1]['default'] = '30';

// $xoopsModuleConfig['t_max']
$modversion['config'][2]['name'] = 't_max';
$modversion['config'][2]['title'] = '_MI_FORMULAIRE_TEXT_MAX';
$modversion['config'][2]['description'] = '';
$modversion['config'][2]['formtype'] = 'textbox';
$modversion['config'][2]['valuetype'] = 'int';
$modversion['config'][2]['default'] = '255';

// $xoopsModuleConfig['ta_rows']
$modversion['config'][3]['name'] = 'ta_rows';
$modversion['config'][3]['title'] = '_MI_FORMULAIRE_TAREA_ROWS';
$modversion['config'][3]['description'] = '';
$modversion['config'][3]['formtype'] = 'textbox';
$modversion['config'][3]['valuetype'] = 'int';
$modversion['config'][3]['default'] = '5';

// $xoopsModuleConfig['ta_cols']
$modversion['config'][4]['name'] = 'ta_cols';
$modversion['config'][4]['title'] = '_MI_FORMULAIRE_TAREA_COLS';
$modversion['config'][4]['description'] = '';
$modversion['config'][4]['formtype'] = 'textbox';
$modversion['config'][4]['valuetype'] = 'int';
$modversion['config'][4]['default'] = '35';

// $xoopsModuleConfig['delimeter'] 
$modversion['config'][5]['name'] = 'delimeter';
$modversion['config'][5]['title'] = '_MI_FORMULAIRE_DELIMETER';
$modversion['config'][5]['description'] = '';
$modversion['config'][5]['formtype'] = 'select';
$modversion['config'][5]['valuetype'] = 'text';
$modversion['config'][6]['default'] = 'space';
$modversion['config'][5]['options'] = array(_MI_FORMULAIRE_DELIMETER_SPACE=>'space', _MI_FORMULAIRE_DELIMETER_BR=>'br');

// $xoopsModuleConfig['method']
$modversion['config'][6]['name'] = 'method';
$modversion['config'][6]['title'] = '_MI_FORMULAIRE_SEND_METHOD';
$modversion['config'][6]['description'] = '_MI_FORMULAIRE_SEND_METHOD_DESC';
$modversion['config'][6]['formtype'] = 'select';
$modversion['config'][6]['valuetype'] = 'text';
$modversion['config'][6]['default'] = 'email';
$modversion['config'][6]['options'] = array(_MI_FORMULAIRE_SEND_METHOD_MAIL=>'email', _MI_FORMULAIRE_SEND_METHOD_PM=>'pm');

/*// $xoopsModuleConfig['group']
$modversion['config'][7]['name'] = 'group';
$modversion['config'][7]['title'] = '_MI_FORMULAIRE_SEND_GROUP';
$modversion['config'][7]['description'] = '';
$modversion['config'][7]['formtype'] = 'group';
$modversion['config'][7]['valuetype'] = 'int';
$modversion['config'][7]['default'] = '1';


//bloc
$modversion['blocks'][1]['file'] = "mymenu.php";
$modversion['blocks'][1]['name'] = _MI_FORMULAIREMENU_BNAME;
$modversion['blocks'][1]['description'] = "Zeigt individuelles Menu an";
$modversion['blocks'][1]['show_func'] = "block_FORMULAIREMENU_show";

// $xoopsModuleConfig['poids']
$modversion['config'][7]['name'] = 'weight';
$modversion['config'][7]['title'] = 'weight';
$modversion['config'][7]['description'] = '';
$modversion['config'][7]['formtype'] = 'upload';
$modversion['config'][7]['valuetype'] = 'int';
$modversion['config'][7]['default'] = '50000';

//Bloc du milieu 
$modversion['blocks'][8]['file'] = "bloc_centre.php";
$modversion['blocks'][8]['name'] = "Formulaires";
$modversion['blocks'][8]['description'] = "";
$modversion['blocks'][8]['template'] = 'formulaire_menu.html';
*/
?>