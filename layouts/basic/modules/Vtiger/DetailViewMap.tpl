{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-DetailViewMap -->
	<input type="hidden" id="coordinates" value="{\App\Purifier::encodeHtml(\App\Json::encode($COORRDINATES))}">
	<div id="mapid" class="u-min-h-85vh"></div>
	<!-- /tpl-Base-DetailViewMap -->
{/strip}
