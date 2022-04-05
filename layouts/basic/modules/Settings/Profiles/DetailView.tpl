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
	<!-- tpl-Settings-Profiles-DetailView -->
	<div class="">
		<div class="o-breadcrumb widget_header row">
			<div class="col-md-10">{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}</div>
			<div class="col-md-2">
				<button class="btn btn-info float-right mt-1" type="button" onclick='window.location.href = "{$RECORD_MODEL->getEditViewUrl()}"'>{\App\Language::translate('LBL_EDIT',$QUALIFIED_MODULE)}</button>
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="profileDetailView">
			<div>
				<div class="row">
					<div class="col-md-12">
						<label class="muted"><span class="redColor">*</span>{\App\Language::translate('LBL_PROFILE_NAME', $QUALIFIED_MODULE)}: </label>&nbsp;
						<span name="profilename" id="profilename" value="{$RECORD_MODEL->getName()}"><strong>{$RECORD_MODEL->getName()}</strong></span>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<label class="muted">{\App\Language::translate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}:</strong></label>&nbsp;
						<span name="description" id="description"><strong>{$RECORD_MODEL->getDescription()}</strong></span>
					</div>
				</div>
				{assign var="ENABLE_CLASS_ICON" value="fas fa-check text-success"}
				{assign var="DISABLE_CLASS_ICON" value="fas fa-times text-danger"}
				<div class="summaryWidgetContainer">
					<div class="row ">
						<div class="col-md-3">
							<span class="mr-2 mt-1 {if $RECORD_MODEL->hasGlobalReadPermission()}{$ENABLE_CLASS_ICON}{else}{$DISABLE_CLASS_ICON}{/if}"></span>
							{\App\Language::translate('LBL_VIEW_ALL',$QUALIFIED_MODULE)}
						</div>
						<div class="col-md-9">
							<i class="fas fa-info-circle mt-1"></i>
							<span class="ml-2">{\App\Language::translate('LBL_VIEW_ALL_DESC',$QUALIFIED_MODULE)}</span>
						</div>
					</div>
					<div class="row">
						<div class="col-md-3">
							<span class="mr-2 mt-1 {if $RECORD_MODEL->hasGlobalWritePermission()}{$ENABLE_CLASS_ICON}{else}{$DISABLE_CLASS_ICON}{/if}"></span>
							{\App\Language::translate('LBL_EDIT_ALL',$QUALIFIED_MODULE)}
						</div>
						<div class="col-md-9">
							<i class="fas fa-info-circle mt-1"></i>
							<span class="ml-2">{\App\Language::translate('LBL_EDIT_ALL_DESC',$QUALIFIED_MODULE)}</span>
						</div>
					</div>
				</div>
				<table class="table customTableRWD table-striped table-bordered">
					<thead>
						<tr>
							<th width="27%">
								{\App\Language::translate('LBL_MODULES', $QUALIFIED_MODULE)}
							</th>
							<th data-hide="phone" width="11%">
								<span class="horizontalAlignCenter">
									&nbsp;{\App\Language::translate('LBL_VIEW_PRIVILEGE', $QUALIFIED_MODULE)}
								</span>
							</th>
							<th data-hide="phone" width="12%">
								<span class="horizontalAlignCenter">
									&nbsp;{\App\Language::translate('LBL_CREATE_PRIVILIGE',$QUALIFIED_MODULE)}
								</span>
							</th>
							<th data-hide="phone" width="12%">
								<span class="horizontalAlignCenter">
									&nbsp;{\App\Language::translate('LBL_EDIT_PRIVILIGE',$QUALIFIED_MODULE)}
								</span>
							</th>
							<th data-hide="phone" width="11%">
								<span class="horizontalAlignCenter">{\App\Language::translate('LBL_DELETE_PRIVILIGE', $QUALIFIED_MODULE)}</span>
							</th>
							<th width="39%" nowrap="nowrap">{\App\Language::translate('LBL_FIELD_AND_TOOL_PRVILIGES', $QUALIFIED_MODULE)}</th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$RECORD_MODEL->getModulePermissions() key=TABID item=PROFILE_MODULE}
							<tr data-name="{$PROFILE_MODULE->getName()}" data-id="{$TABID}">
								<td>
									<span class="mr-2 mt-1 {if $RECORD_MODEL->hasModulePermission($PROFILE_MODULE)}{$ENABLE_CLASS_ICON}{else}{$DISABLE_CLASS_ICON}{/if}"></span>
									{\App\Language::translate($PROFILE_MODULE->get('label'), $PROFILE_MODULE->getName())}
								</td>
								{assign var="BASIC_ACTION_ORDER" value=array(2,3,0,1)}
								{foreach from=$BASIC_ACTION_ORDER item=ACTION_ID}
									<td class="text-center">
										{assign var="ACTION_MODEL" value=$ALL_BASIC_ACTIONS[$ACTION_ID]}
										{if $ACTION_MODEL->isModuleEnabled($PROFILE_MODULE)}
											<span class="mr-2 mt-1 {if $RECORD_MODEL->hasModuleActionPermission($PROFILE_MODULE, $ACTION_MODEL)}{$ENABLE_CLASS_ICON}{else}{$DISABLE_CLASS_ICON}{/if}"></span>
										{/if}
									</td>
								{/foreach}
								<td style="border-left: 1px solid #DDD !important;">
									{if $PROFILE_MODULE->getFields() || $PROFILE_MODULE->isUtilityActionEnabled()}
										<div class="row">
											<span class="col-md-4">&nbsp;</span>
											<span class="col-md-4"><button type="button" data-handlerfor="fields" data-togglehandler="{$TABID}-fields" class="btn btn-sm btn-light" style="padding-right: 20px; padding-left: 20px;">
													<i class="fas fa-chevron-down"></i>
												</button></span>
										</div>
									{/if}
								</td>
							</tr>
							<tr class="d-none">
								<td colspan="6" data-toggle-visible="false">
									<div data-togglecontent="{$TABID}-fields">
										{if $PROFILE_MODULE->getFields()}
											<div class="col-md-12">
												<label class="themeTextColor font-x-large float-left"><strong>{\App\Language::translate('LBL_FIELDS',$QUALIFIED_MODULE)}</strong></label>
												<div class="float-right">
													<span class="mini-slider-control ui-slider" data-value="0">
														<a style="margin-top: 4px;" class="ui-slider-handle"></a>
													</span>
													<span style="margin-left:25px;margin-right: 15px;">{\App\Language::translate('LBL_INVISIBLE',$QUALIFIED_MODULE)}</span>&nbsp;
													<span class="mini-slider-control ui-slider" data-value="1">
														<a style="margin-top: 4px;" class="ui-slider-handle"></a>
													</span>
													<span style="margin-left:25px;margin-right: 15px;">{\App\Language::translate('LBL_READ_ONLY',$QUALIFIED_MODULE)}</span>&nbsp;
													<span class="mini-slider-control ui-slider" data-value="2">
														<a style="margin-top: 4px;" class="ui-slider-handle"></a>
													</span>
													<span style="margin-left:25px;margin-right: 15px;">{\App\Language::translate('LBL_WRITE',$QUALIFIED_MODULE)}</span>&nbsp;
												</div>
												<div class="clearfix"></div>
											</div>
											<table class="table table-bordered table-striped col-12">
												{assign var=COUNTER value=0}
												{foreach from=$PROFILE_MODULE->getFields() key=FIELD_NAME item=FIELD_MODEL name="fields"}
													{if $FIELD_MODEL->isActiveField()}
														{assign var="FIELD_ID" value=$FIELD_MODEL->getId()}
														{if $COUNTER % 3 == 0}
															<tr>
															{/if}
															<td>
																{assign var="DATA_VALUE" value=$RECORD_MODEL->getModuleFieldPermissionValue($PROFILE_MODULE, $FIELD_MODEL)}
																{if $DATA_VALUE eq 0}
																	<span class="mini-slider-control ui-slider" data-value="0">
																		<a style="margin-top: 4px;" class="ui-slider-handle"></a>
																	</span>
																{elseif $DATA_VALUE eq 1}
																	<span class="mini-slider-control ui-slider" data-value="1">
																		<a style="margin-top: 4px;" class="ui-slider-handle"></a>
																	</span>
																{else}
																	<span class="mini-slider-control ui-slider" data-value="2">
																		<a style="margin-top: 4px;" class="ui-slider-handle"></a>
																	</span>
																{/if}
																<span style="margin-left: 25px">
																	{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if} {\App\Language::translate($FIELD_MODEL->getFieldLabel(), $PROFILE_MODULE->getName())}
																</span>
															</td>
															{if $smarty.foreach.fields.last OR ($COUNTER+1) % 3 == 0}
															</tr>
														{/if}
														{assign var=COUNTER value=$COUNTER+1}
													{/if}
												{/foreach}
											</table>
										</div>
										</ul>
									{/if}
				</div>
				</td>
				</tr>
				<tr class="d-none">
					<td colspan="6" data-toggle-visible="false">
						<div data-togglecontent="{$TABID}-fields">
							<div class="col-md-12"><label class="themeTextColor font-x-large float-left"><strong>{\App\Language::translate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></label></div>
							<table class="table table-bordered table-striped">
								{assign var=UTILITY_ACTION_COUNT value=0}
								{assign var="ALL_UTILITY_ACTIONS_ARRAY" value=[]}
								{foreach from=$ALL_UTILITY_ACTIONS item=ACTION_MODEL}
									{if $ACTION_MODEL->isModuleEnabled($PROFILE_MODULE)}
										{append var="ALL_UTILITY_ACTIONS_ARRAY" value=$ACTION_MODEL}
									{/if}
								{/foreach}
								{foreach from=$ALL_UTILITY_ACTIONS_ARRAY item=ACTION_MODEL name="actions"}
									{if $smarty.foreach.actions.index % 3 == 0}
										<tr>
										{/if}
										{assign var=ACTION_ID value=$ACTION_MODEL->get('actionid')}
										<td {if $smarty.foreach.actions.last && (($smarty.foreach.actions.index+1) % 3 neq 0)}
												{assign var="index" value=($smarty.foreach.actions.index+1) % 3}
												{assign var="colspan" value=4-$index}
												colspan="{$colspan}"
											{/if}>
											<span class="mr-2 mt-1 {if $RECORD_MODEL->hasModuleActionPermission($PROFILE_MODULE, $ACTION_ID)}{$ENABLE_CLASS_ICON}{else}{$DISABLE_CLASS_ICON}{/if}" data-id="{$ACTION_ID}" data-name="{$ACTION_MODEL->get('actionname')}"></span>{\App\Language::translate($ACTION_MODEL->getName(),$QUALIFIED_MODULE)}
										</td>
										{if $smarty.foreach.actions.last OR ($smarty.foreach.actions.index+1) % 3 == 0}
								</div>
							{/if}
						{/foreach}
						</table>
			</div>
			</td>
			</tr>
		{/foreach}
		</tbody>
		</table>
	</div>
	</div>
	<br />
	</div>
	<!-- /tpl-Settings-Profiles-DetailView -->
{/strip}
