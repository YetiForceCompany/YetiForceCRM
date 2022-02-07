{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Base-ListView-TableHeader -->
	<thead>
		<tr class="listViewHeaders">
			{if isset($EMPTY_COLUMN) && 1===$EMPTY_COLUMN}
				<th width="1%" class="{$WIDTHTYPE}"></th>
			{/if}
			{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
				{if empty($LISTVIEW_HEADER->get('moduleName')) }
					{assign var=MODULE_NAME_FOR_LABEL value=$QUALIFIED_MODULE}
				{else}
					{assign var=MODULE_NAME_FOR_LABEL value=$LISTVIEW_HEADER->get('moduleName')}
				{/if}
				<th width="{$WIDTH}%" nowrap {if $LISTVIEW_HEADER@last}colspan="2" {/if} class="{$WIDTHTYPE}">
					<a {if !($LISTVIEW_HEADER->has('sort'))} class="listViewHeaderValues u-cursor-pointer js-listview_header" data-js="click" data-nextsortorderval="{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER->get('name')}" {/if}>{\App\Language::translate($LISTVIEW_HEADER->get('label'), $MODULE_NAME_FOR_LABEL)}
						{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}&nbsp;&nbsp;<span class="{$SORT_IMAGE}"></span>{/if}</a>
				</th>
			{/foreach}
		</tr>
	</thead>
	<!-- /tpl-Settings-Base-ListView-TableHeader -->
{/strip}
