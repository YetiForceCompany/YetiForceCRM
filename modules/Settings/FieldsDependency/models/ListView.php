<?php

/**
 *  Settings fields dependency list view model file.
 *
 * @package   Settings.Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

/**
 * Settings fields dependency list view model class.
 */
class Settings_FieldsDependency_ListView_Model extends Settings_Vtiger_ListView_Model
{
	public function getBasicListQuery()
	{
		$query = parent::getBasicListQuery();
		if ($sourceModule = $this->get('sourceModule')) {
			$query->where(['tabid' => $sourceModule]);
		}
		return $query;
	}
}
