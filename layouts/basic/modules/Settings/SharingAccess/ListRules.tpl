{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="ruleListContainer">
		<div class="title row">
			<div class="rulehead col-md-6">
				<!-- Check if the module should the for module to get the translations-->
				<strong>{\App\Language::translate('LBL_SHARING_RULE', $QUALIFIED_MODULE)}&nbsp;{\App\Language::translate('LBL_FOR', $MODULE)}&nbsp;
					{if $FOR_MODULE == 'Accounts'}{\App\Language::translate($FOR_MODULE, $QUALIFIED_MODULE)}{else}{\App\Language::translate($FOR_MODULE, $FOR_MODULE)}{/if}:</strong>
			</div>
			<div class="col-md-6">
				<button class="btn btn-success addButton addCustomRule" type="button" data-url="{$MODULE_MODEL->getCreateRuleUrl()}">
					<strong>{\App\Language::translate('LBL_ADD_CUSTOM_RULE', $QUALIFIED_MODULE)}</strong></button>
			</div>
		</div>
		<hr>	
		<div class="contents padding1per">
			{if $RULE_MODEL_LIST}
			<table class="table table-bordered table-condensed customRuleTable">
				<thead>
					<tr class="customRuleHeaders">
						<th>{\App\Language::translate('LBL_RULE_NO', $QUALIFIED_MODULE)}</th>
						<!-- Check if the module should the for module to get the translations -->
						<th>{if $FOR_MODULE == 'Accounts'}{\App\Language::translate($FOR_MODULE, $QUALIFIED_MODULE)}{else}{\App\Language::translate($FOR_MODULE, $MODULE)}{/if}
							&nbsp;{\App\Language::translate('LBL_OF', $MODULE)}</th>
						<th>{\App\Language::translate('LBL_CAN_ACCESSED_BY', $QUALIFIED_MODULE)}</th>
						<th>{\App\Language::translate('LBL_PRIVILEGES', $QUALIFIED_MODULE)}</th>
					</tr>
				</thead>
				<tbody>
					{foreach item=RULE_MODEL key=RULE_ID from=$RULE_MODEL_LIST name="customRuleIterator"}
					<tr class="customRuleEntries">
						<td class="sequenceNumber">
							{$smarty.foreach.customRuleIterator.index + 1}
						</td>
						<td>
							<a href="{$RULE_MODEL->getSourceDetailViewUrl()}">{\App\Language::translate('SINGLE_'|cat:$RULE_MODEL->getSourceMemberName(), $QUALIFIED_MODULE)}: {\App\Language::translate($RULE_MODEL->getSourceMember()->getName(), $QUALIFIED_MODULE)}</a>
						</td>
						<td>
							<a href="{$RULE_MODEL->getTargetDetailViewUrl()}">{\App\Language::translate('SINGLE_'|cat:$RULE_MODEL->getTargetMemberName(), $QUALIFIED_MODULE)}: {\App\Language::translate($RULE_MODEL->getTargetMember()->getName(), $QUALIFIED_MODULE)}</a>
						</td>
						<td>
							{if $RULE_MODEL->isReadOnly()}
								{\App\Language::translate('Read Only', $QUALIFIED_MODULE)}
							{else}
								{\App\Language::translate('Read Write', $QUALIFIED_MODULE)}
							{/if}
							
							<div class="float-right actions">
								<span class="actionImages">
									<a href="javascript:void(0);" class="edit" data-url="{$RULE_MODEL->getEditViewUrl()}"><span title="{\App\Language::translate('LBL_EDIT', $MODULE)}" class="fas fa-edit alignMiddle"></span></a>
									<span class="alignMiddle actionImagesAlignment"> <b>|</b></span>
									<a href="javascript:void(0);" class="delete" data-url="{$RULE_MODEL->getDeleteActionUrl()}"><span title="{\App\Language::translate('LBL_DELETE', $MODULE)}" class="fas fa-trash-alt alignMiddle"></span></a>
								</span>
							</div>
							
						</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
			<div class="recordDetails hide">
				<p class="textAlignCenter">{\App\Language::translate('LBL_CUSTOM_ACCESS_MESG', $QUALIFIED_MODULE)}.<!--<a href="">{\App\Language::translate('LBL_CLICK_HERE', $QUALIFIED_MODULE)}</a>&nbsp;{\App\Language::translate('LBL_CREATE_RULE_MESG', $QUALIFIED_MODULE)}--></p>
			</div>
			{else}
				<div class="recordDetails">
					<p class="textAlignCenter">{\App\Language::translate('LBL_CUSTOM_ACCESS_MESG', $QUALIFIED_MODULE)}.<!--<a href="">{\App\Language::translate('LBL_CLICK_HERE', $QUALIFIED_MODULE)}</a>&nbsp;{\App\Language::translate('LBL_CREATE_RULE_MESG', $QUALIFIED_MODULE)}--></p>
				</div>
			{/if}
		</div>
	</div>
{/strip}
