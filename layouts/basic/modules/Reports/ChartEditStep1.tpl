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
	<div class="reportContents">
		<form class="form-horizontal recordEditView" id="report_step1" method="post" action="index.php">
			<input type="hidden" name="module" value="{$MODULE}" />
			<input type="hidden" name="view" value="{$VIEW}" />
			<input type="hidden" name="mode" value="step2" />
			<input type="hidden" class="step" value="1" />
			<input type="hidden" name="isDuplicate" value="{$IS_DUPLICATE}" />
			<input type="hidden" name="record" value="{$RECORD_ID}" />
			<input type="hidden" id="relatedModules" data-value="{\App\Purifier::encodeHtml(\App\Json::encode($RELATED_MODULES))}" >
			<div class="well contentsBackground">
				<div class="row padding1per">
					<div class="col-md-3">{\App\Language::translate('LBL_REPORT_NAME',$MODULE)}<span class="redColor">*</span></div>
					<div class="col-md-7 row"><input class="col-md-6 form-control" data-validation-engine='validate[required]' type="text" name="reportname" title="{\App\Language::translate('LBL_REPORT_NAME', $MODULE)}" value="{$REPORT_MODEL->get('reportname')}" /></div>
				</div>
				<div class="row padding1per">
					<div class="col-md-3">{\App\Language::translate('LBL_REPORT_FOLDER',$MODULE)}<span class="redColor">*</span></div>
					<div class="col-md-7 row">
						<select class="chzn-select col-md-6" name="folderid">
							<optgroup>

								{foreach item=REPORT_FOLDER from=$REPORT_FOLDERS}
									<option value="{$REPORT_FOLDER->getId()}"
											{if $REPORT_FOLDER->getId() eq $REPORT_MODEL->get('folderid')}
												selected=""
											{/if}
											>{\App\Language::translate($REPORT_FOLDER->getName(), $MODULE)}</option>
								{/foreach}
							</optgroup>
						</select>
					</div>
				</div>
				<div class="row padding1per">
					<div class="col-md-3">{\App\Language::translate('PRIMARY_MODULE',$MODULE)}<span class="redColor">*</span></div>
					<div class="col-md-7 row">
						<select class="col-md-6 chzn-select" id="primary_module" name="primary_module" title="{\App\Language::translate('PRIMARY_MODULE',$MODULE)}" {if $RECORD_ID and $REPORT_MODEL->getPrimaryModule() and $IS_DUPLICATE neq true} disabled="disabled"{/if}>
							<optgroup>
								{foreach key=RELATED_MODULE_KEY item=RELATED_MODULE from=$MODULELIST}
									<option value="{$RELATED_MODULE_KEY}" {if $REPORT_MODEL->getPrimaryModule() eq $RELATED_MODULE_KEY } selected="selected"{/if}>
										{\App\Language::translate($RELATED_MODULE_KEY,$RELATED_MODULE_KEY)}
									</option>
								{/foreach}
							</optgroup>
						</select>
						{if $RECORD_ID and $REPORT_MODEL->getPrimaryModule() and $IS_DUPLICATE neq true}
							<input type="hidden" name="primary_module" value="{$REPORT_MODEL->getPrimaryModule()}" />
						{/if}
					</div>
				</div>
				<div class="row padding1per">
					<div class="col-md-3">
						<div>{\App\Language::translate('LBL_SELECT_RELATED_MODULES',$MODULE)}</div>
						<div>({\App\Language::translate('LBL_MAX',$MODULE)}&nbsp;2)</div>
					</div>
					<div class="col-md-7 row">
						{assign var=SECONDARY_MODULES_ARR value=explode(':',$REPORT_MODEL->getSecondaryModules())}
						{assign var=PRIMARY_MODULE value=$REPORT_MODEL->getPrimaryModule()}

						{if $PRIMARY_MODULE eq ''}
							{foreach key=PARENT item=RELATED from=$RELATED_MODULES name=relatedlist}
								{if $smarty.foreach.relatedlist.index eq 0}
									{assign var=PRIMARY_MODULE value=$PARENT}
								{/if}
							{/foreach}
						{/if}
						{assign var=PRIMARY_RELATED_MODULES value=$RELATED_MODULES[$PRIMARY_MODULE]}
						<select class="col-md-6 select2-container" title="{\App\Language::translate('LBL_SELECT_RELATED_MODULES',$MODULE)}" id="secondary_module" multiple name="secondary_modules[]"
							data-placeholder="{\App\Language::translate('LBL_SELECT_RELATED_MODULES',$MODULE)}" {if $RECORD_ID and $REPORT_MODEL->getSecondaryModules() and $IS_DUPLICATE neq true} disabled="disabled"{/if}>
							{foreach key=PRIMARY_RELATED_MODULE  item=PRIMARY_RELATED_MODULE_LABEL from=$PRIMARY_RELATED_MODULES}
								<option {if in_array($PRIMARY_RELATED_MODULE,$SECONDARY_MODULES_ARR)} selected="" {/if} value="{$PRIMARY_RELATED_MODULE}">{$PRIMARY_RELATED_MODULE_LABEL}</option>
							{/foreach}
						</select>
						{if $RECORD_ID and $REPORT_MODEL->getSecondaryModules() and $IS_DUPLICATE neq true}
							<input type="hidden" name="secondary_modules[]" value="{$REPORT_MODEL->getSecondaryModules()}" />
						{/if}
					</div>
				</div>
				<div class="row padding1per">
					<div class="col-md-3">{\App\Language::translate('LBL_DESCRIPTION',$MODULE)}</div>
					<div class="col-md-7 row"><textarea class="col-md-6 form-control" type="text" name="description" title="{\App\Language::translate('LBL_DESCRIPTION',$MODULE)}" >{$REPORT_MODEL->get('description')}</textarea></div>
				</div>
			</div>
			<div class="float-right">
				<button type="submit" class="btn btn-success nextStep"><strong>{\App\Language::translate('LBL_NEXT',$MODULE)}</strong></button>&nbsp;&nbsp;
				<button onclick='window.history.back()' class="cancelLink cursorPointer btn btn-warning">{\App\Language::translate('LBL_CANCEL',$MODULE)}</button>
			</div>
		</form>
	</div>
{/strip}

