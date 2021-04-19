<?php

declare(strict_types=1);
/**
 * Generate code coverage report.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
$path = getcwd();
chdir(__DIR__ . '/../../');

if (!file_exists('vendor')) {
	return;
}

include_once 'include/main/WebUI.php';

$codeCoverage = Tests\Coverage::getInstance();
$codeCoverage->generateReport();

chdir($path);
