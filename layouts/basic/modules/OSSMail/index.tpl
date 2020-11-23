{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<script>
	let height = window.innerHeight;
	$(document).ready(function () {
		window.App.Components.Scrollbar.active = false;
		$('#roundcube_interface').css('height', height - $('.js-header').innerHeight() - $('.js-footer').innerHeight() -1 );
	});
</script>
<input type="hidden" value="" id="tempField" name="tempField" />
<iframe id="roundcube_interface" style="width: 100%; height: 590px;margin-bottom: -5px;" frameborder="0" src="{$URL}" frameborder="0"> </iframe>
