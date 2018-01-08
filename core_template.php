<?php

$html = "

<P>$date</P>

<P>$name<BR>$address<BR>$city, $province<BR>$pc, $country</P>

<P>$email</P>

<P>Re: $stream Appointment</P>

<P>Dear $name:</P>

<P>On behalf of the John H. Daniels Faculty of Architecture, Landscape and Design I am pleased to offer you a ".strtolower($stream)." appointment at $percent% at the rank of $rank for duration beginning on $startdate and ending on $enddate.  There is no guarantee of future appointments.</P>

<P>Your prorated salary, effective $startdate will be $salary. This is a firm salary offer and will not be affected by any salary increase effective July 1, ".substr($year,0,4)." as a result of negotiations between the University and the Faculty Association.</P> 

<P><B>Salary</B><BR>Your salary will be paid via direct deposit, issued on the 28th of each month.  If you are not already being paid via direct deposit, or your banking information has changed, please provide a void cheque and submit together with the attached TD1 and TD1 ON tax forms.</P>

<P>Your payroll documentation will be available online through the University’s Employee Self-Service (ESS) at <A HREF='http://www.hrandequity.utoronto.ca/resources/ess.htm'>http://www.hrandequity.utoronto.ca/resources/ess.htm</A>. This includes electronic delivery of your pay statement, tax documentation, and other payroll documentation as made available from time to time. You are able to print copies of these documents directly from ESS. By signing this Employment Agreement, you authorize the University to provide your T4 slips electronically and not in a paper format. If you wish to discuss an alternative format, please contact Central Payroll Services at <A HREF='mailto:payroll.hr@utoronto.ca'>payroll.hr@utoronto.ca</A>.</P> 

<P>The University has an extensive fringe benefit package that includes, but is not limited to, a pension plan, medical and insurance benefits, and a dental plan.  General information on these benefits can be found at <A HREF='http://www.hrandequity.utoronto.ca/faculty-librarians/benefits.htm'>http://www.hrandequity.utoronto.ca/faculty-librarians/benefits.htm</A>. For details and to enroll in these benefits, contact the Faculty’s Human Resources Assistant at 416-946-8682 or via email at <A HREF='mailto:jasmine.olarte@utoronto.ca'>jasmine.olarte@utoronto.ca</A>.</P>

<P><B>Vacation</B><BR>You are entitled to a vacation of one month each year at your prorated salary.  Vacation pay is included within your salary.  Vacation is to be scheduled at a mutually convenient time. If your appointment is renewed, your vacation entitlement may not be carried over.</P>";  

if($travelAllowance) {
    $html .= "<P><B>Travel Allowance</B><BR>Upon production of original receipts, you will be eligible for reimbursement for your travel and accommodation expenses, up to a maximum of CAD $travelAllowance.</P>";
}

$html .= "<P><B>UTFA</B><BR>As a term and condition of employment you are required to authorize the University to deduct from your salary an amount equal to membership dues in the University of Toronto Faculty Association fixed annually in accordance with the Association's constitution.  Your written acceptance of this offer constitutes authorization for the University to make this deduction.  The deduction will be remitted to the Association unless you object as a matter of conscience.  Should you conscientiously object to deduction of dues, you may obtain a form from your Divisional Human Resources Office that you must complete providing a written declaration and direction to remit the deduction to a charity selected from a list agreed upon by the University and the Association. More information about UTFA can be found at <A HREF='http://www.utfa.org/'>http://www.utfa.org/</A>.</P> 

<P><B>Other Deductions</B><BR>Payments in respect of salary, benefits and any other items described in this offer are subject to deductions required by law and those made pursuant to the benefit plans in which you are enrolled, as applicable.</P>";

