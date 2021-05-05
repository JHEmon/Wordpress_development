var style4Height    =   '';
jQuery(document).ready(function () {
    if(jQuery('.min-height-class').length) {
        style4Height    =   jQuery('.min-height-class').height();
    }

    if(jQuery('#datepicker-lang').length) {
        var datepicker_lang =   jQuery('#datepicker-lang').val();
        jQuery.datepicker.regional[datepicker_lang];
    }
});
jQuery(document).ready(function () {
    $bookingDataTarget     =    jQuery('.lp-booking-section-list .booking-slider-arrow-left').attr('data-target');
    $bookingDataBcd        =    jQuery('.lp-booking-section-list .lp-booking-section-month-date').attr('data-bcd');

    if($bookingDataBcd > $bookingDataTarget) {
        jQuery('.lp-booking-section-list .booking-slider-arrow-left').addClass('DisableArrow');
        if (jQuery('.lp-booking-section-list .booking-slider-arrow-left').hasClass("DisableArrow")) {
            return false;
        }
    }

});

jQuery(document).on('click', '.booking-slider-arrow-right, .booking-slider-arrow-left', function () {
    var $this = jQuery(this),
        currentDate         =   $this.closest('.pos-relative').find('.lp-booking-section-month-date').html(),
        currentDatestr      =   $this.closest('.pos-relative').find('.lp-booking-section-month-date').attr('data-bcd'),
        currentMonthYear    =   $this.closest('.lp-booking-section-list').find('.lp-booking-section-top-month').html(),
        splitMonthYear      =   currentMonthYear.split(','),
        currentFullDate     =   splitMonthYear[0] +' '+ currentDate +','+ splitMonthYear[1],
        dataTarget          =   $this.attr('data-target'),
        type = 'next',
        lid = $this.attr('data-lid');

    if(currentDatestr < dataTarget){
        jQuery('.lp-booking-section-list .booking-slider-arrow-left').removeClass('DisableArrow');
    }else if(currentDatestr > dataTarget) {
        jQuery('.lp-booking-section-list .booking-slider-arrow-left').addClass('DisableArrow');
        if (jQuery('.lp-booking-section-list .booking-slider-arrow-left').hasClass("DisableArrow")) {
            return false;
        }
    }
    jQuery('.booking-loader').fadeIn();
    if ($this.hasClass('booking-slider-arrow-left')) {
        type = 'prev';
    }

    jQuery.ajax({
        type: 'POST',
        dataType: 'json',
        url: ajax_search_term_object.ajaxurl,
        data: {
            'action': 'listingpro_booking_npd',
            'dataTarget': dataTarget,
            'type': type,
            'lid': lid
        },
        success: function (res) {
            console.log(res);
            jQuery('.lp-booking-section-month-date').text(res.newcur);
            jQuery('.lp-booking-section-top-month').text(res.newCurMonth+','+' '+res.newCurYear);
            jQuery('.lp-booking-section-top-day-name').text(res.newCurDayName);
            jQuery('.booking-slider-arrow-right').attr('data-target', res.newNext);
            jQuery('.booking-slider-arrow-left').attr('data-target', res.newPrev);
            jQuery('.lp-booking-slots-outer-wrap').html(res.timeslots);
            jQuery('.booking-loader').fadeOut();
        },
        error: function (err) {

        }
    });

});
jQuery(function () {

    jQuery(document).on('click', '#booking-calendar-select-date a.ui-state-default', function (e) {
        e.preventDefault();


        var $thisTD         =   jQuery(this),
            dataDate        =   $thisTD.text(),
            dataMonth       =   $thisTD.closest('tr').find('td').attr('data-month'),
            dataMonthInc         =   parseInt(dataMonth) + 1,
            dataYear        =   $thisTD.closest('tr').find('td').attr('data-year'),
            dataFullDate    =   dataYear +'-'+ dataMonthInc +'-'+ dataDate,
            lid             = jQuery('.booking-slider-arrow-right').attr('data-lid');


            jQuery.ajax({
                dataType: 'json',
                url: ajax_search_term_object.ajaxurl,
                data: {
                    'action': 'create_pills_from_calender',
                    'dataFullDate'  : dataFullDate,
                    'lid'   :   lid,
                },
                success: function (res) {
                    jQuery('.lp-booking-section-month-date').text(res.newcur);
                    jQuery('.lp-booking-section-top-month').text(res.newCurMonth+','+' '+res.newCurYear);
                    jQuery('.lp-booking-section-top-day-name').text(res.newCurDayName);
                    jQuery('.booking-slider-arrow-right').attr('data-target', res.newNext);
                    jQuery('.booking-slider-arrow-left').attr('data-target', res.newPrev);
                    jQuery('.lp-booking-slots-outer-wrap').html(res.timeslots);
                    jQuery('.booking-loader').fadeIn();
                    jQuery('#calendar').hide();
                    jQuery('.lp-booking-section-list').show();
                    jQuery('.booking-loader').fadeOut();

                    jQuery(".lp-booking-section-footer-view-switch").find("i").addClass('fa-calendar-o');
                    jQuery(".lp-booking-section-footer-view-switch").find("i").removeClass('fa-list');

                },
                error: function (err) {
                    console.log(err);
                }
            });
    });

    jQuery('#booking-calendar').datepicker({
        onSelect: function (dateText, inst) {
            var dateString = inst.currentYear + '-' + parseInt(inst.currentMonth + 1) + '-' + inst.currentDay;
            jQuery('.booking-loader').fadeIn();

            jQuery('#calendar').hide();
            jQuery('.lp-booking-section-list').show();

            jQuery(".lp-booking-section-footer-view-switch").find("i").addClass('fa-calendar-o');
            jQuery(".lp-booking-section-footer-view-switch").find("i").removeClass('fa-list');


            var type = 'calendar',
                dataTarget = dateString,
                lid = jQuery('#calendar').attr('data-lid');

            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: ajax_search_term_object.ajaxurl,
                data: {
                    'action': 'listingpro_booking_npd',
                    'dataTarget': dataTarget,
                    'type': type,
                    'lid': lid
                },
                success: function (res) {
                    jQuery('.lp-booking-section-month-date').text(res.newcur);
                    jQuery('.booking-slider-arrow-right').attr('data-target', res.newNext);
                    jQuery('.booking-slider-arrow-left').attr('data-target', res.newPrev);
                    jQuery('.available-booking-slots').html(res.timeslots);
                    jQuery('.booking-loader').fadeOut();

                    console.log(res);
                },
                error: function (err) {

                }
            });
        }

    });

});
jQuery(document).on('click', '.lp-booking-section-time-pill-hover', function () {
    jQuery('.lp-booking-form-wrapper').show();
    if (jQuery(this).closest('li').hasClass("lp-booking-disable")) {
        return false;
    } else {
        var $this = jQuery(this),
            timeslot        =   $this.closest('li').attr('data-booking-slot'),
            timeslotStr     =   $this.closest('li').attr('data-booking-timestr'),
            timeslotStrEnd     =   $this.closest('li').attr('data-booking-timestr-end'),
            timeslotStrDate     =   $this.closest('li').attr('data-booking-date'),
            selectedDate    =   jQuery('.lp-booking-section-month-date').html(),
            selectedMonth   =   jQuery('.lp-booking-section-top-month').html(),
            selectedDay     =   jQuery('.lp-booking-section-top-day-name').html();


        jQuery('.lp-booking-form-wrapper #lp-booking-slot-str').val(timeslotStr);
        jQuery('.lp-booking-form-wrapper #lp-booking-slot-str-end').val(timeslotStrEnd);
        jQuery('.lp-booking-form-wrapper #lp-booking-slot-date').val(timeslotStrDate);

        jQuery('.lp-booking-form-container').show();
        jQuery('.lp-booking-section').hide();

        jQuery('.lp-form-booking-detail').find('.lp-form-booking-detail-time').html(timeslot);
        jQuery('.lp-form-booking-date').html(selectedDate + ' ' + selectedMonth);
        jQuery('.lp-form-booking-detail-day').html(selectedDay);
    }
});

