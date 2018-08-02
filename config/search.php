<?php
/**
 * Search config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
$CONFIG = [
	// Auto select current module in global search (true/false)
	'GLOBAL_SEARCH_SELECT_MODULE' => true,
	// Auto select current module in global search (int)
	'GLOBAL_SEARCH_MODAL_MAX_NUMBER_RESULT' => 100,
	// Global search - Should the results be sorted in MySQL or PHP while displaying (None = 0, PHP = 1, Mysql = 2). The parameter impacts system efficiency.
	'GLOBAL_SEARCH_SORTING_RESULTS' => 0,
	// Global search - Show current module as first in search results (true/false).
	'GLOBAL_SEARCH_CURRENT_MODULE_TO_TOP' => true,
	// Global search - Search for records while entering text  (1/0).
	'GLOBAL_SEARCH_AUTOCOMPLETE' => 1,
	// Global search - Max number of displayed results. The parameter impacts system efficiency.
	'GLOBAL_SEARCH_AUTOCOMPLETE_LIMIT' => 15,
	// Global search - The minimum number of characters a user must type before a search is performed. The parameter impacts system efficiency
	'GLOBAL_SEARCH_AUTOCOMPLETE_MIN_LENGTH' => 3,
	// Global search - Show operator list.
	'GLOBAL_SEARCH_OPERATOR_SELECT' => true,
	// Global search - Default search operator. (FulltextBegin,FulltextWord,Contain,Begin,End)
	'GLOBAL_SEARCH_DEFAULT_OPERATOR' => 'FulltextBegin',
	// Colors for record state will be displayed in list view, history, and preview.
	'LIST_ENTITY_STATE_COLOR' => [
		'Archived' => '#0032a2',
		'Trash' => '#ab0505',
		'Active' => '#009405',
	],
];
