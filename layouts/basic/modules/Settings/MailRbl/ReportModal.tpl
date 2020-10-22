{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-MailRbl-ReportModal -->
<div class="modal-body">
	<div class="alert alert-warning alert-dismissible fade show" role="alert">
		<span class="fas fa-info-circle mr-2 u-fs-lg float-left"></span>
		{\App\Language::translate($MODAL_DESC, $QUALIFIED_MODULE)}
	</div>
	<form>
		<div class="form-group row">
			<label for="inputIp" class="col-sm-3 col-form-label text-right">{\App\Language::translate('LBL_REPORT_IP', $QUALIFIED_MODULE)}:</label>
			<div class="col-sm-9">
				<input type="text" name="ip" readonly class="form-control-plaintext" id="inputIp" value="{$SENDER['ip']}">
			</div>
		</div>
		<div class="form-group row">
			<label for="inputType" class="col-sm-3 col-form-label text-right">{\App\Language::translate('LBL_REPORT_TYPE', $QUALIFIED_MODULE)}:</label>
			<div class="col-sm-9">
				<input type="hidden" name="type" value="{$TYPE}">
				<input type="text" readonly class="form-control-plaintext" id="inputType" value="{\App\Language::translate($TYPE_NAME, $QUALIFIED_MODULE)}">
			</div>
		</div>
		<div class="form-group row">
			<label for="inputDesc" class="col-sm-3 col-form-label text-right">{\App\Language::translate('LBL_REPORT_DESC', $QUALIFIED_MODULE)}:</label>
			<div class="col-sm-9">
				<textarea name="desc" class="form-control" id="inputDesc" rows="3"></textarea>
			</div>
		</div>
		<div class="form-group row">
			<label for="inputCategory" class="col-sm-3 col-form-label text-right">{\App\Language::translate('LBL_REPORT_CATEGORY', $QUALIFIED_MODULE)}:</label>
			<div class="col-sm-9">
				<select name="category" id="inputCategory" class="form-control select2">
					{foreach from=$CATEGORIES key=KEY item=ITEM}
						<option value="{$KEY}">{\App\Language::translate($ITEM, $QUALIFIED_MODULE)}</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="form-group">
			<label for="inputEvidence" class="col-sm-3 col-form-label text-right">{\App\Language::translate('LBL_REPORT_EVIDENCE', $QUALIFIED_MODULE)}:</label>
			<div class="">
				<input type="hidden" name="headers" id="inputEvidence" value="{\App\Purifier::encodeHtml($HEADER)}">
				<pre>{\App\Purifier::encodeHtml($HEADER)}</pre>
			</div>
			<div class="">
				<input type="hidden" name="body" value="{\App\Purifier::encodeHtml($BODY)}">
				<iframe sandbox="allow-same-origin" class="w-100 js-iframe-full-height" frameborder="0" srcdoc="{\App\Purifier::encodeHtml($BODY)}"></iframe>
			</div>
		</div>
	</form>
</div>
<!-- /tpl-Settings-MailRbl-ReportModal -->
{/strip}
