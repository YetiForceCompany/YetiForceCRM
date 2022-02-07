<?php

/**
 * Returns special functions for PDF Settings.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_PDF_Watermark_Action extends Settings_Vtiger_Index_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('delete');
		$this->exposeMethod('upload');
	}

	/**
	 * Delete watermark.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \yii\db\Exception
	 */
	public function delete(App\Request $request)
	{
		$recordId = $request->getInteger('id');
		$pdfModel = Vtiger_PDF_Model::getInstanceById($recordId);
		$output = Settings_PDF_Record_Model::deleteWatermark($pdfModel);
		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}
}
