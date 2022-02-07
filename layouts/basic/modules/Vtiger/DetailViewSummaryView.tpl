{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<!-- tpl-Base-DetailViewSummaryView -->
{strip}
	<div>
		{include file=\App\Layout::getTemplatePath('DetailViewBlockLink.tpl', $MODULE_NAME) TYPE_VIEW='SummaryTop'}
	</div>
	<div class="o-detail-widgets row no-gutters mx-n1">
		{if !empty($DETAILVIEW_WIDGETS[3])}
			{assign var=span value='4'}
		{elseif !empty($DETAILVIEW_WIDGETS[2])}
			{assign var=span value='6'}
		{else}
			{assign var=span value='12'}
		{/if}
		{foreach item=WIDGETCOLUMN from=$DETAILVIEW_WIDGETS}
			<div class="col-md-{$span} px-1">
				{foreach key=key item=WIDGET from=$WIDGETCOLUMN}
					{assign var=FILE value='Detail/Widget/'|cat:$WIDGET['tpl']}
					{include file=\App\Layout::getTemplatePath($FILE, $MODULE_NAME)}
				{/foreach}
			</div>
		{/foreach}
	</div>
	<div>
		{include file=\App\Layout::getTemplatePath('DetailViewBlockLink.tpl', $MODULE_NAME) TYPE_VIEW='SummaryBottom'}
	</div>
	<!-- /tpl-Base-DetailViewSummaryView -->
{/strip}
