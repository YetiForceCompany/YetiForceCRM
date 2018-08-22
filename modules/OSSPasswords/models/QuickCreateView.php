<?php

/**
 * QuickCreateView model.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class OSSPasswords_QuickCreateView_Model.
 */
class OSSPasswords_QuickCreateView_Model extends Vtiger_QuickCreateView_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getLinks(array $linkParams)
	{
		$links = parent::getLinks($linkParams);
		$links['QUICKCREATE_VIEW_HEADER'][] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'QUICKCREATE_VIEW_HEADER',
			'linkhint' => 'Generate Password',
			'showLabel' => 1,
			'linkdata' => ['js' => 'click'],
			'linkclass' => 'btn-success js-generatePass u-text-ellipsis mb-2 mb-md-0 col-12'
		]);
		return $links;
	}
}
