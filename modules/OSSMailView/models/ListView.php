<?php

/**
 * OSSMailView ListView model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMailView_ListView_Model extends Vtiger_ListView_Model
{

	public function getBasicLinks()
	{
		$basicLinks = [];
		$moduleModel = $this->getModule();
		$createPermission = \App\Privilege::isPermitted($moduleModel->getName(), 'CreateView');
		if ($createPermission && AppConfig::main('isActiveSendingMails') && \App\Privilege::isPermitted('OSSMail')) {
			$basicLinks[] = array(
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'LBL_CREATEMAIL',
				'linkurl' => "javascript:window.location='index.php?module=OSSMail&view=Compose'",
				'linkclass' => 'modCT_' . $moduleModel->getName(),
				'linkicon' => 'glyphicon glyphicon-plus',
				'showLabel' => 1,
			);
		}
		return $basicLinks;
	}

	public function getListViewMassActions($linkParams)
	{
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleModel = $this->getModule();
		$massActionLinks = [];

		if ($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'MassDelete')) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_DELETE',
				'linkurl' => 'javascript:Vtiger_List_Js.massDeleteRecords("index.php?module=' . $moduleModel->get('name') . '&action=MassDelete");',
				'linkicon' => ''
			);
		}

		if ($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'EditView')) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_BindMails',
				'linkurl' => 'javascript:OSSMailView_List_Js.bindMails("index.php?module=' . $moduleModel->get('name') . '&action=BindMails")',
				'linkicon' => ''
			);
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_ChangeType',
				'linkurl' => 'javascript:OSSMailView_List_Js.triggerChangeType("index.php?module=' . $moduleModel->get('name') . '&view=ChangeType")',
				'linkicon' => ''
			);
		}
		foreach ($massActionLinks as $massActionLink) {
			$links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}
		return $links;
	}
}
