{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-MailServers-Edit -->
	<div class="verticalScroll ">
		<div class="o-breadcrumb widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		{assign var=EXPAND_BLOCKS value=true}
		<div class="form-horizontal mt-2">
			<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php">
				<input type="hidden" name="module" value="{$MODULE_NAME}" />
				<input type="hidden" name="parent" value="{$PARENT_MODULE}" />
				<input type="hidden" name="action" value="SaveAjax" />
				{if !empty($RECORD_ID)}
					<input type="hidden" name="record" id="recordId" value="{$RECORD_ID}" />
				{/if}
				{foreach from=$STRUCTURE item=FIELDS key=BLOCK name=structre}
					<div class="js-toggle-panel c-panel" data-js="click">
						<div class="js-block-header c-panel__header py-2">
							<span class="iconToggle fas {if $EXPAND_BLOCKS}fa-chevron-down{else}fa-chevron-right{/if} fa-xs m-2" data-hide="fas fa-chevron-right" data-show="fas fa-chevron-down"></span>
							<h5>
								<span class="{$RECORD_MODEL->getModule()->getBlockIcon($BLOCK)} mr-2" aria-hidden="true"></span>
								{\App\Language::translate($BLOCK, $QUALIFIED_MODULE)}
							</h5>
						</div>
						<div class="c-panel__body p-2 js-block-content {if !$EXPAND_BLOCKS}d-none{/if}">
							<div class="form-group row mb-0">
								{foreach from=$FIELDS item=FIELD_MODEL key=FIELD_NAME name=field}
									{if $smarty.foreach.structre.first && !$smarty.foreach.field.first && $smarty.foreach.field.index == 44}
										<div class="w-100 u-fs-10px">&nbsp;</div>
									{/if}

									<div class="col-12 {if $FIELD_NAME neq 'members'}col-md-4 {/if} mb-2 js-field-container {if in_array($FIELD_NAME, ['client_id','client_secret','oauth_provider','redirect_uri_id']) && $FIELDS['auth_method']->get('fieldvalue') !== 'oauth2'} d-none{/if}">
										<label class="u-text-small-bold mb-1">
											{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}
											{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}
											{if $FIELD_MODEL->get('tooltip')}
												<div class="js-popover-tooltip ml-1 d-inline my-auto u-h-fit u-cursor-pointer" data-placement="top" data-content="{\App\Language::translate($FIELD_MODEL->get('tooltip'), $QUALIFIED_MODULE)}">
													<span class="fas fa-info-circle"></span>
												</div>
											{/if}:
										</label>
										{if $FIELD_MODEL->getName() === 'redirect_uri_id'}
											{assign var=VALUE value=$FIELD_MODEL->get('fieldvalue')}
											{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
											<div class="input-group fieldValue m-auto">
												<select id="{$FIELD_MODEL->getName()}" name="{$FIELD_MODEL->getName()}" tabindex="0" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}" class="select2 form-control" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo="{\App\Json::encode($FIELD_MODEL->getFieldInfo())|escape}" data-select="allowClear" data-placeholder="{\App\Language::translate('LBL_SELECT_OPTION')}">
													<optgroup class="p-0">
														<option value="" {if !$VALUE || !isset($PICKLIST_VALUES[$VALUE])} selected{/if}>{\App\Language::translate('LBL_SELECT_OPTION')}</option>
													</optgroup>
													{foreach from=$PICKLIST_VALUES key=KEY item=NAME}
														<option value="{\App\Purifier::encodeHtml($KEY)}" data-redirect="{\App\Mail\Server::getRedirectUriByServiceId($KEY)|escape}" {if $VALUE === $KEY} selected{/if}>{\App\Purifier::encodeHtml($NAME)}</option>
													{/foreach}
												</select>
												<span class="input-group-append">
													<button class="btn btn-outline-secondary clipboard js-popover-tooltip" type="button" data-placement="top" data-content="{\App\Language::translate('BTN_REDIRECT_URI_ID_COPY_TO_CLIPBOARD')}" data-copy-target="#{$FIELD_MODEL->getName()}" data-copy-type="redirect">
														<span class="fas fa-copy"></span>
													</button>
												</span>
											</div>
										{else}
											<div class="fieldValue{if $FIELD_MODEL->getFieldDataType() eq 'boolean'} ml-2 align-top d-inline-block{else} m-auto{/if}">
												{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE MODULE_NAME=$QUALIFIED_MODULE RECORD=null}
											</div>
										{/if}
									</div>
								{/foreach}
							</div>
						</div>
					</div>
				{/foreach}

				<div class="c-form__action-panel">
					<button class="btn btn-success js-save" type="submit">
						<span class="fas fa-check mr-2"></span>
						{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
					</button>
					<button class="btn btn-danger ml-2" type="reset" onclick="javascript:window.history.back();">
						<span class="fa fa-times u-mr-5px"></span>
						{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
					</button>
				</div>
			</form>
		</div>
	</div>
	<!-- /tpl-Settings-MailServers-Edit -->
{/strip}
