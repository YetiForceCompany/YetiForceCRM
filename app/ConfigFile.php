<?php
/**
 * Changes configuration in files.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Class to change configuration in file.
 */
class ConfigFile extends Base
{
	/** @var string Type of configuration file */
	private $type;
	/** @var string|null Module name */
	private $moduleName;
	/** @var string Path to the configuration file */
	private $path;
	/** @var string Path to the configuration template file */
	private $templatePath;
	/** @var array Template data */
	private $template = [];
	/** @var array Types of configuration files */
	private $types = [
		'main',
		'performance',
		'module',
		'api',
		'debug',
		'developer',
		'security',
//		'securityKeys',
		'relation',
		'sounds',
		'search',
	];
	/** @var string License */
	private $license = 'Configuration file.
This file is auto-generated.

@package Config

@copyright YetiForce Sp. z o.o
@license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
';

	/**
	 * ConfigFile constructor.
	 *
	 * @param string      $type
	 * @param string|null $moduleName
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function __construct(string $type, ?string $moduleName = '')
	{
		parent::__construct();
		if (!in_array($type, $this->types)) {
			throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $type, 406);
		}
		$this->type = $type;
		if ($moduleName) {
			$this->moduleName = $moduleName;
		}
		if ($this->type === 'module') {
			$this->templatePath = 'modules' . \DIRECTORY_SEPARATOR . $moduleName . \DIRECTORY_SEPARATOR . 'ConfigTemplate.php';
			$this->path = 'config' . \DIRECTORY_SEPARATOR . 'Modules' . \DIRECTORY_SEPARATOR . "{$moduleName}.php";
		} else {
			$this->templatePath = 'config' . \DIRECTORY_SEPARATOR . 'ConfigTemplates.php';
			$this->path = 'config' . \DIRECTORY_SEPARATOR . \ucfirst($this->type) . '.php';
		}
		$this->loadTemplate();
	}

	/**
	 * Load configuration template.
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	private function loadTemplate()
	{
		if (!\file_exists($this->templatePath)) {
			throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $this->templatePath, 406);
		}
		$data = require "{$this->templatePath}";
		if ($this->type !== 'module') {
			if (!isset($data[$this->type])) {
				throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $this->type, 406);
			}
			$data = $data[$this->type];
		}
		$this->template = $data;
	}

	/**
	 * Gets class name.
	 *
	 * @return string
	 */
	private function getClassName()
	{
		$className = 'Config\\';
		if ($this->type === 'module') {
			$className .= 'Modules\\' . $this->moduleName;
		} else {
			$className .= ucfirst($this->type);
		}
		return $className;
	}

	/**
	 * Function for data validation.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\IllegalValue
	 *
	 * @return bool
	 */
	public function validate(string $key, $value)
	{
		if (!isset($this->template[$key])) {
			throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $key, 406);
		} elseif (!isset($this->template[$key]['validation']) || !\is_callable($this->template[$key]['validation'])) {
			throw new Exceptions\AppException("ERR_CONTENTS_VARIABLE_CANT_CALLED_FUNCTION ||{$this->template[$key]['validation']}", 406);
		}
		return true === \call_user_func_array($this->template[$key]['validation'], [$value]);
	}

	/**
	 * Function for data sanitize.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\IllegalValue
	 *
	 * @return mixed
	 */
	public function sanitize(string $key, $value)
	{
		if (!isset($this->template[$key])) {
			throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $key, 406);
		} elseif (isset($this->template[$key]['sanitization'])) {
			if (!\is_callable($this->template[$key]['sanitization'])) {
				throw new Exceptions\AppException("ERR_CONTENTS_VARIABLE_CANT_CALLED_FUNCTION ||{$this->template[$key]['sanitization']}", 406);
			}
			$value = \call_user_func_array($this->template[$key]['sanitization'], [$value]);
		}
		return $value;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\IllegalValue
	 *
	 * @return self
	 */
	public function set($key, $value)
	{
		if (!$this->validate($key, $value)) {
			throw new Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||{$key}||" . \var_export($value, true), 406);
		}
		return parent::set($key, $this->sanitize($key, $value));
	}

	/**
	 * Create configuration file.
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function create()
	{
		if (\array_diff_key($this->getData(), $this->template)) {
			throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
		}
		$className = $this->getClassName();
		$file = new \Nette\PhpGenerator\PhpFile();
		$file->addComment($this->license);
		$class = $file->addClass($className);
		$class->addComment('Configuration Class.');
		foreach ($this->template as $parameterName => $parameter) {
			$value = $this->has($parameterName) ? $this->get($parameterName) : Config::get($className, $parameterName, $parameter['default']);
			$class->addProperty($parameterName, $value)
				->setVisibility('public')
				->setStatic()
				->addComment($parameter['description']);
		}
		if (false === file_put_contents($this->path, $file, LOCK_EX)) {
			throw new Exceptions\AppException('ERR_CREATE_FILE_FAILURE');
		}
		if (\class_exists($className)) {
			foreach ($class->getProperties() as $name => $property) {
				if (isset($className::$$name)) {
					$className::$$name = $property->getValue();
				}
			}
		} else {
			require "{$this->path}";
		}
		Cache::clear();
		Cache::clearOpcache();
	}
}
