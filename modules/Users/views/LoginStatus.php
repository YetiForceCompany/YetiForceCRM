<?php

/**
 * Login status information class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 **/
class Users_LoginStatus_View extends \App\Controller\View
{
	/**
	 * {@inheritdoc}
	 */
	public function loginRequired()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isSessionExtend()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcess(\App\Request $request, $display = true)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcess(\App\Request $request, $display = true)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		die(\App\User::getCurrentUserId() ? '1' : '0');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderCss(\App\Request $request)
	{
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderScripts(\App\Request $request)
	{
		return [];
	}
}
