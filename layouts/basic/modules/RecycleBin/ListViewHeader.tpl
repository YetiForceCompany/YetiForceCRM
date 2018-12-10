{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<div class="tpl-RecycleBin-ListViewHeader listViewPageDiv">
	<div class="listViewTopMenuDiv noprint">
		<div class="listViewActionsDiv row">
			<div class="col-12 d-inline-flex flex-wrap">
				<div class="c-list__buttons d-flex flex-wrap flex-sm-nowrap u-w-sm-down-100">
					{assign var=LINKS value=[]}
					{if $LISTVIEW_MASSACTIONS}
						{assign var=LINKS value=$LISTVIEW_MASSACTIONS}
					{/if}
					{if isset($LISTVIEW_LINKS['LISTVIEW'])}
						{assign var=LINKS value=array_merge($LINKS,$LISTVIEW_LINKS['LISTVIEW'])}
					{/if}
					{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') LINKS=$LINKS TEXT_HOLDER='LBL_ACTIONS' BTN_ICON='fa fa-list' CLASS='listViewMassActions mr-sm-1 mb-1 mb-sm-0 c-btn-block-sm-down'}
					{foreach item=LINK from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
						{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='listView' CLASS='mr-sm-1 mb-1 c-btn-block-sm-down'}
					{/foreach}
				</div>
				<div class="btn-group mr-md-1 c-btn-block-sm-down">
					<button class="btn btn-light addButton addBookmark">
						<span class="fas fa-trash-alt mr-1"></span>
						{\App\Language::translate('LBL_REMOVE_ALL', $MODULE)}
					</button>
				</div>
				<div class="customFilterMainSpan ml-auto mx-xl-auto">
					{if $MODULE_LIST|@count gt 0}
						<select class="select2 form-control js-source-module" data-js="value">
							{foreach item=MODULEMODEL from=$MODULE_LIST}
								<option value="{$MODULEMODEL->get('name')}"
										{if $SOURCE_MODULE eq $MODULEMODEL->get('name')}selected=""{/if}>
									{\App\Language::translate($MODULEMODEL->get('name'),$MODULEMODEL->get('name'))}
								</option>
							{/foreach}
						</select>
					{/if}
				</div>
				<div class="c-list__right-container d-flex flex-nowrap u-overflow-scroll-xs-down">
					{if (method_exists($MODULE_MODEL,'isPagingSupported') && ($MODULE_MODEL->isPagingSupported()  eq true)) || !method_exists($MODULE_MODEL,'isPagingSupported')}
						{include file=\App\Layout::getTemplatePath('Pagination.tpl', $MODULE)}
						<input type="hidden" id="recordsCount" value=""/>
						<input type="hidden" id="selectedIds" name="selectedIds"/>
						<input type="hidden" id="excludedIds" name="excludedIds"/>
					{/if}
				</div>
			</div>
		</div>
	</div>
	<div id="selectAllMsgDiv" class="alert-block msgDiv noprint">
		<strong><a id="selectAllMsg" href="#">{\App\Language::translate('LBL_SELECT_ALL',$MODULE_NAME)}
				&nbsp;{\App\Language::translate($MODULE_NAME ,$MODULE_NAME)}
				&nbsp;(<span id="totalRecordsCount"></span>)</a></strong>
	</div>
	<div id="deSelectAllMsgDiv" class="alert-block msgDiv noprint">
		<strong><a id="deSelectAllMsg" href="#">{\App\Language::translate('LBL_DESELECT_ALL_RECORDS',$MODULE_NAME)}</a></strong>
	</div>
	<div class="listViewContentDiv" id="listViewContents">
{/strip}

