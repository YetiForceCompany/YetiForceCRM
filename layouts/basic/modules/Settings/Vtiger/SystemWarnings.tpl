{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div class="tpl-SystemWarnings warningsIndexPage">
		<div class="row">
			<div class="col-md-9 mr-2">
				<div class="mr-2" id="warningsContent">

				</div>
			</div>
			<div class="col-md-3 siteBarRight">
				<h4>{\App\Language::translate('LBL_WARNINGS_FOLDERS', $MODULE)}</h4>
				<hr>
				<div class="text-center mb-2">
					<div class="btn-group btn-group-toggle" data-toggle="buttons">
						<label class="btn btn-outline-primary">
							<input class="js-switch--warnings" type="radio" name="options" id="option1" data-js="change"
								   autocomplete="off"
							> {\App\Language::translate('LBL_ACTIVE',$MODULE)}
						</label>
						<label class="btn btn-outline-primary active">
							<input class="js-switch--warnings" type="radio" name="options" id="option2" data-js="change"
								   autocomplete="off" checked
							> {\App\Language::translate('LBL_ALL')}
						</label>
					</div>

				</div>
				<hr>
				<input type="hidden" id="treeValues" value="{\App\Purifier::encodeHtml($FOLDERS)}">
				<div id="jstreeContainer"></div>
			</div>
		</div>
	</div>
{/strip}