if($immigration) {
    $html .= "<P><B>Immigration Issues</B><BR>This offer is subject to compliance with the immigration laws of Canada (as contained in the <I>Immigration and Refugee Protection Act</I> and in the regulations made in pursuance of that <I>Act</I>) and it is conditional upon any approvals, authorizations and/or permits in respect of your employment that may be required under that <I>Act</I> or the regulations.</P>
<P>Upon your acceptance of our offer of employment you will receive from the Office of the Vice-President and Provost instructions on how to begin the process for applying for the temporary Work Permit that you will require for your employment with the University and for Permanent Resident (\"landed immigrant\") status in Canada. To assist with both of these processes we have engaged the Toronto law firm of Rekai LLP. As the University's legal counsel, we have instructed the law firm of Rekai LLP to assist you with all aspects of both your temporary and permanent immigration law requirements. Mr. Peter Rekai will be in touch with you directly as soon as Service Canada has confirmed our offer of employment to you. By accepting the services of the law firm of Rekai LLP, you consent to the release of any and all information pertaining to your and accompanying family members' admissibility to Canada by Rekai LLP to the Office of the Vice-President and Provost of the University of Toronto. This information will be held in strict confidence by the Office of the Vice-President and Provost and will not be released by that Office without your prior written permission.</P>
<P>The University will be responsible for all of Rekai LLP’s routine legal fees (save and except as noted below) and for the Government of Canada's filing fees for your applications provided you remain employed by the University of Toronto. You will be responsible for all other incidental expenses related to your immigration law requirements. This includes, but is not limited to, such incidental matters as the cost of medical examinations, photos, documents, police clearance certificates as well as the expenses to be incurred by Rekai LLP on your behalf for couriers, translations, photocopying, telecopying and long distance. Should your employment with the University cease for any reason and you decide to continue with your Application for Permanent Residence (APR) in Canada, you will be responsible for any remaining fees.  Please note that the University of Toronto will not cover legal fees related to <B>non-routine matters</B> such as overcoming any issue of medical or criminal inadmissibility for you or any accompanying family member(s). If you have any questions about which fees are covered by the University, please contact the Faculty Immigration at <A HREF='mailto:faculty.immigration@utoronto.ca'>faculty.immigration@utoronto.ca</A>.</P>
<P>The University considers it to be a term of our offer of employment to you that you cooperate fully with the law firm of Rekai LLP and promptly deal with any requests that they may make of you. Specifically, because the confirmation of employment (positive Labour Market Opinion) will not be valid for more than three (3) years and there is no arrangement in place with Service Canada for it to be renewed, it is vital that all reasonable steps be taken to complete your permanent immigration to Canada within that time. In addition, several Canadian granting agencies only fund grants to Canadian citizens and permanent residents of Canada and, for that reason, it also may be in your best professional interests to cooperate with the law firm of Rekai LLP in completing the application process as expeditiously as possible.</P>
<P>As part of the process of applying for permanent residency in Canada, and, in some cases, as part of the non-immigrant visa process as well, it will be necessary for you and your accompanying family members to undergo medical examinations and to provide information with respect to criminal and security background investigations that are conducted by Citizenship and Immigration Canada (CIC) on all applicants. These routine immigration procedures are conducted with a view to ensuring that there are no grounds upon which you, or any member of your accompanying family, could be determined to be an \"inadmissible person\" for immigration to Canada. If you require clarification or if you have any questions regarding these matters, you will be able to discuss them with one of the partners at Rekai LLP, but only after you have been contacted by the firm.</P>
<P>Upon receipt of your Work Permit, it is necessary that you obtain a Social Insurance Number (SIN). For information on how to obtain a new SIN, please refer to the Federal Government's website: <A HREF='http://www.servicecanada.gc.ca/en/sc/sin/index.shtml'>http://www.servicecanada.gc.ca/en/sc/sin/index.shtml</A>. Also, you may visit U of T's Human Resources & Equity website for additional information: <A HREF='www.hrandequity.utoronto.ca/about-hr-equity/Payroll/social-insurance-number.htm'>www.hrandequity.utoronto.ca/about-hr-equity/Payroll/social-insurance-number.htm</A>.</P>";

$html .= "<P><B>Health Insurance</B><BR>The provincial health insurance plan (OHIP) normally commences coverage three months after application. You should apply for this coverage on your arrival to ensure there is no further delay. (Please refer to the Faculty Relocation Service website: <A HREF='www.facultyrelocation.utoronto.ca'>www.facultyrelocation.utoronto.ca</A> for more information). If your existing health insurance coverage does not apply to this waiting period, then it is compulsory that you apply immediately for the University's Health Insurance Plan (UHIP; <A HREF='www.uhip.ca'>www.uhip.ca</A>). For further information, please contact Jasmin Olarte in the University of Toronto Human Resources office at 416-946-5638.</P>";

}

$html .= "<P><B>Policies and Procedures</B><BR>On the University's website are posted the policies and procedures that govern your appointment and I draw your attention to the following:</P>

<P><I>Policy and Procedures on Employment Conditions of Part-Time Academic Staff</I><BR><A HREF='http://www.governingcouncil.utoronto.ca/Assets/Governing+Council+Digital+Assets/Policies/PDF/ppmar071994i.pdf'>http://www.governingcouncil.utoronto.ca/Assets/Governing+Council+Digital+Assets/Policies/PDF/ppmar071994i.pdf</A></P>
<P><I>Memorandum of Agreement between the University and the University of Toronto Faculty Association</I><BR><A HREF='http://www.utfa.org/sites/default/files/Dec.%2031-2006%20updated%20MofA.pdf'>http://www.utfa.org/sites/default/files/Dec.%2031-2006%20updated%20MofA.pdf</A></P>
<P><I>Code of Behaviour on Academic Matters</I><BR><A HREF='http://www.governingcouncil.lamp4.utoronto.ca/wp-content/uploads/2016/07/p0701-coboam-2015-2016pol.pdf'>http://www.governingcouncil.lamp4.utoronto.ca/wp-content/uploads/2016/07/p0701-coboam-2015-2016pol.pdf</A></P> 
<P><I>Policy and Procedures Governing Promotions</I><BR><A HREF='http://www.governingcouncil.utoronto.ca/Assets/Governing+Council+Digital+Assets/Policies/PDF/ppapr201980.pdf'>http://www.governingcouncil.utoronto.ca/Assets/Governing+Council+Digital+Assets/Policies/PDF/ppapr201980.pdf</A></P>
<P><I>Policy on Conflict of Interest: Academic Staff</I><BR><A HREF='http://www.governingcouncil.utoronto.ca/Assets/Governing+Council+Digital+Assets/Policies/PDF/ppfeb012007iii.pdf'>http://www.governingcouncil.utoronto.ca/Assets/Governing+Council+Digital+Assets/Policies/PDF/ppfeb012007iii.pdf</A></P>

<P>There are various other policies that govern aspects of your rights and obligations as a faculty member. They can be found on the Provost's web-site at <A HREF='http://www.provost.utoronto.ca'>http://www.provost.utoronto.ca</A>. <I>The Manual of Staff Policies for Academics and Librarians</I> is available on the Human Resources web site at <A HREF='http://hr.webservices.utoronto.ca/Assets/HR+Digital+Assets/Policies$!2c+Guidelines+and+Collective+Agreements/Policies/Manual+of+Staff+Policies+for+Academics+and+Librarians.pdf'>http://hr.webservices.utoronto.ca/Assets/HR+Digital+Assets/Policies$!2c+Guidelines+and+Collective+Agreements/Policies/Manual+of+Staff+Policies+for+Academics+and+Librarians.pdf</A>.  Some of these policies are subject to negotiation with the University of Toronto Faculty Association, and others may be changed directly by the University.  All part-time University of Toronto academic appointments are subject to these provisions and you should familiarize yourself with them.</P>

<P>The law requires the Employment Standards Act Poster to be provided to all employees; it is available on the HR & Equity website at <A HREF='http://www.vicu.utoronto.ca/Assets/VICU+Digital+Assets/Victoria+University/VICU+Digital+Assets/Human+Resources/Policies/ESA+Poster.pdf'>http://www.vicu.utoronto.ca/Assets/VICU+Digital+Assets/Victoria+University/VICU+Digital+Assets/Human+Resources/Policies/ESA+Poster.pdf</A>. This poster describes the minimum rights and obligations contained in the Employment Standards Act. Please note that in many respects this offer of employment exceeds the minimum requirements set out in the Act.</P>

<P><B>Teaching</B><BR>Your teaching assignments are conveyed in the attached teaching assignment memo. You will be expected to assume a normal teaching load in the Department as described in its workload policy, which is attached. We would draw your attention to the availability of the services of the University of Toronto’s Centre for Teaching Support and Innovation located on the 4th Floor of the Robarts Library, St. George Campus.  For more information about the Centre for Teaching Support and Innovation go to <A HREF='http://www.teaching.utoronto.ca/'>http://www.teaching.utoronto.ca/</A>.</P> 

<P><B>Accessibility</B><BR>The University has a number of programs and services available to employees who have need of accommodation due to a disability through its Health & Well-being Programs and Services (<A HREF='http://www.hrandequity.utoronto.ca/about-hr-equity/health.htm'>http://www.hrandequity.utoronto.ca/about-hr-equity/health.htm</A>). A description of the accommodation process is available in the Accommodation for Employees with Disabilities: U of T Guidelines, which may be found at: <A HREF='http://well-being.hrandequity.utoronto.ca/services/#accommodation'>http://well-being.hrandequity.utoronto.ca/services/#accommodation</A>.</P>

<P>In the event that you have a disability that would impact upon how you would respond to an emergency in the workplace (e.g., situations requiring evacuation), you should contact Health & Well-being Programs & Services at 416 978-2149 as soon as possible so that you can be provided with information regarding an individualized emergency response plan.</P>

<P><B>This Offer</B><BR>This letter, and the documents referred to in it, constitute the entire agreement between you and the University.  There are no representations, warranties or other commitments apart from these documents.</P>

<P>If you accept this offer, I would appreciate you signing a copy of this letter together with the attached tax forms and a void cheque (unless your banking information remains unchanged) and returning it to $bo, Business Officer (via email $boemail) no later than $signbackDate.  Should you have any questions regarding this offer, please do not hesitate to contact $programDirector, Program Director, $program $programDirectorEmail</P> 

<P>We look forward to the new academic year with you.</P>

<P>Yours sincerely,<BR><BR><BR><BR>$dean<BR>Associate Dean, Academic</P>

<P>cc:<BR>$cao, Chief Administrative Officer<BR>$bo, Business Officer</P>";

$html2 = "<P><B><I>I have read this letter, the attachments, and the items referred to in the attachments, and accept employment on the basis of all these provisions.</I></B><BR><BR><BR><BR><BR>Name: ________________________________ Date: ______________________________</P>";

return array($html, $html2);