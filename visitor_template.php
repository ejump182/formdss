<?php

$html = "

<P>$date</P>

<P>$name<BR>$address<BR>$city, $province<BR>$pc, $country</P>

<P>$email</P>

<P>Re: $year Academic Year Visiting Professor Appointment</P>

<P>Dear $name:</P>

<P>I would like to invite you to the University of Toronto as a Visiting Professor Position at the rank of $rank with the John H. Daniels Faculty of Architecture, Landscape, and Design, for the period $startdate to $enddate.</P> 

<P>The purpose of your visit will be to teach in the $program:<UL>";
foreach($courses as $course) {
	$html .= "<LI>{$course['code']} - {$course['title']}, Section {$course['section']}<BR>{$course['times']}<BR>{$course['room']}</LI>";
}

$html .= "</UL>It is expected that you will establish office hours to meet with your students and that you be available during midterm and final review weeks to serve on Daniels studio and thesis reviews as well as after end of the term in case there are questions/issues regarding grades.</P>

<P>We will offer an honorarium of CAD $salary for this teaching assingment.</P>";  


if($travelAllowance) {
    $html .= "<P>Upon production of original receipts, you will be eligible for reimbursement for your travel and accommodation expenses, up to a maximum of CAD $travelAllowance.</P>";
}

$html .= "<P>While you are here, the Daniels Faculty will provide you with office space, access to it and library resources, and a departmental e-mail address.</P>

<P>The Office of the Vice-President and Provost maintains a set of links to important policies that will govern any teaching or research at <A HREF='http://www.governingcouncil.utoronto.ca/policies'>http://www.governingcouncil.utoronto.ca/policies</A>.	In particular, I would like to draw your attention to the Code of Behaviour on Academic Matters at <A HREF='http://www.governingcouncil.utoronto.ca/policies/behaveac.htm'>http://www.governingcouncil.utoronto.ca/policies/behaveac.htm</A>, and the Policy on Conflict of Interest Academic Staff at <A HREF='http://www.governingcouncil.utoronto.ca/policies/conacad.htm'>http://www.governingcouncil.utoronto.ca/policies/conacad.htm</A></P>

<P>This letter, and the documents referred to in it, constitute the entire agreement between you and the University. There are no representations, warranties or other commitments apart from these documents.</P>";

