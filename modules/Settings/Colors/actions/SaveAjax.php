<?php
/**
 * Basic colors action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Sławomir Kłos <s.klos@yetiforce.com>
 */

/**
 * Ajax save actions handler class
 */
class Settings_Colors_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateUserColor');
		$this->exposeMethod('removeUserColor');
		$this->exposeMethod('updateGroupColor');
		$this->exposeMethod('removeGroupColor');
		$this->exposeMethod('updateModuleColor');
		$this->exposeMethod('removeModuleColor');
		$this->exposeMethod('activeModuleColor');
		$this->exposeMethod('updatePicklistValueColor');
		$this->exposeMethod('removePicklistValueColor');
		$this->exposeMethod('addPicklistColorColumn');
	}

	/**
	 * Update user color
	 * @param \App\Request $request
	 */
	public function updateUserColor(\App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$color = $request->get('color');
		if (!$color) {
			$color = \App\Colors::getRandomColor();
		}
		\App\Colors::updateUserColor($recordId, $color);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => $color,
			'message' => App\Language::translate('LBL_SAVE_COLOR', $request->getModule(false))
		]);
		$response->emit();
	}

	/**
	 * Remove user color
	 * @param \App\Request $request
	 */
	public function removeUserColor(\App\Request $request)
	{
		\App\Colors::updateUserColor($request->getInteger('record'), '');
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => '',
			'message' => \App\Language::translate('LBL_REMOVED_COLOR', $request->getModule(false))
		]);
		$response->emit();
	}

	/**
	 * Update group color
	 * @param \App\Request $request
	 */
	public function updateGroupColor(\App\Request $request)
	{
		$color = $request->get('color');
		if (!$color) {
			$color = \App\Colors::getRandomColor();
		}
		\App\Colors::updateGroupColor($request->getInteger('record'), $color);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => $color,
			'message' => \App\Language::translate('LBL_SAVE_COLOR', $request->getModule(false))
		]);
		$response->emit();
	}

	/**
	 * Remove group color
	 * @param \App\Request $request
	 */
	public function removeGroupColor(\App\Request $request)
	{
		\App\Colors::updateGroupColor($request->getInteger('record'), '');
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => '',
			'message' => \App\Language::translate('LBL_REMOVED_COLOR', $request->getModule(false))
		]);
		$response->emit();
	}

	/**
	 * Update module color
	 * @param \App\Request $request
	 */
	public function updateModuleColor(\App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$color = $request->get('color');
		if (!$color) {
			$color = \App\Colors::getRandomColor();
		}
		\App\Colors::updateModuleColor($recordId, $color);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => $color,
			'message' => \App\Language::translate('LBL_SAVE_COLOR', $request->getModule(false))
		]);
		$response->emit();
	}

	/**
	 * Remove module color
	 * @param \App\Request $request
	 */
	public function removeModuleColor(\App\Request $request)
	{
		\App\Colors::updateModuleColor($request->getInteger('record'), '');
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => '',
			'message' => \App\Language::translate('LBL_REMOVED_COLOR', $request->getModule(false))
		]);
		$response->emit();
	}

	/**
	 * Activate/deactivate module color
	 * @param \App\Request $request
	 */
	public function activeModuleColor(\App\Request $request)
	{
		$color = \App\Colors::activeModuleColor($request->getInteger('record'), $request->get('status'), $request->get('color'));
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => $color,
			'message' => \App\Language::translate('LBL_SAVE_COLOR', $request->getModule(false))
		]);
		$response->emit();
	}

	/**
	 * Update picklist value color
	 * @param \App\Request $request
	 */
	public function updatePicklistValueColor(\App\Request $request)
	{
		$field = \Vtiger_Field_Model::getInstanceFromFieldId($request->getInteger('fieldId'));
		if (!$field || !in_array($field->getFieldDataType(), ['picklist', 'multipicklist'])) {
			throw new \App\Exceptions\AppException('LBL_FIELD_NOT_FOUND');
		}
		$color = $request->get('color');
		if (!$color) {
			$color = \App\Colors::getRandomColor();
		}
		\App\Colors::updatePicklistValueColor($request->getInteger('fieldId'), $request->getInteger('fieldValueId'), $color);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => $color,
			'message' => \App\Language::translate('LBL_SAVE_COLOR', $request->getModule(false))
		]);
		$response->emit();
	}

	/**
	 * Remove picklist value color
	 * @param \App\Request $request
	 */
	public function removePicklistValueColor(\App\Request $request)
	{
		$field = \Vtiger_Field_Model::getInstanceFromFieldId($request->getInteger('fieldId'));
		if (!$field || !in_array($field->getFieldDataType(), ['picklist', 'multipicklist'])) {
			throw new \App\Exceptions\AppException('LBL_FIELD_NOT_FOUND');
		}
		\App\Colors::updatePicklistValueColor($request->getInteger('fieldId'), $request->getInteger('fieldValueId'), '');
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => '',
			'message' => \App\Language::translate('LBL_REMOVED_COLOR', $request->getModule(false))
		]);
		$response->emit();
	}

	/**
	 * Add picklist color column in db table
	 * @param \App\Request $request
	 */
	public function addPicklistColorColumn(\App\Request $request)
	{
		$field = \Vtiger_Field_Model::getInstanceFromFieldId($request->getInteger('fieldId'));
		if (!$field || !in_array($field->getFieldDataType(), ['picklist', 'multipicklist'])) {
			throw new \App\Exceptions\AppException('LBL_FIELD_NOT_FOUND');
		}
		$fieldId = $request->getInteger('fieldId');
		\App\Colors::addPicklistColorColumn($fieldId);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => \App\Language::translate('LBL_SAVE_COLOR', $request->getModule(false))
		]);
		$response->emit();
	}
}
