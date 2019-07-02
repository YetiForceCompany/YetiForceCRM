{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	<!-- tpl-install-tpl-InstallPostProcess -->
	<br>
	<footer class="noprint text-center fixed-bottom c-footer">
		<p class="text-center text-center {if App\Config::module('Users', 'IS_VISIBLE_USER_INFO_FOOTER')}u-p-05per{/if}">
							<span class="d-none d-sm-inline ">Copyright &copy; YetiForce.com All rights reserved.
								<br/>{\App\Language::translateArgs('LBL_FOOTER_CONTENT', 'Vtiger','open source project')}
							</span>
			<span class="d-inline d-sm-none text-center">&copy; YetiForce.com All rights reserved.</span>
		</p>
	</footer>
	{include file='JSResources.tpl'}
	</div>
	</body>
	</html>
	<!-- /tpl-install-tpl-InstallPostProcess -->
{/strip}
