{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	{if $WARNINGS}
		<div id="systemWarningAletrs">
			<div class="modal fade static">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">
								<span class="fas fa-exclamation-circle redColor mr-1"></span>
								{App\Language::translate('LBL_SYSTEM_WARNINGS','Settings:Vtiger')}
							</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							  <span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<div class="warnings">
								{foreach from=$WARNINGS item=ITEM}
									<div class="warning d-none clearfix" data-id="{get_class($ITEM)}">
										{if $ITEM->getTpl()}
											{include file=$ITEM->getTpl()}
										{else}
											<h3 class="marginTB3">
												{App\Language::translate($ITEM->getTitle(),'Settings:SystemWarnings')}
											</h3>
											<p>
												{$ITEM->getDescription()}
											</p>
											<div class="float-right">
												{if $ITEM->getStatus() != 1 && $ITEM->getPriority() < 8}
													<button class="btn btn-warning ajaxBtn" type="button" data-params="{$ITEM->getStatus()}">
														<span class="fas fa-minus-circle mr-1"></span>
														{App\Language::translate('BTN_SET_IGNORE','Settings:SystemWarnings')}
													</button>
												{/if}
												{if $ITEM->getLink()}
													<a class="btn btn-success ml-1" href="{$ITEM->getLink()}" target="_blank"
													   rel="noreferrer noopener">
														<span class="fas fa-link mr-1"></span>
														{$ITEM->linkTitle}
													</a>
												{/if}
												<button class="btn btn-danger cancel ml-1" type="button">
													<span class="fas fa-ban mr-1"></span>
													{App\Language::translate('LBL_REMIND_LATER','Settings:SystemWarnings')}
												</button>
											</div>
										{/if}
									</div>
								{/foreach}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	{/if}
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
{/strip}
