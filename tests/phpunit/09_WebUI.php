<?php
/**
 * WebUI test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers WebUI::<public>
 */
class WebUI extends TestCase
{

	/**
	 * Testing list view
	 */
	public function testListView()
	{
		\App\Cache::clear();
		foreach (vtlib\Functions::getAllModules() as $module) {
			if ($module['name'] === 'Events') {
				continue;
			}
			ob_start();
			ob_implicit_flush(false);

			$request = App\Request::init();
			$request->set('module', $module['name']);
			$request->set('view', 'List');
			$webUI = new Vtiger_WebUI();
			$webUI->process($request);

			file_put_contents('tests/ListView.txt', ob_get_contents());
			ob_end_clean();
		}
	}

	/**
	 * Testing detail view
	 */
	public function testDetailView()
	{
		ob_start();
		$request = App\Request::init();
		$request->set('module', 'Accounts');
		$request->set('view', 'Detail');
		$request->set('record', ACCOUNT_ID);

		$webUI = new Vtiger_WebUI();
		$webUI->process($request);
		file_put_contents('tests/DetailView.txt', ob_get_contents());
		ob_end_clean();
	}

	/**
	 * Testing edit view
	 */
	public function testEditView()
	{
		ob_start();
		$request = App\Request::init();
		$request->set('module', 'Accounts');
		$request->set('view', 'Edit');
		$request->set('record', ACCOUNT_ID);

		$webUI = new Vtiger_WebUI();
		$webUI->process($request);

		file_put_contents('tests/EditView.txt', ob_get_contents());
		ob_end_clean();
	}

	/**
	 * Search engine testing
	 */
	public function testGlobalSearch()
	{
		ob_start();
		$request = App\Request::init();
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

	/**
	 * Testing reminds of calendars
	 */
	public function testReminders()
	{
		ob_start();
		$request = App\Request::init();
		$request->set('module', 'Calendar');
		$request->set('view', 'Reminders');
		$request->set('type_remainder', true);

		$webUI = new Vtiger_WebUI();
		$webUI->process($request);

		file_put_contents('tests/Reminders.txt', ob_get_contents());
		ob_end_clean();
	}
}
