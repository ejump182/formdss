<?php

// Load the files we need:
require_once XOOPS_ROOT_PATH.'/libraries/htmltodocx/phpword/PHPWord.php';
require_once XOOPS_ROOT_PATH.'/libraries/htmltodocx/simplehtmldom/simple_html_dom.php';
require_once XOOPS_ROOT_PATH.'/libraries/htmltodocx/htmltodocx_converter/h2d_htmlconverter.php';
require_once XOOPS_ROOT_PATH.'/libraries/htmltodocx/example_files/styles.inc';
// Functions to support this example.
require_once XOOPS_ROOT_PATH.'/libraries/htmltodocx/documentation/support_functions.inc';

require_once XOOPS_ROOT_PATH.'/libraries/tcpdf/tcpdf.php';

function daraShowContractLinks($fid, $frid, $type) {

    // cache the export list of entries that is part of this pageload, so we can pick it up next page load if we're trying to generate all contracts
    $exportTime = time();
	$queryFile = fopen(XOOPS_ROOT_PATH . "/cache/contractEntryIdsQuery_".$exportTime, "w");
    fwrite($queryFile, $fid."\n");
    global $xoopsUser;
    $exportUid = $xoopsUser ? $xoopsUser->getVar('uid') : 0;
    fwrite($queryFile, $exportUid."\n");
    fwrite($queryFile, $GLOBALS['formulize_queryForExport']);
    fclose($queryFile);
    formulize_scandirAndClean(XOOPS_ROOT_PATH."/cache/", "contractEntryIdsQuery_"); 
	setcookie('contractEntryIds',$exportTime);
    
    
    list($entries, $data) = daraGatherContractData();
    if($data == "" AND count($entries)>0) {
        $filter = array();
        foreach($entries as $entryId) {
            $filter[] = "entry_id/**/$entryId/**/=";
        }
        $data = getData($frid, $fid, implode('][',$filter), "OR");
    }

    $ROcourses = array();
    $INSTcourses = array();
    foreach($data as $entry) {
        if($html = daraProcessTemplate($type, $entry)) {
            if(!is_array($html)) {
                $html = array($html);
            }
            if($type != "RO" AND $type != "INST") {
                $restartNumbering = true;
                foreach($html as $section) {
                    $pdf = daraWriteContract('pdf', $section, "", $restartNumbering, $type);
                    $doc = daraWriteContract('doc', $section, "", $restartNumbering, $type);
                    $restartNumbering = false;
                }
            } elseif($type == "RO") {
                $ROcourses[display($entry, 'ro_module_semester')][] = $html[0];
            } elseif($type == "INST") {
                $INSTcourses[display($entry, 'hr_module_type_of_appointment_contract_t')][] = $html[0];
            }
        }
    }

    if($type == "RO" AND count($ROcourses)>0) {
        
        $frontPageCodes = getData('', 27); // first entry is undergraduate, second is graduate
        if(isset($_POST['search_ro_module_grad_undergrad']) AND $_POST['search_ro_module_grad_undergrad'] == '=Undergraduate' ) {
            $frontPageCodes = htmlspecialchars_decode(display($frontPageCodes[0], 'timetable_front_page_front_page_code'), ENT_QUOTES);        
        } else {
            $frontPageCodes = htmlspecialchars_decode(display($frontPageCodes[1], 'timetable_front_page_front_page_code'), ENT_QUOTES);        
        }
        $enrollmentControls = "<th style=\"border: 1px solid black; color: white; background-color: black;\">Enrollment Controls</th>";
        $colspan = 6;
        $fullHtml = $frontPageCodes;
            
        if(isset($_POST['showTentInst']) AND $_POST['showTentInst']=="Yes") {
            $tentInstHeader = "<th style=\"border: 1px solid black; color: white; background-color: black;\">Tentative Instr.</th>";
            $colspan++;
        } else {
            $tentInstHeader = "";
        }
        if(isset($_POST['showRooms']) AND $_POST['showRooms']=="Yes") {
            $roomsHeader = "<th style=\"border: 1px solid black; color: white; background-color: black;\">Location/Room</th>";
            $colspan++;
        } else {
            $roomsHeader = "";
        }
        $semesterOrder = array('Fall - F', 'Winter/Spring - S', 'Fall-Winter - Y', 'Summer (May, June) - F', 'Summer (July, August) - S', 'Summer - Y');
        $fullHtml .= "<table style=\"border: 1px solid black; width: 100%;\" cellpadding=\"10\"><thead><tr><th style=\"border: 1px solid black; color: white; background-color: black;\">Course Code</th><th style=\"border: 1px solid black; color: white; background-color: black;\">Course Title</th><th style=\"border: 1px solid black; color: white; background-color: black;\">Section</th><th style=\"border: 1px solid black; color: white; background-color: black;\">Day/Time</th>$roomsHeader<th style=\"border: 1px solid black; color: white; background-color: black;\">Instructor</th>$tentInstHeader$enrollmentControls</tr></thead><tbody>";
        foreach($semesterOrder as $sem) {
            if(count($ROcourses[$sem])>0) {
                $fullHtml .= "<tr><td style=\"border: 1px solid black; color: white; background-color: black;\">$sem</td>";
                for($i=1;$i<$colspan;$i++) {
                    $fullHtml .= "<td style=\"border: 1px solid black; color: white; background-color: black;\"></td>";
                }
                $fullHtml .= "</tr>";
                foreach($ROcourses[$sem] as $course) {
                    $fullHtml .= $course;
                }
            }
        }
        $fullHtml .= "</tbody></table>";
        //print "</div><div style='clear: both;'></div><br><br><br><br><br><br><br><br><br><br>".$fullHtml;
        //exit;
        $pdf = daraWriteContract('pdf', $fullHtml, "", true, $type);
        //$doc = daraWriteContract('doc', $fullHtml, "", true, $type);
        $htmlSchedule = $fullHtml;
    }

    if($type == "INST" AND count($INSTcourses)>0) {
        $apptOrder = array('Core (Tenure Stream)','Core (Teaching Stream)','Core (Non-Tenure Stream)','Visitor','Adjunct/Casual Academic','Emeritus','Sessional','Other');
        $fullHtml = "<table style=\"border: 1px solid black;\" cellpadding=\"10\"><thead><tr><th style=\"border: 1px solid black; color: white; background-color: black;\">Instructor</th><th style=\"border: 1px solid black; color: white; background-color: black;\">Course Code</th><th style=\"border: 1px solid black; color: white; background-color: black;\">Course Title</th><th style=\"border: 1px solid black; color: white; background-color: black;\">Section</th><th style=\"border: 1px solid black; color: white; background-color: black;\">Teaching Weight</th><th style=\"border: 1px solid black; color: white; background-color: black;\">Time</th><th style=\"border: 1px solid black; color: white; background-color: black;\">Room</th></tr></thead><tbody>";
        foreach($apptOrder as $appt) {
            if(count($INSTcourses[$appt])>0) {
                $fullHtml .= "<tr><td style=\"border: 1px solid black; color: white; background-color: black;\">$appt</td>";
                for($i=1;$i<7;$i++) {
                    $fullHtml .= "<td style=\"border: 1px solid black; color: white; background-color: black;\"></td>";
                }
                $fullHtml .= "</tr>";
                foreach($INSTcourses[$appt] as $course) {
                    $fullHtml .= $course;    
                }
            }
        }
        $fullHtml .= "</tbody></table>";
        $pdf = daraWriteContract('pdf', $fullHtml, "", true, $type);
        //$doc = daraWriteContract('doc', $fullHtml, "", true, $type);
    }
    
    $pdfPath = "/cache/pdfFile.pdf";
    $docPath = "/cache/docFile.docx";
    $pdfLink = "";
    $docLink = "";
    if($pdf) {
        daraFinishPDF($pdf, $pdfPath);
        formulize_scandirAndClean(XOOPS_ROOT_PATH."/cache/", "pdfFile");
        if(isset($htmlSchedule)) {
            file_put_contents(XOOPS_ROOT_PATH."/cache/htmlTimetable.html", "<!DOCTYPE html><html>$htmlSchedule</html>");
            $pdfLink = "<a href='".XOOPS_URL."$pdfPath'>Download PDF</a> -- <a href='".XOOPS_URL."/cache/htmlTimetable.html' target='_blank'>Download HTML</a>";
        } else {
            $pdfLink = "<a href='".XOOPS_URL."$pdfPath'>Download PDF</a>";    
        }
        
    }
    if($doc) {
        daraFinishWord($doc, $docPath);
        formulize_scandirAndClean(XOOPS_ROOT_PATH."/cache/", "docFile");
        $docLink = "<a href='".XOOPS_URL."$docPath'>Download Word</a>";
    }
    $sep = ($docLink AND $pdfLink) ? " -- " : "";
    print $pdfLink.$sep.$docLink;
}

