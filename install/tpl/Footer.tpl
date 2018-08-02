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
				<input id="activityReminder" class="d-none noprint" type="hidden" value="{$ACTIVITY_REMINDER}">
				{if !$MAIN_PRODUCT_WHITELABEL}
				<footer class="noprint">
					<div class="vtFooter mb-5 pb-5 pb-sm-0">
						<p>
							{\App\Language::translate('LBL_FOOTER_CONTENT')}
						</p>
					</div>
				</footer>
				{/if}
				{* javascript files *}
				{include file='JSResources.tpl'}
			</div>
		</body>
	</html>
{/strip}
