{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Modals-InterestsConflictUsers -->
	<div class="modal-body mb-0">
		{if empty($BASE_RECORD)}
			<div class="alert alert-warning mb-0" role="alert">
				<h4 class="alert-heading mb-1">
					<span class="fas fa-exclamation-triangle pr-3"></span>
					{\App\Language::translate('LBL_RELATION_NOT_FOUND')}
				</h4>
			</div>
		{else}
			<form class="form-horizontal js-modal-form" data-js="container">
				<input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}" />
				<input type="hidden" name="sourceModuleName" value="{$MODULE_NAME}" />
				<input type="hidden" name="baseRecord" value="{$BASE_RECORD}" />
				<input type="hidden" name="baseModuleName" value="{$BASE_MODULE_NAME}" />
			</form>
			<p>
				{\App\Language::translate('LBL_INTERESTS_CONFLICT_CONFIRMATIONS_FOR')}:&nbsp;
				{\App\Record::getHtmlLink($BASE_RECORD,$BASE_MODULE_NAME, \App\Config::main('href_max_length'))}
			</p>
			<table class="table table-sm dataTable">
				<thead>
					<tr>
						<th class="text-center">{\App\Language::translate('LBL_USER', $MODULE_NAME)}</th>
						<th class="text-center">{\App\Language::translate('LBL_CONFLICT_OF_INTEREST', $MODULE_NAME)}</th>
						<th class="text-center">{\App\Language::translate('LBL_DATE')}</th>
						<th class="text-center">{\App\Language::translate('LBL_ACTIONS')}</th>
					</tr>
				</thead>
				<tbody>
					{foreach item=ITEM from=$USERS}
						{if \App\User::isExists($ITEM['user_id'])}
							<tr data-id="{$ITEM['user_id']}">
								<th scope="row" class="text-center">
									{\App\Fields\Owner::getUserLabel($ITEM['user_id'])}
								</th>
								<th scope="row" class="text-center">
									{if $ITEM['status'] == \App\Components\InterestsConflict::CONF_STATUS_CONFLICT_NO}
										<span class="fas fa-times text-success js-popover-tooltip js-change-icon" aria-hidden="true" data-content="{\App\Language::translate('LBL_INTERESTS_CONFLICT_CONFIRM_NO')}" data-placement="top" data-js="popover"></span>
									{elseif $ITEM['status'] == \App\Components\InterestsConflict::CONF_STATUS_CANCELED}
										<span class="fas fa-slash text-dark js-popover-tooltip js-change-icon" aria-hidden="true" data-content="{\App\Language::translate('LBL_INTERESTS_CONFLICT_CONFIRM_CANCELED')}" data-placement="top" data-js="popover"></span>
									{else}
										<span class="fas fa-check text-danger js-popover-tooltip js-change-icon" aria-hidden="true" data-content="{\App\Language::translate('LBL_INTERESTS_CONFLICT_CONFIRM_YES')}" data-placement="top" data-js="popover"></span>
									{/if}
									<span class="d-none" aria-hidden="true">{$ITEM['status']}</span>
								</th>
								<td class="text-center">
									{\App\Fields\DateTime::formatToDisplay($ITEM['date_time'])}
								</td>
								<td class="text-center">
									{if $ITEM['status'] == \App\Components\InterestsConflict::CONF_STATUS_CONFLICT_YES}
										<button type="button" class="btn btn-info btn-xs js-popover-tooltip js-ic-canceled-btn" data-user="{$ITEM['user_id']}" data-content="{\App\Language::translate('BTN_INTERESTS_CONFLICT_SET_CANCELED')}" data-placement="top" data-js="popover">
											<span class="fas fa-minus"></span>
										</button>
									{/if}
								</td>
							</tr>
						{/if}
					{/foreach}
				</tbody>
			</table>
		{/if}
	</div>
	<!-- /tpl-Base-Modals-InterestsConflictUsers -->
{/strip}
