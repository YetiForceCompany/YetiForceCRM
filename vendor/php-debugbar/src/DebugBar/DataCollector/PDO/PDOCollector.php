<?php

namespace DebugBar\DataCollector\PDO;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use DebugBar\DataCollector\TimeDataCollector;

/**
 * Collects data about SQL statements executed with PDO.
 */
class PDOCollector extends DataCollector implements Renderable, AssetProvider
{
	protected $connections = [];
	protected $timeCollector;
	protected $renderSqlWithParams = false;
	protected $sqlQuotationChar = '<>';
	public $connectType = 'master';

	/**
	 * @param TraceablePDO      $pdo
	 * @param TimeDataCollector $timeCollector
	 */
	public function __construct(TraceablePDO $pdo = null, TimeDataCollector $timeCollector = null, $dbName = 'base')
	{
		$this->timeCollector = $timeCollector;
		if ($pdo !== null) {
			$this->addConnection($pdo, $dbName);
		}
	}

	/**
	 * Renders the SQL of traced statements with params embeded.
	 *
	 * @param bool $enabled
	 */
	public function setRenderSqlWithParams($enabled = true, $quotationChar = '<>')
	{
		$this->renderSqlWithParams = $enabled;
		$this->sqlQuotationChar = $quotationChar;
	}

	/**
	 * @return bool
	 */
	public function isSqlRenderedWithParams()
	{
		return $this->renderSqlWithParams;
	}

	/**
	 * @return string
	 */
	public function getSqlQuotationChar()
	{
		return $this->sqlQuotationChar;
	}

	/**
	 * Adds a new PDO instance to be collector.
	 *
	 * @param TraceablePDO $pdo
	 * @param string       $name Optional connection name
	 */
	public function addConnection(TraceablePDO $pdo, $name = null)
	{
		if ($name === null) {
			$name = spl_object_hash($pdo);
		}
		$this->connections[$name] = $pdo;
	}

	/**
	 * Returns PDO instances to be collected.
	 *
	 * @return array
	 */
	public function getConnections()
	{
		return $this->connections;
	}

	/**
	 * @return array
	 */
	public function collect()
	{
		$data = [
			'nb_statements' => 0,
			'nb_failed_statements' => 0,
			'accumulated_duration' => 0,
			'memory_usage' => 0,
			'peak_memory_usage' => 0,
			'statements' => []
		];

		foreach ($this->connections as $name => $pdo) {
			$pdodata = $this->collectPDO($pdo, $this->timeCollector);
			$data['nb_statements'] += $pdodata['nb_statements'];
			$data['nb_failed_statements'] += $pdodata['nb_failed_statements'];
			$data['accumulated_duration'] += $pdodata['accumulated_duration'];
			$data['memory_usage'] += $pdodata['memory_usage'];
			$data['peak_memory_usage'] = max($data['peak_memory_usage'], $pdodata['peak_memory_usage']);
			$data['statements'] = array_merge($data['statements'], array_map(function ($s) use ($name) {
				$s['connection'] = $name;
				return $s;
			}, $pdodata['statements']));
		}

		$data['accumulated_duration_str'] = $this->getDataFormatter()->formatDuration($data['accumulated_duration']);
		$data['memory_usage_str'] = $this->getDataFormatter()->formatBytes($data['memory_usage']);
		$data['peak_memory_usage_str'] = $this->getDataFormatter()->formatBytes($data['peak_memory_usage']);

		return $data;
	}

	/**
	 * Collects data from a single TraceablePDO instance.
	 *
	 * @param TraceablePDO      $pdo
	 * @param TimeDataCollector $timeCollector
	 *
	 * @return array
	 */
	protected function collectPDO(TraceablePDO $pdo, TimeDataCollector $timeCollector = null)
	{
		$config = \App\Db::getConfig($this->connectType);
		$dbName = $config['dbName'];
		$driverName = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
		$stmts = [];
		foreach ($pdo->getExecutedStatements() as $stmt) {
			$stmts[] = [
				'sql' => $this->renderSqlWithParams ? $stmt->getSqlWithParams($this->sqlQuotationChar) : $stmt->getSql(),
				'row_count' => $stmt->getRowCount(),
				'stmt_id' => $stmt->getPreparedId(),
				'prepared_stmt' => $stmt->getSql(),
				'params' => (object) $stmt->getParameters(),
				'duration' => $stmt->getDuration(),
				'duration_str' => $this->getDataFormatter()->formatDuration($stmt->getDuration()),
				'memory' => $stmt->getMemoryUsage(),
				'memory_str' => $this->getDataFormatter()->formatBytes($stmt->getMemoryUsage()),
				'end_memory' => $stmt->getEndMemory(),
				'end_memory_str' => $this->getDataFormatter()->formatBytes($stmt->getEndMemory()),
				'is_success' => $stmt->isSuccess(),
				'error_code' => $stmt->getErrorCode(),
				'error_message' => $stmt->getErrorMessage(),
				'backtrace' => $stmt->getBackTrace(),
				'driverName' => $driverName,
				'dbName' => $dbName,
				'connectType' => $this->connectType,
			];
			if ($timeCollector !== null) {
				$timeCollector->addMeasure($stmt->getSql(), $stmt->getStartTime(), $stmt->getEndTime());
			}
		}

		return [
			'nb_statements' => count($stmts),
			'nb_failed_statements' => count($pdo->getFailedExecutedStatements()),
			'accumulated_duration' => $pdo->getAccumulatedStatementsDuration(),
			'accumulated_duration_str' => $this->getDataFormatter()->formatDuration($pdo->getAccumulatedStatementsDuration()),
			'memory_usage' => $pdo->getMemoryUsage(),
			'memory_usage_str' => $this->getDataFormatter()->formatBytes($pdo->getPeakMemoryUsage()),
			'peak_memory_usage' => $pdo->getPeakMemoryUsage(),
			'peak_memory_usage_str' => $this->getDataFormatter()->formatBytes($pdo->getPeakMemoryUsage()),
			'statements' => $stmts
		];
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'pdo';
	}

	/**
	 * @return array
	 */
	public function getWidgets()
	{
		return [
			'database' => [
				'icon' => 'inbox',
				'widget' => 'PhpDebugBar.Widgets.SQLQueriesWidget',
				'map' => 'pdo',
				'default' => '[]'
			],
			'database:badge' => [
				'map' => 'pdo.nb_statements',
				'default' => 0
			]
		];
	}

	/**
	 * @return array
	 */
	public function getAssets()
	{
		return [
			'css' => 'widgets/sqlqueries/widget.css',
			'js' => 'widgets/sqlqueries/widget.js'
		];
	}
}
