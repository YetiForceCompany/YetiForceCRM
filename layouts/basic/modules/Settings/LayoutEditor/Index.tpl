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
	<div class="tpl-Settings-LayoutEditor-Index" id="layoutEditorContainer">
		<input id="selectedModuleName" type="hidden" value="{$SELECTED_MODULE_NAME}"/>
		<div class="widget_header row">
			<div class="col-md-6">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
				{if isset($SELECTED_PAGE)}
					{App\Language::translate($SELECTED_PAGE->get('description'),$QUALIFIED_MODULE)}
				{/if}
			</div>
			<div class="float-right col-md-6 form-inline">
				<div class="form-group float-right col-md-6">
					<select class="select2 form-control" name="layoutEditorModules">
						{foreach item=MODULE_NAME from=$SUPPORTED_MODULES}
							<option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SELECTED_MODULE_NAME} selected {/if}>{App\Language::translate($MODULE_NAME, $MODULE_NAME)}</option>
						{/foreach}
					</select>
				</div>
				<div class="form-group float-right">
					<div class="btn-group btn-group-toggle" data-toggle="buttons">
						<label class="btn btn-outline-primary {if !$IS_INVENTORY}active{/if}">
							<input class="js-switch--inventory" type="radio" name="options" id="option1"
								   data-js="change"
								   data-value="basic" autocomplete="off"
								   {if !$IS_INVENTORY}checked{/if}
							> {App\Language::translate('LBL_BASIC_MODULE',$QUALIFIED_MODULE)}
						</label>
						<label class="btn btn-outline-primary {if $IS_INVENTORY}active{/if}">
							<input class="js-switch--inventory" type="radio" name="options" id="option2"
								   data-js="change"
								   data-value="advanced" autocomplete="off"
								   {if $IS_INVENTORY}checked{/if}
							> {App\Language::translate('LBL_ADVANCED_MODULE',$QUALIFIED_MODULE)}
						</label>
					</div>
				</div>
			</div>
		</div>
		<hr>
		<div class="contents tabbable">
			<ul class="nav nav-tabs layoutTabs massEditTabs" role="tablist">
				<li class="nav-item"><a class="nav-link active" data-toggle="tab" role="tab"
										href="#detailViewLayout" aria-selected="true">
						<strong>{App\Language::translate('LBL_DETAILVIEW_LAYOUT', $QUALIFIED_MODULE)}</strong></a>
				</li>
				{if $IS_INVENTORY}
					<li class="nav-item inventoryNav"><a class="nav-link" data-toggle="tab" role="tab"
														 href="#inventoryViewLayout"
														 aria-selected="false">
							<strong>{App\Language::translate('LBL_MANAGING_AN_ADVANCED_BLOCK', $QUALIFIED_MODULE)}</strong></a>
					</li>
				{/if}
			</ul>
			<div class="tab-content layoutContent p-3 themeTableColor overflowVisible">
				<div class="tab-pane fade show active" id="detailViewLayout" role="tabpanel"
					 aria-labelledby="detailViewLayout">
					{assign var=FIELD_TYPE_INFO value=$SELECTED_MODULE_MODEL->getAddFieldTypeInfo()}
					{assign var=IS_SORTABLE value=$SELECTED_MODULE_MODEL->isSortableAllowed()}
					{assign var=IS_BLOCK_SORTABLE value=$SELECTED_MODULE_MODEL->isBlockSortableAllowed()}
					{assign var=ALL_BLOCK_LABELS value=[]}
					{if $IS_SORTABLE}
						<div class="btn-toolbar" id="layoutEditorButtons">
							<button class="btn btn-success addButton addCustomBlock" type="button">
								<span class="fas fa-plus"></span>&nbsp;
								<strong>{App\Language::translate('LBL_ADD_CUSTOM_BLOCK', $QUALIFIED_MODULE)}</strong>
							</button>
							<span class="float-right">
                                <button class="btn btn-success saveFieldSequence d-none" type="button">
                                    <strong>{App\Language::translate('LBL_SAVE_FIELD_SEQUENCE', $QUALIFIED_MODULE)}</strong>
                                </button>
                            </span>
						</div>
					{/if}
					<div class="moduleBlocks">
						{foreach key=BLOCK_LABEL_KEY item=BLOCK_MODEL from=$BLOCKS}
							{assign var=FIELDS_LIST value=$BLOCK_MODEL->getLayoutBlockActiveFields()}
							{assign var=BLOCK_ID value=$BLOCK_MODEL->get('id')}
							{$ALL_BLOCK_LABELS[$BLOCK_ID] = $BLOCK_LABEL_KEY}
							<div id="block_{$BLOCK_ID}"
								 class="editFieldsTable block_{$BLOCK_ID} mb-2 border1px {if $IS_BLOCK_SORTABLE} blockSortable{/if}"
								 data-block-id="{$BLOCK_ID}" data-sequence="{$BLOCK_MODEL->get('sequence')}"
								 style="border-radius: 4px;background: white;">
								<div class="row layoutBlockHeader m-0">
									<div class="blockLabel col-md-6 col-sm-6 p-2 ml-0">
										{if $IS_BLOCK_SORTABLE}
											<img class="alignMiddle" src="{\App\Layout::getImagePath('drag.png')}"
												 alt=""/>
											&nbsp;&nbsp;
										{/if}
										<strong>{App\Language::translate($BLOCK_LABEL_KEY, $SELECTED_MODULE_NAME)}</strong>
									</div>
									<div class="col-md-6 col-sm-6 ml-0">
										<div class="float-right btn-toolbar blockActions m-1">
											{if $BLOCK_MODEL->isAddCustomFieldEnabled()}
												<div class="btn-group">
													<button class="btn btn-success addCustomField" type="button">
														<span class="fa fa-plus u-mr-5px"></span><strong>{App\Language::translate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}</strong>
													</button>
												</div>
											{/if}
											{if $BLOCK_MODEL->isActionsAllowed()}
												<div class="btn-group ml-1">
													<button class="btn btn-info dropdown-toggle" data-toggle="dropdown">
														<strong>{App\Language::translate('LBL_ACTIONS', $QUALIFIED_MODULE)}</strong>&nbsp;&nbsp;
														<span class="caret"></span>
													</button>
													<ul class="dropdown-menu float-right">
														<li class="blockVisibility"
															data-visible="{if !$BLOCK_MODEL->isHidden()}1{else}0{/if}"
															data-block-id="{$BLOCK_MODEL->get('id')}">
															<a class="dropdown-item" href="javascript:void(0)">
																<span class="fas fa-check {if $BLOCK_MODEL->isHidden()} d-none {/if}"></span>&nbsp;
																{App\Language::translate('LBL_ALWAYS_SHOW', $QUALIFIED_MODULE)}
															</a>
														</li>
														<li class="inActiveFields">
															<a class="dropdown-item"
															   href="javascript:void(0)">{App\Language::translate('LBL_INACTIVE_FIELDS', $QUALIFIED_MODULE)}</a>
														</li>
														{if $BLOCK_MODEL->isCustomized()}
															<li class="deleteCustomBlock">
																<a class="dropdown-item"
																   href="javascript:void(0)">{App\Language::translate('LBL_DELETE_CUSTOM_BLOCK', $QUALIFIED_MODULE)}</a>
															</li>
														{/if}
													</ul>
												</div>
											{/if}
										</div>
									</div>
								</div>
								<div class="blockFieldsList blockFieldsSortable row m-0 p-1">
									<ul name="{if $SELECTED_MODULE_MODEL->isFieldsSortableAllowed($BLOCK_LABEL_KEY)}sortable1{/if}"
										class="sortTableUl connectedSortable col-md-6">
										{foreach item=FIELD_MODEL from=$FIELDS_LIST name=fieldlist}
											{if $smarty.foreach.fieldlist.index % 2 eq 0}
												<li>
													<div class="opacity editFields ml-0 border1px"
														 data-block-id="{$BLOCK_ID}"
														 data-field-id="{$FIELD_MODEL->get('id')}"
														 data-sequence="{$FIELD_MODEL->get('sequence')}">
														<div class="row p-2">
															{assign var=IS_MANDATORY value=$FIELD_MODEL->isMandatory()}
															<div class="col-2 col-sm-2">&nbsp;
																{if $FIELD_MODEL->isEditable()}
																	<a>
																		<img src="{\App\Layout::getImagePath('drag.png')}"
																			 border="0"
																			 alt="{App\Language::translate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
																	</a>
																{/if}
															</div>
															<div class="col-10 col-sm-10 ml-0 fieldContainer"
																 style="word-wrap: break-word;">
                                                                <span class="fieldLabel">{App\Language::translate($FIELD_MODEL->getFieldLabel(), $SELECTED_MODULE_NAME)}
																	&nbsp;[{$FIELD_MODEL->getName()}]
																	{if $IS_MANDATORY}
																		<span class="redColor">*</span>
																	{/if}
																</span>
																<span class="float-right actions">
																	<input type="hidden"
																		   value="{$FIELD_MODEL->getName()}"
																		   id="relatedFieldValue{$FIELD_MODEL->get('id')}"/>
																	<button class="btn btn-primary btn-sm copyFieldLabel float-right ml-1"
																			data-target="relatedFieldValue{$FIELD_MODEL->get('id')}">
																		<span class="fas fa-copy"
																			  title="{App\Language::translate('LBL_COPY', $QUALIFIED_MODULE)}"></span>
																	</button>
																	{if $FIELD_MODEL->isEditable()}
																		<button class="btn btn-success btn-sm editFieldDetails ml-1">
																			<span class="fas fa-edit"
																				  title="{App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
																		</button>
																	{/if}
																	{if $FIELD_MODEL->isCustomField() eq 'true'}
																		<button class="btn btn-danger btn-sm deleteCustomField ml-1"
																				data-field-id="{$FIELD_MODEL->get('id')}">
																			<span class="fas fa-trash-alt"
																				  title="{App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"></span>
																		</button>
																	{/if}
																	<button class="btn btn-info btn-sm js-context-help ml-1"
																			data-js="click"
																			data-field-id="{$FIELD_MODEL->get('id')}"
																			data-url="index.php?module=LayoutEditor&parent=Settings&view=HelpInfo&field={$FIELD_MODEL->get('id')}&source={$SELECTED_MODULE_NAME}">
																		<span class="fas fa-info-circle"
																			  title="{App\Language::translate('LBL_CONTEXT_HELP', $QUALIFIED_MODULE)}"></span>
																	</button>
																</span>
															</div>
														</div>
													</div>
												</li>
											{/if}
										{/foreach}
									</ul>
									<ul {if $SELECTED_MODULE_MODEL->isFieldsSortableAllowed($BLOCK_LABEL_KEY)}name="sortable2"{/if}
										class="connectedSortable sortTableUl col-md-6">
										{foreach item=FIELD_MODEL from=$FIELDS_LIST name=fieldlist1}
											{if $smarty.foreach.fieldlist1.index % 2 neq 0}
												<li>
													<div class="opacity editFields ml-0 border1px"
														 data-block-id="{$BLOCK_ID}"
														 data-field-id="{$FIELD_MODEL->get('id')}"
														 data-sequence="{$FIELD_MODEL->get('sequence')}">
														<div class="row p-2">
															{assign var=IS_MANDATORY value=$FIELD_MODEL->isMandatory()}
															<div class="col-2 col-sm-2">&nbsp;
																{if $FIELD_MODEL->isEditable()}
																	<a>
																		<img src="{\App\Layout::getImagePath('drag.png')}"
																			 border="0"
																			 alt="{App\Language::translate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
																	</a>
																{/if}
															</div>
															<div class="col-10 col-sm-10 ml-0 fieldContainer"
																 style="word-wrap: break-word;">
																<span class="fieldLabel">{App\Language::translate($FIELD_MODEL->getFieldLabel(), $SELECTED_MODULE_NAME)}
																	&nbsp;[{$FIELD_MODEL->getName()}]
																	{if $IS_MANDATORY}
																		<span class="redColor">*</span>
																	{/if}
																</span>
																<span class="float-right actions">
																	<button class="btn btn-primary btn-sm copyFieldLabel float-right ml-1"
																			data-target="relatedFieldValue{$FIELD_MODEL->get('id')}">
																		<span class="fas fa-copy"
																			  title="{App\Language::translate('LBL_COPY', $QUALIFIED_MODULE)}"></span>
																	</button>
																	<input type="hidden"
																		   value="{$FIELD_MODEL->getName()}"
																		   id="relatedFieldValue{$FIELD_MODEL->get('id')}"/>
																	{if $FIELD_MODEL->isEditable()}
																		<button class="btn btn-success btn-sm editFieldDetails ml-1">
																			<span class="fas fa-edit"
																				  title="{App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}"></span>
																		</button>
																	{/if}
																	{if $FIELD_MODEL->isCustomField() eq 'true'}
																		<button class="btn btn-danger btn-sm deleteCustomField ml-1"
																				data-field-id="{$FIELD_MODEL->get('id')}">
																			<span class="fas fa-trash-alt"
																				  title="{App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"></span>
																		</button>
																	{/if}
																	<button class="btn btn-info btn-sm js-context-help ml-1"
																			data-js="click"
																			data-field-id="{$FIELD_MODEL->get('id')}"
																			data-url="index.php?module=LayoutEditor&parent=Settings&view=HelpInfo&field={$FIELD_MODEL->get('id')}&source={$SELECTED_MODULE_NAME}">
																		<span class="fas fa-info-circle"
																			  title="{App\Language::translate('LBL_CONTEXT_HELP', $QUALIFIED_MODULE)}"></span>
																	</button>
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
					<input type="hidden" class="inActiveFieldsArray" value='{\App\Json::encode($IN_ACTIVE_FIELDS)}'/>
					{include file=\App\Layout::getTemplatePath('NewCustomBlock.tpl', $QUALIFIED_MODULE)}
					{include file=\App\Layout::getTemplatePath('NewCustomField.tpl', $QUALIFIED_MODULE)}
					{include file=\App\Layout::getTemplatePath('AddBlockModal.tpl', $QUALIFIED_MODULE)}
					{include file=\App\Layout::getTemplatePath('CreateFieldModal.tpl', $QUALIFIED_MODULE)}
					{include file=\App\Layout::getTemplatePath('InactiveFieldModal.tpl', $QUALIFIED_MODULE)}
				</div>
				{if $IS_INVENTORY}
					<div class="tab-pane mt-0" id="inventoryViewLayout" role="tabpanel"
						 aria-labelledby="inventoryViewLayout">
						{include file=\App\Layout::getTemplatePath('Inventory.tpl', $QUALIFIED_MODULE)}
					</div>
				{/if}
			</div>
		</div>
	</div>
{/strip}
