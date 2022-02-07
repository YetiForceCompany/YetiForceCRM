{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-AdminAccess-Permissions -->
	<div class="actions mb-2">
		{foreach from=$LINKS item=LINK}
			{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $QUALIFIED_MODULE) BUTTON_VIEW='listViewBasic' MODULE_NAME=$QUALIFIED_MODULE}
		{/foreach}
	</div>
	<form class="js-filter-form" data-js="container">
		<table id="dataTableExamplePermissions" class="table table-sm table-striped display text-center mt-2 js-data-table" data-url="index.php?module=AdminAccess&parent=Settings&action=GetData&mode=access">
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
							{include file=\App\Layout::getTemplatePath($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(), $MODULE_NAME) SEARCH_INFO=[] MODULE=$MODULE_NAME}
						</td>
					{/foreach}
					<td></td>
				</tr>
			</thead>
		</table>
	</form>
	<!-- /tpl-Settings-AdminAccess-Permissions -->
{/strip}
