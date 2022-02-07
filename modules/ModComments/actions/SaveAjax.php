<?php
/**
 * ModComments save ajax action file.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * ModComments save ajax action class.
 */
class ModComments_SaveAjax_Action extends Vtiger_SaveAjax_Action
{
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
