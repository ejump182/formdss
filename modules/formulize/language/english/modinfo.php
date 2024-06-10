<?php
// Module Info

// The name of this module
define("_MI_formulize_NAME","Formulize");

// A brief description of this module
define("_MI_formulize_DESC","For provisioning forms and analyzing data");

// admin/menu.php
define("_MI_formulize_ADMENU0","Form management");
define("_MI_formulize_ADMENU1","Menu");

// notifications
define("_MI_formulize_NOTIFY_FORM", "Form Notifications");
define("_MI_formulize_NOTIFY_FORM_DESC", "Notifications related to the current form");
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

define("_MI_formulize_ALL_DONE_SINGLES","Should the 'All Done' button appear at the bottom of the form when editing an entry, and creating a new entry in a 'one-entry-per-user' form? (Deprecated - use Form Screen settings)");
define("_MI_formulize_SINGLESDESC","This option is overriden by the settings in Form screens. The 'All Done' button (Leave button) is used to leave a form without saving the information in the form.  If you have made changes to the information in a form and then you click 'All Done' without first clicking 'Save', you get a warning that your data has not been saved.  Because of the way the 'Save' button and 'All Done' button work in tandem, there is normally no way to save information and leave a form all at once.  This bothers/confuses some users.  Set this option to 'Yes' to remove the 'All Done' button and turn the behaviour of the 'Save' button to 'save-and-leave-the-form-all-at-once'.  This option does not affect situations where the user is adding multiple entries to a form (where the form reloads blank every time you click 'Save').");

define("_MI_formulize_LOE_limit", "What is the maximum number of entries that should be displayed in a list of entries, without confirmation from the user that they want to see all entries?");
define("_MI_formulize_LOE_limit_DESC", "If a dataset is very large, displaying a list of entries screen can take a long time, several minutes even.  Use this preference to specify the maximum number of entries that your system should try to display at once.  If a dataset contains more entries than this limit, the user will be asked if they want to load the entire dataset or not.");

define("_MI_formulize_USETOKEN", "Use the security token system to validate form submissions?");
define("_MI_formulize_USETOKENDESC", "By default, when a form is submitted, no data is saved unless Formulize can validate a unique token that was submitted with the form.  This is a partial defence against cross site scripting attacks, meant to ensure only people actually visiting your website can submit forms.  In some circumstances, depending on firewalls or other factors, the token cannot be validated even when it should be.  If this is happening to you repeatedly, you can turn off the token system for Formulize here.  <b>NOTE: you can override this global setting on a screen by screen basis.</b>");

define("_MI_formulize_NUMBER_DECIMALS", "By default, how many decimal places should be displayed for numbers?");
define("_MI_formulize_NUMBER_DECIMALS_DESC", "Normally, leave this as 0, unless you want every number in all forms to have a certain number of decimal places.");
define("_MI_formulize_NUMBER_PREFIX", "By default, should any symbol be shown before numbers?");
define("_MI_formulize_NUMBER_PREFIX_DESC", "For example, if your entire site only uses dollar figures in forms, then put '$' here.  Otherwise, leave it blank.");
define("_MI_formulize_NUMBER_SUFFIX", "By default, should any symbol be shown after numbers?");
define("_MI_formulize_NUMBER_SUFFIX_DESC", "For example, if your entire site only uses percentage figures in forms, then put '%' here.  Otherwise, leave it blank.");
define("_MI_formulize_NUMBER_DECIMALSEP", "By default, if decimals are used, what punctuation should separate them from the rest of the number?");
define("_MI_formulize_NUMBER_SEP", "By default, what punctuation should be used to separate thousands in numbers?");

define("_MI_formulize_HEADING_HELP_LINK", "Should the help link ([?]) and lock icons appear at the top of each column in a list of entries?");
define("_MI_formulize_HEADING_HELP_LINK_DESC", "The help link provides a popup window that shows details about the question in the form, such as the full text of the question, the choice of options if the question is a radio button, etc. The lock icon allows the user to keep a column visible on screen as they scroll to the right, like 'Freeze Panes' in Excel.");

define("_MI_formulize_USECACHE", "Use caching to speed up Procedures?");
define("_MI_formulize_USECACHEDESC", "By default, caching is on.");

define("_MI_formulize_DOWNLOADDEFAULT", "When users are exporting data, use a compatibility trick for some versions of Excel by default?");
define("_MI_formulize_DOWNLOADDEFAULT_DESC", "When users export data, they can check a box on the download page that adds a special code to the file which is necessary to make accented characters appear properly in some versions of Microsoft Excel.  This option controls whether that checkbox is checked by default or not.  You should experiment with your installation to see if exports work best with or without this option turned on.");

define("_MI_formulize_LOGPROCEDURE", "Use logging to monitor Procedures and parameters?");
define("_MI_formulize_LOGPROCEDUREDESC", "By default, logging is off.");

