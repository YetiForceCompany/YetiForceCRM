<?php

/**
 * UIType City Address Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Vtiger_CityAddress_UIType extends Vtiger_Base_UIType
{

	public function isAjaxEditable()
	{
		return false;
	}

	/**
	 * Function to get the Template name for the current UI Type Object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/CityAddress.tpl';
	}

	/**
	 * Function to get the Detailview template name for the current UI Type Object
	 * @return string - Template Name
	 */
	public function getDetailViewTemplateName()
	{
		return 'uitypes/CityAddressDetailView.tpl';
	}
}
