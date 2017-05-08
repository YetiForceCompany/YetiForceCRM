<?php

/**
 * List View Class for Mail Settings
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Mail_List_View extends Settings_Vtiger_List_View
{

	/**
	 * Function to get the page title
	 * @param \App\Request $request
	 * @return string
	 */
	public function getPageTitle(\App\Request $request)
	{
		return 'LBL_MAIL_QUEUE_PAGE_TITLE';
	}
}
