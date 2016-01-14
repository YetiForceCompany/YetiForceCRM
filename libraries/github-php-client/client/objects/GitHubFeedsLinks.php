<?php

require_once(__DIR__ . '/../GitHubObject.php');
require_once(__DIR__ . '/GitHubFeedsLinksTimeline.php');
require_once(__DIR__ . '/GitHubFeedsLinksUser.php');
require_once(__DIR__ . '/GitHubFeedsLinksCurrentUserPublic.php');
require_once(__DIR__ . '/GitHubFeedsLinksCurrentUser.php');
require_once(__DIR__ . '/GitHubFeedsLinksCurrentUserActor.php');
require_once(__DIR__ . '/GitHubFeedsLinksCurrentUserOrganization.php');
	

class GitHubFeedsLinks extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'timeline' => 'GitHubFeedsLinksTimeline',
			'user' => 'GitHubFeedsLinksUser',
			'current_user_public' => 'GitHubFeedsLinksCurrentUserPublic',
			'current_user' => 'GitHubFeedsLinksCurrentUser',
			'current_user_actor' => 'GitHubFeedsLinksCurrentUserActor',
			'current_user_organization' => 'GitHubFeedsLinksCurrentUserOrganization',
		));
	}
	
	/**
	 * @var GitHubFeedsLinksTimeline
	 */
	protected $timeline;

	/**
	 * @var GitHubFeedsLinksUser
	 */
	protected $user;

	/**
	 * @var GitHubFeedsLinksCurrentUserPublic
	 */
	protected $current_user_public;

	/**
	 * @var GitHubFeedsLinksCurrentUser
	 */
	protected $current_user;

	/**
	 * @var GitHubFeedsLinksCurrentUserActor
	 */
	protected $current_user_actor;

	/**
	 * @var GitHubFeedsLinksCurrentUserOrganization
	 */
	protected $current_user_organization;

	/**
	 * @return GitHubFeedsLinksTimeline
	 */
	public function getTimeline()
	{
		return $this->timeline;
	}

	/**
	 * @return GitHubFeedsLinksUser
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @return GitHubFeedsLinksCurrentUserPublic
	 */
	public function getCurrentUserPublic()
	{
		return $this->current_user_public;
	}

	/**
	 * @return GitHubFeedsLinksCurrentUser
	 */
	public function getCurrentUser()
	{
		return $this->current_user;
	}

	/**
	 * @return GitHubFeedsLinksCurrentUserActor
	 */
	public function getCurrentUserActor()
	{
		return $this->current_user_actor;
	}

	/**
	 * @return GitHubFeedsLinksCurrentUserOrganization
	 */
	public function getCurrentUserOrganization()
	{
		return $this->current_user_organization;
	}

}

