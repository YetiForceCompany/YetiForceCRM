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
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="myModalLabel">
								<span class="glyphicon glyphicon-warning-sign redColor" aria-hidden="true"></span>&nbsp;&nbsp;
								{App\Language::translate('LBL_SYSTEM_WARNINGS','Settings:Vtiger')}
							</h4>
						</div>
						<div class="modal-body">
							<div class="warnings">
								{foreach from=$WARNINGS item=ITEM}
									<div class="warning hide" data-id="{get_class($ITEM)}">
										{if $ITEM->getTpl()}
											{include file=$ITEM->getTpl()}
										{else}
											<h3 class="marginTB3">
												{App\Language::translate($ITEM->getTitle(),'Settings:SystemWarnings')}
											</h3>
											<p>
												{$ITEM->getDescription()}
											</p>
											<div class="pull-right">
												{if $ITEM->getStatus() != 1 && $ITEM->getPriority() < 8}
													<button type="button" class="btn btn-warning ajaxBtn" data-params="{$ITEM->getStatus()}">
														<span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
														&nbsp;&nbsp;{App\Language::translate('BTN_SET_IGNORE','Settings:SystemWarnings')}
													</button>&nbsp;&nbsp;
												{/if}
												{if $ITEM->getLink()}
													<a class="btn btn-success" href="{$ITEM->getLink()}" target="_blank">
														<span class="glyphicon glyphicon-link" aria-hidden="true"></span>
														&nbsp;&nbsp;{$ITEM->linkTitle}
													</a>&nbsp;&nbsp;
												{/if}
												<button type="button" class="btn btn-danger cancel">
													<span class="glyphicon glyphicon-ban-circle" aria-hidden="true"></span>
													&nbsp;&nbsp;{App\Language::translate('LBL_REMIND_LATER','Settings:SystemWarnings')}
												</button>
											</div>
										{/if}
										<div class="clearfix"></div>
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
		<div class="">
			<span class="col-md-3 settingsSummary">
				<a href="index.php?module=Users&parent=Settings&view=List">
					<h2 style="font-size: 44px" class="summaryCount">{$USERS_COUNT}</h2> 
					<p class="summaryText" style="margin-top:20px;">{vtranslate('LBL_ACTIVE_USERS',$QUALIFIED_MODULE)}</p> 
				</a>
			</span>
			<span class="col-md-3 settingsSummary">
				<a href="javascript:Settings_Vtiger_Index_Js.showWarnings()">
					<h2 style="font-size: 44px" class="summaryCount">{$WARNINGS_COUNT}</h2> 
                    <p class="summaryText" style="margin-top:20px;">{vtranslate('LBL_SYSTEM_WARNINGS',$QUALIFIED_MODULE)}</p> 
				</a>
			</span>
			<span class="col-md-3 settingsSummary">
				<a href="index.php?module=Workflows&parent=Settings&view=List">
					<h2 style="font-size: 44px" class="summaryCount">{$ALL_WORKFLOWS}</h2> 
                    <p class="summaryText" style="margin-top:20px;">{vtranslate('LBL_WORKFLOWS_ACTIVE',$QUALIFIED_MODULE)}</p> 
				</a>
			</span>
			<span class="col-md-3 settingsSummary">
				<a href="index.php?module=ModuleManager&parent=Settings&view=List">
					<h2 style="font-size: 44px" class="summaryCount">{$ACTIVE_MODULES}</h2> 
					<p class="summaryText" style="margin-top:20px;">{vtranslate('LBL_MODULES',$QUALIFIED_MODULE)}</p>
				</a>
			</span>
		</div>
		<br><br>
		<h3>{vtranslate('LBL_SETTINGS_SHORTCUTS',$QUALIFIED_MODULE)}</h3>
		<hr>
		{assign var=SPAN_COUNT value=1}
		<div class="row">
			<div class="col-md-1">&nbsp;</div>
			<div id="settingsShortCutsContainer" class="col-md-11">
				<div  class="row">
					{foreach item=SETTINGS_SHORTCUT from=$SETTINGS_SHORTCUTS name=shortcuts}
						{include file='SettingsShortCut.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
					{if $SPAN_COUNT==3}</div>{$SPAN_COUNT=1}{if not $smarty.foreach.shortcuts.last}<div class="row">{/if}{continue}{/if}
					{$SPAN_COUNT=$SPAN_COUNT+1}
				{/foreach}
			</div>
		</div>
	</div>
{/strip}
