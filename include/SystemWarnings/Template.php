<?php namespace includes\SystemWarnings;

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
		$status = 'LBL_WARNINGS_INCORRECT';
		switch ($this->status) {
			case 1:
				$status = 'LBL_WARNINGS_CORRECT';
				break;
			case 2:
				$status = 'LBL_WARNINGS_OMITTED';
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
}
