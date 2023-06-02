{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Filters-ExtraSources -->
	{function SHOW_NAV_ROW SOURCE_ID='_SOURCE_ID_' SOURCE=['label'=> '_LABEL_', 'color'=> '_COLOR_'] IS_EDITABLE=true}
		<li class="m-0 p-0 col-12 mb-1 js-filter__item__container" data-js="classs: d-none">
			<div class="mr-0 pr-0 col-12 form-row d-flex align-items-center">
				<div>
					<input value="{$SOURCE_ID}" type="checkbox" id="sourceId{$SOURCE_ID}" class="js-filter__item__val alignMiddle mr-2"
						{if !empty($SIDEBARWIDGET->get('history')) && in_array($SOURCE_ID, $SIDEBARWIDGET->get('history'))}checked{/if}>
				</div>
				<label class="m-0 p-0 col js-filter__item__value u-text-ellipsis"
					for="sourceId{$SOURCE_ID}">
					<div class="d-inline-block align-middle mr-1 u-w-1em u-h-1em js-background" style="background: {$SOURCE['color']};" data-js="container"></div>
					<span class="js-label" data-js="container">{$SOURCE['label']}</span>
				</label>
				{if $IS_EDITABLE}
					<div class="float-right">
						<button class="btn btn-success btn-xs js-source-modal"
							title="{\App\Language::translate('LBL_EDIT', $MODULE_NAME)}"
							data-id="{$SOURCE_ID}" data-js="click">
							<span class="fa-solid fa-pen-to-square"></span>
						</button>
						<button class="btn btn-danger btn-xs ml-2 js-source-delete"
							title="{\App\Language::translate('LBL_DELETE', $MODULE_NAME)}"
							data-id="{$SOURCE_ID}" data-js="click">
							<span class="fas fa-trash-alt"></span>
						</button>
					</div>
				{/if}
			</div>
		</li>
	{/function}
	<div class="card bg-light">
		<div class="card-header p-1 pl-2">
			{\App\Language::translate($SIDEBARWIDGET->get('linklabel'), $MODULE_NAME, null, true, 'Calendar')}
			{if {\App\Privilege::isPermitted($MODULE_NAME, 'CalendarExtraSourcesCreate')} }
				<div class="float-right ml-1">
					<button class="btn btn-success btn-xs js-source-modal" data-js="click">
						<span class="fa-solid fa-plus"></span>
					</button>
				</div>
			{/if}
		</div>
		<div class="card-body row p-1">
			<div class="col-12">
				<div class="js-filter__container">
					<div class="input-group input-group-sm mb-3">
						<div class="input-group-prepend">
							<span class="input-group-text">
								<span class="fas fa-search fa-fw"></span>
							</span>
						</div>
						<input type="text" class="form-control js-filter__search" placeholder="{\App\Language::translate('LBL_TYPE_SEARCH', $MODULE_NAME, null, true, 'Calendar')}">
						<div class="input-group-append">
							<button title="{\App\Language::translate('LBL_REMOVE_FILTERING')}" class="btn btn-outline-secondary border-left-0 border js-filter__clear" type="button">
								<i class="fa fa-times"></i>
							</button>
						</div>
					</div>
					<div class="js-sidebar-filter-body js-filter__container_checkbox_list position-relative p-0 u-max-h-70vh" data-name="extraSources">
						<ul class="nav form-row js-extra-sources-nav" data-js="container">
							{foreach key=SOURCE_ID item=SOURCE from=$FILTER_DATA}
								{if \App\Privilege::isPermitted(\App\Module::getModuleName($SOURCE['target_module'])) }
									{SHOW_NAV_ROW SOURCE_ID=$SOURCE_ID SOURCE=$SOURCE IS_EDITABLE=($SOURCE['user_id'] == $USER_MODEL->getId() || $USER_MODEL->isAdminUser())}
								{/if}
							{/foreach}
						</ul>
					</div>
					<ul class="js-nav-template d-none" data-js="container">
						{SHOW_NAV_ROW}
					</ul>
				</div>
			</div>
		</div>
	</div>
	<!-- tpl-Base-Filters-ExtraSources -->
{/strip}
