<?php

/**
 * OSSPasswords records list view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSPasswords_RecordsList_View extends Vtiger_RecordsList_View
{
	/**
	 * {@inheritdoc}
	 */
	public function initializeContent(App\Request $request)
	{
		parent::initializeContent($request);
		if (isset($this->listViewHeaders['password'])) {
			foreach ($this->listViewEntries as &$recordInstance) {
				$recordInstance->set('password', str_repeat('*', 10));
			}
			$viewer = $this->getViewer($request);
			$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);
		}
	}
}
