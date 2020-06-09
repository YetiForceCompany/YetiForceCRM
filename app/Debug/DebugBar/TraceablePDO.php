<?php
/**
 * A PDO proxy which traces statements.
 *
 * @package   Debug
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Debug\DebugBar;

use PDO;

/**
 * A PDO proxy which traces statements.
 */
class TraceablePDO extends \DebugBar\DataCollector\PDO\TraceablePDO
{
	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
		$this->pdo->setAttribute(PDO::ATTR_STATEMENT_CLASS, [TraceablePDOStatement::class, [$this]]);
	}
}
