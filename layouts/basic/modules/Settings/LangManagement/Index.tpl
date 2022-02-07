{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class=" LangManagement">
		<div class="o-breadcrumb widget_header row">
			<div class="col-md-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="mt-2">
			<div class="contents tabbable">
				<ul class="nav nav-tabs layoutTabs massEditTabs">
					<li class="nav-item">
						<a data-toggle="tab" href="#lang_list" class="nav-link active">
							<strong>{\App\Language::translate('LBL_TAB_LIST', $QUALIFIED_MODULE)}</strong>
						</a>
					</li>
					<li class="edit_lang nav-item">
						<a data-toggle="tab" href="#edit_lang" data-mode="editLang" class="nav-link">
							<strong>{\App\Language::translate('LBL_TAB_EDITLANG', $QUALIFIED_MODULE)}</strong>
						</a>
					</li>
					<li class="lang_stats nav-item">
						<a data-toggle="tab" href="#lang_stats" class="nav-link">
							<strong>{\App\Language::translate('LBL_TAB_STATS', $QUALIFIED_MODULE)}</strong>
						</a>
					</li>
				</ul>
				<div class="tab-content layoutContent padding10 themeTableColor overflowVisible">
					<div class="tab-pane active" id="lang_list">
						{include file=\App\Layout::getTemplatePath('LangList.tpl', $QUALIFIED_MODULE)}
					</div>
					<div class="tab-pane" id="edit_lang" data-mode="editLang"></div>
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
