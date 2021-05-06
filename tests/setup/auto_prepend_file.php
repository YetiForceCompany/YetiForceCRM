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

chdir($path);
