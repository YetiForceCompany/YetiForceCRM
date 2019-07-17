{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-Base-DashBoard-SystemMonitoring">
		{function BOX LABEL='' VALUE='' HREF=''}
			<div class="p-2 d-flex flex-column w-25 shadow m-2">
				<h3 class="summaryCount u-font-size-44px">{$VALUE}</h3>
				<p class="summaryText my-3">{\App\Language::translatePluralized($LABEL, $QUALIFIED_MODULE, $VALUE)}</p>
				{if not empty($HREF)}
					<a href="javascript:Settings_Vtiger_Index_Js.showSecurity()" class="btn btn-dark">LBL_MORE</a>
				{/if}
			</div>
		{/function}
		<div class="d-flex flex-row flex-wrap">
		{foreach from=$SYSTEM_MONITORING item=ITEM}
			{BOX LABEL=$ITEM['LABEL'] VALUE=$ITEM['VALUE'] HREF=$ITEM['HREF']}
		{/foreach}
		</div>
	</div>
{/strip}
