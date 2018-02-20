<?php

/**
 * Issue Model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Github_Issues_Model
{
	private $valueMap;
	public static $totalCount;

	public function get($key)
	{
		return $this->valueMap->$key;
	}

	public static function getInstanceFromArray($issueArray)
	{
		$issueModel = new self();
		$issueModel->valueMap = $issueArray;

		return $issueModel;
	}
}
