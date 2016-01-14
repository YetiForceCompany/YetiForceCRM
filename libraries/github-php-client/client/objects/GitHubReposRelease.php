<?php

require_once(__DIR__ . '/GitHubGitCommitAuthor.php');
require_once(__DIR__ . '/GitHubReposReleaseAsset.php');

	

class GitHubReposRelease extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'url' => 'string',
			'html_url' => 'string',
			'assets_url' => 'string',
			'upload_url' => 'string',
			'tarball_url' => 'string',
			'zipball_url' => 'string',
			'id' => 'int',
			'tag_name' => 'string',
			'target_commitish' => 'string',
			'name' => 'string',
			'body' => 'string',
			'draft' => 'boolean',
			'prerelease' => 'boolean',
			'created_at' => 'string',
			'published_at' => 'string',
			'author' => 'GitHubGitCommitAuthor',
			'assets' => 'array<GitHubReposReleaseAsset>',
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
	 * @var string
	 */
	protected $assets_url;
	/**
	 * @var string
	 */
	protected $upload_url;
	/**
	 * @var string
	 */
	protected $tarball_url;
	/**
	 * @var string
	 */
	protected $zipball_url;
	/**
	 * @var string
	 */
	protected $id;
	/**
	 * @var string
	 */
	protected $tag_name;
	/**
	 * @var string
	 */
	protected $target_commitish;
	/**
	 * @var string
	 */
	protected $name;
	/**
	 * @var string
	 */
	protected $body;
	/**
	 * @var boolean
	 */
	protected $draft;
	/**
	 * @var boolean
	 */
	protected $prerelease;
	/**
	 * @var string
	 */
	protected $created_at;
	/**
	 * @var string
	 */
	protected $published_at;
	/**
	 * @var GitHubGitCommitAuthor
	 */
	protected $author;
	/**
	 * @var array<GitHubReposReleaseAsset>
	 */
	protected $assets;
	
	/**
	 * @return the $url
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @return the $html_url
	 */
	public function getHtml_url()
	{
		return $this->html_url;
	}

	/**
	 * @return the $assets_url
	 */
	public function getAssets_url()
	{
		return $this->assets_url;
	}

	/**
	 * @return the $upload_url
	 */
	public function getUpload_url()
	{
		return $this->upload_url;
	}

	/**
	 * @return the $tarball_url
	 */
	public function getTarball_url()
	{
		return $this->tarball_url;
	}

	/**
	 * @return the $zipball_url
	 */
	public function getZipball_url()
	{
		return $this->zipball_url;
	}

	/**
	 * @return the $id
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return the $tag_name
	 */
	public function getTag_name()
	{
		return $this->tag_name;
	}

	/**
	 * @return the $target_commitish
	 */
	public function getTarget_commitish()
	{
		return $this->target_commitish;
	}

	/**
	 * @return the $name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return the $body
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * @return the $draft
	 */
	public function getDraft()
	{
		return $this->draft;
	}

	/**
	 * @return the $prerelease
	 */
	public function getPrerelease()
	{
		return $this->prerelease;
	}

	/**
	 * @return the $created_at
	 */
	public function getCreated_at()
	{
		return $this->created_at;
	}

	/**
	 * @return the $published_at
	 */
	public function getPublished_at()
	{
		return $this->published_at;
	}

	/**
	 * @return GitHubGitCommitAuthor
	 */
	public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * @return array
	 */
	public function getAssets()
	{
		return $this->assets;
	}
}

