<?php
/**
 * WebUI test class
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

class WebUI extends TestCase
{

	public function listView()
	{
		ob_start();
		$request = AppRequest::init();
		$request->set('module', 'Accounts');
		$request->set('view', 'List');

		$webUI = new Vtiger_WebUI();
		$webUI->process($request);
		$response = ob_get_contents();
		ob_end_clean();
		file_put_contents('tests/ListView.txt', $response);
	}

	public function detailView()
	{
		ob_start();
		$request = AppRequest::init();
		$request->set('module', 'Accounts');
		$request->set('view', 'Detail');
		$request->set('record', ACCOUNT_ID);

		$webUI = new Vtiger_WebUI();
		$webUI->process($request);
		$response = ob_get_contents();
		ob_end_clean();
		file_put_contents('tests/DetailView.txt', $response);
	}

	public function editView()
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
