<?php
/**
 * WebUI test class
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers WebUI::<public>
 */
class WebUI extends TestCase
{

	public function testListView()
	{
		ob_start();
		$request = AppRequest::init();
		$request->set('module', 'Accounts');
		$request->set('view', 'List');

		$webUI = new Vtiger_WebUI();
		$webUI->process($request);

		file_put_contents('tests/ListView.txt', ob_get_contents());
		ob_end_clean();
	}

	public function testDetailView()
	{
		ob_start();
		$request = AppRequest::init();
		$request->set('module', 'Accounts');
		$request->set('view', 'Detail');
		$request->set('record', ACCOUNT_ID);

		$webUI = new Vtiger_WebUI();
		$webUI->process($request);
		file_put_contents('tests/DetailView.txt', ob_get_contents());
		ob_end_clean();
	}

	public function testEditView()
	{
		ob_start();
		$request = AppRequest::init();
		$request->set('module', 'Accounts');
		$request->set('view', 'Edit');
		$request->set('record', ACCOUNT_ID);

		$webUI = new Vtiger_WebUI();
		$webUI->process($request);

		file_put_contents('tests/EditView.txt', ob_get_contents());
		ob_end_clean();
	}
}
