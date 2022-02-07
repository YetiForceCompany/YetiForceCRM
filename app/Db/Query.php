<?php
/**
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Db;

/**
 * Query represents a SELECT SQL statement in a way that is independent of DBMS.
 *
 * Query provides a set of methods to facilitate the specification of different clauses
 * in a SELECT statement. These methods can be chained together.
 *
 * By calling [[createCommand()]], we can get a [[Command]] instance which can be further
 * used to perform/execute the DB query against a database.
 */
class Query extends \yii\db\Query
{
	/**
	 * Creates a DB command that can be used to execute this query.
	 *
	 * @param \App\Db $db the database connection used to generate the SQL statement.
	 *                    If this parameter is not given, the `db` application component will be used.
	 *
	 * @return Command the created DB command instance.
	 */
	public function createCommand($db = null)
	{
		if (null === $db) {
			$db = \App\Db::getInstance();
		}
		[$sql, $params] = $db->getQueryBuilder()->build($this);

		return $db->createCommand($sql, $params);
	}

	/**
	 * Starts a batch query.
	 *
	 * A batch query supports fetching data in batches, which can keep the memory usage under a limit.
	 * This method will return a [[BatchQueryResult]] object which implements the [[\Iterator]] interface
	 * and can be traversed to retrieve the data in batches.
	 *
	 * @param int     $batchSize the number of records to be fetched in each batch.
	 * @param \App\Db $db        the database connection. If not set, the "db" application component will be used.
	 *
	 * @return \yii\db\BatchQueryResult the batch query result. It implements the [[\Iterator]] interface
	 *                                  and can be traversed to retrieve the data in batches.
	 */
	public function batch($batchSize = 100, $db = null)
	{
		if (null === $db) {
			$db = \App\Db::getInstance();
		}
		return \Yii::createObject([
			'class' => \yii\db\BatchQueryResult::className(),
			'query' => $this,
			'batchSize' => $batchSize,
			'db' => $db,
			'each' => false,
		]);
	}

	/**
	 * Starts a batch query and retrieves data row by row.
	 *
	 * This method is similar to [[batch()]] except that in each iteration of the result,
	 * only one row of data is returned. For example,
	 *
	 * @param int     $batchSize the number of records to be fetched in each batch.
	 * @param \App\Db $db        the database connection. If not set, the "db" application component will be used.
	 *
	 * @return \yii\db\BatchQueryResult the batch query result. It implements the [[\Iterator]] interface
	 *                                  and can be traversed to retrieve the data in batches.
	 */
	public function each($batchSize = 100, $db = null)
	{
		if (null === $db) {
			$db = \App\Db::getInstance();
		}
		return \Yii::createObject([
			'class' => \yii\db\BatchQueryResult::class,
			'query' => $this,
			'batchSize' => $batchSize,
			'db' => $db,
			'each' => true,
		]);
	}

	/**
	 * Executes the query and returns a single row of result.
	 *
	 * @param \App\Db $db the database connection used to generate the SQL statement.
	 *                    If this parameter is not given, the `db` application component will be used.
	 *
	 * @return array|bool the first row (in terms of an array) of the query result. False is returned if the query
	 *                    results in nothing.
	 */
	public function one($db = null)
	{
		return $this->limit(1)->createCommand($db)->queryOne();
	}

	/**
	 * Returns the query result as a scalar value.
	 * The value returned will be the first column in the first row of the query results.
	 *
	 * @param \App\Db $db the database connection used to generate the SQL statement.
	 *                    If this parameter is not given, the `db` application component will be used.
	 *
	 * @return string|false|null the value of the first column in the first row of the query result.
	 *                           False is returned if the query result is empty.
	 */
	public function scalar($db = null)
	{
		return $this->limit(1)->createCommand($db)->queryScalar();
	}

	/**
	 * Queries a scalar value by setting [[select]] first.
	 * Restores the value of select to make this query reusable.
	 *
	 * @param string|ExpressionInterface $selectExpression
	 * @param \App\Db|null               $db
	 *
	 * @return bool|string
	 */
	protected function queryScalar($selectExpression, $db)
	{
		if (!$this->distinct
		&& empty($this->groupBy)
		&& empty($this->having)
		&& empty($this->union)) {
			return parent::queryScalar($selectExpression, $db);
		}
		$command = (new static())
			->select([$selectExpression])
			->from(['c' => $this])
			->createCommand($db);
		$this->setCommandCache($command);
		return $command->queryScalar();
	}
}
