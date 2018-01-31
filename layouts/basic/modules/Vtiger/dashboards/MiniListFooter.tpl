{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="widgetFooterContent">
		<div class="row no-margin">
			{if $OWNER eq false}
				{assign var="MINILIST_WIDGET_RECORDS" value=[]}
			{else}
				{assign var="MINILIST_WIDGET_RECORDS" value=$MINILIST_WIDGET_MODEL->getRecords($OWNER)}
			{/if}
			<div class="col-md-4">
				<button class="btn btn-xs btn-light recordCount" data-url="{\App\Purifier::encodeHtml($MINILIST_WIDGET_MODEL->getTotalCountURL($OWNER))}">
					<span class="fab fa-gitter" title="{\App\Language::translate('LBL_WIDGET_FILTER_TOTAL_COUNT_INFO')}"></span>
					<a class="float-left hide" href="{\App\Purifier::encodeHtml($MINILIST_WIDGET_MODEL->getListViewURL($OWNER))}"><span class="count badge float-left"></span></a>
				</button>
			</div>
			{if count($MINILIST_WIDGET_RECORDS) >= $MINILIST_WIDGET_MODEL->getRecordLimit()}
				<div class="col-md-8">
					<a class="btn btn-xs btn-primary float-right" href="{\App\Purifier::encodeHtml($MINILIST_WIDGET_MODEL->getListViewURL($OWNER))}">
						<span class="glyphicon glyphicon-align-justify" aria-hidden="true"></span>&nbsp;&nbsp;
						{\App\Language::translate('LBL_MORE')}
					</a>
				</div>
			{else}&nbsp;{/if}
		</div>
	</div>
{/strip}