function daraProcessTemplate($type, $entry) {
    
    if(file_exists(XOOPS_ROOT_PATH.'/'.$type.'_template.php')) {
        return include XOOPS_ROOT_PATH.'/'.$type.'_template.php';
    } else {
        return false;
    }
    
}

function daraGatherContractData($entryId, $fid, $frid="") {
    
    static $entries = array();
    static $data = "";
    
    if($entryId === 'all') {
        
        // read the cached query to deduce the ids of everything we're supposed to generate
        $queryData = file(XOOPS_ROOT_PATH."/cache/contractEntryIdsQuery_".intval($_COOKIE['contractEntryIds']));
        
        //var_dump($queryData);
        
        global $xoopsUser;
        $exportUid = $xoopsUser ? $xoopsUser->getVar('uid') : 0;
        $groups = $xoopsUser ? $xoopsUser->getGroups() : array(0=>XOOPS_GROUP_ANONYMOUS);
        // query fid must match passed fid in URL, and the current user id must match the userid at the time the export file was created
        if (trim($queryData[0]) == intval($fid) AND trim($queryData[1]) == $exportUid) {
            $GLOBALS['formulize_doingExport'] = true;
            unset($queryData[0]); // get rid of the fid and userid lines
            unset($queryData[1]);
            $data_sql = implode(" ", $queryData); // merge all remaining lines into one string to send to getData
            $data = getData($frid, $fid, $data_sql);
            //exit();
            // probably don't need to split the query into bits
            /*$limitStart = 0;
            $limitSize = 50;    // export in batches of 50 records at a time
            do {
                // load part of the data, since a very large dataset could exceed the PHP memory limit
                $data = getData($frid, $fid, $data_sql, "AND", null, $limitStart, $limitSize);
                if (is_array($data)) {
                    
                    // get the next set of data
                    set_time_limit(90);
                    $limitStart += $limitSize;
                }
            } while (is_array($data) and count($data) > 0);
            */
        }            
    } elseif($entryId AND security_check($fid, $entryId)) {
        $entries[] = $entryId;
    } else {
        return array($entries, $data);
    }
    
}

