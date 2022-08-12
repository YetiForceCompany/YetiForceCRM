<?php
/**
 * Server access field file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Fields;

/**
 * Server access field class.
 */
class ServerAccess
{
	/**
	 * @var array Class mapping for different button places
	 */
	const BTN_CLASS = [
		'ModComments' => [0 => 'text-secondary', 1 => 'text-success'],
		'List' => [0 => 'btn-secondary', 1 => 'btn-success'],
		'RelatedList' => [0 => 'btn-secondary', 1 => 'btn-success'],
	];

	/**
	 * Get links to share the record in external services (Web service - Applications).
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 * @param string               $source
	 *
	 * @return \Vtiger_Link_Model|null
	 */
	public static function getLinks(\Vtiger_Record_Model $recordModel, string $source): ?\Vtiger_Link_Model
	{
		$fields = $recordModel->getModule()->getFieldsByType('serverAccess', true);
		$isActive = 0;
		foreach ($fields as $fieldName => $fieldModel) {
			if (!$fieldModel->isEditable()) {
				unset($fields[$fieldName]);
			}
			if ($recordModel->getValueByField($fieldName)) {
				$isActive = 1;
			}
		}
		if (empty($fields)) {
			return null;
		}
		$return = null;
		if (1 === \count($fields)) {
			$fieldName = array_key_first($fields);
			$fieldModel = $fields[$fieldName];
			$webServiceApp = self::get($fieldModel->get('fieldparams'));
			$label = \App\Language::translate($isActive ? 'BTN_DISABLE_SHARE_RECORD_IN' : 'BTN_SHARE_RECORD_IN') . ' ' . ($webServiceApp['name'] ?? '');
			$return = \Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'BTN_SERVER_ACCESS',
				'linkhint' => $label,
				'linkicon' => ($isActive ? 'yfi-share-portal-record' : 'yfi-unshare-portal-record'),
				'linkclass' => 'js-action-confirm btn-sm ' . self::BTN_CLASS[$source][$isActive],
				'dataUrl' => "index.php?module={$recordModel->getModuleName()}&action=SaveAjax&record={$recordModel->getId()}&field={$fieldName}&value=" . ($isActive ? 0 : 1),
				'linkdata' => ['add-btn-icon' => 1,	'source-view' => $source],
			]);
		} else {
			$return = \Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'BTN_SERVER_ACCESS',
				'linkicon' => ($isActive ? 'yfi-share-portal-record' : 'yfi-unshare-portal-record'),
				'linkclass' => 'btn-sm js-quick-edit-modal ' . self::BTN_CLASS[$source][$isActive],
				'linkdata' => [
					'module' => $recordModel->getModuleName(),
					'record' => $recordModel->getId(),
					'show-layout' => 'vertical',
					'modal-title' => \App\Language::translate('BTN_SERVER_ACCESS'),
					'edit-fields' => \App\Json::encode(array_keys($fields)),
				],
			]);
		}
		return $return;
	}

	/**
	 * Get web service application details by id.
	 *
	 * @param int $serverId
	 *
	 * @return array
	 */
	public static function get(int $serverId): array
	{
		if (\App\Cache::has(__METHOD__, $serverId)) {
			return \App\Cache::get(__METHOD__, $serverId);
		}
		$row = (new \App\Db\Query())->from('w_#__servers')->where(['id' => $serverId])->one(\App\Db::getInstance('webservice')) ?: [];
		\App\Cache::save(__METHOD__, $serverId, $row, \App\Cache::MEDIUM);
		return $row;
	}
}
