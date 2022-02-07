<?php
/**
 * Project module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'COLUMNS_IN_HIERARCHY' => [
		'default' => [],
		'description' => 'Columns visible in Project hierarchy [$label => $columnName]'
	],
	'MAX_HIERARCHY_DEPTH' => [
		'default' => 50,
		'description' => 'Max depth of hierarchy',
		'validation' => '\App\Validator::naturalNumber',
		'sanitization' => function () {
			return (int) func_get_arg(0);
		}
	],
	'COUNT_IN_HIERARCHY' => [
		'default' => true,
		'description' => 'Count Projects in hierarchy',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'defaultGanttColors' => [
		'default' => [
			'Project' => [
				'projectstatus' => [
					'PLL_PLANNED' => '#7B1FA2',
					'PLL_IN_PROGRESSING' => '#1976D2',
					'PLL_IN_APPROVAL' => '#F57C00',
					'PLL_ON_HOLD' => '#455A64',
					'PLL_COMPLETED' => '#388E3C',
					'PLL_CANCELLED' => '#616161',
				]
			],
			'ProjectMilestone' => [
				'projectmilestone_status' => [
					'PLL_PLANNED' => '#3F51B5',
					'PLL_IN_PROGRESSING' => '#2196F3',
					'PLL_COMPLETED' => '#4CAF50',
					'PLL_ON_HOLD' => '#607D8B',
					'PLL_CANCELLED' => '#9E9E9E',
				]
			],
			'ProjectTask' => [
				'projecttaskstatus' => [
					'PLL_PLANNED' => '#7986CB',
					'PLL_IN_PROGRESSING' => '#64B5F6',
					'PLL_COMPLETED' => '#81C784',
					'PLL_ON_HOLD' => '#90A4AE',
					'PLL_CANCELLED' => '#E0E0E0'
				]
			],
		],
		'description' => 'Default colors of statuses for gantt chart. f not specified - picklists colors are taken or random color is assigned if there is not one in picklist.'
	],
	'showGanttTab' => [
		'default' => true,
		'description' => 'Show / hide Gantt tab in module Projects',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
];
