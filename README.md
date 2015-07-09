
## YetiForceCRM

We design an innovative CRM system that is dedicated for large and medium sized companies. We dedicate it to everyone who values open source software, security and innovation. YetiForce was built on a rock-solid Vtiger foundation, but has hundreds of changes that help to accomplish even the most challenging tasks in the simplest way. Every function within the system was thought through and automated to ensure that all of them work together seamlessly and form a coherent integrity. We looked at the entire sales process and consequently refined the system, module by module. We have years of experience creating tailor made CRM software for a variety of different companies. Download it and have a first-hand experience.

Test [YetiForce] (https://test.yetiforce.com/index.php)

[GitMaster] (https://gitmaster.yetiforce.com)

[GitDeveloper] (https://gitdeveloper.yetiforce.com)

Read the [documentation] (https://yetiforce.com/en/documentation.html) to learn more.

Sign up for our [mailing list](https://lists.sourceforge.net/lists/listinfo/yetiforce-mailing). It is dedicated for people/companies which would like to contribute to development of YetiForce. It isn't an appropriate place for regular users of the software.

Follow us on [Twitter](https://twitter.com/YetiForceEN) to get real-time info about new articles and functionalities. 

YetiForce CRM was orginally forked from Vtiger CRM and has mechanisms that allow to easily migrate from Vtiger to YetiForce.

Below you can see how we improve our project:


#YetiForce 2.1 GA [under development]

2.0 was launched on 21st May 2015 and the following changes are up to 2.0.407 from 8th July 2015.


**Bug Fixes:** 
-	Fixed an option that allows to add records from widgets.
-	Fixed the generation of PDFs.
-	Fixed sharing privileges.
-	Fixed the display of module names in widget history.
-	Fixed a bug that appeared during printing of PDF files from List view.
-	Fixed a bug in filters - it was impossible to edit or delete them.
-	Fixed a bug of not saving notification settings.
-	Fixed a bug of not saving an email address to which notifications about backups should be sent.
-	Fixed a bug of not saving the parameters in General Settings of Backup.
-	Fixed a bug that appeared during saving of a field in Company Details.
-	Fixed a bug that appeared during seving of calculations.
-	Fixed a bug that appeared during export of invoices to PDF.
-	Fixed a bug that appeared during the installation prosess and caused the system to fail.
-	Fixed a bug that did not allow to add a custom field with 1:M relation
-	Fixed a bug that appeared after adding a comment to a related module of Contacts.
-	Fixed a bug that appeared during entering data to the Campaigns Module.
-	Fixed a bug that appeared in a dropdown while creating/editing menu items. 
-	Fixed a bug that appeared during adding a new PriceBook.
-	Fixed a bug that appeared during a transfer of ownership in the FAQs module.
-	Fixed a bug that appeared during a search for duplicates in Sales Orders and Quotes.
-	Fixed a bug that appeared during adding of Data Summary widget.
-	Fixed a bug that appeared during adding of a detail report.
-	Fixed a bug that appeared during setting rss feed as default.
-	Fixed a bug that appeared during changing 'Language available for" in the Language Management.
-	Fixed a bug that appeared during changing buttons configuration in the General Configuration of PDF.
-	Fixed a bug that appeared during deleting related records in the Documents and Opportunities modules.
-	Fixed a bug that appeared after deleting a product in the Calculations module.
-	Fixed a bug that caused that calendar events disappeared after page refresh.
-	Fixed a bug in the OSSMail module - server configuration display failed when the module was disabled.
-	Fixed a bug in the list view in Workflow panel.
-	Fixed a bug that showed a blank page in a related module (Charts) of Projects.
-	Fixed handlers link and unlink.
-	Fixed the display of two parameters in server configuration.
-	Fixed the display of calendar records.
-	Fixed sending emails in workflows.
-	Fixed the display of tickets and invoices in a related list in Accounts.
-	Fixed the display of firstname field in the summary view of Widgets.
-	Fixed the display of PDF module.
-	Fixed the display of tooltip elements. 
-	Fixed copy address from Accounts and Vendors in cost module.
-	Fixed printing of PDF file when related record is deleted.
-	Fixed a deprecated key name that was breaking search in uitype 10 fields
-	Fixed info about the number of records in related modules.
-	Fixed a relation between Quotes and Calculations.
-	Fixed the authentication of users.
-	Fixed the abilty to add fields in the 'Company details' panel.
-	Fixed the system installer.
-	Fixed database connection in the mail module.
-	Fixed closing of calendar notifications. 
-	Fixed import of records from file. 
-	Fixed a header in detail view of Contacts.
-	Fixed workflow responsible for sending emails.
-	Fixed the Mail Scanner configuration.
-	Fixed collapse blocks in various locations.
-	Fixed browser compatibility.
-	Fixed a function responsible for adding widgets. 
-	Fixed widget privileges after the change of role in users.
-	Fixed many error messages that appeared twice.
-	Fixed Quick Create for Costs.


**Improvements:**
-	Further improvements that allow visually impaired peaople to navigate around the system.
-	Moved constant variable assignments out of loop.
-	Moved excess translations from various modules to one file. 
-	Updated jQuery to v2.1.4 and jQuery-ui to v1.11.4 and made changes in javascript files.
-	Updated Bootbox library.
-	Updated DataTables Table jQuery 1.10.7.
-	Updated PNotify library to v2.0.1.
-	Updated jstorage library.
-	Improved the compatibility with PHP version 5.4.
-	Improved the display of text fields.
-	Improved the display of logs in cron.
-	Improved the display of progress bar in the Backup module.
-	Improved the display of edit window for cron tasks.
-	Improved the display of buttons in the Calendar.
-	Improved the display of Groups panel in detail view.
-	Improved the display of many popups. 
-	Improved the display of many modal windows.
-	Improved the display of variables in Brute Force panel.
-	Improved the global variables.
-	Improved the titles of buttons in Details view.
-	Improved the generation of queries for reference fields.
-	Improved icons of filters.
-	Improved error reporting during the verification of configuration.
-	Improved security in converting a Lead. 
-	Improved privileges in the Calendar.
-	Improved email content filtering.
-	Improved backup configuration.
-	Improved the conversion of a lead.
-	Improved the validation of modules with Products.
-	Improved loading of data when a related module is selected.
-	Improved relation handlers.
-	Improved a select window for modules in quick create.
-	Improved search of email addresses in the Mail Module.
-	Improved the layout and functioning of custom views.
-	Improved the loading of unique ID.
-	Improved the panel for Language Management.
-	Improved the export module.
-	Improved the mechanism responsible for password change.
-	Improved database Contoller.
-	Improved the global search.
-	Improved server configuration check.
-	Improved 'Change owner" action for a related Calendar module.
-	Improved import and export view in the Calculations module.
-	Improved adding of new sharing rules.
-	Improved a list of users and groups in the list of records.
-	Improved debugging.
-	Added changes from Vtiger rev. 14461, 14484, 14490, 14501 (6.3.0).
-	Added omitted fixes that had an influence on records creation.
-	Added protection when creating records. 
-	Added protection that prevents from double saving when profiles are being saved. 
-	Added validation of mandatory fields in hidden blocks.
-	Added javascript action for events in the Reservations and Time Control. 
-	Added an uncompressed file to the Bootbox library. 
-	Added a 'type' attribute to buttons in headers in edit view.
-	Added many missing translations.
-	Added missing buttons for generating random colors in Calendar configuration and Activity configuration panels.
-	Optimized backup files.
-	Optimized load scripts.
-	Expanded the configuration of emails. 
-	Secured search of records in a list view.
-	Secured the creation of records in the Calendar.
-	Changed icon names in database.
-	Rebuilt the loading of JS files in import and export inventory modules.
-	Bootstrap 3 - fixed the appearance of the products blok.
-	Bootstrap 3 - subsequent amendments.
-	Bootstrap 3 - amendments in security menu.
-	Bootstrap 3 - improved security menu.
-	Bootstrap 3 - amendments in the Menu Manager.
-	Bootstrap 3 0 improved the appearance of panels within the configuration - Tools, Processes, Mail, Integration, Access Control blocks.
-	Bootstrap 3 - amendments in company menu. 
-	Bootstrap 3 - improved the display of popups.
-	Bootstrap 3 - amendments of comments in Details view.
-	Bootstrap 3 - removed a row from position name.
-	Bootstrap 3 - improved the Edit view of passwords.
-	Bootstrap 3 - improved the Reports module.
-	Bootstrap 3 - improved styles, fixed show/hide fields in the Calendar module.



**New functionalities:**
- Added the ability to send emil in a popup window.
- Added a new type of an email templete. 
- Added the ability to customize languages, layouts and the main file of a module (overwriting the default language files).
- Added support for PDO during installation.
- Added support for php cgi-fcgi (CRON).
- Added checking of the file upload limit in imported modules.
- Added library Selectize.
- Added a function that retrieves a database name.
- Added API webservices to the configuration. 
- Added a new element to the Marketing Processes.
- Added system filters.
- Added a possibility to change the access rules in the Calendar.
- Added a status of email accounts in the Mail Scanner.
- Added a functionality responsible for loading of a new web service.
- Added a configuration for Portal 2.0.


The lists of changes for previous versions of YetiForce CRM are available at [our website] (https://yetiforce.com/pl/dokumentacja-programisty/zmiany.html).
