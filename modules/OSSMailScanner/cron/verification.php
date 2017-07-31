<?php
/**
 * Verification
 * @package YetiForce.Cron
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
require_once 'include/main/WebUI.php';
$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
$recordModel->verificationCron();
