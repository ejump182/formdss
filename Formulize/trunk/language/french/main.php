<?php
define("_AM_CATGENERAL", "General Forms");
define("_AM_NOFORMS_AVAIL", "There are no forms currently available.");
define("_formulize_FORM_TITLE","Contactez nous en remplissant ce formulaire.");
//define("_formulize_MSG_SUBJECT",$xoopsConfig['sitename'].' - Formulaire de contact');
define("_formulize_MSG_SUBJECT", '['.$xoopsConfig['sitename'].'] -');
define("_formulize_MSG_FORM", ' formulaire : ');
//next two added by jwe 7/23/04
define("_formulize_INFO_RECEIVED", "Nous avons bien re�u vos informations.");
define("_formulize_NO_PERMISSION", "Vous ne disposez pas des autorisations vous permettant de pouvoir afficher ce formulaire.");
define("_formulize_MSG_SENT","Votre message a �t� envoy�.");
define("_formulize_MSG_THANK","<br />Merci pour vos commentaires.");
define("_formulize_MSG_SUP","<br /> Attention les enregistrements de ce formulaire ont �t� supprim�s");
define("_formulize_MSG_BIG","LE fichier joint est trop volumineux pour �tre t�l�charg�.");
define("_formulize_MSG_UNSENT","Veuillez joindre un fichier dont la taille est inf�rieure � ");
define("_formulize_MSG_UNTYPE","Vous ne pouvez pas joindre ce type de fichier.<br>Les types autoris�s sont : ");

define("_formulize_NEWFORMADDED","Nouveau formulaire ajout� avec succ�s!");
define("_formulize_FORMMOD","Titre de formulaire modifi� avec succ�s!");
define("_formulize_FORMDEL","formulaire effac� avec succ�s!");
define("_formulize_FORMCHARG","Chargement du formulaire");
define("_formulize_FORMSHOW","R�sultats de formulaire : ");
define("_formulize_FORMTITRE","Param�tres d'envoi du formulaire modifi�s avec succ�s");
define("_formulize_NOTSHOW","Le formulaire ");
define("_formulize_NOTSHOW2"," ne contient pas de requ�tes enregistr�es.");
define("_formulize_FORMCREA","Formulaire cr�� avec succ�s!");

define("_MD_ERRORTITLE","Erreur ! Vous n'avez pas saisie le titre du formulaire !!!!");
define("_MD_ERROREMAIL","Erreur ! votre adresse e-mail n'est pas valide !!!!");
define("_MD_ERRORMAIL","Erreur ! Vous n'avez pas saisie de destinataire pour le formulaire !!!!");

define("_FORM_ACT","Action");
define("_FORM_CREAT","Cr�er un nouveau formulaire");
define("_FORM_RENOM","Renommer");
define("_FORM_RENOM_IMG","<img src='../images/attach.png'>");
define("_FORM_SUP", "Supprimer");
define("_FORM_ADD","Param�tres d'envoi");
define("_FORM_SHOW","Consultation des r�sultats");
define("_FORM_TITLE","Titre du formulaire : ");
define("_FORM_EMAIL","Adresse E-mail :");
define("_FORM_ADMIN","Envoyer uniquement � l'admin :");
define("_FORM_EXPE","Recevoir le formulaire rempli :");
define("_FORM_GROUP","Envoyer au groupe :");
define("_FORM_MODIF","Modifier");
define("_FORM_DELTITLE","Titre du formulaire � effacer :");
define("_FORM_NEW","Nouveau formulaire");
define("_FORM_NOM","Entrer le nouveau nom du formulaire");
define("_FORM_OPT","Options");
define("_FORM_MENU","Consulter le menu");
define("_FORM_PREF","Consulter les pr�f�rences");

//next section added by jwe 7/25/07
define("_FORM_SINGLEENTRY","Ce formulaire n'alloue qu'une entr�e par utilisateur (remplir une nouvelle fois le formulaire mettra � jour la m�me entr�e):");
define("_FORM_GROUPSCOPE","Les entr�es de ce formulaire sont partag�es et vue par tous les membre d'un m�me groupe (pas seulement par l'utilisateur qui l'a rempli):");
define("_FORM_HEADERLIST","El�ments de formulaire montr� dans la page 'Visualisation des entr�es' :");
define("_FORM_SHOWVIEWENTRIES","Les utilisateurs peuvent visualiser les entr�es ant�rieurement saisies dans ce formulaire :");
define("_FORM_MAXENTRIES","Apr�s un temps suffisament long les utilisateurs ayant saisie le formulaire, ne pourront plus y avoir acc�s de nouveau (0 afin de ne pas mettre de limite de temps):");
define("_FORM_DEFAULTADMIN","Groups disposant de droits sur ce formulaire :");

define("_FORM_COLOREVEN","Premier couleur alternative pour la page de r�daction de rapport  (la couleur alternative remplacera la couleur par d�faut et permettra de distinguer un formulaire d'un autre):");
define("_FORM_COLORODD","Seconde couleur alternative pour la page de r�daction de rapport:");


