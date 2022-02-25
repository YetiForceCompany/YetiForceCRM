{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="ruleListContainer">
		<div class="title row">
			<div class="rulehead col-md-6">
				<!-- Check if the module should the for module to get the translations-->
				<strong>{\App\Language::translate('LBL_SHARING_RULE', $QUALIFIED_MODULE)}&nbsp;{\App\Language::translate('LBL_FOR', $MODULE)}&nbsp;
					{if $FOR_MODULE == 'Accounts'}{\App\Language::translate($FOR_MODULE, $QUALIFIED_MODULE)}{else}{\App\Language::translate($FOR_MODULE, $FOR_MODULE)}{/if}:</strong>
			</div>
			<div class="col-md-6">
				<button class="btn btn-success js-add-custom-rule float-right" data-js="click" type="button" data-url="{$MODULE_MODEL->getCreateRuleUrl()}">
					<strong>
						<span class="fas fa-plus mr-1"></span>
						{\App\Language::translate('LBL_ADD_CUSTOM_RULE', $QUALIFIED_MODULE)}
					</strong>
				</button>
			</div>
		</div>
		<hr>
		<div class="contents p-3">
			{if $RULE_MODEL_LIST}
				<table class="table table-bordered table-sm js-custom-rule-table" data-js="container">
					<thead>
						<tr class="js-custom-rule-headers" data-js="remove">
							<th>{\App\Language::translate('LBL_RULE_NO', $QUALIFIED_MODULE)}</th>
							<!-- Check if the module should the for module to get the translations -->
							<th>{if $FOR_MODULE == 'Accounts'}{\App\Language::translate($FOR_MODULE, $QUALIFIED_MODULE)}{else}{\App\Language::translate($FOR_MODULE, $FOR_MODULE)}{/if}
								&nbsp;{\App\Language::translate('LBL_OF', $MODULE)}</th>
							<th>{\App\Language::translate('LBL_CAN_ACCESSED_BY', $QUALIFIED_MODULE)}</th>
							<th>{\App\Language::translate('LBL_PRIVILEGES', $QUALIFIED_MODULE)}</th>
						</tr>
					</thead>
					<tbody>
						{foreach item=RULE_MODEL key=RULE_ID from=$RULE_MODEL_LIST name="customRuleIterator"}
							<tr class="js-custom-rule-entries" data-js="container">
								<td class="js-sequence-number" data-js="text">
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
									<div class="float-right">
										<button type="button" class="js-edit btn btn-sm btn-primary mr-2" data-js="click" data-url="{$RULE_MODEL->getEditViewUrl()}"><span title="{\App\Language::translate('LBL_EDIT', $MODULE)}" class="yfi yfi-full-editing-view"></span></button>
										<button type="button" class="js-delete btn btn-sm btn-danger" data-js="click" data-url="{$RULE_MODEL->getDeleteActionUrl()}"><span title="{\App\Language::translate('LBL_DELETE', $MODULE)}" class="fas fa-trash-alt"></span></button>
									</div>
								</td>
							</tr>
						{/foreach}
					</tbody>
				</table>
				<div class="js-record-details d-none" data-js="removeClass:d-none">
					<p class="text-center">{\App\Language::translate('LBL_CUSTOM_ACCESS_MESG', $QUALIFIED_MODULE)}.
						<!--<a href="">{\App\Language::translate('LBL_CLICK_HERE', $QUALIFIED_MODULE)}</a>&nbsp;{\App\Language::translate('LBL_CREATE_RULE_MESG', $QUALIFIED_MODULE)}-->
					</p>
				</div>
			{else}
				<div class="js-record-details" data-js="removeClass:d-none">
					<p class="text-center">{\App\Language::translate('LBL_CUSTOM_ACCESS_MESG', $QUALIFIED_MODULE)}.
						<!--<a href="">{\App\Language::translate('LBL_CLICK_HERE', $QUALIFIED_MODULE)}</a>&nbsp;{\App\Language::translate('LBL_CREATE_RULE_MESG', $QUALIFIED_MODULE)}-->
					</p>
				</div>
			{/if}
		</div>
	</div>
{/strip}
