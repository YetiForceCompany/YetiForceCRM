{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	<div class="tpl-Settings-PickListDependency-DependencyGraph">
		<div id="accordion" class="my-3">
			<div class="card border-light">
				<div class="card-header bg-transparent border-light" id="headingOne">
					<span class="mr-2">
						<span class="fas fa-info-circle align-middle mr-3"></span>
						{\App\Language::translate('LBL_CONFIGURE_DEPENDENCY_INFO', $QUALIFIED_MODULE)}
					</span>
					<h5 class="mb-0">
						<span class="btn btn-link px-0" data-toggle="collapse" data-target="#collapseOne"
						      aria-expanded="true" aria-controls="collapseOne">
							{\App\Language::translate('LBL_MORE', $QUALIFIED_MODULE)}..
						</span>
					</h5>
				</div>
				<div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
					<div class="card-body" id="dependencyHelp">
						<ul>
							<li>{\App\Language::translate('LBL_CONFIGURE_DEPENDENCY_HELP_1', $QUALIFIED_MODULE)}</li>
							<li class="my-3">{\App\Language::translate('LBL_CONFIGURE_DEPENDENCY_HELP_2', $QUALIFIED_MODULE)|unescape:"html"}</li>
							<li>{\App\Language::translate('LBL_CONFIGURE_DEPENDENCY_HELP_3', $QUALIFIED_MODULE)}
								<span class="selectedCell p-1"> {\App\Language::translate('Selected Values', $QUALIFIED_MODULE)} </span>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<div class="mb-3">
			<span class="btn-toolbar">
				<button class="btn sourceValues btn-light mr-2" type="button">
					<strong>
						<span class="fas fa-hand-point-up mr-1"></span>
						{\App\Language::translate('LBL_SELECT_SOURCE_VALUES', $QUALIFIED_MODULE)}
					</strong>
				</button>
				<button class="btn unmarkAll btn-light" type="button">
					<strong>
						<span class="far fa-times-circle mr-2"></span>
						{\App\Language::translate('LBL_UNMARK_ALL', $QUALIFIED_MODULE)}
					</strong>
				</button>
			</span>
		</div>
		{assign var=SELECTED_MODULE value=$RECORD_MODEL->get('sourceModule')}
		{assign var=SOURCE_FIELD value=$RECORD_MODEL->get('sourcefield')}
		{assign var=MAPPED_SOURCE_PICKLIST_VALUES value=[]}
		{assign var=MAPPED_TARGET_PICKLIST_VALUES value=[]}
		{foreach item=MAPPING from=$MAPPED_VALUES}
			{append var="MAPPED_SOURCE_PICKLIST_VALUES" value=$MAPPING['sourcevalue']}
			{$MAPPED_TARGET_PICKLIST_VALUES[$MAPPING['sourcevalue']] = $MAPPING['targetvalues']}
		{/foreach}
		<input type="hidden" class="allSourceValues"
		       value='{\App\Purifier::encodeHtml(\App\Json::encode($SOURCE_PICKLIST_VALUES))}'/>
		<div class="row depandencyTable m-0">
			<div class="col-2 col-lg-1 p-0  table-responsive">
				<table class="table table-sm themeTableColor" width="100%">
					<thead>
					<tr class="blockHeader">
						<th class="text-center">{$RECORD_MODEL->getSourceFieldLabel()}</th>
					</tr>
					</thead>
					<tbody>
					{foreach item=TARGET_VALUE from=$TARGET_PICKLIST_VALUES name=targetValuesLoop}
						{if $smarty.foreach.targetValuesLoop.index eq 0}
							<tr>
								<td class="text-center h5">
									{$RECORD_MODEL->getTargetFieldLabel()}
								</td>
							</tr>
						{/if}
					{/foreach}
					</tbody>
				</table>
			</div>
			<div class="col-10 col-lg-11 px-0 ml-0 dependencyMapping table-responsive">
				<table class="table-bordered table-sm table themeTableColor pickListDependencyTable">
					<thead>
					<tr class="blockHeader">
						{foreach item=SOURCE_PICKLIST_VALUE from=$SOURCE_PICKLIST_VALUES}
							<th class="align-baseline" data-source-value="{\App\Purifier::encodeHtml($SOURCE_PICKLIST_VALUE)}" style="
									{if !empty($MAPPED_VALUES) && !in_array($SOURCE_PICKLIST_VALUE, array_map('App\Purifier::decodeHtml', $MAPPED_SOURCE_PICKLIST_VALUES))}display: none;{/if}">
								{\App\Language::translate($SOURCE_PICKLIST_VALUE, $SELECTED_MODULE)}</th>
						{/foreach}
					</tr>
					</thead>
					<tbody>
					{foreach key=TARGET_INDEX item=TARGET_VALUE from=$TARGET_PICKLIST_VALUES name=targetValuesLoop}
						<tr>
							{foreach item=SOURCE_PICKLIST_VALUE from=$SOURCE_PICKLIST_VALUES}
								{assign var=PURIFIER_TMP_VAL value=\App\Purifier::encodeHtml($SOURCE_PICKLIST_VALUE)}
								{if !empty($MAPPED_TARGET_PICKLIST_VALUES[$PURIFIER_TMP_VAL])}
									{assign var=targetValues value=$MAPPED_TARGET_PICKLIST_VALUES[$PURIFIER_TMP_VAL]}
								{/if}
								{assign var=SOURCE_INDEX value=$smarty.foreach.mappingIndex.index}
								{assign var=IS_SELECTED value=false}

								{if empty($targetValues) || in_array($TARGET_VALUE, array_map('App\Purifier::decodeHtml',$targetValues))}
									{assign var=IS_SELECTED value=true}
								{/if}
								<td data-source-value='{\App\Purifier::encodeHtml($SOURCE_PICKLIST_VALUE)}'
								    data-target-value='{\App\Purifier::encodeHtml($TARGET_VALUE)}'
								    class="{if $IS_SELECTED}selectedCell {else}unselectedCell {/if} targetValue picklistValueMapping u-cursor-pointer"
								    {if !empty($MAPPED_VALUES) && !in_array($SOURCE_PICKLIST_VALUE, array_map('App\Purifier::decodeHtml', $MAPPED_SOURCE_PICKLIST_VALUES))}style="display: none;" {/if}>
									{\App\Language::translate($TARGET_VALUE, $SELECTED_MODULE)}
								</td>
							{/foreach}
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
		</div>
		<div class="modal sourcePicklistValuesModal modalCloneCopy fade" tabindex="-1">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header contentsBackground">
						<h5 class="modal-title">
							<span class="fas fa-hand-point-up mr-1"></span>
							{\App\Language::translate('LBL_SELECT_SOURCE_PICKLIST_VALUES', $QUALIFIED_MODULE)}
						</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="m-0 table-responsive">
							<table class="table table-borderless table-sm mb-0">
								<tr>
									{foreach key=SOURCE_INDEX item=SOURCE_VALUE from=$SOURCE_PICKLIST_VALUES name=sourceValuesLoop}
									{if $smarty.foreach.sourceValuesLoop.index % 3 == 0}
								</tr>
								<tr>
									{/if}
									<td>
										<div class="form-group">
											<div class="controls checkbox">
												<label class="ml-1">
													<input type="checkbox"
													       class="sourceValue {\App\Purifier::encodeHtml($SOURCE_VALUE)} mr-1"
													       id="sourceValue-{$smarty.foreach.sourceValuesLoop.index}"
													       data-source-value="{\App\Purifier::encodeHtml($SOURCE_VALUE)}"
													       value="{\App\Purifier::encodeHtml($SOURCE_VALUE)}"
															{if empty($MAPPED_VALUES) || in_array($SOURCE_VALUE, array_map('App\Purifier::decodeHtml', $MAPPED_SOURCE_PICKLIST_VALUES))} checked {/if}/>
													{\App\Language::translate($SOURCE_VALUE, $SELECTED_MODULE)}
												</label>
											</div>
										</div>
									</td>
									{/foreach}
								</tr>
							</table>
						</div>
					</div>
					{include file=App\Layout::getTemplatePath('Modals/Footer.tpl', 'Vtiger') BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</div>
			</div>
		</div>
		<div class="p-3">
			<div class="btn-toolbar  float-right">
				<button class="btn btn-success mr-2" type="submit"><span
							class="fa fa-check u-mr-5px"></span><strong>{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</strong>
				</button>
				<button type="button" class="cancelLink cancelDependency btn btn-danger text-white"
				        title="{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}">
					<span class="fa fa-times u-mr-5px"></span><strong>{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</strong>
				</button>
			</div>
			<br/><br/>
		</div>
	</div>
{/strip}
