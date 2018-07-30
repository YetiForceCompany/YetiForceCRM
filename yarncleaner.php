<?php
/**
 * Cleaning after Yarn installation.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
define('ROOT_DIRECTORY', __DIR__ !== DIRECTORY_SEPARATOR ? __DIR__ : '');

require __DIR__ . '/include/RequirementsValidation.php';
require __DIR__ . '/include/main/WebUI.php';

//\App\Installer\Yarn::clean();

if (isset($argv[1])) {
	\App\Installer\Yarn::runEvent($argv[1]);
} else {
	throw new \TypeError('The event name parameter is missing');
}
