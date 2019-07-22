{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal-body">
		<form>
			<input type="hidden" name="module" value="YetiForce"/>
			<input type="hidden" name="parent" value="Settings"/>
			<input type="hidden" name="view" value="BuyModal"/>
			<input type="hidden" name="product" value="{$PRODUCT->getName()}"/>
			{assign var="QUALIFIED_MODULE" value="Settings::Companies"}
			<div class="alert alert-info" role="alert">
			sfwef {$PRODUCT->getName()}
			</div>
		</form>
	</div>
{/strip}
