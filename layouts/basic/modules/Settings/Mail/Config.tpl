{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="configContainer tpl-Settings-Mail-Config">
		<div class="o-breadcrumb widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		{assign var=ALL_ACTIVEUSER_LIST value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
		<ul class="nav nav-tabs mt-2 mb-2" role="tabs">
			<li class="nav-item"><a class="nav-link active" href="#config" data-toggle="tab" role="tab">{\App\Language::translate('LBL_MAIL_ICON_CONFIG', $QUALIFIED_MODULE)}</a></li>
			<li class="nav-item"><a class="nav-link" href="#signature" data-toggle="tab" role="tab">{\App\Language::translate('LBL_SIGNATURE', $QUALIFIED_MODULE)}</a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane fade show active ml-3" id="config" role="tabpanel" aria-labelledby="home-tab">
				{assign var=CONFIG value=$MODULE_MODEL->getConfig('mailIcon')}
				<div class="d-flex">
					<div class="">
						<input class="configCheckbox" type="checkbox" name="showMailIcon" id="showMailIcon" data-type="mailIcon" value="1" {if $CONFIG['showMailIcon']=='true'}checked="" {/if}>
					</div>
					<div class="ml-1">
						<label for="showMailIcon">{\App\Language::translate('LBL_SHOW_MAIL_ICON', $QUALIFIED_MODULE)}</label>
					</div>
				</div>
				<div class="d-flex">
					<div class="float-left pagination-centered">
						<input class="configCheckbox" type="checkbox" name="showNumberUnreadEmails" id="showNumberUnreadEmails" data-type="mailIcon" value="1" {if $CONFIG['showNumberUnreadEmails']=='true'}checked="" {/if}>
					</div>
					<div class="ml-1">
						<label for="showNumberUnreadEmails">{\App\Language::translate('LBL_NUMBER_UNREAD_EMAILS', $QUALIFIED_MODULE)}</label>
					</div>
				</div>
			</div>
			<div class="tab-pane fade" id="signature" role="tabpanel">
				{assign var=CONFIG_SIGNATURE value=$MODULE_MODEL->getConfig('signature')}
				<div>
					<input class="configCheckbox" type="checkbox" name="addSignature" id="addSignature" data-type="signature" value="1" {if $CONFIG_SIGNATURE['addSignature']=='true'}checked="" {/if}>
					<label class="ml-1" for="addSignature">{\App\Language::translate('LBL_ADD_SIGNATURE', $QUALIFIED_MODULE)}</label>
				</div>
				<div class="form-row js-container-variable" data-js="container">
					{include file=\App\Layout::getTemplatePath('VariablePanel.tpl') SELECTED_MODULE='Users' PARSER_TYPE='mail'}
				</div>
				<hr />
				<div class="row">
					<div class="col-md-12">
						<textarea id="signatureEditor" class="js-editor" data-js="ckeditor" name="signature" data-purify-mode="Html">{$CONFIG_SIGNATURE['signature']}</textarea>
					</div>
				</div>
				<br />
				<div class="row">
					<div class="col-md-12">
						<button class="btn btn-success float-right js-save-signature" data-js="click">
							<span class="fa fa-check u-mr-5px"></span><strong>{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
