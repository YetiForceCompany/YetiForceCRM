{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
<div class=" LangManagement">
	<div class="widget_header row">
		<div class="col-md-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			&nbsp;{vtranslate('LBL_Module_desc', $QUALIFIED_MODULE)}
		</div>
	</div>
	<hr>
	<div class="">
        <div class="contents tabbable">
            <ul class="nav nav-tabs layoutTabs massEditTabs">
                <li class="active">
					<a data-toggle="tab" href="#lang_list">
						<strong>{vtranslate('LBL_TAB_LIST', $QUALIFIED_MODULE)}</strong>
					</a>
				</li>
                <li class="edit_lang">
					<a data-toggle="tab" href="#edit_lang" data-mode="editLang">
						<strong>{vtranslate('LBL_TAB_EDITLANG', $QUALIFIED_MODULE)}</strong>
					</a>
				</li>
				<li class="editHelpIcon">
					<a data-toggle="tab" href="#editHelpIcon" data-mode="editHelpIcon">
						<strong>{vtranslate('LBL_EDIT_HELP_ICON', $QUALIFIED_MODULE)}</strong>
					</a>
				</li>
                <li class="lang_stats">
					<a data-toggle="tab" href="#lang_stats">
						<strong>{vtranslate('LBL_TAB_STATS', $QUALIFIED_MODULE)}</strong>
					</a>
				</li>
            </ul>
			<div class="tab-content layoutContent padding10 themeTableColor overflowVisible">
				<div class="tab-pane active" id="lang_list">
					{include file='LangList.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
				</div>
				<div class="tab-pane" id="edit_lang" data-mode="editLang"></div>
				<div class="tab-pane" id="editHelpIcon" data-mode="editHelpIcon"></div>
				<div class="tab-pane" id="lang_stats">
					{include file='Stats.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
				</div>
			</div>
		</div>
	</div>
	<div class="clearfix"></div>
	{include file='AddLanguage.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
	{include file='AddTranslation.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
{/strip}