define("_FORM_MODIF","Modification des questions du formulaire");
define("_AM_FORM","Formulaire : ");
define("_FORM_EXPORT","Export au format CSV");
define("_FORM_ALT_EXPORT","Exporter");
define("_FORM_DROIT","Groupes autoris�s � utiliser ce formulaire");
define("_FORM_MODPERM","Modifier les permissions d'acc�s aux formulaires");
define("_FORM_PERM","Permissions");

define("_FORM_MODPERMLINKS","Modifier le p�rim�tres des bo�tes de s�lection li�es");
define("_FORM_PERMLINKS","P�rim�tres des bo�tes de s�lection li�es");

define("_FORM_MODFRAME","Cr�er ou modifier un framework de formulaire");
define("_FORM_FRAME", "Frameworks");


// commented the line below since it's a duplicate of a line above --jwe 7/25/04
//define("_AM_FORM","Form : ");
define("_AM_FORM_SELECT","S�lection du formulaire");
define("_MD_FILEERROR","Erreur chargement de fichier");
define("_AM_FORMUL","Formulaires");

//added by jwe - 7/28/04
define("_AM_FORM_TITLE", "Autorisations d'acces au formulaire"); // not used
define("_AM_FORM_CURPERM", "Permissions courantes :"); 
define("_AM_FORM_CURPERMLINKS", "Bo�tes de s�lections li�es courantes :"); 
define("_AM_FORM_PERMVIEW", "Visualiser");
define("_AM_FORM_PERMADD", "Ajout/Mise � jour");
define("_AM_FORM_PERMADMIN", "Admin");
define("_AM_FORM_SUBMITBUTTON", "Montrer les nouvelles permissions"); // not used

define("_AM_FORMLINK_PICK", "Choisir une option");
define("_AM_CONFIRM_DEL", "Etes vous certain de d�sirer effacer ce formulaire !  Veuillez confirmer.");

define("_AM_FRAME_NEW", "Cr�ation d'un nouveau Framework:");
define("_AM_FRAME_NEWBUTTON", "Cr�er maintenant !");
define("_AM_FRAME_EDIT", "Modification d'un Framework existant :");
define("_AM_FRAME_NONE", "Aucun Framework n'existe");
define("_AM_FRAME_CHOOSE", "Choix d'un Framework");
define("_AM_FRAME_TYPENEWNAME", "Saisissez le nouveau nom ici");
define("_AM_CONFIRM_DEL_FF_FORM", "Etes vous certain de d�sirer effacer ce set de formulaire dans le framework!  Veuillez confirmer.");
define("_AM_CONFIRM_DEL_FF_FRAME", "Etes vous certain de d�sirer effacer ce framework! Veuillez confirmer.");
define("_AM_FRAME_NAMEOF", "Nom du Framework :");
define("_AM_FRAME_ADDFORM", "Ajouter une paire de formulaire � ce Framework:");
define("_AM_FRAME_FORMSIN", "Formulaires dans ce Framework: (cliquez sur son nom afin d'en visualiser les d�tails)");
define("_AM_FRAME_DELFORM", "Enlever");
define("_AM_FRAME_EDITFORM", "Details pour:");
define("_AM_FRAME_DONEBUTTON", "Effectu�");
define("_AM_FRAME_NOFORMS", "Il n'y a aucun formulaires dans ce Framework");
define("_AM_FRAME_AVAILFORMS1", "Formulaire un :");
define("_AM_FRAME_AVAILFORMS2", "Formulaire deux :");
define("_AM_FRAME_DELETE", "Effacer un Framework existant :");
define("_AM_FRAME_SUBFORM_OF", "En faire un sous formulaire de :");
define("_AM_FRAME_NOPARENTS", "Aucun formulaire dans le Framework"); 
define("_AM_FRAME_TYPENEWFORMNAME", "Saisissez un nom cour ici");
define("_AM_FRAME_NEWFORMBUTTON", "Ajout de formulaire !");
define("_AM_FRAME_NOKEY", "aucun n'est sp�cifi�!");
define("_AM_FRAME_FORMNAMEPROMPT", "Nom pour ce formulaire dans ce framework:");
define("_AM_FRAME_RELATIONSHIP", "Relations :");
define("_AM_FRAME_ONETOONE", "un pour un");
define("_AM_FRAME_ONETOMANY", "un pour beaucoup");
define("_AM_FRAME_MANYTOONE", "beaucoup pour un");
define("_AM_FRAME_LINKAGE", "Lien entre ces formulaires :");
define("_AM_FRAME_DISPLAY", "Montrer ces formulaires comme un seul ?");
define("_AM_FRAME_UIDLINK", "N� d'utilisateur de la personne l'ayant rempli");
define("_AM_FRAME_UPDATEBUTTON", "Mise � jour de ce Framework avec ces param�tres");
define("_AM_FRAME_UPDATEFORMBUTTON", "Mise � jour de ce formulaire avec ces Handles");
define("_AM_FRAME_UPDATEANDGO", "Mise � jour, et retour � la page pr�c�dente");