define("_MI_formulize_PRINTVIEWSTYLESHEETS", "What custom stylesheets, if any, should be used in the printable view?");
define("_MI_formulize_PRINTVIEWSTYLESHEETSDESC", "Type the URL for each stylesheet, separated by a comma. If the URL starts with http, it will be used as is. If the URL does not start with http, it will be appended to the end of the base URL for the site.");

define("_MI_formulize_DEBUGDERIVEDVALUES", "Turn on debugging mode for working with derived values?");
define("_MI_formulize_DEBUGDERIVEDVALUESDESC", "When this is on, derived values will be re-computed every time they are displayed. When this is off, derived values are computed on first display only, or when data is saved.");

define("_MI_formulize_NOTIFYBYCRON", "Send notifications via a cron job?");
define("_MI_formulize_NOTIFYBYCRONDESC", "When this is on, create a cron job that triggers '/modules/formulize/notify.php' and notifications will be sent behind the scenes. When this is off, notifications are sent as part of the pageload that generated them.");

define("_MI_formulize_ISSAVELOCKED", "Lock system for synchronization");
define("_MI_formulize_ISSAVELOCKEDDESC", "When locked, you can only change the configuration of Formulize by synchronizing with another system. This is intended for use in a live production system that is being updated by periodic synchronization with a staging system.");

define("_MI_formulize_CUSTOMSCOPE", "Use custom code for determining the scope of queries");
define("_MI_formulize_CUSTOMSCOPEDESC", "Leave this blank, unless you specifically want to override the \$scope variable used in the data extraction layer. The contents of this box will be run as PHP code, and will receive the \$scope variable, which is typically an array of group ids. You can return a set of different ids, or a string in the format 'uid = X' or 'uid = X OR uid = Y...' This is useful if you can isolate certain groups using only one or a few user ids, since then the subquery to the Entry Owner Groups table is bypassed, dramatically improving query speed in large databases.");

define("_MI_formulize_F7MENUTEMPLATE", "Use the modern, mobile friendly menu layout - compatible with the Formulize 7 Theme \"Anari\"");
define("_MI_formulize_F7MENUTEMPLATEDESC", "If you have upgraded from an older version of Formulize, this will be set to \"No\" but if/when you update the theme of your website to \"Anari\" then you should switch this to \"Yes\".");

define("_MI_formulize_USEOLDCUSTOMBUTTONEFFECTWRITING", "Use the old method of writing effects for custom buttons");
define("_MI_formulize_USEOLDCUSTOMBUTTONEFFECTWRITINGDESC", "This should always be \"No\" unless this is an older installation that already has custom buttons that are dependent on the old method, which was based on the declaring human readable values, instead of the database values for elements.");

define("_MI_formulize_FORMULIZELOGFILELOCATION", "Location to store Formulize log files");
define("_MI_formulize_FORMULIZELOGFILELOCATIONDESC", "Formulize generates log files that contain the history of user actions, such as logging in and saving data. You can specify the path to the folder where the log files are stored. Relative paths are determined based on the path to mainfile.php");
define("_MI_formulize_FORMULIZELOGFILESTORAGEDURATION", "How long should Formulize log files be kept (in hours)");
define("_MI_formulize_FORMULIZELOGFILESTORAGEDURATIONDESC", "After this many hours, the log files will be deleted from the server");

// The name of this module
define("_MI_formulizeMENU_NAME","MyMenu");

// A brief description of this module
define("_MI_formulizeMENU_DESC","Displays an individually configurable menu in a block");

// Names of blocks for this module (Not all module has blocks)
define("_MI_formulizeMENU_BNAME","Form Menu");

define("_MI_formulize_EXPORTINTROCHAR","Prefix strings in .csv files with a character to smooth importing and appearance in Excel and Google?");
define("_MI_formulize_EXPORTINTROCHARDESC","Excel and Google Sheets try to be helpful and automatically interpret certain values when opening .csv files. This can damage your data. To force non-numeric values to be read as-is, Formulize can prefix them with certain characters that will trigger them to be read as plain strings by Excel and Google. However, this can cause havoc in other programs if you need plain .csv data. The default behaviour suits opening downloaded files in Excel, and using the IMPORTDATA function in Google Sheets to gather data via a makecsv.php reference.");
define("_MI_formulize_EIC_BASIC", "Prefix strings with a TAB character (for Excel), unless makecsv.php is generating the file, then use an apostrophe (for Google Sheets)");
define("_MI_formulize_EIC_ALWAYSAPOS", "Always prefix strings with an apostrophe (for Google Sheets)");
define("_MI_formulize_EIC_ALWAYSTAB", "Always prefix strings with a TAB (for Excel)");
define("_MI_formulize_EIC_PLAIN", "Never prefix strings (for programs that need clean, raw data)");