if (jQuery('.available-booking-slots li').hasClass("disable")) {
    jQuery(".disable.lp-booking-section-time-pill-hover").off("click");
}

jQuery(document).on('click', '.lp-booking-section-footer-view-switch', function () {

    var firstDay    =   jQuery('#start_of_weekk').val();

    var $this = jQuery(this);
    var $icon = $this.find("i");
    if ($icon.hasClass('fa-calendar-o')) {
        $icon.removeClass('fa-calendar-o');
        $icon.addClass('fa-list');
        jQuery('.lp-booking-section-list').hide();
        jQuery('#calendar').show();

        jQuery("#booking-calendar-select-date").datepicker(
            {
                firstDay: firstDay,
                minDate: new Date(),
            },
            jQuery.datepicker._selectDate   =   function (id, dateStr) {
                var onSelect,
                    target = jQuery(id),
                    inst = this._getInst(target[0]);

                var dateArr =   dateStr.split("/");

                var dataDate        =   dateArr[1],
                    dataMonthInc    =   dateArr[0],
                    dataYear        =   dateArr[2],
                    dataFullDate    =   dataYear +'-'+ dataMonthInc +'-'+ dataDate,
                    lid             = jQuery('.booking-slider-arrow-right').attr('data-lid');


                jQuery('.booking-loader').fadeIn();

                jQuery.ajax({
                    dataType: 'json',
                    url: ajax_search_term_object.ajaxurl,
                    data: {
                        'action': 'create_pills_from_calender',
                        'dataFullDate'  : dataFullDate,
                        'lid'   :   lid,
                    },
                    success: function (res) {
                        jQuery('.lp-booking-section-month-date').text(res.newcur);
                        jQuery('.lp-booking-section-top-month').text(res.newCurMonth+','+' '+res.newCurYear);
                        jQuery('.lp-booking-section-top-day-name').text(res.newCurDayName);
                        jQuery('.booking-slider-arrow-right').attr('data-target', res.newNext);
                        jQuery('.booking-slider-arrow-left').attr('data-target', res.newPrev);
                        
                        jQuery('.lp-booking-section-list .lp-booking-section-month-date').text(dataDate);

                        jQuery('.lp-booking-slots-outer-wrap').html(res.timeslots);

                        jQuery('#calendar').hide();
                        jQuery('.lp-booking-section-list').show();
                        jQuery('.booking-loader').fadeOut();

                        jQuery(".lp-booking-section-footer-view-switch").find("i").addClass('fa-calendar-o');
                        jQuery(".lp-booking-section-footer-view-switch").find("i").removeClass('fa-list');

                    },
                    error: function (err) {
                        console.log(err);
                    }
                });
            }
        );
    } else if ($icon.hasClass('fa-list')) {
        $icon.removeClass('fa-list');
        $icon.addClass('fa-calendar-o');
        jQuery('#calendar').hide();
        jQuery('.lp-booking-section-list').show();
        jQuery('.booking-date-slider').find('.booking-slider-arrow-left').show();
        jQuery('.booking-date-slider').find('.booking-slider-arrow-right').show();
    }
});


