<?php
/**
 * Abstract modal controller for administration panel file.
 *
 * @package Controller
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Controller\Traits;

/**
 * Record settings basic controller trait.
 */
trait RecordSettings
{
	/**
	 * Record ID.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Record name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('url');
	}

	/**
	 * Get record links.
	 *
	 * @return Vtiger_Link_Model[]
	 */
	public function getRecordLinks(): array
	{
		$links = [];
		foreach ([
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'BTN_RECORD_EDIT',
				'linkdata' => ['url' => $this->getEditViewUrl()],
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn btn-sm btn-primary js-edit-record-modal',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => "javascript:Settings_Vtiger_List_Js.deleteById('{$this->getId()}')",
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn btn-sm btn-danger text-white',
			],
		] as $recordLink) {
			$links[] = \Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Function to delete the current record model.
	 *
	 * @return int
	 */
	public function delete(): int
	{
		return \App\Db::getInstance()->createCommand()
			->delete($this->getModule()->baseTable, ['id' => $this->getId()])
			->execute();
	}

	/**
	 * Sets data from request.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function setDataFromRequest(\App\Request $request): void
	{
		foreach ($this->getModule()->getFormFields() as $fieldName => $fieldInfo) {
			if ($request->has($fieldName)) {
				$value = $request->getByType($fieldName, $fieldInfo['purifyType']);
				$fieldModel = $this->getFieldInstanceByName($fieldName)->getUITypeModel();
				$fieldModel->validate($value, true);
				$this->set($fieldName, $fieldModel->getDBValue($value));
			}
		}
	}
}
