<?php
define("_FORMULAIRE_FORM_TITLE", "Contact us by filling out this form.");
//define("_FORMULAIRE_MSG_SUBJECT", $xoopsConfig['sitename'].' - Contact Us Form');
define("_FORMULAIRE_MSG_SUBJECT", '['.$xoopsConfig['sitename'].'] -');
define("_FORMULAIRE_MSG_FORM", ' Form: ');
//next two added by jwe 7/23/04
define("_FORMULAIRE_INFO_RECEIVED", "Your information has been received.");
define("_FORMULAIRE_NO_PERMISSION", "You do not have permission to view this form.");
define("_FORMULAIRE_MSG_SENT", "Your message has been sent.");
define("_FORMULAIRE_MSG_THANK", "<br />Thank you for your comments.");
define("_FORMULAIRE_MSG_SUP","<br /> Take care data have been erased");
define("_FORMULAIRE_MSG_BIG","The join file is too big to be uploaded.");
define("_FORMULAIRE_MSG_UNSENT","Please join a file with a size down to ");
define("_FORMULAIRE_MSG_UNTYPE","You could not join this type's file.<br>Types which are authorize are : ");

define("_FORMULAIRE_NEWFORMADDED","New form added successfully!");
define("_FORMULAIRE_FORMMOD","Form title modified successfully!");
define("_FORMULAIRE_FORMDEL","Form erased successfully!");
define("_FORMULAIRE_FORMCHARG","Form Loading");
define("_FORMULAIRE_FORMSHOW","Form results: ");
define("_FORMULAIRE_FORMTITRE","Form sent parameters have been modify with success");
define("_FORMULAIRE_NOTSHOW","Form: ");
define("_FORMULAIRE_NOTSHOW2"," does not contain any registers.");
define("_FORMULAIRE_FORMCREA","Form created with success!");

define("_MD_ERRORTITLE","Error ! You did not put the form title !!!!");
define("_MD_ERROREMAIL","Error ! You did not put a valid E-mail address !!!!");
define("_MD_ERRORMAIL","Error ! You did not put the form recipient !!!!");

define("_FORM_ACT","Action");
define("_FORM_CREAT","Create a form");
define("_FORM_RENOM","Rename a form");
define("_FORM_RENOM_IMG","<img src='../images/attach.png'>");
define("_FORM_SUP","Erase a form");
define("_FORM_ADD","Sent parameters");
define("_FORM_SHOW","Consult the results");
define("_FORM_TITLE","Form title:");
define("_FORM_EMAIL","E-mail: ");
define("_FORM_ADMIN","Send to the admin only:");
define("_FORM_EXPE","Receive the form filled:");
define("_FORM_GROUP","Send to a group:");
define("_FORM_MODIF","Modify a form");
define("_FORM_DELTITLE","Form title to erase:");
define("_FORM_NEW","New form");
define("_FORM_NOM","Enter the new file name");
define("_FORM_OPT","Options");
define("_FORM_MENU","Consult the menu");
define("_FORM_PREF","Consult the preferences");

//next section added by jwe 7/25/07
define("_FORM_SINGLEENTRY","This form allows each user only one entry (filling in the form again updates the same entry):");
define("_FORM_GROUPSCOPE","Entries in this form are shared and visible to all users in the same groups (not just the user who entered them):");
define("_FORM_HEADERLIST","Form elements displayed on the 'View Entries' page:");
define("_FORM_SHOWVIEWENTRIES","Users can view previous entires made in this form:");
define("_FORM_MAXENTRIES","After a user has filled in the form this many times, they cannot access the form again (0 means no limit):");
define("_FORM_DEFAULTADMIN","Groups that have rights to this form:");


define("_FORM_MODIF","Modify");
define("_AM_FORM","Form: ");
define("_FORM_EXPORT","Export in CSV format");
define("_FORM_ALT_EXPORT","Export");
define("_FORM_DROIT","Athorized group to consult the form");
define("_FORM_MODPERM","Modify form access permissions");
define("_FORM_PERM","Permissions");

// commented the line below since it's a duplicate of a line above --jwe 7/25/04
//define("_AM_FORM","Form : ");
define("_AM_FORM_SELECT","Select a form");
define("_MD_FILEERROR","Error in sending the file");
define("_AM_FORMUL","Forms");

//added by jwe - 7/28/04
define("_AM_FORM_TITLE", "Form Access Permissions"); // not used
define("_AM_FORM_CURPERM", "Current Permission:"); 
define("_AM_FORM_PERMVIEW", "View");
define("_AM_FORM_PERMADD", "Add/Update");
define("_AM_FORM_PERMADMIN", "Admin");
define("_AM_FORM_SUBMITBUTTON", "Show New Permission"); // not used

define("_AM_FORMLINK_PICK", "Choose an option");



define("_FORM_EXP_CREE","File has been exported with success");