jQuery(document).on('click', '.lp-booking-bar', function () {

    jQuery('.booking-loader').fadeIn();
    jQuery('.lp-booking-bar').hide();
    jQuery('.lp-booking-section').show();
    jQuery('.booking-loader').fadeOut();

    style4_adjust_height();

});

jQuery(document).on('click', '.lp-booking-form-input-confirm', function () {
    var frmemail        = jQuery('.lp-booking-client-email[name="email"]').val(),
        emailReg     = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/,
        phoneNum     = jQuery('.lp-booking-client-phone[name="phone"]').val(),
        phoneRegex   = /[0-9 -()+]+$/;
    if(!emailReg.test(frmemail) || frmemail == '')
    {
        alert('Please enter a valid email address.');
    }else if(!phoneRegex.test(phoneNum))
    {
        alert('Please enter a valid phone number.');
    } else{
        jQuery('.booking-loader').fadeIn();
        //jQuery('.lp-booking-form-container').hide();

        var name = jQuery('.lp-booking-client-first-name').val(),
            lName = jQuery('.lp-booking-client-last-name').val(),
            phone = jQuery('.lp-booking-client-phone').val(),
            email = jQuery('.lp-booking-client-email').val(),
            comment = jQuery('.lp-booking-client-comment').val(),
            lid = jQuery('.lp-booking-form-container').attr('data-lid'),
            timee = jQuery('.lp-form-booking-detail-time').html(),
            datee       = jQuery('.lp-form-booking-date').html(),
            timeSlotStr =   jQuery('.lp-booking-form-wrapper #lp-booking-slot-str').val(),
            timeSlotStrDate =   jQuery('.lp-booking-form-wrapper #lp-booking-slot-date').val(),
            timeSlotStrEnd =   jQuery('.lp-booking-form-wrapper #lp-booking-slot-str-end').val();


        jQuery.ajax({
        url: ajax_search_term_object.ajaxurl,
        data: {
            'action': 'create_lp_bookings',
            'name': name,
            'lName': lName,
            'phone': phone,
            'email': email,
            'comment': comment,
            'lid': lid,
            'timee': timee,
            'datee': datee,
            'timeSlotStr': timeSlotStr,
            'timeSlotStrDate': timeSlotStrDate,
            'timeSlotStrEnd': timeSlotStrEnd,

        },
        success: function (data) {
            jQuery('.lp-booking-form').find('input:text').val('');
            jQuery('.lp-booking-form').find('textarea').val('');
            jQuery('.booking-loader').fadeOut();
            jQuery('.lp-booking-form-container').show();
            jQuery('.lp-booking-form-wrapper').hide();
            jQuery('.lp-booking-send-request').show();
            jQuery('.lp-booking-form').find('input:text').val('');
            jQuery('.lp-booking-form').find('textarea').val('');
        },
        error: function (error) {
            console.log(error);
        }

    });
    }

});
jQuery(document).on('click', '.booking-form-close', function () {
    jQuery('.booking-loader').fadeIn();
    jQuery('.lp-booking-form-container').hide();
    jQuery('.lp-booking-send-request').hide();
    jQuery('.lp-booking-section').show();
    jQuery('.booking-loader').fadeOut();
});

/* ****************  GET BOOKING DETAILS  ****************** */

jQuery(document).on('click', '.booking-content ul.booking-action-content i.noticefi_er', function (e) {
    e.preventDefault();
    var $this = jQuery(this).closest('ul'),
        cbid = $this.find('.radio-container-box').attr('id');
        jQuery('#booking-details-sidebar').append('<div id="booking-details-sidebar-preloader"><i class="fa fa-spin fa-spinner"></i></div>');
    jQuery.ajax({
        dataType: 'html',
        url: ajax_search_term_object.ajaxurl,
        data: {
            'action': 'get_booking_details',
            'cbid': cbid
        },
        success: function (res) {
            console.log(res);
            jQuery('#booking-details-sidebar').html(res);
        },
        error: function (err) {
            console.log(err);
        }
    });

    return false;
});
jQuery(document).on('click', '.booking-action-content .dropdown-menu a', function (e) {

    e.stopPropagation();
    var $this = jQuery(this);
    cbid = $this.attr('data-id');
    cBstatus = $this.attr('data-status');


    e.preventDefault();

    if($this.hasClass('active-ajax')) {

    } else {
        $this.closest('div.dropdown').find('.dropdown-toggle').append('<span class="booking-action-spinner"><i class="fa fa-spinner fa-spin"></i></span>');
        $this.closest('.dropdown-menu').hide();
        $this.addClass('active-ajax');

        jQuery.ajax({
            dataType: 'html',
            url: ajax_search_term_object.ajaxurl,
            data: {
                'action': 'Booking_status',
                'cbid': cbid,
                'cBstatus': cBstatus,
            },
            success: function (res) {
                // console.log(res);
                $this.find('.booking-action-spinner').remove();
                if (cBstatus == "APPROVED") {
                    $status_color = "approved";
                } else if (cBstatus == "CANCELED") {
                    $status_color = "canceled";
                } else {
                    $status_color = "pending";
                }
                $this.closest('.booking-action-content').find('.booking-status').removeClass("pending canceled approved");
                $this.closest('.booking-action-content').find('.booking-status').addClass($status_color);
                $this.closest('div.dropdown').find('button.dropdown-toggle').html(res);
                location.reload();

            },
            error: function (err) {
                console.log(err);
            }
        });
    }


});


/* ************************* CALANDER JS *************************** */

