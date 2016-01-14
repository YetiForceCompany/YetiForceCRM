<?php

require_once(__DIR__ . '/GitHubUser.php');

	

class GitHubReposReleaseAsset extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'url' => 'string',
			'id' => 'int',
			'name' => 'string',
			'label' => 'string',
			'state' => 'string',
			'content_type' => 'string',
			'size' => 'int',
			'download_count' => 'int',
			'created_at' => 'string',
			'updated_at' => 'string',
			'uploader' => 'GitHubUser', 
		));
	}
	
	/**
	 * @var string
	 */
	protected $url;

	
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
	protected $label;
	
	/**
	 * @var string
	 */
	protected $state;
	
	/**
	 * @var string
	 */
	protected $content_type;
	
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
	protected $created_at;
	
	/**
	 * @var string
	 */
	protected $updated_at;
	
	/**
	 * @var GitHubUser
	 */
	protected $uploader;
	
	/**
	 * @return the $url
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @return the $id
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return the $name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return the $label
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * @return the $state
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * @return the $content_type
	 */
	public function getContent_type()
	{
		return $this->content_type;
	}

	/**
	 * @return the $size
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * @return the $download_count
	 */
	public function getDownload_count()
	{
		return $this->download_count;
	}

	/**
	 * @return the $created_at
	 */
	public function getCreated_at()
	{
		return $this->created_at;
	}

	/**
	 * @return the $updated_at
	 */
	public function getUpdated_at()
	{
		return $this->updated_at;
	}

	/**
	 * @return GitHubUser
	 */
	public function getUploader()
	{
		return $this->uploader;
	}
}

