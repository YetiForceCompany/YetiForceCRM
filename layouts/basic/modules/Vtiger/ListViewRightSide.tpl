{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="btn-toolbar pull-right">
		{foreach from=$LISTVIEW_ENTRY->getRecordListViewLinks() item=LINK}
			{include file='ButtonLink.tpl'|@vtemplate_path:$MODULE BUTTON_VIEW='listViewBasic'}
		{/foreach}
	</div>
{/strip}
