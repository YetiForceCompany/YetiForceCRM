{strip}{assign var=BG value=$SAVED_DATA['bg']}
{assign var=TEXT value=$SAVED_DATA['text']}
<div class="row">
	<div class="col-md-6">
		<h5 class="padding-bottom1per"><strong>{vtranslate('LBL_BACKGROUND_COLOR', 'DataAccess')}:</strong></h5>
		<p class="calendarColorPickerBG"></p>
		<input name="bg" type="hidden" id="calendarColorPickerBG" value="{$BG}">
	</div>
	<div class="col-md-6">
		<h5 class="padding-bottom1per"><strong>{vtranslate('LBL_TEXT_COLOR', 'DataAccess')}:</strong></h5>
		<p class="calendarColorPickerTX"></p>
		<input name="text" type="hidden" id="calendarColorPickerTX" value="{$TEXT}">
	</div>
</div>
<link rel="stylesheet" href="libraries/jquery/colorpicker/css/colorpicker.css" type="text/css" media="screen">
<script type="text/javascript" src="libraries/jquery/colorpicker/js/colorpicker.js"></script>
{/strip}
<script type="text/javascript">
(function($){
	$('.calendarColorPickerBG').ColorPicker({
		flat: true,
		color: '{$BG}',
		onChange : function(hsb, hex, rgb) {
			$('#calendarColorPickerBG').val('#'+hex);
		}
	});
	$('.calendarColorPickerTX').ColorPicker({
		flat: true,
		color: '{$TEXT}',
		onChange : function(hsb, hex, rgb) {
			$('#calendarColorPickerTX').val('#'+hex);
		}
	});
})(jQuery)
</script>
