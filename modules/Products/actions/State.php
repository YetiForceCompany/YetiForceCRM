<?php

/**
 * Change product record state action file.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Change product record state action class.
 */
class Products_State_Action extends Vtiger_State_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$responseMessage = 'LBL_RECORD_EXISTS_IN_INV_MODULE';
		$recordWasDeleted = $recordsWhereInvIsSet = false;
		$deleteConfirmed = $request->has('deleteConfirmed') ? true : false;
		$state = $request->getByType('state', \App\Purifier::STANDARD);
		$moduleName = $request->getModule();
		$sourceView = $request->getByType('sourceView', App\Purifier::STANDARD);
		if ('Active' !== $state) {
			$inventoryDeleteModel = new Vtiger_InventoryDelete_Model($moduleName, $this->record->getId());
			$recordsWhereInvIsSet = $inventoryDeleteModel->getRecordsWhereRecordIsSet();
		}

		if ('Active' === $state || !$recordsWhereInvIsSet || $deleteConfirmed) {
			$responseMessage = 'LBL_CHANGES_SAVED';
			$this->record->changeState($state);
			$recordWasDeleted = true;
		}

		$responseResult = '';
		if (!$recordWasDeleted && 'Active' !== $state) {
			$secondConfirmContent = \App\Language::translate('LBL_DELETE_INVENTORY_CONFIRMATION_DESC', $moduleName) . '<br>' .
				\App\Language::translate('LBL_RECORDS_LIMIT', $moduleName) . ' ' . $inventoryDeleteModel->recordsLimit . '<br>' .
				$inventoryDeleteModel->getRelatedRecordsLabels();
			$confirmBoxParams = [
				'title' => '<span class="fas fa-eraser"></span><span class="ml-1">' . \App\Language::translate('LBL_DELETE_RECORD_COMPLETELY_CONFIRMATION', $moduleName) . '</span>',
				'icon' => false,
				'url' => "index.php?module={$moduleName}&action=State&state={$state}&record={$this->record->getId()}&deleteConfirmed=1&sourceView={$sourceView}",
				'text' => $secondConfirmContent,
			];
			$responseResult = ['anotherConfirm' => true, 'confirmBoxParams' => $confirmBoxParams];
		} else {
			if ('List' === $sourceView) {
				$responseResult = ['notify' => ['type' => 'success', 'text' => \App\Language::translate($responseMessage)]];
			} else {
				$responseResult = [$this->record->getDetailViewUrl()];
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($responseResult);
		$response->emit();
	}
}
