<?php
// Base => Parent
$modulesMap1M = [
	'Contacts' => ['Accounts'],
	'Potentials' => ['Accounts'],
	'HelpDesk' => ['Accounts','Vendors'],
	'Project' => ['Accounts'],
	'ProjectMilestone' => ['Project'],
	'ProjectTask' => ['ProjectMilestone'],
	'ServiceContracts' => ['Accounts'],
	'Faq' => ['Products'],
	'PaymentsOut' => ['Accounts'],
	'PaymentsIn' => ['Accounts'],
	'OSSTimeControl' => ['Accounts','Project','HelpDesk','Potentials','Leads'],
	'HolidaysEntitlement' => ['OSSEmployees'],
	'OSSSoldServices' => ['Accounts','Leads'],
	'OSSOutsourcedServices' => ['Accounts','Leads'],
	'Assets' => ['Accounts','Leads'],
	'OutsourcedProducts' => ['Accounts','Leads'],
	'OSSPasswords' => ['Accounts','Leads','HelpDesk','Vendors'],
	'Calendar' => ['Accounts','Contacts','OSSEmployees','Leads','Vendors','HelpDesk','Project','HelpDesk','Potentials','ServiceContracts','Campaigns'],
];

$modulesMapMMBase = ['Services','Reservations'];
$modulesMapMMCustom = [
	'Documents' => ['table' => 'vtiger_senotesrel', 'rel' => 'crmid', 'base' => 'notesid'],
	'Products' => ['table' => 'vtiger_seproductsrel', 'rel' => 'crmid', 'base' => 'productid'],
	'OSSMailView' => ['table' => 'vtiger_ossmailview_relation', 'rel' => 'crmid', 'base' => 'ossmailviewid'],
];
