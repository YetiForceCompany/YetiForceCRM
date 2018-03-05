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
													<a class="btn btn-success ml-1" href="{$ITEM->getLink()}" target="_blank">
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
		<div class="row center-block">
			<span class="col-5 col-sm-4 col-md-3 col-lg-2 settingsSummary">
				<a href="javascript:Settings_Vtiger_Index_Js.showWarnings()">
					<h2 style="font-size: 44px" class="summaryCount">{$WARNINGS_COUNT}</h2>
                    <p class="summaryText" style="margin-top:20px;">{\App\Language::translatePluralized('PLU_SYSTEM_WARNINGS', $QUALIFIED_MODULE, $WARNINGS_COUNT)}</p>
				</a>

			</span>
			<span class="col-5 col-sm-4 col-md-3 col-lg-2 settingsSummary">
				<a href="javascript:Settings_Vtiger_Index_Js.showSecurity()">
					<h2 style="font-size: 44px" class="summaryCount">{$SECURITY_COUNT}</h2>
					<p class="summaryText" style="margin-top:20px;">{\App\Language::translatePluralized('PLU_SECURITY', $QUALIFIED_MODULE, $SECURITY_COUNT)}</p>
				</a>
			</span>
			<span class="col-5 col-sm-4 col-md-3 col-lg-2 settingsSummary">
				<a href="index.php?module=Users&parent=Settings&view=List">
					<h2 style="font-size: 44px" class="summaryCount">{$USERS_COUNT}</h2>
					<p class="summaryText" style="margin-top:20px;">{\App\Language::translatePluralized('PLU_USERS', $QUALIFIED_MODULE, $USERS_COUNT)}</p>
				</a>
			</span>
			<span class="col-5 col-sm-4 col-md-3 col-lg-2 settingsSummary">
				<a href="index.php?module=Workflows&parent=Settings&view=List">
					<h2 style="font-size: 44px" class="summaryCount">{$ALL_WORKFLOWS}</h2>
                    <p class="summaryText" style="margin-top:20px;">{\App\Language::translatePluralized('PLU_WORKFLOWS_ACTIVE',$QUALIFIED_MODULE,$ALL_WORKFLOWS)}</p>
				</a>
			</span>
			<span class="col-5 col-sm-4 col-md-3 col-lg-2 settingsSummary">
				<a href="index.php?module=ModuleManager&parent=Settings&view=List">
					<h2 style="font-size: 44px" class="summaryCount">{$ACTIVE_MODULES}</h2>
					<p class="summaryText" style="margin-top:20px;">{\App\Language::translatePluralized('PLU_MODULES',$QUALIFIED_MODULE,$ACTIVE_MODULES)}</p>
				</a>
			</span>
		</div>
		<br /><br />
		<h3>{\App\Language::translate('LBL_SETTINGS_SHORTCUTS',$QUALIFIED_MODULE)}</h3>
		<hr>
		{assign var=SPAN_COUNT value=1}
		<div class="row">
			<div class="col-md-1">&nbsp;</div>
			<div id="settingsShortCutsContainer" class="col-md-11">
				<div  class="row">
					{foreach item=SETTINGS_SHORTCUT from=$SETTINGS_SHORTCUTS name=shortcuts}
						{include file=\App\Layout::getTemplatePath('SettingsShortCut.tpl', $QUALIFIED_MODULE)}
					{if $SPAN_COUNT==3}</div>{$SPAN_COUNT=1}{if not $smarty.foreach.shortcuts.last}<div class="row">{/if}{continue}{/if}
					{$SPAN_COUNT=$SPAN_COUNT+1}
				{/foreach}
			</div>
		</div>
	</div>
{/strip}
