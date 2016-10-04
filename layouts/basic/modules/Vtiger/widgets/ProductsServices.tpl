{strip}
{if count($DATA) gt 0 }
	<div>
		<div class="row">
			{if $RELATED_MODULE == 'Products' }
				<div class="col-md-6"><strong>{vtranslate('Product Category', $RELATED_MODULE)}</strong></div>
				<div class="col-md-6"><strong>{vtranslate('Product Name', $RELATED_MODULE)}</strong></div>
			{/if}
			{if $RELATED_MODULE == 'Services' }
				<div class="col-md-6"><strong>{vtranslate('Service Category', $RELATED_MODULE)}</strong></div>
				<div class="col-md-6"><strong>{vtranslate('Service Name', $RELATED_MODULE)}</strong></div>
			{/if}
		</div>
		{foreach item=ROW from=$DATA}
			<div class="row">
				<div class="col-md-6">{Vtiger_Tree_UIType::getDisplayValueByField($ROW['category'],'pscategory',$RELATED_MODULE)}&nbsp;</div>
				<div class="col-md-6"><a class="moduleColor_{$RELATED_MODULE}" href="index.php?module={$RELATED_MODULE}&view=Detail&record={$ROW['id']}">{$ROW['name']}</a></div>
			</div>
		{/foreach}
	</div>
	{if $SHOWMORE}
		<div class="row">
			<div class="pull-right">
				<button type="button" class="btn btn-primary btn-xs marginRight10 marginTop10 moreProductsService">{vtranslate('LBL_MORE',$MODULE_NAME)}..</button>
			</div>
		</div>
	{/if}
{/if}
{/strip}
