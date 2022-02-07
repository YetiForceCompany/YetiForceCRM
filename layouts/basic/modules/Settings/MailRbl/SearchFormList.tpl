{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-MailRbl-SearchFormList -->
	<form class="js-filter-form form-inline" data-js="container">
		<div class="input-group col-md-6">
			<div class="input-group-prepend">
				<span class="input-group-text" id="{$ID}Ip">
					<span class="fas fa-stream mr-2"></span>
					{\App\Language::translate('LBL_IP', $QUALIFIED_MODULE)}
				</span>
			</div>
			<input name="ip" type="text" value="{$IP}" class="form-control" aria-describedby="{$ID}Ip" />
		</div>
		<div class="input-group col-md-6">
			<div class="input-group-prepend">
				<span class="input-group-text" id="{$ID}Status">
					<span class="fas fa-stream mr-2"></span>
					{\App\Language::translate('Status', $QUALIFIED_MODULE)}
				</span>
			</div>
			<select id="{$ID}StatusPicklist" class="form-control select2" multiple="true" name="status[]" aria-describedby="{$ID}Status">
				{foreach from=\App\Mail\Rbl::LIST_STATUS key=KEY item=STATUS}
					<option value="{$KEY}">
						{\App\Language::translate($STATUS['label'], $QUALIFIED_MODULE)}
					</option>
				{/foreach}
			</select>
		</div>
	</form>
	<!-- /tpl-Settings-MailRbl-SearchFormList -->
{/strip}
