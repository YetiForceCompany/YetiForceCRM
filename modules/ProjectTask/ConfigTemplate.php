<?php
/**
 * ProjectTask module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'STATUS_DEFAULT_VALUE' => [
		'default' => 'PLL_PLANNED',
		'description' => 'The default value is returned when no rule applies.',
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
];
