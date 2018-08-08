/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class('Settings_SocialMedia_Index_Js', {}, {
	/**
	 * Register all events in view
	 */
	registerEvents() {
		Settings_SocialMedia_Twitter_Js.getInstance().registerEvents();
	}
});
