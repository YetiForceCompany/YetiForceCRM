<?php

/**
 * Pdf test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace Tests\Settings;

class Pdf extends \Tests\Base
{
	/**
	 * Module name.
	 */
	const MODULE_NAME = 'Accounts';

	/**
	 * File name.
	 */
	public $fileName = 'test.pdf';

	/**
	 * Temporary model.
	 *
	 * @var \Settings_PDF_Record_Model
	 */
	private static $pdfModel;

	/**
	 * Testing creating template.
	 */
	public function testCreateTemplate()
	{
		$this->fileName = uniqid() . '.pdf';
		$pdfModel = \Settings_PDF_Record_Model::getCleanInstance(self::MODULE_NAME);
		$pdfModel->set('module_name', self::MODULE_NAME);
		$pdfModel->set('status', 1);
		$pdfModel->set('primary_name', 'test');
		$pdfModel->set('page_format', 'A4');
		$pdfModel->set('language', 'pl-PL');
		$pdfModel->set('page_orientation', 'PLL_PORTRAIT');
		$pdfModel->set('filename', $this->fileName);
		$pdfModel->set('metatags_status', 1);
		$pdfModel->set('margin_top', 15);
		$pdfModel->set('margin_bottom', 15);
		$pdfModel->set('margin_left', 15);
		$pdfModel->set('margin_right', 15);
		$pdfModel->set('header_height', 10);
		$pdfModel->set('footer_height', 10);
		$pdfModel->set('visibility', 'PLL_LISTVIEW,PLL_DETAILVIEW');
		$pdfModel->set('header_content', 'Test Header');
		$pdfModel->set('body_content', 'Test Body');
		$pdfModel->set('footer_content', 'Test Footer');
		$pdfModel->set('watermark_type', 0);
		$pdfModel->set('watermark_text', '');
		$pdfModel->set('watermark_size', 0);
		$pdfModel->set('watermark_angle', 0);
		$pdfModel->set('watermark_image', '');
		$pdfModel->set('template_members', '');
		\Settings_PDF_Record_Model::save($pdfModel, 'import');
		$this->assertSame((int) (new \App\Db\Query())->select(['pdfid'])
			->from('a_#__pdf')
			->where(['module_name' => self::MODULE_NAME, 'filename' => $this->fileName, 'primary_name' => 'test'])
			->scalar(), (int) $pdfModel->get('pdfid'), 'Not created template');
		self::$pdfModel = $pdfModel;
	}

	/**
	 * Testing PDF generation.
	 */
	public function testGenerate()
	{
		$pathToFile = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'cache/pdf/' . $this->fileName;
		\Vtiger_PDF_Model::exportToPdf(\Tests\Base\C_RecordActions::createAccountRecord()->getId(), self::$pdfModel->get('pdfid'), $pathToFile, 'F');
		$this->assertFileExists($pathToFile);
	}

	/**
	 * Testing removing template.
	 */
	public function testRemoveTemplate()
	{
		\Settings_PDF_Record_Model::delete(self::$pdfModel);
		$this->assertFalse((new \App\Db\Query())->select(['pdfid'])
			->from('a_#__pdf')
			->where(['module_name' => self::MODULE_NAME, 'filename' => $this->fileName, 'primary_name' => 'test'])
			->exists(\App\Db::getInstance('admin')), 'Not removed template');
		$pathToFile = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'cache/pdf/' . $this->fileName;
		if (\file_exists($pathToFile)) {
			\unlink($pathToFile);
		}
	}
}
