<?php

/**
 * QuickCreateView model.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class Calendar_QuickCreateView_Model.
 */
class Calendar_QuickCreateView_Model extends Vtiger_QuickCreateView_Model
{
	/** {@inheritdoc} */
	public function getLinks(array $linkParams)
	{
		$links = parent::getLinks($linkParams);
		$links['QUICKCREATE_VIEW_HEADER'][] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'QUICKCREATE_VIEW_HEADER',
			'linkhint' => 'LBL_MARK_AS_HELD',
			'linkicon' => 'far fa-square fa-lg mr-1 c-btn-checkbox--unchecked',
			'linksecondicon' => 'far fa-check-square fa-lg mr-1 c-btn-checkbox--checked',
			'showLabel' => 1,
			'linkdata' => ['js' => 'click', 'toggle' => 'buttons'],
			'linkclass' => 'c-btn-checkbox c-btn-outline-done js-btn--mark-as-completed btn-group-toggle'
		]);
		return $links;
	}
}
