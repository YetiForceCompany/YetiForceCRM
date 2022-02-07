{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-RelatedList -->
	<div id="relatedTabOrder">
		<div class="" id="layoutEditorContainer">
			<input id="selectedModuleName" type="hidden" value="{$SELECTED_MODULE_NAME}" />
			<div class="o-breadcrumb widget_header row align-items-lg-center">
				<div class="col-md-7">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
				</div>
				<div class="col-md-5">
					<div class="btn-toolbar justify-content-end form-row">
						{if \Config\Developer::$CHANGE_RELATIONS}
							<button class="btn btn-primary float-right addRelation mr-2" type="button">
								<span class="fas fa-plus"></span>&nbsp;&nbsp;
								{\App\Language::translate('LBL_ADD_RELATION', $QUALIFIED_MODULE)}
							</button>
						{/if}
						<div class="btn-group col-5 float-right px-0">
							<select class="select2 form-control layoutEditorRelModules" name="layoutEditorRelModules">
								{foreach item=MODULE_NAME from=$SUPPORTED_MODULES}
									<option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SELECTED_MODULE_NAME} selected {/if}>{\App\Language::translate($MODULE_NAME, $MODULE_NAME)}</option>
								{/foreach}
							</select>
						</div>
					</div>
				</div>
			</div>
			<hr>
			<div class="relatedTabModulesList">
				{if empty($RELATED_MODULES)}
					<div class="emptyRelatedTabs">
						<div class="recordDetails">
							<p class="textAlignCenter">{\App\Language::translate('LBL_NO_RELATED_INFORMATION',$QUALIFIED_MODULE)}</p>
						</div>
					</div>
				{else}
					<div class="relatedListContainer">
						<div class="relatedModulesList">
							{foreach item=MODULE_MODEL from=$RELATED_MODULES}
								{assign var=INVENTORY_MODEL value=false}
								{assign var=RELATED_MODULE_NAME value=$MODULE_MODEL->getRelationModuleName()}
								{assign var=RELATED_MODULE_MODEL value=$MODULE_MODEL->getRelationModuleModel()}
								{assign var=RECORD_STRUCTURE_INSTANCE value=Vtiger_RecordStructure_Model::getInstanceForModule($RELATED_MODULE_MODEL)}
								{assign var=RECORD_STRUCTURE value=$RECORD_STRUCTURE_INSTANCE->getStructure()}
								{if $RELATED_MODULE_MODEL->isInventory()}
									{assign var=INVENTORY_MODEL value=Vtiger_Inventory_Model::getInstance($RELATED_MODULE_NAME)}
									{assign var=SELECTED_INVENTORY_FIELDS value=$MODULE_MODEL->getRelationInventoryFields()}
								{/if}
								{if $MODULE_MODEL->isActive()}
									{assign var=STATUS value='1'}
								{else}
									{assign var=STATUS value='0'}
								{/if}
								{assign var=SELECTED_FIELDS value=App\Field::getFieldsFromRelation($MODULE_MODEL->getId())}
								<div class="relatedModule mainBlockTable card mb-2" data-relation-id="{$MODULE_MODEL->getId()}" data-status="{$STATUS}">
									<div class="mainBlockTableHeader card-header d-flex justify-content-between align-items-center px-2 py-1">
										<h5 class="card-title my-0">
											<div class="relatedModuleLabel mainBlockTableLabel">
												<a>
													<img class="align-baseline" src="{\App\Layout::getImagePath('drag.png')}" title="{\App\Language::translate('LBL_DRAG',$QUALIFIED_MODULE)}" />
												</a>
												<span class="yfm-{$RELATED_MODULE_NAME} ml-2 mr-2"></span>
												{\App\Language::translate($MODULE_MODEL->get('label'), $RELATED_MODULE_NAME)}{if \Config\Developer::$CHANGE_RELATIONS} ({$MODULE_MODEL->get('name')}){/if}
											</div>
										</h5>
										<div class="btn-toolbar btn-group-xs">
											{assign var=FAVORITES value=$MODULE_MODEL->isFavorites()}
											<button class="btn btn-sm btn-outline-secondary addToFavorites mr-1" type="button" data-state="{$MODULE_MODEL->get('favorites')}">
												<span class="fas fa-star {if !$FAVORITES}d-none{/if}" title="{\App\Language::translate('LBL_DEACTIVATE_FAVORITES', $QUALIFIED_MODULE)}"></span>
												<span class="far fa-star {if $FAVORITES}d-none{/if}" title="{\App\Language::translate('LBL_ACTIVATE_FAVORITES', $QUALIFIED_MODULE)}"></span>
											</button>
											<button class="btn btn-sm btn-success inActiveRelationModule{if !$MODULE_MODEL->isActive()} d-none{/if} mr-1" type="button">
												<span class="fas fa-check"></span>&nbsp;&nbsp;<strong>{\App\Language::translate('LBL_VISIBLE', $QUALIFIED_MODULE)}</strong>
											</button>
											<button class="btn btn-sm btn-warning activeRelationModule{if $MODULE_MODEL->isActive()} d-none{/if} mr-1" type="button">
												<span class="fas fa-times"></span>&nbsp;<strong>{\App\Language::translate('LBL_HIDDEN', $QUALIFIED_MODULE)}</strong>
											</button>
											{if \Config\Developer::$CHANGE_RELATIONS}
												<button type="button"
													class="btn btn-sm btn-danger removeRelation float-right"
													title="{\App\Language::translate('LBL_REMOVE_RELATION', $QUALIFIED_MODULE)}">
													<span class="fas fa-times"></span>
												</button>
											{/if}
										</div>
									</div>
									<div class="relatedModuleFieldsList mainBlockTableContent card-body">
										<div class="form-horizontal  js-related-column-list-container" data-js="container">
											<div class="form-group row">
												<label class="col-sm-2 col-form-label text-right">{\App\Language::translate('LBL_RELATED_VIEW_TYPE',$QUALIFIED_MODULE)}
													:</label>
												<div class="col-sm-10">
													<select data-placeholder="{\App\Language::translate('LBL_RELATED_VIEW_TYPE_DESC',$MODULE)}"
														multiple="multiple" data-prompt-position="topLeft"
														class="form-control select2_container relatedViewType validate[required]">
														{foreach key=KEY item=NAME from=Settings_LayoutEditor_Module_Model::getRelatedViewTypes()}
															<option value="{$KEY}" {if $MODULE_MODEL->isRelatedViewType($KEY)}selected{/if}>
																{\App\Language::translate($NAME, $QUALIFIED_MODULE)}
															</option>
														{/foreach}
													</select>
												</div>
											</div>
										</div>
										<div class="form-horizontal js-related-column-list-container" data-js="container">
											<div class="form-group row">
												<label class="col-sm-2 col-form-label text-right">
													{\App\Language::translate('LBL_STANDARD_FIELDS',$QUALIFIED_MODULE)}:
												</label>
												<div class="col-sm-10">
													<select data-placeholder="{\App\Language::translate('LBL_ADD_MORE_COLUMNS',$MODULE)}" multiple="multiple"
														class="form-control select2_container columnsSelect js-related-column-list" data-select-cb="registerSelectSortable"
														data-js="sortable | change | select2">
														<optgroup label=''>
															{foreach item=SELECTED_FIELD from=$SELECTED_FIELDS}
																{assign var=FIELD_INSTANCE value=$RELATED_MODULE_MODEL->getField($SELECTED_FIELD)}
																{if $FIELD_INSTANCE}
																	<option value="{$FIELD_INSTANCE->getId()}" data-name="{$FIELD_INSTANCE->getFieldName()}" selected>
																		{\App\Language::translate($FIELD_INSTANCE->get('label'), $RELATED_MODULE_NAME)}
																	</option>
																{/if}
															{/foreach}
														</optgroup>
														{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
															<optgroup label='{\App\Language::translate($BLOCK_LABEL, $RELATED_MODULE_NAME)}'>
																{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
																	{if empty($SELECTED_FIELDS[$FIELD_MODEL->getId()])}
																		<option value="{$FIELD_MODEL->getId()}" data-field-name="{$FIELD_NAME}">
																			{\App\Language::translate($FIELD_MODEL->get('label'), $RELATED_MODULE_NAME)}
																		</option>
																	{/if}
																{/foreach}
															</optgroup>
														{/foreach}
													</select>
												</div>
											</div>
										</div>
										<div class="form-horizontal js-related-custom-view-container" data-js="container">
											<div class="form-group row">
												<label class="col-sm-2 col-form-label text-right">
													{\App\Language::translate('LBL_RELATED_CUSTOM_VIEW',$QUALIFIED_MODULE)}:
												</label>
												<div class="col-sm-10">
													{assign var=SELECTED_CUSTOM_VIEW value=$MODULE_MODEL->getCustomView()}
													{assign var=ALL_CUSTOM_VIEW value=CustomView_Record_Model::getAll($RELATED_MODULE_NAME)}
													<select multiple="multiple" name="custom_view[]" class="form-control select2_container columnsSelect js-related-custom-view" data-select-cb="registerSelectSortable" data-js="sortable | change | select2">
														<optgroup label=''>
															{foreach item=SELECTED_CV from=$SELECTED_CUSTOM_VIEW}
																<option value="{$SELECTED_CV}" selected>
																	{if isset($BASE_CUSTOM_VIEW[$SELECTED_CV])}
																		{$BASE_CUSTOM_VIEW[$SELECTED_CV]}
																	{elseif isset($ALL_CUSTOM_VIEW[$SELECTED_CV])}
																		{\App\Language::translate($ALL_CUSTOM_VIEW[$SELECTED_CV]->get('viewname'), $RELATED_MODULE_NAME)}
																	{/if}
																</option>
															{/foreach}
															{foreach key=CV_ID item=CV_NAME from=$BASE_CUSTOM_VIEW}
																{if !in_array($CV_ID,$SELECTED_CUSTOM_VIEW)}
																	<option value="{$CV_ID}">{$CV_NAME}</option>
																{/if}
															{/foreach}
														</optgroup>
														<optgroup label='{\App\Language::translate('LBL_FILTERS_FROM_MODULE', $QUALIFIED_MODULE)}'>
															{foreach key=CV_ID item=CV_MODEL from=$ALL_CUSTOM_VIEW}
																{if !in_array($CV_ID,$SELECTED_CUSTOM_VIEW)}
																	<option value="{$CV_ID}">
																		{\App\Language::translate($CV_MODEL->get('viewname'), $RELATED_MODULE_NAME)}
																	</option>
																{/if}
															{/foreach}
														</optgroup>
													</select>
												</div>
											</div>
										</div>
										{if $INVENTORY_MODEL}
											{assign var=INVENTORY_FIELDS value=$INVENTORY_MODEL->getFields()}
											<div class="form-horizontal js-related-column-list-container" data-js="container">
												<div class="form-group row">
													<label class="col-sm-2 col-form-label text-right">{\App\Language::translate('LBL_ADVANCED_BLOCK_FIELDS',$QUALIFIED_MODULE)}
														:</label>
													<div class="col-sm-10">
														<select data-placeholder="{\App\Language::translate('LBL_ADD_ADVANCED_BLOCK_FIELDS', $QUALIFIED_MODULE)}"
															multiple="multiple"
															class="select2_container js-related-column-list" data-select-cb="registerSelectSortable"
															data-js="sortable | change | select2" data-type="inventory">
															{foreach item=NAME key=SELECTED_FIELD from=$SELECTED_INVENTORY_FIELDS}
																{assign var=FIELD_INSTANCE value=$INVENTORY_FIELDS[$SELECTED_FIELD]}
																{if $FIELD_INSTANCE}
																	<option value="{$FIELD_INSTANCE->getColumnName()}" data-name="{$FIELD_INSTANCE->getColumnName()}" selected>
																		{\App\Language::translate($FIELD_INSTANCE->get('label'), $RELATED_MODULE_NAME)}
																	</option>
																{/if}
															{/foreach}
															{foreach item=FIELD_MODEL from=$INVENTORY_FIELDS}
																{if !in_array($FIELD_MODEL->getColumnName(), $SELECTED_FIELDS)}
																	<option value="{$FIELD_MODEL->getColumnName()}" data-field-name="{$FIELD_MODEL->getColumnName()}">
																		{\App\Language::translate($FIELD_MODEL->get('label'), $RELATED_MODULE_NAME)}
																	</option>
																{/if}
															{/foreach}
														</select>
													</div>
												</div>
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
						<span class="fas fa-plus mt-2"></span>&nbsp;&nbsp;
						<h5 id="myModalLabel"
							class="modal-title">{\App\Language::translate('LBL_ADD_RELATION', $QUALIFIED_MODULE)}</h5>
						<button type="button" class="close" data-dismiss="modal"
							title="{\App\Language::translate('LBL_CLOSE')}">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<form class="modal-Fields">
							<div class="form-horizontal">
								<div class="form-group row">
									<label class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_RELATION_TYPE', $QUALIFIED_MODULE)}
										:</label>
									<div class="col-md-7">
										<select name="type" class="form-control">
											{foreach from=Settings_LayoutEditor_Module_Model::getRelationsTypes($SELECTED_MODULE_NAME) item=ITEM key=KEY}
												<option value="{$KEY}">{\App\Language::translate($ITEM, $QUALIFIED_MODULE)}</option>
											{/foreach}
										</select>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_RELATION_ACTIONS', $QUALIFIED_MODULE)}
										:</label>
									<div class="col-md-7 marginTop">
										<select multiple="multiple" name="actions" class="form-control">
											{foreach from=Settings_LayoutEditor_Module_Model::getRelationsActions() item=ITEM key=KEY}
												<option value="{$KEY}">{\App\Language::translate($ITEM, $QUALIFIED_MODULE)}</option>
											{/foreach}
										</select>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_SOURCE_MODULE', $QUALIFIED_MODULE)}
										:</label>
									<div class="col-md-7 marginTop">
										<select name="source" class="form-control">
											{foreach item=MODULE_NAME from=$SUPPORTED_MODULES}
												<option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SELECTED_MODULE_NAME} selected {/if}>{\App\Language::translate($MODULE_NAME, $MODULE_NAME)}</option>
											{/foreach}
										</select>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_TARGET_MODULE', $QUALIFIED_MODULE)}
										:</label>
									<div class="col-md-7 marginTop">
										<select name="target" class="target form-control">
											{foreach item=MODULE_NAME from=$SUPPORTED_MODULES}
												<option value="{$MODULE_NAME}">{\App\Language::translate($MODULE_NAME, $MODULE_NAME)}</option>
											{/foreach}
										</select>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-md-4 col-form-label text-right">{\App\Language::translate('LBL_RELATION_LABLE', $QUALIFIED_MODULE)}
										:</label>
									<div class="col-md-7">
										<input name="label" type="text" class="relLabel form-control" />
									</div>
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">

						<button class="btn btn-success addButton" data-dismiss="modal" aria-hidden="true"><span
								class="fas fa-check u-mr-5px"></span>&nbsp;&nbsp;{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
						</button>
						<button class="btn btn-warning" id="closeModal" data-dismiss="modal" aria-hidden="true"><span
								class="fas fa-times u-mr-5px"></span>{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-LayoutEditor-RelatedList -->
{/strip}
