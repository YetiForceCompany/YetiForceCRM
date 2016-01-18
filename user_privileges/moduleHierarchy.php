<?php
$modulesHierarchy = [
	'Accounts' => ['level' => 0],
	'Contacts' => ['level' => 0],
	'Leads' => ['level' => 0],
	'Vendors' => ['level' => 0],
	'Partners' => ['level' => 0],
	'Competition' => ['level' => 0],
	'OSSEmployees' => ['level' => 0],
	'SSalesProcesses' => ['level' => 1],
	'Project' => ['level' => 1],
	'ServiceContracts' => ['level' => 1],
	'Campaigns' => ['level' => 1],
	'HelpDesk' => ['level' => 2, 'parentModule' => 'ServiceContracts'],
	'ProjectTask' => ['level' => 2, 'parentModule' => 'Project'],
	'ProjectMilestone' => ['level' => 2, 'parentModule' => 'Project'],
	'SQuoteEnquiries' => ['level' => 2, 'parentModule' => 'SSalesProcesses'],
	'SRequirementsCards' => ['level' => 2, 'parentModule' => 'SSalesProcesses'],
	'SCalculations' => ['level' => 2, 'parentModule' => 'SSalesProcesses'],
	'SQuotes' => ['level' => 2, 'parentModule' => 'SSalesProcesses'],
	'SSingleOrders' => ['level' => 2, 'parentModule' => 'SSalesProcesses'],
	'SRecurringOrders' => ['level' => 2, 'parentModule' => 'SSalesProcesses']
];
/*
 * Map links between modules
 */
$modulesMapRelatedFields = [
	'ProjectTask' => [
		'projectmilestoneid' => ['ProjectMilestone' => ['projectid' => ['projectid']]],
		'parentid' => ['ProjectTask' => ['projectid' => ['projectid'], 'projectmilestoneid' => ['projectmilestoneid']]]
	],
	'HelpDesk' => [
		'projectid' => ['Project' => ['parent_id' => ['linktoaccountscontacts']]],
		'contact_id' => ['Contacts' => ['parent_id' => ['parent_id']]],
		'pssold_id' => ['Assets' => ['product_id' => ['product', 'Products']], 'OSSSoldServices' => ['product_id' => ['serviceid', 'Services']]]
	],
	'OSSTimeControl' => [
		'projectid' => ['Project' => ['accountid' => ['linktoaccountscontacts']]]
	],
	'SRequirementsCards' => [
		'salesprocessid' => ['SSalesProcesses' => ['accountid' => ['related_to']]]
	],
	'SCalculations' => [
		'salesprocessid' => ['SSalesProcesses' => ['accountid' => ['related_to']]]
	],
	'SQuotes' => [
		'accountid' => ['Accounts' => ['company' => ['accountname']]],
		'salesprocessid' => ['SSalesProcesses' => ['accountid' => ['related_to']]]
	],
	'SSingleOrders' => [
		'accountid' => ['Accounts' => ['company' => ['accountname']]],
		'salesprocessid' => ['SSalesProcesses' => ['accountid' => ['related_to']]]
	],
	'SRecurringOrders' => [
		'accountid' => ['Accounts' => ['company' => ['accountname']]],
		'salesprocessid' => ['SSalesProcesses' => ['accountid' => ['related_to']]]
	],
	'SQuoteEnquiries' => [
		'salesprocessid' => ['SSalesProcesses' => ['accountid' => ['related_to']]]
	],
	'SSalesProcesses' => [
		'projectid' => ['Project' => ['accountid' => ['linktoaccountscontacts']]]
	],
];

// Base => Parent
$modulesMap1M = [
	'Contacts' => ['Accounts'],
	'HelpDesk' => ['Accounts', 'Vendors'],
	'Project' => ['Accounts'],
	'ProjectTask' => ['Project'],
	'ProjectMilestone' => ['Project'],
	'ServiceContracts' => ['Accounts'],
	'Faq' => ['Products'],
	'PaymentsOut' => ['Accounts'],
	'PaymentsIn' => ['Accounts'],
	'OSSTimeControl' => ['Accounts', 'Project', 'HelpDesk', 'Leads'],
	'HolidaysEntitlement' => ['OSSEmployees'],
	'OSSSoldServices' => ['Accounts', 'Leads'],
	'OSSOutsourcedServices' => ['Accounts', 'Leads'],
	'Assets' => ['Accounts', 'Leads'],
	'OutsourcedProducts' => ['Accounts', 'Leads'],
	'OSSPasswords' => ['Accounts', 'Leads', 'HelpDesk', 'Vendors'],
	'Calendar' => ['Accounts', 'Contacts', 'OSSEmployees', 'Leads', 'Vendors', 'HelpDesk', 'Project', 'HelpDesk', 'ServiceContracts', 'Campaigns'],
];

$modulesMapMMBase = ['Services', 'Reservations'];
$modulesMapMMCustom = [
	'Documents' => ['table' => 'vtiger_senotesrel', 'rel' => 'crmid', 'base' => 'notesid'],
	'Products' => ['table' => 'vtiger_seproductsrel', 'rel' => 'crmid', 'base' => 'productid'],
	'OSSMailView' => ['table' => 'vtiger_ossmailview_relation', 'rel' => 'crmid', 'base' => 'ossmailviewid'],
];
