{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class=" LangManagement">
		<div class="widget_header row">
			<div class="col-md-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
				&nbsp;{\App\Language::translate('LBL_Module_desc', $QUALIFIED_MODULE)}
			</div>
		</div>
		<hr>
		{if \AppConfig::performance('LOAD_CUSTOM_FILES')}
			<div class="alert alert-info fade in">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				{\App\Language::translate('LBL_CUSTOM_TYPE_INFO', $QUALIFIED_MODULE)}
			</div>
		{/if}
		<div class="">
			<div class="contents tabbable">
				<ul class="nav nav-tabs layoutTabs massEditTabs">
					<li class="active">
						<a data-toggle="tab" href="#lang_list">
							<strong>{\App\Language::translate('LBL_TAB_LIST', $QUALIFIED_MODULE)}</strong>
						</a>
					</li>
					<li class="edit_lang">
						<a data-toggle="tab" href="#edit_lang" data-mode="editLang">
							<strong>{\App\Language::translate('LBL_TAB_EDITLANG', $QUALIFIED_MODULE)}</strong>
						</a>
					</li>
					<li class="editHelpIcon">
						<a data-toggle="tab" href="#editHelpIcon" data-mode="editHelpIcon">
							<strong>{\App\Language::translate('LBL_EDIT_HELP_ICON', $QUALIFIED_MODULE)}</strong>
						</a>
					</li>
					<li class="lang_stats">
						<a data-toggle="tab" href="#lang_stats">
							<strong>{\App\Language::translate('LBL_TAB_STATS', $QUALIFIED_MODULE)}</strong>
						</a>
					</li>
				</ul>
				<div class="tab-content layoutContent padding10 themeTableColor overflowVisible">
					<div class="tab-pane active" id="lang_list">
						{include file=\App\Layout::getTemplatePath('LangList.tpl', $QUALIFIED_MODULE)}
					</div>
					<div class="tab-pane" id="edit_lang" data-mode="editLang"></div>
					<div class="tab-pane" id="editHelpIcon" data-mode="editHelpIcon"></div>
					<div class="tab-pane" id="lang_stats">
						{include file=\App\Layout::getTemplatePath('Stats.tpl', $QUALIFIED_MODULE)}
					</div>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
		{include file=\App\Layout::getTemplatePath('AddLanguage.tpl', $QUALIFIED_MODULE)}
		{include file=\App\Layout::getTemplatePath('AddTranslation.tpl', $QUALIFIED_MODULE)}
	{/strip}
