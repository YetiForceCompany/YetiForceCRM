<?php

/**
 * ActivityState view Class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Calendar_ActivityState_View extends Calendar_ActivityStateModal_View
{
	/**
	 * {@inheritdoc}
	 */
	protected function getTpl()
	{
		return 'Extended/ActivityState.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLinks(): array
	{
		$links = [];
		if ($this->record->isEditable() && \App\Config::main('isActiveSendingMails') && \App\Privilege::isPermitted('OSSMail') && 1 === \App\User::getCurrentUserModel()->getDetail('internal_mailer')) {
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
