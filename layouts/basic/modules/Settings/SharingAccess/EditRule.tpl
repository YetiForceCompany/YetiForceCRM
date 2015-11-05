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
    {assign var=RULE_MODEL_EXISTS value=true}
    {assign var=RULE_ID value=$RULE_MODEL->getId()}
    {if empty($RULE_ID)}
        {assign var=RULE_MODEL_EXISTS value=false}
    {/if}
    <div id="myModal" class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3 class="modal-title">{vtranslate('LBL_ADD_CUSTOM_RULE_TO', $QUALIFIED_MODULE)}&nbsp;{vtranslate($MODULE_MODEL->get('name'), $MODULE)}</h3>
				</div>
				<form id="editCustomRule" class="form-horizontal" method="POST">
					<input type="hidden" name="for_module" value="{$MODULE_MODEL->get('name')}" />
					<input type="hidden" name="record" value="{$RULE_ID}" />
					<div class="modal-body">
						<div class="row form-group">
							<label class="col-md-5 control-label">{vtranslate($MODULE_MODEL->get('name'), $MODULE)}&nbsp;{vtranslate('LBL_OF', $MODULE)}</label>
							<div class="col-md-6 controls">
								<select class="chzn-select form-control" name="source_id">
									{foreach from=$ALL_RULE_MEMBERS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
										<optgroup label="{vtranslate($GROUP_LABEL, $QUALIFIED_MODULE)}">
											{foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
												<option value="{$MEMBER->getId()}" {if $RULE_MODEL_EXISTS} {if $RULE_MODEL->getSourceMember()->getId() == $MEMBER->getId()}selected{/if}{/if}>
													{vtranslate($MEMBER->getName(),$QUALIFIED_MODULE)}
												</option>
											{/foreach}
										</optgroup>
									{/foreach}
								</select>
							</div>
						</div>
						<div class="row form-group">
							<label class="col-md-5 control-label">{vtranslate('LBL_CAN_ACCESSED_BY', $QUALIFIED_MODULE)}</label>
							<div class="col-md-6 controls">
								<select class="chzn-select form-control" name="target_id">
									{foreach from=$ALL_RULE_MEMBERS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
										<optgroup label="{vtranslate($GROUP_LABEL, $QUALIFIED_MODULE)}">
											{foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
												<option value="{$MEMBER->getId()}" {if $RULE_MODEL_EXISTS}{if $RULE_MODEL->getTargetMember()->getId() == $MEMBER->getId()}selected{/if}{/if}>
													{vtranslate($MEMBER->getName(),$QUALIFIED_MODULE)}
												</option>
											{/foreach}
										</optgroup>
									{/foreach}
								</select>
							</div>	
						</div>
						<div class="row form-group">
							<label class="col-md-5 control-label">{vtranslate('LBL_WITH_PERMISSIONS', $QUALIFIED_MODULE)}</label>
							<div class="col-md-6 controls">
								<label class="checkbox">
									<input type="radio" value="0" name="permission" {if $RULE_MODEL_EXISTS} {if $RULE_MODEL->isReadOnly()} checked {/if} {else} checked {/if}/>&nbsp;{vtranslate('LBL_READ', $QUALIFIED_MODULE)}&nbsp;
								</label>
								<label class="checkbox">
									<input type="radio" value="1" name="permission" {if $RULE_MODEL->isReadWrite()} checked {/if} />&nbsp;{vtranslate('LBL_READ_WRITE', $QUALIFIED_MODULE)}&nbsp;
								</label>
							</div>
						</div>
					</div>
					{include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
				</form>
			</div>
		</div>
	</div>
{/strip}