jQuery(document).ready(function () {
    var firstDay    =   jQuery('#start_of_weekk').val();
    jQuery("#lp-dashboard-booking-calander").datepicker(
        {
            firstDay: firstDay,
            minDate: new Date(),
        },
        jQuery.datepicker._selectDate   =   function (id, dateStr) {
            var onSelect,
                target = jQuery(id),
                inst = this._getInst(target[0]);
            if(id == '#lp-booking-calander') {
                return false;
            }
        }
    );
    jQuery('#lp-dashboard-booking-calander .ui-datepicker-next').addClass("dashboard-calander-next");
    jQuery('#lp-dashboard-booking-calander .ui-datepicker-prev').addClass("dashboard-calander-prev");

    var kldsfm =
        '<div class='+'droped-content'+'>'
        +
        '<h3 class='+'detail-booker-name'+'>John Doe</h3>'
        +
        '<p class='+'detail-booker-info'+'>Tuesday, August 2nd</p>'
        +
        '<p class='+'detail-booker-info'+'>9am - 10am<i class='+'tag-time-zone'+'>PST</i></p>'
        +
        '<p class='+'detail-booker-info'+'>212-222-3344</p>'
        +
        '<p class='+'detail-booker-info'+'>1001 New York, NY, 10001, USA</p>'
        +
        '<p class='+'detail-booker-info'+'>Maecenas iaculis placerat nisi eget faucibus. Duis ultricies turpis ut diam maximus placerata <i class='+'tag-reply'+'>Reply</i></p>'
        +
        '<p class='+'booking-listing-info'+'>Associated Listing:</p>'
        +
        '<h4 class='+'booking-listing-info'+'>Dr. Fay Chan</h4>'
        +
        '</div>';


    var bookingHtmlString   =

        '<div class="lp-dashboard-booking-calander-cell">'
        +
        '<div class="cell-pill-container" data-toggle="cus-popover" data-content="'+kldsfm+'"><label class="cell-pill">10:00am</label><label>Mary Jane</label></div>'
        +
        '<div class="cell-pill-container" data-toggle="cus-popover" data-content="'+kldsfm+'"><label class="cell-pill">11:00am</label><label>Mary Jane</label></div>'
        +
        '<div class="cell-pill-container" data-toggle="cus-popover" data-content="'+kldsfm+'"><label class="cell-pill">12:00pm</label><label>Mary Jane</label></div>'
        +
        '<a href="" class="pull-right cell-bookings-row-expanded">2 More</a>'
        +
        '</div>';


    //jQuery('#lp-dashboard-booking-calander .ui-datepicker-today').append(bookingHtmlString);

    var allbookingHtmlString   =
        '<div class="lp-dashboard-booking-calander-section lp-dashboard-booking-calander-more-popup">'
        +
        '<i class="fa fa-close close-lp-dashboard-booking-calander-section"></i>'
        +
        '<h3 class="lp-dashboard-booking-calander-section-head-day">Tuesday</h3>'
        +
        '<h2 class="lp-dashboard-booking-calander-section-head-date">2</h2>'
        +
        '<hr>'
        +
        '<div class="cell-pill-container" data-toggle="cus-popover" data-content="'+kldsfm+'"><label class="cell-pill">10:00am</label><label>Mary Jane</label></div>'
        +
        '<div class="cell-pill-container" data-toggle="cus-popover" data-content="'+kldsfm+'"><label class="cell-pill">11:00am</label><label>Mary Jane</label></div>'
        +
        '<div class="cell-pill-container" data-toggle="cus-popover" data-content="'+kldsfm+'"><label class="cell-pill">12:00am</label><label>Mary Jane</label></div>'
        +
        '<div class="cell-pill-container" data-toggle="cus-popover" data-content="'+kldsfm+'"><label class="cell-pill">01:00am</label><label>Mary Jane</label></div>'
        +
        '<div class="cell-pill-container" data-toggle="cus-popover" data-content="'+kldsfm+'"><label class="cell-pill">02:00am</label><label>Mary Jane</label></div>'
        +
        '<div class="cell-pill-container" data-toggle="cus-popover" data-content="'+kldsfm+'"><label class="cell-pill">03:00am</label><label>Mary Jane</label></div>'
        +
        '<div class="cell-pill-container" data-toggle="cus-popover" data-content="'+kldsfm+'"><label class="cell-pill">04:00am</label><label>Mary Jane</label></div>'
        +
        '<div class="cell-pill-container" data-toggle="cus-popover" data-content="'+kldsfm+'"><label class="cell-pill">05:00pm</label><label>Mary Jane</label></div>'
        +
        '<div class="cell-pill-container" data-toggle="cus-popover" data-content="'+kldsfm+'"><label class="cell-pill">06:00pm</label><label>Jane Mary</label></div>'
        +
        '</div>';

    //jQuery('#lp-dashboard-booking-calander  .ui-datepicker-today').append(allbookingHtmlString);

    jQuery('.cell-bookings-row-expanded').click(function () {
        var dropbooking = document.querySelector('.lp-dashboard-booking-calander-section');
        var domRect = dropbooking.getBoundingClientRect();
        var spaceBelow = window.innerHeight - domRect.bottom;
        dropbooking.style.top = (spaceBelow < 25 ? 'unset' : '0');
        dropbooking.style.bottom = (spaceBelow < 25 ? '0' : 'unset');
    })
    jQuery('.cell-pill-container').click(function () {
        if (jQuery(this).hasClass('active')) {
            jQuery('.cell-pill-container').removeClass('active');
            jQuery('.cell-pill-container').popover('hide');
        }else{
            jQuery('.cell-pill-container').removeClass('active');
            jQuery(this).addClass('active');
            jQuery('.cell-pill-container').popover('hide');
        }
    });

    // var disable_bookings = '<div class="disable-booking"><i class="fa fa-close"></i><h5>Disable Bookings</h5></div>';
    // jQuery('#lp-dashboard-booking-calander td').append(disable_bookings);

    var calender_spinner =   '<div class="booking-loader"><i class="fa fa-spinner fa-spin lp-booking-calender-spinner"></i></div>'
    jQuery('#lp-dashboard-booking-calander td').append(calender_spinner);

    // var allow_bookings = '<div class="enable-booking"><i class="fa fa-close"></i><h5>Enable Bookings</h5></div>';
    // jQuery('#lp-dashboard-booking-calander td').append(allow_bookings);


    // jQuery('#lp-dashboard-booking-calander table.ui-datepicker-calendar tbody tr td').click(function(){
    //     var $Ctd       =   jQuery(this);
    //     $Ctd.find('.booking-loader').fadeIn();
    //     cmonth         =   $Ctd.data('month');
    //     cyear          =   $Ctd.data('year');
    //     cdate          =   $Ctd.find('a.ui-state-default').html();
    //     cMonthName     =   ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    //     cFullDate      =   cMonthName[cmonth]+' '+cdate+','+' '+cyear;
    //     bookingAction  =   '';
    //
    //     if (jQuery(this).hasClass('ui-datepicker-today')) {}else{
    //         if (jQuery(this).hasClass('allow_bookings')) {
    //             bookingAction  =   'Disabled';
    //             jQuery('#lp-dashboard-booking-calander td.ui-datepicker-other-month.ui-datepicker-unselectable.ui-state-disabled').removeClass('allow_bookings');
    //             jQuery('#lp-dashboard-booking-calander td.ui-datepicker-other-month.ui-datepicker-unselectable.ui-state-disabled').removeClass('disable_bookings');
    //         }else{
    //             bookingAction  =   'Enabled';
    //             jQuery('#lp-dashboard-booking-calander td.ui-datepicker-other-month.ui-datepicker-unselectable.ui-state-disabled').removeClass('allow_bookings');
    //             jQuery('#lp-dashboard-booking-calander td.ui-datepicker-other-month.ui-datepicker-unselectable.ui-state-disabled').removeClass('disable_bookings');
    //         }
    //     }
    //
    //     jQuery.ajax({
    //         dataType: 'json',
    //         url: ajax_search_term_object.ajaxurl,
    //         data: {
    //             'action': 'Enable_Disable_Bookings',
    //             'cFullDate' : cFullDate,
    //             'bookingAction' :   bookingAction,
    //         },
    //         success: function (res) {
    //             if(res  ==  'Disabled' && $Ctd.hasClass('allow_bookings')){
    //                 //alert("disabled");
    //                 var disabled_bookings = '<div class="disabled-booking"><i class="fa fa-close"></i><h5>Bookings Disabled</h5></div>';
    //                 $Ctd.append(disabled_bookings);
    //                 $Ctd.removeClass('allow_bookings');
    //                 $Ctd.addClass('disable_bookings');
    //             }else{
    //                 //alert("Enabled");
    //                 $Ctd.find('.disabled-booking').css('visibility','hidden');
    //                 $Ctd.addClass('allow_bookings');
    //                 $Ctd.removeClass('disable_bookings');
    //             }
    //             jQuery('.booking-loader').fadeOut();
    //         },
    //         error: function (err) {
    //             console.log(err);
    //         }
    //     });
    // });


    // jQuery('#lp-dashboard-booking-calander table.ui-datepicker-calendar tbody tr td').addClass('allow_bookings');
    // jQuery('#lp-dashboard-booking-calander table.ui-datepicker-calendar tbody tr td.ui-datepicker-today').removeClass('allow_bookings');
    // jQuery('#lp-dashboard-booking-calander td.ui-datepicker-other-month.ui-datepicker-unselectable.ui-state-disabled').removeClass('allow_bookings');
    // jQuery('#lp-dashboard-booking-calander td.ui-datepicker-other-month.ui-datepicker-unselectable.ui-state-disabled').removeClass('disable_bookings');



});

