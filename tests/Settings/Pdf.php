<?php

/**
 * Pdf test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class Pdf extends \Tests\Base
{
	/**
	 * Module name.
	 */
	const MODULE_NAME = 'Accounts';

	/**
	 * File name.
	 */
	const FILE_NAME = 'test.pdf';

	/**
	 * Temporary model.
	 *
	 * @var Settings_PDF_Record_Model
	 */
	private static $pdfModel;

	/**
	 * Testing creating template.
	 */
	public function testCreateTemplate()
	{
		$pdfModel = Settings_PDF_Record_Model::getCleanInstance(self::MODULE_NAME);
		$pdfModel->set('module_name', self::MODULE_NAME);
		$pdfModel->set('status', 1);
		$pdfModel->set('primary_name', 'test');
		$pdfModel->set('page_format', 'A4');
		$pdfModel->set('language', 'pl_pl');
		$pdfModel->set('page_orientation', 'PLL_PORTRAIT');
		$pdfModel->set('filename', self::FILE_NAME);
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
		Settings_PDF_Record_Model::save($pdfModel, 'import');
		$this->assertSame((int) (new \App\Db\Query())->select(['pdfid'])
			->from('a_#__pdf')
			->where(['module_name' => self::MODULE_NAME, 'filename' => self::FILE_NAME, 'primary_name' => 'test'])
			->scalar(App\Db::getInstance('admin')), (int) $pdfModel->get('pdfid'), 'Not created template');
		self::$pdfModel = $pdfModel;
	}

	/**
	 * Testing PDF generation.
	 */
	public function testGenerate()
	{
		$pathToFile = 'cache/pdf/' . self::FILE_NAME;
		Vtiger_PDF_Model::exportToPdf(ACCOUNT_ID, self::MODULE_NAME, self::$pdfModel->get('pdfid'), $pathToFile, 'F');
		$this->assertFileExists($pathToFile);
	}

	/**
	 * Testing removing template.
	 */
	public function testRemoveTemplate()
	{
		Settings_PDF_Record_Model::delete(self::$pdfModel);
		$this->assertFalse((new \App\Db\Query())->select(['pdfid'])
			->from('a_#__pdf')
			->where(['module_name' => self::MODULE_NAME, 'filename' => self::FILE_NAME, 'primary_name' => 'test'])
			->exists(App\Db::getInstance('admin')), 'Not removed template');
		$pathToFile = 'cache/pdf/' . self::FILE_NAME;
		if (file_exists($pathToFile)) {
			unlink($pathToFile);
		}
	}
}
