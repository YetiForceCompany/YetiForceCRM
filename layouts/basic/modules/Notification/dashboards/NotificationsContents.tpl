{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="notificationContainer">
		{if $NOTIFICATIONS neq false}
			<div class="notificationEntries">
				{foreach item=ROW from=$NOTIFICATIONS}
					{include file=\App\Layout::getTemplatePath('NotificationsItem.tpl', $MODULE_NAME)}
				{/foreach}
			</div>
		{else}
			<span class="noDataMsg">
				{\App\Language::translate('LBL_NO_NOTIFICATIONS', $MODULE_NAME)}
			</span>
		{/if}
	</div>
{/strip}
