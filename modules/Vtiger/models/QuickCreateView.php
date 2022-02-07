<?php

/**
 * QuickCreateView model.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class Vtiger_QuickCreateView_Model.
 */
class Vtiger_QuickCreateView_Model extends \App\Base
{
	/**
	 * Function to get the instance.
	 *
	 * @param string $moduleName
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return self
	 */
	public static function getInstance(string $moduleName)
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'QuickCreateView', $moduleName);
		$instance = new $modelClassName();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		return $instance->set('module', $moduleModel);
	}

	/**
	 * Function to get the Module Model.
	 *
	 * @return Vtiger_Module_Model instance
	 */
	public function getModule()
	{
		return $this->get('module');
	}

	/**
	 * Function to get the list of links for the module.
	 *
	 * @param array $linkParams
	 *
	 * @return Vtiger_Link_Model[] - Associate array of Link Type to List of Vtiger_Link_Model instances
	 */
	public function getLinks(array $linkParams)
	{
		$links = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), ['QUICKCREATE_VIEW_HEADER', 'EDIT_VIEW_RECORD_COLLECTOR'], $linkParams);
		$links['QUICKCREATE_VIEW_HEADER'][] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'QUICKCREATE_VIEW_HEADER',
			'linkhint' => 'LBL_GO_TO_FULL_FORM',
			'showLabel' => 1,
			'linkicon' => 'yfi yfi-full-editing-view',
			'linkdata' => ['js' => 'click', 'url' => $this->getModule()->getCreateRecordUrl()],
			'linkclass' => 'btn-light js-full-editlink fontBold u-text-ellipsis mb-2 mb-md-0 col-12'
		]);
		return $links;
	}
}
