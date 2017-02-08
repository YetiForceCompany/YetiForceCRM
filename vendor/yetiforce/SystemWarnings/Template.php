<?php
namespace App\SystemWarnings;

/**
 * System warnings template abstract class
 * @package YetiForce.SystemWarnings
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
abstract class Template
{

	protected $title;
	protected $description;
	protected $priority = 0;
	protected $color;
	protected $status = 0;
	protected $folder;
	protected $link;
	protected $tpl = false;

	/**
	 * Checking whether there is a warning
	 */
	abstract function process();

	/**
	 * Whether a warning is active
	 * @return boolean
	 */
	public function preProcess()
	{
		return true;
	}

	/**
	 * Returns the warning priority
	 * @return int
	 */
	public function getPriority()
	{
		return $this->priority;
	}

	/**
	 * Returns the warning title
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Returns the warning color
	 * @return string
	 */
	public function getColor()
	{
		return $this->color;
	}

	/**
	 * Returns the warning status
	 * @return string|int
	 */
	public function getStatus($returnText = false)
	{
		if (!$returnText) {
			return $this->status;
		}
		$status = 2;
		switch ($this->status) {
			case 1:
				$status = 'OK';
				break;
			case 2:
				$status = 'Bład';
				break;
		}
		return $status;
	}

	/**
	 * Returns the warning description
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Returns the warning link
	 * @return string
	 */
	public function getLink()
	{
		return $this->link;
	}

	/**
	 * Updates the warning folder
	 * @return string
	 */
	public function setFolder($folder)
	{
		return $this->folder = $folder;
	}

	/**
	 * Returns the warning folder
	 * @return string
	 */
	public function getFolder($toArray = true)
	{
		if ($toArray && strpos($this->folder, '\\') !== false) {
			$this->folder = explode('\\', $this->folder);
		}
		return $this->folder;
	}

	public function getTpl()
	{
		if (!$this->tpl || is_string($this->tpl)) {
			return $this->tpl;
		}
		$refClass = new \ReflectionClass($this);
		$className = $refClass->getShortName();
		$path = vtemplate_path("{$this->getFolder(false)}/{$className}.tpl", 'Settings:SystemWarnings');
		$this->tpl = $path;
		return $path;
	}

	/**
	 * Update ignoring status
	 * @param int $params
	 * @return boolean
	 */
	public function update($params)
	{
		$status = $params === '2' ? 0 : 2;
		$refClass = new \ReflectionClass($this);
		$filePath = $refClass->getFileName();
		$fileContent = file_get_contents($filePath);
		if (strpos($fileContent, 'protected $status ') !== false) {
			$pattern = '/\$status = ([^;]+)/';
			$replacement = '$status = ' . $status;
			$fileContent = preg_replace($pattern, $replacement, $fileContent);
		} else {
			$replacement = '{' . PHP_EOL . '	protected $status = ' . $status . ';';
			$fileContent = preg_replace('/{/', $replacement, $fileContent, 1);
		}
		file_put_contents($filePath, $fileContent);
		return true;
	}
}
