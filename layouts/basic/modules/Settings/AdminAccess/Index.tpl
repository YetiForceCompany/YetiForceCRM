{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-AdminAccess-Index -->
<div>
	<div class="o-breadcrumb widget_header row mb-2">
		<div class="col-md-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	<div>
		<ul id="tabs" class="nav nav-tabs my-2 mr-0" data-tabs="tabs">
			<li class="nav-item">
				<a class="nav-link active" href="#Permissions" data-toggle="tab" data-name="Permissions">
					<span class="fas fa-history mr-2"></span>{\App\Language::translate('LBL_PERMISSIONS', $QUALIFIED_MODULE)}
				</a>
			</li>
		</ul>
	</div>
	<div id="my-tab-content" class="tab-content ml-1 mr-1">
		<div class="js-tab tab-pane active font-weight-normal" id="Permissions" data-js="data">
			<div class="actions mb-2">
				{foreach from=$LINKS item=LINK}
					{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $QUALIFIED_MODULE) BUTTON_VIEW='listViewBasic' MODULE_NAME=$QUALIFIED_MODULE}
				{/foreach}
			</div>
			<form class="js-filter-form" data-js="container">
				<table id="dataTableExample" class="table table-sm table-striped display text-center mt-2 js-data-table">
					<thead>
						<tr>
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$MODULE_MODEL->getListFields()}
								<th data-name="{$FIELD_NAME}" data-orderable="{$FIELD_MODEL->get('sort')}">{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_MODEL->getName(true))}</th>
							{/foreach}
							<th style="width:1%"></th>
						</tr>
						<tr class="js-search-container">
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$MODULE_MODEL->getListFields()}
								<td class="pl-1">
									{assign var=FIELD_UI_TYPE_MODEL value=$FIELD_MODEL->getUITypeModel()}
									{include file=\App\Layout::getTemplatePath($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(), $MODULE_NAME) SEARCH_INFO=[]}
								</td>
							{/foreach}
							<td></td>
						</tr>
					</thead>
				</table>
			</form>
		</div>
	</div>
</div>
<!-- /tpl-Settings-AdminAccess-Index -->
{/strip}
