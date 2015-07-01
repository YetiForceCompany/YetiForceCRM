/* http://keith-wood.name/datepick.html
   Faroese localisation for jQuery Datepicker.
   Written by Sverri Mohr Olsen, sverrimo@gmail.com */
(function($) {
	$.datepick.regional['fo'] = {
		monthNames: ['Januar','Februar','Mars','Apríl','Mei','Juni',
		'Juli','August','September','Oktober','November','Desember'],
		monthNamesShort: ['Jan','Feb','Mar','Apr','Mei','Jun',
		'Jul','Aug','Sep','Okt','Nov','Des'],
		dayNames: ['Sunnudagur','Mánadagur','Týsdagur','Mikudagur','Hósdagur','Fríggjadagur','Leyardagur'],
		dayNamesShort: ['Sun','Mán','Týs','Mik','Hós','Frí','Ley'],
		dayNamesMin: ['Su','Má','Tý','Mi','Hó','Fr','Le'],
		dateFormat: 'dd-mm-yyyy', firstDay: 0,
		renderer: $.datepick.defaultRenderer,
		prevText: '&#x3c;Sísta', prevStatus: 'Vís sísta mána?an',
		prevJumpText: '&#x3c;&#x3c;', prevJumpStatus: 'Vís sísta ári?',
		nextText: 'Nasta&#x3e;', nextStatus: 'Vís nasta mána?an',
		nextJumpText: '&#x3e;&#x3e;', nextJumpStatus: 'Vís nasta ári?',
		currentText: 'Í dag', currentStatus: 'Vís mána?an fyri í dag',
		todayText: 'Í dag', todayStatus: 'Vís mána?an fyri í dag',
		clearText: 'Strika', clearStatus: 'Strika allir mána?arnar',
		closeText: 'Goym', closeStatus: 'Goym hetta vindey?ga',
		yearStatus: 'Broyt ári?', monthStatus: 'Broyt mána?an',
		weekText: 'Vk', weekStatus: 'Vika av árinum',
		dayStatus: 'Vel DD, M d, yyyy', defaultStatus: 'Vel ein dato',
		isRTL: false
	};
	$.datepick.setDefaults($.datepick.regional['fo']);
})(jQuery);
