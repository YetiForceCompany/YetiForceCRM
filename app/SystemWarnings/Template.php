<?php

/**
 * System warnings template abstract file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\SystemWarnings;

/**
 * System warnings template abstract class.
 */
abstract class Template
{
	/**
	 * Status value
	 * 0 - active, 2 - ignored.
	 *
	 * @var int
	 */
	protected $statusValue = 0;

	/** @var string Modal header title */
	protected $title;

	/** @var string|null Modal description */
	protected $description;

	/** @var int Warning priority code */
	protected $priority = 0;
	protected $color;
	/**
	 * Status
	 * 0 - warning occurred, 1 - no warning.
	 *
	 * @var int
	 */
	protected $status = 0;
	protected $folder;

	/** @var string|null Link URL */
	protected $link;

	/** @var bool Template flag */
	protected $tpl = false;

	/**
	 * Checking whether there is a warning.
	 *
	 * @return void
	 */
	abstract public function process(): void;

	/**
	 * Whether a warning is active.
	 *
	 * @return bool
	 */
	public function preProcess(): bool
	{
		return true;
	}

	/**
	 * Returns the warning priority.
	 *
	 * @return int
	 */
	public function getPriority(): int
	{
		return $this->priority;
	}

	/**
	 * Returns the warning title.
	 *
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

	/**
	 * Returns the warning color.
	 *
	 * @return string
	 */
	public function getColor(): string
	{
		return $this->color;
	}

	/**
	 * Get status value.
	 *
	 * @return int
	 */
	public function getStatusValue(): int
	{
		return $this->statusValue;
	}

	/**
	 * Returns the warning status.
	 *
	 * @return int
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Returns the warning description.
	 *
	 * @return string|null
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * Returns the warning link.
	 *
	 * @return string|null
	 */
	public function getLink(): ?string
	{
		return $this->link;
	}

	public function getTpl()
	{
		if (!$this->tpl || \is_string($this->tpl)) {
			return $this->tpl;
		}
		$refClass = new \ReflectionClass($this);
		$className = $refClass->getShortName();
		$path = \App\Layout::getTemplatePath("{$this->getFolder(false)}/{$className}.tpl", 'Settings:SystemWarnings');
		$this->tpl = $path;

		return $path;
	}

	/**
	 * Returns the warning folder.
	 *
	 * @param mixed $toArray
	 *
	 * @return string
	 */
	public function getFolder($toArray = true)
	{
		if ($toArray && false !== strpos($this->folder, '\\')) {
			$this->folder = explode('\\', $this->folder);
		}
		return $this->folder;
	}

	/**
	 * Updates the warning folder.
	 *
	 * @param mixed $folder
	 *
	 * @return string
	 */
	public function setFolder($folder)
	{
		return $this->folder = $folder;
	}

	/**
	 * Update ignoring status.
	 *
	 * @param int $params
	 *
	 * @return bool
	 */
	public function update($params)
	{
		$statusValue = '2' === $params ? 0 : 2;
		$refClass = new \ReflectionClass($this);
		$filePath = $refClass->getFileName();
		$fileContent = file_get_contents($filePath);
		if (false !== strpos($fileContent, 'protected $statusValue ')) {
			$pattern = '/\$statusValue = ([^;]+)/';
			$replacement = '$statusValue = ' . $statusValue;
			$fileContent = preg_replace($pattern, $replacement, $fileContent);
		} else {
			$replacement = '{' . PHP_EOL . '	protected $statusValue = ' . $statusValue . ';';
			$fileContent = preg_replace('/{/', $replacement, $fileContent, 1);
		}
		file_put_contents($filePath, $fileContent);
		\App\Cache::resetFileCache($filePath);

		return ['result' => true, 'message' => \App\Language::translate('LBL_DATA_SAVE_OK', 'Settings::SystemWarnings')];
	}
}