define("_AM_FRAME_FORMHANDLE", "Handle pour ce formulaire :");
define("_AM_FRAME_FORMELEMENTS", "Elements dans ce formulaire");
define("_AM_FRAME_ELEMENT_CAPTIONS", "L�gendes");
define("_AM_FRAME_ELEMENT_HANDLES", "Handles");
define("_AM_FRAME_HANDLESHELP", "Utilisez cette page pour sp�cifier les <i>Handles</i> pour ce formulaire et ses �l�ments.  Handles sont des noms courts qui peuvent �tre utilis� en r�c�fence � ce formulaire ou � ces �l�ments en dehors du cadre de ce module.");

define("_FORM_EXP_CREE","Le fichier a �t� export� avec succ�s");

//template constants added by jwe 7/24/04
define("_formulize_TEMP_ADDENTRY", "Ajout d'une entr�e");
define("_formulize_TEMP_VIEWENTRIES", "Visualisation des entr�es");
define("_formulize_TEMP_ADDINGENTRY", "Ajouter une entr�e");
define("_formulize_TEMP_VIEWINGENTRIES", "Visualiser les entr�es");
define("_formulize_TEMP_SELENTTITLE", "Votre entr�e dans '");
define("_formulize_TEMP_SELENTTITLE_GS", "Toutes les entr�es dans '");
define("_formulize_TEMP_SELENTTITLE_RP", "Recherche de r�sultats pour '");
define("_formulize_TEMP_SELENTTITLE2_RP", "Calcul de r�sultats pour '");
define("_formulize_TEMP_VIEWTHISENTRY", "Visualiser cette entr�e");
define("_formulize_TEMP_EDITINGENTRY", "Editer cette entr�e");
define("_formulize_TEMP_NOENTRIES", "Aucune entr�e.");
define("_formulize_TEMP_ENTEREDBY", "Saisie par : ");
define("_formulize_TEMP_ENTEREDBYSINGLE", "Saisie ");
define("_formulize_TEMP_ON", "Actif");
define("_formulize_TEMP_QYES", "Oui");
define("_formulize_TEMP_QNO", "Non");
define("_formulize_REPORT_ON", "Activer le mode rapport");
define("_formulize_REPORT_OFF", "D�sactiver le mode rapport");
define("_formulize_VIEWAVAILREPORTS", "Visualisation de Rapport:");
define("_formulize_NOREPORTSAVAIL", "Vu par d�faut");
define("_formulize_CHOOSEREPORT", "Vue par d�faut");
define("_formulize_REPORTING_OPTION", "Options de journalisation");
define("_formulize_SUBMITTEXT", "Appliquer");
define("_formulize_RESETBUTTON", "Netoyer");
define("_formulize_QUERYCONTROLS", "Contr�les des requ�tes");
define("_formulize_SEARCH_TERMS", "Termes de recherche :");
define("_formulize_STERMS", "Termes:");
define("_formulize_AND", "ET");
define("_formulize_OR", "OU");
define("_formulize_SEARCH_OPERATOR", "Op�rateur:");
define("_formulize_NOT", "NON");
define("_formulize_LIKE", "COMME");
define("_formulize_NOTLIKE", "DIFFERENT DE");
define("_formulize_CALCULATIONS", "Calcul :");
define("_formulize_SUM", "Somme");
define("_formulize_SUM_TEXT", "Total des toutes les valeurs dans la colonne :");
define("_formulize_AVERAGE", "Moyenne");
define("_formulize_AVERAGE_INCLBLANKS", "Valeur moyenne dans la colonne:");
define("_formulize_AVERAGE_EXCLBLANKS", "Valeur moyenne exclue des blancs et des z�ro:");
define("_formulize_MINIMUM", "Minimum");
define("_formulize_MINIMUM_INCLBLANKS", "Valeur minimum dans la colonne :");
define("_formulize_MINIMUM_EXCLBLANKS", "Valeur minimum exclue des blancs et des z�ros:");
define("_formulize_MAXIMUM", "Maximum");
define("_formulize_MAXIMUM_TEXT", "Valeur maximum dans la colonne :");
define("_formulize_COUNT", "Comptage");
define("_formulize_COUNT_INCLBLANKS", "Nombre de valeurs totales dans la colonne :");
define("_formulize_COUNT_ENTRIES", "Nombre total d'entr�es dans la colonne :");
define("_formulize_COUNT_NONBLANKS", "Total d'entr�es non-blanc, non-z�ro dans la colonne :");
define("_formulize_COUNT_EXCLBLANKS", "Total de valeurs non-blanc, non-z�ro dans la colonne :");
define("_formulize_COUNT_PERCENTBLANKS", "Percentage de valeur non-blanc, non-z�ro :");
define("_formulize_COUNT_UNIQUES", "total de valeur unique dans la colonne :");
define("_formulize_COUNT_UNIQUEUSERS", "Nombre d'utilisateurs ayant effectu�s des entr�es dans la colonne :");
define("_formulize_PERCENTAGES", "Percentages");
define("_formulize_PERCENTAGES_VALUE", "Valeur :");
define("_formulize_PERCENTAGES_COUNT", "Comptage :");
define("_formulize_PERCENTAGES_PERCENT", "Total de % :");
define("_formulize_PERCENTAGES_PERCENTEXCL", "% excl. blancs:");
define("_formulize_SORTING_ORDER", "Ordre de tri :");
define("_formulize_SORT_PRIORITY", "Ordre de priorit� :");
define("_formulize_NONE", "Aucun");
define("_formulize_CHANGE_COLUMNS", "Changer pour visualiser des colonnes diff�rentes :");
define("_formulize_CHANGE", "Changer");
define("_formulize_SEARCH_HELP", "Si vous sp�cifiez les termes de recherche dans plus d'une colonne, les param�tres interchamps ET/OU d�termine si rechercher les entr�es qui s'assortissent dans toutes les colonnes (ET), ou en fait � une colonne (OU).<br><br>L'option ET/OU d�termine si les boites de termes s'associe aux entr�es de tous les termes (ET), ou � un des terme (OU).<br><br>Utilisez une virgule pour s�parer les termes.  Utilisez [,] entre chaque terme.");
define("_formulize_SORT_HELP", "Vous pouvez op�rer un tri par n'importe quel �l�ment, except� celui qui accepte les entr�es multiples, comme les cases � cocher.");
define("_formulize_REPORTSCOPE", "S�lection du p�rim�tre du rapport :");
define("_formulize_SELECTSCOPEBUTTON", "S�lection");
define("_formulize_GROUPSCOPE", "Groupe : ");
define("_formulize_USERSCOPE", "Utilisateur : ");
define("_formulize_GOREPORT", "Go");
define("_formulize_REPORTSAVING", "Sauvegarder cette requ�te comme l'une de votre rapport :");
define("_formulize_SAVEREPORTBUTTON", "Sauvegarder");
define("_formulize_REPORTNAME", "Nom de rapport:");
define("_formulize_ANDORTITLE", "Param�tre d'interchamps ET/OU :");

