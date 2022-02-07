<?php

/**
 * UIType Reference Field Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class ModComments_Reference_UIType extends Vtiger_Reference_UIType
{
	/** {@inheritdoc} */
	public function setValueFromRequest(App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$fieldName = $this->getFieldModel()->getFieldName();
		if (!$requestFieldName) {
			$requestFieldName = $fieldName;
		}
		parent::setValueFromRequest($request, $recordModel, $requestFieldName);
		if ('parent_comments' === $fieldName && ($parentId = $request->getInteger('parent_comments'))) {
			$parentModel = Vtiger_Record_Model::getInstanceById($parentId);
			if (!empty($parentModel->get('parents'))) {
				$parents = $parentModel->get('parents') . '::' . $parentModel->get('modcommentsid');
			} else {
				$parents = $parentModel->get('modcommentsid');
			}
			$recordModel->set('parents', $parents);
		}
	}
}
