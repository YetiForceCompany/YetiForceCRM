<?php

namespace App\SocialMedia;

interface SocialMediaInterface
{
	public function __construct($userName);

	public static function isConfigured();

	public function retrieveDataFromApi();
}
