<?php
/**
 * A traceable PDO statement to use with Traceablepdo.
 *
 * @package Log
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Debug\DebugBar;

use PDO;
use PDOException;

/**
 * A traceable PDO statement to use with Traceablepdo.
 */
class TraceablePDOStatement extends \DebugBar\DataCollector\PDO\TraceablePDOStatement
{
	/**
	 * Executes a prepared statement.
	 *
	 * @see   http://php.net/manual/en/pdostatement.execute.php
	 *
	 * @param array $input_parameters [optional] An array of values with as many elements as there
	 *                                are bound parameters in the SQL statement being executed. All values are treated as
	 *                                PDO::PARAM_STR.
	 *
	 * @throws PDOException
	 *
	 * @return bool TRUE on success or FALSE on failure.
	 */
	public function execute($input_parameters = null)
	{
		$this->boundParameters['backtrace'] = \App\Debuger::getBacktrace(4);
		$this->boundParameters['driverName'] = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
		return parent::execute($input_parameters);
	}
}
