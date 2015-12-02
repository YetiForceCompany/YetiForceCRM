{*/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/*}
 
<style>
.fieldDetailsForm .zeroOpacity{
display: none;
}
.visibility{
visibility: hidden;
}
.marginLeft20{
margin-left: 20px;
}
.marginRight20{
	margin-right: 20px;
}
.marginTop5{
	margin-top: 5px;
}
.paddingNoTop20{
padding: 20px 20px 20px 20px;
}
</style>
<div class="" id="widgetsManagementEditorContainer">
		<input id="selectedModuleName" type="hidden" value="{$SELECTED_MODULE_NAME}" />
		<div class="widget_header row">
			<div class="col-md-9">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
				{vtranslate('LBL_WIDGETS_MANAGEMENT_DESCRIPTION', $QUALIFIED_MODULE)}
			</div>
			<div class="col-md-3">
				<div class="pull-right">
					<select class="chzn-select form-control" name="widgetsManagementEditorModules">
						{foreach item=mouleName from=$SUPPORTED_MODULES}
							<option value="{$mouleName}" {if $mouleName eq $SELECTED_MODULE_NAME} selected {/if}>{vtranslate($mouleName, $QUALIFIED_MODULE)}</option>
						{/foreach}
				</select>
				</div>
			</div>
		</div>
		<hr>

<div class="contents tabbable">
	
	<div class="tab-content paddingNoTop10 themeTableColor overflowVisible">
		
	<div class="tab-pane active" id="layoutDashBoards">
		<div class="btn-toolbar marginBottom10px">
			<button type="button" class="btn btn-success addBlockDashBoard"><span class="glyphicon glyphicon-plus"></span>&nbsp;{vtranslate('LBL_ADD_CONDITION', $QUALIFIED_MODULE)}</button>
		</div>
		
		<div id="moduleBlocks">
			<input type="hidden" name="filter_users" value='{Zend_Json::encode($WIDGETS_WITH_FILTER_USERS)}'>
			<input type="hidden" name="filter_restrict" value='{Zend_Json::encode($RESTRICT_FILTER)}'>
			{foreach key=AUTHORIZATION_KEY item=AUTHORIZATION_INFO from=$DASHBOARD_AUTHORIZATION_BLOCKS}
				{assign var=AUTHORIZATION_NAME value=$AUTHORIZATION_INFO.name}
				<div id="block_{$AUTHORIZATION_KEY}" class="editFieldsTable block_{$AUTHORIZATION_KEY} marginBottom10px border1px blockSortable" data-block-id="{$AUTHORIZATION_KEY}" data-sequence="" data-code="{$AUTHORIZATION_INFO.code}" style="border-radius: 4px 4px 0px 0px;background: white;">
					<div class="row layoutBlockHeader no-margin">
						<div class="blockLabel col-sm-5 padding10 ">
							<span class="marginLeft20">
								<strong>{vtranslate($AUTHORIZATION_NAME, $SELECTED_MODULE_NAME)}</strong>
							</span>
						</div>
						<div class="col-sm-7 marginLeftZero pull-right">
							<div class="pull-right btn-toolbar blockActions" style="margin: 4px;">
								<div class="btn-group">
									<button class="btn btn-success addCustomField" type="button"><span class="glyphicon glyphicon-plus"></span>&nbsp;
										<strong>{vtranslate('LBL_ADD_WIDGET', $QUALIFIED_MODULE)}</strong>
									</button>
								</div>
								{if $SELECTED_MODULE_NAME eq 'Home'}
									{assign var=MINILISTWIDGET value=$SPECIAL_WIDGETS['Mini List']}
									<div class="btn-group">
										<button class="btn btn-success addMiniList" type="button"  data-url="{$MINILISTWIDGET->getUrl()}" data-linkid="{$MINILISTWIDGET->get('linkid')}" data-name="{$MINILISTWIDGET->getName()}" data-width="{$MINILISTWIDGET->getWidth()}" data-height="{$MINILISTWIDGET->getHeight()}" data-block-id="{$AUTHORIZATION_KEY}"><span class="glyphicon glyphicon-plus"></span>
											<strong>{vtranslate('LBL_ADD_MINILIST', $QUALIFIED_MODULE)}</strong>
										</button>
									</div>
									{assign var=NOTEBOOKWIDGET value=$SPECIAL_WIDGETS['Notebook']}
									<div class="btn-group">
										<button class="btn btn-success addNotebook" type="button" data-url="{$NOTEBOOKWIDGET->getUrl()}" data-linkid="{$NOTEBOOKWIDGET->get('linkid')}" data-name="{$NOTEBOOKWIDGET->getName()}" data-width="{$NOTEBOOKWIDGET->getWidth()}" data-height="{$NOTEBOOKWIDGET->getHeight()}" data-block-id="{$AUTHORIZATION_KEY}"><span class="glyphicon glyphicon-plus"></span>
											<strong>{vtranslate('LBL_ADD_NOTEBOOK', $QUALIFIED_MODULE)}</strong>
										</button>
									</div>
									<div class="btn-group actions form-control-static">
										<a href="javascript:void(0)" class="deleteCustomBlock" >
											<span class="glyphicon glyphicon-trash alignMiddle" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}"></span>
										</a>
									</div>
								{/if}
							</div>
						</div>
						
					</div>
					<div class="blockFieldsList blockFieldsSortable row" style="padding:5px;min-height: 27px">
						<ul name="sortable1" class="connectedSortable col-md-6" style="list-style-type: none; min-height: 1px;padding:2px;">
							{assign var=WIDGETS_AUTHORIZATION value=$WIDGETS_AUTHORIZATION_INFO.$AUTHORIZATION_KEY}
							{foreach item=WIDGET_MODEL from=$WIDGETS_AUTHORIZATION name=fieldlist}
								{assign var=WIDGET_INFO value=Zend_Json::decode(html_entity_decode($WIDGET_MODEL->get('data')))}
								{assign var=LINKID value=$WIDGET_MODEL->get('linkid')}
								{if $smarty.foreach.fieldlist.index % 2 eq 0}
									<li class="col-md-12">
										<div class="opacity editFieldsWidget marginLeftZero border1px" data-block-id="{$AUTHORIZATION_KEY}" data-field-id="{$WIDGET_MODEL->get('id')}" data-linkid="{$LINKID}" data-sequence="">
											<div class="row padding1per">
												<div class="pull-left " style="word-wrap: break-word;">
													<span class="fieldLabel marginLeft20">{vtranslate($WIDGET_MODEL->getTitle(), $SELECTED_MODULE_NAME)}</span>
												</div>
												<span class="btn-group pull-right marginRight20 actions">
													<a href="javascript:void(0)" class="dropdown-toggle editFieldDetails" data-toggle="dropdown">
														<span class="glyphicon glyphicon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
													</a>
													<div class="basicFieldOperations hide pull-right" style="width : 375px;">
														<form class="form-horizontal fieldDetailsForm" method="POST">
															<div class="modal-header contentsBackground">
																<strong>{vtranslate($WIDGET_MODEL->getTitle(), $SELECTED_MODULE_NAME)}</strong>
																<div class="pull-right"><a href="javascript:void(0)" class='cancel'>X</a></div>
															</div>
															<div class="clearfix">
																<div class="row" style="padding: 5px; ">
																	<div class="col-md-3" style="text-align:center">
																		<input type="checkbox" name="isdefault" class="" {if $WIDGET_MODEL->get('isdefault') eq 1} checked {/if}>
																	</div>	
																	<label class="col-md-9 marginTop5 pull-left" >
																		&nbsp;{vtranslate('LBL_MANDATORY_WIDGET', $QUALIFIED_MODULE)}&nbsp;
																	</label>
																</div>
																{assign var=WIDGET_SIZE value=Zend_Json::decode(html_entity_decode($WIDGET_MODEL->get('size')))}
																<div class="row" style="padding: 5px; ">
																	<div class="col-md-3" style="text-align:center">
																		<select class="width col-md-1 pull-left form-control" name="width" >
																			{foreach from=$SIZE.width item=item}
																				<option value="{$item}" {if $WIDGET_SIZE.width eq $item} selected {/if}>{$item}</option>
																			{/foreach}
																		</select>
																	</div>	
																	<label  class="col-md-9 marginTop5 pull-left" >
																		&nbsp;{vtranslate('LBL_WIDTH', $QUALIFIED_MODULE)}&nbsp;
																	</label>
																</div>
																<div class="row" style="padding: 5px; ">
																	<div class="col-md-3" style="text-align:center">
																		<select class="height col-md-1 pull-left form-control" name="height">
																			{foreach from=$SIZE.height item=item}
																				<option value="{$item}" {if $WIDGET_SIZE.height eq $item} selected {/if}>{$item}</option>
																			{/foreach}
																		</select>
																	</div>
																	<label class="col-md-9 marginTop5 pull-left" >
																		&nbsp;{vtranslate('LBL_HEIGHT', $QUALIFIED_MODULE)}&nbsp;
																	</label>	
																</div>
																{if in_array($WIDGET_MODEL->get('linklabel'), $TITLE_OF_LIMIT) }
																<div class="row" style="padding: 5px; ">
																	<div class="col-md-3" style="text-align:center">
																		<input type="text" name="limit" class="col-md-1 form-control" value="{$WIDGET_MODEL->get('limit')}" >
																	</div>
																	<label class="col-md-9 marginTop5 pull-left" >
																		&nbsp;{vtranslate('LBL_NUMBER_OF_RECORDS_DISPLAYED', $QUALIFIED_MODULE)}&nbsp;
																	</label>
																</div>
																{/if}
															</div>
															{if in_array($WIDGET_MODEL->get('linklabel'),$WIDGETS_WITH_FILTER_USERS)}
																<div class="row form-group">
																	{assign var=WIDGET_OWNERS value=Zend_Json::decode(html_entity_decode($WIDGET_MODEL->get('owners')))}
																	
																		<div class="col-md-5 filter">
																			<select class="widgetFilter form-control" id="owner" name="default_owner">
																				{foreach key=OWNER_NAME item=OWNER_ID from=$FILTER_SELECT_DEFAULT}
																					{if !(is_array($RESTRICT_FILTER[$WIDGET_MODEL->get('linklabel')]) && in_array($OWNER_ID, $RESTRICT_FILTER[$WIDGET_MODEL->get('linklabel')]))}
																						<option value="{$OWNER_ID}" {if $WIDGET_OWNERS.default eq $OWNER_ID} selected {/if} >{vtranslate($OWNER_NAME, $QUALIFIED_MODULE)}</option>
																					{/if}
																				{/foreach}
																			</select>
																		</div>
																		<label class="col-md-6 marginTop5 paddingLRZero form-control-static pull-left" >
																			{vtranslate('LBL_DEFAULT_FILTER', $QUALIFIED_MODULE)}
																		</label>
																	
																	{if !is_array($WIDGET_OWNERS.available)}
																		{$WIDGET_OWNERS.available = array($WIDGET_OWNERS.available)}
																	{/if}
																	<div>
																		<div class="col-md-8 filter">
																			<select class="widgetFilter" multiple="true" name="owners_all" placeholder="{vtranslate('LBL_PLEASE_SELECT_ATLEAST_ONE_OPTION', $QUALIFIED_MODULE)}">
																				{foreach key=OWNER_NAME item=OWNER_ID from=$FILTER_SELECT}
																					{if !(is_array($RESTRICT_FILTER[$WIDGET_MODEL->get('linklabel')]) && in_array($OWNER_ID, $RESTRICT_FILTER[$WIDGET_MODEL->get('linklabel')]))}
																						<option value="{$OWNER_ID}" {if in_array($OWNER_ID, $WIDGET_OWNERS.available)} selected {/if} >{vtranslate($OWNER_NAME, $QUALIFIED_MODULE)}</option>
																					{/if}
																				{/foreach}
																			</select>
																		</div>
																		<label class="col-md-3 marginTop5 paddingLRZero form-control-static pull-left" >
																			{vtranslate('LBL_FILTERS_AVAILABLE', $QUALIFIED_MODULE)}
																		</label>
																	</div>	
																</div>
															{/if}
															<div class="modal-footer">
																<span class="pull-right">
																	<div class="pull-right"><button class='cancel btn btn-warning' type="reset">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</button></div>
																	<button class="btn btn-success saveFieldDetails" data-field-id="{$WIDGET_MODEL->get('id')}" type="submit">
																		<strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong>
																	</button>
																</span>
															</div>
														</form>
													</div>
													<a href="javascript:void(0)" class="deleteCustomField" data-field-id="{$WIDGET_MODEL->get('id')}">
														<span class="glyphicon glyphicon-trash alignMiddle" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}"></span>
													</a>
												</span>
											</div>
										</div>
									</li>
								{/if}
							{/foreach}
						</ul>
						<ul name="sortable2" class="connectedSortable col-md-6" style="list-style-type: none; margin: 0; min-height: 1px;padding:2px;">
							{foreach item=WIDGET_MODEL from=$WIDGETS_AUTHORIZATION name=fieldlist1}
								{assign var=WIDGET_INFO value=Zend_Json::decode(html_entity_decode($WIDGET_MODEL->get('data')))}
								{assign var=LINKID value=$WIDGET_MODEL->get('linkid')}
								{if $smarty.foreach.fieldlist1.index % 2 neq 0}
									<li class="col-md-12">
										<div class="opacity editFieldsWidget marginLeftZero border1px" data-block-id="{$AUTHORIZATION_KEY}" data-field-id="{$WIDGET_MODEL->get('id')}" data-linkid="{$LINKID}" data-sequence="">
											<div class="row padding1per">
												<div class="pull-left" style="">
													<span class="fieldLabel marginLeft20">{vtranslate($WIDGET_MODEL->getTitle(), $SELECTED_MODULE_NAME)}</span>
													
												</div>
												<span class="btn-group pull-right marginRight20 actions">
													<a href="javascript:void(0)" class="dropdown-toggle editFieldDetails" data-toggle="dropdown">
														<span class="glyphicon glyphicon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
													</a>
													<div class="basicFieldOperations hide pull-right" style="width: 375px;">
														<form class="form-horizontal fieldDetailsForm" method="POST">
															<div class="modal-header contentsBackground">
																<strong>{vtranslate($WIDGET_MODEL->getTitle(), $SELECTED_MODULE_NAME)}</strong>
																<div class="pull-right"><a href="javascript:void(0)" class='cancel'>X</a></div>
															</div>
															<div class="clearfix">
																<div class="row" style="padding: 5px; ">
																	<span class="col-md-3" style="text-align:center">
																		<input type="checkbox" name="isdefault" class="" {if $WIDGET_MODEL->get('isdefault') eq 1} checked {/if}>
																	</span>	
																	<label class="col-md-9 marginTop5 pull-left" >
																		&nbsp;{vtranslate('LBL_MANDATORY_WIDGET', $QUALIFIED_MODULE)}&nbsp;
																	</label>
																</div>
																{assign var=WIDGET_SIZE value=Zend_Json::decode(html_entity_decode($WIDGET_MODEL->get('size')))}
																<div class="row" style="padding: 5px; ">
																	<span class="col-md-3" style="text-align:center">
																		<select class="width col-md-1 pull-left form-control" name="width" >
																			{foreach from=$SIZE.width item=item}
																				<option value="{$item}" {if $WIDGET_SIZE.width eq $item} selected {/if}>{$item}</option>
																			{/foreach}
																		</select>
																	</span>	
																	<label  class="col-md-9 marginTop5 pull-left" >
																		&nbsp;{vtranslate('LBL_WIDTH', $QUALIFIED_MODULE)}&nbsp;
																	</label>
																</div>
																<div class="row" style="padding: 5px; ">
																	<span class="col-md-3" style="text-align:center">
																		<select class="height col-md-1 pull-left form-control" name="height">
																			{foreach from=$SIZE.height item=item}
																				<option value="{$item}" {if $WIDGET_SIZE.height eq $item} selected {/if}>{$item}</option>
																			{/foreach}
																		</select>
																	</span>
																	<label class="col-md-9 marginTop5 pull-left" >
																		&nbsp;{vtranslate('LBL_HEIGHT', $QUALIFIED_MODULE)}&nbsp;
																	</label>	
																</div>
																{if in_array($WIDGET_MODEL->get('linklabel'), $TITLE_OF_LIMIT) }
																<div class="row" style="padding: 5px; ">
																	<span class="col-md-3" style="text-align:center">
																		<input type="text" name="limit" class="col-md-1 form-control" value="{$WIDGET_MODEL->get('limit')}" >
																	</span>
																	<label class="col-md-9 marginTop5 pull-left" >
																		&nbsp;{vtranslate('LBL_NUMBER_OF_RECORDS_DISPLAYED', $QUALIFIED_MODULE)}&nbsp;
																	</label>
																</div>
																{/if}
															</div>
															{if in_array($WIDGET_MODEL->get('linklabel'),$WIDGETS_WITH_FILTER_USERS)}
																<div class="row form-group">
																	{assign var=WIDGET_OWNERS value=Zend_Json::decode(html_entity_decode($WIDGET_MODEL->get('owners')))}
																	
																		<div class="col-md-5 filter">
																			<select class="widgetFilter form-control" id="owner" name="default_owner">
																				{foreach key=OWNER_NAME item=OWNER_ID from=$FILTER_SELECT_DEFAULT}
																					{if !(is_array($RESTRICT_FILTER[$WIDGET_MODEL->get('linklabel')]) && in_array($OWNER_ID, $RESTRICT_FILTER[$WIDGET_MODEL->get('linklabel')]))}
																						<option value="{$OWNER_ID}" {if $WIDGET_OWNERS.default eq $OWNER_ID} selected {/if} >{vtranslate($OWNER_NAME, $QUALIFIED_MODULE)}</option>
																					{/if}
																				{/foreach}
																			</select>
																		</div>
																		<label class="col-md-6 marginTop5 paddingLRZero form-control-static pull-left" >
																			{vtranslate('LBL_DEFAULT_FILTER', $QUALIFIED_MODULE)}
																		</label>
																	
																	{if !is_array($WIDGET_OWNERS.available)}
																		{$WIDGET_OWNERS.available = array($WIDGET_OWNERS.available)}
																	{/if}
																	<div class="">
																		<span class="col-md-8 filter">
																			<select class="widgetFilter" multiple="true" name="owners_all" placeholder="{vtranslate('LBL_PLEASE_SELECT_ATLEAST_ONE_OPTION', $QUALIFIED_MODULE)}">
																				{foreach key=OWNER_NAME item=OWNER_ID from=$FILTER_SELECT}
																					{if !(is_array($RESTRICT_FILTER[$WIDGET_MODEL->get('linklabel')]) && in_array($OWNER_ID, $RESTRICT_FILTER[$WIDGET_MODEL->get('linklabel')]))}
																						<option value="{$OWNER_ID}" {if in_array($OWNER_ID, $WIDGET_OWNERS.available)} selected {/if} >{vtranslate($OWNER_NAME, $QUALIFIED_MODULE)}</option>
																					{/if}
																				{/foreach}
																			</select>
																		</span>
																		<label class="col-md-3 marginTop5 form-control-static paddingLRZero pull-left" >
																			{vtranslate('LBL_FILTERS_AVAILABLE', $QUALIFIED_MODULE)}
																		</label>
																	</div>	
																</div>
															{/if}
															<div class="modal-footer">
																<span class="pull-right">
																	<div class="pull-right"><button class='cancel btn btn-warning' type="reset">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</button></div>
																	<button class="btn btn-success saveFieldDetails" data-field-id="{$WIDGET_MODEL->get('id')}" type="submit">
																		<strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong>
																	</button>
																</span>
															</div>
														</form>
													</div>
													<a href="javascript:void(0)" class="deleteCustomField" data-field-id="{$WIDGET_MODEL->get('id')}">
														<span class="glyphicon glyphicon-trash alignMiddle" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}"></span>
													</a>
												</span>
											</div>
										</div>
									</li>
								{/if}
							{/foreach}
						</ul>
					</div>
				</div>
			{/foreach}
		</div>
{* copy elements hide *}	


		<div class="modal addBlockDashBoardModal fade">
			<div class="modal-dialog">
			<div class="modal-content">
			<div class="modal-header contentsBackground">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 class="modal-title">{vtranslate('LBL_ADD_DASHBOARD_BLOCK', $QUALIFIED_MODULE)}</h3>
			</div>
			<form class="form-horizontal addBlockDashBoardForm">
				<div class="modal-body">
					<div class="form-group">
						<div class="col-sm-4 control-label">
							<span>{vtranslate('LBL_CHOISE_AUTHORIZED', $QUALIFIED_MODULE)}</span>
							<span class="redColor">*</span>
						</div>
						<div class="col-sm-6 controls">
							<select class="authorized form-control" name="authorized" style="margin-bottom:0px;" >
								{foreach from=$ALL_AUTHORIZATION item=AUTHORIZED key=AUTHORIZED_CODE}
									<option value="{$AUTHORIZED_CODE}" data-label="{$AUTHORIZED->get('rolename')}">{vtranslate($AUTHORIZED->get('rolename'),$QUALIFIED_MODULE)}</option>
								{/foreach}
							</select>
						</div>
					</div>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
			</form>
		</div>
		</div>
		</div>
		
		<div class="newCustomBlockCopy hide marginBottom10px border1px blockSortable " data-block-id="" data-sequence="" style="border-radius: 4px 4px 0px 0px;background: white">
			<div class="row layoutBlockHeader no-margin">
				<div class="blockLabel col-md-5 padding10 ">
					<span class="marginLeft20">

					</span>
				</div>
				<div class="col-md-6 marginLeftZero" style="float:right !important;">
					
					<div class="pull-right btn-toolbar blockActions" style="margin: 4px;">
						<div class="btn-group">
							<button class="btn btn-default addCustomField hide" type="button"><span class="glyphicon glyphicon-plus"></span>&nbsp;
								<strong>{vtranslate('LBL_ADD_WIDGET', $QUALIFIED_MODULE)}</strong>
							</button>
						</div>
						{if $SELECTED_MODULE_NAME eq 'Home'}
							{assign var=MINILISTWIDGET value=$SPECIAL_WIDGETS['Mini List']}
							<div class="btn-group">
								<button class="btn btn-default addMiniList specialWidget" type="button"  data-url="{$MINILISTWIDGET->getUrl()}" data-linkid="{$MINILISTWIDGET->get('linkid')}" data-name="{$MINILISTWIDGET->getName()}" data-width="{$MINILISTWIDGET->getWidth()}" data-height="{$MINILISTWIDGET->getHeight()}" data-block-id=""><span class="glyphicon glyphicon-plus"></span>&nbsp;
									<strong>{vtranslate('LBL_ADD_MINILIST', $QUALIFIED_MODULE)}</strong>
								</button>
							</div>
							{assign var=NOTEBOOKWIDGET value=$SPECIAL_WIDGETS['Notebook']}
							<div class="btn-group">
								<button class="btn btn-default addNotebook specialWidget" type="button" data-url="{$NOTEBOOKWIDGET->getUrl()}" data-linkid="{$NOTEBOOKWIDGET->get('linkid')}" data-name="{$NOTEBOOKWIDGET->getName()}" data-width="{$NOTEBOOKWIDGET->getWidth()}" data-height="{$NOTEBOOKWIDGET->getHeight()}" data-block-id=""><span class="glyphicon glyphicon-plus"></span>&nbsp;
									<strong>{vtranslate('LBL_ADD_NOTEBOOK', $QUALIFIED_MODULE)}</strong>
								</button>
							</div>
							<div class="btn-group actions form-control-static">
								<a href="javascript:void(0)" class="deleteCustomBlock" >
									<span class="glyphicon glyphicon-trash alignMiddle" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}"></span>
								</a>
							</div>
						{/if}
					</div>
				</div>
			</div>
			<div class="blockFieldsList row blockFieldsSortable" style="padding:5px;min-height: 27px">
				<ul class="connectedSortable col-md-6 ui-sortable" style="list-style-type: none; float: left;min-height:1px;padding:2px;" name="sortable1"></ul>
				<ul class="connectedSortable col-md-6 ui-sortable" style="list-style-type: none; margin: 0;float: left;min-height:1px;padding:2px;" name="sortable2"></ul>
			</div>
		</div>
		
		<div class="modal createFieldModal fade" tabindex="-1">
			<div class="modal-dialog">
			<div class="modal-content">
			
			<div class="modal-header contentsBackground">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 class="modal-title">{vtranslate('LBL_CREATE_CUSTOM_FIELD', $QUALIFIED_MODULE)}</h3>
			</div>
			<form class="form-horizontal createCustomFieldForm"  method="POST">
				<div class="modal-body">
					<div class="form-group">
						<div class="col-md-3 control-label">
							{vtranslate('LBL_SELECT_WIDGET', $QUALIFIED_MODULE)}
						</div>
						<div class="col-md-8 controls">
							<select class="widgets form-control" name="widgets" data-validation-engine="validate[required]"  >
								{foreach from=$WIDGETS item=WIDGET}
									{if $WIDGET->getTitle() eq 'Mini List' || $WIDGET->getTitle() eq 'Notebook'}
										{continue}
									{/if}
									<option value="{$WIDGET->get('linkid')}" data-name="{$WIDGET->get('linklabel')}">{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-3 control-label">
							{vtranslate('LBL_WIDTH', $QUALIFIED_MODULE)}
						</div>
							<div class="col-sm-2">
								<select class="width form-control pull-left" name="width">
								{foreach from=$SIZE.width item=item}
									<option value="{$item}" {if $DEFAULTVALUES.width eq $item} selected {/if}>{$item}</option>
								{/foreach}
							</select>
						</div>
						<div class="col-sm-3 control-label" style="width:135px">
							{vtranslate('LBL_HEIGHT', $QUALIFIED_MODULE)}
						</div>	
						<div class="col-sm-2">
							<select class="height form-control pull-left" name="height"  >
								{foreach from=$SIZE.height item=item}
									<option value="{$item}" {if $DEFAULTVALUES.height eq $item} selected {/if}>{$item}</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-3 control-label">
							{vtranslate('LBL_MANDATORY_WIDGET', $QUALIFIED_MODULE)}
						</div>
						<div class="col-sm-2 controls">
							<input type="checkbox" name="isdefault" >
						</div>
					</div>
					<div class="form-group widgetFilter hide">
						<div class="col-sm-3 control-label">
							{vtranslate('LBL_DEFAULT_FILTER', $QUALIFIED_MODULE)}
						</div>
						<div class="col-sm-8 controls">
							<select class="form-control" id="owner" disabled name="default_owner">
								{foreach key=OWNER_NAME item=OWNER_ID from=$FILTER_SELECT_DEFAULT}
									<option value="{$OWNER_ID}">{vtranslate($OWNER_NAME, $QUALIFIED_MODULE)}</option>
								{/foreach}
							</select>
						</div>	
					</div>	
					<div class="form-group widgetFilter hide">
						<div class="col-sm-3 control-label">
							{vtranslate('LBL_FILTERS_AVAILABLE', $QUALIFIED_MODULE)}
						</div>
						<div class="col-sm-8 controls">
							<select class="form-control owners_all" multiple="true" disabled name="owners_all" placeholder="{vtranslate('LBL_PLEASE_SELECT_ATLEAST_ONE_OPTION', $QUALIFIED_MODULE)}">
								{foreach key=OWNER_NAME item=OWNER_ID from=$FILTER_SELECT}
									<option value="{$OWNER_ID}" selected>{vtranslate($OWNER_NAME, $QUALIFIED_MODULE)}</option>
								{/foreach}
							</select>
						</div>	
					</div>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
			</form>
		</div>
		</div>
		</div>
		
		<li class="newCustomFieldCopy hide col-md-12">
			<div class="marginLeftZero border1px" data-field-id="" data-linkid="" data-sequence="">
				<div class="row padding1per">
					<div class="col-md-10 " style="word-wrap: break-word;">
						<span class="fieldLabel marginLeft20"></span>
					</div>
					<span class="btn-group pull-right marginRight20 actions">
						<a href="javascript:void(0)" class="dropdown-toggle editFieldDetails" data-toggle="dropdown">
							<span class="glyphicon glyphicon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
						</a>
						<div class="basicFieldOperations hide pull-right" style="width: 375px;">
							<form class="form-horizontal fieldDetailsForm" method="POST">
								<div class="modal-header contentsBackground">
								</div>
								<div class="clearfix">
									<div class="row" style="padding: 5px; ">
										<div class="col-md-3" style="text-align:center">
											<input type="checkbox" name="isdefault" >
										</div>	
										<label class="col-md-9 marginTop5 pull-left" >
											&nbsp;{vtranslate('LBL_MANDATORY_WIDGET', $QUALIFIED_MODULE)}&nbsp;
										</label>
									</div>
									<div class="row" style="padding: 5px; ">
										<div class="col-md-3" style="text-align:center">
											<select class="width col-md-1 pull-left form-control" name="width" >
												{foreach from=$SIZE.width item=item}
													<option value="{$item}">{$item}</option>
												{/foreach}
											</select>
										</div>	
										<label  class="col-md-9 marginTop5 pull-left" >
											&nbsp;{vtranslate('LBL_WIDTH', $QUALIFIED_MODULE)}&nbsp;
										</label>
									</div>
									<div class="row" style="padding: 5px; ">
										<div class="col-md-3" style="text-align:center">
											<select class="height col-md-1 pull-left form-control" name="height">
												{foreach from=$SIZE.height item=item}
													<option value="{$item}" >{$item}</option>
												{/foreach}
											</select>
										</div>
										<label class="col-md-9 marginTop5 pull-left" >
											&nbsp;{vtranslate('LBL_HEIGHT', $QUALIFIED_MODULE)}&nbsp;
										</label>	
									</div>
									<div class="row limit" style="padding: 5px; ">
										<div class="col-md-3" style="text-align:center">
											<input type="text" name="limit" class="col-md-1 form-control" value="10" >
										</div>
										<label class="col-md-9 marginTop5 pull-left" >
											&nbsp;{vtranslate('LBL_NUMBER_OF_RECORDS_DISPLAYED', $QUALIFIED_MODULE)}&nbsp;
										</label>
									</div>
								</div>
								<div class="row widgetFilterAll hide form-group">
									<div class="">
										<div class="col-md-5 filter">
											<select class="widgetFilter form-control" id="owner" name="default_owner">
												{foreach key=OWNER_NAME item=OWNER_ID from=$FILTER_SELECT_DEFAULT}
													<option value="{$OWNER_ID}">{vtranslate($OWNER_NAME, $QUALIFIED_MODULE)}</option>
												{/foreach}
											</select>
										</div>
										<label class="col-md-6 marginTop5 paddingLRZero form-control-static pull-left" >
											{vtranslate('LBL_DEFAULT_FILTER', $QUALIFIED_MODULE)}
										</label>
									</div>	
									<div class="">
										<div class="col-md-8 filter">
											<select class="widgetFilter form-control" multiple="true" name="owners_all" placeholder="{vtranslate('LBL_PLEASE_SELECT_ATLEAST_ONE_OPTION', $QUALIFIED_MODULE)}">
												{foreach key=OWNER_NAME item=OWNER_ID from=$FILTER_SELECT}
													<option value="{$OWNER_ID}" selected>{vtranslate($OWNER_NAME, $QUALIFIED_MODULE)}</option>
												{/foreach}
											</select>
										</div>
										<label class="col-md-3 marginTop5 paddingLRZero form-control-static pull-left" >
											{vtranslate('LBL_FILTERS_AVAILABLE', $QUALIFIED_MODULE)}
										</label>
									</div>	
								</div>
								<div class="modal-footer">
									<span class="pull-right">
										<div class="pull-right"><button class='cancel btn btn-warning' type="reset">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</button></div>
										<button class="btn btn-success saveFieldDetails" data-field-id="" type="submit"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
									</span>
								</div>
							</form>
						</div>
						<a href="javascript:void(0)" class="deleteCustomField" data-field-id=""><span class="glyphicon glyphicon-trash alignMiddle" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}"></span></a>
					</span>
				</div>
			</div>
		</li>
	</div>
</div>
</div>
