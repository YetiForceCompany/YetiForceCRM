<?php
namespace App\Db;

/**
 * Command represents a SQL statement to be executed against a database.
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Command extends \yii\db\Command
{

	/**
	 * Executes the SQL statement and returns query result.
	 * This method is for executing a SQL query that returns result set, such as `SELECT`.
	 * @return DataReader the reader object for fetching the query result
	 * @throws Exception execution failed
	 */
	public function query()
	{
		return $this->queryInternal('');
		try {
			
		} catch (\yii\db\Exception $e) {
			if (AppConfig::debug('SQL_DIE_ON_ERROR')) {
				
			}
		}
	}

	/**
	 * Executes the SQL statement and returns ALL rows at once.
	 * @param integer $fetchMode the result fetch mode. Please refer to [PHP manual](http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
	 * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
	 * @return array all rows of the query result. Each array element is an array representing a row of data.
	 * An empty array is returned if the query results in nothing.
	 * @throws Exception execution failed
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
		}
	}
}
