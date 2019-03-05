<?php

/**
 * Response file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Response class.
 */
class Response
{
	/**
	 * Error data.
	 */
	private $error;

	/**
	 * Result data.
	 */
	private $result;

	/**
	 * Environment data.
	 */
	private $env;

	/**
	 * Set error data to send.
	 *
	 * @param int         $code
	 * @param string|null $message
	 * @param string|null $trace
	 */
	public function setError(int $code, ?string $message = null, ?string $trace = null)
	{
		if ($message === null) {
			$message = $code;
		}
		$error = ['code' => $code, 'message' => $message, 'trace' => $trace];
		$this->error = $error;
	}

	/**
	 * Set the result data.
	 *
	 * @param mixed $result
	 */
	public function setResult($result)
	{
		$this->result = $result;
	}

	/**
	 * Set environment data.
	 *
	 * @param array $env
	 */
	public function setEnv(array $env)
	{
		$this->env = $env;
	}

	/**
	 * Prepare the response wrapper.
	 */
	private function getResponse()
	{
		$response = [];
		if (null !== $this->error) {
			$response['success'] = false;
			$response['error'] = $this->error;
		} else {
			$response['success'] = true;
			$response['result'] = $this->result;
		}
		if (null !== $this->env) {
			$response['env'] = $this->env;
		}
		return $response;
	}

	/**
	 * Send response to client.
	 */
	public function emit()
	{
		$charset = Config::main('default_charset', 'UTF-8');
		header("content-type: text/json; charset={$charset}");
		echo \App\Json::encode($this->getResponse());
	}
}
