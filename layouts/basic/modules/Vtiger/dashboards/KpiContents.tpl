{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if count($DATA) gt 0 }
		<input class="widgetData" type="hidden" value='{\App\Purifier::encodeHtml(\App\Json::encode($DATA))}' />
		<div class="widgetDescContainer" style="margin-left: 10px;">
			<h4>Usługa: {$KPILIST[$DSERVICE]}</h4>
			<h4>Typ: {$KPITYPES[$DTYPE]}</h4>
			Przedział czasu: {$DTIME['start']} - {$DTIME['end']}<br />
			Wartość referencyjna: {$DATA['reference_lable']}<br />
			Toleranacja: {$DATA['tolerance']}<br />
			Ilość dancyh: {$DATA['all']}<br /><br />
			<h5>{$DATA['result_lable']}</h5>
		</div>
		<div class="widgetChartContainer" style="height:90px;width:90%"></div>
	{else}
		<span class="noDataMsg">
			{\App\Language::translate('LBL_NO_DATA', $MODULE_NAME)}
		</span>
	{/if}
{/strip}
