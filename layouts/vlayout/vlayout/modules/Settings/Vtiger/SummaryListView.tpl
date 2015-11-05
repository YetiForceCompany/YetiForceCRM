{strip}
<div class="row">
		<div class="titleBar row">
			<div class="col-md-8">
				<h3 class="title">{vtranslate($MENU->getLabel(), $QUALIFIED_MODULE)}</h3>
				<p>&nbsp;</p>
			</div>
			<div class="col-md-4">
				<div class="pull-right">
					<div class="btn-toolbar">
						<span class="btn-group">
							<a class="btn btn-xs vtButton btn-default" href="javascript:window.history.back();">Back ...</a>
						</span>
					</div>
				</div>
			</div>
		</div>
		<div class="clearfix summaryListView">
			{foreach item=MENU_ITEM from=$MENU_ITEMS}
				<div class="row item">
					<div class="col-md-1">
						<img src="{vimage_path($MENU_ITEM->get('iconpath'))}" />
					</div>
					<div class="col-md-10">
						<h4>
							<a href="{$MENU_ITEM->getUrl()}">{vtranslate($MENU_ITEM->get('name'), $MENU_ITEM->getModuleName())}</a>
						</h4>
						<p>
							{vtranslate($MENU_ITEM->get('description'), $MENU_ITEM->getModuleName())}
						</p>
					</div>
					<div class="col-md-1"></div>
				</div>
			{/foreach}
		</div>
	</div>
</div>
{/strip}