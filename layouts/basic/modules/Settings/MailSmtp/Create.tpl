{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	<form class="form-horizontal validateForm" action="index.php?module=MailSmtp&parent=Settings&action=SaveAjax&mode=save" id="createForm">
		<div class="modal-header">
			<div class="pull-left">
				<h3 class="modal-title">{\App\Language::translate('LBL_CREATE_SMTP', $QUALIFIED_MODULE)}</h3>
			</div>
			<div class="clearfix"></div>
		</div>
		<div class="modal-body">
			<div class="">
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
						<input type="checkbox" name="default" {if $RECORD_MODEL->get('default') eq 1} checked {/if}>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">
						{\App\Language::translate('LBL_HOST', $QUALIFIED_MODULE)}
					</label>
					<div class="controls col-md-8">
						<input class="form-control" type="text" name="host" value="{$RECORD_MODEL->get('host')}" data-validation-engine="validate[custom[integer]]">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">
						{\App\Language::translate('LBL_PORT', $QUALIFIED_MODULE)}
					</label>
					<div class="controls col-md-8">
						<input class="form-control" type="text" name="port" value="{$RECORD_MODEL->get('port')}" >
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">
						{\App\Language::translate('LBL_AUTHENTICATION', $QUALIFIED_MODULE)}
					</label>
					<div class="controls col-md-8">
						<input type="checkbox" name="authentication"  {if $RECORD_MODEL->get('authentication') eq 1} checked {/if}>
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
						{\App\Language::translate('LBL_AUTHENTICATION', $QUALIFIED_MODULE)}
					</label>
					<div class="controls col-md-8">
						<input type="checkbox" name="authentication"  {if $RECORD_MODEL->get('authentication') eq 1} checked {/if}>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">
						{\App\Language::translate('LBL_INDIVIDUAL_DELIVERY', $QUALIFIED_MODULE)}
					</label>
					<div class="controls col-md-8">
						<input type="checkbox" name="individual_delivery"  {if $RECORD_MODEL->get('individual_delivery') eq 1} checked {/if}>
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
						{\App\Language::translate('LBL_FROM_EMAIL', $QUALIFIED_MODULE)}
					</label>
					<div class="controls col-md-8">
						<input class="form-control" type="email" value="{$RECORD_MODEL->get('from_email')}" name="from_email" >
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
						{\App\Language::translate('LBL_REPLY_TO', $QUALIFIED_MODULE)}
					</label>
					<div class="controls col-md-8">
						<input class="form-control" type="email" name="replay_to"  value="{$RECORD_MODEL->get('replay_to')}">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">
						{\App\Language::translate('LBL_OPTIONS', $QUALIFIED_MODULE)}
					</label>
					<div class="controls col-md-8">
						<textarea class="form-control" name="options" value="{$RECORD_MODEL->get('options')}"></textarea>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="submit" class="btn btn-success submitButton">{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
			<button type="button" class="btn btn-warning dismiss" data-dismiss="modal">{\App\Language::translate('LBL_CLOSE', $QUALIFIED_MODULE)}</button>
		</div>
	</form>


{/strip}