jQuery(document).on('click', '.calendar-btn,.ui-datepicker-next,.ui-datepicker-prev', function(){
    var $CananderActionBtn  =   jQuery(this);
    if(!$CananderActionBtn.hasClass('ui-state-disabled')){
        jQuery('#lp-dashboard-booking-calander').append('<div class="lp-dashboard-booking-calander-loader"><i class="fa fa-spinner fa-spin"></i> </div>');
    }
    if($CananderActionBtn.hasClass('ui-datepicker-next')) {

    } else if($CananderActionBtn.hasClass('ui-datepicker-prev')) {

    }else{
        jQuery('.back-to-bookings').show();
        jQuery('#lp-dashboard-booking-calander').toggle();
        jQuery('.lp-dashboard-booking-calander-header').toggle();
        jQuery('.booking-grid-wrapper').hide();
    }

    firstDay    =   $CananderActionBtn.attr('data-first-day'),
        lastDay     =   $CananderActionBtn.attr('data-last-day');

    //console.log(firstDay+'---'+lastDay);

    jQuery('#lp-dashboard-booking-calander table.ui-datepicker-calendar td').find('.lp-dashboard-booking-calander-cell').remove();

    jQuery.ajax({
        dataType: 'json',
        url: ajax_search_term_object.ajaxurl,
        data: {
            'action': 'calendar_bookings_listing',
            'firstDay' : firstDay,
            'lastDay' : lastDay
        },
        success: function (res) {
            console.log(res);


            jQuery('#lp-dashboard-booking-calander').find('.lp-dashboard-booking-calander-loader').remove();
            jQuery('.ui-datepicker-next').attr({
                'data-first-day': res.next_first,
                'data-last-day': res.next_last
            });

            jQuery('.ui-datepicker-prev').attr({
                'data-first-day': res.prev_first,
                'data-last-day': res.prev_last
            });

            //console.log(res.lp_booking_settings);

            jQuery('#lp-dashboard-booking-calander table.ui-datepicker-calendar td').each(function (thisTD) {
                var loopTD = jQuery(this),
                    dataMonth = loopTD.data('month'),
                    dataMonthName = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
                    dataweekday = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
                    dataYear = loopTD.data('year'),
                    tdDay = loopTD.find('a.ui-state-default').text(),
                    dataFullDate = dataMonthName[dataMonth] + ' ' + tdDay + ',' + ' ' + dataYear,
                    dataDayName = new Date(dataFullDate),
                    allbookingspopup = '';

                if(res.lp_booking_settings[dataFullDate]){
                    var disabled_bookings = '<div class="disabled-booking"><i class="fa fa-close"></i><h5>Appointments Disabled</h5></div>';
                    loopTD.append(disabled_bookings);
                    // loopTD.removeClass('allow_bookings');
                    // loopTD.addClass('disable_bookings');
                }else{
                    loopTD.find('.disabled-booking').css('visibility','hidden');
                    // loopTD.addClass('allow_bookings');
                    // loopTD.removeClass('disable_bookings');
                }
                calenderPill = '<div class="lp-dashboard-booking-calander-cell">';
                allbookingspopup = '<div class="lp-dashboard-booking-calander-section lp-dashboard-booking-calander-more-popup">'+'<i class="fa fa-close close-lp-dashboard-booking-calander-section"></i>'
                    + '<h3 class="lp-dashboard-booking-calander-section-head-day">' + dataweekday[dataDayName.getDay()] + '</h3>'
                    + '<h2 class="lp-dashboard-booking-calander-section-head-date">' + tdDay + '</h2>'
                    + '<hr>';


                jQuery.each(res[dataFullDate], function (i, item) {
                    var listingTitle = item['Listing Title'],
                        Listing_author = item['Booker Name'],
                        bookingDate = item['Booking Date'],
                        bookingEndTime = item['End Time'],
                        bookingStartTime = item['Start Time'],
                        bookingPhone = item['Booking Phone'],
                        bookingMsg = item['Booking Message'],
                        PrevMonthStart  =   item['Prev Start'],
                        PrevMonthEnd  =   item['Prev Last'],
                        addr  =   item['addr'],
                        NextMonthStart  =   item['Next Start'],
                        NextMonthLast  =   item['Next Last'],
                        lp_booking_date = item['lp_booking_date'],
                        time_zone = jQuery('#lp_booking_get_time_zone_val').val();


                    var calenderPillData = '<div class=' + 'droped-content' + '>'
                        + '<h3 class=' + 'detail-booker-name' + '><i class= ' + ' fa' + '>&#xf007;</i>' + Listing_author + '</h3>'
                        + '<p class=' + 'detail-booker-info' + '><i class= ' + ' fa' + '>&#xf133;</i>' + lp_booking_date + '</p>'
                        + '<p class=' + 'detail-booker-info' + '><i class= ' + ' fa ' + '>&#xf017;</i>' + bookingStartTime + ' - ' + bookingEndTime + '<i class=' + 'tag-time-zone' + '>' + time_zone + '</i></p>'
                        + '<p class=' + 'detail-booker-info' + '><i class= ' + ' fa ' + '>&#xf095;</i>' + bookingPhone + '</p>';

                    if(addr != '') {
                        calenderPillData    +=  '<p class=' + 'detail-booker-info' + '><i class= ' + ' fa ' + '>&#xf124;</i>' + addr + '</p>';
                    }
                    calenderPillData    +=  '<p class=' + 'detail-booker-info' + '><i class= ' + ' fa ' + '>&#xf249;</i>' + bookingMsg + '<i class=' + 'tag-reply' + '>Reply</i></p>'
                        + '<p class=' + 'booking-listing-info' + '>'+ lp_booking_vars.associated_listing +'</p>'
                        + '<h4 class=' + 'booking-listing-info' + '>' + listingTitle + '</h4>'
                        + '</div>',


                    calenderPill += '<a href="#" class="cell-pill-container cell-pill-container-list" data-trigger="focus" data-toggle="cus-popover" data-content="' + calenderPillData + '"><label class="cell-pill">'+ bookingStartTime +' <span class="cal-bookingEndTime">'+ ' &nbsp; - &nbsp;&nbsp; ' + bookingEndTime + '</span></label><label class="cal-pill-listing_title">' + listingTitle + '</label> </a>';
                    allbookingspopup += '<a href="#" class="cell-pill-container" data-trigger="focus" data-toggle="cus-popover" data-content="' + calenderPillData + '"><label class="cell-pill">'+ bookingStartTime +' <span class="cal-bookingEndTime">'+ ' &nbsp; - &nbsp;&nbsp; ' + bookingEndTime + '</span></label><label class="cal-pill-listing_title">' + listingTitle + '</label> </a>';
                    console.log(calenderPillData);
                });
                calenderPill += '</div>';
                allbookingspopup += '</div>';

                loopTD.append(calenderPill);
                loopTD.append(allbookingspopup);

                pillsLength = loopTD.find('.cell-pill-container-list').length;
                if (pillsLength > '3') {
                    pillsLength = loopTD.find('.cell-pill-container-list').length - 3;
                    loadmorebtn = '<a href="" class="pull-right cell-bookings-row-expanded">' + pillsLength + ' More</a>';
                    loopTD.find('.lp-dashboard-booking-calander-cell').append(loadmorebtn);
                }
                jQuery('[data-toggle="cus-popover"]').popover({
                    placement: "left",
                    container: '#lp-dashboard-booking-calander',
                    html: true,
                });
                jQuery('.cell-bookings-row-expanded').click(function () {
                    jQuery('.lp-dashboard-booking-calander-section').hide();
                    jQuery(this).closest('td').find('.lp-dashboard-booking-calander-section').show();

                });
                jQuery('.close-lp-dashboard-booking-calander-section').click(function () {
                    jQuery('.lp-dashboard-booking-calander-section').hide();
                });
            });
            // jQuery('.droped-content i').addClass('fa');
        },
        error: function (err) {
        }
    });
});


