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
		$moduleModel = \Settings_CustomView_Module_Model::getInstance('Settings:CustomView');
		$recordModels = $moduleModel->getCustomViews(\App\Module::getModuleId('Leads'));
		$recordModel = \array_pop($recordModels);
		$this->assertNotEmpty($recordModel, 'Leads module should contain view');
		$this->assertInternalType('array', $recordModels, 'Custom views list should be array type');
		if ($recordModel) {
			$this->assertInternalType('array', $moduleModel->getFilterPermissionsView($recordModel['cvid'], 'default'), 'Custom view permissions list(default) should be array type');
			$this->assertInternalType('array', $moduleModel->getFilterPermissionsView($recordModel['cvid'], 'featured'), 'Custom view permissions list(featured) should be array type');
		}

		$supportedModules = \Settings_CustomView_Module_Model::getSupportedModules();
		$this->assertNotEmpty($supportedModules, 'System should have any custom view supported modules');
		$this->assertSame($supportedModules[\App\Module::getModuleId('Leads')], 'Leads', 'Module mapping mismatch');
	}
}
