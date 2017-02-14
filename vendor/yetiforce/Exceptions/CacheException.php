<?php
namespace App\Exceptions;

/**
 * CacheException represents a database error.
 * @package YetiForce.Exception
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class CacheException extends \Exception
{

	public function __construct($message = '', $code = 0, \Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
