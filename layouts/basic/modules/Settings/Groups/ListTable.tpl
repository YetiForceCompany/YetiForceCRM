{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Groups-ListTable -->
	<div class="o-breadcrumb widget_header row mb-2">
		<div class="col-md-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
		</div>
	</div>
	<div class="actions mb-2">
		{if !empty($LINKS)}
			{include file=\App\Layout::getTemplatePath('ButtonLinks.tpl', $QUALIFIED_MODULE) LINKS=$LINKS BUTTON_VIEW='listViewBasic' MODULE=$QUALIFIED_MODULE}
		{/if}
	</div>
	<form class="js-filter-form" data-js="container">
		<table id="dataTableGroups" class="table table-sm table-striped display nowrap text-center mt-2 js-data-table u-cursor-pointer" data-url="index.php?module=Groups&parent=Settings&view=GetData">
			<thead>
				<tr>
					{foreach key=FIELD_NAME item=FIELD_MODEL from=$MODULE_MODEL->getListFields()}
						<th class="text-center" data-name="{$FIELD_NAME}" data-orderable="{$FIELD_MODEL->get('sort')}">{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_MODEL->getName(true))}</th>
					{/foreach}
					<th></th>
				</tr>
				<tr class="js-search-container">
					{foreach key=FIELD_NAME item=FIELD_MODEL from=$MODULE_MODEL->getListFields()}
						<td class="pl-1">
							{assign var=FIELD_UI_TYPE_MODEL value=$FIELD_MODEL->getUITypeModel()}
							{include file=\App\Layout::getTemplatePath($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(), $QUALIFIED_MODULE) SEARCH_INFO=[] MODULE=$MODULE_NAME RECORD=null}
						</td>
					{/foreach}
					<td></td>
				</tr>
			</thead>
		</table>
	</form>
	<!-- /tpl-Settings-Groups-ListTable -->
{/strip}
