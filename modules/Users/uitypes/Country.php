<?php
/**
 * UIType country field class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Users_Country_UIType extends Vtiger_Country_UIType{

    /**
     * @inheritdoc
     */
    public function getDetailViewTemplateName()
	{
		return 'Detail/Field/Country.tpl';
	}
}
