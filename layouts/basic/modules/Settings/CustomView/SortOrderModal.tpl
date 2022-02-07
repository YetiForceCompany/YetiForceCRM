{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-CustomView-SortOrderModal -->
	<div class="col-md-12">
		<div class="alert alert-danger alert-dismissible fade show mb-0" role="alert">
			<h4 class="alert-heading">{\App\Language::translate('LBL_ATTENTION', $MODULE_NAME)}</h4>
			{\App\Language::translate('LBL_SORTING_SETTINGS_WORNING', $MODULE_NAME)}
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	</div>
	<form class="js-sorting-form" data-js="submit">
		<input type="hidden" id="cvid" name="cvid" value="{$CVID}" />
		<input type="hidden" id="sourceModule" value="{$SOURCE_MODULE}" />
		<input type="hidden" id="orderBy" value="{\App\Purifier::encodeHtml(\App\Json::encode($SORT_ORDER_BY))}">
	</form>
	{include file=\App\Layout::getTemplatePath('Modals/SortOrderModal.tpl', $MODULE_NAME)}
	<!-- /tpl-Settings-CustomView-SortOrderModal -->
{/strip}
