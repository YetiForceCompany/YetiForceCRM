<?php

/**
 * @package YetiForce.Webservice
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Wojciech Brüggemann <w.bruggemann@yetiforce.com>
 */
class Settings_Countries_Record_Model
{

	public static function getAll()
	{
		$query = (new \App\Db\Query())->from('u_#__countries')->orderBy('sortorderid');
		return $query->createCommand()->queryAll();
	}
}
