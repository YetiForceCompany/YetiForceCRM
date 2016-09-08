<?php

/**
 * Cron test class
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

class WebUI extends TestCase {

    public function test() {
	ob_start();
	$webUI = new Vtiger_WebUI();
	$webUI->process(AppRequest::init());
	$response = ob_get_contents();
	ob_end_clean();
	file_put_contents('tests/WebUI.txt',$response);
    }

}
