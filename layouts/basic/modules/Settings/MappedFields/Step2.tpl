{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-MappedFields-Step2 mfTemplateContents">
		<form name="editMFTemplate" action="index.php" method="post" id="mf_step2" class="form-horizontal">
			<input type="hidden" name="module" value="MappedFields">
			<input type="hidden" name="view" value="Edit">
			<input type="hidden" name="mode" value="Step3" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" class="step" value="2" />
			<input type="hidden" name="record" value="{$RECORDID}" />
			{assign var="PARAMS" value=$MAPPEDFIELDS_MODULE_MODEL->get('params')}
			<div class="col-md-12 px-0">
				<div class="card">
					<div class="card-header">
						<label>
							<strong>{\App\Language::translateArgs('LBL_STEP_N',$QUALIFIED_MODULE, 2)}
								: {\App\Language::translate('LBL_MAPPING_SETTINGS_DETAILS',$QUALIFIED_MODULE)}</strong>
						</label>
					</div>
					<div class="card-body">
						<div class="btn-toolbar">
							<button id="addMapping" class="btn btn-light addButton mb-2" type="button">
								<span class="fas fa-plus"></span>&nbsp;<strong>{\App\Language::translate('LBL_ADD_CONDITION', $QUALIFIED_MODULE)}</strong>
							</button>
							<div class="checkbox col-md-8 align-self-center">
								<label class="mr-1">
									<input class="mr-1" type="checkbox"
										name="autofill" {if !empty($PARAMS['autofill'])} checked {/if}>{\App\Language::translate('LBL_AUTOFILL',$QUALIFIED_MODULE)}
								</label>
								<span class="js-popover-tooltip delay0" data-js="popover" data-placement="top"
									data-content="{\App\Language::translate('LBL_AUTOFILL_INFO',$QUALIFIED_MODULE)}">
									<span class="fas fa-info-circle"></span>
								</span>
							</div>
						</div>
						<div class="contents" id="detailView">
							<div class="table-responsive">
								<table class="table table-bordered" id="mappingToGenerate">
									<tbody>
										<tr class="listViewHeaders">
											<th class="sourceModuleName">
												<b>{\App\Language::translate('SINGLE_'|cat:$SEL_MODULE_MODEL->getName(), $SEL_MODULE_MODEL->getName())}</b>
											</th>
											<th><b>{\App\Language::translate('LBL_FIELDS_TYPE', $QUALIFIED_MODULE)}</b></th>
											<th class="targetModuleName">
												<b>{\App\Language::translate('SINGLE_'|cat:$REL_MODULE_MODEL->getName(), $REL_MODULE_MODEL->getName())}</b>
											</th>
											<th class="defaultHeader">
												<b>{\App\Language::translate('LBL_DEFAULT_VALUE', $QUALIFIED_MODULE)}</b>
											</th>
											<th class="actionsHeader">
												<b>{\App\Language::translate('LBL_ACTIONS', $QUALIFIED_MODULE)}</b>
											</th>
										</tr>
										{foreach key=MAPPING_ID item=MAPPING_ARRAY from=$MAPPEDFIELDS_MODULE_MODEL->getMapping()  name="mappingLoop"}
											{assign var="SEQ" value=$smarty.foreach.mappingLoop.iteration}
											<tr class="listViewEntries" sequence-number="{$SEQ}">
												<td>
													<select class="sourceFields select2" name="mapping[{$SEQ}][source]">
														{foreach key=BLOCK_NAME item=FIELDS from=$SEL_MODULE_MODEL->getFields(true)}
															<optgroup
																label="{\App\Language::translate($BLOCK_NAME, $SEL_MODULE_MODEL->getName())}">
																{foreach key=FIELD_ID item=FIELD_OBJECT from=$FIELDS}
																	<option data-type="{$FIELD_OBJECT->getFieldDataType()}"
																		data-mappingtype="{$FIELD_OBJECT->getFieldType()}" {if $FIELD_ID eq $MAPPING_ARRAY['source']->getId()} selected {/if}
																		label="{\App\Language::translate($FIELD_OBJECT->getFieldLabelKey(), $SEL_MODULE_MODEL->getName())}"
																		value="{$FIELD_ID}">
																		{\App\Language::translate($FIELD_OBJECT->getFieldLabelKey(), $SEL_MODULE_MODEL->getName())}
																	</option>
																{/foreach}
															</optgroup>
														{/foreach}
													</select>
													<input type="hidden" class="mappingType" name="mapping[{$SEQ}][type]"
														value="{$MAPPING_ARRAY['type']}" />
												</td>
												<td class="selectedFieldDataType text-center alignMiddle">{\App\Language::translate($MAPPING_ARRAY['source']->getFieldDataType(), $QUALIFIED_MODULE)}</td>
												<td>
													<select class="targetFields select2" name="mapping[{$SEQ}][target]">
														{foreach key=BLOCK_NAME item=FIELDS from=$REL_MODULE_MODEL->getFields()}
															<optgroup
																label="{\App\Language::translate($BLOCK_NAME, $REL_MODULE_MODEL->getName())}">
																{foreach key=FIELD_ID item=FIELD_OBJECT from=$FIELDS}
																	{if $MAPPING_ARRAY['target']->getFieldDataType() eq $FIELD_OBJECT->getFieldDataType()}
																		<option data-type="{$FIELD_OBJECT->getFieldDataType()}" {if $FIELD_ID eq $MAPPING_ARRAY['target']->getId()} selected {/if}
																			label="{\App\Language::translate($FIELD_OBJECT->getFieldLabelKey(), $SEL_MODULE_MODEL->getName())}"
																			value="{$FIELD_ID}">
																			{\App\Language::translate($FIELD_OBJECT->getFieldLabelKey(), $REL_MODULE_MODEL->getName())}
																		</option>
																	{/if}
																{/foreach}
															</optgroup>
														{/foreach}
													</select>
												</td>
												<td class="">
													{if $MAPPING_ARRAY['default']}
														<input type="hidden" class="form-control default"
															value="{$MAPPING_ARRAY['default']}" />
													{/if}
												</td>
												<td class="textAlignCenter">
													<button title="{\App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"
														type="button" class="btn btn-danger deleteMapping">
														<span class="fas fa-trash-alt"></span>
													</button>
												</td>
											</tr>
										{/foreach}
										<tr class="d-none newMapping listViewEntries">
											<td>
												<select class="sourceFields newSelect">
													<option data-type="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}"
														value="0"
														label="{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
													{foreach key=BLOCK_NAME item=FIELDS from=$SEL_MODULE_MODEL->getFields(true)}
														<optgroup
															label="{\App\Language::translate($BLOCK_NAME, $SEL_MODULE_MODEL->getName())}">
															{foreach key=FIELD_ID item=FIELD_OBJECT from=$FIELDS}
																<option data-type="{$FIELD_OBJECT->getFieldDataType()}"
																	data-type-name="{\App\Language::translate($FIELD_OBJECT->getFieldDataType(), $QUALIFIED_MODULE)}"
																	data-mappingtype="{$FIELD_OBJECT->getFieldType()}"
																	label="{\App\Language::translate($FIELD_OBJECT->getFieldLabelKey(), $SEL_MODULE_MODEL->getName())}"
																	value="{$FIELD_ID}">
																	{\App\Language::translate($FIELD_OBJECT->getFieldLabelKey(), $SEL_MODULE_MODEL->getName())}
																</option>
															{/foreach}
														</optgroup>
													{/foreach}
												</select>
												<input type="hidden" class="mappingType" value="" />
											</td>
											<td class="selectedFieldDataType text-center alignMiddle"></td>
											<td>
												<select class="targetFields newSelect">
													{foreach key=BLOCK_NAME item=FIELDS from=$REL_MODULE_MODEL->getFields()}
														<optgroup
															label="{\App\Language::translate($BLOCK_NAME, $REL_MODULE_MODEL->getName())}">
															{foreach key=FIELD_ID item=FIELD_OBJECT from=$FIELDS}
																<option data-type="{$FIELD_OBJECT->getFieldDataType()}"
																	label="{\App\Language::translate($FIELD_OBJECT->getFieldLabelKey(), $SEL_MODULE_MODEL->getName())}"
																	value="{$FIELD_ID}">
																	{\App\Language::translate($FIELD_OBJECT->getFieldLabelKey(), $REL_MODULE_MODEL->getName())}
																</option>
															{/foreach}
														</optgroup>
													{/foreach}
												</select>
											</td>
											<td class="">
											</td>
											<td class="textAlignCenter">
												<button title="{\App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}"
													type="button" class="btn btn-danger deleteMapping">
													<span class="fas fa-trash-alt"></span>
												</button>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="card-footer clearfix">
						<div class="btn-toolbar float-right">
							<button class="btn btn-danger backStep mr-1" type="button">
								<strong>
									<span class="fas fa-caret-left mr-1"></span>
									{\App\Language::translate('LBL_BACK', $QUALIFIED_MODULE)}
								</strong>
							</button>
							<button class="btn btn-success" type="submit">
								<strong>
									<span class="fas fa-caret-right mr-1"></span>
									{\App\Language::translate('LBL_NEXT', $QUALIFIED_MODULE)}
								</strong>
							</button>
							<button class="btn btn-warning cancelLink" type="reset">
								<span class="fas fa-times mr-1"></span>
								{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
							</button>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
	<div class="d-none" id="defaultValuesElementsContainer">
		{foreach key=BLOCK_NAME item=FIELDS from=$REL_MODULE_MODEL->getFields()}
			{foreach key=_FIELD_ID item=_FIELD_INFO from=$FIELDS}
				{assign var="_FIELD_TYPE" value=$_FIELD_INFO->getFieldDataType()}
				{assign var="_FIELD_UITYPE" value=$_FIELD_INFO->getUIType()}
				{if $_FIELD_TYPE eq 'picklist' || $_FIELD_TYPE eq 'multipicklist'}
					<select id="{$_FIELD_ID}_defaultvalue" {if $_FIELD_TYPE eq 'multipicklist'} multiple {/if}
						class="form-control" disabled>
						{if $_FIELD_TYPE neq 'multipicklist'}
							<option value=" ">{\App\Language::translate('LBL_SELECT_OPTION','Vtiger')}</option>
						{/if}
						{foreach key=KEY item=PICKLIST_VALUE from=$_FIELD_INFO->getPicklistValues()}
							<option value="{\App\Purifier::encodeHtml($KEY)}">{\App\Purifier::encodeHtml($PICKLIST_VALUE)}</option>
						{/foreach}
					</select>
				{elseif in_array($_FIELD_TYPE, ['owner', 'sharedOwner']) || $_FIELD_UITYPE eq '52'}
					<select id="{$_FIELD_ID}_defaultvalue" name="{$_FIELD_ID}_defaultvalue" class=""
						disabled {if $_FIELD_TYPE eq 'sharedOwner'} multiple {/if}>
						{if $_FIELD_TYPE neq 'sharedOwner'}
							<option value="0">{\App\Language::translate('LBL_NONE', $QUALIFIED_MODULE)}</option>
						{/if}
						{foreach key=BLOCK_NAME item=ITEM from=$USERS_LIST}
							{if $_FIELD_UITYPE eq '52'} continue {/if}
							<optgroup label="{\App\Language::translate($BLOCK_NAME, $QUALIFIED_MODULE)}">
								{foreach key=_ID item=_NAME from=$ITEM}
									<option value="{$_ID}">{$_NAME}</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				{elseif $_FIELD_TYPE eq 'date'}
					<input type="text" id="{$_FIELD_ID}_defaultvalue"
						data-date-format="{$USER_MODEL->get('date_format')}"
						class="defaultInputTextContainer form-control col-md-2 dateField" value="" disabled />
				{elseif $_FIELD_TYPE eq 'datetime'}
					<input type="text" id="{$_FIELD_ID}_defaultvalue"
						class="defaultInputTextContainer form-control col-md-2" value=""
						data-date-format="{$USER_MODEL->get('date_format')}" />
				{elseif $_FIELD_TYPE eq 'boolean'}
					<input type="checkbox" id="{$_FIELD_ID}_defaultvalue" class="" disabled />
				{elseif !in_array($_FIELD_TYPE,['sharedOwner','reference'])}
					<input type="input" id="{$_FIELD_ID}_defaultvalue" class="defaultInputTextContainer form-control"
						disabled />
				{/if}
			{/foreach}
		{/foreach}
	</div>
{/strip}
