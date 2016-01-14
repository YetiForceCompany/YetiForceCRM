<?php

require_once(__DIR__ . '/../GitHubObject.php');
require_once(__DIR__ . '/GitHubOauthAccessApp.php');
	

class GitHubOauthAccess extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'id' => 'int',
			'url' => 'string',
			'token' => 'string',
			'note' => 'string',
			'note_url' => 'string',
			'updated_at' => 'string',
			'created_at' => 'string',
			'app' => 'GitHubOauthAccessApp',
		));
	}
	
	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var string
	 */
	protected $token;

	/**
	 * @var string
	 */
	protected $note;

	/**
	 * @var string
	 */
	protected $note_url;

	/**
	 * @var string
	 */
	protected $updated_at;

	/**
	 * @var string
	 */
	protected $created_at;

	/**
	 * @var GitHubOauthAccessApp
	 */
	protected $app;

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
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @return string
	 */
	public function getToken()
	{
		return $this->token;
	}

	/**
	 * @return string
	 */
	public function getNote()
	{
		return $this->note;
	}

	/**
	 * @return string
	 */
	public function getNoteUrl()
	{
		return $this->note_url;
	}

	/**
	 * @return string
	 */
	public function getUpdatedAt()
	{
		return $this->updated_at;
	}

	/**
	 * @return string
	 */
	public function getCreatedAt()
	{
		return $this->created_at;
	}

	/**
	 * @return GitHubOauthAccessApp
	 */
	public function getApp()
	{
		return $this->app;
	}

}

