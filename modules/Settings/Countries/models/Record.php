<?php

/**
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Wojciech Bruggemann <w.bruggemann@yetiforce.com>
 */
class Settings_Countries_Record_Model
{
	/**
	 * Get all records with all fields from countries.
	 *
	 * @return array all rows of the query result. Each array element is an array representing a row of data
	 */
	public static function getAll()
	{
		$query = (new \App\Db\Query())->from('u_#__countries')->orderBy('sortorderid');

		return $query->createCommand()->queryAll();
	}
}
