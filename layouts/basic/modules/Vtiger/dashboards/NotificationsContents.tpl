{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	<div class="notificationContainer">
		{if $NOTIFICATIONS neq false}
			{assign var=SHOW_TYPE value=true}
			<div class="notificationEntries">
				{foreach item=ROWS from=$NOTIFICATIONS}
					{foreach item=ROW from=$ROWS}
						{include file='NotificationsItem.tpl'|@vtemplate_path:$MODULE_NAME}
					{/foreach}
				{/foreach}
			</div>
		{else}
			<span class="noDataMsg">
				{vtranslate('LBL_NO_NOTIFICATIONS', $MODULE_NAME)}
			</span>
		{/if}
	</div>
{/strip}
