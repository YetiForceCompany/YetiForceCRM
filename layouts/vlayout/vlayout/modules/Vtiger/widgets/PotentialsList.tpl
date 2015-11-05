{if count($DATA) gt 0 }
	<div>
		<div class="row">
			<div class="col-md-3"><strong>{vtranslate('Potential Name', $RELATED_MODULE)}</strong></div>
			<div class="col-md-4"><strong>{vtranslate('Sales Stage', $RELATED_MODULE)}</strong></div>
			<div class="col-md-3"><strong>{vtranslate('Assigned To', $RELATED_MODULE)}</strong></div>
		</div>
		{foreach item=ROW from=$DATA}
			<div class="row">
				<div class="col-md-3"><a class="moduleColor_{$RELATED_MODULE}" href="index.php?module={$RELATED_MODULE}&view=Detail&record={$ROW.potentialid}">{$ROW.potentialname}</a></div>
				<div class="col-md-4">{vtranslate($ROW.sales_stage, $RELATED_MODULE)}</div>
				<div class="col-md-3">{Vtiger_Functions::getOwnerRecordLabel($ROW.smownerid)}</div>
			</div>
		{/foreach}
	</div>
{/if}
