{strip}
	<div class="row">
		<div class="titleBar row">
			<div class="col-md-8">
				<h3 class="title">{\App\Language::translate($MENU->getLabel(), $QUALIFIED_MODULE)}</h3>
				<p>&nbsp;</p>
			</div>
			<div class="col-md-4">
				<div class="float-right">
					<div class="btn-toolbar">
					<span class="btn-group">
						<a class="btn btn-sm vtButton btn-light" role="button" href="javascript:window.history.back();">Back ...</a>
					</span>
					</div>
				</div>
			</div>
		</div>
		<div class="clearfix summaryListView">
			{foreach item=MENU_ITEM from=$MENU_ITEMS}
				<div class="row item">
					<div class="col-md-1">
						<img src="{\App\Layout::getImagePath($MENU_ITEM->get('iconpath'))}"/>
					</div>
					<div class="col-md-10">
						<h4>
							<a href="{$MENU_ITEM->getUrl()}">{\App\Language::translate($MENU_ITEM->get('name'), $MENU_ITEM->getModuleName())}</a>
						</h4>
						<p>
							{\App\Language::translate($MENU_ITEM->get('description'), $MENU_ITEM->getModuleName())}
						</p>
					</div>
					<div class="col-md-1"></div>
				</div>
			{/foreach}
		</div>
	</div>
	</div>
{/strip}
