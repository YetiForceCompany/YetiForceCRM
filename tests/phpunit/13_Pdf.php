<?php
/**
 * Pdf test class
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers Pdf::<public>
 */
class Pdf extends TestCase
{

	public function testPdf()
	{
		/*
		  $row = (new \App\Db\Query())->from('vtiger_crmentity')->where(['setype' => 'SQuotes'])->limit(1)->one();
		  if ($row) {
		  $request = AppRequest::init();
		  $request->set('module', $row['setype']);
		  $request->set('action', 'Detail');
		  $request->set('record', $row['crmid']);
		  $request->set('template', '2');
		  (new Vtiger_WebUI())->process($request);
		  }
		 */
	}
}
