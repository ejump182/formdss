<?php
include("admin_header.php");
global $xoopsDB, $xoopsConfig;

if( is_dir(formulize_ROOT_PATH."/language/".$xoopsConfig['language']."/mail_template") ){
	$template_dir = formulize_ROOT_PATH."/language/".$xoopsConfig['language']."/mail_template";
}else{
	$template_dir = formulize_ROOT_PATH."/language/english/mail_template";
}
if ( file_exists("../language/".$xoopsConfig['language']."/main.php") ) {
	include "../language/".$xoopsConfig['language']."/main.php";
} else {
	include "../language/english/main.php";
}
        xoops_cp_header();

if(!isset($HTTP_POST_VARS['form'])){
	$form = isset ($HTTP_GET_VARS['form']) ? $HTTP_GET_VARS['form'] : '1';
}else {
	$form = $HTTP_POST_VARS['form'];
}
if(!isset($HTTP_POST_VARS['req'])){
	$req = isset ($HTTP_GET_VARS['req']) ? $HTTP_GET_VARS['req'] : '';
}else {
	$req = $HTTP_POST_VARS['req'];
}

if (isset ($li)) {
	/************* Cr�ation du fichier *************/
	$req = array();
	$req[] = array();
	$tmp0 = $tmp1 = 0;
	$sql = "SELECT * FROM " . $xoopsDB->prefix("form_form");
	$result = mysql_query($sql) or die("Requete SQL ligne 53");
	if ($result) {
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	       		$req[$row["ele_id"]][0] = $row["id_form"];
	       		$req[$row["ele_id"]][1] = $row["id_req"];
	       		$req[$row["ele_id"]][2] = $row["ele_type"];
	       		$req[$row["ele_id"]][3] = $row["ele_caption"];
	       		$req[$row["ele_id"]][4] = $row["ele_value"];
	       		$req[$row["ele_id"]][5] = $row["uid"];
	       		$req[$row["ele_id"]][6] = $row["date"];
	       		$row["ele_id"] = $row["id_req"];
          	}
	}
	
	$fp = fopen ("".formulize_ROOT_PATH."upload/form.csv", "w") or die ("Fichier non cr��");
	
		
	$msg = 'formulize;Requete;Titre;;;;Valeur
';
	
	foreach ($req as $ele_id => $v) {
		if ($tmp0 == $v[0] && $tmp1 == $v[1])
			$msg .= ';;'.$v[3].';;;;'.$v[4].'
';
		else if ($v[3] != null)
			$msg .= '
'.$v[0].';'.$v[1].';'.$v[3].';;;;'.$v[4].'
';
		$tmp0 = $v[0];
		$tmp1 = $v[1];
	}	
	
	fwrite ($fp, $msg) or die("Fichier non �crit");
	fclose ($fp) or die ("fichier non ferm�");
	
	echo '<script language="JavaScript">
 	document.window.location("".formulize_ROOT_PATH."/upload/form.csv");
	</script>';
	echo '<META HTTP-EQUIV="refresh" CONTENT="5; URL="/upload/form.csv">';
	
	redirect_header("export.php?li=1", 2, _FORM_EXP_CREE);
}
else {
	echo '<a href= "../upload/form.csv" target="_blank">Enregistrer le fichier form.csv</a>';
	echo '<br><br><a href= "formindex.php">Revenir � la page d\'accueil</a>';
}	


include 'footer.php';
    xoops_cp_footer();

?>