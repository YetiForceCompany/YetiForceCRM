<?php

require_once(__DIR__ . '/../GitHubObject.php');

	

class GitHubThreadSubject extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'title' => 'string',
			'url' => 'string',
			'latest_comment_url' => 'string',
			'type' => 'string',
		));
	}
	
	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var string
	 */
	protected $latest_comment_url;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
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
	public function getLatestCommentUrl()
	{
		return $this->latest_comment_url;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

}

