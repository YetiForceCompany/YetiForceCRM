{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="" id="DavKeysContainer">
		<div class="widget_header row">
			<div class="col-md-8">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
				{\App\Language::translate('LBL_DAV_KEYS_DESCRIPTION', $QUALIFIED_MODULE)}
			</div>
			<div class="col-md-4"><button class="btn btn-primary addKey float-right marginTop20">{\App\Language::translate('LBL_ADD_KEY',$QUALIFIED_MODULE)}</button></div>
		</div>
		<div class="contents">
			{if $ENABLEDAV }
				<div class="alert alert-warning">
					<button type="button" class="close" data-dismiss="alert">×</button>
					<h4 class="alert-heading">{\App\Language::translate('LBL_ALERT_DAV_NO_ACTIVE_TITLE', $QUALIFIED_MODULE)}</h4>
					<p>{\App\Language::translate('LBL_ALERT_DAV_NO_ACTIVE_DESC', $QUALIFIED_MODULE)}</p>
				</div>	
			{/if}
			<div class="alert alert-info">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<h4 class="alert-heading">{\App\Language::translate('LBL_ALERT_DAV_CONFIG_TITLE', $QUALIFIED_MODULE)}</h4>
				<p>{\App\Language::translateArgs('LBL_ALERT_DAV_CONFIG_DESC', $QUALIFIED_MODULE,AppConfig::main('site_URL'))}</p>
			</div>
			<div>
				<div class="contents tabbable">
					<table class="table table-bordered  tableRWD table-condensed listViewEntriesTable">
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
							{foreach from=$MODULE_MODEL->getAllKeys() item=item key=key}
								{assign var=ADDRESSBOOK value=$AMOUNT_DATA['addressbook'][$item.addressbooksid]}
								{assign var=CALENDAR value=$AMOUNT_DATA['calendar'][$item.calendarsid]}
								<tr data-user="{$item.userid}" data-name="{$item.user_name}">
									<td>{$item.user_name}</td>
									<td>{$item.key}</td>
									<td>{$item.displayname}</td>
									<td>{$item.email}</td>
									<td>{\App\Language::translate($item.status,'Users')}</td>
									<td>{if $item.addressbooksid}{\App\Language::translate('LBL_YES')}{else}{\App\Language::translate('LBL_NO')}{/if}</td>
									<td>{if $item.calendarsid}{\App\Language::translate('LBL_YES')}{else}{\App\Language::translate('LBL_NO')}{/if}</td>
									<td>{\App\Language::translate('LBL_YES')}</td>
									<td>{if $ADDRESSBOOK}{$ADDRESSBOOK}{else}0{/if}</td>
									<td>{if $CALENDAR}{$CALENDAR}{else}0{/if}</td>
									<td>
										<button class="btn btn-danger deleteKey">{\App\Language::translate('LBL_DELETE_KEY',$QUALIFIED_MODULE)}</button>
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
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h3 class="modal-title">{\App\Language::translate('LBL_ADD_KEY', $QUALIFIED_MODULE)}</h3>
						</div>
						<div class="modal-body">
							<form class="form-horizontal">
								<div class="form-group">
									<label class="col-sm-3 control-label">{\App\Language::translate('LBL_SELECT_USER', $QUALIFIED_MODULE)}</label>
									<div class="col-sm-6 controls">
										<select class="select user form-control" name="user" data-validation-engine="validate[required]">
											{foreach from=$USERS item=item key=key}
												<option value="{$key}">{$item->getDisplayName()}</option>
											{/foreach}
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{\App\Language::translate('LBL_SELECT_TYPE', $QUALIFIED_MODULE)}</label>
									<div class="col-sm-6 controls">
										<select multiple="" class="select type form-control" name="type">
											{foreach from=$MODULE_MODEL->getTypes() item=item}
												<option selected="" value="{$item}">{$item}</option>
											{/foreach}
										</select>
									</div>
								</div>	
							</form>
						</div>
						{include file=\App\Layout::getTemplatePath('ModalFooter.tpl', $MODULE)}
					</div>
				</div>
			</div>
		</div>	
	</div>
{/strip}
