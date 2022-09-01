<?php

/**
 * Records list view file.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Records list view class.
 */
class PriceBooks_RecordsList_View extends Vtiger_RecordsList_View
{
	/**
	 * Set record list model.
	 *
	 * @param App\Request $request
	 */
	public function setRecordListModel(App\Request $request)
	{
		parent::setRecordListModel($request);
		if (!$request->isEmpty('currency_id', true)) {
			$this->recordListModel->set('currency_id', $request->getInteger('currency_id'));
		} elseif ($currencyId = $request->getArray('additionalData')['currency_id'] ?? null) {
			$this->recordListModel->set('currency_id', (int) $currencyId);
		}
	}
}