function daraWriteContract($type, $content, $title="", $restartNumbering = true, $templateType) {
    static $pdf = "";
    static $doc = "";
    static $section = "";
    switch($type) {
        case 'pdf':
            if($content) {
                if(!$pdf) {
                    $pdf = daraStartPDF($title, '', $templateType);
                } else {
                    if($restartNumbering) {
                        $pdf->startPageGroup();
                    }
                    $pdf->writeHTML('<br pagebreak="true"/>', true, 0);
                }
                $pdf->writeHTML($content, true, 0);
            }
            return $pdf;
        case 'doc':
            if($content) {
                if(!$doc) {
                    $doc = new PHPWord();
                    $section = daraStartWord($doc, '', true, $templateType);
                } else {
                    $section = daraStartWord($doc, $section, $restartNumbering, $templateType);
                }
                daraAppendWord($doc, $section, '<html><body>'.$content.'</body></html>');
            }
            return $doc;
    }
    return false;
}

function daraFinishPDF($pdf, $path) {
    $PDFfile = fopen(XOOPS_ROOT_PATH.$path, 'w');
    fclose($PDFfile);
	$pdf->Output(XOOPS_ROOT_PATH.$path, "F");
}

class SchedulePDF extends TCPDF {
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        //$image_file = K_PATH_IMAGES.'danielsfooter.png';
        //$this->Image($image_file, 50, 600, 150, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Page number
        $this->Cell(0, 8, 'Created on: '.date('F j, Y').'. Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
    public function Header() {
        //$this->Cell(0, 8, 'John H. Daniels Faculty of Architecture, Landscape, and Design', 0, false, 'L', 0, '', 0, false, 'T', 'M');
        //$this->Cell(0, 8, 'Year, Type Academic Timetable', 0, 1, 'L', 0, '', 0, false, 'T', 'M');
        // we might or might not have qsf parts to trim out...
        $yearParts = strstr($_POST['search_ro_module_year'], "_") ? explode('_', $_POST['search_ro_module_year']) : array(2=>$_POST['showYear']);
        $yearParts = explode("/",$yearParts[2]);
        $year = intval($yearParts[0])."/".intval($yearParts[1]); // all in the name of sanitization!
        $type = str_replace("=", "", $_POST['search_ro_module_grad_undergrad']);
        $internalUseOnly = (isset($_POST['showTentInst']) AND $_POST['showTentInst'] == 'Yes') ? "FOR INTERNAL USE ONLY - " : "";
        if($internalUseOnly) {
            $spacer = "";
        } else {
            $spacer = "                                                 ";
        }
        if($type == "Graduate") {
            $spacer .= "         ";
        } elseif($type == "Undergraduate") {
            $spacer .= "";
        } else { // instructor-based timetable
            $spacer .= "                        ";
        }
        $type = $type ? " $type" : "";
        $internalUseOnly = (isset($_POST['showTentInst']) AND $_POST['showTentInst'] == 'Yes') ? "FOR INTERNAL USE ONLY - " : "";
        $this->MultiCell(0, 8, "John H. Daniels Faculty of Architecture, Landscape, and Design\n".$internalUseOnly.$year.$type." Academic Timetable".$spacer."                                                                                              M=Monday|T=Tuesday|W=Wednesday|R=Thursday|F=Friday", 0, 'L');
        //$this->Cell(20,8,'M=Monday|T=Tuesday|W=Wednesday|R=Thursday|F=Friday', 0, false, 'R');

    }
}

class DocumentPDF extends TCPDF {
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        //$image_file = K_PATH_IMAGES.'danielsfooter.png';
        //$this->Image($image_file, 50, 600, 150, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Page number
        $this->Cell(0, 8, 'Page '.$this->getPageNumGroupAlias().'/'.$this->getPageGroupAlias(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->Image(XOOPS_ROOT_PATH.'/libraries/tcpdf/examples/images/Address.png', 165, 258, 29);
        $this->Image(XOOPS_ROOT_PATH.'/libraries/tcpdf/examples/images/Logo.png', 15, 261, 59);
    }
    public function Header() {
        $this->Image(XOOPS_ROOT_PATH.'/libraries/tcpdf/examples/images/Daniels.png', 170, 5, 22);
    }
}


function daraStartPDF($doc_title, $doc_keywords, $templateType) {

    $orientation = ($templateType == "RO" OR $templateType == "INST") ? "L" : "P";

    if($templateType == "RO" OR $templateType == "INST") {
        $pdf = new SchedulePDF($orientation, PDF_UNIT, PDF_PAGE_FORMAT, true);    
    } else {
        $pdf = new DocumentPDF($orientation, PDF_UNIT, PDF_PAGE_FORMAT, true);    
    }
	
	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor(PDF_AUTHOR);
	$pdf->SetTitle($doc_title);
	$pdf->SetSubject($doc_title);
	$pdf->SetKeywords($doc_keywords);

	//set margins
    $marginTop = ($templateType == "RO" OR $templateType == "INST") ? 15 : 35;
	$pdf->SetMargins(PDF_MARGIN_LEFT, $marginTop, PDF_MARGIN_RIGHT, true);
	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, 27); // number is bottom margin, default is 25
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFontSize(10);
    if($templateType == "RO" OR $templateType == "INST") {
        $pdf->setFooterFont(array('Helvetica', '', 8));
        $pdf->setHeaderFont(array('Helvetica', '', 8));
    } else {
        $pdf->setFooterFont(array('Helvetica', '', 10));
    }
    $pdf->setFooterData($tc=array(0,0,0), $lc=array(255,255,255));


    
	$pdf->setLanguageArray($l); //set language items
	// set font
	//$TextFont = (@_PDF_LOCAL_FONT && file_exists(ICMS_PDF_LIB_PATH.'/fonts/'._PDF_LOCAL_FONT.'.php')) ? _PDF_LOCAL_FONT : 'dejavusans';
	//$pdf -> SetFont($TextFont);

