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
            <div class="col-md-8">
                <h3>{vtranslate('LBL_REL_MODULE_LAYOUT_EDITOR', $QUALIFIED_MODULE)}</h3>
            </div>
            <div class="col-md-4 row">
				<div class="col-md-5">
					<button class="btn btn-default addRelation" type="button">{vtranslate('LBL_ADD_RELATION', $QUALIFIED_MODULE)}</button>
				</div>
                <div class="col-md-7">
                    <select class="select2 form-control" name="layoutEditorRelModules" title="{vtranslate('LBL_ARRANGE_RELATED_TABS')}">
                        {foreach item=MODULE_NAME from=$SUPPORTED_MODULES}
                            <option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SELECTED_MODULE_NAME} selected {/if}>{vtranslate($MODULE_NAME, $QUALIFIED_MODULE)}</option>
                        {/foreach}
                    </select>
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
							{assign var=RELATED_MODULE_NAME value=$MODULE_MODEL->getRelationModuleName()}
							{assign var=RELATED_MODULE_MODEL value=$MODULE_MODEL->getRelationModuleModel()}
							{assign var=RECORD_STRUCTURE_INSTANCE value=Vtiger_RecordStructure_Model::getInstanceForModule($RELATED_MODULE_MODEL)}
							{assign var=RECORD_STRUCTURE value=$RECORD_STRUCTURE_INSTANCE->getStructure()}
							{if $MODULE_MODEL->isActive()}
								{assign var=STATUS value='1'}
							{else}
								{assign var=STATUS value='0'}
							{/if}
							{assign var=SELECTED_FIELDS value=$MODULE_MODEL->getRelationFields(true)}
							<div class="relatedModule mainBlockTable marginBottom10px border1px" data-relation-id="{$MODULE_MODEL->getId()}" data-status="{$STATUS}" style="border-radius: 4px 4px 0px 0px;background: white;">
                                <div class="mainBlockTableHeader">
                                    <div class="relatedModuleLabel mainBlockTableLabel padding10 col-md-6 marginLeftZero">
                                        <a><img src="{vimage_path('drag.png')}" alt="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/></a>&nbsp;&nbsp;
                                        <strong>{vtranslate($MODULE_MODEL->get('label'), $RELATED_MODULE_NAME)}</strong>
                                    </div>
			                        <div class="btn-toolbar pull-right">
			                        	<button class="btn btn-success inActiveRelationModule {if !$MODULE_MODEL->isActive()}hide{/if}"><span class="glyphicon glyphicon-ok icon-white"></span>&nbsp;&nbsp;<strong>{vtranslate('LBL_VISIBLE', $QUALIFIED_MODULE)}</strong></button>&nbsp;
			                        	<button class="btn btn-danger activeRelationModule {if $MODULE_MODEL->isActive()}hide{/if}"><span class="glyphicon glyphicon-remove icon-white"></span>&nbsp;<strong>{vtranslate('LBL_HIDDEN', $QUALIFIED_MODULE)}</strong></button>&nbsp;
										<!-- <button class="close" data-dismiss="modal" title="{vtranslate('LBL_CLOSE')}">x</button> -->
			                        </div>
                                </div>
								<div class="relatedModuleFieldsList mainBlockTableContent">
									<div class="">
										<select data-placeholder="{vtranslate('LBL_ADD_MORE_COLUMNS',$MODULE)}" title="{vtranslate('LBL_ADD_MORE_COLUMNS',$MODULE)}" multiple class="select2_container columnsSelect relatedColumnsList">
				                        	<optgroup label=''>
												{foreach item=SELECTED_FIELD from=$SELECTED_FIELDS}
													{assign var=FIELD_INSTANCE value=$RELATED_MODULE_MODEL->getField($SELECTED_FIELD)}
													{if $FIELD_INSTANCE}
														<option value="{$FIELD_INSTANCE->getId()}" data-field-name="{$FIELD_INSTANCE->getFieldName()}" selected>
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
								</div>
							</div>
						{/foreach}
                    </div>
                </div>
            {/if}
        </div>
        </div>
		<div class="hide addRelationContainer">	
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h3 id="myModalLabel">{vtranslate('LBL_ADD_RELATION', $QUALIFIED_MODULE)}</h3>
			</div>
			<div class="modal-body" >
				<form class="modal-Fields">
					<div class="row">
						<div class="col-md-5 marginLeftZero">{vtranslate('LBL_RELATION_TYPE', $QUALIFIED_MODULE)}:</div>
						<div class="col-md-7">
							<select name="type" title="{vtranslate('LBL_RELATION_TYPE', $QUALIFIED_MODULE)}" class="form-control">
								{foreach from=Settings_LayoutEditor_Module_Model::getRelationsTypes() item=ITEM key=KEY}
									<option value="{$KEY}">{vtranslate($ITEM, $QUALIFIED_MODULE)}</option>
								{/foreach}
							</select>
						</div>
						<br /><br />
						<div class="col-md-5 marginLeftZero">{vtranslate('LBL_RELATION_ACTIONS', $QUALIFIED_MODULE)}:</div>
						<div class="col-md-7 marginTop">
							<select multiple name="actions" title="{vtranslate('LBL_RELATION_ACTIONS', $QUALIFIED_MODULE)}" class="form-control">
								{foreach from=Settings_LayoutEditor_Module_Model::getRelationsActions() item=ITEM key=KEY}
									<option value="{$KEY}">{vtranslate($ITEM, $QUALIFIED_MODULE)}</option>
								{/foreach}
							</select>
						</div>
						<br /><br />
						<div class="col-md-5 marginLeftZero">{vtranslate('LBL_SOURCE_MODULE', $QUALIFIED_MODULE)}:</div>
						<div class="col-md-7 marginTop">
							<select name="source" title="{vtranslate('LBL_SOURCE_MODULE', $QUALIFIED_MODULE)}" class="form-control">
								{foreach item=MODULE_NAME from=$SUPPORTED_MODULES}
									<option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SELECTED_MODULE_NAME} selected {/if}>{vtranslate($MODULE_NAME, $MODULE_NAME)}</option>
								{/foreach}
							</select>
						</div>
						<br /><br />
						<div class="col-md-5 marginLeftZero">{vtranslate('LBL_TARGET_MODULE', $QUALIFIED_MODULE)}:</div>
						<div class="col-md-7 marginTop">
							<select name="target" title="{vtranslate('LBL_TARGET_MODULE', $QUALIFIED_MODULE)}" class="target form-control">
								{foreach item=MODULE_NAME from=$SUPPORTED_MODULES}
									<option value="{$MODULE_NAME}">{vtranslate($MODULE_NAME, $MODULE_NAME)}</option>
								{/foreach}
							</select>
						</div>
						<br /><br />
						<div class="col-md-5 marginLeftZero">{vtranslate('LBL_RELATION_LABLE', $QUALIFIED_MODULE)}:</div>
						<div class="col-md-7">
							<input name="label" title="{vtranslate('LBL_RELATION_LABLE', $QUALIFIED_MODULE)}"  type="text" class="relLabel form-control"/>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button class="btn btn-default" id="closeModal" data-dismiss="modal" aria-hidden="true">{vtranslate('LBL_CLOSE', $QUALIFIED_MODULE)}</button>
				<button class="btn btn-default addButton" data-dismiss="modal" aria-hidden="true" >{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
			</div>
		</div>	
    </div>	
{/strip}
