{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-RepeatEvents -->
	<div class="modal-body">
		<div class="col-12 px-0 mb-3 form-row m-0">
			<div class="col-12 col-lg-4 px-0">
				<button class="btn btn-primary btn-sm typeSavingBtn" data-value="2">
					{App\Language::translate('LBL_UPDATE_THIS_EVENT', $MODULE_NAME)}
				</button>
			</div>
			<div class="col-12 col-lg-8 px-0">
				{App\Language::translate('LBL_UPDATE_THIS_EVENT_DESCRIPTION', $MODULE_NAME)}
			</div>
		</div>
		<div class="col-12 px-0 mb-3 form-row m-0">
			<div class="col-12 col-lg-4 px-0">
				<button class="btn btn-primary btn-sm typeSavingBtn" data-value="3">
					{App\Language::translate('LBL_UPDATE_FUTURE_EVENTS', $MODULE_NAME)}
				</button>
			</div>
			<div class="col-12 col-lg-8 px-0">
				{App\Language::translate('LBL_UPDATE_FUTURE_EVENTS_DESCRIPTION', $MODULE_NAME)}
			</div>
		</div>
		<div class="col-12 px-0 mb-3 form-row m-0">
			<div class="col-12 col-lg-4 px-0">
				<button class="btn btn-primary btn-sm typeSavingBtn" type="button" data-value="1">
					{App\Language::translate('LBL_UPDATE_ALL_EVENTS', $MODULE_NAME)}
				</button>
			</div>
			<div class="col-12 col-lg-8 px-0">
				{App\Language::translate('LBL_UPDATE_ALL_EVENTS_DESCRIPTION', $MODULE_NAME)}
			</div>
		</div>
	</div>
	<!-- /tpl-Calendar-RepeatEvents -->
{/strip}
