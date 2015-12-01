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
	<div class="ruleListContainer">
		<div class="title row">
			<div class="rulehead col-md-6">
				<!-- Check if the module should the for module to get the translations-->
				<strong>{vtranslate('LBL_SHARING_RULE', $QUALIFIED_MODULE)}&nbsp;{vtranslate('LBL_FOR', $MODULE)}&nbsp;
					{if $FOR_MODULE == 'Accounts'}{vtranslate($FOR_MODULE, $QUALIFIED_MODULE)}{else}{vtranslate($FOR_MODULE, $FOR_MODULE)}{/if}:</strong>
			</div>
			<div class="col-md-6">
				<button class="btn btn-success addButton addCustomRule" type="button" data-url="{$MODULE_MODEL->getCreateRuleUrl()}">
					<strong>{vtranslate('LBL_ADD_CUSTOM_RULE', $QUALIFIED_MODULE)}</strong></button>
			</div>
		</div>
		<hr>	
		<div class="contents padding1per">
			{if $RULE_MODEL_LIST}
			<table class="table table-bordered table-condensed customRuleTable">
				<thead>
					<tr class="customRuleHeaders">
						<th>{vtranslate('LBL_RULE_NO', $QUALIFIED_MODULE)}</th>
						<!-- Check if the module should the for module to get the translations -->
						<th>{if $FOR_MODULE == 'Accounts'}{vtranslate($FOR_MODULE, $QUALIFIED_MODULE)}{else}{vtranslate($FOR_MODULE, $MODULE)}{/if}
							&nbsp;{vtranslate('LBL_OF', $MODULE)}</th>
						<th>{vtranslate('LBL_CAN_ACCESSED_BY', $QUALIFIED_MODULE)}</th>
						<th>{vtranslate('LBL_PRIVILEGES', $QUALIFIED_MODULE)}</th>
					</tr>
				</thead>
				<tbody>
					{foreach item=RULE_MODEL key=RULE_ID from=$RULE_MODEL_LIST name="customRuleIterator"}
					<tr class="customRuleEntries">
						<td class="sequenceNumber">
							{$smarty.foreach.customRuleIterator.index + 1}
						</td>
						<td>
							<a href="{$RULE_MODEL->getSourceDetailViewUrl()}">{vtranslate('SINGLE_'|cat:$RULE_MODEL->getSourceMemberName(), $QUALIFIED_MODULE)}: {vtranslate($RULE_MODEL->getSourceMember()->getName(), $QUALIFIED_MODULE)}</a>
						</td>
						<td>
							<a href="{$RULE_MODEL->getTargetDetailViewUrl()}">{vtranslate('SINGLE_'|cat:$RULE_MODEL->getTargetMemberName(), $QUALIFIED_MODULE)}: {vtranslate($RULE_MODEL->getTargetMember()->getName(), $QUALIFIED_MODULE)}</a>
						</td>
						<td>
							{if $RULE_MODEL->isReadOnly()}
								{vtranslate('Read Only', $QUALIFIED_MODULE)}
							{else}
								{vtranslate('Read Write', $QUALIFIED_MODULE)}
							{/if}
							
							<div class="pull-right actions">
								<span class="actionImages">
									<a href="javascript:void(0);" class="edit" data-url="{$RULE_MODEL->getEditViewUrl()}"><span title="{vtranslate('LBL_EDIT', $MODULE)}" class="glyphicon glyphicon-pencil alignMiddle"></span></a>
									<span class="alignMiddle actionImagesAlignment"> <b>|</b></span>
									<a href="javascript:void(0);" class="delete" data-url="{$RULE_MODEL->getDeleteActionUrl()}"><span title="{vtranslate('LBL_DELETE', $MODULE)}" class="glyphicon glyphicon-trash alignMiddle"></span></a>
								</span>
							</div>
							
						</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
			<div class="recordDetails hide">
				<p class="textAlignCenter">{vtranslate('LBL_CUSTOM_ACCESS_MESG', $QUALIFIED_MODULE)}.<!--<a href="">{vtranslate('LBL_CLICK_HERE', $QUALIFIED_MODULE)}</a>&nbsp;{vtranslate('LBL_CREATE_RULE_MESG', $QUALIFIED_MODULE)}--></p>
			</div>
			{else}
				<div class="recordDetails">
					<p class="textAlignCenter">{vtranslate('LBL_CUSTOM_ACCESS_MESG', $QUALIFIED_MODULE)}.<!--<a href="">{vtranslate('LBL_CLICK_HERE', $QUALIFIED_MODULE)}</a>&nbsp;{vtranslate('LBL_CREATE_RULE_MESG', $QUALIFIED_MODULE)}--></p>
				</div>
			{/if}
		</div>
	</div>
{/strip}
