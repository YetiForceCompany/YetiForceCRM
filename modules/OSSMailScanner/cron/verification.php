<?php
/**
 * Verification.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
require_once 'include/main/WebUI.php';
$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
$recordModel->verificationCron();
