<?php

return [
	'modulesHierarchy' => [
		'Accounts' => ['level' => 0],
		'Leads' => ['level' => 0],
		'Vendors' => ['level' => 0],
		'Partners' => ['level' => 0],
		'Competition' => ['level' => 0],
		'OSSEmployees' => ['level' => 0],
		'Contacts' => ['level' => 4],
		'SSalesProcesses' => ['level' => 1],
		'Project' => ['level' => 1],
		'ServiceContracts' => ['level' => 1],
		'Campaigns' => ['level' => 1],
		'FBookkeeping' => ['level' => 1],
		'HelpDesk' => ['level' => 2, 'parentModule' => 'ServiceContracts'],
		'ProjectTask' => ['level' => 3, 'parentModule' => 'ProjectMilestone'],
		'ProjectMilestone' => ['level' => 2, 'parentModule' => 'Project'],
		'SQuoteEnquiries' => ['level' => 2, 'parentModule' => 'Campaigns'],
		'SRequirementsCards' => ['level' => 2, 'parentModule' => 'SSalesProcesses'],
		'SCalculations' => ['level' => 2, 'parentModule' => 'SSalesProcesses'],
		'SQuotes' => ['level' => 2, 'parentModule' => 'SSalesProcesses'],
		'SSingleOrders' => ['level' => 2, 'parentModule' => 'SSalesProcesses'],
		'SRecurringOrders' => ['level' => 2, 'parentModule' => 'SSalesProcesses'],
		'FInvoice' => ['level' => 2, 'parentModule' => 'FBookkeeping'],
		'SVendorEnquiries' => ['level' => 2, 'parentModule' => 'SSalesProcesses'],
	],
	'modulesMapRelatedFields' => [//Map links between modules
		'ProjectTask' => [
			'projectmilestoneid' => ['ProjectMilestone' => ['projectid' => ['projectid']]],
			'parentid' => ['ProjectTask' => ['projectid' => ['projectid'], 'projectmilestoneid' => ['projectmilestoneid']]]
		],
		'HelpDesk' => [
			'projectid' => ['Project' => ['parent_id' => ['linktoaccountscontacts']]],
			'contact_id' => ['Contacts' => ['parent_id' => ['parent_id']]],
			'pssold_id' => ['Assets' => ['product_id' => ['product', 'Products'], 'parent_id' => ['parent_id', 'Accounts']], 'OSSSoldServices' => ['product_id' => ['serviceid', 'Services']]],
			'servicecontractsid' => ['ServiceContracts' => ['parent_id' => ['sc_related_to', 'Accounts'], 'ticketpriorities' => ['contract_priority'], 'contract_type' => ['contract_type'], 'contracts_end_date' => ['due_date']]]
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
		'SVendorEnquiries' => [
			'salesprocessid' => ['SSalesProcesses' => ['accountid' => ['related_to']]]
		],
		'SQuotes' => [
			'accountid' => ['Accounts' => ['company' => ['accountname']]],
			'salesprocessid' => ['SSalesProcesses' => ['accountid' => ['related_to']]]
		],
		'SSingleOrders' => [
			'accountid' => ['Accounts' => ['company' => ['accountname']]],
			'salesprocessid' => ['SSalesProcesses' => ['accountid' => ['related_to']]],
			'squotesid' => ['SQuotes' => ['accountid' => ['accountid']]]
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
		'IGRNC' => [
			'igrnid' => ['IGRN' => ['vendorid' => ['vendorid'], 'storageid' => ['storageid']]]
		],
		'IGDNC' => [
			'igdnid' => ['IGDN' => ['storageid' => ['storageid'], 'accountid' => ['accountid']]]
		],
		'Assets' => [
			'contactid' => ['Contacts' => ['parent_id' => ['parent_id']]],
		],
		'FCorectingInvoice' => [
			'finvoiceid' => ['FInvoice' => ['accountid' => ['accountid']]]
		]
	],
	'modulesMap1M' => [// Base => Parent
		'OSSEmployees' => ['MultiCompany'],
		'Contacts' => ['Accounts'],
		'HelpDesk' => ['Accounts', 'Vendors'],
		'SSalesProcesses' => ['Accounts'],
		'SQuotes' => ['SSalesProcesses'],
		'FInvoice' => ['Accounts'],
		'SSingleOrders' => ['SSalesProcesses'],
		'SRecurringOrders' => ['SSalesProcesses', 'SQuotes'],
		'FBookkeeping' => ['Accounts'],
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
	],
	'modulesMapMMBase' => ['Services', 'Reservations'],
	'modulesMapMMCustom' => [
		'Documents' => ['table' => 'vtiger_senotesrel', 'rel' => 'crmid', 'base' => 'notesid'],
		'Products' => ['table' => 'vtiger_seproductsrel', 'rel' => 'crmid', 'base' => 'productid'],
		'OSSMailView' => ['table' => 'vtiger_ossmailview_relation', 'rel' => 'crmid', 'base' => 'ossmailviewid'],
	]
];
