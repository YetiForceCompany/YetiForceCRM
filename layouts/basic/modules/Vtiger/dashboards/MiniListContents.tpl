{************************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
*************************************************************************************}
{strip}
	{* Comupte the nubmer of columns required *}
	{assign var="SPANSIZE_ARRAY" value=[]}
	{assign var="SPANSIZE" value=12}
	{assign var="HEADER_COUNT" value=$MINILIST_WIDGET_MODEL->getHeaderCount()}
	{if $HEADER_COUNT}
		{assign var="SPANSIZE" value=(12/$HEADER_COUNT)|string_format:"%d"}
	{/if}

	<div class="row">
		{foreach item=FIELD from=$MINILIST_WIDGET_MODEL->getHeaders() name=headers}
			{assign var="ITERATION" value=$smarty.foreach.headers.iteration}
			{$SPANSIZE_ARRAY[$ITERATION] = $SPANSIZE}
			{if $HEADER_COUNT eq 5 && in_array($ITERATION, [4,5])}
				{$SPANSIZE_ARRAY[$ITERATION] = 3}
			{/if}
			<h6 class="p-0 pr-2 col-sm-{$SPANSIZE_ARRAY[$ITERATION]} u-font-size-100per mb-0"><strong>{\App\Language::translate($FIELD->get('label'),$BASE_MODULE)} </strong></h6>
		{/foreach}
	</div>
	{if $OWNER eq false}
		{assign var="MINILIST_WIDGET_RECORDS" value=[]}
	{else}
		{assign var="MINILIST_WIDGET_RECORDS" value=$MINILIST_WIDGET_MODEL->getRecords($OWNER)}
	{/if}
	{foreach item=RECORD from=$MINILIST_WIDGET_RECORDS}
		<div class="row">
			{foreach item=FIELD from=$MINILIST_WIDGET_MODEL->getHeaders() name="minilistWidgetModelRowHeaders"}
				{assign var="ITERATION" value=$smarty.foreach.minilistWidgetModelRowHeaders.iteration}
				{assign var="LAST_RECORD" value=$smarty.foreach.minilistWidgetModelRowHeaders.last}
				<div class="p-0 col-sm-{$SPANSIZE_ARRAY[$ITERATION]}">
					{if $LAST_RECORD}
						<a href="{$RECORD->getDetailViewUrl()}" class="float-right"><span title="{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS',$MODULE_NAME)}" class="fas fa-th-list alignMiddle"></span></a>
					{/if}
					{if $RECORD->get($FIELD->get('name'))}
					<div class="pr-2 u-text-ellipsis u-text-ellipsis--bg-white">{$RECORD->getDisplayValue($FIELD->get('name'))}</div>
					{else}
						&nbsp;
					{/if}
				</div>
			{/foreach}
		</div>
	{/foreach}
{/strip}
