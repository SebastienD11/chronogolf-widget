(function( $ ) {
    $(function() {
         
        // Add Color Picker to all inputs that have 'color-field' class
        $( '.chronogolf-color-picker' ).wpColorPicker();
        $('.datepicker-here').datepicker({
			    language: {
			       	days: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
				    daysShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
				    daysMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
				    months: ['January','February','March','April','May','June', 'July','August','September','October','November','December'],
				    monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
				    today: 'Today',
				    clear: 'Clear',
				    dateFormat: 'mm/dd/yyyy',
				    timeFormat: 'hh:ii aa',
				    firstDay: 0
			    }
			})

    });
})( jQuery );