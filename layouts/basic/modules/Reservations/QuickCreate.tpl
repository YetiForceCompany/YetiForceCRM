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
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	<div class="modelContainer modal fade" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header contentsBackground">
					<button class="close" aria-hidden="true" data-dismiss="modal" type="button" title="{vtranslate('LBL_CLOSE')}">x</button>
					<h3 class="modal-title">{vtranslate('LBL_QUICK_CREATE', $MODULE)} {vtranslate($SINGLE_MODULE, $MODULE)}</h3>
				</div>
				<form class="form-horizontal recordEditView" name="QuickCreate" method="post" action="index.php">
					{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
						<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
					{/if}
					{if !empty($MAPPING_RELATED_FIELD)}
						<input type="hidden" name="mappingRelatedField" value='{Vtiger_Util_Helper::toSafeHTML($MAPPING_RELATED_FIELD)}' />
					{/if}
					<input type="hidden" name="module" value="{$MODULE}">
					<input type="hidden" name="action" value="SaveAjax">
					<div class="quickCreateContent">
						<div class="modal-body">
							<table class="massEditTable table table-bordered">
								<tr>
									{assign var=COUNTER value=0}
									{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}

										{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
										{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
										{assign var="refrenceListCount" value=count($refrenceList)}
										{if $FIELD_MODEL->get('uitype') eq "19"}
											{if $COUNTER eq '1'}
												<td></td><td></td></tr><tr>
												{assign var=COUNTER value=0}
											{/if}
										{/if}
										{if $COUNTER eq 2}
										</tr><tr>
											{assign var=COUNTER value=1}
										{else}
											{assign var=COUNTER value=$COUNTER+1}
										{/if}
										<td class='fieldLabel {$WIDTHTYPE}'>
											{assign var=HELPINFO value=explode(',',$FIELD_MODEL->get('helpinfo'))}
											{assign var=HELPINFO_LABEL value=$MODULE|cat:'|'|cat:$FIELD_MODEL->get('label')}
											{if in_array($VIEW,$HELPINFO) && vtranslate($HELPINFO_LABEL, 'HelpInfo') neq $HELPINFO_LABEL}
												<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{htmlspecialchars(vtranslate($MODULE|cat:'|'|cat:$FIELD_MODEL->get('label'), 'HelpInfo'))}" data-original-title='{vtranslate($FIELD_MODEL->get("label"), $MODULE)}'><i class="glyphicon glyphicon-info-sign"></i></a>
											{/if}
											<label class="muted pull-right">
												{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span>{/if}
												{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
											</label>
										</td>
										<td class="fieldValue {$WIDTHTYPE}" {if $FIELD_MODEL->get('uitype') eq '19'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
											{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
										</td>
									{/foreach}
								</tr>
							</table>
						</div>
					</div>
					<div class="modal-footer quickCreateActions">
						{assign var="EDIT_VIEW_URL" value=$MODULE_MODEL->getCreateRecordUrl()}
						<a class="cancelLink cancelLinkContainer pull-right btn btn-warning" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
						<button class="btn btn-success test" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
						<button class="btn btn-default" id="goToFullForm" data-edit-view-url="{$EDIT_VIEW_URL}" type="button"><strong>{vtranslate('LBL_GO_TO_FULL_FORM', $MODULE)}</strong></button>
					</div>
					{if !empty($SOURCE_RELATED_FIELD)}
						{foreach key=RELATED_FIELD_NAME item=RELATED_FIELD_VALUE from=$SOURCE_RELATED_FIELD}
							<input type="hidden" name="{$RELATED_FIELD_NAME}" value='{$RELATED_FIELD_VALUE}' />
						{/foreach}
					{/if}
				</form>
			</div>
		</div>
	</div>
{/strip}
