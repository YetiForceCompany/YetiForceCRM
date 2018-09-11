<?php
/**
 * Cron for downloading messages from social media.
 *
 * @package   Cron
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
foreach (\App\SocialMedia::ALLOWED_UITYPE as $uiType => $socialMediaType) {
	if (\App\SocialMedia::isConfigured($uiType)) {
		$availableSocialMediaType[] = \App\SocialMedia::ALLOWED_UITYPE[$uiType];
	} else {
		\App\SocialMedia::log($uiType, 'warning', 'Unconfigured API');
	}
}

foreach (\App\SocialMedia::getSocialMediaAccount($availableSocialMediaType) as $socialMedia) {
	$socialMedia->retrieveDataFromApi();
}