define("_formulize_PUBLISHINGOPTIONS", "Options de publication :");
define("_formulize_PUBLISHREPORT", "Publier ce rapport � l'intention des autres utilisateurs.");
define("_formulize_PUBLISHNOVE", "enlever 'Visualiser cette entr�e' des liens du rapport (aussi les utilisateurs ne pourront plus voir les d�tails de chaque entr�e).");
define("_formulize_PUBLISHCALCONLY", "enlever en totalit� la liste des entr�es, et n'exposer que les calculs globaux.");


define("_formulize_LOCKSCOPE", "<b>Sauvegarder le rapport courant avec son p�rim�tre v�rouill�</b> (autrement des navigateurs sont limit�s � leur p�rim�tre par d�faut).");
define("_formulize_REPORTPUBGROUPS", "S�lection du groupe pour lequel publier :");
define("_formulize_REPORTDELETE", "Effacer le rapport s�lectionn� courant:");
define("_formulize_DELETE", "Effacer");
define("_formulize_DELETECONFIRM", "Cochez cette bo�te ainsi que le bouton effacer");

define("_formulize_REPORTEXPORTING", "Exporter cette requ�te comme un fichier de tableur:");
define("_formulize_EXPORTREPORTBUTTON", "Exporter");
define("_formulize_EXPORTEXPLANATION", "Cliquez sur le bouton <b>Exporter</b> pour t�l�charger un fichier au format de tableur lisible contenant les r�sultats de la requ�te courante.  Note : vous pouvez utiliser les d�limitateurs utiliser entre les champs.  Si le caract�re de d�limitation que vous choisissez est pr�sent dans vos r�sultats, le fichier de tableur ne s'ouvrira pas correctement, aussi veillez � essayer avec plusieurs d�limitateurs.");
define("_formulize_FILEDELTITLE", "D�limitateur de champs :");
define("_formulize_FDCOMMA", "Virgugle");
define("_formulize_FDTAB", "Tabuslation");
define("_formulize_FDCUSTOM", "Customis�");
define("_formulize_exfile", "donnees_exportees_");
define("_formulize_DLTEXT", "<b>Cliquez droit sur le lien suivant et s�lectionnez <i>Sauvegarde</i>.</b> (Ctrl-click sur Mac.)  Une fois le fichier sauvegard� sur votre ordinateur, vous pourrez l'ouvrir avec un logiciel tableur. Si les champs ne s'alignent pas proprement lorque vous ouvre le fichier, tentez d'exporter avec un d�limitateur diff�rent.");
define("_formulize_DLHEADER", "Votre fichier est pret � �tre t�l�charg�.");

define("_formulize_PICKAPROXY", "Aucun utilisateur proxy s�lectionn�");
define("_formulize_PROXYFLAG", "(Proxy)");

