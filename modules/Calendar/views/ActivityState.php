<?php

/**
 * ActivityState view Class.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Calendar_ActivityState_View extends Calendar_ActivityStateModal_View
{
	/** {@inheritdoc} */
	protected function getTpl()
	{
		return 'Calendar/ActivityState.tpl';
	}

	/** {@inheritdoc} */
	public function getLinks(): array
	{
		$links = [];
		if ($this->record->isEditable() && \App\Mail::checkInternalMailClient()) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_SEND_CALENDAR',
				'linkdata' => ['url' => "index.php?module={$this->record->getModuleName()}&view=SendInvitationModal&record={$this->record->getId()}"],
				'linkicon' => 'yfi-send-invitation',
				'linkclass' => 'js-show-modal mt-1 mr-1',
			]);
		}
		return $links;
	}
}
