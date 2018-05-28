<?php
/**
 * Project module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	/**
	 * Default colors of statuses for gantt chart.
	 * If not specified - picklists colors are taken or random color is assigned if there isn't one in picklist.
	 */
	'defaultGanttColors'=>[
		'Project'=>[
			'projectstatus'=>[
				'prospecting'=>'#7B1FA2',
				'initiated'=>'#303F9F',
				'in progress'=>'#1976D2',
				'waiting for feedback'=>'#F57C00',
				'on hold'=>'#455A64',
				'completed'=>'#388E3C',
				'delivered'=>'#5D4037',
				'archived'=>'#616161',
			]
		],
		'ProjectMilestone'=>[
			'projectmilestone_status'=>[
				'PLL_OPEN'=>'#3F51B5',
				'PLL_IN_PROGRESS'=>'#2196F3',
				'PLL_COMPLETED'=>'#4CAF50',
				'PLL_DEFERRED'=>'#607D8B',
				'PLL_CANCELLED'=>'#9E9E9E',
			]
		],
		'ProjectTask'=>[
			'projecttaskstatus'=>[
				'Open'=>'#7986CB',
				'In Progress'=>'#64B5F6',
				'Completed'=>'#81C784',
				'Deferred'=>'#90A4AE',
				'Cancelled'=>'#E0E0E0'
			]
		],
	]
];
