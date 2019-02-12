<?php
/**
 * CustomView test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\Settings;

class CustomView extends \Tests\Base
{
	/**
	 * Testing module model.
	 */
	public function testModuleModel()
	{
		$moduleId = \App\Module::getModuleId('Leads');
		$moduleModel = \Settings_CustomView_Module_Model::getInstance('Settings:CustomView');
		$recordModels = $moduleModel->getCustomViews($moduleId);
		$this->assertInternalType('array', $recordModels, 'Custom views list should be array type');
		$this->assertNotEmpty($recordModels, 'Leads module should contain views');
		$recordModel = \array_pop($recordModels);
		$this->assertNotEmpty($recordModel, 'Leads custom view record should contain data');
		if ($recordModel) {
			$this->assertInternalType('array', $moduleModel->getFilterPermissionsView($recordModel['cvid'], 'default'), 'Custom view permissions list(default) should be array type');
			$this->assertEmpty($moduleModel->getFilterPermissionsView($recordModel['cvid'], 'default'), 'Custom view permissions list(default) should be empty');
			$moduleModel->setDefaultUsersFilterView($moduleId, $recordModel['cvid'], \App\User::getActiveAdminId(), 'add');
			$filterPermsView = $moduleModel->getFilterPermissionsView($recordModel['cvid'], 'default');
			$this->assertNotEmpty($filterPermsView, 'Custom view permissions list(default) should be not empty');
			$filterPermsFound = false;
			foreach ($filterPermsView as $val) {
				if (\in_array(\App\User::getActiveAdminId(), $val)) {
					$filterPermsFound = true;
				}
			}
			$this->assertTrue($filterPermsFound, 'Created default users filter view entry not found');
			$moduleModel->setDefaultUsersFilterView($moduleId, $recordModel['cvid'], \App\User::getActiveAdminId(), 'add');
			$moduleModel->setDefaultUsersFilterView($moduleId, $recordModel['cvid'], \App\User::getActiveAdminId(), 'remove');
			$this->assertEmpty($moduleModel->getFilterPermissionsView($recordModel['cvid'], 'default'), 'Custom view permissions list(default) should be emptied');
			$this->assertInternalType('array', $moduleModel->getFilterPermissionsView($recordModel['cvid'], 'featured'), 'Custom view permissions list(featured) should be array type');
			$this->assertEmpty($moduleModel->getFilterPermissionsView($recordModel['cvid'], 'featured'), 'Custom view permissions list(featured) should be empty');
			\CustomView_Record_Model::setFeaturedFilterView($recordModel['cvid'], \App\User::getActiveAdminId(), 'add');
			$this->assertNotEmpty($moduleModel->getFilterPermissionsView($recordModel['cvid'], 'featured'), 'Custom view permissions list(featured) should be not empty');
			\CustomView_Record_Model::setFeaturedFilterView($recordModel['cvid'], \App\User::getActiveAdminId(), 'remove');
			$this->assertEmpty($moduleModel->getFilterPermissionsView($recordModel['cvid'], 'featured'), 'Custom view permissions list(featured) should be empty');
		}
		$supportedModules = \Settings_CustomView_Module_Model::getSupportedModules();
		$this->assertNotEmpty($supportedModules, 'System should have any custom view supported modules');
		$this->assertSame($supportedModules[\App\Module::getModuleId('Leads')], 'Leads', 'Module mapping mismatch');
		$this->assertSame('index.php?module=CustomView&view=EditAjax&source_module=Leads&record=115', $moduleModel->getUrlToEdit('Leads', 115), 'Generated edit url mismatch');
		$this->assertSame('index.php?module=CustomView&view=EditAjax&source_module=Leads', $moduleModel->getCreateFilterUrl('Leads'), 'Generated create filter url mismatch');
		$this->assertSame('index.php?module=CustomView&parent=Settings&view=FilterPermissions&type=default&sourceModule=Leads&cvid=115&isDefault=1', $moduleModel->getUrlDefaultUsers('Leads', 115, 1), 'Generated default users url mismatch');
		$this->assertSame('index.php?module=CustomView&parent=Settings&view=FilterPermissions&type=featured&sourceModule=Leads&cvid=115', $moduleModel->getFeaturedFilterUrl('Leads', 115), 'Generated featured filter url mismatch');
		$this->assertSame('index.php?module=CustomView&parent=Settings&view=Sorting&type=featured&sourceModule=Leads&cvid=115', $moduleModel->getSortingFilterUrl('Leads', 115), 'Generated sorting filter url mismatch');
		$leadsDefCvid = (new \App\Db\Query())->select(['cvid'])->from('vtiger_customview')->where(['entitytype' => 'Leads', 'setdefault' => 1])->scalar();
		$this->assertTrue(\Settings_CustomView_Module_Model::updateField(['cvid' => $recordModel['cvid'], 'name' => 'setdefault', 'mod' => 'Leads', 'value' => 1]), 'Update CustomView record field failed');
		$this->assertSame($recordModel['cvid'], (new \App\Db\Query())->select(['cvid'])->from('vtiger_customview')->where(['entitytype' => 'Leads', 'setdefault' => 1])->scalar(), 'Default cvid for module Leads mismatch');
		$this->assertTrue(\Settings_CustomView_Module_Model::updateField(['cvid' => $leadsDefCvid, 'name' => 'setdefault', 'mod' => 'Leads', 'value' => 1]), 'Restore default cvid for module Leads failed');
		$newCustomViewModel = \CustomView_Record_Model::getCleanInstance();
		$newCustomViewModel->setModule('Leads');
		$customViewData = [
			'viewname' => 'DeleteTest',
			'setdefault' => 0,
			'setmetrics' => 0,
			'status' => 0,
			'featured' => 0,
			'color' => '',
			'description' => 'Record delete test',
			'columnslist' => \CustomView_Record_Model::getInstanceById($leadsDefCvid)->getSelectedFields()
		];
		$newCustomViewModel->setData($customViewData);
		$newCustomViewModel->save();
		$newCvid = $newCustomViewModel->getId();
		$this->assertNotNull($newCvid, 'Expected cvid');
		\Settings_CustomView_Module_Model::delete($newCvid);
		$this->assertEmpty((new \App\Db\Query())->select(['cvid'])->from('vtiger_customview')->where(['cvid' => $newCvid])->scalar(), 'New CustomView should be removed');
	}
}
