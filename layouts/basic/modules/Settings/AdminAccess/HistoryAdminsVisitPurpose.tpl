{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-AdminAccess-HistoryAdminsVisitPurpose -->
	<div id="dataTableHistoryAdminsVisitPurpose">
		<form class="js-form-single-save js-validate-form" data-js="container|validationEngine">
			<input type="hidden" name="parent" value="Settings">
			<input type="hidden" name="module" value="{$MODULE_NAME}">
			<input type="hidden" name="action" value="SaveConfigForm">
			{include file=\App\Layout::getTemplatePath('ConfigForm.tpl','Vtiger/Utils')}
		</form>
		<form class="js-filter-form" data-js="container">
			<table class="table table-sm table-striped display text-center mt-2 js-data-table" data-url="index.php?module=AdminAccess&parent=Settings&action=GetData&mode=historyVisitPurpose">
				<thead>
					<tr>
						{foreach key=FIELD_NAME item=FIELD_MODEL from=$MODULE_MODEL->getStructure('visitPurpose')}
							<th data-name="{$FIELD_NAME}" data-orderable="{$FIELD_MODEL->get('sort')}">{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_MODEL->getName(true))}</th>
						{/foreach}
					</tr>
					<tr class="js-search-container">
						{foreach key=FIELD_NAME item=FIELD_MODEL from=$MODULE_MODEL->getStructure('visitPurpose')}
							<td class="pl-1">
								{assign var=FIELD_UI_TYPE_MODEL value=$FIELD_MODEL->getUITypeModel()}
								{include file=\App\Layout::getTemplatePath($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(), $MODULE_NAME) SEARCH_INFO=[] MODULE=$MODULE_NAME}
							</td>
						{/foreach}
					</tr>
				</thead>
			</table>
		</form>
	</div>
	<!-- /tpl-Settings-AdminAccess-HistoryAdminsVisitPurpose -->
{/strip}
