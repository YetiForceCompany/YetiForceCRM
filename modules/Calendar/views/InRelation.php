<?php
/**
 * In relation calendar view - file.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * In relation calendar view - class.
 */
class Calendar_InRelation_View extends Vtiger_RelatedList_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('TIME', $request->isEmpty('time', true) ? 'current' : $request->getByType('time'));
		return parent::process($request);
	}

	/** {@inheritdoc} */
	public function loadView()
	{
		return $this->viewer->view($this->getTemplateName(), 'Calendar', true);
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'InRelatedList.tpl';
	}
}