define("_AM_SELECT_PROXY", "Est-ce que cette information concerne un autre utilisateur?");

define("_formulize_DELBUTTON", "Effacer");
define("_formulize_DELCONF", "Etes-vous certain de vouloir effacer cette information !  Veuillez confirmer.");
define("_formulize_DONE", "Tout Fini");
define("_formulize_SAVE", "Enregistrer");
define("_formulize_TEMP_ON", "sur"); 
define("_FORM_ANON_USER","Quelqu'un sur l'Internet");
define("_formulize_FD_ABOUT", "D�tails sur cette information:");
define("_formulize_FD_CREATED", "Cr�� par: ");
define("_formulize_FD_MODIFIED", "Modifi�e par: ");
define("_formulize_FD_NEWENTRY", "Ceci est une nouvelle information qui n'a pas d�j� �t� enregistr�e.");
define("_formulize_INFO_SAVED", "Votre information a �t� enregistr�e.");
define("_formulize_INFO_DONE1", "Si vous avez fini, cliquez le bouton <i>");
define("_formulize_INFO_DONE2", "</i>.");
define("_formulize_INFO_CONTINUE1", "Vous pouvez mettre � jour votre information ci-dessous.");
define("_formulize_INFO_CONTINUE2", "Vous pouvez enregistrer toute autre information en compl�tant le formulaire de nouveau.");
define("_formulize_INFO_SAVEBUTTON", "Pour enregistrer vos changements, cliquez le bouton <i>" . _formulize_SAVE . "</i>.");
define("_formulize_INFO_SAVE1", "Pour enregistrer vos changements, cliquez le bouton <i>");
define("_formulize_INFO_SAVE2", "</i>.");
define("_formulize_INFO_NOSAVE", "You can review this entry, but you <i>cannot save changes</i>.");
define("_formulize_INFO_MAKENEW", "Vous pouvez enregistrer votre nouvelle information en compl�tant le formulaire ci-dessous.");

define("_formulize_ADD", "Ajouter");
define("_formulize_ADD_ONE", "Add One");
define("_formulize_ADD_ENTRIES", "ligne(s)");
define("_formulize_DELETE_CHECKED", "Delete checked items");
define("_formulize_ADD_HELP", "Pour ajouter des cases, appuyez sur le bouton <i>Ajouter</i>.");
define("_formulize_ADD_HELP2", "See an entire entry by clicking the <i>View</i> button.");
define("_formulize_ADD_HELP3", "Update an entry by changing the values on the right.");
define("_formulize_ADD_HELP4", "Delete an entry by checking the boxes and clicking the button below.");
define("_formulize_SUBFORM_VIEW", "View full entry");
define("_formulize_CONFIRMNOSAVE", "You have not saved your changes!  Is that OK?  Click 'Cancel' to return to the form and then click 'Save' to save your changes.");


define("_formulize_PRINTVIEW", "Version imprimable");

// account creation
define("_formulize_ACTDETAILS", "Informations sur le compte:");
define("_formulize_PERSONALDETAILS", "Information personnelle:");
define("_formulize_TYPEPASSTWICE_NEW", "(Entrez deux fois votre mot de passe.  Doit contenir au moins ");
define("_formulize_TYPEPASSTWICE_CHANGE", "(Pour modifier votre mot de passe, entrez deux fois un nouveau mot de passe.  Doit contenir au moins ");
define("_formulize_CDISPLAYMODE", "Pr�f�rences d'affichage des commentaires/inscriptions");
define("_formulize_CSORTORDER", "Pr�f�rences de classement des commentaires/inscriptions");
define("_formulize_CREATEACT", "Cr�er mon compte!");
define("_formulize_ACTCREATED", "Your account has been created and you are being logged into the site now.");
define("_formulize_USERNAME_HELP1", " (Ne doit pas contenir d'espaces.  Doit �tre entre ");
define("_formulize_USERNAME_HELP2", " et ");
define("_formulize_USERNAME_HELP3", " caract�res)");
define("_formulize_PASSWORD_HELP1", " caract�res)");

// "Other" for checkboxes and radio buttons:
define("_formulize_OPT_OTHER", "Autre: ");

// multi-page forms
define("_formulize_DMULTI_THANKS", "<h1>You're done!</h1><p>Thanks for taking the time to fill in that form.  We really appreciate it.</p>");
define("_formulize_DMULTI_NEXT", "Save and Continue >>");
define("_formulize_DMULTI_PREV", "<< Save and Go Back");
define("_formulize_DMULTI_SAVE", "Save and Finish >>");
define("_formulize_DMULTI_PAGE", "Page");
define("_formulize_DMULTI_OF", "Of");
define("_formulize_DMULTI_SKIP", "One or more pages was skipped because they don't apply");
define("_formulize_DMULTI_ALLDONE", "Leave this form and continue browsing the site");

// CALENDAR
define("_formulize_CAL_ADD_ITEM", "Click to add a new item on this day.");
define("_formulize_CAL_RETURNFROMMULTI", "Return to the Calendar");

