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
	<div class="tpl-Settings-Vtiger-CustomRecordNumbering">
		<form id="EditView" method="POST">
			<div class="widget_header row mb-3">
				<div class="col-6 col-md-9">
					<div class="d-inline-flex">
						{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
					</div>
					<div class="d-inline-flex">
						<div class="js-popover-tooltip ml-2" data-js="popover"
							 data-content="{\App\Language::translate('LBL_CUSTOMIZE_MODENT_NUMBER_DESCRIPTION',$QUALIFIED_MODULE)}">
							<span class="fas fa-info-circle"></span>
						</div>
					</div>
				</div>
				<div class="col-6 col-md-3">
					<button type="button" class="btn btn-info float-right mt-1" name="updateRecordWithSequenceNumber">
						<span class="fas fa-exchange-alt u-mr-5px"></span>{\App\Language::translate('LBL_UPDATE_MISSING_RECORD_SEQUENCE', $QUALIFIED_MODULE)}
					</button>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<table class="table table-bordered">
						{assign var=SELECTED_MODULE_NAME value=$SELECTED_MODULE_MODEL->getName()}
						{assign var=SELECTED_MODULE_DATA value=\App\Fields\RecordNumber::getNumber($SELECTED_MODULE_NAME)}
						{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
						<thead>
						<tr>
							<th width="30%" class="{$WIDTHTYPE}">
								{\App\Language::translate('LBL_CUSTOMIZE_RECORD_NUMBERING', $QUALIFIED_MODULE)}
							</th>
							<th width="70%"></th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td class="{$WIDTHTYPE}">
								<label class="float-right"><b>{\App\Language::translate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}</b></label>
							</td>
							<td class="fieldValue {$WIDTHTYPE} border-left-0 position-relative">
								<select class="select2 form-control" name="sourceModule">
									{foreach key=index item=MODULE_MODEL from=$SUPPORTED_MODULES}
										{assign var=MODULE_NAME value=$MODULE_MODEL->get('name')}
										<option value={$MODULE_NAME} {if $MODULE_NAME eq $SELECTED_MODULE_NAME} selected {/if}>
											{\App\Language::translate($MODULE_NAME, $MODULE_NAME)}
										</option>
									{/foreach}
								</select>
							</td>
						</tr>
						<tr>
							<td class="{$WIDTHTYPE}">
								<label class="float-right">
									<b>{\App\Language::translate('LBL_USE_PREFIX', $QUALIFIED_MODULE)}</b>
									<a href="#" class="js-popover-tooltip ml-2"
									   data-js="popover"
									   data-trigger="focus hover"
									   data-content="{\App\Language::translate('LBL_USE_PREFIX_INFO', $QUALIFIED_MODULE)}">
										<span class="fas fa-info-circle"></span>
									</a>
								</label>
							</td>
							<td class="fieldValue {$WIDTHTYPE} border-left-0 position-relative">
								<input type="text" class="form-control" value="{$SELECTED_MODULE_DATA['prefix']}"
									   placeholder="{\App\Language::translate('LBL_NO_PREFIX', $QUALIFIED_MODULE)}"
									   data-old-prefix="{$SELECTED_MODULE_DATA['prefix']}" name="prefix"
									   data-validation-engine="validate[funcCall[Vtiger_AlphaNumericWithSlashesCurlyBraces_Validator_Js.invokeValidation]]"/>
							</td>
						</tr>
						<tr>
							<td class="{$WIDTHTYPE}">
								<label class="float-right">
									<b>{\App\Language::translate("LBL_LEADING_ZEROS", $QUALIFIED_MODULE)}</b>
									<a href="#" class="js-popover-tooltip ml-2"
									   data-js="popover"
									   data-trigger="focus hover"
									   data-content="{\App\Language::translate('LBL_LEADING_ZEROS_INFO', $QUALIFIED_MODULE)}">
										<span class="fas fa-info-circle"></span>
									</a>
								</label>
							</td>
							<td class="fieldValue {$WIDTHTYPE} border-left-0 position-relative">
								<select class="select2" name="leading_zeros">
									{assign var=LEADING_ZEROS value=[
									0 => '2, 6, 88, 954, 1549',
									2 => '02, 06, 88, 954, 1549',
									3 => '002, 006, 088, 954, 1549',
									4 => '0002, 0006, 0088, 0954, 1549',
									5 => '00002, 00006, 00088, 00954, 01549',
									6 => '000002, 000006, 000088, 000954, 001549',
									7 => '0000002, 0000006, 0000088, 0000954, 0001549',
									8 => '00000002, 00000006, 00000088, 00000954, 00001549',
									9 => '000000002, 000000006, 000000088, 000000954, 000001549'
									]}
									{foreach key=VAL item=DESC from=$LEADING_ZEROS}
										<option value="{$VAL}"
												{if $VAL === 0}
												{if empty($SELECTED_MODULE_DATA['leading_zeros'])}selected="selected"{/if}>
											{\App\Language::translate('LBL_NO_LEADING_ZEROS', $QUALIFIED_MODULE)}&nbsp;
											{else}
											{if $SELECTED_MODULE_DATA['leading_zeros']===$VAL}selected="selected"{/if}
											>{$VAL}&nbsp;
											{/if}
											({\App\Language::translate('LBL_FOR_EXAMPLE_SHORT',$QUALIFIED_MODULE)}
											{$DESC}
											)
										</option>
									{/foreach}
								</select>
							</td>
						</tr>
						<tr>
							<td class="{$WIDTHTYPE}">
								<label class="float-right">
									<b>{\App\Language::translate('LBL_USE_POSTFIX', $QUALIFIED_MODULE)}</b>
									<a href="#" class="js-popover-tooltip ml-2"
									   data-js="popover"
									   data-trigger="focus hover"
									   data-content="{\App\Language::translate('LBL_USE_POSTFIX_INFO', $QUALIFIED_MODULE)}">
										<span class="fas fa-info-circle"></span>
									</a>
								</label>
							</td>
							<td class="fieldValue {$WIDTHTYPE} border-left-0 position-relative">
								<input type="text" class="form-control" value="{$SELECTED_MODULE_DATA['postfix']}"
									   placeholder="{\App\Language::translate('LBL_NO_POSTFIX', $QUALIFIED_MODULE)}"
									   data-old-postfix="{$SELECTED_MODULE_DATA['postfix']}" name="postfix"
									   data-validation-engine="validate[funcCall[Vtiger_AlphaNumericWithSlashesCurlyBraces_Validator_Js.invokeValidation]]"/>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<table class="table table-bordered">
						<thead>
						<tr>
							<th width="30%" class="{$WIDTHTYPE}">
								{\App\Language::translate('LBL_CUSTOMIZE_SEQUENCE', $QUALIFIED_MODULE)}
							</th>
							<th></th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td class="{$WIDTHTYPE}">
								<label class="float-right">
									<b>{\App\Language::translate('LBL_START_SEQUENCE', $QUALIFIED_MODULE)}</b>
									<span class="redColor">*</span>
									<a href="#" class="js-popover-tooltip ml-2"
									   data-js="popover"
									   data-trigger="focus hover"
									   data-content="{\App\Language::translate('LBL_START_SEQUENCE_INFO', $QUALIFIED_MODULE)}">
										<span class="fas fa-info-circle"></span>
									</a>
								</label>
							</td>
							<td class="fieldValue {$WIDTHTYPE} border-left-0 position-relative">
								<input type="text" class="form-control"
									   value="{$SELECTED_MODULE_DATA['sequenceNumber']}"
									   data-old-sequence-number="{$SELECTED_MODULE_DATA['sequenceNumber']}"
									   name="sequenceNumber"
									   data-validation-engine="validate[required,funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]"/>
							</td>
						</tr>
						<tr>
							<td class="{$WIDTHTYPE}">
								<label class="float-right">
									<b>{\App\Language::translate('LBL_RS_RESET_SEQUENCE', $QUALIFIED_MODULE)}</b>
									<a href="#" class="js-popover-tooltip ml-2"
									   data-js="popover"
									   data-trigger="focus hover"
									   data-content="{\App\Language::translate('LBL_RS_RESET_SEQUENCE_INFO', $QUALIFIED_MODULE)}">
										<span class="fas fa-info-circle"></span>
									</a>
								</label>
							</td>
							<td class="fieldValue {$WIDTHTYPE} border-left-0 position-relative">
								<select class="select2" name="reset_sequence"
										data-placeholder="{\App\Language::translate('LBL_RS_RESET_SEQUENCE', $QUALIFIED_MODULE)}">
									<option value="n">{\App\Language::translate('LBL_RS_DO_NOT_RESET', $QUALIFIED_MODULE)}</option>
									<option value="Y"{if $SELECTED_MODULE_DATA['reset_sequence']==='Y'} selected {/if}>{\App\Language::translate('LBL_RS_YEAR',$QUALIFIED_MODULE)}</option>
									<option value="M"{if $SELECTED_MODULE_DATA['reset_sequence']==='M'} selected {/if}>{\App\Language::translate('LBL_RS_MONTH',$QUALIFIED_MODULE)}</option>
									<option value="D"{if $SELECTED_MODULE_DATA['reset_sequence']==='D'} selected {/if}>{\App\Language::translate('LBL_RS_DAY',$QUALIFIED_MODULE)}</option>
								</select>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					{if empty($TEXT_PARSER)}
						{assign var=TEXT_PARSER value=\App\TextParser::getInstance($SELECTED_MODULE_NAME)}
					{/if}
					<table id="customRecordNumbering" class="table table-bordered">
						<thead>
						<tr>
							<th width="30%" class="{$WIDTHTYPE}">
								{\App\Language::translate('LBL_USE_CUSTOM_VARIABLES', $QUALIFIED_MODULE)}
							</th>
							<th width="70%" class="{$WIDTHTYPE} border-left-0"></th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td class="{$WIDTHTYPE}">
								<label class="float-right">
									{\App\Language::translate('LBL_MODULE_FIELDS','Other.TextParser')}
								</label>
							</td>
							<td class="fieldValue {$WIDTHTYPE} border-left-0 position-relative">
								<div class="input-group">
									<select class="select2 form-control" id="recordVariable"
											data-width="style">
										{foreach item=FIELDS key=BLOCK_NAME from=$TEXT_PARSER->getRecordVariable()}
											<optgroup label="{$BLOCK_NAME}">
												{foreach item=ITEM from=$FIELDS}
													<option value="{$ITEM['var_value']}"
															data-label="{$ITEM['var_label']}">{\App\Language::translate($ITEM['label'], $SELECTED_MODULE_NAME)}</option>
												{/foreach}
											</optgroup>
										{/foreach}
									</select>
									<div class="input-group-append">
										<button type="button" class="btn btn-primary js-value-copy"
												data-copy-target="#recordVariable"
												title="{\App\Language::translate('LBL_COPY_TO_CLIPBOARD','Other.TextParser')} - {\App\Language::translate('LBL_COPY_VALUE','Other.TextParser')}">
											<span class="fas fa-copy"></span>
										</button>
									</div>
								</div>
							</td>
						</tr>
						{assign var=RELATED_VARIABLE value=$TEXT_PARSER->getRelatedVariable()}
						{if $RELATED_VARIABLE}
							<tr>
								<td class="{$WIDTHTYPE}">
									<label class="float-right">
										{\App\Language::translate('LBL_DEPENDENT_MODULE_FIELDS','Other.TextParser')}
									</label>
								</td>
								<td class="fieldValue {$WIDTHTYPE} border-left-0 position-relative">

									<div class="input-group">
										<select class="select2 form-control" id="relatedVariable"
												data-width="style">
											{foreach item=FIELDS from=$RELATED_VARIABLE}
												{foreach item=RELATED_FIELDS key=BLOCK_NAME from=$FIELDS}
													<optgroup label="{$BLOCK_NAME}">
														{foreach item=ITEM from=$RELATED_FIELDS}
															<option value="{$ITEM['var_value']}"
																	data-label="{$ITEM['var_label']}">{$ITEM['label']}</option>
														{/foreach}
													</optgroup>
												{/foreach}
											{/foreach}
										</select>
										<div class="input-group-append">
											<button type="button" class="btn btn-primary js-value-copy"
													data-copy-target="#relatedVariable"
													title="{\App\Language::translate('LBL_COPY_TO_CLIPBOARD','Other.TextParser')} - {\App\Language::translate('LBL_COPY_VALUE','Other.TextParser')}">
												<span class="fas fa-copy"></span>
											</button>
										</div>
									</div>
								</td>
							</tr>
						{/if}
						{assign var=SOURCE_VARIABLE value=$TEXT_PARSER->getSourceVariable()}
						{if $SOURCE_VARIABLE}
							<tr>
								<td class="{$WIDTHTYPE}">
									<label class="float-right">
										{\App\Language::translate('LBL_SOURCE_MODULE_FIELDS','Other.TextParser')}
									</label>
								</td>
								<td class="fieldValue {$WIDTHTYPE} border-left-0 position-relative">
									<div class="input-group">
										<select class="select2" id="sourceVariable" data-width="style">
											{foreach item=BLOCKS key=SOURCE_MODULE from=$SOURCE_VARIABLE}
												{if $SOURCE_MODULE == 'LBL_ENTITY_VARIABLES'}
													<optgroup
															label="{\App\Language::translate($SOURCE_MODULE, 'Other.TextParser')}">
														{foreach item=ITEM from=$BLOCKS}
															<option value="{$ITEM['var_value']}"
																	data-label="{$ITEM['var_label']}">{$ITEM['label']}</option>
														{/foreach}
													</optgroup>
												{else}
													{assign var=SOURCE_LABEL value=\App\Language::translate(\App\Language::getSingularModuleName($SOURCE_MODULE), $SOURCE_MODULE)}
													{foreach item=FIELDS key=BLOCK_NAME from=$BLOCKS}
														<optgroup
																label="{$SOURCE_LABEL} - {\App\Language::translate($BLOCK_NAME, $SOURCE_MODULE)}">
															{foreach item=ITEM from=$FIELDS}
																<option value="{$ITEM['var_value']}"
																		data-label="{$ITEM['var_label']}">{$SOURCE_LABEL}
																	: {$ITEM['label']}</option>
															{/foreach}
														</optgroup>
													{/foreach}
												{/if}
											{/foreach}
										</select>
										<div class="input-group-append">
											<button type="button" class="btn btn-primary js-value-copy"
													data-copy-target="#sourceVariable"
													title="{\App\Language::translate('LBL_COPY_TO_CLIPBOARD','Other.TextParser')} - {\App\Language::translate('LBL_COPY_VALUE','Other.TextParser')}">
												<span class="fas fa-copy"></span>
											</button>
										</div>
									</div>
								</td>
							</tr>
						{/if}
						<tr>
							<td class="{$WIDTHTYPE}">
								<label class="float-right">
									{\App\Language::translate('LBL_ADDITIONAL_VARIABLES','Other.TextParser')}
								</label>
							</td>
							<td class="fieldValue {$WIDTHTYPE} border-left-0 position-relative">
								<div class="input-group">
									<select class="select2 form-control" id="generalVariable"
											data-container-class-css="form-control" data-width="style">
										<option value="YYYY">{\App\Language::translate('LBL_CV_FULL_YEAR', $QUALIFIED_MODULE)}</option>
										<option value="YY">{\App\Language::translate('LBL_CV_YEAR', $QUALIFIED_MODULE)}</option>
										<option value="MM">{\App\Language::translate('LBL_CV_FULL_MONTH', $QUALIFIED_MODULE)}</option>
										<option value="M">{\App\Language::translate('LBL_CV_MONTH', $QUALIFIED_MODULE)}</option>
										<option value="DD">{\App\Language::translate('LBL_CV_FULL_DAY', $QUALIFIED_MODULE)}</option>
										<option value="D">{\App\Language::translate('LBL_CV_DAY', $QUALIFIED_MODULE)}</option>
										{foreach item=FIELDS key=BLOCK_NAME from=$TEXT_PARSER->getGeneralVariable()}
											<optgroup
													label="{\App\Language::translate($BLOCK_NAME, 'Other.TextParser')}">
												{foreach item=LABEL key=VARIABLE from=$FIELDS}
													<option value="{$VARIABLE}">{$LABEL}</option>
												{/foreach}
											</optgroup>
										{/foreach}
									</select>
									<div class="input-group-append">
										<button type="button" class="btn btn-primary js-value-copy"
												data-copy-target="#generalVariable"
												title="{\App\Language::translate('LBL_COPY_TO_CLIPBOARD','Other.TextParser')}">
											<span class="fas fa-copy"></span>
										</button>
									</div>
								</div>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12 float-right">
					<div class="float-right">
						<button class="btn btn-success saveButton" type="submit" disabled="disabled">
							<span class="fa fa-check u-mr-5px"></span>{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
						</button>
						<button class="cancelLink btn btn-warning" type="reset"
								onclick="javascript:window.history.back();">
							<span class="fa fa-times u-mr-5px"></span>{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
						</button>
					</div>
				</div>
			</div>
		</form>
	</div>
{/strip}
