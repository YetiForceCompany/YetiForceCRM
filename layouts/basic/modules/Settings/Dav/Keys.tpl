{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="" id="DavKeysContainer">
		<div class="widget_header row mb-2">
			<div class="col-md-8">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $QUALIFIED_MODULE)}
			</div>
			<div class="col-md-4 d-flex justify-content-end align-items-center">
				<button class="btn btn-primary js-add-key" data-js="click">
					<span class="fas fa-plus mr-1"></span>{\App\Language::translate('LBL_ADD_KEY',$QUALIFIED_MODULE)}
				</button>
			</div>
		</div>
		<div class="contents">
			{if $ENABLEDAV}
				<div class="alert alert-warning">
					<button type="button" class="close" data-dismiss="alert">×</button>
					<h5 class="alert-heading">{\App\Language::translate('LBL_ALERT_DAV_NO_ACTIVE_TITLE', $QUALIFIED_MODULE)}</h5>
					<p>{\App\Language::translate('LBL_ALERT_DAV_NO_ACTIVE_DESC', $QUALIFIED_MODULE)}</p>
				</div>
			{/if}
			<div class="alert alert-info">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<h5 class="alert-heading">{\App\Language::translate('LBL_ALERT_DAV_CONFIG_TITLE', $QUALIFIED_MODULE)}</h5>
				<p>{\App\Language::translateArgs('LBL_ALERT_DAV_CONFIG_DESC', $QUALIFIED_MODULE,AppConfig::main('site_URL'))|unescape:"html"}</p>
			</div>
			<div>
				<div class="contents tabbable">
					<table class="table table-bordered  tableRWD table-sm listViewEntriesTable">
						<thead>
						<tr class="blockHeader">
							<th><strong>{\App\Language::translate('LBL_LOGIN',$QUALIFIED_MODULE)}</strong></th>
							<th><strong>{\App\Language::translate('LBL_KEY',$QUALIFIED_MODULE)}</strong></th>
							<th><strong>{\App\Language::translate('LBL_DISPLAY_NAME',$QUALIFIED_MODULE)}</strong></th>
							<th><strong>{\App\Language::translate('LBL_EMAIL',$QUALIFIED_MODULE)}</strong></th>
							<th><strong>{\App\Language::translate('LBL_ACTIVE_USER',$QUALIFIED_MODULE)}</strong></th>
							<th><strong>{\App\Language::translate('CardDAV',$QUALIFIED_MODULE)}</strong></th>
							<th><strong>{\App\Language::translate('CalDAV',$QUALIFIED_MODULE)}</strong></th>
							<th><strong>{\App\Language::translate('WebDAV',$QUALIFIED_MODULE)}</strong></th>
							<th><strong>{\App\Language::translate('LBL_COUNT_CARD',$QUALIFIED_MODULE)}</strong></th>
							<th><strong>{\App\Language::translate('LBL_COUNT_CAL',$QUALIFIED_MODULE)}</strong></th>
							<th><strong>{\App\Language::translate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
						</tr>
						</thead>
						<tbody>
						{assign var=AMOUNT_DATA value=$MODULE_MODEL->getAmountData()}
						{foreach from=$MODULE_MODEL->getAllKeys() item=RECORD}
							{if !empty($AMOUNT_DATA['addressbook'][$RECORD['addressbooksid']])}
								{assign var=ADDRESSBOOK value=$AMOUNT_DATA['addressbook'][$RECORD['addressbooksid']]}
							{else}
								{assign var="ADDRESSBOOK" value=""}
							{/if}
							{if !empty($AMOUNT_DATA['calendar'][$RECORD['calendarsid']])}
								{assign var=CALENDAR value=$AMOUNT_DATA['calendar'][$RECORD['calendarsid']]}
							{else}
								{assign var=CALENDAR value=""}
							{/if}
							<tr data-user="{$RECORD['userid']}" class="js-tr-row" data-js="data/remove">
								<td>{$RECORD['user_name']}</td>
								<td>**********</td>
								<td>{$RECORD['displayname']}</td>
								<td>{$RECORD['email']}</td>
								<td>{\App\Language::translate($RECORD['status'],'Users')}</td>
								<td>{if $RECORD['addressbooksid']}{\App\Language::translate('LBL_YES')}{else}{\App\Language::translate('LBL_NO')}{/if}</td>
								<td>{if $RECORD['calendarsid']}{\App\Language::translate('LBL_YES')}{else}{\App\Language::translate('LBL_NO')}{/if}</td>
								<td>{\App\Language::translate('LBL_YES')}</td>
								<td>{if $ADDRESSBOOK}{$ADDRESSBOOK}{else}0{/if}</td>
								<td>{if $CALENDAR}{$CALENDAR}{else}0{/if}</td>
								<td>
									<button class="btn btn-danger js-delete-key ml-2 mr-1" data-js="click">
										<span class="fas fa-trash mr-1"></span>{\App\Language::translate('LBL_DELETE_KEY',$QUALIFIED_MODULE)}
									</button>
									<button class="btn btn-primary clipboard" data-copy-attribute="clipboard-text"
											data-clipboard-text="{App\Encryption::getInstance()->decrypt($RECORD['key'])}">
										<span class="fas fa-copy mr-1"></span>{\App\Language::translate('LBL_KEY',$QUALIFIED_MODULE)}
									</button>
								</td>
							</tr>
						{/foreach}
						</tbody>
					</table>
				</div>
			</div>
			<div class="modal addKeyContainer fade" tabindex="-1">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header contentsBackground">
							<h5 class="modal-title">
								<span class="fas fa-plus fa-sm mr-2"></span>{\App\Language::translate('LBL_ADD_KEY', $QUALIFIED_MODULE)}
							</h5>
							<button type="button" class="close" data-dismiss="modal"
									title="{\App\Language::translate('LBL_CLOSE')}">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<form class="form-horizontal">
								<div class="form-group form-row">
									<label class="col-sm-5 col-form-label u-text-small-bold">{\App\Language::translate('LBL_SELECT_USER', $QUALIFIED_MODULE)}</label>
									<div class="col-sm-7 controls">
										<select class="select user form-control" name="user"
												data-validation-engine="validate[required]">
											{foreach from=$USERS item=ITEM key=KEY}
												<option value="{$KEY}">{$ITEM->getDisplayName()}</option>
											{/foreach}
										</select>
									</div>
								</div>
								<div class="form-group form-row">
									<label class="col-sm-5 col-form-label u-text-small-bold">{\App\Language::translate('LBL_SELECT_TYPE', $QUALIFIED_MODULE)}</label>
									<div class="col-sm-7 controls">
										<select multiple="" class="select type form-control" name="type">
											{foreach from=$MODULE_MODEL->getTypes() item=ITEM}
												<option selected="" value="{$ITEM}">{$ITEM}</option>
											{/foreach}
										</select>
									</div>
								</div>
							</form>
						</div>
						{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
