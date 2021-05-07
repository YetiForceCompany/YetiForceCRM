<?php

declare(strict_types=1);
/**
 * Code coverage collection.
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

require_once 'include/ConfigUtils.php';

$codeCoverage = Tests\Coverage::getInstance();
$codeCoverage->start();

file_put_contents(ROOT_DIRECTORY . '/tests/coverages/_timer.txt', print_r([
	'auto_prepend_file1 >> register_shutdown_function',
	($_SERVER['REQUEST_METHOD'] ?? '') . ':' . ($_SERVER['REQUEST_URI'] ?? '')
], true), FILE_APPEND);

register_shutdown_function(function () {
	file_put_contents(ROOT_DIRECTORY . '/tests/coverages/_timer.txt', print_r([
		'auto_prepend_file2 >> register_shutdown_function',
		($_SERVER['REQUEST_METHOD'] ?? '') . ':' . ($_SERVER['REQUEST_URI'] ?? '')
	], true), FILE_APPEND);
});

chdir($path);
