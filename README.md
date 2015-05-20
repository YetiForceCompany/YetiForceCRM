
## YetiForceCRM

We design an innovative CRM system that is dedicated for large and medium sized companies. We dedicate it to everyone who values open source software, security and innovation. YetiForce was built on a rock-solid Vtiger foundation, but has hundreds of changes that help to accomplish even the most challenging tasks in the simplest way. Every function within the system was thought through and automated to ensure that all of them work together seamlessly and form a coherent integrity. We looked at the entire sales process and consequently refined the system, module by module. We have years of experience creating tailor made CRM software for a variety of different companies. Download it and have a first-hand experience.

Test [YetiForce] (https://test.yetiforce.com/index.php)

Read the [documentation] (https://yetiforce.com/en/documentation.html) to learn more.

Sign up for our [mailing list](https://lists.sourceforge.net/lists/listinfo/yetiforce-mailing).

Follow us on [Twitter](https://twitter.com/YetiForceEN) to get real-time info about new articles and functionalities. 

YetiForce CRM was orginally forked from Vtiger CRM and has mechanisms that allow to easily migrate from Vtiger to YetiForce.

Below you can see how we improve our project with each new version:


#YetiForce 2.0 (under development)

The following changes are up to 1.4.459 - [#842] (https://github.com/YetiForceCompany/YetiForceCRM/commit/7a1c10b435fa6e782bf0eaafc6917f0b5a3882ee) revision. 


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
-	Fixed the display of the history of changes in Dashboard.
-	Fixed the display of reminders from the Calendar
-	Fixed the display of Gantt chart.
-	Fixed the display of page header.
-	Fixed the display of the number of records.
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
-	Fixed a bug occuring while saving of field and column names.
-	Fixed getInstance function.
-	Fixed responsive comments in record summary.
-	Fixed a bug with sending emails for the latest version of PHP.
-	Fixed Time Control widget.
-	Fixed Fatal Error that appeared during the installation process [#710] (https://github.com/YetiForceCompany/YetiForceCRM/issues/710)
-	Fixed verification of configuration.
-	Fixed filtering of widgets summary.


**Improvements:**
-	Improved the compatibility of NuSOAP library with php 5.4
-	Improved breadcrumbs menu.
-	Improved the appearance of edit view for the OSSMailTemplates module.
-	Improved the appearance of many widgets.
-	Improved the management of DAV keys.
-	Improved the display of events in the Calendar.
-	Improved the display of history on your desktop.
-	Improved the display of the menu.
-	Improved the display of sales orders.
-	Improved the display of notifications.
-	Improved permissions in Quick Create.
-	Improved the migration mechanisms.
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
-	Removed 'More' button from module detail view.
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
-	Updated slimScroll to v.1.3.0
-	Upgraded debugging.
-	Added new icons to the Calendar.
-	Rebuilt relations in the Calendar module.
-	Moved a tab with a configuration from Mail client to Server configuration settings.
-	Moved a tab with a configuration from Module Colors to Colors.
-	Moved user colors from the calendar configuration to colors in settings.
-	Moved non custom field 'from_portal' to basic table of HelpDesk module.
-	Improved 'open tickets' and 'ticket by status' widgets. 
-	Improved 'user time control' widget.
-	Improved 'Calendar' widget.
-	Improved 'Leads by industry' and 'Leads by status' widgets.
-	Improved the appearance of the menu.
-	Added a safety lock for an invalid PHP version.
-	Fixed the display of events in widgets.
-	Fixed the display of fields when a Lead is converted to an Account.
-	Fixed the display of related records in the Mail module.
-	Fixed the appearance of windows in the manual start of workflows.
-	Reconstructed 'Tickets by status' chart.
-	Reconstructed 'Lead by status' chart.
-	Reconstructed charts in HelpDesk and Project modules.
-	Reconstructed the display of related calendar entries.
-	Reconstructed the retrieving of the list of modules.
-	Reconstructed relations in the Mail module.
-	Added loading of the start and end of work when adding calendar entries.
-	Changed allowed dimensions of widgets.
-	Changed fields order in many Quick Create popups.
-	Changed downloading of an email address.
-	Changed the owner input in widgets.
-	Changed the size of the 'Add record' icons
-	Changed statuses in Assets and Calculations.
-	Changed the license information.
-	Changed the configuration of DAV.
-	Changed the configuration of debugging.
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
-	Added security during the writing of data.
-	Added missing allowances to profiles.
-	Added devel mode to Roundcube.
-	Added entitlement to a notification window in the calendar. 
-	Added SSL force.
-	Added a right panel to the calendar.
-	Added a summary view to Calculations.
-	Added calculations, opportunities and sold products to the configuration of the sales process.
-	Added a widget to the desktop of calculations.
-	Added a 'Switch' button to widgets.
-	Added a function that counts the number of records in a related module.
-	Added a maximum number of records that can be mass edited.
-	Added a new column (Unit) to Products block in Quotes, Purchase Orders, Sales Orders, Invoices, Costs and Calculations.
-	Added a new column to widgets in Opportunities and Calculations.
-	Added a feature that allows to display content of tree fields.
-	Added csrf configuration.
-	Added a relation to the Contacts module.
-	Added additional configuration parameters to IMAP connection.
-	Rebuilt Quick Create. 
-	Rebuilt selection of the owner in convert lead.
-	Rebuilt the sales process.
-	Rebuilt the validation of server requirements.
-	Removed a 'hide completed events' field.
-	Removed a contact field 'Potentials'.
-	Removed an unnecessary loader.
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
-	Improved the header in the calendar.
-	Improved filtering of email content.
-	Improved filtering in Time Control widget.
-	Improved loading of widgets.
-	Improved sharing permissions while associating of records.
-	Improved generating of tasks in workflows.
-	Improved generating of CalUri in CalDAV.
-	Improved the mechnism responsible for replying and forwarding email messages.
-	Improved 'send emails' logs.
-	Improved the fuctioning of the Project Templates module.
-	Secured a function updating information about an email.
-	Secured adding of linkages.
-	Secured the management of widgets.
-	Secured loading of record labels.
-	Increased the width of a search bar.
-	Merged all plugins into one.
-	Disabled the validation of work time in modules: Time Control and Reservations.
-	Diabled the suspension of the system when sql query error appears. 


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
-	Added 'Job title' and 'Decision maker' to Contacts.
-	Added a comparator 'currently logged on user'.
-	Added a manual start of workflows.
-	Added a possibility to assign owners of records only to yourself.
-	Added new widgets to record summary view of Calculations and Opportunities.
-	Added a new 'Expiring sold products' widget.
-	Added a new widget with products and services.
-	Added a possibility to create records in Dashboard widgets.
-	Added a possibility to load attachments for email templates.
-	Added a possibility to block the creation of Opportunities for Accounts in which Assigned To is not a user.
-	Added a possibility to attach documents from CRM to emails.
-	Added a possibility to enable/disable storage and backup folders.
-	Added a possibility to present content in different ways without loosing information or structure.
-	Added a variety of attributes that help visually impaired peaople to navigate around the system.
-	Added an option to enable/disable 'Decision maker' in widget summary.


The lists of changes for previous versions of YetiForce CRM are available at [our website] (https://yetiforce.com/en/ideas,-changes,-bugs/changes.html).
