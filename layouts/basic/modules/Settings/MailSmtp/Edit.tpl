{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
	<div class="row widget_header">
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{if $RECORD_ID}
				{App\Language::translate('LBL_MAILSMTP_EDIT', $QUALIFIED_MODULE)}
			{/if}
		</div>
	</div>
	<div class="editViewContainer">
		<form name="EditMailSmtp"  id="EditView" class="form-horizontal validateForm">
			<div class="alert alert-block alert-danger fade in " hidden="">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<h4 class="alert-heading">{\App\Language::translate('LBL_ERROR', $QUALIFIED_MODULE)}</h4>
				<p></p>
			</div>
			<input type="hidden" name="module" value="MailSmtp">
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="action" value="Save">
			<input type="hidden" name="mode" value="save">
			<input type="hidden" name="record" value="{$RECORD_ID}">
			<div class="form-group">
				<label class="control-label col-md-3">
					{\App\Language::translate('LBL_NAME', $QUALIFIED_MODULE)} <span class="redColor"> *
				</label>
				<div class="controls col-md-8">
					</span><input class="form-control" type="text" name="name" value="{$RECORD_MODEL->get('name')}" data-validation-engine="validate[required]"> 
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">
					{\App\Language::translate('LBL_MAILER_TYPE', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<select class="select2 form-control sourceModule col-md-8" name="mailer_type" id="mailerType">
						<option {if $RECORD_MODEL->get('mailer_type') eq 'smtp'} selected {/if} value="smtp">{\App\Language::translate('LBL_SMTP', $QUALIFIED_MODULE)}</option>
						<option {if $RECORD_MODEL->get('mailer_type') eq 'sendmail'} selected {/if} value="sendmail">{\App\Language::translate('LBL_SENDMAIL', $QUALIFIED_MODULE)}</option>
						<option {if $RECORD_MODEL->get('mailer_type') eq 'mail'} selected {/if} value="mail">{\App\Language::translate('LBL_MAIL', $QUALIFIED_MODULE)}</option>
						<option {if $RECORD_MODEL->get('mailer_type') eq 'qmail'} selected {/if} value="qmail">{\App\Language::translate('LBL_QMAIL', $QUALIFIED_MODULE)}</option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">
					{\App\Language::translate('LBL_DEFAULT', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input type="checkbox" name="default" value="1" {if $RECORD_MODEL->get('default') eq 1} checked {/if}>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">
					{\App\Language::translate('LBL_HOST', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" name="host" value="{$RECORD_MODEL->get('host')}" >
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">
					{\App\Language::translate('LBL_PORT', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" name="port" value="{$RECORD_MODEL->get('port')}"  data-validation-engine="validate[custom[integer]]">
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">
					{\App\Language::translate('LBL_AUTHENTICATION', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input type="checkbox" name="authentication" value="1"  {if $RECORD_MODEL->get('authentication') eq 1} checked {/if}>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">
					{\App\Language::translate('LBL_USERNAME', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" value="{$RECORD_MODEL->get('username')}" name="username" >
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">
					{\App\Language::translate('LBL_PASSWORD', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="password" value="{$RECORD_MODEL->get('password')}" name="password" >
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">
					{\App\Language::translate('LBL_INDIVIDUAL_DELIVERY', $QUALIFIED_MODULE)}&nbsp;
					<span class="popoverTooltip"  data-placement="top"
						  data-content="{\App\Language::translate('LBL_INDIVIDUAL_DELIVERY_INFO',$QUALIFIED_MODULE)}">
						<span class="glyphicon glyphicon-info-sign"></span>
					</span>
				</label>
				<div class="controls col-md-8">
					<input type="checkbox" name="individual_delivery" value="1" {if $RECORD_MODEL->get('individual_delivery') eq 1} checked {/if}>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">
					{\App\Language::translate('LBL_SECURE', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<select class="select2 form-control sourceModule col-md-8" name="secure" id="secure">
						<option  value="">{\App\Language::translate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}</option>
						<option {if $RECORD_MODEL->get('secure') eq 'tls'} selected {/if} value="tls">{\App\Language::translate('LBL_TLS', $QUALIFIED_MODULE)}</option>
						<option {if $RECORD_MODEL->get('secure') eq 'ssl'} selected {/if} value="ssl">{\App\Language::translate('LBL_SSL', $QUALIFIED_MODULE)}</option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">
					{\App\Language::translate('LBL_FROM_NAME', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" name="from_name"  value="{$RECORD_MODEL->get('from_name')}">
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">
					{\App\Language::translate('LBL_FROM_EMAIL', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" value="{$RECORD_MODEL->get('from_email')}" name="from_email"  data-validation-engine="validate[custom[email]]">
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">
					{\App\Language::translate('LBL_REPLAY_TO', $QUALIFIED_MODULE)}
				</label>
				<div class="controls col-md-8">
					<input class="form-control" type="text" name="replay_to"  value="{$RECORD_MODEL->get('replay_to')}" data-validation-engine="validate[custom[email]]">
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">
					{\App\Language::translate('LBL_OPTIONS', $QUALIFIED_MODULE)}&nbsp;
					<span class="popoverTooltip delay0"  data-placement="top"
						  data-content="{\App\Language::translate('LBL_OPTIONS_INFO',$QUALIFIED_MODULE)}">
						<span class="glyphicon glyphicon-info-sign"></span>
					</span>
				</label>
				<div class="controls col-md-8">
					<textarea class="form-control" name="options">{$RECORD_MODEL->get('options')}</textarea>
				</div>
			</div>
			<div class="row">
				<div class="col-md-5 pull-right">
					<span class="pull-right">
						<button class="btn btn-success" type="submit"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span>&nbsp;<strong>{App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
						<button class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>&nbsp;{App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
					</span>
				</div>
			</div>
		</form>
	</div>
{/strip}
