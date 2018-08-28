<?php

/**
 * Social Media Handler Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Vtiger_SocialMedia_Handler
{
	/**
	 * EntityAfterSave handler function.
	 *
	 * @param \App\EventHandler $eventHandler
	 */
	public function entityAfterSave(\App\EventHandler $eventHandler)
	{
		\App\DebugerEx::log('entityAfterSave');
		$recordModel = $eventHandler->getRecordModel();
		if (Vtiger_SocialMedia_Model::isEnableForModule($recordModel)) {
			$socialMedia = Vtiger_SocialMedia_Model::getInstanceByRecordModel($recordModel);
			if ($recordModel->isNew()) {
				$accounts = $socialMedia->getAllSocialMediaAccount('twitter');
				foreach ($accounts as $account) {
					\App\SocialMedia\SocialMedia::addAccount($account, 'twitter');
				}
			} else {
				$columns = $socialMedia->getAllColumnName();
				foreach ($columns as $column) {
					if (empty($recordModel->getPreviousValue($column)) && !empty($recordModel->get($column))) {
						\App\DebugerEx::log($recordModel->get($column));
						\App\SocialMedia\SocialMedia::addAccount($recordModel->get($column), 'twitter');
					}
				}
			}
		}
	}

	/**
	 * EntityBeforeDelete handler function.
	 *
	 * @param \App\EventHandler $eventHandler
	 *
	 * @return bool
	 */
	public function entityBeforeDelete(App\EventHandler $eventHandler)
	{
	}
}
