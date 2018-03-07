<?php

include_once XOOPS_ROOT_PATH."/dara_helper_functions.php";

if(!function_exists("getInstructorName")) {
    function getInstructorName($instructor, $activeYear) {
        $instructorName = display($instructor, 'instr_assignments_instructor');
        $acceptanceStatus = getData('', 23, "hr_annual_accept_status_instructor/**/$instructorName/**/=][hr_annual_accept_status_year/**/$activeYear/**/=");
        return strstr(display($acceptanceStatus[0], "hr_annual_accept_status_accepted"), "Accepted") ? $instructorName : "TBA";
    }
}

$courseActive = display($entry, 'ro_module_course_active');
if($courseActive == 'No') {
    return '';
}

$code = display($entry, 'ro_module_course_code');
$title = display($entry, 'ro_module_course_title');
$displayEnrollment = (isset($_POST['search_ro_module_grad_undergrad']) AND $_POST['search_ro_module_grad_undergrad'] == '=Undergraduate' ) ? true : false;
if($displayEnrollment) {
    $enrollment_controls = display($entry, 'ro_module_enrolment_controls');
    $enrollment_controls = strstr($enrollment_controls, ' - ') ? substr($enrollment_controls,0,1) : $enrollment_controls;
    $enrollment_controls = $enrollment_controls . " " . display($entry, 'ro_module_enrolment_control_desc');
}

// load the revision data...
global $indexedLockData, $compareOn;

if(!is_array($indexedLockData) AND isset($_POST['compareDate']) AND $_POST['compareDate'] !== '') {
    
    $lockDataSource = getData('',22,$_POST['compareDate']);
    $lockData = unserialize(display($lockDataSource[0], 'lock_dates_data'));
    foreach($lockData as $thisLockedEntry) {
        $sectionIds = internalRecordIds($thisLockedEntry, 4);
        $indexedLockData[$sectionIds[0]] = $thisLockedEntry;
    }
    $compareOn = true;
} elseif(!is_array($indexedLockData)) {
    $compareOn = false;
}

$activeYearParts = explode("_", $_POST['search_ro_module_year']);
$activeYear = $activeYearParts[2]; // qsf filter on the RO page will have _ in it that means the third item is the actual year value
$sectionIds = internalRecordIds($entry, 4);
$sections = array();
$revSections = array();
$times = array();
$revTimes = array();
$rooms = array();
$revRooms = array();
$inst = array();
$revInst = array();
$tentInst = array();
$revTentInst = array();
$titles = array();
$revTitles = array();
foreach($sectionIds as $i=>$sectionId) {
    $section = getData(18, 4, $sectionId, 'AND', '', '', '', 'sections_section_number');
    $sections[$i] = display($section[0], 'sections_section_number');
    $revSections[$i] = display($indexedLockData[$sectionId], 'sections_section_number');
    $times[$i] = makeSectionTimes($section[0]);
    $revTimes[$i] = makeSectionTimes($indexedLockData[$sectionId]);
    $rooms[$i] = display($section[0], 'sections_practica_room');
    $revRooms[$i] = display($indexedLockData[$sectionId], 'sections_practica_room');
    $titles[$i] = display($section[0], 'course_components_section_title_optional');
    $revTitles[$i] = display($indexedLockData[$sectionId], 'course_components_section_title_optional');
    $instructorData = getData('', 15, 'instr_assignments_section_number/**/'.$sectionId.'/**/=');
    foreach($instructorData as $instructor) {
        $inst[$i][] = getInstructorName($instructor, $activeYear);
        $tentInst[$i][] = display($instructor, 'instr_assignments_instructor');
    }
    $revInstructors = display($indexedLockData[$sectionId], 'instr_assignments_instructor');
    if(is_array($revInstructors)) {
        foreach($revInstructors as $thisRevInstructor) {
            $revInst[$i][] = getInstructorName($thisRevInstructor, $activeYear);
            $revTentInst[$i][] = display($thisRevInstructor, 'instr_assignments_instructor');
        }
    } else {
        $revInst[$i][] = getInstructorName($revInstructors, $activeYear);
        $revTentInst[$i][] = display($revInstructors, 'instr_assignments_instructor');
    }

}

asort($sections);

$html = "<tr nobr=\"true\"><td style=\"border-top: 1px solid black;\" ><b>$code</b></td>";
$html .= "<td style=\"border-top: 1px solid black;\" >$title";
$start = true;
foreach($sections as $i=>$section) {
    if(!$start) {
        $html .= "<tr nobr=\"true\"><td></td><td>";
    }
    if($sectionTitle = compData($titles[$i],$revTitles[$i])) {
        if($start) {
            $html .= "<br>";
        }
        $html .= "<i>$sectionTitle</i>";
    }
    $html .= "</td><td style=\"border-top: 1px solid black;\"><b>".compData($section, $revSections[$i])."</b></td>";
    $html .= "<td style=\"border-top: 1px solid black;\">";
    $timeStart = true;
    foreach($times[$i] as $x=>$time) {
        if(!$timeStart) {
            $html .= "<br>";
        }
        $html .= compData($time, $revTimes[$i][$x]);
        $timeStart = false;
    }
    $html .= "</td>";
    $html .= "<td style=\"border-top: 1px solid black;\">".compData($rooms[$i], $revRooms[$i])."</td>";
    $html .= "<td style=\"border-top: 1px solid black;\">";
    $instStart = true;
    foreach($inst[$i] as $x=>$instructor) {
        if(!$instStart) {
            $html .= "<br>";
        }
        $instText = compData($instructor, $revInst[$i][$x]);
        $html .= $instText ? $instText : 'TBA';
        $instStart = false;
    }
    $html .= "</td>";
    if(isset($_POST['showTentInst']) AND $_POST['showTentInst']=="Yes") {
        $html .= "<td style=\"border-top: 1px solid black;\">";
        $tentInstStart = true;
        foreach($tentInst[$i] as $x=>$instructor) {
            if(!$tentInstStart) {
                $html .= "<br>";
            }
            $tentInstText = compData($instructor, $revTentInst[$i][$x]);
            $html .= $tentInstText ? $tentInstText : 'TBA';
            $tentInstStart = false;
        }
        $html .= "</td>";
    }
    
    if($displayEnrollment) {
        $html .= "<td style=\"border-top: 1px solid black;\">$enrollment_controls</td>";
    }
    
    $html .= "</tr>";
    $start = false;
}

if(isset($_POST['showCoords']) AND $_POST['showCoords'] AND $coordName = display($entry,'ro_module_course_coordinator')) {
    $html .= "<tr nobr=\"true\"><td></td>
    <td></td>
    <td style=\"border-top: 1px solid black;\"><b>Coordinator</b></td>
    <td style=\"border-top: 1px solid black;\"></td>
    <td style=\"border-top: 1px solid black;\"></td>
    <td style=\"border-top: 1px solid black;\">".$coordName."</td>";
    if($displayEnrollment) {
        $html .= "<td style=\"border-top: 1px solid black;\">$enrollment_controls</td>";
    }
    $html .= "</tr>";
}


return $html;
