{strip}
	<div class="">
		{include file=\App\Layout::getTemplatePath('DetailViewBlockLink.tpl', $MODULE_NAME) TYPE_VIEW='SummaryTop'}
	</div>
	<div class="form-row ml-0">
		{if !empty($DETAILVIEW_WIDGETS[3])}
			{assign var=span value='4'}
		{elseif !empty($DETAILVIEW_WIDGETS[2])}
			{assign var=span value='6'}
		{else}
			{assign var=span value='12'}
		{/if}
		{foreach item=WIDGETCOLUMN from=$DETAILVIEW_WIDGETS}
			<div class="col-md-{$span} pl-0">
				{foreach key=key item=WIDGET from=$WIDGETCOLUMN}
					{assign var=FILE value='Detail/Widget/'|cat:$WIDGET['tpl']}
					{include file=\App\Layout::getTemplatePath($FILE, $MODULE_NAME)}
				{/foreach}
			</div>
		{/foreach}
	</div>
	<div class="">
		{include file=\App\Layout::getTemplatePath('DetailViewBlockLink.tpl', $MODULE_NAME) TYPE_VIEW='SummaryBottom'}
	</div>
{/strip}
