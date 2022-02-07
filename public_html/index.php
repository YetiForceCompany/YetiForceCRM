<?php
/**
 * Index file.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
chdir(__DIR__ . '/../');
if (!\defined('IS_PUBLIC_DIR')) {
	\define('IS_PUBLIC_DIR', true);
}
require './index.php';
