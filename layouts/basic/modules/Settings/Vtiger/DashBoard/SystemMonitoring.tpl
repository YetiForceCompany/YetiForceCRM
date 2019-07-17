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
			{BOX LABEL='PLU_SYSTEM_WARNINGS' VALUE=$WARNINGS_COUNT HREF='javascript:Settings_Vtiger_Index_Js.showWarnings()'}
			{BOX LABEL='PLU_SECURITY' VALUE=$SECURITY_COUNT HREF='javascript:Settings_Vtiger_Index_Js.showSecurity()'}
			{BOX LABEL='PLU_USERS' VALUE=$USERS_COUNT HREF='index.php?module=Users&parent=Settings&view=List'}
			{BOX LABEL='PLU_WORKFLOWS_ACTIVE' VALUE=$ALL_WORKFLOWS HREF='index.php?module=Workflows&parent=Settings&view=List'}
			{BOX LABEL='PLU_MODULES' VALUE=$ACTIVE_MODULES HREF='index.php?module=ModuleManager&parent=Settings&view=List'}
		</div>
	</div>
{/strip}
