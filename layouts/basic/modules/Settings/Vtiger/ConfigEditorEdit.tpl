{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce Sp. z o.o.
********************************************************************************/
-->*}
{strip}
	<div class="tpl-Settings-Vtiger-ConfigEditorEdit">
		<div class="contents">
			<form id="ConfigEditorForm" class="form-horizontal" data-detail-url="{$MODEL->getDetailViewUrl()}"
				  method="POST">
				<div class="row widget_header">
					<div class="col-md-8">
						{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
					</div>
					<div class="col-md-4 btn-toolbar mt-2">
						<div class="float-right">
							<button class="btn btn-success saveButton" type="submit"
									title="{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}">
								<span class="fa fa-check u-mr-5px"></span><strong>{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</strong>
							</button>
							<button type="reset" class="cancelLink btn btn-warning"
									title="{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}">
								<span class="fas fa-times u-mr-5px"></span>{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
							</button>
						</div>
					</div>
				</div>
				<hr>
				{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
				<table class="table table-bordered table-sm themeTableColor">
					<thead>
					<tr class="blockHeader">
						<th colspan="2"
							class="{$WIDTHTYPE}">{\App\Language::translate('LBL_MAIN_CONFIG', $QUALIFIED_MODULE)}</th>
					</tr>
					</thead>
					<tbody>
					{foreach key=FIELD_NAME item=FIELD_LABEL from=$MODEL->listFields}
						{assign var="FIELD_MODEL" value=$MODEL->getFieldInstanceByName($FIELD_NAME)->set('fieldvalue',$MODEL->get($FIELD_NAME))}
						<tr>
							<td width="30%" class="{$WIDTHTYPE} textAlignRight">
								<div class="form-row">
									<label class="col-form-label col-md-4 u-text-small-bold text-left text-md-right">
										{\App\Language::translate($FIELD_LABEL, $QUALIFIED_MODULE)}
									</label>
									{if $FIELD_NAME eq 'upload_maxsize'}
										<div class="input-group col-md-3 fieldValue">
											{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE RECORD=null }
											<div class="input-group-append">
												<span class="input-group-text">{\App\Language::translate('LBL_MB', $QUALIFIED_MODULE)}</span>
											</div>
										</div>
										<label class="col-form-label">
											(upload_max_filesize: {vtlib\Functions::showBytes(vtlib\Functions::getMaxUploadSize())}
											)
										</label>
									{else}
										<div class="col-md-3 fieldValue">
											{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE RECORD=null }
										</div>
									{/if}
								</div>
							</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			</form>
		</div>
	</div>
{/strip}
