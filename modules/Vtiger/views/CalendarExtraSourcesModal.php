<?php
/**
 * Calendar extra sources modal view file.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Calendar extra sources modal view class.
 */
class Vtiger_CalendarExtraSourcesModal_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $modalIcon = 'fa-solid fa-code-branch';
	/** @var Vtiger_CalendarExtSource_Model Extra source model */
	private $source;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$privileges = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$privileges->hasModuleActionPermission($request->getModule(), 'CalendarExtraSourcesCreate')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!$request->isEmpty('id', true)) {
			$this->source = Vtiger_CalendarExtSource_Model::getInstanceById($request->getInteger('id'));
			if (!$privileges->isAdminUser() && $this->source->get('user_id') != $privileges->getId()) {
				throw new \App\Exceptions\NoPermitted('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		}
	}

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		if (!$request->has('target_module')) {
			parent::preProcessAjax($request);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('SOURCE', $this->source);
		if ($request->has('target_module')) {
			$dynamicFields = [
				'target_module' => $request->getInteger('target_module'),
				'custom_view' => $request->getInteger('custom_view'),
				'type' => $request->getInteger('type'),
				'field_label' => $request->getInteger('field_label'),
				'fieldid_a_date' => $request->getInteger('fieldid_a_date'),
				'fieldid_a_time' => $request->isEmpty('fieldid_a_time', true) ? 0 : $request->getInteger('fieldid_a_time'),
				'fieldid_b_date' => $request->isEmpty('fieldid_b_date', true) ? 0 : $request->getInteger('fieldid_b_date'),
				'fieldid_b_time' => $request->isEmpty('fieldid_b_time', true) ? 0 : $request->getInteger('fieldid_b_time'),
			];
			$isDynamic = true;
		} else {
			$dynamicFields = $this->source ? $this->source->getData() : [];
			$isDynamic = false;
		}
		$viewer->assign('IS_DYNAMIC', $isDynamic);
		$viewer->assign('DYNAMIC_FIELDS', $dynamicFields);
		$viewer->view('Calendar/ExtraSourcesModal.tpl', $request->getModule());
	}

	/** {@inheritdoc} */
	public function postProcessAjax(App\Request $request)
	{
		if (!$request->has('target_module')) {
			parent::postProcessAjax($request);
		}
	}

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		return \App\Language::translate('LBL_EXTRA_SOURCES', $request->getModule(), null, true, 'Calendar');
	}
}
