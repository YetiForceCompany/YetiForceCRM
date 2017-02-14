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
		\App\Cache::clear();
		foreach (vtlib\Functions::getAllModules() as $id => $module) {
			if ($module['name'] === 'Events' || !\App\Module::isModuleActive($module['name'])) {
				continue;
			}
			$request = AppRequest::init();
			$request->set('module', $module['name']);
			$request->set('view', 'List');
			$webUI = new Vtiger_WebUI();
			$webUI->process($request);
		}
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

	public function testGlobalSearch()
	{
		ob_start();
		$request = AppRequest::init();
		$request->set('module', 'Vtiger');
		$request->set('view', 'BasicAjax');
		$request->set('value', 'yeti');
		$request->set('searchModule', 'Contacts');
		$request->set('mode', 'showSearchResults');
		$request->set('limit', 15);
		$request->set('html', false);
		$request->set('limit', 15);

		$webUI = new Vtiger_WebUI();
		//$webUI->process($request);

		file_put_contents('tests/GlobalSearch.txt', ob_get_contents());
		ob_end_clean();
	}

	public function testReminders()
	{
		ob_start();
		$request = AppRequest::init();
		$request->set('module', 'Calendar');
		$request->set('view', 'Reminders');
		$request->set('type_remainder', true);

		$webUI = new Vtiger_WebUI();
		$webUI->process($request);

		file_put_contents('tests/Reminders.txt', ob_get_contents());
		ob_end_clean();
	}
}
