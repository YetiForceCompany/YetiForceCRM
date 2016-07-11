<?php
/**
 * Travis CI test script
 * @package YetiForce.Travis CI
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
chdir(dirname(__FILE__) . '/../');
require 'include/main/WebUI.php';
require('install/models/InitSchema.php');

$db = PearDatabase::getInstance();
$initSchema = new Install_InitSchema_Model($db);
$initSchema->initialize();
