{************************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
<div class="col-sm-12">

	{* Comupte the nubmer of columns required *}
	{assign var="SPANSIZE" value=12}
	{if $WIDGET_MODEL->getHeaderCount()}
		{assign var="SPANSIZE" value=12/$WIDGET_MODEL->getHeaderCount()}
	{/if}

	<div class="row">
		{foreach item=FIELD from=$WIDGET_MODEL->getHeaders()}
			<div class="col-sm-{$SPANSIZE}"><strong>{vtranslate($FIELD->get('label'),$BASE_MODULE)} </strong></div>
		{/foreach}
	</div>
	{assign var="WIDGET_RECORDS" value=$WIDGET_MODEL->getRecords($OWNER)}
	{foreach item=RECORD from=$WIDGET_RECORDS}
		<div class="row">
			{foreach item=FIELD from=$WIDGET_MODEL->getHeaders() name="widgetModelRowHeaders"}
				{assign var="LAST_RECORD" value=$smarty.foreach.widgetModelRowHeaders.last}
				<div class="col-sm-{$SPANSIZE} textOverflowEllipsis" title="{strip_tags($RECORD->get($FIELD->get('name')))}">
					{if $LAST_RECORD && $RECORD->isEditable()}
						<a class="showModal pull-right" data-url="{$RECORD->getEditStatusUrl()}">
							<span title="{vtranslate('LBL_SET_RECORD_STATUS', $BASE_MODULE)}" class="glyphicon glyphicon-modal-window alignMiddle"></span>
						</a>&nbsp;
					{/if}
					{if $RECORD->get($FIELD->get('name'))}
						{vtranslate($RECORD->get($FIELD->get('name')), $BASE_MODULE)}
					{else}
						&nbsp;
					{/if}
				</div>
			{/foreach}
		</div>
	{/foreach}

	{if count($WIDGET_RECORDS) >= $WIDGET_MODEL->getRecordLimit()}
		<div class="">
			<a class="pull-right" href="index.php?module={$WIDGET_MODEL->getTargetModule()}&view=List&mode=showListViewRecords&viewname={$WIDGET->get('filterid')}">{vtranslate('LBL_MORE')}</a>
		</div>
	{/if}

</div>
