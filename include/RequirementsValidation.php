<?php
/**
 * Requirements validation.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
if (\PHP_VERSION_ID < 70400) {
	throw new \Exception('Wrong PHP version, recommended version >= 7.4');
}
if (isset($checkLibrary)) {
	$extensions = get_loaded_extensions();
	foreach (['PDO', 'pdo_mysql', 'json', 'session', 'mbstring', 'fileinfo', 'intl', 'SPL', 'Reflection', 'SimpleXML', 'bcmath'] as $extension) {
		if (!\in_array($extension, $extensions)) {
			throw new \Exception("The {$extension} library is missing");
		}
	}
}
