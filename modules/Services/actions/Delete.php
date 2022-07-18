<?php

/**
 * Delete service record action file.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Serives delete record action class.
 */
class Services_Delete_Action extends Products_Delete_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$responseMessage = 'LBL_RECORD_EXISTS_IN_INV_MODULE';
		$recordWasDeleted = false;
		$deleteConfirmed = $request->has('deleteConfirmed') ? true : false;
		$moduleName = $request->getModule();
		$sourceView = $request->getByType('sourceView', App\Purifier::STANDARD);
		$inventoryDeleteModel = new Vtiger_InventoryDelete_Model($moduleName, $this->record->getId());
		$recordsWhereInvIsSet = $inventoryDeleteModel->getRecordsWhereRecordIsSet();
		if (!$recordsWhereInvIsSet || $deleteConfirmed) {
			$responseMessage = 'LBL_RECORD_HAS_BEEN_DELETED';
			$this->record->delete();
			$recordWasDeleted = true;
		}
		$responseResult = '';
		if (!$recordWasDeleted) {
			$secondConfirmContent = \App\Language::translate('LBL_DELETE_INVENTORY_CONFIRMATION_DESC', $moduleName) . '<br>' . $inventoryDeleteModel->getRelatedRecordsLabels();
			$confirmBoxParams = [
				'title' => '<span class="fas fa-eraser"></span><span class="ml-1">' . \App\Language::translate('LBL_DELETE_RECORD_COMPLETELY_CONFIRMATION', $moduleName) . '</span>',
				'icon' => false,
				'url' => $this->record->getDeleteUrl() . "&deleteConfirmed=1&sourceView=$sourceView",
				'text' => $secondConfirmContent,
			];
			$responseResult = ['anotherConfirm' => true, 'confirmBoxParams' => $confirmBoxParams];
		} else {
			if ('List' === $sourceView) {
				$responseResult = ['notify' => ['type' => 'success', 'text' => \App\Language::translate($responseMessage)]];
			} else {
				$responseResult = [$this->record->getModule()->getListViewUrl()];
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($responseResult);
		$response->emit();
	}
}
