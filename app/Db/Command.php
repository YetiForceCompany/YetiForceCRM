<?php

namespace App\Db;

/**
 * Command represents a SQL statement to be executed against a database.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Command extends \yii\db\Command
{
	/**
	 * Executes the SQL statement and returns query result.
	 * This method is for executing a SQL query that returns result set, such as `SELECT`.
	 *
	 * @throws Exception execution failed
	 *
	 * @return DataReader the reader object for fetching the query result
	 */
	public function query()
	{
		return $this->queryInternal('');
	}

	/**
	 * Executes the SQL statement and returns ALL rows at once.
	 *
	 * @param int $fetchMode the result fetch mode. Please refer to [PHP manual](http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
	 *                       for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used
	 *
	 * @throws Exception execution failed
	 *
	 * @return array all rows of the query result. Each array element is an array representing a row of data.
	 *               An empty array is returned if the query results in nothing
	 */
	public function queryAllByGroup($type = 0)
	{
		switch ($type) {
			case 0:
				return $this->queryInternal('fetchAll', \PDO::FETCH_KEY_PAIR);
			case 1:
				return $this->queryInternal('fetchAll', \PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
			case 2:
				return $this->queryInternal('fetchAll', \PDO::FETCH_GROUP | \PDO::FETCH_COLUMN | \PDO::FETCH_ASSOC);
			default:
				break;
		}
	}
}
