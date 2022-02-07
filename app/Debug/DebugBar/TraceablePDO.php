<?php
/**
 * A PDO proxy which traces statements.
 *
 * @package Log
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
