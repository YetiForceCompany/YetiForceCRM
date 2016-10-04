<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
$CONFIG = [
	// Tab name in record preview
	'DEFAULT_VIEW_RECORD' => 'LBL_RECORD_PREVIEW',
	'fileTypeSettings' => [
		// Image settings
		'img' => [
			// Path to uploaded images
			'dir' => '/storage/KnowledgeBase/Img/',
			// Maximum file size, in KiloBytes (2 MB)
			'maxsize' => 2000,
			// Maximum allowed width, in pixels
			'maxwidth' => 900,
			// Maximum allowed height, in pixels
			'maxheight' => 800,
			// Minimum allowed width, in pixels
			'minwidth' => 10,
			// Minimum allowed height, in pixels
			'minheight' => 10,
			// Allowed extensions
			'type' => ['bmp', 'gif', 'jpg', 'jpe', 'png']
		],
		// Audio settings
		'audio' => [
			// Path to uploaded audio
			'dir' => '/storage/KnowledgeBase/Audio/',
			// Maximum file size, in KiloBytes (20 MB)
			'maxsize' => 20000,
			// Allowed extensions
			'type' => ['mp3', 'ogg', 'wav']
		],
		// Video settings
		'video' => [
			// Path to uploaded videos
			'dir' => '/storage/KnowledgeBase/Video/',
			// Maximum file size, in KiloBytes (20 MB)
			'maxsize' => 20000, 
			// Allowed extensions
			'type' => ['mp4'],
			'tagclass' => 'responsiveVideo'
		],
	],
	// If 1 and filename exists, RENAME file, adding "_NR" to the end of filename (name_1.ext, name_2.ext, ..)
	// If 0, will OVERWRITE the existing file
	'rename' => 1,
	// Allowed types of files
	'allowedFileTypes' => ['img', 'audio', 'video'],
];
