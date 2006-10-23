<?php
// Module Info

// The name of this module
define("_MI_formulize_NAME","Forms");

// A brief description of this module
define("_MI_formulize_DESC","For provisioning forms and analyzing data");

// admin/menu.php
define("_MI_formulize_ADMENU0","Form management");
define("_MI_formulize_ADMENU1","Menu");

// notifications
define("_MI_formulize_NOTIFY_FORM", "Form Notifications");
define("_MI_formulize_NOTIFY_FORM", "Notifications related to the current form");
define("_MI_formulize_NOTIFY_NEWENTRY", "New Entry in a Form");
define("_MI_formulize_NOTIFY_NEWENTRY_CAP", "Notify me when someone makes a new entry in this form");
define("_MI_formulize_NOTIFY_NEWENTRY_DESC", "A notification option that alerts users when new entries are made in a form");
define("_MI_formulize_NOTIFY_NEWENTRY_MAILSUB", "New Entry in a Form");

define("_MI_formulize_NOTIFY_UPENTRY", "Updated Entry in a Form");
define("_MI_formulize_NOTIFY_UPENTRY_CAP", "Notify me when someone updates an entry in this form");
define("_MI_formulize_NOTIFY_UPENTRY_DESC", "A notification option that alerts users when entries are updated in a form");
define("_MI_formulize_NOTIFY_UPENTRY_MAILSUB", "Updated Entry in a Form");

define("_MI_formulize_NOTIFY_DELENTRY", "Entry deleted from a Form");
define("_MI_formulize_NOTIFY_DELENTRY_CAP", "Notify me when someone deletes an entry from this form");
define("_MI_formulize_NOTIFY_DELENTRY_DESC", "A notification option that alerts users when entries are deleted from a form");
define("_MI_formulize_NOTIFY_DELENTRY_MAILSUB", "Entry deleted from a Form");


//	preferences
define("_MI_formulize_TEXT_WIDTH","Default width of text boxes");
define("_MI_formulize_TEXT_MAX","Default maximum length of text boxes");
define("_MI_formulize_TAREA_ROWS","Default rows of text areas");
define("_MI_formulize_TAREA_COLS","Default columns of text areas");
define("_MI_formulize_DELIMETER","Default delimiter for check boxes and radio buttons");
define("_MI_formulize_DELIMETER_SPACE","White space");
define("_MI_formulize_DELIMETER_BR","Line break");
define("_MI_formulize_SEND_METHOD","Send method");
define("_MI_formulize_SEND_METHOD_DESC","Note: Form submitted by anonymous users cannot be sent by using private message.");
define("_MI_formulize_SEND_METHOD_MAIL","Email");
define("_MI_formulize_SEND_METHOD_PM","Private message");
define("_MI_formulize_SEND_GROUP","Send to group");
define("_MI_formulize_SEND_ADMIN","Send to site admin only");
define("_MI_formulize_SEND_ADMIN_DESC","Settings of \"Send to group\" will be ignored");
define("_MI_formulize_PROFILEFORM","Which form is to be used as part of the registration process and when viewing and editing accounts? (requires use of the Registration Codes module)");

define("_MI_formulize_ALL_DONE_SINGLES","Should the 'All Done' button appear at the bottom of the form when editing an entry, and creating a new entry in a 'one-entry-per-user' form?");
define("_MI_formulize_SINGLESDESC","The 'All Done' button is used to leave a form without saving the information in the form.  If you have made changes to the information in a form and then you click 'All Done' without first clicking 'Save', you get a warning that your data has not been saved.  Because of the way the 'Save' button and 'All Done' button work in tandem, there is normally no way to save information and leave a form all at once.  This bothers/confuses some users.  Set this option to 'Yes' to remove the 'All Done' button and turn the behaviour of the 'Save' button to 'save-and-leave-the-form-all-at-once'.  This option does not affect situations where the user is adding multiple entries to a form (where the form reloads blank every time you click 'Save').");

// The name of this module
define("_MI_formulizeMENU_NAME","MyMenu");

// A brief description of this module
define("_MI_formulizeMENU_DESC","Displays an individually configurable menu in a block");

// Names of blocks for this module (Not all module has blocks)
define("_MI_formulizeMENU_BNAME","Form Menu");

// Version
define("_MI_VERSION","2.0b");
?>
