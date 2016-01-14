<?php

require_once(__DIR__ . '/../GitHubObject.php');

	

class GitHubTag extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'name' => 'string',
			'zipball_url' => 'string',
			'tarball_url' => 'string',
		));
	}
	
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $zipball_url;

	/**
	 * @var string
	 */
	protected $tarball_url;

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getZipballUrl()
	{
		return $this->zipball_url;
	}

	/**
	 * @return string
	 */
	public function getTarballUrl()
	{
		return $this->tarball_url;
	}

}

