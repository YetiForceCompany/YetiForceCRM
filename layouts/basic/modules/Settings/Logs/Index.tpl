{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Logs-Index -->
	<div class="settingsIndexPage">
		<div class="form-row d-flex justify-content-lg-start justify-content-xl-center">
			<span class="col-12 col-sm-4 col-md-3 col-lg-3 col-xl-2 settingsSummary">
				<a href="javascript:Settings_Vtiger_Index_Js.showWarnings()">
					<h3 class="summaryCount u-font-size-44px">{$WARNINGS_COUNT}</h3>
                    <p class="summaryText my-3">{\App\Language::translatePluralized('PLU_SYSTEM_WARNINGS', $QUALIFIED_MODULE, $WARNINGS_COUNT)}</p>
				</a>
			</span>
			<span class="col-12 col-sm-4 col-md-3 col-lg-3 col-xl-2 settingsSummary">
				<a href="javascript:Settings_Vtiger_Index_Js.showSecurity()">
					<h3 class="summaryCount u-font-size-44px">{$SECURITY_COUNT}</h3>
                    <p class="summaryText my-3">{\App\Language::translatePluralized('PLU_SECURITY', $QUALIFIED_MODULE, $SECURITY_COUNT)}</p>
				</a>
			</span>
			<span class="col-12 col-sm-4 col-md-3 col-lg-3 col-xl-2 settingsSummary">
				<a href="index.php?module=Users&parent=Settings&view=List">
					<h3 class="summaryCount u-font-size-44px">{$USERS_COUNT}</h3>
					<p class="summaryText my-3">{\App\Language::translatePluralized('PLU_USERS', $QUALIFIED_MODULE, $USERS_COUNT)}</p>
				</a>
			</span>
			<span class="col-12 col-sm-4 col-md-3 col-lg-3 col-xl-2 settingsSummary">
				<a href="index.php?module=Workflows&parent=Settings&view=List">
					<h3 class="summaryCount u-font-size-44px">{$ALL_WORKFLOWS}</h3>
                    <p class="summaryText my-3">{\App\Language::translatePluralized('PLU_WORKFLOWS_ACTIVE',$QUALIFIED_MODULE,$ALL_WORKFLOWS)}</p>
				</a>
			</span>
			<span class="col-12 col-sm-4 col-md-3 col-lg-3 col-xl-2 settingsSummary">
				<a href="index.php?module=ModuleManager&parent=Settings&view=List">
					<h3 class="summaryCount u-font-size-44px">{$ACTIVE_MODULES}</h3>
					<p class="summaryText my-3">{\App\Language::translatePluralized('PLU_MODULES',$QUALIFIED_MODULE,$ACTIVE_MODULES)}</p>
				</a>
			</span>
		</div>
		<br /><br />
		<h3>{\App\Language::translate('LBL_SETTINGS_SHORTCUTS',$QUALIFIED_MODULE)}</h3>
		<hr>
		{assign var=SPAN_COUNT value=1}
		<div class="col-md-12 form-row d-flex justify-content-lg-start justify-content-xl-center m-0" id="settingsShortCutsContainer">
			{foreach item=SETTINGS_SHORTCUT from=$SETTINGS_SHORTCUTS name=shortcuts}
				{include file=\App\Layout::getTemplatePath('SettingsShortCut.tpl', $QUALIFIED_MODULE)}
				{if $SPAN_COUNT==3}
					{$SPAN_COUNT=1} {continue}
				{/if}
					{$SPAN_COUNT=$SPAN_COUNT+1}
			{/foreach}
		</div>
	<!-- /tpl-Settings-Logs-Index -->
{/strip}
