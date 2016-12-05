DDWFrontEnd = function() {
    var self = this;
    self.loadedForBackButton = false; //flag to only run the related function once
    self.$calendar = $("#ddw_calendar");
    self.$timeslots = $("#ddw_timeslots");
    self.$input_ddw_order_date = $("input#ddw_order_date");
    self.$input_ddw_order_time = $("input#ddw_order_time");
    self.required = 0;
    self.required_error = translation_required_error;
    self.id_lang = id_lang;
    self.one_page_checkout = false;

    self.updateDDWCart = function() {
        $.ajax({
            type: 'POST',
            url: baseDir + 'modules/deliverydateswizard/ajax.php?action=update_ddw_cart&rand=' + new Date().getTime(),
            async: true,
            cache: false,
            data : {
                ddw_date : self.$input_ddw_order_date.val(),
                ddw_time : self.$input_ddw_order_time.val()
            },
            success: function(jsonData) {
                window.ddw_order_date_cache = $("input#ddw_order_date").val();
                window.ddw_order_time_cache = $("input#ddw_order_time").val();
            }
        });
    }

    self.refreshTimeSlots = function(timeslots_collection) {
        self.$timeslots.html('');
        $.each(timeslots_collection, function (i, timeslot) {
            if (typeof timeslot.time_slots[self.id_lang] !== 'undefined' && timeslot.time_slots[self.id_lang] != '')
                self.$timeslots.append('<div class=""><input type="radio" name="chk_timeslot" class="chk_timeslot" value="' + timeslot.time_slots[self.id_lang] + '"><label>' + timeslot.time_slots[self.id_lang] + '</label></div>')
        });
    }

    self.getBlockedDates = function(id_carrier) {
        var arrBlockedDates = Array() //of DDWCalendarBlockedDate ;
        $.ajax({
            type: 'POST',
            url: baseDir + 'modules/deliverydateswizard/ajax.php?action=get_blocked_dates&rand=' + new Date().getTime(),
            async: true,
            cache: false,
            data : {
                id_carrier : id_carrier
            },
            dataType : "json",
            complete: function(d) {
            },
            success: function(jsonData) {
                self.refreshTimeSlots(jsonData.timeslots);

                if (self.$calendar.hasClass("hasDatepicker"))
                    self.$calendar.datepicker("destroy");

                if (typeof jsonData.required !== "undefined")
                    self.required = jsonData.required;
                else
                    self.required = 0;

                if (typeof jsonData.enabled !== "undefined")
                    if (jsonData.enabled == false) {
                        $("input[name='ddw_order_date']").val('');
                        $("input[name='ddw_order_time']").val('');
                        $("#delivery-date-panel").fadeOut(100);
                        return false;
                    }
                    else {
                        $("#delivery-date-panel").fadeIn(100);
                    }

                $.each(jsonData.calendar_blocked_dates, function (i, blocked_date) {
                    arrBlockedDates.push(blocked_date.date);
                });

                self.loadForBackButton();

                self.$calendar.datepicker({
                    dateFormat: 'yy-mm-dd',
                    minDate: jsonData.min_date,
                    maxDate: jsonData.max_date,
                    defaultDate: jsonData.defaults.calendar_default_year + '-' + jsonData.defaults.calendar_default_month + '-01',
                    beforeShowDay: function(date) {
                        var dateString = jQuery.datepicker.formatDate('yy-mm-dd', date);
                        return [arrBlockedDates.indexOf(dateString) == -1];
                    },
                    onSelect: function(date) {
                        var dateFormatted = date.split("-").reverse().join("/");
                        $("#ddw_text").html(dateFormatted);
                        self.$input_ddw_order_date.val(date);
                        self.$input_ddw_order_date.trigger("change");
                        if (self.one_page_checkout) self.updateDDWCart();
                    }
                });
            }
        });

    }

    self.reloadCalender = function(id_carrier) {
        self.getBlockedDates(id_carrier);
    }

    self.reload_time_slots = function(id_carrier) {
        $("input[name='DDW_time_slot']").val("");
        $('input.ddw_time_slot').prop('checked', false);
        $("div.delivery-time-widget").hide();
        $("div.delivery-time-widget[data-shipping-method-code='"+id_carrier+"']").show();
    }

    self.reload_translations = function(id_carrier) {
        $(".ddw_texts").hide();
        $(".ddw_texts-"+id_carrier).show();
    }


    self.blockDates = function(jsonData) {
        for (var d = new Date(startDate); d <= new Date(endDate); d.setDate(d.getDate() + 1)) {
            dateRange.push($.datepicker.formatDate('yy-mm-dd', d));
        }
    }

    $("input[name='shipping_method']").change(function() {
        window.ddw_order_date = '';
        window.ddw_order_time = '';
        $("input[name='ddw_order_date']").val('');
        $("input[name='ddw_order_time']").val('');
        var id_carrier = $(this).attr("id").split(".")[0];
        self.reloadCalender(id_carrier);
        self.reload_time_slots(id_carrier);
        self.reload_translations(id_carrier);
    });

    self.$timeslots.on('click', '.chk_timeslot', function() {
        $("input[name='ddw_order_time']").val($(this).val());
        if (self.one_page_checkout) self.updateDDWCart();
    });

    self.checkNextStep = function() {
        var perror = false;

        if ($("input[name='chk_timeslot']").length > 0 &&  $("input[name='chk_timeslot']").is(":checked") == false) perror = true;

        if (self.$input_ddw_order_date.val() == '' || self.$input_ddw_order_date.val() == '0000-00-00 00:00:00') perror = true;
        if ($("#ddw_calendar").html() == "") perror = false; //no calendar displayed for selected carrier, proceed checkout
        if (self.required == 0) perror = false;
        return !perror;
    }

    self.showRequiredError = function() {
        if (!!$.prototype.fancybox)
            $.fancybox.open([
                {
                    type: 'inline',
                    autoScale: true,
                    minHeight: 30,
                    content: '<p class="fancybox-error">' + self.required_error + '</p>'
                }],
                {
                    padding: 0
                });
        else
            alert(self.required_error);
    }

    self.nextStep = function(e) {
        if (!self.checkNextStep()) {
            e.preventDefault();
            self.showRequiredError();
            return false;
        } else
            return true;
    }

    self.displayDate = function() {
        $("span#ddw_text").html(self.$input_ddw_order_date.val().replace(' 00:00:00', ''));
    }

    self.displayTime = function() {
        $("input[name='chk_timeslot'][value='"+self.$input_ddw_order_time.val()+"']").prop('checked', true);
    }

    // if the back button was pressed, reapply the date and time
    self.loadForBackButton = function() {
        if (self.loadedForBackButton) return false;

		$.ajax({
			type: 'POST',
			url: baseDir + 'modules/deliverydateswizard/ajax.php?action=get_last_ddw_cart&rand=' + new Date().getTime(),
			async: true,
			cache: false,
			dataType : "json",
			success: function(jsonData) {
				if (typeof jsonData.ddw_order_date !== 'undefined')
					self.$input_ddw_order_date.val(jsonData.ddw_order_date);

				if (typeof jsonData.ddw_order_time !== 'undefined')
					self.$input_ddw_order_time.val(jsonData.ddw_order_time);

				if (self.$input_ddw_order_date.val() != '')
					self.displayDate();

				if (self.$input_ddw_order_time.val() != '')
					self.displayTime();
				self.loadedForBackButton = true;
			}
		});
    }

    self.init = function() {
        if (controller_name == 'order-opc') self.one_page_checkout = true;

        id_carrier = $("input.delivery_option_radio:radio:checked").val();
        $('body').off('submit.ddwNextStep');
        $('body').on('submit.ddwNextStep', '#paypal_payment_form', self.nextStep);

        $('body').on('submit', '#paypal_payment_form', function(e) {
            if (!self.checkNextStep()) {
                e.preventDefault();
                self.showRequiredError();
                return false;
            } else
                return true;
        });
		
        /* all other payment methods on one page checkout */
        $('body').off('click', 'p.payment_module a');
        $('body').on('click', 'p.payment_module a', function (e) {
            if (!self.checkNextStep()) {
                e.preventDefault();
                self.showRequiredError();
                return false;
            } else {
                /* support for mollie payment methods module (remove inline onclick from template first */
                if (typeof(mollie_click) === "function") {
                    var id_method = $(this).attr("data-id");
                    mollie_click(id_method);
                }
                else {
                    return true;    
                }                
            }
        });
		

        // Need to unbind and rebind as the back button causes double triggering of the click event
        $('[name="processCarrier"]').unbind("click");
        $('[name="processCarrier"]').bind("click", {msg: name},(function (e) {
            if (!self.checkNextStep()) {
                e.preventDefault();
                self.showRequiredError();
                return false;
            } else
                return true;
        }));

        self.reloadCalender(id_carrier);
        self.reload_time_slots(id_carrier);

        if (typeof default_time != "undefined") {
            $("input[name='DDW_time_slot']").val(default_time);
            $('input.ddw_time_slot[value="'+default_time+'"]').prop('checked', true);
        }
    }
    self.init();
}