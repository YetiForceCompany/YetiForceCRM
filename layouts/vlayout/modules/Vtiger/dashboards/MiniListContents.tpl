{************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************}
<div style='padding:4%;padding-top: 0;margin-bottom: 2%'>

	{* Comupte the nubmer of columns required *}
	{assign var="SPANSIZE" value=12}
	{if $MINILIST_WIDGET_MODEL->getHeaderCount()}
		{assign var="SPANSIZE" value=12/$MINILIST_WIDGET_MODEL->getHeaderCount()}
	{/if}

	<div class="row" style="padding:5px">
		{foreach item=FIELD from=$MINILIST_WIDGET_MODEL->getHeaders()}
		<div class="col-sm-{$SPANSIZE}"><strong>{vtranslate($FIELD->get('label'),$BASE_MODULE)}</strong></div>
		{/foreach}
	</div>
	
	{if $USER eq false}
		{assign var="MINILIST_WIDGET_RECORDS" value=array()}
	{else}
		{assign var="MINILIST_WIDGET_RECORDS" value=$MINILIST_WIDGET_MODEL->getRecords($USER)}
	{/if}
	{foreach item=RECORD from=$MINILIST_WIDGET_RECORDS}
	<div class="row">
		{foreach item=FIELD from=$MINILIST_WIDGET_MODEL->getHeaders() name="minilistWidgetModelRowHeaders"}
			<div class="col-sm-{$SPANSIZE} textOverflowEllipsis" title="{strip_tags($RECORD->get($FIELD->get('name')))}">
				{if $smarty.foreach.minilistWidgetModelRowHeaders.last}
					<a href="{$RECORD->getDetailViewUrl()}" class="pull-right"><i title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS',$MODULE_NAME)}" class="glyphicon glyphicon-th-list alignMiddle"></i></a>
				{/if}
				{$RECORD->get($FIELD->get('name'))}&nbsp;
			</div>
		{/foreach}
	</div>
	{/foreach}

	{if count($MINILIST_WIDGET_RECORDS) >= $MINILIST_WIDGET_MODEL->getRecordLimit()}
	<div class="row" style="padding:5px;padding-bottom:10px;">
		<a class="pull-right" href="index.php?module={$MINILIST_WIDGET_MODEL->getTargetModule()}&view=List&mode=showListViewRecords&viewname={$WIDGET->get('filterid')}">{vtranslate('LBL_MORE')}</a>
	</div>
	{/if}

</div>
