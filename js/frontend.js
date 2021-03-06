jQuery(document).ready(function() {

	/*---------------------------------------------------------*/
	/* Shadow Boxes                                            */
	/*---------------------------------------------------------*/

	jQuery('.mbt-shadowbox-inline').colorbox({inline:true, scrolling:false, transition:'none'});
	jQuery('.mbt-shadowbox-iframe').colorbox({iframe:true, scrolling:true, transition:'none', width:"80%", height:"80%"});

	/*---------------------------------------------------------*/
	/* Find Bookstore Form                                     */
	/*---------------------------------------------------------*/

	var geocoder = new google.maps.Geocoder();
	var formtimer = null;

	function mbt_update_bookstore_form(form) {
		form.find('[type="submit"]').prop('disabled', true);
		window.clearTimeout(formtimer);
		formtimer = setTimeout(function() {
			var city = form.find('.mbt-city').val();
			var zip = form.find('.mbt-zip').val();

			geocoder.geocode({ 'address': city + " " + zip }, function(results, status) {
				if(status == google.maps.GeocoderStatus.OK) {
					var lat = results[0].geometry.location.lat();
					var lng = results[0].geometry.location.lng();
					var url = "https://www.google.com/maps/search/bookstore/@"+lat+","+lng+",14z";
					form.attr('action', url);
					form.find('[type="submit"]').prop('disabled', false);
				}
			});
		}, 1000);
	}

	jQuery('form.mbt-find-bookstore-form').each(function(i, e) {
		var form = jQuery(e);

		updatefn = function() { mbt_update_bookstore_form(form); }
		form.find('.mbt-city').on('input', updatefn);
		form.find('.mbt-zip').on('input', updatefn);

		form.submit(function() {
			window.open(form.attr('action'), "", "");
			return false;
		});
	});

});
