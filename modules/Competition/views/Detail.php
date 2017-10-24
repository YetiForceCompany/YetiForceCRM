<?php

/**
 * Competition detail view class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Competition_Detail_View extends Vtiger_Detail_View
{

	/**
	 * {@inheritDoc}
	 */
	public function getHeaderCss(\App\Request $request)
	{
		$cssFileNames = [
			'~libraries/jquery/flot/jquery.flot.valuelabels.css',
		];
		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles($cssFileNames));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$jsFileNames = [
			'~libraries/jquery/flot/jquery.flot.min.js',
			'~libraries/jquery/flot/jquery.flot.resize.js',
			'~libraries/jquery/flot/jquery.flot.stack.min.js',
			'~libraries/jquery/flot/jquery.flot.valuelabels.min.js',
		];
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
	}
}