jQuery(document).on('click', '.booking-setting-icon-delete', function(){
    var $delSetting =   jQuery(this),
        currentSLot =   $delSetting.closest('li').find('span').html();

        if($delSetting.hasClass('active-ajax')) {

        } else {
            $delSetting.find('i').removeClass('fa-trash').addClass('fa-spinner fa-spin');
            $delSetting.addClass('active-ajax');
            jQuery.ajax({
                dataType: 'json',
                url: ajax_search_term_object.ajaxurl,
                data:{
                    'action': 'dashboard_booking_settings_delete_slot',
                    'currentSLot' : currentSLot,
                },
                success:function (res) {
                    if(!res.del_booking_slot[currentSLot]){
                        $delSetting.closest('li').remove();
                    }
                },
                error:function (err) {
                    console.log(err);
                },
            });
        }


});

jQuery(document).on('change','#dashboard-timeslot', function() {
    var selectedSlot    =   jQuery(this).find(":selected").val();
    jQuery('.timeslot-spinner').css('display','block');
    jQuery.ajax({
        dataType:'json',
        url: ajax_search_term_object.ajaxurl,
        data:{
            'action':'lp_booking_timeSlot_duration',
            'selectedSlot' : selectedSlot,
        },
        success:function (res) {
            console.log(res);
            jQuery('.timeslot-spinner').css('display','none');
        },
        error:function (err) {
            console.log(err);
        },
    });
});

