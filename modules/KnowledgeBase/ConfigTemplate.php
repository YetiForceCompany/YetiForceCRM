<?php
/**
 * KnowledgeBase module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'DEFAULT_VIEW_RECORD' => [
		'default' => 'LBL_RECORD_PREVIEW',
		'description' => 'Default view for record detail view. Values: LBL_RECORD_DETAILS or LBL_RECORD_SUMMARY',
		'validation' => function () {
			$arg = func_get_arg(0);
			return \in_array($arg, ['LBL_RECORD_PREVIEW', 'LBL_RECORD_SUMMARY', 'LBL_RECORD_DETAILS']);
		}
	],
	'defaultViewName' => [
		'default' => 'KnowledgeBase',
		'description' => 'Default module view. Values: KnowledgeBase, List, ListPreview or DashBoard, refresh menu files after you change this value',
		'validation' => function () {
			$arg = func_get_arg(0);
			return 'List' === $arg || 'ListPreview' === $arg || 'DashBoard' === $arg || 'KnowledgeBase' === $arg;
		}
	],
	'rename' => [
		'default' => 1,
		'description' => 'If 1 and filename exists, RENAME file, adding "_NR" to the end of filename (name_1.ext, name_2.ext, ..) If 0, will OVERWRITE the existing file',
	],
	'knowledgeBaseArticleLimit' => [
		'default' => 50,
		'description' => 'Article limit in the knowledge base window',
		'validation' => function () {
			$arg = func_get_arg(0);
			return $arg && \App\Validator::naturalNumber($arg);
		}
	],
];
