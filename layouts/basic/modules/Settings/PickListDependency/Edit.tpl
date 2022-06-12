{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-PickListDependency-Edit -->
	<div class="verticalScroll">
		<div class="o-breadcrumb widget_header row mb-3">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="contents js-picklist-dependent-container " data-js="container">
			<form id="pickListDependencyForm" name="tmp" method="post" action="index.php">
				{if !empty($RECORD_ID)}
					<input type="hidden" name="record" value="{$RECORD_ID}" />
				{/if}
				<div class="js-dependent-fields" data-js="container">
					{include file=\App\Layout::getTemplatePath('DependentFields.tpl', $QUALIFIED_MODULE)}
				</div>
				<div id="dependencyGraph" class="my-3 w-100 js-dependency-tables-container" data-js="container">
					{if $RECORD_ID}
						{include file=\App\Layout::getTemplatePath('ConditionList.tpl', $QUALIFIED_MODULE)}
					{/if}
				</div>
			</form>
		</div>
	</div>
	<!-- /tpl-Settings-PickListDependency-Edit -->
{/strip}
