<div class="container-fluid configContainer" style="margin-top:10px;">
	<h3>{vtranslate('LBL_MAIL_GENERAL_CONFIGURATION', $QUALIFIED_MODULE)}</h3>&nbsp;{vtranslate('LBL_MAIL_GENERAL_CONFIGURATION_DESCRIPTION', $QUALIFIED_MODULE)}<hr>
	{assign var=ALL_ACTIVEUSER_LIST value=$USER_MODEL->getAccessibleUsers()}
	<ul id="tabs" class="nav nav-tabs nav-justified" data-tabs="tabs">
		<li class="active"><a href="#configuration" data-toggle="tab">{vtranslate('LBL_MAIL_ICON_CONFIG', $QUALIFIED_MODULE)}</a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="configuration">
			{assign var=CONFIG value=$MODULE_MODEL->getConfig('mailIcon')}
			<div class="row-fluid">
				<div class="span1 pagination-centered">
					<input class="configCheckbox" type="checkbox" name="showMailIcon" id="showMailIcon" value="1" {if $CONFIG['showMailIcon']=='true'}checked=""{/if}>
				</div>
				<div class="span11">
					<label for="showMailIcon">{vtranslate('LBL_SHOW_MAIL_ICON', $QUALIFIED_MODULE)}</label>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span1 pagination-centered">
					<input class="configCheckbox" type="checkbox" name="showMailAccounts" id="showMailAccounts" value="1" {if $CONFIG['showMailAccounts']=='true'}checked=""{/if}>
				</div>
				<div class="span11">
					<label for="showMailAccounts">{vtranslate('LBL_SHOW_MAIL_ACCOUNTS', $QUALIFIED_MODULE)}</label>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span1 pagination-centered">
					<input class="configCheckbox" type="checkbox" name="showNumberUnreadEmails" id="showNumberUnreadEmails" value="1" {if $CONFIG['showNumberUnreadEmails']=='true'}checked=""{/if}>
				</div>
				<div class="span11">
					<label for="showNumberUnreadEmails">{vtranslate('LBL_NUMBER_UNREAD_EMAILS', $QUALIFIED_MODULE)}</label>
				</div>
			</div>
		</div>
	</div>
</div>
