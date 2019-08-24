{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<style type="text/css">
	.fieldDetailsForm .zeroOpacity {
		display: none;
	}

	.visibility {
		visibility: hidden;
	}

	.paddingNoTop20 {
		padding: 0 20px 20px 20px;
	}
</style>
{strip}
	<div class="tpl-Settings-QuickCreateEditor-Index" id="quickCreateEditorContainer">
		<input id="selectedModuleName" type="hidden" value="{$SELECTED_MODULE_NAME}"/>
		<div class="widget_header row align-items-center">
			<div class="col-md-8">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
			<div class="float-right col-md-4 h3">
				<select class="select2 form-control" name="quickCreateEditorModules">
					{foreach key=mouleName item=moduleModel from=$SUPPORTED_MODULES}
						{if $moduleModel->isPermitted('EditView')}
							{assign var='quickCreateModule' value=$moduleModel->isQuickCreateSupported()}
							{assign var='singularLabel' value=$moduleModel->getSingularLabelKey()}
							{if $singularLabel == 'SINGLE_Calendar'}
								{assign var='singularLabel' value='LBL_EVENT_OR_TASK'}
							{/if}
							{if $quickCreateModule == '1'}
								<option value="{$mouleName}" {if $mouleName eq $SELECTED_MODULE_NAME} selected {/if}>{\App\Language::translate($singularLabel, $mouleName)}</option>
							{/if}
						{/if}
					{/foreach}
				</select>
			</div>
		</div>
		<div class="contents tabbable">
			<ul class="nav nav-tabs layoutTabs massEditTabs">
				<li class="nav-item">
					<a class="nav-link active" data-toggle="tab" href="#detailViewLayout"><strong>{\App\Language::translate('LBL_SEQUENCE', $QUALIFIED_MODULE)}</strong></a>
				</li>
			</ul>
			<div class="tab-content layoutContent paddingNoTop20 themeTableColor overflowVisible">
				<div class="tab-pane active" id="detailViewLayout">
					<div class="btn-toolbar justify-content-end">
						<button class="btn btn-success saveFieldSequence visibility mt-2 mb-2" type="button">
							<span class="fa fa-check u-mr-5px"></span><strong>{\App\Language::translate('LBL_SAVE_FIELD_SEQUENCE', $QUALIFIED_MODULE)}</strong>
						</button>
					</div>
					<div class="moduleBlocks">
						{foreach key=BLOCK_LABEL_KEY item=BLOCK_MODEL from=$BLOCKS}
							{assign var=FIELDS_LIST value=$BLOCK_MODEL->getLayoutBlockActiveFields()}
							{assign var=BLOCK_ID value=$BLOCK_MODEL->get('id')}
							{$ALL_BLOCK_LABELS[$BLOCK_ID] = $BLOCK_LABEL_KEY}
							<div id="block_{$BLOCK_ID}"
								 class="editFieldsTable block block_{$BLOCK_ID} mb-2 border1px {if $IS_BLOCK_SORTABLE} blockSortable{/if}"
								 data-block-id="{$BLOCK_ID}" data-sequence="{$BLOCK_MODEL->get('sequence')}"
								 >
								<div class="layoutBlockHeader d-flex flex-wrap justify-content-between m-0 p-1 pt-1 w-100">
									<div class="blockLabel u-white-space-nowrap">
										{if $IS_BLOCK_SORTABLE}
											<img class="align-middle" src="{\App\Layout::getImagePath('drag.png')}"
												 alt=""/>
											&nbsp;&nbsp;
										{/if}
										<strong class="align-middle">{App\Language::translate($BLOCK_LABEL_KEY, $SELECTED_MODULE_NAME)}</strong>
									</div>
								</div>
								<div class="blockFieldsList blockFieldsSortable row m-0 p-1 u-min-height-50">
									<ul name="{if $SELECTED_MODULE_MODEL->isFieldsSortableAllowed($BLOCK_LABEL_KEY)}sortable1{/if}"
										class="sortTableUl js-sort-table1 connectedSortable col-md-6 mb-0" data-js="container">
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

															</div>
														</div>
													</div>
												</li>
											{/if}
										{/foreach}
									</ul>
									<ul {if $SELECTED_MODULE_MODEL->isFieldsSortableAllowed($BLOCK_LABEL_KEY)}name="sortable2"{/if}
										class="connectedSortable js-sort-table2 sortTableUl col-md-6 mb-0"
										data-js="container">
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
	</div>
{/strip}
