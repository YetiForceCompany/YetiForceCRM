{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-Calendar-ActivityState -->
	<div class="js-activity-state modalEditStatus" data-js="container" tabindex="-1">
		{assign var=ID value=$RECORD->getId()}
		<div class="o-calendar__form w-100 d-flex flex-column">
			<h6 class="boxEventTitle text-muted text-center my-1">
				{\App\Language::translate('LBL_SET_RECORD_STATUS', $MODULE_NAME)}
			</h6>
			{include file=\App\Layout::getTemplatePath('Calendar/ActivityButtons.tpl', $MODULE_NAME)}
			{include file=\App\Layout::getTemplatePath('ActivityStateContent.tpl', $MODULE_NAME)}
			<div class="o-calendar__form__actions">
				<div class="d-flex flex-wrap">
					{foreach item=LINK from=$LINKS}
						{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME)}
					{/foreach}
					{if $RECORD->isEditable()}
						<a href="#" data-url="{$RECORD->getEditViewUrl()}" data-id="{$ID}"
							class="editRecord btn mt-1 btn-default mr-1"
							title="{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}">
							<span class="yfi yfi-full-editing-view summaryViewEdit"></span>
							<span class="ml-1">{\App\Language::translate('LBL_EDIT',$MODULE_NAME)}</span>
						</a>
					{/if}
					{if $RECORD->isViewable()}
						<a href="{$RECORD->getDetailViewUrl()}" class="btn mt-1 btn-default mr-1"
							title="{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS', $MODULE_NAME)}">
							<span class="fas fa-list summaryViewEdit"></span>
							<span class="ml-1">{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS', $MODULE_NAME)}</span>
						</a>
					{/if}
					<a href="#" class="btn mt-1 btn-danger js-summary-close-edit ml-auto"
						title="{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}">
						<span class="fas fa-times" title="{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}"></span>
						<span class="ml-1 d-none d-xl-inline">{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}</span>
					</a>
				</div>
			</div>
		</div>
	</div>
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}?&v={$YETIFORCE_VERSION}"></script>
	{/foreach}
	<!-- /tpl-Calendar-Calendar-ActivityState -->
{/strip}
