{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 class="modal-title">{\App\Language::translate('LBL_EDIT_FOLDER_ACCOUNT', $MODULE_NAME)} - {$ADDRESS_EMAIL}</h3>
	</div>
	<div class="modal-body col-md-12 tl-slide-content" data-user="{$RECORD}">
		{if count($MISSING_FOLDERS) > 0}
			<div class="alert alert-danger" role="alert">
				{\App\Language::translate('LBL_INFO_ABOUT_FOLDERS_TO_REMOVE', $QUALIFIED_MODULE)}
				<ul>
					{foreach from=$MISSING_FOLDERS item=$FOLDER_NAME}
						<li>{$FOLDER_NAME}</li>
						{/foreach}
				</ul>
			</div>
		{/if}
		{if $FOLDERS === false}
			<div class="alert alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				{\App\Language::translate('ERR_INCORRECT_ACCESS_DATA', $QUALIFIED_MODULE)}
			</div>
		{else}
			<div class="alert alert-warning" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				{\App\Language::translate('LBL_ALERT_EDIT_FOLDER', $MODULE_NAME)}
			</div>
			<div class="row marginBottom5">
				<label class="col-sm-3 col-form-label">{\App\Language::translate('Received', 'OSSMailScanner')}</label>
				<div class="col-sm-6 controls">
					<select multiple name="Received" class="select2 form-control">
						{foreach key=FOLDER item=NAME from=$FOLDERS}
							<option value="{$FOLDER}" {if $SELECTED['Received'] && in_array($FOLDER,$SELECTED['Received'])}selected="selected"{/if}>
								{$NAME}
							</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="row marginBottom5">
				<label class="col-sm-3 col-form-label">{\App\Language::translate('Sent', 'OSSMailScanner')}</label>
				<div class="controls col-sm-6">
					<select multiple name="Sent" class="select2 form-control">
						{foreach key=FOLDER item=NAME from=$FOLDERS}
							<option value="{$FOLDER}" {if $SELECTED['Sent'] && in_array($FOLDER,$SELECTED['Sent'])}selected="selected"{/if}>
								{$NAME}
							</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="row marginBottom5">
				<label class="col-sm-3 col-form-label" >{\App\Language::translate('Spam', 'OSSMailScanner')}</label>
				<div class="col-sm-6 controls">
					<select multiple name="Spam" class="select2 form-control">
						{foreach key=FOLDER item=NAME from=$FOLDERS}
							<option value="{$FOLDER}" {if $SELECTED['Spam'] && in_array($FOLDER,$SELECTED['Spam'])}selected="selected"{/if}>
								{$NAME}
							</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="row marginBottom5">
				<label class="col-sm-3 col-form-label" >{\App\Language::translate('Trash', 'OSSMailScanner')}</label>
				<div class="col-sm-6 controls">
					<select multiple name="Trash" class="select2 form-control">
						{foreach key=FOLDER item=NAME from=$FOLDERS}
							<option value="{$FOLDER}" {if $SELECTED['Trash'] && in_array($FOLDER,$SELECTED['Trash'])}selected="selected"{/if}>
								{$NAME}
							</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="row">
				<label class="col-sm-3 col-form-label" >{\App\Language::translate('All_folder', 'OSSMailScanner')}</label>
				<div class="col-sm-6 controls">
					<select multiple name="All" class="select2 form-control">
						{foreach key=FOLDER item=NAME from=$FOLDERS}
							<option value="{$FOLDER}" {if $SELECTED['All'] && in_array($FOLDER,$SELECTED['All'])}selected="selected"{/if}>
								{$NAME}
							</option>
						{/foreach}
					</select>
				</div>
			</div>
		{/if}
	</div>
	<div class="modal-footer">
		<div class="float-right">
			<button class="btn btn-success" type="submit" name="saveButton"><strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong></button>
			<button class="btn btn-warning" type="reset" data-dismiss="modal"><strong>{\App\Language::translate('LBL_CANCEL', $MODULE)}</strong></button>
		</div>
	</div>
{/strip}
