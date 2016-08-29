{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
<style type="text/css">
.fieldDetailsForm .zeroOpacity{
display: none;
}
.visibility{
visibility: hidden;
}
.paddingNoTop20{
padding: 0 20px 20px 20px;
}
</style>
{strip}
    <div class="" id="quickCreateEditorContainer">
        <input id="selectedModuleName" type="hidden" value="{$SELECTED_MODULE_NAME}" />
        <div class="widget_header row">
		<div class="col-md-8">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
			{vtranslate('LBL_QUICK_CREATE_EDITOR_DESCRIPTION', $QUALIFIED_MODULE)}
		</div>
		<div class="pull-right col-md-4 h3">
			<select class="select2 form-control" name="quickCreateEditorModules">
				{foreach key=mouleName item=moduleModel from=$SUPPORTED_MODULES}
					{if $moduleModel->isPermitted('EditView')}
						{assign var='quickCreateModule' value=$moduleModel->isQuickCreateSupported()}
						{assign var='singularLabel' value=$moduleModel->getSingularLabelKey()}
						{if $singularLabel == 'SINGLE_Calendar'}
							{assign var='singularLabel' value='LBL_EVENT_OR_TASK'}
						{/if}
						{if $quickCreateModule == '1'}
							<option value="{$mouleName}" {if $mouleName eq $SELECTED_MODULE_NAME} selected {/if}>{vtranslate($singularLabel, $mouleName)}</option>
						{/if}
					{/if}
				{/foreach}
			</select>
		</div>
        </div>
        <div class="contents tabbable">
            <ul class="nav nav-tabs layoutTabs massEditTabs">
                <li class="active"><a data-toggle="tab" href="#detailViewLayout"><strong>{vtranslate('LBL_SEQUENCE', $QUALIFIED_MODULE)}</strong></a></li>
            </ul>
            <div class="tab-content layoutContent paddingNoTop20 themeTableColor overflowVisible">
                <div class="tab-pane active" id="detailViewLayout">
					<div class="btn-toolbar">
						<span class="pull-right">
							<button class="btn btn-success saveFieldSequence visibility"  type="button">
								<strong>{vtranslate('LBL_SAVE_FIELD_SEQUENCE', $QUALIFIED_MODULE)}</strong>
							</button>
						</span>
						<div class="clearfix">
						</div>
					</div>
                    <div id="moduleBlocks">
						{foreach  key=MODULE item=RECORD_STRUCTURE from=$RECORDS_STRUCTURE }
							{assign var='MODULE_NAME' value=$MODULE}
							{if $MODULE == 'Calendar'}
								{assign var='MODULE_NAME' value='Tasks'}
							{/if}
							<div class="editFieldsTable block marginBottom10px border1px blockSortable"  style="border-radius: 4px 4px 0px 0px;background: white;">
                                <div class="row layoutBlockHeader no-margin">
                                    <div class="blockLabel col-md-5 marginLeftZero" style="padding:5px 10px 5px 10px">
                                        {vtranslate($MODULE_NAME, $MODULE)}
                                    </div>
                                </div>
                                <div class="blockFieldsList row no-margin" style="padding:5px;min-height: 27px">
                                    <ul name="sortable1" class="connectedSortable col-md-6" style="list-style-type: none; min-height: 1px;padding:2px;">
                                        {foreach  key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=fieldlist}
                                            {assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
                                            {if $smarty.foreach.fieldlist.index % 2 eq 0}
                                                <li>
                                                    <div class="opacity editFields marginLeftZero border1px"  data-field-id="{$FIELD_MODEL->get('id')}" data-sequence="{$FIELD_MODEL->get('sequence')}">
                                                        <div class="row padding1per">
                                                            {assign var=IS_MANDATORY value=$FIELD_MODEL->isMandatory()}
                                                            <div class="col-sm-1 col-xs-2 col-md-2">&nbsp;
                                                                {if $FIELD_MODEL->isEditable()}
                                                                    <a>
                                                                        <img src="{vimage_path('drag.png')}" border="0" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
                                                                    </a>
                                                                {/if}
                                                            </div>
                                                            <div class="col-sm-11 col-xs-10 col-md-10 marginLeftZero" style="word-wrap: break-word;">
                                                                <span class="fieldLabel">{vtranslate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}&nbsp;
                                                                {if $IS_MANDATORY}<span class="redColor">*</span>{/if}</span>
															</div>
														</div>
													</div>
												</li>
											{/if}
										{/foreach}
									</ul>
									<ul name="sortable2" class="connectedSortable col-md-6" style="list-style-type: none; margin: 0;min-height: 1px;padding:2px;">
										{foreach item=FIELD_MODEL from=$RECORD_STRUCTURE name=fieldlist1}
											{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
											{if $smarty.foreach.fieldlist1.index % 2 neq 0}
												<li>
													<div class="opacity editFields marginLeftZero border1px" data-field-id="{$FIELD_MODEL->get('id')}" data-sequence="{$FIELD_MODEL->get('sequence')}">
														<div class="row padding1per">
															{assign var=IS_MANDATORY value=$FIELD_MODEL->isMandatory()}
															<span class="col-sm-1 col-xs-2 col-md-2">&nbsp;
																{if $FIELD_MODEL->isEditable()}
																	<a>
																		<img src="{vimage_path('drag.png')}" border="0" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
																	</a>
																{/if}
															</span>
															<div class="col-sm-11 col-xs-10 col-md-10 marginLeftZero" style="word-wrap: break-word;">
																<span class="fieldLabel">
																	{if $IS_MANDATORY}
																		<span class="redColor">*</span>
																	{/if}
																	{vtranslate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}&nbsp;
																</span>
																
															</div>
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
				</div>
			</div>
		</div>
{/strip}
