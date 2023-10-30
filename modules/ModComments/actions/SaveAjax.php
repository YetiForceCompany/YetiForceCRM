<?php
/**
 * ModComments save ajax action file.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * ModComments save ajax action class.
 */
class ModComments_SaveAjax_Action extends Vtiger_SaveAjax_Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		$parentCommentId = $request->isEmpty('parent_comments') ? 0 : $request->getInteger('parent_comments');
		if ($parentCommentId && (!\App\Record::isExists($parentCommentId, $request->getModule()) || 'Active' !== \App\Record::getState($parentCommentId))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function getRecordModelFromRequest(App\Request $request)
	{
		if ('QuickEdit' === $request->getByType('fromView')) {
			$fields = array_merge(['reasontoedit', 'commentcontent'], array_keys($this->record->getModule()->getFieldsByType('serverAccess', true)));
		} else {
			$request->set('assigned_user_id', App\User::getCurrentUserRealId());
		}
		if (!empty($fields)) {
			foreach ($this->record->getModule()->getFields() as $fieldName => $fieldModel) {
				if (!$fieldModel->isWritable()) {
					continue;
				}
				if ($request->has($fieldName) && !\in_array($fieldName, $fields)) {
					$fieldModel->set('isReadOnly', true);
				}
			}
		}
		return parent::getRecordModelFromRequest($request);
	}

	/**
	 * Add custom data to the response.
	 *
	 * @param array $result
	 *
	 * @return void
	 */
	protected function addCustomResult(array &$result): void
	{
		$result['modifiedtime']['formatToViewDate'] = \App\Fields\DateTime::formatToViewDate($this->record->get('modifiedtime'));
		$result['modifiedtime']['formatToDay'] = \App\Fields\DateTime::formatToDay($this->record->get('modifiedtime'));
	}
}