if($immigration) {
    $html .= "<P><B>Immigration Issues</B><BR>This offer is subject to compliance with the immigration laws of Canada (as contained in the <I>Immigration and Refugee Protection Act</I> and in the regulations made in pursuance of that <I>Act</I>) and it is conditional upon any approvals, authorizations and/or permits in respect of your employment that may be required under that <I>Act</I> or the regulations.</P>
<P>Upon your acceptance of our offer of employment you will receive from the Office of the Vice-President and Provost instructions on how to begin the process for applying for the temporary Work Permit that you will require for your employment with the University and for Permanent Resident (\"landed immigrant\") status in Canada. To assist with both of these processes we have engaged the Toronto law firm of Rekai LLP. As the University's legal counsel, we have instructed the law firm of Rekai LLP to assist you with all aspects of both your temporary and permanent immigration law requirements. Mr. Peter Rekai will be in touch with you directly as soon as Service Canada has confirmed our offer of employment to you. By accepting the services of the law firm of Rekai LLP, you consent to the release of any and all information pertaining to your and accompanying family members' admissibility to Canada by Rekai LLP to the Office of the Vice-President and Provost of the University of Toronto. This information will be held in strict confidence by the Office of the Vice-President and Provost and will not be released by that Office without your prior written permission.</P>
<P>The University will be responsible for all of Rekai LLP’s routine legal fees (save and except as noted below) and for the Government of Canada's filing fees for your applications provided you remain employed by the University of Toronto. You will be responsible for all other incidental expenses related to your immigration law requirements. This includes, but is not limited to, such incidental matters as the cost of medical examinations, photos, documents, police clearance certificates as well as the expenses to be incurred by Rekai LLP on your behalf for couriers, translations, photocopying, telecopying and long distance. Should your employment with the University cease for any reason and you decide to continue with your Application for Permanent Residence (APR) in Canada, you will be responsible for any remaining fees.  Please note that the University of Toronto will not cover legal fees related to <B>non-routine matters</B> such as overcoming any issue of medical or criminal inadmissibility for you or any accompanying family member(s). If you have any questions about which fees are covered by the University, please contact the Faculty Immigration at <A HREF='mailto:faculty.immigration@utoronto.ca'>faculty.immigration@utoronto.ca</A>.</P>
<P>The University considers it to be a term of our offer of employment to you that you cooperate fully with the law firm of Rekai LLP and promptly deal with any requests that they may make of you. Specifically, because the confirmation of employment (positive Labour Market Opinion) will not be valid for more than three (3) years and there is no arrangement in place with Service Canada for it to be renewed, it is vital that all reasonable steps be taken to complete your permanent immigration to Canada within that time. In addition, several Canadian granting agencies only fund grants to Canadian citizens and permanent residents of Canada and, for that reason, it also may be in your best professional interests to cooperate with the law firm of Rekai LLP in completing the application process as expeditiously as possible.</P>
<P>As part of the process of applying for permanent residency in Canada, and, in some cases, as part of the non-immigrant visa process as well, it will be necessary for you and your accompanying family members to undergo medical examinations and to provide information with respect to criminal and security background investigations that are conducted by Citizenship and Immigration Canada (CIC) on all applicants. These routine immigration procedures are conducted with a view to ensuring that there are no grounds upon which you, or any member of your accompanying family, could be determined to be an \"inadmissible person\" for immigration to Canada. If you require clarification or if you have any questions regarding these matters, you will be able to discuss them with one of the partners at Rekai LLP, but only after you have been contacted by the firm.</P>
<P>Upon receipt of your Work Permit, it is necessary that you obtain a Social Insurance Number (SIN). For information on how to obtain a new SIN, please refer to the Federal Government's website: <A HREF='http://www.servicecanada.gc.ca/en/sc/sin/index.shtml'>http://www.servicecanada.gc.ca/en/sc/sin/index.shtml</A>. Also, you may visit U of T's Human Resources & Equity website for additional information: <A HREF='www.hrandequity.utoronto.ca/about-hr-equity/Payroll/social-insurance-number.htm'>www.hrandequity.utoronto.ca/about-hr-equity/Payroll/social-insurance-number.htm</A>.</P>";

$html .= "<P><B>Health Insurance</B><BR>The provincial health insurance plan (OHIP) normally commences coverage three months after application. You should apply for this coverage on your arrival to ensure there is no further delay. (Please refer to the Faculty Relocation Service website: <A HREF='www.facultyrelocation.utoronto.ca'>www.facultyrelocation.utoronto.ca</A> for more information). If your existing health insurance coverage does not apply to this waiting period, then it is compulsory that you apply immediately for the University's Health Insurance Plan (UHIP; <A HREF='www.uhip.ca'>www.uhip.ca</A>). For further information, please contact Jasmin Olarte in the University of Toronto Human Resources office at 416-946-5638.</P>";

    $html .= "<P>This offer is conditional on you being legally entitled to work in Canada, in this position. In order to facilitate your entry to Canada, you will need to provide us a copy of your valid passport and a letter from your home institution attesting to the fact that you will be retaining your position there to resume your duties in $country after $enddate. Please note that you are required to be in possession of a valid passport and it will be necessary for the passport to be valid for the entire length of stay in Canada.</P>";
}

$html .= "<P>If you accept this offer, I would appreciate you signing a copy of this letter and returning it to $bo, Business Officer (via email $boemail) no later than $signbackDate";
if($immigration) {
    $html .= " together with a copy of your valid passport";
}
$html .= ".</P><P>Should you have any questions regarding this offer, please do not hesitate to contact $programDirector, Program Director, $program $programDirectorEmail</P>

<P>We expect that you will govern yourself in accordance with all applicable faculty and University policies.</P>

<P>Sincerely,</P><BR><BR><P>$cao<BR>Chief Administrative Officer</P>

<P>cc: $programDirector, Program Director, $pdProgram</P>";

$html2 = "<P><B><I>I have read this letter, the attachments, and the items referred to in the attachments, and accept employment on the basis of all these provisions.</I></B></P>

<BR>
<BR>
<BR>
	
<P>___________________________<BR>
$name</P>	

<BR>
<BR>
<BR>

<P>___________________________<BR>
Date</P>";

return array($html, $html2);

