{if count($DATA) gt 0 }
	<div>
		<div class="row">
			{if $RELATED_MODULE == 'Products' }
				<div class="col-md-3"><strong>{vtranslate('Product Category', $RELATED_MODULE)}</strong></div>
				<div class="col-md-3"><strong>{vtranslate('Product Name', $RELATED_MODULE)}</strong></div>
				<div class="col-md-3"><strong>{vtranslate('Handler', $RELATED_MODULE)}</strong></div>
				<div class="col-md-3"><strong>{vtranslate('Share with users', $RELATED_MODULE)}</strong></div>
			{/if}
			{if $RELATED_MODULE == 'Services' }
				<div class="col-md-3"><strong>{vtranslate('Service Category', $RELATED_MODULE)}</strong></div>
				<div class="col-md-3"><strong>{vtranslate('Service Name', $RELATED_MODULE)}</strong></div>
				<div class="col-md-3"><strong>{vtranslate('Assigned To', $RELATED_MODULE)}</strong></div>
				<div class="col-md-3"><strong>{vtranslate('Share with users', $RELATED_MODULE)}</strong></div>
			{/if}
		</div>
		{foreach item=ROW from=$DATA}
			<div class="row">
				<div class="col-md-3">{Vtiger_Tree_UIType::getDisplayValueByField($ROW[1],'pscategory',$RELATED_MODULE)}&nbsp;</div>
				<div class="col-md-3"><a class="moduleColor_{$RELATED_MODULE}" href="index.php?module={$RELATED_MODULE}&view=Detail&record={$ROW[0]}">{$ROW[2]}</a></div>
				<div class="col-md-3">
					<span>{$ROW.smownerid}</span>
				</div>
				<div class="col-md-3">
					{foreach key=KEY item=ITEM from=$ROW.shownerid}
						<span>{$ITEM}</span>{if array_key_exists($KEY+1,$ROW.shownerid)},{/if}
					{/foreach}
				</div>
			</div>
		{/foreach}
	</div>
	{if $SHOWMORE}
		<div class="row">
			<div class="pull-right">
				<a onClick="showMoreRecordProductsServices()" >{vtranslate('LBL_MORE',$MODULE_NAME)}..</a>
				<script type='text/javascript'>
					function showMoreRecordProductsServices() {
						jQuery('.related .mainNav[data-reference="ProductsAndServices"]').trigger('click');
					}
				</script>
			</div>
		</div>
	{/if}
{/if}
