{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
{strip}
{foreach key=index item=jsModel from=$SCRIPTS}
	<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
{/foreach}
{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
<div class="modelContainer modal fade" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button class="close" aria-hidden="true" data-dismiss="modal" type="button" title="{vtranslate('LBL_CLOSE')}">x</button>
				<h3 class="modal-title">{vtranslate('LBL_QUICK_CREATE', $MODULE)} {vtranslate($SINGLE_MODULE, $MODULE)}</h3>
			</div>
			<form class="form-horizontal recordEditView contentsBackground" name="QuickCreate" method="post" action="index.php">
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
									{if $isReferenceField neq "reference"}<label class="muted pull-right">{/if}
									{if $FIELD_MODEL->isMandatory() eq true && $isReferenceField neq "reference"} <span class="redColor">*</span> {/if}
									{if $isReferenceField eq "reference"}
										{if $refrenceListCount > 1}
											{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
											{assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
											{if !empty($REFERENCED_MODULE_STRUCT)}
												{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
											{/if}
											<span class="pull-right">
												{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
												<select style="width: 150px;" class="chzn-select referenceModulesList" id="referenceModulesList_{$FIELD_MODEL->get('id')}">
													<optgroup>
														{foreach key=index item=value from=$refrenceList}
															<option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if} >{vtranslate($value, $value)}</option>
														{/foreach}
													</optgroup>
												</select>
											</span>		
										{else}
											<label class="muted pull-right">{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}{vtranslate($FIELD_MODEL->get('label'), $MODULE)}</label>
										{/if}
									{else}
										{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
									{/if}
								{if $isReferenceField neq "reference"}</label>{/if}
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
						<button class="cancelLink cancelLinkContainer pull-right btn btn-warning" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</button>
						<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
						<button class="btn btn-success" name="save" type="submit" onclick="{$GENERATEONCLICK}"><strong>{vtranslate('Generate Password', $RELATEDMODULE)}</strong></button>
						<button class="btn btn-default" id="goToFullForm" data-edit-view-url="{$EDIT_VIEW_URL}" type="button"><strong>{vtranslate('LBL_GO_TO_FULL_FORM', $MODULE)}</strong></button>
				</div>
			</form>
		</div>
	</div>
</div>

<link rel="stylesheet" type="text/css" href="layouts/vlayout/modules/OSSPasswords/resources/validate_pass.css">
<script type="text/javascript" src="layouts/vlayout/modules/OSSPasswords/resources/gen_pass.js"></script>
{/strip}
