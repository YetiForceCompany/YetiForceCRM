<?php

declare(strict_types=1);
/**
 * Code coverage collection.
 *
 * @package   Tests
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 *
 * @codeCoverageIgnore
 */
$path = getcwd();
chdir(__DIR__ . '/../../');

if (!file_exists('vendor')) {
	return;
}
if (PHP_SAPI !== 'cli') {
	\define('IS_PUBLIC_DIR', true);
}
require_once 'include/ConfigUtils.php';

$codeCoverage = Tests\Coverage::getInstance();
$codeCoverage->start();

chdir($path);