jQuery(document).ready(function(){
    var firstDay    =   jQuery('#start_of_weekk').val();
    jQuery( "#booking-settings-datepicker" ).datepicker(
        {
            firstDay: firstDay
        },
        jQuery.datepicker._selectDate   =   function (id, dateStr) {
            var selectedDate        =   dateStr;
                splitDate           =   selectedDate.split('/');
                currentMonth        =   splitDate[0]-1;
                dataMonthName       = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                currentDate         =   splitDate[1];
                currentDateTrim     = currentDate.replace(/^0+/, '');
                currentYear         =   splitDate[2];
                get_booking_setting_td_date    =   dataMonthName[currentMonth]+' '+currentDateTrim+','+' '+ currentYear;
            jQuery('.data-picker-in-out').attr("value" , get_booking_setting_td_date);
            jQuery('#booking-settings-datepicker').animate({height: '0px'});
        }
    );
    jQuery(document).on('click','.add-booking-date', function() {
        var $this   =   jQuery(this),
            booking_setting_td_date =   jQuery('.data-picker-in-out').attr("value"),
            recurring_settings_date =   'no';
        if(jQuery('#repeat-switch').is(':checked')) {
            recurring_settings_date =   'yes';
        }
        if($this.hasClass('active-ajax')){

        } else {
            $this.find('i').removeClass('fa-plus').addClass('fa-spinner fa-spin');
            $this.addClass('active-ajax');
            jQuery.ajax({
                dataType: 'json',
                url: ajax_search_term_object.ajaxurl,
                data:{
                    'action': 'dashboard_booking_settings_add_slot',
                    'booking_setting_td_date' : booking_setting_td_date,
                    'recurring_settings_date' : recurring_settings_date,
                },
                success:function (res) {
                    //console.log(res);

                    $this.removeClass('active-ajax');
                    $this.find('.fa-spinner').removeClass('fa-spinner fa-spin').addClass('fa-plus');

                    jQuery('#booking-settings-sidebar .user-booking-settings-time-slot').append(res);
                    jQuery('.data-picker-in-out').val('');
                },
                error:function (err) {
                    console.log(err);
                },
            });
        }

    });
    jQuery(".grid-btn, .bookings-back-btn").click(function(){
        jQuery('.back-to-bookings').hide();
        jQuery('#lp-dashboard-booking-calander').hide();
        jQuery('.booking-grid-wrapper').toggle();
        jQuery('.lp-dashboard-booking-calander-header').hide();
    });
    var $pending_bookings = jQuery('.pending_listing_Arr').attr("value");
    jQuery('.notice-text h2 span').html($pending_bookings);
});


