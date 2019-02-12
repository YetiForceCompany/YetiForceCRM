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
					<div id="moduleBlocks">
						<div class="editFieldsTable block marginBottom10px border1px blockSortable" style="border-radius: 4px 4px 0px 0px;background: white;">
							<div class="row layoutBlockHeader no-margin">
								<div class="blockLabel col-md-5 marginLeftZero" style="padding:5px 10px 5px 10px">
									{\App\Language::translate($SELECTED_MODULE_NAME, $SELECTED_MODULE_NAME)}
								</div>
							</div>
							<div class="blockFieldsList row no-margin" style="padding:5px;min-height: 27px">
								<ul name="sortable1" class="connectedSortable col-md-6" style="list-style-type: none; min-height: 1px;padding:2px;">
									{foreach  key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=fieldlist}
										{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
										{if $smarty.foreach.fieldlist.index % 2 eq 0}
											<li>
												<div class="opacity editFields marginLeftZero border1px" data-field-id="{$FIELD_MODEL->get('id')}" data-sequence="{$FIELD_MODEL->get('sequence')}">
													<div class="row padding1per">
														{assign var=IS_MANDATORY value=$FIELD_MODEL->isMandatory()}
														<div class="col-sm-1 col-2 col-md-2">&nbsp;
															{if $FIELD_MODEL->isEditable()}
																<a>
																	<img src="{\App\Layout::getImagePath('drag.png')}" border="0" title="{\App\Language::translate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
																</a>
															{/if}
														</div>
														<div class="col-sm-11 col-10 col-md-10 marginLeftZero" style="word-wrap: break-word;">
                                                                <span class="fieldLabel">{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SELECTED_MODULE_NAME)}
																	&nbsp;
																	{if $IS_MANDATORY}
																		<span class="redColor">*</span>
																	{/if}</span>
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
														<span class="col-sm-1 col-2 col-md-2">&nbsp;
															{if $FIELD_MODEL->isEditable()}
																<a>
																		<img src="{\App\Layout::getImagePath('drag.png')}" border="0" title="{\App\Language::translate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
																	</a>
															{/if}
															</span>
														<div class="col-sm-11 col-10 col-md-10 marginLeftZero" style="word-wrap: break-word;">
																<span class="fieldLabel">
																	{if $IS_MANDATORY}
																		<span class="redColor">*</span>
																	{/if}
																	{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SELECTED_MODULE_NAME)}
																	&nbsp;
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
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
