{strip}
	<div class="row">
		{assign var=col1 value=count($DETAILVIEW_WIDGETS[1])}
		{assign var=col2 value=count($DETAILVIEW_WIDGETS[2])}
		{assign var=col3 value=count($DETAILVIEW_WIDGETS[3])}
		{assign var=span value='12'}
		{if $col2 neq 0}
			{assign var=span value='6'}
		{/if}
		{if $col3 neq 0}
			{assign var=span value='4'}
		{/if}
		{foreach item=WIDGETCOLUMN from=$DETAILVIEW_WIDGETS}
			<div class="col-md-{$span}">
				{foreach key=key item=WIDGET from=$WIDGETCOLUMN}
					{assign var=FILE value='widgets/'|cat:$WIDGET['tpl']}
					{include file=$FILE|@vtemplate_path:$MODULE_NAME}
				{/foreach}
			</div>
		{/foreach}
	</div>
{/strip}