jQuery(document).ready(function () {
        var $pending_bookings = jQuery('.pending_listing_Arr').attr("value");
    if ($pending_bookings == '0 Appointments are pending approval. Pending appointments will only show in calendar upon approval.') {
        jQuery('.lp-dashboard-panel-outer.lp-new-dashboard-panel-outer').fadeOut();
    }else {
        jQuery('.lp-dashboard-panel-outer.lp-new-dashboard-panel-outer').fadeIn();
        jQuery('.notice-text h2 span').html($pending_bookings);
    }

    jQuery(document).on('click', '.booking-content ul.booking-action-content i.noticefi_er', function () {
        var $thisUL  =   jQuery(this).closest('ul');
        jQuery('.booking-content ul.booking-action-content').removeClass('active');
        $thisUL.addClass('active');
    });


    jQuery('.form-control.pull-left.hasDatepicker').click(function () {
        var $outerwidth = jQuery(".col-md-3.tab-content.lp-tab-content-outer").width();
        var $total = $outerwidth + "px !important";
        jQuery('.ui-datepicker.ui-widget.ui-widget-content.ui-helper-clearfix.ui-corner-all').width($total);
    })
    
    jQuery('.data-picker-in-out').click(function () {
        jQuery('#booking-settings-datepicker').animate({height: '225px'});
    });

    var $docheight = jQuery(document).height();
    var $combine = $docheight+"px !important";
    jQuery('.col-md-3.tab-content.lp-tab-content-outer').css('min-height' ,$combine );


    jQuery(".lp-booking-client-first-name, .lp-booking-client-last-name, .lp-booking-client-phone, .lp-booking-client-email, .lp-booking-client-comment").keyup(function(){
        var name = jQuery('.lp-booking-client-first-name').val(),
            lname = jQuery('.lp-booking-client-last-name').val(),
            phone = jQuery('.lp-booking-client-phone').val(),
            email = jQuery('.lp-booking-client-email').val();
        comment = jQuery('.lp-booking-client-comment').val();
        if( name.length > 0 && lname.length > 0 && phone.length > 0 && email.length > 0 && comment.length > 0 ){
            jQuery(".lp-booking-form-input-confirm").removeAttr("disabled");
        }else{
            jQuery(".lp-booking-form-input-confirm").attr("disabled","");
        }
    });

    /* ******************************  DASHBOARD ADD LISTING FOR BOOKING  ******************************** */
    jQuery(document).on('click','.add-listing-for-booking, .dash-add-booking-btn', function(e) {
        e.preventDefault();
        var SelectedListingID = jQuery('#setting-listing-dropdown').find(":selected").val(),
            SelectedListingattr = jQuery('#setting-listing-dropdown').find(":selected").attr('data-disable');
        if(SelectedListingattr  ==  "yes"){
            jQuery(".lp-booking-error").fadeIn("slow").delay(1000).fadeOut("slow");
            return false;
        } else {
            var $this             = jQuery(this);
            if(SelectedListingID == "0"){
            }else{
                $this.find('i').removeClass('fa-plus').addClass('fa-spinner fa-spin');
                $this.addClass('active-ajax');
                jQuery.ajax({
                    dataType: 'json',
                    url: ajax_search_term_object.ajaxurl,
                    data:{
                        'action': 'add_listing_for_booking',
                        'SelectedListingID' : SelectedListingID,
                    },
                    success:function (res) {
                        $this.removeClass('active-ajax');
                        $this.find('.fa-spinner').removeClass('fa-spinner fa-spin').addClass('fa-plus');
                        if ($this.hasClass('dash-add-booking-btn')) {
                            jQuery('#booking-settings-sidebar .user-booking-settings-time-slot, #bookings-form-toggle .user-booking-settings-time-slot').addClass('res');
                            jQuery('#booking-settings-sidebar .user-booking-settings-time-slot, #bookings-form-toggle .user-booking-settings-time-slot').append(res.AddedListingDash);
                        }
                        else{
                            jQuery('#booking-settings-sidebar .user-booking-settings-time-slot, #bookings-form-toggle .user-booking-settings-time-slot').addClass('res');
                            jQuery('#booking-settings-sidebar .user-booking-settings-time-slot, #bookings-form-toggle .user-booking-settings-time-slot').append(res.addedListing);
                        }
                        jQuery('#setting-listing-dropdown').val(0);
                    },
                    error:function (err) {
                        console.log(err);
                    },
                });
            }
        }
    });
    jQuery(document).on('click', '.booking-setting-icon-delete-listing', function(){
        var $delListing =   jQuery(this),
            currentListingID =   $delListing.closest('li').find('span').attr("data-listing-id");

            $delListing.find('i').removeClass('fa-trash').addClass('fa-spinner fa-spin');
            $delListing.addClass('active-ajax');
            jQuery.ajax({
                dataType: 'json',
                url: ajax_search_term_object.ajaxurl,
                data:{
                    'action': 'del_listing_for_booking',
                    'currentListingID' : currentListingID,
                },
                success:function (res) {
                   console.log(res);
                    if(!res.del_listing_booking_slot[currentListingID]){
                        $delListing.closest('li').remove();
                    }
                },
                error:function (err) {
                    console.log(err);
                },
            });
    });
});
jQuery(document).ready(function () {
    jQuery('ul.booking-action-content:first').addClass('active');
});

function style4_adjust_height() {
    if(jQuery('.sidebar-top0').length) {
        var sidebarHeight   =   jQuery('.sidebar-top0').height(),
            topHeader       =   jQuery('.lp-listing-top-title-header').height(),
            newHeight       =   sidebarHeight-topHeader;

        jQuery('.min-height-class').css('min-height', newHeight+'px');
    }
}

jQuery(document).on('click','.lp-add-new-listing-for-booking, .cancelLpBookings, .bookings-save-btn',function(e){
    e.preventDefault();
    var $thisBTN    =   jQuery(this);
        targetForm  =   '#'+jQuery(this).data('form')+'-form-toggle';
        if($thisBTN.hasClass('lp-add-new-listing-for-booking')){
            jQuery(targetForm).fadeIn("fast", function () {
                jQuery( ".lp-blank-section" ).fadeOut();
            });
        }else if($thisBTN.hasClass('cancelLpBookings')){
            jQuery( "#bookings-form-toggle" ).fadeOut('fast');
            jQuery( ".lp-blank-section" ).fadeIn();
        }else{
            jQuery( "#bookings-form-toggle" ).fadeOut("fast");
            jQuery( ".lp-bookings-after-save-screen" ).fadeIn();
        }
});

jQuery(document).ready(function () {
    jQuery('.lp-booking-section-slide-up').click(function () {
        jQuery('.lp-booking-section').slideUp(function () {
            jQuery('.lp-booking-bar').show();
        });
    });
});


