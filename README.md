
## YetiForceCRM

We design an innovative CRM system that is dedicated for large and medium sized companies. We dedicate it to everyone who values open source software, security and innovation. YetiForce was built on a rock-solid Vtiger foundation, but has hundreds of changes that help to accomplish even the most challenging tasks in the simplest way. Every function within the system was thought through and automated to ensure that all of them work together seamlessly and form a coherent integrity. We looked at the entire sales process and consequently refined the system, module by module. We have years of experience creating tailor made CRM software for a variety of different companies. Download it and have a first-hand experience.

Read the [documentation] (https://yetiforce.com/en/documentation.html) to learn more.

Sign up for our [mailing list](https://lists.sourceforge.net/lists/listinfo/yetiforce-mailing).

Follow us on [Twitter](https://twitter.com/YetiForceEN) to get real-time info about new articles and functionalities. 

Below you can see how we improve our project with each new version:


#YetiForce 1.5 (under development)

The following changes are up to 1.4.266 - [#666] (https://github.com/YetiForceCompany/YetiForceCRM/commit/2de362754ded59baa9cce7bc596e95cb4f5fe5e6) revision. 


**Bug Fixes:** 
-	Fixed notifications for contacts when a ticket is created, modified or closed.
-	Fixed parsing of variables in email templates.
-	Fixed colors legend in user time widget.
-	Fixed a missing relation of contacts to tickets.
-	Fixed a handler and workflows of helpdesk.
-	Fixed uitype 'Industry' field in Leads.
-	Fixed uitype 'Was read' field.
-	Fixed saving of fields for tasks/events in Quick Create editor. 
-	Fixed hiding of a part of a popup window with events in the Calendar.
-	Fixed saving of wysiwyg fields in Quick Create.
-	Fixed checking for the amount of unread e-mails.
-	Fixed reset of user’s password from YetiForce Customer Portal. 
-	Fixed the pagination in the Calendar.
-	Fixed listing of the calendar entries in summary view.
-	Fixed the creation of events in workflow.
-	Fixed 'change your password'.
-	Fixed the display of date field in PDF.
-	Fixed a bug in coding of the Portuguese language. Changed file format to utf without bom.
-	Fixed loading of the menu.
-	Fixed the default setting of Assigned To when a Lead is converted to an Account.
-	Fixed the reading of data in the conversion of a lead.
-	Fixed sending of emails in brute force.
-	Fixed the conversion of dates.
-	Fixed 'ticket by status' and 'open tickets' widgets.
-	Fixed 'ticket by status' layout in smaller resolution.
-	Fixed linking of records.
-	Fixed bugs in code names.
-	Fixed Calendar Reminder.
-	Fixed a 'More' button in a Calendar widget.
-	Fixed labels in a Calendar widget.
-	Fixed listing of events in a related module.
-	Fixed permissions of related modules.
-	Fixed QuickCreateEditor.
-	Fixed menu items. 
-	Fixed Quick Create for working time in a calendar view.
-	Fixed moving of calendar entries. 
-	Fixed the display of entries in the Calendar.
-	Fixed a reply function in emails.
-	Fixed a function responsible for adding tasks in emails. 
-	Fixed save of Project Tasks.
-	Fixed a relation between a contact and a sales opportunity.
-	Fixed Installation Wizard reports.
-	Fixed the width of a widget filter.
-	Fixed a way to create Project Tasks from a workflow level.
-	Fixed a bug in the windows of the Scheduled Reports.
-	Fixed a generation of CalDAV.
-	Fixed a bug that caused problems with the installation process.
-	Fixed the conversion of Leads.
-	Fixed a function of auto complete for fields.
-	Fixed listing of groups.
-	Fixed queries for permission. 
-	Fixed a bug occuring in some custom modules in which reference fields in List view were always empty.


**Improvements:**
-	Improved the compatibility of NuSOAP library with php 5.4
-	Improved breadcrumbs menu.
-	Improved the appearance of edit view for the OSSMailTemplates module.
-	Improved the management of DAV keys.
-	Improved the display of events in the Calendar.
-	Improved the display of history on your desktop.
-	Improved the display of the menu.
-	Improved permissions in Quick Create.
-	Cleaned up some functions on files.
-	Cleaned up the configuration for colors.
-	Added a file with content for AmountPerSalesPerson widget. Changed a width of the chart.
-	Reconstruction of the auto login and display the quantity of e-mails
-	Added missing values for 'emailoptout' field in the database.
-	Added checking if contact and record owner agreed to get emails.
-	Added a possibility to remove a webdav account.
-	Changed the order of fields in a quick create window for contacts.
-	Added verification of duplicates in workflow 'Create To Do/Task'.
-	Removed a field with a signature and added a global signature configuration.
-	Turned off Signature in send_mail.
-	Turned off inactive chart.
-	Removed some unnecessary files.
-	Removed jquery.hotkeys.js library.
-	Removed the OSSMenuManager module.
-	Updated FullCalendar v2.3.1 library.
-	Updated jstree library. 
-	Updated icons for modules.
-	Updated Chart.js library to v1.0.2. 
-	Updated Browser_compatibility.html page.
-	Updated Credits.
-	Updated Smart 3.1.21. library
-	Upgraded debugging.
-	Added new icons to the Calendar.
-	Rebuilt relations in the Calendar module.
-	Moved a tab with a configuration from Mail client to Server configuration settings.
-	Moved a tab with a configuration from Module Colors to Colors.
-	Moved user colors from the calendar configuration to colors in settings.
-	Improved 'open tickets' and 'ticket by status' widgets. 
-	Improved 'user time control' widget.
-	Improved 'Calendar' widget.
-	Improved 'Leads by industry' and 'Leads by status' widgets.
-	Improved the appearance of the menu.
-	Added a safety lock for an invalid PHP version.
-	Fixed the display of events in widgets.
-	Fixed the display of fields when a Lead is converted to an Account.
-	Fixed the appearance of windows in the manual start of workflows.
-	Reconstructed 'Tickets by status' chart.
-	Reconstructed 'Lead by status' chart.
-	Reconstructed charts in HelpDesk and Project modules.
-	Reconstructed the display of related calendar entries.
-	Reconstructed the retrieving of the list of modules.
-	Added loading of the start and end of work when adding calendar entries.
-	Changed allowed dimensions of widgets.
-	Changed fields order in many Quick Create popups.
-	Changed downloading of an email address.
-	Changed the owner input in widgets.
-	Changed the size of the 'Add record' icons
-	Expanded masking with new types of fields.
-	Added a new template for the mail module.
-	Added selects buttons for widgets.
-	Added an option to check the status in workflow VTCreateTodoTask.
-	Added shared permissions with a product/service in the sales opportunity.
-	Added a related module 'Documents' to modules: OSSSoldServices, OSSOutsourcedServices and OutsourcedProducts.
-	Added sharing privileges in events list.
-	Added Grantt charts to the Projects module.
-	Added session life time to the configuration file.
-	Added a security of the displayed value in the reference to the edit view.
-	Added a switch (completed and future) in the events and related modules for the calendar.
-	Added support for bootstrap-switch.
-	Added a possibility to hide a left panel in the calendar.
-	Added records to Quick Create.
-	Added a mechanism limiting the list of groups.
-	Added loading of data in te calendar.
-	Added close in time Events and To Dos to the calendar when in a quick create view.
-	Aded security during the writing of data.
-	Rebuilt Quick Create. 
-	Rebuilt selection of the owner in convert lead.
-	Rebuilt the sales process.
-	Removed a 'hide completed events' field.
-	Removed a contact field 'Potentials'.
-	Improved a function getArrayFromValue.
-	Improved getQuickCreateModules method.
-	Improved import of updates.
-	Improved the update system.
-	Improved the definition of IP in Brute Force and logging history.
-	Improved an action bar in emails. 
-	Improved printing in emails.
-	Improved the display of notifications.
-	Improved a global variable in the database.
-	Improved permissions to Quick Create records.
-	Improved the reference field.
-	Secured a function updating information about an email.
-	Secured adding of linkages.
-	Secured the management of widgets.
-	Secured loading of record labels.


**New functionalities:**
-	Added 'determine version of php' to htaccess.
-	Added a configuration of auto login.
-	Added a plugin to RC needed for auto login.
-	Added new fields to the OSSMailTemplates module.
-	Added a possibility to select an email account.
-	Added a new field to Users - 'Approval for email'.
-	Added a new field to ProjecTasks - 'Estimated work time'.
-	Added new fields to ProjectMilestone - 'Priority' and 'Progress'.
-	Added a ReadRecord button to Profiles panel. 
-	Added additional actions to profiles.
-	Added support, financial, marketing and realization processes to settings.
-	Added 'Convert to Account' in the Marketing Processes.
-	Added a possibility to hide dashboard view in modules.
-	Added a global signature in the mail module.
-	Added a random color generation for users.
-	Added a flot library.
-	Added jsTree v3.1.0 library.
-	Added Mousetrap v 1.5.2 library.
-	Added a bootstrap switch library.
-	Added a table with menus.
-	Added a new panel for the configuration of menu.
-	Added keyboard shortcuts (hotkeys).
-	Added safeAjax function to app.js
-	Added LDAP configuration.
-	Added Calendar Widget.
-	Added Cache Control.
-	Added an auto complete function for fields when a record is being created.
-	Added an auto complete function for a subject field in Quick Create of Time Control.
-	Added a history of recently viewed pages. 
-	Added date and time to the history of recently viewed pages.
-	Added a function to retrieve time.
-	Added an 'Update' button.
-	Added a releated module ProjectTask to the module ProjectMilestone.
-	Added a function which determines remote IP.
-	Added 'Job title' to Contacts.
-	Added a comparator 'currently logged on user'.
-	Added a manual start of workflows.
-	Added a possibility to assign owners of records only to yourself.



#YetiForce 1.4 (released on 25th March 2015)

**Bug Fixes:** 
-	Fixed various bugs from Vtiger’s engine that appeared when a recurring invoice was created.
-	Fixed a bug that appeared when a display type of newly added field was corrupted during editing, just after its creation.
-	Fixed a Time Control Dashboard widget.
-	Fixed an email templates creation.
-	Fixed module columns appearing in the documents.
-	Fixed loading of data in the fields in the quick create.
-	Fixed a generation of recurrent invoice date.
-	Fixed Credits view.
-	Fixed an email template in Send Customer Login Details.
-	Fixed an option of mass edit for user passwords.
-	Fixed a critical error in Calculations. After adding a record to Calculations, list view of the module was unusable.
-	Fixed a Calendars Ajax Edit functionality.
-	Fixed a display of tree type fields in popups.
-	Fixed hiding of a button for records creation in related modules if a user doesn't have privileges to create new records.
-	Fixed a propagation of products/services limit (in relation to Potential) to other modules than Quotes, Calculations, Sales Order and Invoice.
-	Fixed add events to the Calendar.
-	Fixed a bug that appeared while saving new order of menu blocks.
-	Fixed an edit option in the records preview.
-	Fixed save of a "Copy permissions automatically" field.
-	Fixed quick create for Products and Services.
-	Fixed a bug in logs.
-	Fixed the display of headers.
-	Fixed loading of data into the fields in the quick create.
-	Fixed the edit view in the Passwords module.
-	Fixed adding pdf attachments in the Quotes module.
-	Fixed search of an email address in an email message.
-	Fixed the display of Products/Services in a popup window.
-	Fixed a template error that appeared when there were more menu blocks that could be contained.
- Fixed the export module


**Improvements:**
-	Added a possibility to change ID of sessions after logging.
-	Added names for subcategories in search results in trees.
-	Added a relation between Contacts and Vendors.
-	Added an integration of mobile phone calls listening.
-	Added hints to the mask in the editor field.
-	Improved translations.
-	Added other types to workflow.
-	Added blocks hiding management fields. 
-	Added a configuration panel for adding non-working Public Holidays.
-	Improved a tree field.
-	Added a possibility to close events quickly in the widget summary.
-	Reconstruction and a clean-up of file structure.
-	Added a new type of field to Time Control.
-	Added a possibility to add fields to a company.
-	Added a possibility to redirect a list to Time Control after clicking on user time widget.
-	Improved migration mechanisms.
-	Improved search for tree type fields in a record list view.
-	Added a possibility to delete record from the Calendar widget when the task was marked as done.
-	Added an option that sets by default a person who convers lead to opportunity as a record owner.
-	Updated a Fullcalendar library.
-	Improved search in Public Holiday.
-	Added the option that allows to select time type that will be shown on a user time control chart.
-	Added default start and end hours in month view in Time Control.
-	Added users and types of coloring to the Calendar.
-	Added a possibility to enable/disable brute force, a possibility to add users (with admin privileges) to email notifications if brute force has been detected.
-	Improved the way that columns appear in the associated modules.
-	Improved the creation of picklists.
-	Added comments widget to SalesOrders.
-	Improved checking of the configuration during installation.
-	Improved debugging.
-	Improved the display of user time control widget. 
-	Improved  the menu.
-	Improved the Quotes module.
-	Improved the Calculations module.
-	Improved  a validation of records.
- Improved the time chart
-	Added new types of fields for working time. 
-	Added a possibility to create relationships between modules.
-	Added a page with recent history.
-	Improved Import updates.
-	Added a possibility to import and export relations in the manifest file with modules other than the installed ones.
-	Added a protection when events in the widget are being closed.
-	Added a file with a new handler that updates sharing.
-	Added a new type of handler that is triggered when records are related.
-	Added validation fields with masks.
-	Added a new field Legal form to the Accounts and Leads module.
-	Added a new field Relation to the Leads module
-	Updated Roundcube to version 1.1.0.


 
**New functionalities:**
-	Added a widget with a last updated record.
-	Added a filter to the calendar time.
-	Added a new module – Requirement cards.
-	Added a new module – Quotes enquires.
-	Added a configuration panel for setting if a popup for products should show products only related to presently chosen Potential or not.
-	Added a configuration of Sales Processes.
-	Added a field 'All Day' to the Calendar.
-	Added a new module – Holidays Entitlement.
-	Added modules: PaymentsIn and PaymentsOut
-	Added a possibility to create and edit help icons.
-	Added fields: local number and building number in Vendors.
-	Added validation in Quick Create of Time Control.
-	Reconstruction of events displayed in the Calendar.
-	Added a new option to the Calendar configuration panel.
-	Added the integration with CalDAV.
-	Added a new module - NewOrders.
-	Added a functionality, which can send email notifications when backup is complete.
-	Added a possibility to send a backup to the ftp server.
-	Added Saber Dav library.
-	Added a new menu block "Secretariat".
-	Added new modules: Incoming and Outgoing Letters.
-	Added the first part of the integration with WebDav.
-	Added a new module - Reservations.



#YetiForce 1.3 (released on 13th February 2015)

**Bug Fixes:** 
-	Fixed an issue with a decimal separator in PDF.
-	Fixed a bug in dynamically generated enquiries.
-	Fixed an issue related to reading ICS files.
-	Improved language files.
-	Deleted unused files.
-	Improved a mechanism responsible for saving translations.
-	Engine optimization – logical operators.
-	Fixed the display of related modules.
-	Fixed the quick edit view in the Calendar.
-	Fixed saving of tasks in workflow.
-	Fixed the display of pick list in PDF module.
-	Fixed the display of widgets.
-	Fixed relation of data in the Calendar.
-	Fixed an issue that existed when a widget was added to a Project Task.
-	Fixed report generation in PDF.
-	Fixed the system installer.
-	Fixed the display of WYSIWIG editor in quick create.
-	Fixed the display of time format in Employees.
-	Fixed the creation of tickets in the Customer Portal.
-	Fixed the setting of the main module field (Entity Identifier).
-	Fixed encoding in WYSIWYG file.


**Improvements:**
-	Improved mechanisms verifying the actual web server configuration (during installation and configuration).
-	Added a function that tests connection with the mobile application.
-	Added support for untypical ICS files.
-	Added additional mechanism detecting errors in the email module.
-	Added new translations in language files.
-	Updated a Russian language pack.
-	Updated a German language pack.
-	Improved a mechanism responsible for a password reset in the Customer Portal.
-	Improved a mechanism responsible for a migration to a new version [Vtiger 6.1 > YetiForce 1.3]. In the new version, migration will be only available for Vtiger 6.2.
-	Developed a mechanism responsible for coloring of module names and icons in modules.
-	Optimization of calls to mobile phones.
-	Optimization of a management panel for related modules.
-	Migration of a folder field from Documents to a new filed type.
-	Optimization of ADODB.
-	Improved a mechanism responsible for making relations in mobile phone connections.
-	Added a possibility to configure the minimum time for CRON [above 15 mins].
-	Added records summary to the Employees module.
-	Improved an integrative bar in the email module.
-	Updated zend library (encoding and decoding).
-	Improved the display of displaytype in fields management.
-	Improved the authorization in the email module.
-	Added a possibility to set a primary language from the panel.
-	Improved the display of emails, added filters checking WYSIWYG displayed content.
-	Secured user creation, improved import of users.
-	Added vendors to action bar in the email module.
-	Organized file structure (data directory removed). 
-	Improved webforms.
-	Redeveloped Brute Force verification.
-	Redeveloped tag cloud and its management.
-	Redeveloped address search.
-	Improved export and import of modules.
-	Added a time stamp for records preview.
-	Added a new field: “Updated”.
-	Redeveloped a password reminder in the Customer Portal.
-	Added suggestions on buttons.


**New functionalities:**
-	Added jsTree library.
-	Added a module for the management of jsTree templates.
-	Added new uitype for jsTree.
-	Added a panel for widget management and for defining privileges.
-	Added a panel for column management in related modules and popup windows.
-	Added a mechanism for mass enable and disable of tasks in workflow.
-	Updated the primary engine to Vtiger 6.2.0 [rev. 14427].
-	Added a mechanism that allows to duplicate users.
-	Added a panel that allows to identify modules that are registered by ModTracker.
-	Added a widget with Employee’s working time.
-	Added jquery inputmask library.
-	Added a functionality that allows to set a mask on a filed.
-	Added a possibility to search for addresses Google Maps.
-	Added a functionality that allows to unmark whether a record was read. 
-	Added a new module: Ideas.



#YetiForce 1.2 (released on 20th January 2015)


**Bug fixes:**
- Fixed the display of errors in mail module.
- Fixed the display of records in calendar.
- Fixed the display of colored records.
- Fixed search in uitype10 fields.
- Fixed adding of menu items. 
- Fixed coping of address data. 
- Improved saving in language module. 
- Fixed function responsible for sending PDF via mail using workflows. 
- Fixed date format in validation mechanism. 
- Fixed edit view in PDF module. 
- Fixed function responsible for creating modules from console. 
- Disabled a possibility to switch users. 
- Fixed size of modal window. 
- Improved mechanism responsible for updates and migration [FOREIGN_KEY_CHECKS].
- Fixed issues reported at github.com

**Improvements:**
- Language files. 
- Added new uitype (list of modules with records).
- Added validation of separators for numbers.
- Added api for communication with mobile devices [PushCall, DialHistory].
- Improved login in mail module. 
- Added validation in a mechanism responsible for creating fields. 
- Added validation of user filter. 
- Added a mechanism that allows to add empty module with records. 
- Improved a mechanism responsible for updates.

**New functionalities:**
- A possibility to color module names (list view, record preview, list view in related modules, breadcrumbs menu). 
- Added a panel allowing to manage filters in calendar. 
- Added a new language (Russian) – special thanks to waw555 [Алексей].
- Added a panel allowing to manage connections with API.
- New module for mobile call history. 
- Added a panel allowing to manage widgets.




#YetiForce 1.1 (released on 8th January 2015)

**Updates and enhancements:**
-	Uploaded changes from Vtiger 6.2.0 rev. 14388 [only these that we considered essential, e.g. changes within their shop were ignored].
-	Language updates (PL_PL, EN_US, DE_DE) and a new language in a beta version: PT_BR. We would like to thank new developers for github!
-	Optimized records coloring within lists.
-	Optimized widgets in a record summary.
-	Fixed a bug that disabled the function of generating modules from a console.
-	Fixed special functions in PDF.
-	Error logs from php server moved to the logs directory.
-	Fixed numerous minor bugs.

**New functionalities:**
-	An option enabling permissions to edit comments was added to profiles.
-	Added a module for system updates.
-	Added a Backup module. 
-	Added readcrumbs menu. 
-	Added a tool for verifying the configuration of parameters and permissions on the server. 
-	Added D3js - Data-Driven Documents library.
-	Optimized data loading in uitype10 fields.
-	Added new displaytype 10.
-	Added Widget KPI [it is going to be improved and optimized].
-	iCal support! Now you can accept invitations and send them from an email client level!
-	Added a new autocomplete mechanism for email addresses within an email client.
-	Added a possibility to color some elements [adding a color in a module menu e.g. Accounts results in having the same color in a breadcrumbs menu and in a record list).
-	A possibility to manage widgets from a startup screen was added to a panel!

**Security:**
-	Remote access to the following directories was blocked: api, backup, cache\addressBook, logs, session, storage.

**Optimization of processes:**
-	Added new fields.
-	Changed logics of fields within Tickets module.
-	Moved the management of calendar filters to a panel.
-	Optimized a mechanism calculating tax.

#YetiForce 1.0 was the first version reeased in 2014.