define("_formulize_CAL_MONTH_01", "Janvier");
define("_formulize_CAL_MONTH_02", "F�vrier");
define("_formulize_CAL_MONTH_03", "Mars");
define("_formulize_CAL_MONTH_04", "Avril");
define("_formulize_CAL_MONTH_05", "Mai");
define("_formulize_CAL_MONTH_06", "Juin");
define("_formulize_CAL_MONTH_07", "Juillet");
define("_formulize_CAL_MONTH_08", "Ao�t");
define("_formulize_CAL_MONTH_09", "Septembre");
define("_formulize_CAL_MONTH_10", "Octobre");
define("_formulize_CAL_MONTH_11", "Novembre");
define("_formulize_CAL_MONTH_12", "D�cembre");

define("_formulize_CAL_WEEK_1", "Dimanche");
define("_formulize_CAL_WEEK_2", "Lundi");
define("_formulize_CAL_WEEK_3", "Mardi");
define("_formulize_CAL_WEEK_4", "Mercredi");
define("_formulize_CAL_WEEK_5", "Jeudi");
define("_formulize_CAL_WEEK_6", "Vendredi");
define("_formulize_CAL_WEEK_7", "Samedi");
define("_formulize_CAL_WEEK_1_3ABRV", "Dim");
define("_formulize_CAL_WEEK_2_3ABRV", "Lun");
define("_formulize_CAL_WEEK_3_3ABRV", "Mar");
define("_formulize_CAL_WEEK_4_3ABRV", "Mer");
define("_formulize_CAL_WEEK_5_3ABRV", "Jeu");
define("_formulize_CAL_WEEK_6_3ABRV", "Ven");
define("_formulize_CAL_WEEK_7_3ABRV", "Sam");


// constants related to the new display entries functions... added by kw 2008-09-03
define("_formulize_DE_CURRENT_VIEW", "Format de vue courrant: ");
define("_formulize_DE_FILLINFORM", "Remplir ce formulaire: ");
define("_formulize_DE_ACTIONS", "Actions: ");
define("_formulize_DE_NODATAFOUND", "No entries were found in the current view that match the current search terms (if any).");
define("_formulize_DE_STANDARD_VIEWS", "FORMAT DE VUE STANDARD:");
define("_formulize_DE_NO_STANDARD_VIEWS", "No standard views available");
define("_formulize_DE_SAVED_VIEWS", "YOUR SAVED VIEWS:");
define("_formulize_DE_PUB_VIEWS", "PUBLISHED VIEWS:");
define("_formulize_DE_SEARCH_HELP", "Indiquer les mots de la recherch� ici");
define("_formulize_DE_WARNLOCK", "<p>The view that you have selected is set to <i>lock the controls</i>.  This means that you cannot change the columns, do calculations, do advanced searches, or export data.</p><p>You can perform sorting and basic searches using the controls at the top of each column.</p>");
define("_formulize_DE_MINE", "Mes entr�es");
define("_formulize_DE_GROUP", "Les entr�es de tous les gens dans mon(mes) groupe(s)");
define("_formulize_DE_ALL", "Entries by all users in all groups");
define("_formulize_DE_GO", "Apply search terms");
define("_formulize_DE_CHANGECOLS", "Changer les colonnes");
define("_formulize_DE_PICKNEWCOLS", "Pick different columns to view");
define("_formulize_DE_AVAILCOLS", "Available columns:");
define("_formulize_DE_LASTMOD", "Last modified by");
define("_formulize_DE_CREATED", "Created by");
define("_formulize_DE_ON", "on");
define("_formulize_DE_VIEWDETAILS", "Click to view details for this entry.");
define("_formulize_DE_RESETVIEW", "Restaurer le format de vue standard");
define("_formulize_DE_CALCS", "Calculs");
define("_formulize_DE_EXPORT", "Exporter les entr�es");
define("_formulize_DE_EXPORT_CALCS", "Export Calcs");
define("_formulize_DE_SAVE", "Sauvegarder le format de vue courrant");
define("_formulize_DE_DELETE", "Supprimer le format de vue courrant");
define("_formulize_DE_ADDENTRY", "Ajouter une entr�e");
define("_formulize_DE_ADD_MULTIPLE_ENTRY", "Ajouter plusieurs entr�es");
define("_formulize_DE_PROXYENTRY", "Make a proxy entry");
define("_formulize_DE_UPDATEENTRY", "Mettre � jour l�entr�e");
define("_formulize_DE_DELETESEL", "Supprimer la selection");
define("_formulize_DE_CLONESEL", "Cloner la selection");
define("_formulize_DE_CLONE_PROMPT", "How many copies of the selected entries do you want to make? (use numbers, not words)");
define("_formulize_DE_SELALL", "S�lectionner toutes les entr�es");
define("_formulize_DE_CLEARALL", "Effacer les selections");
define("_formulize_DE_CONFIRMDEL", "You are about to delete the selected entries.  Please confirm!");
define("_formulize_DE_DELBOXDESC", "Check this box to select/unselect this entry.");
define("_formulize_DE_CHOOSE_EXPORT", "Choose the export format you want");
define("_formulize_DE_EXPORT_INST", "Choose the format you would like your data exported in.  Comma delimited should work with all data.  However, if you have especially complex combinations of quotation marks and commas within your data itself, and your exported files are not formatting correctly, you may want to try one of the other delimiters instead.");
define("_formulize_DE_XCOMMA", "Comma delimited");
define("_formulize_DE_XTAB", "Tab delimited");
define("_formulize_DE_XCUST", "Custom:");
define("_formulize_DE_XF", "exported_");
define("_formulize_DE_EXPORTCALC_TITLE", "Calculation Results for: ");
define("_formulize_DE_CLICKSAVE", "Right click and save to download your data.");
define("_formulize_DE_CANCELCALCS", "Annuler les calculs");
define("_formulize_DE_SHOWLIST", "Switch to entries");
define("_formulize_DE_HIDELIST", "Switch to calcs.");
define("_formulize_DE_SORTTHISCOL", "Click to sort entries by this column");

