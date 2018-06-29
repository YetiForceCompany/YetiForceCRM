{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<script>
	var height = window.innerHeight;
	$(document).ready(function () {
		var v = 83;
		if ($('.js-footer__user-info').length) {
			v = 100;
		}
		$('#roundcube_interface').css('height', height - v)
	});
</script>
<input type="hidden" value="" id="tempField" name="tempField" />
<iframe id="roundcube_interface" style="width: 100%; height: 590px;margin-bottom: -5px;" frameborder="0" src="{$URL}" frameborder="0"> </iframe>
