{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	<div class="notificationContainer">
		{if $NOTIFICATIONS neq false}
			<div class="notificationEntries">
				{foreach item=ROW from=$NOTIFICATIONS}
					{include file='NotificationsItem.tpl'|@vtemplate_path:$MODULE_NAME}
				{/foreach}
			</div>
		{else}
			<span class="noDataMsg">
				{\App\Language::translate('LBL_NO_NOTIFICATIONS', $MODULE_NAME)}
			</span>
		{/if}
	</div>
{/strip}