define("_formulize_DE_DELETE_ALERT", "You are not allowed to delete that view from the list.");
define("_formulize_DE_CONF_DELVIEW", "You are about to delete this view!  Please confirm.");

//calculations added by kw 2008-09-03
define("_formulize_DE_PICKCALCS", "Pick the calculations you want");
define("_formulize_DE_MODCALCS", "Changer les calculs");
define("_formulize_DE_CALC_COL", "Column(s) to use for the calculations:");
define("_formulize_DE_CALCSUB", "Add Calculation(s) to list");
define("_formulize_DE_CALC_CALCS", "Calculations to perform on the column(s):");
define("_formulize_DE_CALCGO", "Perform Requested Calculations");
define("_formulize_DE_REQDCALCS", "Requested Calculations:");
define("_formulize_DE_CALCALL", "Include blanks/zeros"); 
define("_formulize_DE_CALCNOBLANKS", "Exclude blanks/zeros");
define("_formulize_DE_CALCONLYBLANKS", "Include only blanks/zeros");
define("_formulize_DE_CALC_GROUPING", "Group results by...");
define("_formulize_DE_NOGROUPING", "Do not group results");
define("_formulize_DE_GROUPBYCREATOR", "Group by: User who made entry");
define("_formulize_DE_GROUPBYCREATEDATE", "Group by: Creation date");
define("_formulize_DE_GROUPBYMODIFIER", "Group by: User who last modified entry");
define("_formulize_DE_GROUPBYMODDATE", "Group by: Last modification date");
define("_formulize_DE_GROUPBYCREATOREMAIL", "Group by: Creator's e-mail address");
define("_formulize_DE_CALC_LISTDISPLAY", "Only display calculations<br>(hide the list of entries)");
define("_formulize_DE_CALC_CREATOR", "Utilisateur qui a cr�� l�entr�e");
define("_formulize_DE_CALC_CREATEDATE", "Creation date");
define("_formulize_DE_CALC_MODIFIER", "User who last modified entry");
define("_formulize_DE_CALC_MODDATE", "Last modification date");
define("_formulize_DE_CALC_CREATOR_EMAIL", "Creator's e-mail address");
define("_formulize_DE_REMOVECALC", "Remove this calculation from the list");
define("_formulize_DE_CALC_BTEXT", "Which entries?");
define("_formulize_DE_CALC_GTEXT", "Group Results?");
define("_formulize_DE_CALCHEAD", "Calculation Results");
define("_formulize_DE_CALC_SUM", "Sum Total");
define("_formulize_DE_CALC_AVG", "Averages");
define("_formulize_DE_CALC_MIN", "Minimum Value");
define("_formulize_DE_CALC_MAX", "Maximum Value");
define("_formulize_DE_CALC_COUNT", "Count Entries");
define("_formulize_DE_CALC_PER", "Percentage Breakdown");
define("_formulize_DE_EXCLBLANKS", "Excludes blanks/zeros");
define("_formulize_DE_INCLBLANKS", "Includes blanks/zeros");
define("_formulize_DE_INCLONLYBLANKS", "Includes <i>only</i> blanks/zeros");
define("_formulize_DE_CALC_MEAN", "Mean");
define("_formulize_DE_CALC_MEDIAN", "Median");
define("_formulize_DE_CALC_MODE", "Mode");
define("_formulize_DE_CALC_NUMENTRIES", "Number of Entries");
define("_formulize_DE_CALC_NUMUNIQUE", "Number of Unique Values");
define("_formulize_DE_PER_ITEM", "Item");
define("_formulize_DE_PER_COUNT", "Count");
define("_formulize_DE_PER_PERCENT", "Percentage");
define("_formulize_DE_PER_PERCENTRESPONSES", "As % of responses");
define("_formulize_DE_PER_PERCENTENTRIES", "As % of entries");
define("_formulize_DE_PER_TOTAL", "TOTAL");
define("_formulize_DE_PER_TOTALRESPONSES", "responses");
define("_formulize_DE_PER_TOTALENTRIES", "entries");
define("_formulize_DE_PER_RESPONSESPERENTRY", "response(s) / entry");
define("_formulize_DE_DATAHEADING", "Liste d�entr�es");

