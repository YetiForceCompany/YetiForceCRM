{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-RepeatEventsDelete -->
	<div class="modal-body">
		<div class="col-12">
			<div class="col-12 paddingLRZero marginBottom10px">
				<div class="col-4">
					<button class="btn btn-primary btn-sm typeSavingBtn" data-value="2">
						{App\Language::translate('LBL_DELETE_THIS_EVENT', $MODULE)}
					</button>
				</div>
				<div class="col-8">
					{App\Language::translate('LBL_DELETE_THIS_EVENT_DESCRIPTION', $MODULE)}
				</div>
			</div>
			<div class="col-12 paddingLRZero marginBottom10px">
				<div class="col-4">
					<button class="btn btn-primary btn-sm typeSavingBtn" data-value="3">
						{App\Language::translate('LBL_DELETE_FUTURE_EVENTS', $MODULE)}
					</button>
				</div>
				<div class="col-8">
					{App\Language::translate('LBL_DELETE_FUTURE_EVENTS_DESCRIPTION', $MODULE)}
				</div>
			</div>
			<div class="col-12 paddingLRZero marginBottom10px">
				<div class="col-4">
					<button class="btn btn-primary btn-sm typeSavingBtn" data-value="1">
						{App\Language::translate('LBL_DELETE_ALL_EVENTS', $MODULE)}
					</button>
				</div>
				<div class="col-8">
					{App\Language::translate('LBL_DELETE_ALL_EVENTS_DESCRIPTION', $MODULE)}
				</div>
			</div>
		</div>
	</div>
	<!-- /tpl-Calendar-RepeatEventsDelete -->
{/strip}
