<?php namespace App\Db;

/**
 * Command represents a SQL statement to be executed against a database.
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
}