//template constants added by jwe 7/24/04
define("_FORMULAIRE_TEMP_ADDENTRY", "ADD AN ENTRY");
define("_FORMULAIRE_TEMP_VIEWENTRIES", "VIEW ENTRIES");
define("_FORMULAIRE_TEMP_ADDINGENTRY", "ADDING AN ENTRY");
define("_FORMULAIRE_TEMP_VIEWINGENTRIES", "VIEWING ENTRIES");
define("_FORMULAIRE_TEMP_SELENTTITLE", "Your entries in '");
define("_FORMULAIRE_TEMP_SELENTTITLE_GS", "All entries in '");
define("_FORMULAIRE_TEMP_SELENTTITLE_RP", "Search Results for '");
define("_FORMULAIRE_TEMP_SELENTTITLE2_RP", "Calculation Results for '");
define("_FORMULAIRE_TEMP_VIEWTHISENTRY", "View this entry");
define("_FORMULAIRE_TEMP_EDITINGENTRY", "EDITING AN ENTRY");
define("_FORMULAIRE_TEMP_NOENTRIES", "No entries.");
define("_FORMULAIRE_TEMP_ENTEREDBY", "Entered by: ");
define("_FORMULAIRE_TEMP_ENTEREDBYSINGLE", "Entered ");
define("_FORMULAIRE_TEMP_ON", "on");
define("_FORMULAIRE_TEMP_QYES", "YES");
define("_FORMULAIRE_TEMP_QNO", "NO");
define("_FORMULAIRE_REPORT_ON", "Turn Reporting Features ON");
define("_FORMULAIRE_REPORT_OFF", "Turn Reporting Features OFF");
define("_FORMULAIRE_VIEWAVAILREPORTS", "View Report:");
define("_FORMULAIRE_NOREPORTSAVAIL", "No reports available");
define("_FORMULAIRE_CHOOSEREPORT", "Choose a report");
define("_FORMULAIRE_REPORTING_OPTION", "Reporting Options");
define("_FORMULAIRE_SUBMITTEXT", "Apply");
define("_FORMULAIRE_RESETBUTTON", "RESET");
define("_FORMULAIRE_QUERYCONTROLS", "Query Controls");
define("_FORMULAIRE_SEARCH_TERMS", "Search Terms:");
define("_FORMULAIRE_STERMS", "Terms:");
define("_FORMULAIRE_AND", "AND");
define("_FORMULAIRE_OR", "OR");
define("_FORMULAIRE_SEARCH_OPERATOR", "Operator:");
define("_FORMULAIRE_NOT", "NOT");
define("_FORMULAIRE_LIKE", "LIKE");
define("_FORMULAIRE_NOTLIKE", "NOT LIKE");
define("_FORMULAIRE_CALCULATIONS", "Calculations:");
define("_FORMULAIRE_SUM", "Sum");
define("_FORMULAIRE_SUM_TEXT", "Total of all values in column:");
define("_FORMULAIRE_AVERAGE", "Average");
define("_FORMULAIRE_AVERAGE_INCLBLANKS", "Average value in column:");
define("_FORMULAIRE_AVERAGE_EXCLBLANKS", "Average value excluding blanks and zeros:");
define("_FORMULAIRE_MINIMUM", "Minimum");
define("_FORMULAIRE_MINIMUM_INCLBLANKS", "Minimum value in column:");
define("_FORMULAIRE_MINIMUM_EXCLBLANKS", "Minimum value excluding blanks and zeros:");
define("_FORMULAIRE_MAXIMUM", "Maximum");
define("_FORMULAIRE_MAXIMUM_TEXT", "Maximum value in column:");
define("_FORMULAIRE_COUNT", "Count");
define("_FORMULAIRE_COUNT_INCLBLANKS", "Total entries in column:");
define("_FORMULAIRE_COUNT_EXCLBLANKS", "Total entries excluding blanks and zeros:");
define("_FORMULAIRE_COUNT_PERCENTBLANKS", "Percentage of non-blank, non-zero entries:");
define("_FORMULAIRE_PERCENTAGES", "Percentages");
define("_FORMULAIRE_PERCENTAGES_VALUE", "Value:");
define("_FORMULAIRE_PERCENTAGES_COUNT", "Count:");
define("_FORMULAIRE_PERCENTAGES_PERCENT", "% of total:");
define("_FORMULAIRE_PERCENTAGES_PERCENTEXCL", "% excl. blanks:");
define("_FORMULAIRE_SORTING_ORDER", "Sorting Order:");
define("_FORMULAIRE_SORT_PRIORITY", "Sort Priority:");
define("_FORMULAIRE_NONE", "None");
define("_FORMULAIRE_CHANGE_COLUMNS", "Change to Different Columns");
define("_FORMULAIRE_CHANGE", "Change");
define("_FORMULAIRE_SEARCH_HELP", "Use commas to separate terms.  Use [,] to specify a comma.");
define("_FORMULAIRE_SORT_HELP", "You can sort by any element, except ones that accept multiple inputs, such as checkboxes.");




?>
