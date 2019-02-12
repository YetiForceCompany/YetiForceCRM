<?php

namespace App\Db;

/**
 * Query represents a SELECT SQL statement in a way that is independent of DBMS.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Query extends \yii\db\Query
{
	/**
	 * Creates a DB command that can be used to execute this query.
	 *
	 * @param Connection $db the database connection used to generate the SQL statement.
	 *                       If this parameter is not given, the `db` application component will be used
	 *
	 * @return Command the created DB command instance
	 */
	public function createCommand($db = null)
	{
		if ($db === null) {
			$db = \App\Db::getInstance();
		}
		list($sql, $params) = $db->getQueryBuilder()->build($this);

		return $db->createCommand($sql, $params);
	}

	/**
	 * Starts a batch query.
	 *
	 * A batch query supports fetching data in batches, which can keep the memory usage under a limit.
	 * This method will return a [[BatchQueryResult]] object which implements the [[\Iterator]] interface
	 * and can be traversed to retrieve the data in batches.
	 *
	 * @param int        $batchSize the number of records to be fetched in each batch
	 * @param Connection $db        the database connection. If not set, the "db" application component will be used
	 *
	 * @return BatchQueryResult the batch query result. It implements the [[\Iterator]] interface
	 *                          and can be traversed to retrieve the data in batches
	 */
	public function batch($batchSize = 100, $db = null)
	{
		if ($db === null) {
			$db = \App\Db::getInstance();
		}
		return \Yii::createObject([
			'class' => \yii\db\BatchQueryResult::class,
			'query' => $this,
			'batchSize' => $batchSize,
			'db' => $db,
			'each' => false,
		]);
	}

	/**
	 * Starts a batch query and retrieves data row by row.
	 * This method is similar to [[batch()]] except that in each iteration of the result,
	 * only one row of data is returned. For example,.
	 *
	 * @param int        $batchSize the number of records to be fetched in each batch
	 * @param Connection $db        the database connection. If not set, the "db" application component will be used
	 *
	 * @return BatchQueryResult the batch query result. It implements the [[\Iterator]] interface
	 *                          and can be traversed to retrieve the data in batches
	 */
	public function each($batchSize = 100, $db = null)
	{
		if ($db === null) {
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
	 * @param Connection $db the database connection used to generate the SQL statement.
	 *                       If this parameter is not given, the `db` application component will be used
	 *
	 * @return array|bool the first row (in terms of an array) of the query result. False is returned if the query
	 *                    results in nothing
	 */
	public function one($db = null)
	{
		return $this->limit(1)->createCommand($db)->queryOne();
	}

	/**
	 * Returns the query result as a scalar value.
	 * The value returned will be the first column in the first row of the query results.
	 *
	 * @param Connection $db the database connection used to generate the SQL statement.
	 *                       If this parameter is not given, the `db` application component will be used
	 *
	 * @return string|null|false the value of the first column in the first row of the query result.
	 *                           False is returned if the query result is empty
	 */
	public function scalar($db = null)
	{
		return $this->limit(1)->createCommand($db)->queryScalar();
	}
}
