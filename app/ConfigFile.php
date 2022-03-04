<?php
/**
 * Changes configuration in files.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Class to change configuration in file.
 */
class ConfigFile extends Base
{
	/** Types of configuration files */
	public const TYPES = [
		'main',
		'db',
		'performance',
		'module',
		'api',
		'debug',
		'developer',
		'security',
		'securityKeys',
		'relation',
		'sounds',
		'search',
		'component',
		'layout',
	];

	/** @var string Type of configuration file */
	private $type;
	/** @var string|null Component name */
	private $component;
	/** @var string Path to the configuration file */
	private $path;
	/** @var string Path to the configuration template file */
	private $templatePath;
	/** @var array Template data */
	private $template = [];

	/** @var string License */
	private $license = 'Configuration file.
This file is auto-generated.

@package Config

@copyright YetiForce S.A.
@license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
';

	/**
	 * ConfigFile constructor.
	 *
	 * @param string      $type
	 * @param string|null $component
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function __construct(string $type, ?string $component = '')
	{
		parent::__construct();
		if (!\in_array($type, self::TYPES)) {
			throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $type, 406);
		}
		$this->type = $type;
		if ($component) {
			$this->component = $component;
		}
		if ('module' === $this->type) {
			$this->templatePath = 'modules' . \DIRECTORY_SEPARATOR . $component . \DIRECTORY_SEPARATOR . 'ConfigTemplate.php';
			$this->path = 'config' . \DIRECTORY_SEPARATOR . 'Modules' . \DIRECTORY_SEPARATOR . "{$component}.php";
		} elseif ('component' === $this->type) {
			$this->templatePath = 'config' . \DIRECTORY_SEPARATOR . 'Components' . \DIRECTORY_SEPARATOR . 'ConfigTemplates.php';
			$this->path = 'config' . \DIRECTORY_SEPARATOR . 'Components' . \DIRECTORY_SEPARATOR . "{$component}.php";
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
		Cache::resetFileCache($this->templatePath);
		$data = require "{$this->templatePath}";
		if ('component' === $this->type) {
			if (!isset($data[$this->component])) {
				throw new Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||{$this->type}:{$this->component}", 406);
			}
			$data = $data[$this->component];
		} elseif ('module' !== $this->type) {
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
		if ('module' === $this->type) {
			$className .= 'Modules\\' . $this->component;
		} elseif ('component' === $this->type) {
			$className .= 'Components\\' . $this->component;
		} else {
			$className .= ucfirst($this->type);
		}
		return $className;
	}

	/**
	 * Gets template data.
	 *
	 * @param string|null $key
	 *
	 * @return mixed
	 */
	public function getTemplate(?string $key = null)
	{
		return $key ? ($this->template[$key] ?? null) : $this->template;
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
		}
		if (isset($this->template[$key]['validationValues']) && \is_array($this->template[$key]['validationValues'])) {
			return \in_array($value, $this->template[$key]['validationValues']);
		}
		if (!empty($this->template[$key]['loopValidate'])) {
			$status = true;
			foreach ($value as $row) {
				if (true !== \call_user_func_array($this->template[$key]['validation'], [$row])) {
					$status = false;
					break;
				}
			}
			return $status;
		}
		if (!isset($this->template[$key]['validation']) || !\is_callable($this->template[$key]['validation'])) {
			throw new Exceptions\AppException("ERR_CONTENTS_VARIABLE_CANT_CALLED_FUNCTION||{$key}", 406);
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
		if (isset($this->template[$key]['sanitization'])) {
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
		$class->addComment("Configuration file: $className.");
		foreach ($this->template as $parameterName => $parameter) {
			if (isset($parameter['type']) && 'function' === $parameter['type']) {
				$property = $class->addMethod($parameterName)->setStatic()->setBody($parameter['default'])->addComment($parameter['description']);
			} else {
				$value = $this->has($parameterName) ? $this->get($parameterName) : Config::get($className, $parameterName, $parameter['default']);
				$property = $class->addProperty($parameterName, $value)->setStatic()->addComment($parameter['description']);
			}
			if (isset($parameter['docTags'])) {
				foreach ($parameter['docTags'] as $tagName => $val) {
					$property->addComment('');
					$property->addComment("@{$tagName} {$val}");
				}
			}
		}
		if (false === file_put_contents($this->path, $file, LOCK_EX)) {
			throw new Exceptions\AppException("ERR_CREATE_FILE_FAILURE||{$this->path}");
		}
		Cache::resetFileCache($this->path);
		if (\class_exists($className)) {
			foreach ($class->getProperties() as $name => $property) {
				if (isset($className::${$name})) {
					$className::${$name} = $property->getValue();
				}
			}
		} else {
			require "{$this->path}";
		}
	}
}
