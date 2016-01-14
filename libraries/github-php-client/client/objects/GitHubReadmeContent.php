<?php

require_once(__DIR__ . '/../GitHubObject.php');

	

class GitHubReadmeContent extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'type' => 'string',
			'encoding' => 'string',
			'size' => 'int',
			'name' => 'string',
			'path' => 'string',
			'content' => 'string',
			'sha' => 'string',
			'url' => 'string',
			'git_url' => 'string',
			'html_url' => 'string',
		));
	}
	
	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $encoding;

	/**
	 * @var int
	 */
	protected $size;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var string
	 */
	protected $content;

	/**
	 * @var string
	 */
	protected $sha;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var string
	 */
	protected $git_url;

	/**
	 * @var string
	 */
	protected $html_url;

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getEncoding()
	{
		return $this->encoding;
	}

	/**
	 * @return int
	 */
	public function getSize()
	{
		return $this->size;
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
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @return string
	 */
	public function getSha()
	{
		return $this->sha;
	}

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
	public function getGitUrl()
	{
		return $this->git_url;
	}

	/**
	 * @return string
	 */
	public function getHtmlUrl()
	{
		return $this->html_url;
	}

}

