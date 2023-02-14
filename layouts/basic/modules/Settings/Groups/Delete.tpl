{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Groups-Delete -->
	<div class="modal-body js-modal-body" data-js="container">
		<form class="form-horizontal" class="js-delete-transform">
			<input type="hidden" name="module" value="{$MODULE_NAME}" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="action" value="Delete" />
			<input type="hidden" name="record" id="record" value="{$RECORD_MODEL->getId()}" />
			<div class="form-group row align-items-center">
				<div class="col-md-4">
					<strong>
						{\App\Language::translate('LBL_TRANSFORM_OWNERSHIP', $QUALIFIED_MODULE)} {\App\Language::translate('LBL_TO', $QUALIFIED_MODULE)}<span class="redColor">*</span>
					</strong>
				</div>
				<div class="col-md-8">
					<select name="transfer_record" class="select2 form-control">
						<optgroup label="{\App\Language::translate('LBL_USERS', $QUALIFIED_MODULE)}">
							{foreach from=$ALL_USERS key=USER_ID item=USER_MODEL}
								<option value="{$USER_ID}">{$USER_MODEL->getName()}</option>
							{/foreach}
						</optgroup>
						<optgroup label="{\App\Language::translate('LBL_GROUPS', $QUALIFIED_MODULE)}">
							{foreach from=$ALL_GROUPS key=GROUP_ID item=GROUP_MODEL}
								{if $RECORD_MODEL->getId() != $GROUP_ID }
									<option value="{$GROUP_ID}">{$GROUP_MODEL->getName()}</option>
								{/if}
							{/foreach}
						</optgroup>
					</select>
				</div>
			</div>
		</form>
	</div>
	<!-- /tpl-Settings-Groups-Delete -->
{/strip}
