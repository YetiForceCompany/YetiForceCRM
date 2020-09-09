{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-MailClient-Edit -->
	<div class="row widget_header">
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div class="editViewContainer">
		<form name="EditMailClient" method="post" id="EditView" class="form-horizontal validateForm">
			<div class="alert alert-block alert-danger d-none">
				<h4 class="alert-heading">{\App\Language::translate('LBL_ERROR', $QUALIFIED_MODULE)}</h4>
				<p></p>
			</div>
			<input type="hidden" name="module" value="MailClient"/>
			<input type="hidden" name="parent" value="Settings"/>
			<input type="hidden" name="action" value="SaveAjax"/>
			<input type="hidden" name="mode" value="updateClient"/>
			<input type="hidden" name="record" value="{$RECORD_ID}"/>
			<div class="form-group row mt-3">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_VALIDATE_CERT', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input type="checkbox" name="validate_cert"
						   value="1" {if $RECORD_MODEL->get('validate_cert') eq 1} checked {/if}>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_ADD_TYPE', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input type="checkbox" name="validate_cert"
						   value="1" {if $RECORD_MODEL->get('add_connection_type') eq 1} checked {/if}>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					<span class="redColor">*</span>
					{\App\Language::translate('LBL_IMAP_SERVER', $QUALIFIED_MODULE)}
					<span class="js-popover-tooltip ml-1" data-js="popover" data-placement="top"
						  data-content="{\App\Language::translate('LBL_IMAP_SERVER_DESC',$QUALIFIED_MODULE)}">
						<span class="fas fa-info-circle"></span>
					</span>
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" name="default_host"
						   value="{$RECORD_MODEL->get('default_host')}"
						   data-validation-engine="validate[required]"/>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					<span class="redColor">*</span>
					{\App\Language::translate('LBL_PORT_CONNECT_IMAP', $QUALIFIED_MODULE)}
					<span class="js-popover-tooltip ml-1" data-js="popover" data-placement="top"
						  data-content="{\App\Language::translate('LBL_PORT_CONNECT_IMAP_DESC',$QUALIFIED_MODULE)}">
						<span class="fas fa-info-circle"></span>
					</span>
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" name="default_port"
						   value="{$RECORD_MODEL->get('default_port')}"
						   data-validation-engine="validate[required,custom[integer]]"/>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					<span class="redColor">*</span>
					{\App\Language::translate('LBL_SMTP_SERVER', $QUALIFIED_MODULE)}
					<span class="js-popover-tooltip ml-1" data-js="popover" data-placement="top"
						  data-content="{\App\Language::translate('LBL_SMTP_SERVER_DESC',$QUALIFIED_MODULE)}">
						<span class="fas fa-info-circle"></span>
					</span>
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" name="smtp_server"
						   value="{$RECORD_MODEL->get('smtp_server')}" data-validation-engine="validate[required]"/>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					<span class="redColor">*</span>
					{\App\Language::translate('LBL_SMTP_PORT', $QUALIFIED_MODULE)}
					<span class="js-popover-tooltip ml-1" data-js="popover" data-placement="top"
						  data-content="{\App\Language::translate('LBL_SMTP_PORT_DESC',$QUALIFIED_MODULE)}">
						<span class="fas fa-info-circle"></span>
					</span>
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" name="smtp_port"
						   value="{$RECORD_MODEL->get('smtp_port')}"
						   data-validation-engine="validate[required,custom[integer]]"/>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					<span class="redColor">*</span>
					{\App\Language::translate('LBL_LANGUAGE', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<select class="select2 form-control" name="language" data-validation-engine="validate[required]">
						{foreach item=LANGUAGE from=$LANGUAGES_VALUE}
							<option value="{$LANGUAGE}" {if $LANGUAGE eq $RECORD_MODEL->get('language')} selected {/if}>
								{$LANGUAGE}
							</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_DOMAIN_AUTOMATICALLY', $QUALIFIED_MODULE)}
					<span class="js-popover-tooltip ml-1" data-js="popover" data-placement="top"
						  data-content="{\App\Language::translate('LBL_SMTP_PORT_DESC',$QUALIFIED_MODULE)}">
						<span class="fas fa-info-circle"></span>
					</span>
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" value="{$RECORD_MODEL->get('username_domain')}" name="username_domain">
				</div>
			</div>

			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_IP_ADDRESS', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input type="checkbox" name="ip_check"
						   value="1" {if $RECORD_MODEL->get('ip_check') eq 1} checked {/if}>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_ENABLE_SPELL_CHECK', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input type="checkbox" name="enable_spellcheck"
						   value="1" {if $RECORD_MODEL->get('enable_spellcheck') eq 1} checked {/if}>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					<span class="redColor">*</span>
					{\App\Language::translate('LBL_ACCESS_IDENTITY', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<select class="select2 form-control" name="identities_level" data-validation-engine="validate[required]">
						{foreach item=IDENTITY from=$IDENTITYS}
							<option value="{$IDENTITY}" {if $IDENTITY eq $RECORD_MODEL->get('identities_level')} selected {/if}>
								{\App\Language::translate('PLL_IDENTITY_'|cat:$IDENTITY, $QUALIFIED_MODULE)}
							</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					<span class="redColor">*</span>
					{\App\Language::translate('LBL_LIFE_SESSION', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" name="session_lifetime"
						   value="{$RECORD_MODEL->get('session_lifetime')}"
						   data-validation-engine="validate[required,custom[integer]]"/>
				</div>
			</div>
			<div class="row mb-3">
				<div class="col-12 text-center">
					<button class="btn btn-success" type="submit">
						<span class="fas fa-check mr-2"></span><strong>{App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</strong>
					</button>
					<button class="cancelLink btn btn-warning ml-2" type="reset" onclick="javascript:window.history.back();">
						<span class="fas fa-times mr-2"></span>{App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
					</button>
				</div>
			</div>
		</form>
	</div>
	<!-- /tpl-Settings-MailClient-Edit -->
{/strip}
