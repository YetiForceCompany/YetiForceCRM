<?php
namespace App\Debug;

use PDO;
use PDOException;
use PDOStatement;
use DebugBar\DataCollector\PDO\TracedStatement;

/**
 * A traceable PDO statement to use with Traceablepdo
 */
class DebugBarStatement extends \PDOStatement
{
	
}
