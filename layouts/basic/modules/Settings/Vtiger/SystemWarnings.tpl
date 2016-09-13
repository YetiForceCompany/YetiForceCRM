{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
	<div class="warningsIndexPage">
		<div class="row">
			<div class="col-md-9 marginRight10">
				<div class="marginRight10" id="warningsContent">

				</div>
			</div>
			<div class="col-md-3 siteBarRight">
				<h4>{vtranslate('LBL_WARNINGS_FOLDERS', $MODULE)}</h4>
				<hr>
				<div class="text-center marginBottom5">
					<input class="switchBtn" type="checkbox" title="{vtranslate('LBL_WARNINGS_SWITCH',$MODULE)}" data-size="normal" data-label-width="5" data-handle-width="90" data-on-text="{vtranslate('LBL_ACTIVE',$MODULE)}" data-off-text="{vtranslate('LBL_ALL')}">
				</div>
				<hr>
				<input type="hidden" id="treeValues" value="{Vtiger_Util_Helper::toSafeHTML($FOLDERS)}">
				<div id="jstreeContainer"></div>
			</div>
		</div>
	</div>
{/strip}
