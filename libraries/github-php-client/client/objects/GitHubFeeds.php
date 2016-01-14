<?php

require_once(__DIR__ . '/../GitHubObject.php');
require_once(__DIR__ . '/GitHubFeedsLinks.php');
	

class GitHubFeeds extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'timeline_url' => 'string',
			'user_url' => 'string',
			'current_user_public' => 'string',
			'current_user_url' => 'string',
			'current_user_actor_url' => 'string',
			'current_user_organization_url' => 'string',
			'links' => 'GitHubFeedsLinks',
		));
	}
	
	/**
	 * @var string
	 */
	protected $timeline_url;

	/**
	 * @var string
	 */
	protected $user_url;

	/**
	 * @var string
	 */
	protected $current_user_public;

	/**
	 * @var string
	 */
	protected $current_user_url;

	/**
	 * @var string
	 */
	protected $current_user_actor_url;

	/**
	 * @var string
	 */
	protected $current_user_organization_url;

	/**
	 * @var GitHubFeedsLinks
	 */
	protected $links;

	/**
	 * @return string
	 */
	public function getTimelineUrl()
	{
		return $this->timeline_url;
	}

	/**
	 * @return string
	 */
	public function getUserUrl()
	{
		return $this->user_url;
	}

	/**
	 * @return string
	 */
	public function getCurrentUserPublic()
	{
		return $this->current_user_public;
	}

	/**
	 * @return string
	 */
	public function getCurrentUserUrl()
	{
		return $this->current_user_url;
	}

	/**
	 * @return string
	 */
	public function getCurrentUserActorUrl()
	{
		return $this->current_user_actor_url;
	}

	/**
	 * @return string
	 */
	public function getCurrentUserOrganizationUrl()
	{
		return $this->current_user_organization_url;
	}

	/**
	 * @return GitHubFeedsLinks
	 */
	public function getLinks()
	{
		return $this->links;
	}

}

