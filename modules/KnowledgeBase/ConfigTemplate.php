<?php
/**
 * KnowledgeBase module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'DEFAULT_VIEW_RECORD' => [
		'default' => 'LBL_RECORD_PREVIEW',
		'description' => 'Default view for record detail view. Values: LBL_RECORD_DETAILS or LBL_RECORD_SUMMARY',
		'validation' => function () {
			$arg = func_get_arg(0);
			return in_array($arg, ['LBL_RECORD_PREVIEW', 'LBL_RECORD_SUMMARY', 'LBL_RECORD_DETAILS']);
		}
	],
//	'fileTypeSettings' => [
//		'default' => [
//			'img' => [
//				'dir' => '/storage/KnowledgeBase/Img/',
//				'maxsize' => 2000,
//				'maxwidth' => 900,
//				'maxheight' => 800,
//				'minwidth' => 10,
//				'minheight' => 10,
//				'type' => ['bmp', 'gif', 'jpg', 'jpe', 'png'],
//			],
//			'audio' => [
//				'dir' => '/storage/KnowledgeBase/Audio/',
//				'maxsize' => 20000,
//				'type' => ['mp3', 'ogg', 'wav'],
//			],
//			'video' => [
//				'dir' => '/storage/KnowledgeBase/Video/',
//				'maxsize' => 20000,
//				'type' => ['mp4'],
//				'tagclass' => 'responsiveVideo',
//			],
//		],
//		'description' => 'File type settings'
//	],
	'rename' => [
		'default' => 1,
		'description' => 'If 1 and filename exists, RENAME file, adding "_NR" to the end of filename (name_1.ext, name_2.ext, ..) If 0, will OVERWRITE the existing file',
	],
//	'allowedFileTypes' => [
//		'default' => ['img', 'audio', 'video'],
//		'description' => 'allowed File Types'
//	],
];