    $pdf->SetFontSize(10);     
    $tagvs = array('p' => array( array('h' => '', 'n' => 1), array('h' => '', 'n' => 1) ) );
    $pdf->setHtmlVSpace($tagvs);
    
    $pdf->startPageGroup();
    
	$pdf->AddPage();
    
    return $pdf;
}

function daraFinishWord(&$doc, $path) {
    // Save File
    $docFile = fopen(XOOPS_ROOT_PATH.$path, 'w');
    fclose($docFile);
    $objWriter = PHPWord_IOFactory::createWriter($doc, 'Word2007');
    $objWriter->save(XOOPS_ROOT_PATH.$path);
}

function daraStartWord(&$doc, $section, $restartNumbering=true, $templateType) {
    // New section for this content
    if($restartNumbering) {
        $sectionStyle['restartPageNumbering'] = 1;
        if($templateType == "RO") {
            $sectionStyle['orientation'] = 'landscape';
        }
        $section = $doc->createSection($sectionStyle);
        if($templateType != "RO" AND $templateType != "INST") {
            // Add header
            $header = $section->createHeader();
            $header->addImage(XOOPS_ROOT_PATH."/libraries/tcpdf/examples/images/Daniels-Word.png");
        }
        // Add footer
        $footer = $section->createFooter();
        if($templateType != "RO" AND $templateType != "INST") {
            $footer->addImage(XOOPS_ROOT_PATH."/libraries/tcpdf/examples/images/Footer-Word.png");
            $footer->addPreserveText('Page {PAGE}/{SECTIONPAGES}', null, array('align'=>'center'));
        }
        
    } else {
        $section->addPageBreak();
    }
    
    return $section;
}

function daraAppendWord(&$doc, &$section, $content) {
    
       
    // HTML Dom object:
    $html_dom = new simple_html_dom();
    $html_dom->load($content);
    // Note, we needed to nest the html in a couple of dummy elements.
    
    // Create the dom array of elements which we are going to work on:
    $html_dom_array = $html_dom->find('html',0)->children();
    
    // We need this for setting base_root and base_path in the initial_state array
    // (below). We are using a function here (derived from Drupal) to create these
    // paths automatically - you may want to do something different in your
    // implementation. This function is in the included file 
    // documentation/support_functions.inc.
    $paths = htmltodocx_paths();
    
    // Provide some initial settings:
    $initial_state = array(
      // Required parameters:
      'phpword_object' => &$doc, // Must be passed by reference.
      // 'base_root' => 'http://test.local', // Required for link elements - change it to your domain.
      // 'base_path' => '/htmltodocx/documentation/', // Path from base_root to whatever url your links are relative to.
      'base_root' => $paths['base_root'],
      'base_path' => $paths['base_path'],
      // Optional parameters - showing the defaults if you don't set anything:
      'current_style' => array('size' => '10'), // The PHPWord style on the top element - may be inherited by descendent elements.
      'parents' => array(0 => 'body'), // Our parent is body.
      'list_depth' => 0, // This is the current depth of any current list.
      'context' => 'section', // Possible values - section, footer or header.
      'pseudo_list' => TRUE, // NOTE: Word lists not yet supported (TRUE is the only option at present).
      'pseudo_list_indicator_font_name' => 'Wingdings', // Bullet indicator font.
      'pseudo_list_indicator_font_size' => '7', // Bullet indicator size.
      'pseudo_list_indicator_character' => 'l ', // Gives a circle bullet point with wingdings.
      'table_allowed' => TRUE, // Note, if you are adding this html into a PHPWord table you should set this to FALSE: tables cannot be nested in PHPWord.
      'treat_div_as_paragraph' => TRUE, // If set to TRUE, each new div will trigger a new line in the Word document.
          
      // Optional - no default:    
      'style_sheet' => htmltodocx_styles_example(), // This is an array (the "style sheet") - returned by htmltodocx_styles_example() here (in styles.inc) - see this function for an example of how to construct this array.
      );    
    
    // Convert the HTML and put it into the PHPWord object
    htmltodocx_insert_html($section, $html_dom_array[0]->nodes, $initial_state);
    
    // Clear the HTML dom object:
    $html_dom->clear(); 
    unset($html_dom);
}