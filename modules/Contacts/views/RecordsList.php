<?php

/**
 * Contacts records list view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Contacts_RecordsList_View extends Vtiger_RecordsList_View
{
	/**
	 * {@inheritdoc}
	 */
	public function initializeContent(\App\Request $request)
	{
		$sourceModule = $request->getByType('src_module', 2);
		if (!$request->has('related_parent_id') && in_array($sourceModule, ['HelpDesk', 'Project', 'SSalesProcesses']) && !$request->isEmpty('src_record') && \App\Record::isExists($request->getInteger('src_record'))) {
			$sourceRecord = $request->getInteger('src_record');
			$filterField = ['HelpDesk' => 'parent_id', 'Project' => 'linktoaccountscontacts', 'OSSPasswords' => 'related_to'];
			$relId = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule)->get($filterField[$sourceModule]);
			if ($relId && \App\Record::getType($relId) === 'Accounts') {
				$request->set('related_parent_module', 'Accounts');
				$request->set('related_parent_id', $relId);
				$request->set('showSwitch', true);
			}
		}
		parent::initializeContent($request);
	}
}
