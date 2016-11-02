{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}

<div class=" configContainer">
	<div class="widget_header row">
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			&nbsp;{vtranslate('LBL_MAIL_GENERAL_CONFIGURATION_DESCRIPTION', $QUALIFIED_MODULE)}
		</div>
	</div>
	{assign var=ALL_ACTIVEUSER_LIST value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
	<ul id="tabs" class="nav nav-tabs nav-justified" data-tabs="tabs">
		<li class="active"><a href="#configuration" data-toggle="tab">{vtranslate('LBL_MAIL_ICON_CONFIG', $QUALIFIED_MODULE)}</a></li>
		<li><a href="#signature" data-toggle="tab">{vtranslate('LBL_SIGNATURE', $QUALIFIED_MODULE)}</a></li>
	</ul>
	<br />
	<div class="tab-content">
		<div class="tab-pane active" id="configuration">
			{assign var=CONFIG value=$MODULE_MODEL->getConfig('mailIcon')}
			<div class="col-xs-12">
				<div class="pull-left pagination-centered">
					<input class="configCheckbox" type="checkbox" name="showMailIcon" id="showMailIcon" data-type="mailIcon" value="1" {if $CONFIG['showMailIcon']=='true'}checked=""{/if}>
				</div>
				<div class="col-md-11 col-sm-10 col-xs-10">
					<label for="showMailIcon">{vtranslate('LBL_SHOW_MAIL_ICON', $QUALIFIED_MODULE)}</label>
				</div>
			</div>
			<div class="col-xs-12">
				<div class="pull-left pagination-centered">
					<input class="configCheckbox" type="checkbox" name="showMailAccounts" id="showMailAccounts" data-type="mailIcon" value="1" {if $CONFIG['showMailAccounts']=='true'}checked=""{/if}>
				</div>
				<div class="col-md-11 col-sm-10 col-xs-10">
					<label for="showMailAccounts">{vtranslate('LBL_SHOW_MAIL_ACCOUNTS', $QUALIFIED_MODULE)}</label>
				</div>
			</div>
			<div class="col-xs-12">
				<div class="pull-left pagination-centered">
					<input class="configCheckbox" type="checkbox" name="showNumberUnreadEmails" id="showNumberUnreadEmails" data-type="mailIcon" value="1" {if $CONFIG['showNumberUnreadEmails']=='true'}checked=""{/if}>
				</div>
				<div class="col-md-11 col-sm-10 col-xs-10">
					<label for="showNumberUnreadEmails">{vtranslate('LBL_NUMBER_UNREAD_EMAILS', $QUALIFIED_MODULE)}</label>
				</div>
			</div>
		</div>
		<div class="tab-pane" id="signature">
			{assign var=CONFIG_SIGNATURE value=$MODULE_MODEL->getConfig('signature')}
			<div>
				<input class="configCheckbox" type="checkbox" name="addSignature" id="addSignature" data-type="signature" value="1" {if $CONFIG_SIGNATURE['addSignature']=='true'}checked=""{/if}>
				&nbsp;<label for="addSignature">{vtranslate('LBL_ADD_SIGNATURE', $QUALIFIED_MODULE)}</label>
			</div>
			<hr />
			<div class="row">
				<div class="col-md-12">
					<textarea id="signatureCkEditor" class="ckEditorSource" name="signature">{$CONFIG_SIGNATURE['signature']}</textarea>
				</div>
			</div>
			<br />
			<div class="row">
				<div class="col-md-12">
					<button class="btn btn-success pull-right"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
				</div>
			</div>
		</div>
	</div>
</div>
