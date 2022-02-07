{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if count($DATA) gt 0 }
		<div>
			<div class="row">
				<div class="col p-1"><strong>{\App\Language::translate('Asset Name', $RELATED_MODULE)}</strong></div>
				<div class="col p-1"><strong>{\App\Language::translate('Date in Service', $RELATED_MODULE)}</strong></div>
				<div class="col p-1"><strong>{\App\Language::translate('Parent ID', $RELATED_MODULE)}</strong></div>
			</div>
			{foreach item=ROW from=$DATA}
				<div class="row">
					<div class="col-4 p-1 u-text-ellipsis"><a class="modCT_{$RELATED_MODULE}" href="index.php?module={$RELATED_MODULE}&view=Detail&record={$ROW['id']}">{\App\Purifier::encodeHtml($ROW['assetname'])}</a></div>
					<div class="col-4 p-1 u-text-ellipsis">{DateTimeField::convertToUserFormat($ROW['dateinservice'])}</div>
					<div class="col-4 p-1 u-text-ellipsis">
						{if $ROW['parent_id'] gt 0 }
							{assign var="CRMTYPE" value=\App\Record::getType($ROW['parent_id'])}
							<a class="modCT_{$CRMTYPE}" href="index.php?module={$CRMTYPE}&view=Detail&record={$ROW['parent_id']}" title="{\App\Language::translateSingularModuleName($CRMTYPE)}">{\App\Record::getLabel($ROW['parent_id'])}</a>
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
