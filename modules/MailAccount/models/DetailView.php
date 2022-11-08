<?php

/**
 * Mail account detail view model file.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Mail account detail view model class.
 */
class MailAccount_DetailView_Model extends Vtiger_DetailView_Model
{
	/** {@inheritdoc} */
	public function getDetailViewLinks(array $linkParams): array
	{
		$recordModel = $this->getRecord();
		$mailServer = \App\Mail\Server::getInstanceById((int) $recordModel->get('mail_server_id'));
		$linkModelList = parent::getDetailViewLinks($linkParams);

		if (!$recordModel->isReadOnly() && $recordModel->isEditable() && $mailServer && $mailServer->isViewable() && $mailServer->isOAuth() && $mailServer->getRedirectUri()) {
			$linkModelList['DETAIL_VIEW_ADDITIONAL'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'DETAIL_VIEW_ADDITIONAL',
				'linkdata' => ['url' => "index.php?module={$recordModel->getModuleName()}&action=OAuth&record={$recordModel->getId()}", 'confirm' => \App\Language::translate('LBL_OAUTH_AUTHORIZATION_DESC', $recordModel->getModuleName()), 'type' => 'href'],
				'linkicon' => \App\Integrations\OAuth::getProviderByName($mailServer->get('oauth_provider'))->getIcon(),
				'linkhint' => \App\Language::translate('LBL_OAUTH_AUTHORIZATION', $recordModel->getModuleName()),
				'linkclass' => 'btn-outline-primary btn-sm js-action-confirm',
				'showLabel' => true,
			]);
		}

		return $linkModelList;
	}
}
