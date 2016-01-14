<?php

require_once(__DIR__ . '/../GitHubObject.php');
require_once(__DIR__ . '/GitHubSimpleRepo.php');
require_once(__DIR__ . '/GitHubThreadSubject.php');
	

class GitHubThread extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'id' => 'int',
			'repository' => 'GitHubSimpleRepo',
			'reason' => 'string',
			'unread' => 'boolean',
			'updated_at' => 'string',
			'last_read_at' => 'string',
			'url' => 'string',
			'subject' => 'GitHubThreadSubject',
		));
	}
	
	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var GitHubSimpleRepo
	 */
	protected $repository;

	/**
	 * @var string
	 */
	protected $reason;

	/**
	 * @var boolean
	 */
	protected $unread;

	/**
	 * @var string
	 */
	protected $updated_at;

	/**
	 * @var string
	 */
	protected $last_read_at;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var GitHubThreadSubject
	 */
	protected $subject;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return GitHubSimpleRepo
	 */
	public function getRepository()
	{
		return $this->repository;
	}

	/**
	 * @return string
	 */
	public function getReason()
	{
		return $this->reason;
	}

	/**
	 * @return boolean
	 */
	public function getUnread()
	{
		return $this->unread;
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
	public function getLastReadAt()
	{
		return $this->last_read_at;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @return GitHubThreadSubject
	 */
	public function getSubject()
	{
		return $this->subject;
	}

}

