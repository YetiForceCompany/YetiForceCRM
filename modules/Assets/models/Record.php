<?php

/**
 * Record Class for Assets
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Assets_Record_Model extends Vtiger_Record_Model
{

	/**
	 * Function to get EditStatus view url for the record
	 * @return <String> - Record Detail View Url
	 */
	public function getEditStatusUrl()
	{
		return 'index.php?module=' . $this->getModuleName() . '&view=EditStatus&record=' . $this->getId();
	}
}
