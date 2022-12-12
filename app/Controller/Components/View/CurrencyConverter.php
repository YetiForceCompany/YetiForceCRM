<?php
/**
 * Currency converter file.
 *
 * @package   Controller
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Controller\Components\View;

/**
 * Currency converter class.
 */
class CurrencyConverter extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_CURRENCY_CONVERTER';
	/** {@inheritdoc} */
	public $modalSize = 'modal-md';
	/** {@inheritdoc} */
	public $modalIcon = 'adminIcon-currencies';
	/** {@inheritdoc} */
	public $successBtn = 'LBL_OK';

	/** {@inheritdoc} */
	public function checkPermission(\App\Request $request)
	{
		return true;
	}

	/** {@inheritdoc} */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$currencyParam = $request->has('currencyParam') ? $request->getArray('currencyParam') : [];
		$amount = $request->has('amount') ? $request->getByType('amount', \App\Purifier::NUMBER) : 0;
		$currencyId = $request->getInteger('currencyId', 0);
		$currencies = array_map(fn ($currency) => array_merge($currency, \vtlib\Functions::getConversionRateInfo($currency['id']), $currencyParam[$currency['id']] ?? []), \App\Fields\Currency::getAll(true));

		if (!$currencyId || !isset($currencies[$currencyId])) {
			$currencyId = key($currencies);
		}
		$viewer->assign('AMOUNT', $amount);
		$viewer->assign('CURRENCY_ID', $currencyId);
		$viewer->assign('CURRENCIES', $currencies);
		$viewer->assign('CURRENCY_BASE', \App\Fields\Currency::getDefault());

		$viewer->view('CurrencyConverter.tpl', $request->getModule());
	}

	/** {@inheritdoc} */
	public function getModalScripts(\App\Request $request)
	{
		return array_merge(parent::getModalScripts($request), $this->checkAndConvertJsScripts(['components.CurrencyConverter']));
	}
}
