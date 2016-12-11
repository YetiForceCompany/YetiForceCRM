{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=LINKS value=$LISTVIEW_ENTRY->getRecordListViewLinks()}
	{if count($LINKS) > 0}
		<div class="actions">
			<div class="actionImages hide">
				{foreach from=$LINKS item=LINK}
					{include file='ButtonLink.tpl'|@vtemplate_path:$MODULE BUTTON_VIEW='listViewBasic'}
				{/foreach}
			</div>
			<button type="button" class="btn btn-sm btn-default toolsAction">
				<span class="glyphicon glyphicon-wrench"></span>
			</button>
		</div>
	{/if}
{/strip}
