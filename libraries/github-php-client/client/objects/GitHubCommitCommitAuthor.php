<?php

require_once(__DIR__ . '/../GitHubObject.php');

	

class GitHubCommitCommitAuthor extends GitHubObject
{
	/* (non-PHPdoc)
	 * @see GitHubObject::getAttributes()
	 */
	protected function getAttributes()
	{
		return array_merge(parent::getAttributes(), array(
			'name' => 'string',
			'date' => 'string',
			'email' => 'string',
		));
	}

   /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $date;

    /**
     * @var string
     */
    protected $email;

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
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
}

