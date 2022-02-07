<?php
/**
 * Faq module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'knowledgeBaseArticleLimit' => [
		'default' => 50,
		'description' => 'Article limit in the knowledge base window',
		'validation' => function () {
			$arg = func_get_arg(0);
			return $arg && \App\Validator::naturalNumber($arg);
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
];
