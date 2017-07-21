{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
{if count($DATA) gt 0 }
	<div style="padding:5px;">
		<div class="row">
			<div class="col-md-4"><strong>{\App\Language::translate('Asset Name', $RELATED_MODULE)}</strong></div>
			<div class="col-md-4"><strong>{\App\Language::translate('Date in Service', $RELATED_MODULE)}</strong></div>
			<div class="col-md-3"><strong>{\App\Language::translate('Parent ID', $RELATED_MODULE)}</strong></div>
		</div>
		{foreach item=ROW from=$DATA}
			<div class="row">
				<div class="col-md-4"><a class="moduleColor_{$RELATED_MODULE}" href="index.php?module={$RELATED_MODULE}&view=Detail&record={$ROW.assetsid}">{$ROW.assetname}</a></div>
				<div class="col-md-4">{DateTimeField::convertToUserFormat($ROW.dateinservice)}</div>
				<div class="col-md-3">
					{if $ROW.parent_id gt 0 }
						{assign var="CRMTYPE" value=vtlib\Functions::getCRMRecordType($ROW.parent_id)}
						<a class="moduleColor_{$CRMTYPE}" href="index.php?module={$CRMTYPE}&view=Detail&record={$ROW.parent_id}" title="{\App\Language::translate($CRMTYPE, $CRMTYPE)}">{vtlib\Functions::getCRMRecordLabel($ROW.parent_id)}</a>
					{/if}
				</div>
			</div>
		{/foreach}
	</div>
{else}
	<span class="noDataMsg">
		{\App\Language::translate('LBL_NO_DATA', $MODULE_NAME)}
	</span>
{/if}
{/strip}
