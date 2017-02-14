{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
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
		<div class="row rowAction cursorPointer" {if $RECORD->editFieldByModalPermission(true)} data-url="{$RECORD->getEditFieldByModalUrl()}"{/if}>
			{foreach item=FIELD from=$WIDGET_MODEL->getHeaders()}
				<div class="col-sm-{$SPANSIZE} textOverflowEllipsis" title="{strip_tags($RECORD->get($FIELD->get('name')))}">
					{if $RECORD->get($FIELD->get('name'))}
						<span class="pull-left">{vtranslate($RECORD->getListViewDisplayValue($FIELD->get('name')), $BASE_MODULE)}</span>
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
{/strip}
