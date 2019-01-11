<?php
/**
 * Backup module config.
 *
 * @package   Config
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	// Backup catalog path
	'BACKUP_PATH' => '', //String
	// Allowed extensions to show on the list
	'EXT_TO_SHOW' => [ //array
		'7z',
		'bz2',
		'gz',
		'rar',
		'tar',
		'tar.bz2',
		'tar.gz',
		'tar.lzma',
		'tbz2',
		'tgz',
		'zip',
		'zipx'
	]
];
