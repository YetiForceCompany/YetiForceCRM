{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Dashboards-ProductsSoldToRenewContents -->
	{assign var="SPANSIZE" value=12}
	{if $WIDGET_MODEL->getHeaderCount()}
		{assign var="SPANSIZE" value=12/$WIDGET_MODEL->getHeaderCount()}
	{/if}
	<div class="row">
		{foreach item=FIELD from=$WIDGET_MODEL->getHeaders()}
			<div class="col-sm-{$SPANSIZE}">
				<strong>{\App\Language::translate($FIELD->get('label'),$BASE_MODULE)} </strong>
			</div>
		{/foreach}
	</div>
	{assign var="WIDGET_RECORDS" value=$WIDGET_MODEL->getRecords()}
	{foreach item=RECORD from=$WIDGET_RECORDS}
		<div class="row rowAction u-cursor-pointer">
			{foreach item=FIELD from=$WIDGET_MODEL->getHeaders()}
				<div class="col-sm-{$SPANSIZE} u-text-ellipsis--no-hover" title="{\App\Purifier::encodeHtml($RECORD->get($FIELD->get('name')))}">
					{if $RECORD->get($FIELD->get('name'))}
						<span>{$RECORD->getListViewDisplayValue($FIELD->get('name'))}</span>
					{else}
						&nbsp;
					{/if}
				</div>
			{/foreach}
		</div>
	{/foreach}
	{if count($WIDGET_RECORDS) >= $WIDGET_MODEL->getRecordLimit()}
		<button class="btn btn-light float-right btn-sm goToListView"
			data-url="{$WIDGET_MODEL->getUrl()}"
			title="{\App\Language::translate('LBL_MORE', $MODULE_NAME)}">
			<span>{\App\Language::translate('LBL_MORE', $MODULE_NAME)}</span>
		</button>
	{/if}
	<!-- /tpl-Base-Dashboards-ProductsSoldToRenewContents -->
{/strip}
