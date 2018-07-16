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
		$moduleId= \App\Module::getModuleId('Leads');
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
			$this->assertNotEmpty($moduleModel->getFilterPermissionsView($recordModel['cvid'], 'default'), 'Custom view permissions list(default) should be not empty');
			$moduleModel->setDefaultUsersFilterView($moduleId, $recordModel['cvid'], \App\User::getActiveAdminId(), 'remove');
			$this->assertEmpty($moduleModel->getFilterPermissionsView($recordModel['cvid'], 'default'), 'Custom view permissions list(default) should be emptied');

			$this->assertInternalType('array', $moduleModel->getFilterPermissionsView($recordModel['cvid'], 'featured'), 'Custom view permissions list(featured) should be array type');
			$this->assertEmpty($moduleModel->getFilterPermissionsView($recordModel['cvid'], 'featured'), 'Custom view permissions list(featured) should be empty');
			$moduleModel->setFeaturedFilterView($recordModel['cvid'], \App\User::getActiveAdminId(), 'add');
			$this->assertNotEmpty($moduleModel->getFilterPermissionsView($recordModel['cvid'], 'featured'), 'Custom view permissions list(featured) should be not empty');
			$moduleModel->setFeaturedFilterView($recordModel['cvid'], \App\User::getActiveAdminId(), 'remove');
			$this->assertEmpty($moduleModel->getFilterPermissionsView($recordModel['cvid'], 'featured'), 'Custom view permissions list(featured) should be empty');
		}

		$supportedModules = \Settings_CustomView_Module_Model::getSupportedModules();
		$this->assertNotEmpty($supportedModules, 'System should have any custom view supported modules');
		$this->assertSame($supportedModules[\App\Module::getModuleId('Leads')], 'Leads', 'Module mapping mismatch');

		$this->assertSame('module=CustomView&view=EditAjax&source_module=Leads&record=115', $moduleModel->getUrlToEdit('Leads', 115), 'Generated edit url mismatch');
		$this->assertSame('index.php?module=CustomView&view=EditAjax&source_module=Leads', $moduleModel->getCreateFilterUrl('Leads'), 'Generated create filter url mismatch');
	}
}
