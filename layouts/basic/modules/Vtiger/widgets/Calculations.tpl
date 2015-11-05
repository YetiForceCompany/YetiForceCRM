{if count($DATA) gt 0 }
	<div>
		<div class="row">
			<div class="col-md-3"><strong>{vtranslate('Title', $RELATED_MODULE)}</strong></div>
			<div class="col-md-4"><strong>{vtranslate('CalculationStatus', $RELATED_MODULE)}</strong></div>
			{if $SOURCE_MODULE == 'Accounts' }
				<div class="col-md-3"><strong>{vtranslate('Potential', $RELATED_MODULE)}</strong></div>
			{/if}
			{if $SOURCE_MODULE == 'Potentials' }
				<div class="col-md-3"><strong>{vtranslate('Assigned To', $RELATED_MODULE)}</strong></div>
			{/if}
		</div>
		{foreach item=ROW from=$DATA}
			<div class="row">
				<div class="col-md-3"><a class="moduleColor_{$RELATED_MODULE}" href="index.php?module={$RELATED_MODULE}&view=Detail&record={$ROW.calculationsid}">{$ROW.name}</a></div>
				<div class="col-md-4">{vtranslate($ROW.calculationsstatus, $RELATED_MODULE)}</div>
				<div class="col-md-3">
					{if $ROW.potentialid gt 0 }
						{assign var="CRMTYPE" value=Vtiger_Functions::getCRMRecordType($ROW.potentialid)}
						<a class="moduleColor_{$CRMTYPE}" href="index.php?module={$CRMTYPE}&view=Detail&record={$ROW.potentialid}" title="{vtranslate($CRMTYPE, $CRMTYPE)}">{Vtiger_Functions::getCRMRecordLabel($ROW.potentialid)}</a>
					{/if}
					{if $ROW.smownerid gt 0 }
						{Vtiger_Functions::getOwnerRecordLabel($ROW.smownerid)}
					{/if}
				</div>
			</div>
		{/foreach}
	</div>
{/if}
