<?php

require_once(__DIR__ . '/../GitHubObject.php');

	

class GitHubDownload extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'url' => 'string',
			'html_url' => 'string',
			'id' => 'int',
			'name' => 'string',
			'description' => 'string',
			'size' => 'int',
			'download_count' => 'int',
			'content_type' => 'string',
		));
	}
	
	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var string
	 */
	protected $html_url;

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var int
	 */
	protected $size;

	/**
	 * @var int
	 */
	protected $download_count;

	/**
	 * @var string
	 */
	protected $content_type;

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @return string
	 */
	public function getHtmlUrl()
	{
		return $this->html_url;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

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
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @return int
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * @return int
	 */
	public function getDownloadCount()
	{
		return $this->download_count;
	}

	/**
	 * @return string
	 */
	public function getContentType()
	{
		return $this->content_type;
	}

}

