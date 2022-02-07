<?php
/**
 * Users detail view class.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

/**
 * Class Users_Detail_View.
 */
class Users_Detail_View extends Vtiger_Detail_View
{
	/** {@inheritdoc} */
	public function isAjaxEnabled($recordModel)
	{
		return false;
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$recordModel = !empty($this->record) ? $this->record->getRecord() : Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		$dayStartPicklistValues = $recordModel->getDayStartsPicklistValues();
		$viewer = $this->getViewer($request);
		$viewer->assign('DAY_STARTS', \App\Json::encode($dayStartPicklistValues));
		return parent::process($request);
	}
}