//ADVANCED SEARCH: added by kw 2008-09-03
define("_formulize_DE_BUILDQUERY", "Build your query");
define("_formulize_DE_AS_FIELD", "To search this field(s):");
define("_formulize_DE_AS_MULTI_AND", "use AND between multiple fields");
define("_formulize_DE_AS_MULTI_OR", "use OR between multiple fields");
define("_formulize_DE_AS_OPTERM", "Use this operator and term:");
define("_formulize_DE_AS_ADD", "Add this search to the query");
define("_formulize_DE_AS_ADDOTHER", "Other items you can add:");
define("_formulize_DE_AS_REMOVE", "Remove last item from the query");
define("_formulize_DE_ADVSEARCH", "Recherche avanc�e");
define("_formulize_DE_ADVSEARCH_ERROR", "There was a \"parse error\" in the advanced search query you specified.  Most often, this is caused by not having an AND or an OR in between two search terms.  Another common cause is not having ( ) arranged correctly, or not having an equal number of opening and closing ones.");
define("_formulize_DE_SEARCHGO", "Perform Requested Query");
define("_formulize_DE_AS_QUERYSOFAR", "Requested Query So Far:");
define("_formulize_DE_CANCELASEARCH", "Cancel this search");
define("_formulize_DE_MOD_ADVSEARCH", "Modify search");

//CHANGE SCOPE added by kw 2008-09-03
define("_formulize_DE_ADVSCOPE", "Advanced scope");
define("_formulize_DE_PICKASCOPE", "Choose the groups to use for the scope");
define("_formulize_DE_AVAILGROUPS", "Available groups:");
define("_formulize_DE_USETHISSCOPE", "Use these groups as the scope");
define("_formulize_DE_AS_ENTRIESBY", "Entries by: ");
define("_formulize_DE_AS_PICKGROUPS", "Les entr�es de tous les gens dans� [choisir un(des) groupe(s)]");
define("_formulize_DE_PICKDIFFGROUP", "Pick diff. groups");
define("_formulize_DE_NOGROUPSPICKED", "Please click on one or more groups from the list above.  Use CTRL-click to select more than one group.");

//added by kw 2008-09-03
define("_AM_FORMULIZE_LOE_FIRSTPAGE", "<< First");
define("_AM_FORMULIZE_LOE_LASTPAGE", "Derni�re page >>");
define("_AM_FORMULIZE_LOE_ONPAGE", "Page ");

// Notifications added by kw 2008-09-03
define("_formulize_DE_NOTBUTTON", "Notifications");
define("_formulize_DE_SETNOT", "Add a notification option for this form");
define("_formulize_DE_SETNOT_WHEN", "Send this notification when:");
define("_formulize_DE_SETNOT_TOME_WHEN", "Send me this notification when:");
define("_formulize_DE_SETNOT_WHEN_NEW", "a new entry is created");
define("_formulize_DE_SETNOT_WHEN_UPDATE", "an entry is updated");
define("_formulize_DE_SETNOT_WHEN_DELETE", "an entry is deleted");
define("_formulize_DE_SETNOT_WHO", "Send this notification to:");
define("_formulize_DE_SETNOT_WHO_ME", "me");
define("_formulize_DE_SETNOT_WHO_CURUSER", "the user who just created/updated/deleted the entry");
define("_formulize_DE_SETNOT_WHO_GROUP", "users in this group: ");
define("_formulize_DE_SETNOT_FOR", "Send this notification for:");
define("_formulize_DE_SETNOT_FOR_ALL", "all entries");
define("_formulize_DE_SETNOT_FOR_CON", "only entries that meet these conditions:");
define("_formulize_DE_SETNOT_ADDCON", "Add another condition");
define("_formulize_DE_SETNOT_TEMP", "Use a custom message template?  If so, type the filename here.");
define("_formulize_DE_SETNOT_SUBJ", "Use a custom message subject line?  If so, type the subject here.");
define("_formulize_DE_SETNOT_SAVE", "Save this notification");
define("_formulize_DE_NOTLIST", "Current notifications");
define("_formulize_DE_NOT_WHENTEXT", "When ");
define("_formulize_DE_NOT_SENDTEXT", " send a notification to ");
define("_formulize_DE_NOT_CONTEXTIF", " only if ");
define("_formluize_DE_NOT_CONTEXTAND", ", and ");
define("_formulize_DE_NOT_TEMPTEXT", "Use this custom template file: ");
define("_formulize_DE_NOT_SUBJTEXT", "Use this custom subject line: ");

define("_formulize_DE_IMPORTDATA", "Importer des donn�es");

?>
