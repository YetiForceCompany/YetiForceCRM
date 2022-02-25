{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
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
					<h5 class="modal-title">
						<span class="fas fa-plus mr-1"></span>
						{\App\Language::translate('LBL_ADD_CUSTOM_RULE_TO', $QUALIFIED_MODULE)}&nbsp;{\App\Language::translate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form class="form-horizontal js-edit-rule-form" data-js="submit" method="POST">
					<input type="hidden" name="for_module" value="{$MODULE_MODEL->getName()}" />
					<input type="hidden" name="record" value="{$RULE_ID}" />
					<div class="modal-body">
						<div class="row form-group align-items-center">
							<label class="col-md-5 col-form-label text-right">{\App\Language::translate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}&nbsp;{\App\Language::translate('LBL_OF', $MODULE)}</label>
							<div class="col-md-6">
								<select class="select2 form-control" name="source_id">
									{foreach from=$ALL_RULE_MEMBERS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
										<optgroup label="{\App\Language::translate($GROUP_LABEL, $QUALIFIED_MODULE)}">
											{foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
												<option value="{$MEMBER->getId()}" {if $RULE_MODEL_EXISTS} {if $RULE_MODEL->getSourceMember()->getId() == $MEMBER->getId()}selected{/if}{/if}>
													{\App\Language::translate($MEMBER->getName(),$QUALIFIED_MODULE)}
												</option>
											{/foreach}
										</optgroup>
									{/foreach}
								</select>
							</div>
						</div>
						<div class="row form-group align-items-center">
							<label class="col-md-5 col-form-label text-right">{\App\Language::translate('LBL_CAN_ACCESSED_BY', $QUALIFIED_MODULE)}</label>
							<div class="col-md-6">
								<select class="select2 form-control" name="target_id">
									{foreach from=$ALL_RULE_MEMBERS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
										<optgroup label="{\App\Language::translate($GROUP_LABEL, $QUALIFIED_MODULE)}">
											{foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
												<option value="{$MEMBER->getId()}" {if $RULE_MODEL_EXISTS}{if $RULE_MODEL->getTargetMember()->getId() == $MEMBER->getId()}selected{/if}{/if}>
													{\App\Language::translate($MEMBER->getName(),$QUALIFIED_MODULE)}
												</option>
											{/foreach}
										</optgroup>
									{/foreach}
								</select>
							</div>
						</div>
						<div class="row form-group align-items-center">
							<label class="col-md-5 col-form-label text-right">{\App\Language::translate('LBL_WITH_PERMISSIONS', $QUALIFIED_MODULE)}</label>
							<div class="col-md-6 d-flex flex-column">
								<label class="checkbox">
									<input type="radio" value="0" name="permission" {if $RULE_MODEL_EXISTS} {if $RULE_MODEL->isReadOnly()} checked {/if} {else} checked {/if} />&nbsp;{\App\Language::translate('LBL_READ', $QUALIFIED_MODULE)}&nbsp;
								</label>
								<label class="checkbox">
									<input type="radio" value="1" name="permission" {if $RULE_MODEL->isReadWrite()} checked {/if} />&nbsp;{\App\Language::translate('LBL_READ_WRITE', $QUALIFIED_MODULE)}&nbsp;
								</label>
							</div>
						</div>
					</div>
					{include file=App\Layout::getTemplatePath('Modals/Footer.tpl', 'Vtiger') BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
{/strip}
