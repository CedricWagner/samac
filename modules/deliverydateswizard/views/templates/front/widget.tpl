<div id="delivery-date-panel">
	<div class="ddw_text_checkout">
		<p><input type="radio" id="method-no-select" checked="checked" name="rb-shipping-method" class="rb-shipping-method" />{$text_no_date|escape:'quotes':'UTF-8'}</p><br />
		<p><input type="radio" id="method-select" name="rb-shipping-method" class="rb-shipping-method"  />{$text_checkout|escape:'quotes':'UTF-8'}</p>
	</div>
	<div class="calendar-wrapper">	
		<div id="ddw_calendar"></div>
		<span id="ddw_text"></span>
	</div>	
	<div id="ddw_timeslots"></div>
</div>
<input id="ddw_order_date" name="ddw_order_date" type="hidden" value="">
<input id="ddw_order_time" name="ddw_order_time" type="hidden" value="">
<script>
	$(document).ready(function() {
        id_lang = "{$cart->id_lang|escape:'html':'UTF-8'}";
		translation_required_error = "{$required_error|escape:'html':'UTF-8'}";
		controller_name = "{$controller_name|escape:'html':'UTF-8'}";
		ddw = new DDWFrontEnd();
        if (typeof (window.ddw_order_date_cache) !== 'undefined') {
            $("#ddw_order_date").val(window.ddw_order_date_cache);
            $("span#ddw_text").html(window.ddw_order_date_cache);
        }
        if (typeof (window.ddw_order_time_cache) !== 'undefined') $("#ddw_order_time").val(window.ddw_order_time_cache);

        /* if elements such as the gift option cause the widget to reload, use the window as caches for the date and time, and resubmit for saving to cart as it's being cleared each time the state gift option checkboxes changes */
        if (typeof (window.ddw_order_date_cache) !== 'undefined' || typeof (window.ddw_order_time_cache) !== 'undefined')
            ddw.updateDDWCart();

        toggleDatePicker();

	});

	$('.rb-shipping-method').change(function(){
		toggleDatePicker();
	});

	function toggleDatePicker(){
		if($('#method-no-select').is(':checked')){
			$('#ddw_order_date').val('');
			$('#ddw_order_time').val('');
			$('.calendar-wrapper').hide();
		}
		if($('#method-select').is(':checked')){
			$('.calendar-wrapper').show();
		}
	}
</script>

