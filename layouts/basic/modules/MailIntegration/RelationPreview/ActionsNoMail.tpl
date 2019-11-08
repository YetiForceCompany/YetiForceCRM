{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-MailIntegration-RelationPreview-ActionsNoMail -->
	<div>
		{if \App\Privilege::isPermitted('OSSMailView', 'CreateView')}
			<div class="alert alert-warning mb-1 p-1 d-flex flex-wrap align-items-center" role="alert">
				{\App\Language::translate('LBL_MAIL_NOT_FOUND_IN_DB',$MODULE_NAME)}
				<button class="btn btn-outline-dark btn-sm ml-auto js-import-mail js-popover-tooltip" data-content="{\App\Language::translate('LBL_IMPORT_MAIL_MANUALLY_DESC', $MODULE_NAME)}" data-js="popover">
					<span class="fas fa-download mr-1"></span>
					{\App\Language::translate('LBL_IMPORT_MAIL_MANUALLY',$MODULE_NAME)}
				</button>
			</div>
		{/if}
		{if !empty($RELATIONS)}
			<div class="mb-1">
				<ul class="list-group">
					{foreach item="RELATION" from=$RELATIONS}
						{include file=\App\Layout::getTemplatePath('RelationPreview/Row.tpl', $MODULE_NAME) ROW=$RELATION REMOVE_RECORD=false}
					{/foreach}
				</ul>
			</div>
		{/if}
	</div>
<!-- /tpl-MailIntegration-RelationPreview-ActionsNoMail -->
{/strip}
