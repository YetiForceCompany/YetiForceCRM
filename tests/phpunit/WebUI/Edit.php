<?php
/**
 * Cron test class
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

class EditView extends TestCase
{

	public function test()
	{
		ob_start();
		$request = AppRequest::init();
		$request->set('module', 'Accounts');
		$request->set('view', 'Edit');
		$request->set('record', ACCOUNT_ID);

		$webUI = new Vtiger_WebUI();
		$webUI->process($request);
		$response = ob_get_contents();
		ob_end_clean();
		file_put_contents('tests/EditView.txt', $response);
	}
}
