{strip}
	<div class="">
		{include file=\App\Layout::getTemplatePath('DetailViewBlockLink.tpl', $MODULE_NAME) TYPE_VIEW='SummaryTop'}
	</div>
	<div class="form-row ml-0">
		{assign var=col1 value=count((array)$DETAILVIEW_WIDGETS[1])}
		{assign var=col2 value=count((array)$DETAILVIEW_WIDGETS[2])}
		{assign var=col3 value=count((array)$DETAILVIEW_WIDGETS[3])}
		{assign var=span value='12'}
		{if $col2 neq 0}
			{assign var=span value='6'}
		{/if}
		{if $col3 neq 0}
			{assign var=span value='4'}
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
