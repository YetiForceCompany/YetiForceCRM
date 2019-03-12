<?php
/**
 * Api base file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require __DIR__ . '/include/ConfigUtils.php';

$api = new \App\Controller\WebApi();
$api->process();
