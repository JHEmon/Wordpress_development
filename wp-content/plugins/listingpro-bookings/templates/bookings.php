    <?php

    $author_id = get_post_field( 'post_author', get_the_ID() );
    $listing_for_bookings = get_user_meta($author_id, 'listings_for_bookings', true);

    if(is_array($listing_for_bookings) && in_array(get_the_ID(), $listing_for_bookings)){
        if (!is_user_logged_in()) {
            $popup_style = $listingpro_options['login_popup_style'];
            $listing_mobile_view            =   $listingpro_options['single_listing_mobile_view'];
            if ( $popup_style == 'style1' && !wp_is_mobile() ) {
                echo '<div class="lp-booking-bar-login md-trigger" data-modal="modal-3">';
            } elseif ( $popup_style == 'style2' && !wp_is_mobile() ) {
                echo '<div class="lp-booking-bar-login app-view-popup-style" data-target="#app-view-login-popup">';
            } elseif ( wp_is_mobile() && $listing_mobile_view == 'app_view' ){
                echo '<div class="lp-booking-bar-login app-view-popup-style" data-target="#app-view-login-popup">';
            } elseif ( wp_is_mobile() && $listing_mobile_view == 'app_view2' ){
                echo '<div class="lp-booking-bar-login app-view-popup-style" data-target="#app-view-login-popup">';
            } elseif ( $popup_style == 'style1' && wp_is_mobile() && $listing_mobile_view != 'app_view' ) {
                echo '<div class="lp-booking-bar-login md-trigger" data-modal="modal-3">';
            } elseif ( $popup_style == 'style2' && wp_is_mobile() && $listing_mobile_view != 'app_view' ) {
                echo '<div class="lp-booking-bar-login app-view-popup-style" data-target="#app-view-login-popup">';
            }
        }else{
            echo '<div class="lp-booking-bar">';
        }
    ?>
    <i class="fa fa-calendar" aria-hidden="true"></i>
    <span class="lp-booking-bar-text"><?php echo esc_html__('Book an Appointment Now!','listingpro-bookings');  ?></span>
    <i class="fa fa-angle-down" aria-hidden="true"></i>
</div>
        <input type="hidden" id="datepicker-lang" value="<?php echo get_option('WPLANG'); ?>">
    <?php }?>

<div class="clearfix"></div>
<div class="lp-booking-section pos-relative">
    <i class="fa fa-angle-up lp-booking-section-slide-up"></i>
    <div class="lp-booking-section-list">
        <p class="lp-booking-section-top-month"><?php echo date_i18n('F'); ?>, <?php echo date_i18n('Y'); ?></p>
        <div class="date-slider">
            <div class="pos-relative">
                <span class="booking-slider-arrow-left" data-lid="<?php echo get_the_ID(); ?>" data-target="<?php echo strtotime("-1 day"); ?>"><i class="fa fa-angle-left" aria-hidden="true"></i></span>
                <p class="lp-booking-section-month-date" data-bcd="<?php echo strtotime(date_i18n('Y-m-d')); ?>"><?php echo date('j'); ?></p>
                <span class="booking-slider-arrow-right" data-lid="<?php echo get_the_ID(); ?>" data-target="<?php echo strtotime("+1 day"); ?>"><i class="fa fa-angle-right" aria-hidden="true"></i></span>
            </div>
        </div>
        <p class="lp-booking-section-top-day-name">
            <?php echo date_i18n("l"); ?>
        </p>
        <div class="lp-booking-slots-outer-wrap">
            <?php
                $timeSlotDuration   =   get_option('lp_booking_timeslot_duration');
                if(!$timeSlotDuration || empty($timeSlotDuration)) {
                    $timeSlotDuration   =   '30';
                }
                echo $times = create_time_range(get_the_ID(), strtotime(date("d F Y")), $timeSlotDuration.' mins', 12, 'yes');

            ?>
        </div>
    </div>

    <div class="booking-loader">
        <i class="fa fa-spinner fa-spin lp-booking-preloader-spinner"></i>
    </div>
    <div class="clearfix"></div>
    <div id="calendar" data-lid="<?php echo get_the_ID(); ?>">
        <div id="booking-calendar-select-date"></div>
    </div>
    <div class="clearfix"></div>
    <div class="lp-booking-section-footer">


        <?php
        $current_offset = get_option( 'gmt_offset' );
        $tzstring       = get_option( 'timezone_string' );
        $check_zone_info = true;
        if ( false !== strpos( $tzstring, 'Etc/GMT' ) ) {
            $tzstring = '';
        }
        if ( empty( $tzstring ) ) { // Create a UTC+- zone if no timezone string exists
            $check_zone_info = false;
            if ( 0 == $current_offset ) {
                $tzstring = 'UTC+0';
            } elseif ( $current_offset < 0 ) {
                $tzstring = 'UTC' . $current_offset;
            } else {
                $tzstring = 'UTC+' . $current_offset;
            }
        }
        ?>
        <span class="lp-booking-section-footer-timezone"><?php echo 'Timezone: '.$tzstring; ?> </span>
        <span class="lp-booking-section-footer-view-switch"><?php echo esc_html__('Switch to','listingpro-bookings');  ?> <i class="fa fa-calendar-o" aria-hidden="true"></i> </span>
    </div>
