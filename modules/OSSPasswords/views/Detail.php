<?php

/**
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class OSSPasswords_Detail_View extends Vtiger_Detail_View
{
	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(\App\Request $request)
	{
		return array_merge($this->checkAndConvertJsScripts([
			'modules.OSSPasswords.resources.gen_pass',
			'libraries.clipboard.dist.clipboard',
			'modules.OSSPasswords.resources.zClipDetailView',
		]), parent::getFooterScripts($request));
	}

	/**
	 * {@inheritdoc}
	 */
	public function isAjaxEnabled($recordModel)
	{
		return false;
	}
}
