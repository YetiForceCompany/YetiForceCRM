{strip}
<div class="row-fluid">
		<div class="titleBar row-fluid">
			<div class="span8">
				<h3 class="title">{vtranslate($MENU->getLabel(), $QUALIFIED_MODULE)}</h3>
				<p>&nbsp;</p>
			</div>
			<div class="span4">
				<div class="pull-right">
					<div class="btn-toolbar">
						<span class="btn-group">
							<a class="btn btn-mini vtButton" href="javascript:window.history.back();">Back ...</a>
						</span>
					</div>
				</div>
			</div>
		</div>
		<div class="clearfix summaryListView">
			{foreach item=MENU_ITEM from=$MENU_ITEMS}
				<div class="row-fluid item">
					<div class="span1">
						<img src="{vimage_path($MENU_ITEM->get('iconpath'))}" />
					</div>
					<div class="span10">
						<h4>
							<a href="{$MENU_ITEM->getUrl()}">{vtranslate($MENU_ITEM->get('name'), $MENU_ITEM->getModuleName())}</a>
						</h4>
						<p>
							{vtranslate($MENU_ITEM->get('description'), $MENU_ITEM->getModuleName())}
						</p>
					</div>
					<div class="span1"></div>
				</div>
			{/foreach}
		</div>
	</div>
</div>
{/strip}