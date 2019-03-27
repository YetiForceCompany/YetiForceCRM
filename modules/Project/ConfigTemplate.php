<?php
/**
 * Project module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	'STATUS_RULES' => [
		'default' => [
			'PLL_PLANNED' => [
				'condition' => 'or',
				'rules' => [
					['operator' => 'all_equal', 'value' => 'PLL_PLANNED'],
					['operator' => 'empty'],
				],
			],
			'PLL_IN_PROGRESSING' => [
				'condition' => 'and',
				'rules' => [
					['operator' => 'is_exist', 'value' => 'PLL_IN_PROGRESSING'],
				],
			],
			'PLL_ON_HOLD' => [
				'condition' => 'and',
				'rules' => [
					['operator' => 'all_equal', 'value' => 'PLL_ON_HOLD', 'automation' => 1],
				],
			],
			'PLL_COMPLETED' => [
				'condition' => 'or',
				'rules' => [
					['operator' => 'all_equal', 'value' => 'PLL_COMPLETED'],
					['operator' => 'only_contains', 'value' => ['PLL_COMPLETED', 'PLL_CANCELLED']],
				],
			],
			'PLL_IN_APPROVAL' => [
				'condition' => 'and',
				'rules' => [
					['operator' => 'all_equal', 'value' => 'PLL_IN_APPROVAL', 'automation' => 1],
				],
			],
			'PLL_CANCELLED' => [
				'condition' => 'and',
				'rules' => [
					['operator' => 'all_equal', 'value' => 'PLL_CANCELLED'],
				],
			],
		],
		'description' => 'Definition of rules for automatic status change',
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
	]
];
