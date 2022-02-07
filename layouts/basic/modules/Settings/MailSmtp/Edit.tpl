{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-MailSmtp-Edit -->
	<div class="row widget_header">
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div class="editViewContainer">
		<form name="EditMailSmtp" method="post" id="EditView" class="form-horizontal validateForm">
			<div class="alert alert-block alert-danger d-none ">
				<h4 class="alert-heading">{\App\Language::translate('LBL_ERROR', $QUALIFIED_MODULE)}</h4>
				<p></p>
			</div>
			<input type="hidden" name="module" value="MailSmtp" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="action" value="SaveAjax" />
			<input type="hidden" name="mode" value="updateSmtp" />
			<input type="hidden" name="record" value="{$RECORD_ID}" />
			<div class="form-group row mt-3">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_NAME', $QUALIFIED_MODULE)}<span class="redColor">*</span>
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" name="name" value="{$RECORD_MODEL->get('name')}"
						data-validation-engine="validate[required]">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_MAILER_TYPE', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<select class="select2 form-control sourceModule col-md-8" name="mailer_type" id="mailerType">
						<option {if $RECORD_MODEL->get('mailer_type') eq 'smtp'} selected {/if}
							value="smtp">{\App\Language::translate('LBL_SMTP', $QUALIFIED_MODULE)}</option>
						<option {if $RECORD_MODEL->get('mailer_type') eq 'sendmail'} selected {/if}
							value="sendmail">{\App\Language::translate('LBL_SENDMAIL', $QUALIFIED_MODULE)}</option>
						<option {if $RECORD_MODEL->get('mailer_type') eq 'mail'} selected {/if}
							value="mail">{\App\Language::translate('LBL_MAIL', $QUALIFIED_MODULE)}</option>
						<option {if $RECORD_MODEL->get('mailer_type') eq 'qmail'} selected {/if}
							value="qmail">{\App\Language::translate('LBL_QMAIL', $QUALIFIED_MODULE)}</option>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_DEFAULT', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input type="checkbox" name="default"
						value="1" {if $RECORD_MODEL->get('default') eq 1} checked {/if}>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_HOST', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" name="host" placeholder="smtp.gmail.com"
						value="{$RECORD_MODEL->get('host')}">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_PORT', $QUALIFIED_MODULE)}<span class="redColor">*</span>
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" name="port"
						value="{$RECORD_MODEL->get('port')}"
						data-validation-engine="validate[required,custom[integer]]" />
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_AUTHENTICATION', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input type="checkbox" name="authentication"
						value="1" {if $RECORD_MODEL->get('authentication') eq 1} checked {/if}>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_USERNAME', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" value="{$RECORD_MODEL->get('username')}" name="username">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_PASSWORD', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<div class="input-group">
						<input class="form-control" type="password"
							value="{App\Purifier::encodeHtml(App\Encryption::getInstance()->decrypt($RECORD_MODEL->get('password')))}"
							name="password">
						<span class="input-group-append">
							<button class="btn btn-outline-secondary previewPassword" type="button"
								data-target-name="password">
								<span class="fas fa-eye"></span>
							</button>
						</span>
					</div>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_INDIVIDUAL_DELIVERY', $QUALIFIED_MODULE)}&nbsp;
					<span class="js-popover-tooltip" data-js="popover" data-placement="top"
						data-content="{\App\Language::translate('LBL_INDIVIDUAL_DELIVERY_INFO',$QUALIFIED_MODULE)}">
						<span class="fas fa-info-circle"></span>
					</span>
				</label>
				<div class="controls col-md-8">
					<input type="checkbox" name="individual_delivery"
						value="1" {if $RECORD_MODEL->get('individual_delivery') eq 1} checked {/if}>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_SECURE', $QUALIFIED_MODULE)}<span class="redColor">*</span>
				</label>
				<div class="controls col-md-8">
					<select class="select2 form-control sourceModule col-md-8" name="secure" id="secure"
						data-validation-engine="validate[required]">
						<option value="">{\App\Language::translate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}</option>
						<option {if $RECORD_MODEL->get('secure') eq 'tls'} selected {/if}
							value="tls">{\App\Language::translate('LBL_TLS', $QUALIFIED_MODULE)}</option>
						<option {if $RECORD_MODEL->get('secure') eq 'ssl'} selected {/if}
							value="ssl">{\App\Language::translate('LBL_SSL', $QUALIFIED_MODULE)}</option>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_FROM_NAME', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" name="from_name" value="{$RECORD_MODEL->get('from_name')}">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_FROM_EMAIL', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" value="{$RECORD_MODEL->get('from_email')}" name="from_email"
						data-validation-engine="validate[custom[email]]">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_REPLY_TO', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" name="reply_to" value="{$RECORD_MODEL->get('reply_to')}"
						data-validation-engine="validate[custom[email]]">
				</div>
			</div>
			<div class="c-text-divider mb-3">
				<hr class="c-text-divider__line" />
				<span class="c-text-divider__title bg-white"> {\App\Language::translate('LBL_ADDITIONAL_HEADERS', $QUALIFIED_MODULE)} </span>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_MAIL_PRIORITY', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<select class="select2 form-control sourceModule col-md-8" name="priority" id="priority">
						{if empty($RECORD_MODEL->get('priority'))}<option value=""></option>{/if}
						<option {if $RECORD_MODEL->get('priority') eq 'normal'} selected {/if}
							value="normal">{\App\Language::translate('LBL_NORMAL', $QUALIFIED_MODULE)}</option>
						<option {if $RECORD_MODEL->get('priority') eq 'non-urgent'} selected {/if}
							value="non-urgent">{\App\Language::translate('LBL_NO_URGENT', $QUALIFIED_MODULE)}</option>
						<option {if $RECORD_MODEL->get('priority') eq 'urgent'} selected {/if}
							value="urgent">{\App\Language::translate('LBL_URGENT', $QUALIFIED_MODULE)}</option>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_CONFIRM_READING_TO', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" name="confirm_reading_to" value="{$RECORD_MODEL->get('confirm_reading_to')}" data-validation-engine="validate[custom[email]]">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_ORGANIZATION', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" name="organization" value="{$RECORD_MODEL->get('organization')}">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_UNSUBSCIBE', $QUALIFIED_MODULE)}&nbsp;
					<span class="js-popover-tooltip delay0" data-js="popover" data-placement="top"
						data-content="{\App\Purifier::encodeHtml(\App\Language::translate('LBL_UNSUBSCRIBE_INFO',$QUALIFIED_MODULE))}">
						<span class="fas fa-info-circle"></span>
					</span>
				</label>
				<div class="controls col-md-8">
					<select class="form-control select2" name="unsubscribe" data-select="tags" multiple="multiple">
						{if $RECORD_MODEL->get('unsubscribe')}
							{foreach item=UNSUBSCRIBE from=App\Json::decode($RECORD_MODEL->get('unsubscribe'))}
								<option selected value="{$UNSUBSCRIBE}">{$UNSUBSCRIBE}</option>
							{/foreach}
						{/if}
					</select>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_OPTIONS', $QUALIFIED_MODULE)}&nbsp;
					<span class="js-popover-tooltip delay0" data-js="popover" data-placement="top"
						data-content="{\App\Purifier::encodeHtml(\App\Language::translate('LBL_OPTIONS_INFO',$QUALIFIED_MODULE))}">
						<span class="fas fa-info-circle"></span>
					</span>
				</label>
				<div class="controls col-md-8">
					<textarea class="form-control" name="options">{$RECORD_MODEL->get('options')}</textarea>
				</div>
			</div>
			<div class="c-text-divider mb-3">
				<hr class="c-text-divider__line" />
				<span class="c-text-divider__title bg-white">{\App\Language::translate('LBL_SAVE_SENT_MESSAGE', $QUALIFIED_MODULE)}</span>
			</div>
			<div class="form-group row">
				<label class="col-form-label col-md-3 text-right">
					{\App\Language::translate('LBL_SAVE_SEND_MAIL', $QUALIFIED_MODULE)}&nbsp;
					<span class="js-popover-tooltip" data-js="popover" data-placement="top"
						data-content="{\App\Language::translate('LBL_SAVE_SEND_MAIL_INFO',$QUALIFIED_MODULE)}">
						<span class="fas fa-info-circle"></span>
					</span>
				</label>
				<div class="controls col-md-8">
					<input type="checkbox" name="save_send_mail" class="saveSendMail js-save-send-mail" data-js="click"
						value="1" {if $RECORD_MODEL->get('save_send_mail') eq 1} checked {/if}>
				</div>
			</div>
			<div class="saveMailContent {if $RECORD_MODEL->get('save_send_mail') neq 1}d-none{/if}">
				<div class="c-text-divider mb-3">
					<hr class="c-text-divider__line" />
					<span class="c-text-divider__title bg-white">{\App\Language::translate('LBL_IMAP_SAVE_MAIL', $QUALIFIED_MODULE)}</span>
				</div>
				<div class="form-group row">
					<label class="col-form-label col-md-3 text-right">
						{\App\Language::translate('LBL_HOST', $QUALIFIED_MODULE)}<span class="redColor">*</span>
					</label>
					<div class="controls col-md-8">
						<input class="form-control js-smtp-host" type="text" name="smtp_host"
							placeholder="ssl://imap.gmail.com"
							value="{$RECORD_MODEL->get('smtp_host')}" data-js="validation">
					</div>
				</div>
				<div class="form-group row">
					<label class="col-form-label col-md-3 text-right">
						{\App\Language::translate('LBL_PORT', $QUALIFIED_MODULE)}<span class="redColor">*</span>
					</label>
					<div class="controls col-md-8">
						<input class="form-control js-smtp-port" type="text" name="smtp_port"
							value="{$RECORD_MODEL->get('smtp_port')}"
							data-js="validation">
					</div>
				</div>
				<div class="form-group row">
					<label class="col-form-label col-md-3 text-right">
						{\App\Language::translate('LBL_USERNAME', $QUALIFIED_MODULE)}<span class="redColor">*</span>
					</label>
					<div class="controls col-md-8">
						<input class="form-control js-smtp-username" type="text"
							value="{$RECORD_MODEL->get('smtp_username')}"
							name="smtp_username" data-js="validation">
					</div>
				</div>
				<div class="form-group row">
					<label class="col-form-label col-md-3 text-right">
						{\App\Language::translate('LBL_PASSWORD', $QUALIFIED_MODULE)}<span class="redColor">*</span>
					</label>
					<div class="controls col-md-8">
						<div class="input-group">
							<input class="form-control js-smtp-password" type="password"
								value="{App\Purifier::encodeHtml(App\Encryption::getInstance()->decrypt($RECORD_MODEL->get('smtp_password')))}"
								name="smtp_password" data-js="validation">
							<span class="input-group-append">
								<button class="btn btn-outline-secondary previewPassword" type="button"
									data-target-name="smtp_password">
									<span class="fas fa-eye"></span>
								</button>
							</span>
						</div>
					</div>
					<div class="controls col-md-8">

					</div>
				</div>
				<div class="form-group row">
					<label class="col-form-label col-md-3 text-right">
						{\App\Language::translate('LBL_SEND_FOLDER', $QUALIFIED_MODULE)} <span class="redColor"> *
					</label>
					<div class="controls col-md-8">
						<input class="form-control js-smtp-folder" type="text"
							value="{\App\Purifier::encodeHtml($RECORD_MODEL->get('smtp_folder'))}"
							name="smtp_folder" data-js="validation">
					</div>
				</div>
				<div class="form-group row">
					<label class="col-form-label col-md-3 text-right">
						{\App\Language::translate('LBL_VALIDATE_CERT', $QUALIFIED_MODULE)}
					</label>
					<div class="controls col-md-8">
						<input type="checkbox" name="smtp_validate_cert"
							value="1" {if $RECORD_MODEL->get('smtp_validate_cert') eq 1} checked {/if}>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-12 text-center">
					<button class="btn btn-success" type="submit">
						<span class="fas fa-check"></span>&nbsp;<strong>{App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</strong>
					</button>
					<button class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">
						<span class="fas fa-times"></span>&nbsp;{App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
					</button>
				</div>
			</div>
		</form>
	</div>
	<!-- /tpl-Settings-MailSmtp-Edit -->
{/strip}
