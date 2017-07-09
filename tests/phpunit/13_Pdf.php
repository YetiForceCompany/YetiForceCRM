<?php

/**
 * Pdf test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers Pdf::<public>
 */
class Pdf extends TestCase
{

	/**
	 * Testing PDF generation
	 */
	public function testPdf()
	{
		/*
		  $row = (new \App\Db\Query())->from('vtiger_crmentity')->where(['setype' => 'SQuotes'])->limit(1)->one();
		  if ($row) {
		  $request = App\Request::init();
		  $request->set('module', $row['setype']);
		  $request->set('action', 'Detail');
		  $request->set('record', $row['crmid']);
		  $request->set('template', '2');
		  (new Vtiger_WebUI())->process($request);
		  }
		 */
	}
}
