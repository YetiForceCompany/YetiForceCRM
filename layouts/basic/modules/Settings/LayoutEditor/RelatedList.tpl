{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
    <div id="relatedTabOrder">
    <div class="" id="layoutEditorContainer">
        <input id="selectedModuleName" type="hidden" value="{$SELECTED_MODULE_NAME}" />
        <div class="widget_header row">
            <div class="col-md-7">
                {include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
            </div>
            <div class="col-md-5">
				<div class="btn-toolbar">
					<div class="btn-group col-xs-5 pull-right paddingLRZero">
						<select class="select2 form-control layoutEditorRelModules" name="layoutEditorRelModules">
							{foreach item=MODULE_NAME from=$SUPPORTED_MODULES}
								<option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SELECTED_MODULE_NAME} selected {/if}>{vtranslate($MODULE_NAME, $MODULE_NAME)}</option>
							{/foreach}
						</select>
					</div>
					{if AppConfig::developer('CHANGE_RELATIONS')}
						<button class="btn btn-primary pull-right addRelation" type="button">{vtranslate('LBL_ADD_RELATION', $QUALIFIED_MODULE)}</button>
					{/if}	
				</div>
            </div>
        </div>
        <hr>
        <div class="relatedTabModulesList">
            {if empty($RELATED_MODULES)}
                <div class="emptyRelatedTabs">
                    <div class="recordDetails">
                        <p class="textAlignCenter">{vtranslate('LBL_NO_RELATED_INFORMATION',$QUALIFIED_MODULE)}</p>
                    </div>
                </div>
            {else}
                <div class="relatedListContainer">	
					<div class="relatedModulesList">
						{foreach item=MODULE_MODEL from=$RELATED_MODULES}
							{assign var=INVENTORY_FIELD_MODEL value=false}
							{assign var=RELATED_MODULE_NAME value=$MODULE_MODEL->getRelationModuleName()}
							{assign var=RELATED_MODULE_MODEL value=$MODULE_MODEL->getRelationModuleModel()}
							{assign var=RECORD_STRUCTURE_INSTANCE value=Vtiger_RecordStructure_Model::getInstanceForModule($RELATED_MODULE_MODEL)}
							{assign var=RECORD_STRUCTURE value=$RECORD_STRUCTURE_INSTANCE->getStructure()}
							{if $RELATED_MODULE_MODEL->isInventory()}
								{assign var=INVENTORY_FIELD_MODEL value=Vtiger_InventoryField_Model::getInstance($RELATED_MODULE_NAME)}
								{assign var=SELECTED_INVENTORY_FIELDS value=$MODULE_MODEL->getRelationInventoryFields()}
							{/if}
							{if $MODULE_MODEL->isActive()}
								{assign var=STATUS value='1'}
							{else}
								{assign var=STATUS value='0'}
							{/if}
							{assign var=SELECTED_FIELDS value=Settings_LayoutEditor_Module_Model::getRelationFields($MODULE_MODEL->getId())}
							<div class="relatedModule mainBlockTable panel panel-default" data-relation-id="{$MODULE_MODEL->getId()}" data-status="{$STATUS}">
                                <div class="mainBlockTableHeader panel-heading">
									<div class="btn-toolbar btn-group-xs pull-right">
										{if AppConfig::developer('CHANGE_RELATIONS')}
											<button type="button" class="btn btn-danger removeRelation pull-right" title="{vtranslate('LBL_REMOVE_RELATION', $QUALIFIED_MODULE)}">x</button>
										{/if}
										{assign var=FAVORITES value=$MODULE_MODEL->isFavorites()}
			                        	<button type="button" class="btn btn-default addToFavorites" data-state="{$MODULE_MODEL->get('favorites')}">
												<span class="glyphicon glyphicon-star {if !$FAVORITES}hide{/if}" title="{vtranslate('LBL_DEACTIVATE_FAVORITES', $QUALIFIED_MODULE)}"></span>
												<span class="glyphicon glyphicon-star-empty {if $FAVORITES}hide{/if}" title="{vtranslate('LBL_ACTIVATE_FAVORITES', $QUALIFIED_MODULE)}"></span>	
										</button>
			                        	<button type="button" class="btn btn-success inActiveRelationModule{if !$MODULE_MODEL->isActive()} hide{/if}"><span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;<strong>{vtranslate('LBL_VISIBLE', $QUALIFIED_MODULE)}</strong></button>
			                        	<button type="button" class="btn btn-warning activeRelationModule{if $MODULE_MODEL->isActive()} hide{/if}"><span class="glyphicon glyphicon-remove"></span>&nbsp;<strong>{vtranslate('LBL_HIDDEN', $QUALIFIED_MODULE)}</strong></button>
			                        </div>
									<h4 class="panel-title">
										<div class="relatedModuleLabel mainBlockTableLabel">
											<a><img src="{vimage_path('drag.png')}" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/></a>
											<strong>{vtranslate($MODULE_MODEL->get('label'), $RELATED_MODULE_NAME)}</strong>
										</div>
									</h4>
                                </div>
								<div class="relatedModuleFieldsList mainBlockTableContent panel-body paddingBottomZero">
									<div class="form-group">
									<label class="control-label">{vtranslate('LBL_STANDARD_FIELDS',$QUALIFIED_MODULE)}</label>
										<select data-placeholder="{vtranslate('LBL_ADD_MORE_COLUMNS',$MODULE)}" multiple class="select2_container columnsSelect relatedColumnsList">
				                        	<optgroup label=''>
												{foreach item=SELECTED_FIELD from=$SELECTED_FIELDS}
													{assign var=FIELD_INSTANCE value=$RELATED_MODULE_MODEL->getField($SELECTED_FIELD)}
													{if $FIELD_INSTANCE}
														<option value="{$FIELD_INSTANCE->getId()}" data-name="{$FIELD_INSTANCE->getFieldName()}" selected>
															{vtranslate($FIELD_INSTANCE->get('label'), $RELATED_MODULE_NAME)}
												  		</option>
											  		{/if}
												{/foreach}
											</optgroup>
					                        {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
												<optgroup label='{vtranslate($BLOCK_LABEL, $RELATED_MODULE_NAME)}'>
													{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
														{if !in_array($FIELD_MODEL->getId(), $SELECTED_FIELDS)}
															<option value="{$FIELD_MODEL->getId()}" data-field-name="{$FIELD_NAME}">
																{vtranslate($FIELD_MODEL->get('label'), $RELATED_MODULE_NAME)}
													  		</option>
												  		{/if}
													{/foreach}
												</optgroup>
						                    {/foreach}
                     					</select>
                    				</div>
									{if $INVENTORY_FIELD_MODEL}
										{assign var=INVENTORY_FIELDS value=$INVENTORY_FIELD_MODEL->getFields()}
										<div class="form-group">
										<label class="control-label">{vtranslate('LBL_ADVANCED_BLOCK_FIELDS',$QUALIFIED_MODULE)}</label>
											<select data-placeholder="{vtranslate('LBL_ADD_ADVANCED_BLOCK_FIELDS', $QUALIFIED_MODULE)}" multiple class="select2_container relatedColumnsList" data-type="inventory">
												{foreach item=NAME key=SELECTED_FIELD from=$SELECTED_INVENTORY_FIELDS}
													{assign var=FIELD_INSTANCE value=$INVENTORY_FIELDS[$SELECTED_FIELD]}
													{if $FIELD_INSTANCE}
														<option value="{$FIELD_INSTANCE->getColumnName()}" data-name="{$FIELD_INSTANCE->getColumnName()}" selected>
															{vtranslate($FIELD_INSTANCE->get('label'), $RELATED_MODULE_NAME)}
														</option>
													{/if}
												{/foreach}
												{foreach item=FIELD_MODEL from=$INVENTORY_FIELDS}
													{if !in_array($FIELD_MODEL->getColumnName(), $SELECTED_FIELDS)}
														<option value="{$FIELD_MODEL->getColumnName()}" data-field-name="{$FIELD_MODEL->getColumnName()}">
															{vtranslate($FIELD_MODEL->get('label'), $RELATED_MODULE_NAME)}
														</option>
													{/if}
												{/foreach}
											</select>
										</div>
									{/if}
								</div>
							</div>
						{/foreach}
					</div>
				</div>
            {/if}
        </div>
        </div>
		<div class="addRelationContainer modal fade" tabindex="-1">	
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h3 id="myModalLabel" class="modal-title">{vtranslate('LBL_ADD_RELATION', $QUALIFIED_MODULE)}</h3>
					</div>
					<div class="modal-body" >
						<form class="modal-Fields">
							<div class="row form-horizontal">
								<div class="form-group">
									<label class="col-md-4 control-label">{vtranslate('LBL_RELATION_TYPE', $QUALIFIED_MODULE)}:</label>
									<div class="col-md-7">
										<select name="type" class="form-control">
											{foreach from=Settings_LayoutEditor_Module_Model::getRelationsTypes() item=ITEM key=KEY}
												<option value="{$KEY}">{vtranslate($ITEM, $QUALIFIED_MODULE)}</option>
											{/foreach}
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-4 control-label">{vtranslate('LBL_RELATION_ACTIONS', $QUALIFIED_MODULE)}:</label>
									<div class="col-md-7 marginTop">
										<select multiple name="actions" class="form-control">
											{foreach from=Settings_LayoutEditor_Module_Model::getRelationsActions() item=ITEM key=KEY}
												<option value="{$KEY}">{vtranslate($ITEM, $QUALIFIED_MODULE)}</option>
											{/foreach}
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-4 control-label">{vtranslate('LBL_SOURCE_MODULE', $QUALIFIED_MODULE)}:</label>
									<div class="col-md-7 marginTop">
										<select name="source" class="form-control">
											{foreach item=MODULE_NAME from=$SUPPORTED_MODULES}
												<option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SELECTED_MODULE_NAME} selected {/if}>{vtranslate($MODULE_NAME, $MODULE_NAME)}</option>
											{/foreach}
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-4 control-label">{vtranslate('LBL_TARGET_MODULE', $QUALIFIED_MODULE)}:</label>
									<div class="col-md-7 marginTop">
										<select name="target" class="target form-control">
											{foreach item=MODULE_NAME from=$SUPPORTED_MODULES}
												<option value="{$MODULE_NAME}">{vtranslate($MODULE_NAME, $MODULE_NAME)}</option>
											{/foreach}
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-4 control-label">{vtranslate('LBL_RELATION_LABLE', $QUALIFIED_MODULE)}:</label>
									<div class="col-md-7">
										<input name="label"  type="text" class="relLabel form-control"/>
									</div>
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button class="btn btn-success addButton" data-dismiss="modal" aria-hidden="true" >{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
						<button class="btn btn-warning" id="closeModal" data-dismiss="modal" aria-hidden="true">{vtranslate('LBL_CLOSE', $QUALIFIED_MODULE)}</button>
					</div>
				</div>	
			</div>	
		</div>	
    </div>	
{/strip}