</div>
<div class="clearfix"></div>
<div class="lp-booking-form-container" data-lid="<?php echo get_the_ID(); ?>">
    <div class="lp-booking-form-wrapper">
        <input type="hidden" id="lp-booking-slot-str" value="">
        <input type="hidden" id="lp-booking-slot-date" value="">
        <input type="hidden" id="lp-booking-slot-str-end" value="">
        <p class="lp-form-booking-date"><?php echo esc_html__('August 26,2019','listingpro-bookings');  ?></p>
        <ul class="lp-form-booking-detail">
            <li class="lp-form-booking-detail-day"><?php echo esc_html__('Tuesday','listingpro-bookings');  ?></li>
            <li class="lp-form-booking-detail-vrdash"></li>
            <li class="lp-form-booking-detail-time"><?php echo esc_html__('9:00am - 10:00am','listingpro-bookings');  ?></li>
            <li class="lp-form-booking-detail-vrdash"></li>
            <li class="lp-form-booking-detail-author-name"> <?php echo get_the_author(); ?> </li>
        </ul>
        <div class="lp-booking-form">
            <?php
            $current_booker = wp_get_current_user();
            $booker_phone = get_user_meta($current_booker->ID, 'phone', true);
            ?>
            <input value="<?php echo $current_booker->first_name; ?>" type="text" placeholder="<?php esc_html_e('First Name','listingpro-bookings'); ?>"
                   class="lp-booking-form-input lp-booking-client-first-name" name="">
            <input value="<?php echo $current_booker->last_name; ?>" type="text" placeholder="<?php esc_html_e('Last Name','listingpro-bookings'); ?>"
                   class="lp-booking-form-input lp-booking-client-last-name" name="">
            <input value="<?php echo $current_booker->user_email; ?>" type="text" placeholder="<?php esc_html_e('Email','listingpro-bookings'); ?>"
                   class="lp-booking-form-input lp-booking-client-email" name="email">
            <input value="<?php echo $booker_phone; ?>" type="text" placeholder="<?php esc_html_e('Phone','listingpro-bookings'); ?>"
                   class="lp-booking-form-input lp-booking-client-phone" name="phone">
            <textarea class="lp-booking-form-input lp-booking-client-comment" placeholder="<?php esc_html_e('Comment','listingpro-bookings'); ?>"></textarea>
            <button type="submit" class="lp-booking-form-input-confirm" disabled><?php echo esc_html__('book it','listingpro-bookings');  ?></button>
            <span class="lp-booking-form-caption"><i class="fa fa-info-circle" aria-hidden="true"></i><?php echo esc_html__('Appointment confirmation email will be sent upon approval.','listingpro-bookings');  ?></span>
        </div>
    </div>
    <div class="booking-form-close">
        <i class="fa fa-times" aria-hidden="true"></i>
    </div>
    <div class="lp-booking-send-request">
        <i class="fa fa-calendar-check-o lp-booking-send-request-success"></i>
        <p class="lp-booking-send-request-success-caption"><?php echo esc_html__('Awesome Job!','listingpro-bookings');  ?></p>
        <p class="lp-booking-send-request-success-info"><?php echo esc_html__('We have received your appointment and will send you a confirmation to your provided email upon approval.','listingpro-bookings');  ?></p>
        <div class="booking-form-close">
            <i class="fa fa-times" aria-hidden="true"></i>
        </div>
        <div class="booking-loader">
            <i class="fa fa-spinner fa-spin lp-booking-preloader-spinner"></i>
        </div>
    </div>
    <div class="booking-loader">
        <i class="fa fa-spinner fa-spin lp-booking-preloader-spinner"></i>
    </div>
</div>

<div class="clearfix"></div>
