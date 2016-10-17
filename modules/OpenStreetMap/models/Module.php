<?php

/**
 * Module Model
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OpenStreetMap_Module_Model extends Vtiger_Module_Model
{

    /**
     * Check if module is allowed
     * @param string $moduleName
     * @return boolean
     */
    public function isAllowModules($moduleName)
    {
        return in_array($moduleName, AppConfig::module($this->getName(), 'ALLOW_MODULES'));
    }
}
