<?php

require_once(__DIR__ . '/../GitHubObject.php');

	

class GitHubSubscription extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'subscribed' => 'boolean',
			'ignored' => 'boolean',
			'reason' => 'string',
			'created_at' => 'string',
			'url' => 'string',
			'thread_url' => 'string',
		));
	}
	
	/**
	 * @var boolean
	 */
	protected $subscribed;

	/**
	 * @var boolean
	 */
	protected $ignored;

	/**
	 * @var string
	 */
	protected $reason;

	/**
	 * @var string
	 */
	protected $created_at;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var string
	 */
	protected $thread_url;

	/**
	 * @return boolean
	 */
	public function getSubscribed()
	{
		return $this->subscribed;
	}

	/**
	 * @return boolean
	 */
	public function getIgnored()
	{
		return $this->ignored;
	}

	/**
	 * @return string
	 */
	public function getReason()
	{
		return $this->reason;
	}

	/**
	 * @return string
	 */
	public function getCreatedAt()
	{
		return $this->created_at;
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
	public function getThreadUrl()
	{
		return $this->thread_url;
	}

}

