<?php
/*
Plugin Name: ListingPro Bookings
Text Domain: listingpro-bookings
Plugin URI:
Description: This plugin Only compatible With listingpro Theme By CridioStudio.
Version: 1.5
Author: CridioStudio (Dev Team)
Author URI: http://www.cridio.io
Author Email: support@cridio.com

Copyright 2018 CridioStudio
*/

class Listingpro_bookings{

}
function lp_booking_script()
{
    if (is_singular('listing') || isset($_GET['dashboard']) && $_GET['dashboard'] == 'manage-booking') {

        $lp_wp_lang    =   get_option('WPLANG');
		
        wp_register_script('bookingjs', plugins_url('/assets/js/listingpro-bookings.js', __FILE__), array('jquery'));
        $available_locales  =   array(
            'de_DE',
            'af',
            'ar_DZ',
            'ar',
            'az',
            'be',
            'bg',
            'cs',
            'bs',
            'ca',
            'cy_GB',
            'da',
            'de',
            'el',
            'en_AU',
            'en_GB',
            'en_NZ',
            'eo',
            'es',
            'es_ES',
            'es_MX',
            'et',
            'eu',
            'fa',
            'fi',
            'fo',
            'fr_CA',
            'fr_CH',
            'fr',
            'fr_FR',
            'gl',
            'he',
            'hi',
            'hr',
            'hu',
            'hy',
            'id',
            'is',
            'it_CH',
            'it',
            'js',
            'ka',
            'kk',
            'km',
            'ko',
            'ky',
            'lb',
            'lt',
            'lv',
            'mk',
            'ml',
            'ms',
            'nb',
            'nl_BE',
            'nl',
            'nn',
            'no',
            'pl',
            'pt',
            'pt_BR',
            'rm',
            'ro',
            'ru',
            'sk',
            'sl',
            'sq',
            'sr',
            'sr_SR',
            'sv',
            'ta',
            'tj',
            'th',
            'tr',
            'uk',
            'vi',
            'zh_CN',
            'zh_HK',
            'zh_TW'
        );
        if(!empty($lp_wp_lang) && in_array($lp_wp_lang, $available_locales)) {
            wp_register_script('datelocale', 'https://sandbox.listingprowp.com/datepicker-locales/datepicker-'.$lp_wp_lang.'.js', array('jquery-ui'));
        }

        wp_enqueue_script('bookingjs');
        wp_localize_script('bookingjs', 'lp_booking_vars', array(
            'associated_listing' => esc_html__('Associated Listing:', 'listingpro-bookings'),
        ));
        wp_enqueue_script('datelocale');
        wp_enqueue_style('bookingStyle', WP_PLUGIN_URL . '/listingpro-bookings/assets/css/listingpro-bookings.css');
    }
}

add_action('wp_enqueue_scripts', 'lp_booking_script');

/* ***************  BOOKING POSTTYPE  ************** */

add_action('init', 'create_post_type_lpBookings');
function create_post_type_lpBookings()
{
    $labels = array(
        'name' => _x('Appointments', 'post type general name', 'listingpro-bookings'),
        'singular_name' => _x('Booking', 'post type singular name', 'listingpro-bookings'),
        'menu_name' => _x('Appointments', 'admin menu', 'listingpro-bookings'),
        'name_admin_bar' => _x('Booking', 'add new on admin bar', 'listingpro-bookings'),
        'add_new' => _x('Add New', 'review', 'listingpro-bookings'),
        'add_new_item' => __('Add New Booking', 'listingpro-bookings'),
        'new_item' => __('New Booking', 'listingpro-bookings'),
        'edit_item' => __('Edit Booking', 'listingpro-bookings'),
        'view_item' => __('View Booking', 'listingpro-bookings'),
        'all_items' => __('All Bookings', 'listingpro-bookings'),
        'search_items' => __('Search Bookings', 'listingpro-bookings'),
        'parent_item_colon' => __('Parent Bookings:', 'listingpro-bookings'),
        'not_found' => __('No Bookings found.', 'listingpro-bookings'),
        'not_found_in_trash' => __('No Booking found in Trash.', 'listingpro-bookings')
    );

    $args = array(
        'labels' => $labels,
        'menu_icon' => 'dashicons-media-spreadsheet',
        'description' => __('Description.', 'listingpro-bookings'),
        'public' => true,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array(
            'slug' => 'listingpro-bookings'
        ),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => true,
        'menu_position' => 30,
        'supports' => array('title', 'editor', 'thumbnail'),
    );

    register_post_type('listingpro-bookings', $args);
}

function disable_new_posts() {
    // Hide sidebar link
    global $submenu;
    unset($submenu['edit.php?post_type=listingpro-bookings'][10]);

    // Hide link on listing page
    if (isset($_GET['post_type']) && $_GET['post_type'] == 'listingpro-bookings') {
        echo '<style type="text/css">
        a.page-title-action { display:none; }
        </style>';
    }
}
add_action('admin_menu', 'disable_new_posts');


/* ****************** BOOKING FORM TO POST ****************** */

add_action('wp_ajax_listingpro_booking_npd', 'listingpro_booking_npd');
add_action('wp_ajax_nopriv_listingpro_booking_npd', 'listingpro_booking_npd');
if (!function_exists('listingpro_booking_npd')) {

    function listingpro_booking_npd()
    {

        $type = sanitize_text_field($_POST['type']);
        $dataTarget = sanitize_text_field($_POST['dataTarget']);
        $lid = sanitize_text_field($_POST['lid']);

        if ($type == 'calendar') {
            $dataTarget = strtotime($dataTarget);
        }

        $today_date     =   date_i18n('d-m-y');
        $selected_date  =   date_i18n('d-m-y', $dataTarget);

        $timeZon_check  =   'no';
        if($today_date == $selected_date) {
            $timeZon_check  =   'yes';
        }


        $targetDay = date_i18n('l', (int)$dataTarget);
        $timeSlotDuration = get_option('lp_booking_timeslot_duration');
        if (!$timeSlotDuration || empty($timeSlotDuration)) {
            $timeSlotDuration = '30';
        }

        $newCurDay = date_i18n('d', (int)$dataTarget);
        $newCurMonth = date_i18n('F', (int)$dataTarget);
        $newCurYear = date_i18n('Y', (int)$dataTarget);
        $newCurDayName = date_i18n('l', (int)$dataTarget);

        if ($type == 'next') {
            $newNextStr = strtotime(date_i18n('Y-m-d', (int)$dataTarget) . " +1 days");
            $newPrevStr = strtotime(date_i18n('Y-m-d', (int)$dataTarget) . " -1 days");
        } elseif ($type == 'prev') {
            $newNextStr = strtotime(date_i18n('Y-m-d', (int)$dataTarget) . " +1 days");
            $newPrevStr = strtotime(date_i18n('Y-m-d', (int)$dataTarget) . " -1 days");
        } else {
            $newNextStr = strtotime(date_i18n('Y-m-d', (int)$dataTarget) . " +1 days");
            $newPrevStr = strtotime(date_i18n('Y-m-d', (int)$dataTarget) . " -1 days");
        }

        $times = create_time_range($lid, $dataTarget, $timeSlotDuration . ' mins', 12, $timeZon_check);


        $return = json_encode(
            array(
                'type' => $type,
                'timeslots' => $times,
                'newNext' => $newNextStr,
                'newPrev' => $newPrevStr,
                'newcur' => $newCurDay,
                'newCurMonth' => $newCurMonth,
                'newCurYear' => $newCurYear,
                'newCurDayName' => $newCurDayName
            )
        );
        die($return);
    }

}
function create_time_range($listing_id, $timeString, $interval = '30 mins', $format = '12', $timeZon_check) {

    $business_hours =   listing_get_metabox_by_ID('business_hours', $listing_id);
    if(is_array($business_hours)){
        $business_hours =   array_change_key_case($business_hours, CASE_LOWER);
    }

    $day             =   strtolower(date_i18n('l', $timeString));
    $timeString_date =   date('d F Y', $timeString);
    
    $times               =   '';
    $startTime_2nd_slot  = '';
    $endTime_2nd_slot    = '';
    $startTime_prev_slot = '';
    $endTime_prev_slot   = '';
    
    if(isset($business_hours) && !empty($business_hours)){
        foreach($business_hours as $key => $value){
            if( strpos($key, $day."~") !== false  ){
                $start_2nd_slot     =   $business_hours[$key]['open'][1];
                $end_2nd_slot       =   '11:59pm';
                $startTime_2nd_slot =   strtotime(date('Y-m-d', $timeString).' '.$start_2nd_slot);
                $endTime_2nd_slot   =   strtotime(date('Y-m-d', $timeString).' ' .$end_2nd_slot);
            }
            if( strpos($key, "~".$day) !== false  ){
                $start_prev_slot     =   '12:00am';
                $end_prev_slot       =   $business_hours[$key]['close'][1];
                $startTime_prev_slot =   strtotime(date('Y-m-d', $timeString).' ' .$start_prev_slot);
                $endTime_prev_slot   =   strtotime(date('Y-m-d', $timeString).' '.$end_prev_slot);

            }
        }
    }
    
    
    if( isset($business_hours[$day]) || ($startTime_prev_slot && $endTime_prev_slot) ){
        if(isset($business_hours[$day]['open']) && !empty($business_hours[$day]['open']) && isset($business_hours[$day]['close']) && !empty($business_hours[$day]['close'])) {
            if(count($business_hours[$day]['open']) > 1){
                $start   =   date('Y-m-d', $timeString).' '.$business_hours[$day]['open'][0];
                $end     =   date('Y-m-d', $timeString).' '.$business_hours[$day]['close'][0];
                
                $start_2nd_slot  =   date('Y-m-d', $timeString).' '.$business_hours[$day]['open'][1];
                $end_2nd_slot    =   date('Y-m-d', $timeString).' '.$business_hours[$day]['close'][1];
                
                $startTime_2nd_slot = strtotime($start_2nd_slot);
                $endTime_2nd_slot   = strtotime($end_2nd_slot);
            }else{
                if( $startTime_2nd_slot != '' && $endTime_2nd_slot != ''){
                    $start   =   date('Y-m-d', $timeString).' '.$business_hours[$day]['open'][0];
                    $end     =   date('Y-m-d', $timeString).' '.$business_hours[$day]['close'][0];
                }else{
                    $start   =   date('Y-m-d', $timeString).' '.$business_hours[$day]['open'];
                    $end     =   date('Y-m-d', $timeString).' '.$business_hours[$day]['close'];
                }
            }
        }else{
            if(isset($business_hours[$day])){
                $start   =   date('Y-m-d', $timeString).' '.'12:00am';
                $end     =   date('Y-m-d', $timeString).' '.'11:59pm';
            }else{
                $start = '';
                $end   = '';
            }
        }
        
        $startTime = '';
        $endTime   = '';
        if( $start != '' ){
            $startTime = strtotime($start);
        }
        if($end != ''){
            $endTime   = strtotime($end);
        }
        
        $listing_bookings_details   =   get_post_meta($listing_id, 'listing-booking-details', true);
        $b_args = array(
            'post_type' => 'listingpro-bookings',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'lp_listing_id',
                    'value' => $listing_id,
                    'compare' => '='
                ),
            )
        );
        $lp_bookings        =   new WP_Query($b_args);
        $lp_bookings_start_time_arr    =   array();
        if($lp_bookings->have_posts()) : while ($lp_bookings->have_posts()) : $lp_bookings->the_post();
          $bookings_start_time         =    get_post_meta(get_the_ID(),'lp_booking_start_time',true);
            $lp_bookings_start_time_arr[]   =   $bookings_start_time;
        endwhile; wp_reset_postdata(); endif;
        
        
        $times  .=   '<ul class="available-booking-slots lp-booking-section-time-pill-container">';
            $time_slots  = '';
            $time_slots .= create_time_slots( $timeString, $startTime_prev_slot, $endTime_prev_slot, $day, $interval, $timeZon_check, $lp_bookings_start_time_arr );
            $time_slots .= create_time_slots( $timeString, $startTime, $endTime, $day, $interval, $timeZon_check, $lp_bookings_start_time_arr );
            $time_slots .= create_time_slots( $timeString, $startTime_2nd_slot, $endTime_2nd_slot, $day, $interval, $timeZon_check, $lp_bookings_start_time_arr );

            if( $time_slots == '' ){
                $times  .=  esc_html__('Closed Now', 'listingpro-bookings');
            }else{
                $times  .= $time_slots;
            }
        $times  .=  '</ul>';

    }else{
        $times  .= '<strong>'.esc_html__('DAY OFF','listingpro-bookings').'</strong>';
    }

    return $times;
}

function create_time_slots( $timeString, $startTime = '', $endTime = '', $day, $interval, $timeZon_check = 'yes', $lp_bookings_start_time_arr = array() ){
    
    if( $startTime == '' || $endTime == '' ){
        return '';
    }
    
    $timezone           =   get_option('gmt_offset');
    $time_now           =   gmdate("Y-m-d H:i", time() + 3600 * ($timezone + date("I")));
    $timeZone_str       =   strtotime($time_now);
    
    $current            =   time();
    $addTime            =   strtotime('+'.$interval, $current);
    $diff               =   $addTime - $current;
    $intervalEnd        =   $startTime+$diff;
    
    $time_slots = '';
    while ($startTime < $endTime) {
        
        $appt_start      =   (int)$startTime;
        $appt_end        =   (int)$intervalEnd;
        $appt_time_slot  =   date_i18n(get_option('time_format'), $appt_start).' - '.date_i18n(get_option('time_format'), $appt_end);

        $disable = '';
        if(in_array( $appt_start, $lp_bookings_start_time_arr )){
            $disable = "lp-booking-disable";
        }
        
        if($timeZon_check == 'yes') {
            if($appt_start > $timeZone_str) {
                $time_slots  .= '
                <li class="'. $disable .'" data-booking-date="'. $timeString .'" data-booking-timestr-end="'. $appt_end .'" data-booking-timestr="'. $appt_start .'" data-booking-slot="'. $appt_time_slot .'">
                    <p class="lp-booking-section-time-pill-hover">
                        <span class="lp-booking-section-time-pill-1">'. date_i18n(get_option('time_format'), $appt_start) .'</span>
                        <span class="lp-booking-section-time-pill-dash">-</span>
                        <span class="lp-booking-section-time-pill-2">'. date_i18n(get_option('time_format'), $appt_end) .'</span>
                    </p>
                </li>';
            }
        } else {
            $time_slots  .= '
                <li class="'. $disable .'" data-booking-date="'. $timeString .'" data-booking-timestr-end="'. $appt_end .'" data-booking-timestr="'. $appt_start .'" data-booking-slot="'. $appt_time_slot .'">
                    <p class="lp-booking-section-time-pill-hover">
                        <span class="lp-booking-section-time-pill-1">'.date_i18n(get_option('time_format'), $appt_start).'</span>
                        <span class="lp-booking-section-time-pill-dash">-</span>
                        <span class="lp-booking-section-time-pill-2">'. date_i18n(get_option('time_format'), $appt_end) .'</span>
                    </p>
                </li>
            ';
        }

        $startTime      +=  $diff;
        $intervalEnd    +=  $diff;
    }
    
    return $time_slots;
}

/* ******************************  BOOKING CREATION  ******************************** */

add_action('wp_ajax_create_lp_bookings', 'create_lp_bookings');
add_action('wp_ajax_nopriv_create_lp_bookings', 'create_lp_bookings');

function create_lp_bookings()
{
    $current_user = wp_get_current_user();

    $return = array();

    if (isset($_REQUEST)) {
        $name = sanitize_text_field($_REQUEST['name']);
        $lName = sanitize_text_field($_REQUEST['lName']);
        $email = sanitize_email($_REQUEST['email']);
        $phone = sanitize_text_field($_REQUEST['phone']);
        $comment = sanitize_text_field($_REQUEST['comment']);
        $lid = sanitize_text_field($_REQUEST['lid']);
        $datee = sanitize_text_field($_REQUEST['datee']);
        $timee = sanitize_text_field($_REQUEST['timee']);
        $timeSlotStr = sanitize_text_field($_REQUEST['timeSlotStr']);
        $timeSlotStrDate = sanitize_text_field($_REQUEST['timeSlotStrDate']);
        $timeSlotStrEnd = sanitize_text_field($_REQUEST['timeSlotStrEnd']);
        $booking_status = 'PENDING';
        $user_id = $current_user->ID;
    }

    /* ***************  FOR SUCCESSFUL BOOKING EMAIL  *************** */
    $author_id = get_post_field('post_author', $lid);
    $author_mail = get_the_author_meta('user_email', $author_id);
    $mail_to = $email;
    $mail_subject = esc_html__('New Appointment For','listingpro-bookings') . ' ' . get_the_title($lid);
    $mail_msg = $name . ' ' . $lName . ' ' . $phone . ' ' . $datee . ' ' . $timee . ' ' . $comment;

    $return["Author Mail"] = $author_mail;
    $return["to"] = $mail_to;
    $return["Subject"] = $mail_subject;
    $return["Msg"] = $mail_msg;


    $splitTime = str_replace(' ', '', $timee);
    $timeslots = explode("-", $splitTime);


    $args = array(
        'post_content' => $comment,
        'post_status' => 'publish',
        'post_title' => $name . ' ' . $lName . ' ' . '(' . get_the_title($lid) . ')',
        'post_type' => 'listingpro-bookings',
        'post_author' => $user_id
    );


    $lp_booking_id = wp_insert_post($args);
    if (!is_wp_error($lp_booking_id)) {
        $return['status'] = 'success';


        $date_arr1 = explode(', ', $datee);
        $date_arr2 = explode(' ', $date_arr1[0]);

        $date_mname = $date_arr2[1];

        $date_year = $date_arr1[1];
        $date_day = $date_arr2[0];
        $date_mname_num = date('m', strtotime($date_mname));

        $date_str = strtotime($date_day . '-' . $date_mname_num . '-' . $date_year);


        update_post_meta($lp_booking_id, 'lp_current_user_booking_id', $user_id);
        update_post_meta($lp_booking_id, 'lp_booking_name', $name);
        update_post_meta($lp_booking_id, 'lp_booking_lName', $lName);
        update_post_meta($lp_booking_id, 'lp_booking_emial', $email);
        update_post_meta($lp_booking_id, 'lp_booking_phone', $phone);
        update_post_meta($lp_booking_id, 'lp_booking_date', $timeSlotStrDate);
        update_post_meta($lp_booking_id, 'lp_booking_start_time', $timeSlotStr);
        update_post_meta($lp_booking_id, 'lp_booking_end_time', $timeSlotStrEnd);
        update_post_meta($lp_booking_id, 'lp_booking_status', $booking_status);
        update_post_meta($lp_booking_id, 'lp_listing_author', $author_id);
        update_post_meta($lp_booking_id, 'lp_listing_id', $lid);

        $user_bookings_list = get_user_meta($user_id, 'lp_user_booking_ids_list', true);
        if (is_array($user_bookings_list) && !empty($user_bookings_list)) {
            $user_bookings_list_arr = array($lp_booking_id);
            $user_bookings_list[$lp_booking_id] = $user_bookings_list_arr;
            update_user_meta($user_id, 'lp_user_booking_ids_list', $user_bookings_list);
        } else {
            $user_listing_bookings_list = array();
            $user_bookings_list_arr = array($lp_booking_id);
            $user_listing_bookings_list[$lp_booking_id] = $user_bookings_list_arr;
            update_user_meta($user_id, 'lp_user_booking_ids_list', $user_listing_bookings_list);
        }

        $current_listing_bookings = get_post_meta($lid, 'listing-booking-details', true);
        if (is_array($current_listing_bookings) && !empty($current_listing_bookings)) {
            $booking_detail_arr = array(strtotime($timeslots[0] . ' ' . $datee), strtotime($timeslots[1] . ' ' . $datee), $booking_status);
            $current_listing_bookings[$timeSlotStr . '-' . $lp_booking_id] = $booking_detail_arr;

            update_post_meta($lid, 'listing-booking-details', $current_listing_bookings);

        } else {
            $listing_bookings = array();

            $booking_detail_arr = array(strtotime($timeslots[0] . ' ' . $datee), strtotime($timeslots[1] . ' ' . $datee), $booking_status);
            $listing_bookings[$timeSlotStr . '-' . $lp_booking_id] = $booking_detail_arr;

            update_post_meta($lid, 'listing-booking-details', $listing_bookings);
        }
        $booking_mail_msg_booker            =   lp_booking_get_email_content($lp_booking_id, 'create', 'booker');
        $booking_mail_msg_listing_author    =   lp_booking_get_email_content($lp_booking_id, 'create', 'listing_author');

        $author_details         =   get_user_by('ID', $author_id);
        $listing_author_email   =   $author_details->user_email;
        $headers    = array();

        lp_mail_headers_append();
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        LP_send_mail( $email, esc_html__('Appointment Created', 'listingpro-bookings'), $booking_mail_msg_booker, $headers );
        LP_send_mail( $listing_author_email, esc_html__('Appointment Received', 'listingpro-bookings'), $booking_mail_msg_listing_author, $headers );
        lp_mail_headers_remove();


    } else {
        $return['status'] = 'error';
    }

    die(json_encode($return));

}

/* **************** LP_BOOKINFS CUSTOM COLUMNS ******************* */

function lp_bookings_columns($columns)
{
    return array(
        'cb' => '<input type="checkbox" />',
        'title' => esc_html__('Title', 'listingpro-bookings'),
        'email' => esc_html__('Email', 'listingpro-bookings'),
        'phone' => esc_html__('Phone', 'listingpro-bookings'),
        'Booking-Date' => esc_html__('Booking Date', 'listingpro-bookings'),
        'startTime' => esc_html__('Booking Start Time', 'listingpro-bookings'),
        'endTime' => esc_html__('Booking End Time', 'listingpro-bookings'),
        'Booking-Status' => esc_html__('Booking Status', 'listingpro-bookings'),
        'date' => esc_html__('Date', 'listingpro-bookings'),
    );
}

add_filter('manage_listingpro-bookings_posts_columns', 'lp_bookings_columns');

function lp_booking_columns_content($column, $post_id)
{
    if ($column == 'email') {
        $booking_email = get_post_meta($post_id, 'lp_booking_emial', true);
        echo $booking_email;
    }
    if ($column == 'phone') {
        $booking_phone = get_post_meta($post_id, 'lp_booking_phone', true);
        echo $booking_phone;
    }
    if ($column == 'Booking-Date') {
        $booking_booking_date = get_post_meta($post_id, 'lp_booking_date', true);
        if (!empty($booking_booking_date)) {
            echo date_i18n(get_option('date_format'), $booking_booking_date);
        }
    }
    if ($column == 'startTime') {
        $booking_start_time = get_post_meta($post_id, 'lp_booking_start_time', true);
        echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $booking_start_time);
    }
    if ($column == 'endTime') {
        $booking_end_time = get_post_meta($post_id, 'lp_booking_end_time', true);
        echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $booking_end_time);
    }
    if ($column == 'Booking-Status') {
        $lp_booking_status = get_post_meta($post_id, 'lp_booking_status', true);
        echo listingpro_booking_status_label($lp_booking_status);
    }
}

add_action('manage_listingpro-bookings_posts_custom_column', 'lp_booking_columns_content', 10, 2);

/* ******************************  BOOKING DETAILS  ******************************** */
add_action('wp_ajax_get_booking_details', 'get_booking_details');
add_action('wp_ajax_nopriv_get_booking_details', 'get_booking_details');
function get_booking_details()
{
    $markup = '';

    if (isset($_REQUEST)) {
        $cbid = sanitize_text_field($_REQUEST['cbid']);

        $cb_date_str = get_post_meta($cbid, 'lp_booking_date', true);
        $cb_date = date_i18n(get_option('date_format'), $cb_date_str);
        $cb_day = date_i18n('l', $cb_date_str);
        $cb_start_time_str = get_post_meta($cbid, 'lp_booking_start_time', true);
        $cb_start_time = date_i18n(get_option('time_format'), $cb_start_time_str);
        $cb_end_time_str = get_post_meta($cbid, 'lp_booking_end_time', true);
        $cb_end_time = date_i18n(get_option('time_format'), $cb_end_time_str);
        $cb_email = get_post_meta($cbid, 'lp_booking_emial', true);
        $cb_phone = get_post_meta($cbid, 'lp_booking_phone', true);
        $cb_msg = get_post_field('post_content', $cbid);
        $cb_name = get_post_meta($cbid, 'lp_booking_name', true);
        $cb_lName = get_post_meta($cbid, 'lp_booking_lName', true);

        $booking_listing_id = get_post_meta($cbid, 'lp_listing_id', true);

        $gAddress = get_post_meta($booking_listing_id, 'lp_listingpro_options', true);
        $gAddress = $gAddress['gAddress'];

        $booker_id = get_post_field('post_author', $cbid);
        $booker_data = get_user_by('ID', $booker_id);

        $author_avatar_url = get_user_meta($booker_id, "listingpro_author_img_url", true);
        if (!empty($author_avatar_url)) {
            $avatar = $author_avatar_url;
        } else {
            $avatar_url = listingpro_get_avatar_url($booker_id, $size = '94');
            $avatar = $avatar_url;
        }


        $tzstring = get_option('timezone_string');
        $current_offset = get_option('gmt_offset');
        $check_zone_info = true;
        if (false !== strpos($tzstring, 'Etc/GMT')) {
            $tzstring = '';
        }
        if (empty($tzstring)) { // Create a UTC+- zone if no timezone string exists
            $check_zone_info = false;
            if (0 == $current_offset) {
                $tzstring = 'UTC+0';
            } elseif ($current_offset < 0) {
                $tzstring = 'UTC' . $current_offset;
            } else {
                $tzstring = 'UTC+' . $current_offset;
            }
        }

        $markup .= '<div class="user-detail">
                <div class="user-sidebar-avatar">
                    <img src="' . $avatar . '">
                </div>
                <p class="user-name">' . $booker_data->user_login . '</p>
                <p class="user-status">Registered User</p>
            </div>';

        $markup .= '<h4 class="booking-action-header">'.esc_html__('Appointment Detail','listingpro-bookings').'</h4>';
        $markup .= '<div class="user-booking-detail">';
        $markup .= '
                <span class="user-booking-detail-name">'.esc_html__('Full Name','listingpro-bookings').' </span>
                <span class="user-booking-detail-name-detail pull-right">' . $cb_name . ' '. $cb_lName .'</span> 
                <br>
                <span class="user-booking-detail-name">'.esc_html__('Date','listingpro-bookings').'</span>
             
                <span class="user-booking-detail-name-detail pull-right">' . $cb_day .' '. ', ' . $cb_date . '</span><br>
                <span class="user-booking-detail-name">'.esc_html__('Time','listingpro-bookings').'</span>
                <span class="user-booking-detail-name-detail pull-right">' . $cb_start_time . ' - ' . $cb_end_time . ' ' . $tzstring . '</span><br>
                <span class="user-booking-detail-name">'.esc_html__('Email','listingpro-bookings').'</span>
                <span class="user-booking-detail-name-detail pull-right">' . $cb_email . '</span><br>
                <span class="user-booking-detail-name">'.esc_html__('Phone','listingpro-bookings').'</span>
                <span class="user-booking-detail-name-detail pull-right">' . $cb_phone . '</span><br>
                <span class="user-booking-detail-name">'.esc_html__('Address','listingpro-bookings').'</span>
                <span class="user-booking-detail-name-detail pull-right underline">' . $gAddress . '</span><br>
                <span class="user-booking-detail-name">'.esc_html__('Message','listingpro-bookings').'</span><br>
                <span class="user-booking-detail-name-detail">' . $cb_msg . '</span>
        ';
        $markup .= '</div>';
    }
    echo $markup;
    die();
}

/* ******************************  BOOKING STATUS  ******************************** */
add_action('wp_ajax_Booking_status', 'Booking_status');
add_action('wp_ajax_nopriv_Booking_status', 'Booking_status');
function Booking_status()
{
    if (isset($_REQUEST)) {
        $cBstatus = sanitize_text_field($_REQUEST['cBstatus']);
        $cbid = sanitize_text_field($_REQUEST['cbid']);

        update_post_meta($cbid, 'lp_booking_status', $cBstatus);

        $booker_email               =   get_post_meta($cbid, 'lp_booking_emial', true);

        $subject    =   esc_html__('Appointment Approved','listingpro-bookings');
        $booking_a  =   'approved';

        if($cBstatus == 'CANCELED') {
            $subject    =   esc_html__('Appointment Canceled','listingpro-bookings');
            $booking_a  =   'canceled';
        }

        $booking_mail_msg_booker    =   lp_booking_get_email_content($cbid, $booking_a, 'booker');
        
        lp_mail_headers_append();
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        wp_mail( $booker_email, $subject, $booking_mail_msg_booker, $headers );
        lp_mail_headers_remove();

        $cb_updated_status = get_post_meta($cbid, 'lp_booking_status', true);
    }
    echo listingpro_booking_status_label($cb_updated_status) . ' &nbsp;&nbsp;<span class="caret"></span>';
    die();
}

/* ******************************  LIST BOOKINGS ON CALANDER  ******************************** */
add_action('wp_ajax_calendar_bookings_listing', 'calendar_bookings_listing');
add_action('wp_ajax_nopriv_calendar_bookings_listing', 'calendar_bookings_listing');
function calendar_bookings_listing()
{
    if (isset($_REQUEST)) {
        $lastDay = sanitize_text_field($_REQUEST['lastDay']);
        $firstDay = sanitize_text_field($_REQUEST['firstDay']);

        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $b_args = array(
            'post_type' => 'listingpro-bookings',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'lp_listing_author',
                    'value' => $user_id,
                    'compare' => '='
                ),
                array(
                    'key' => 'lp_booking_status',
                    'value' => 'APPROVED',
                    'compare' => '='
                ),
                array(
                    'key' => 'lp_booking_date',
                    'value' => array(strtotime($firstDay), strtotime($lastDay)),
                    'compare' => 'BETWEEN',
                    'type' => 'numeric'
                )
            )
        );
        $current_disable_booking = get_option('lp_booking_settings');
        $lp_bookings = new WP_Query($b_args);
        $lp_bookings_arr = array();
        if ($lp_bookings->have_posts()) : while ($lp_bookings->have_posts()) : $lp_bookings->the_post();
            $booking_start_time = get_post_meta(get_the_ID(), 'lp_booking_start_time', true);
            $lp_bookings_arr[$booking_start_time] = get_the_ID();
        endwhile;
            wp_reset_postdata(); endif;
        ksort($lp_bookings_arr);

        $timezone       =   get_option('gmt_offset');
        $time_now       =   gmdate("H:i", time() + 3600*($timezone+date("I")));
        $timeZone_str   =   strtotime($time_now);

        if (is_array($lp_bookings_arr) && count($lp_bookings_arr > 0)) {
            foreach ($lp_bookings_arr as $k => $v) {

                $listing_id = get_post_meta($v, 'lp_listing_id', true);


                $gAddress = get_post_meta($listing_id, 'lp_listingpro_options', true);
                $listing_addr = $gAddress['gAddress'];

                $listing_title = get_the_title($listing_id);

                $booking_id = $v;

                $booker_id = get_post_field('post_author', $booking_id);
                $booker_data = get_user_by('ID', $booker_id);
                $booker_name = $booker_data->user_login;

                $booking_phone = get_post_meta($booking_id, 'lp_booking_phone', true);
                $booking_msg = get_post_field('post_content', $booking_id);

                $booking_date = date('F j, Y', (int)get_post_meta($booking_id, 'lp_booking_date', true));
                $lp_booking_date = date_i18n('F j, Y', (int)get_post_meta($booking_id, 'lp_booking_date', true));
                $booking_start = date_i18n(get_option('time_format'), (int)get_post_meta($booking_id, 'lp_booking_start_time', true));
                $booking_end = date_i18n(get_option('time_format'), (int)get_post_meta($booking_id, 'lp_booking_end_time', true));

                $current_month_number = date('m');
                $booking_date_str = strtotime($booking_date);

                $nextMonthFirstDate = date_create($lastDay . 'first day of next month')->format('1 F Y');
                $nextMonthLastDate = date_create($lastDay . 'first day of next month')->format('t F Y');

                $PrevMonthLastDate = date_create($lastDay . 'first day of last month')->format('t F Y');
                $PrevMonthStartDate = date_create($firstDay . 'last day of last month')->format('1 F Y');


                $current_month_start_date = strtotime($firstDay);
                $current_month_end_date = strtotime($lastDay);
                if(get_post_meta($booking_id, 'lp_booking_end_time', true) > $timeZone_str) {
                    if ($booking_date_str >= $current_month_start_date && $booking_date_str <= $current_month_end_date) {
                        $booking_data_by_date[$booking_date][$booking_id] = array('Booker Name' => $booker_name, 'Prev Last' => $PrevMonthLastDate, 'Prev Start' => $PrevMonthStartDate, 'Next Start' => $nextMonthFirstDate, 'Next Last' => $nextMonthLastDate, 'addr' => $listing_addr, 'Listing Title' => $listing_title, 'Booking Date' => $booking_date,'lp_booking_date'=>$lp_booking_date, 'Month Start Date' => $current_month_start_date, 'Current Month' => $current_month_number, 'Month End Date' => $current_month_end_date, 'Start Time' => $booking_start, 'End Time' => $booking_end, 'Booking Status' => $booking_status, 'Booking Phone' => $booking_phone, 'Booking Message' => $booking_msg);
                    }
                }
            }
        }
    }

    $nextMonthFirstDate = date_create($lastDay . 'first day of next month')->format('1 F Y');
    $nextMonthLastDate = date_create($lastDay . 'first day of next month')->format('t F Y');

    $PrevMonthLastDate = date_create($lastDay . 'first day of last month')->format('t F Y');
    $PrevMonthStartDate = date_create($firstDay . 'last day of last month')->format('1 F Y');


    $booking_data_by_date['lp_booking_settings'] = $current_disable_booking;
    $booking_data_by_date['booking_arry'] = $lp_bookings_arr;
    $booking_data_by_date['next_first'] = $nextMonthFirstDate;
    $booking_data_by_date['next_last'] = $nextMonthLastDate;
    $booking_data_by_date['prev_first'] = $PrevMonthStartDate;
    $booking_data_by_date['prev_last'] = $PrevMonthLastDate;
    $booking_data_by_date['assoc'] = esc_html__( 'Associated Listing:', 'listingpro-bookings' );
    die(json_encode($booking_data_by_date));

}

/* ******************************  ENABLE DISABLE BOOKINGS ON CALANDER  ******************************** */
//add_action('wp_ajax_Enable_Disable_Bookings', 'Enable_Disable_Bookings');
//add_action('wp_ajax_nopriv_Enable_Disable_Bookings', 'Enable_Disable_Bookings');
function Enable_Disable_Bookings()
{
    if (isset($_REQUEST)) {
        $cFullDate = sanitize_text_field($_REQUEST['cFullDate']);
        $bookingAction = sanitize_text_field($_REQUEST['bookingAction']);
        $cFullDate_str = strtotime($cFullDate);
        $lp_bookings_arr = array($cFullDate => $cFullDate_str);
        $current_settings = get_option('lp_booking_settings');


        if ($bookingAction == "Disabled") {
            if (!$current_settings || empty($current_settings)) {
                update_option('lp_booking_settings', $lp_bookings_arr);
            } else {
                $current_settings[$cFullDate] = $cFullDate_str;
                update_option('lp_booking_settings', $current_settings);
            }
        } else if ($bookingAction == "Enabled") {
            $current_settings[$cFullDate] = $cFullDate_str;
            unset($current_settings[$cFullDate]);
            update_option('lp_booking_settings', $current_settings);
        }
    }
    die(json_encode($bookingAction));
}

/* ******************************  DASHBOARD DELETE BOOKING SLOT  ******************************** */

add_action('wp_ajax_dashboard_booking_settings_delete_slot', 'dashboard_booking_settings_delete_slot');
add_action('wp_ajax_nopriv_dashboard_booking_settings_delete_slot', 'dashboard_booking_settings_delete_slot');
function dashboard_booking_settings_delete_slot()
{
    if (isset($_REQUEST)) {
        $DelcurrentSLot = sanitize_text_field($_REQUEST['currentSLot']);
        $DelcurrentSLot_str = strtotime($DelcurrentSLot);
        $dash_booking_setting_delete_slot = get_option('lp_booking_settings');
        if ($dash_booking_setting_delete_slot[$DelcurrentSLot] = $DelcurrentSLot_str) {
            unset($dash_booking_setting_delete_slot[$DelcurrentSLot]);
            update_option('lp_booking_settings', $dash_booking_setting_delete_slot);
        }
        $del_booking_slot = get_option('lp_booking_settings');
    }
    $return = json_encode(
        array(
            'dash_booking_setting_delete_slot' => $dash_booking_setting_delete_slot,
            'del_booking_slot' => $del_booking_slot,
        )
    );
    die($return);
}

/* ******************************  DASHBOARD ADD BOOKING SLOT  ******************************** */
add_action('wp_ajax_dashboard_booking_settings_add_slot', 'dashboard_booking_settings_add_slot');
add_action('wp_ajax_nopriv_dashboard_booking_settings_add_slot', 'dashboard_booking_settings_add_slot');
function dashboard_booking_settings_add_slot()
{
    if (isset($_REQUEST)) {
        $addcurrentSLot = sanitize_text_field($_REQUEST['booking_setting_td_date']);
        $recurring_settings_date = sanitize_text_field($_REQUEST['recurring_settings_date']);


        $addcurrentSLot_str = strtotime($addcurrentSLot);

        if ($recurring_settings_date == 'yes') {
            $current_recurring_dsiabled = get_option('booking_recurring_disabled');
            if ($current_recurring_dsiabled && is_array($current_recurring_dsiabled)) {
                $current_recurring_dsiabled[date_i18n('l', $addcurrentSLot_str)] = $addcurrentSLot_str;
                update_option('booking_recurring_disabled', $current_recurring_dsiabled);
            } else {
                $current_recurring_dsiabled = array(date_i18n('l', $addcurrentSLot_str) => $addcurrentSLot_str);
                update_option('booking_recurring_disabled', $current_recurring_dsiabled);
            }
        }

        $addcurrentSLot_arr = array($addcurrentSLot => $addcurrentSLot_str);
        $current_settings = get_option('lp_booking_settings');

        if (!$current_settings || empty($current_settings)) {
            update_option('lp_booking_settings', $addcurrentSLot_arr);
        } else {
            $current_settings[$addcurrentSLot] = $addcurrentSLot_str;
            update_option('lp_booking_settings', $current_settings);
        }

        $dash_booking_setting = get_option('lp_booking_settings');
        if (!empty($dash_booking_setting)) {
            foreach ($dash_booking_setting as $key => $val) {
                $dash_booking_setting_Date = $key;
                $addedSlot = '<li>' . '<a class="booking-setting-icon-calander"> <i class="fa fa-calendar" aria-hidden="true"></i></a> &nbsp;' . '<span>' . $dash_booking_setting_Date . '</span>' . '<a class="booking-setting-icon-delete pull-right"><i class="fa fa-trash" aria-hidden="true"></i></a>' . '</li>';
            }
        }
    }
    die(json_encode($addedSlot));
}

/* ******************************  SELECT DURATION OF TIMESLOT  ******************************** */
add_action('wp_ajax_lp_booking_timeSlot_duration', 'lp_booking_timeSlot_duration');
add_action('wp_ajax_nopriv_lp_booking_timeSlot_duration', 'lp_booking_timeSlot_duration');
function lp_booking_timeSlot_duration()
{
    if (isset($_REQUEST)) {
        $selectedSlot = sanitize_text_field($_REQUEST['selectedSlot']);
        update_option('lp_booking_timeslot_duration', $selectedSlot);
    }
    die(json_encode($selectedSlot));
}

add_action('wp_ajax_create_pills_from_calender', 'create_pills_from_calender');
add_action('wp_ajax_nopriv_create_pills_from_calender', 'create_pills_from_calender');
function create_pills_from_calender()
{
    if (isset($_REQUEST)) {
        $dataFullDate = sanitize_text_field($_REQUEST['dataFullDate']);
        $lid = sanitize_text_field($_REQUEST['lid']);
        $dataFullDate_str = strtotime($dataFullDate);

        $today_date     =   date_i18n('d-m-y');
        $selected_date  =   date_i18n('d-m-y', $dataFullDate_str);

        $timeZon_check  =   'no';
        if($today_date == $selected_date) {
            $timeZon_check  =   'yes';
        }

        $dataFullDAy = date_i18n('l', $dataFullDate_str);
        $timeSlotDuration = get_option('lp_booking_timeslot_duration');
        if (!$timeSlotDuration || empty($timeSlotDuration)) {
            $timeSlotDuration = '30';
        }
        $newCurDay = date_i18n('d', $dataFullDate_str);
        $newCurMonth = date_i18n('F', $dataFullDate_str);
        $newCurYear = date_i18n('Y', $dataFullDate_str);
        $newCurDayName = date_i18n('l', $dataFullDate_str);

        $newNextStr = strtotime(date_i18n('Y-m-d', $dataFullDate_str) . " +1 days");
        $newPrevStr = strtotime(date_i18n('Y-m-d', $dataFullDate_str) . " -1 days");

        $times = create_time_range($lid, $dataFullDate_str, $timeSlotDuration . ' mins', 12, $timeZon_check);


        $return = json_encode(
            array(
                $dataFullDAy,
                'timeslots' => $times,
                'newNext' => $newNextStr,
                'newPrev' => $newPrevStr,
                'newcur' => $newCurDay,
                'newCurMonth' => $newCurMonth,
                'newCurYear' => $newCurYear,
                'newCurDayName' => $newCurDayName
            )
        );
    }
    die($return);
}

/* ******************************  DASHBOARD ADD LISTING FOR BOOKING  ******************************** */
add_action('wp_ajax_add_listing_for_booking', 'add_listing_for_booking');
add_action('wp_ajax_nopriv_add_listing_for_booking', 'add_listing_for_booking');
function add_listing_for_booking()
{
    if (isset($_REQUEST)) {
        $SelectedListingID = sanitize_text_field($_REQUEST['SelectedListingID']);

        $addedListing = '';
        if (is_user_logged_in()) {
            $current_user_Id = get_current_user_id();

            $added_listings = get_user_meta($current_user_Id, 'listings_for_bookings', true);

            if (!$added_listings || empty($added_listings)) {
                $new_listing_for_booking = array($SelectedListingID => $SelectedListingID);
                update_user_meta($current_user_Id, 'listings_for_bookings', $new_listing_for_booking);
            } else {
                $added_listings[$SelectedListingID] = $SelectedListingID;
                update_user_meta($current_user_Id, 'listings_for_bookings', $added_listings);
            }

            $addedListing = '<li>' . '<span data-listing-id=' . $SelectedListingID . '>' . get_the_title($SelectedListingID) . '</span>' . '<a class="booking-setting-icon-delete-listing pull-right"><i class="fa fa-trash" aria-hidden="true"></i></a>' . '</li>';
            $addedListingDash = '<li>' . '<span data-listing-id=' . $SelectedListingID . '>' . get_the_title($SelectedListingID) . '</span>' . '<a class="booking-setting-icon-delete-listing dash-booking-remove-icon pull-right"><i class="fa fa-times"></i></a>' . '</li>';

        }
    }
    $return = json_encode(
        array(
            'addedListing' => $addedListing,
            'AddedListingDash' => $addedListingDash,
        )
    );
    die($return);
}

add_action('wp_ajax_del_listing_for_booking', 'del_listing_for_booking');
add_action('wp_ajax_nopriv_del_listing_for_booking', 'del_listing_for_booking');
function del_listing_for_booking()
{
    if (isset($_REQUEST)) {
        $currentListingID = sanitize_text_field($_REQUEST['currentListingID']);
        $current_user_Id = get_current_user_id();
        $listing_booking_delete_slot = get_user_meta($current_user_Id, 'listings_for_bookings', true);
        if ($listing_booking_delete_slot[$currentListingID] = $currentListingID) {
            unset($listing_booking_delete_slot[$currentListingID]);
            update_user_meta($current_user_Id, 'listings_for_bookings', $listing_booking_delete_slot);
        }
        $del_listing_booking_slot = get_user_meta($current_user_Id, 'listings_for_bookings', true);
    }
    $return = json_encode(
        array(
            '$listing_booking_delete_slot' => $listing_booking_delete_slot,
            'del_listing_booking_slot' => $del_listing_booking_slot,
        )
    );
    die($return);
}

if(!function_exists('lp_booking_get_email_content')) {
    function lp_booking_get_email_content($booking_id, $booking_action, $mail_for) {
        ob_start();
        ?>
        <?php
        $listing_id         =   get_post_meta($booking_id, 'lp_listing_id', true);


        $listing_title      =   get_the_title($listing_id);
        $listing_perma      =   @get_permalink($listing_id);

        $booking_date       =   date_i18n(get_option('date_format'), get_post_meta($booking_id, 'lp_booking_date', true) );
        $booking_start      =   date_i18n(get_option('time_format'), get_post_meta($booking_id, 'lp_booking_start_time', true) );
        $booking_ends       =   date_i18n(get_option('time_format'), get_post_meta($booking_id, 'lp_booking_end_time', true) );
        $booking_status     =   get_post_meta($booking_id, 'lp_booking_status', true);

        if($booking_action == 'create' && $mail_for == 'booker') {
            ?>
            <p><strong><?php echo esc_html__('You have created a new Appointment','listingpro-bookings'); ?></strong></p>
            <?php
        }
        if($booking_action == 'approved' && $mail_for == 'booker') {
            ?>
            <p><strong><?php echo esc_html__('Your Appointment has been approved','listingpro-bookings'); ?></strong></p>
            <?php
        }
        if($booking_action == 'canceled' && $mail_for == 'booker') {
            ?>
            <p><strong><?php echo esc_html__('Your Appointment has been canceled','listingpro-bookings'); ?></strong></p>
            <?php
        }
        if($booking_action == 'create' && $mail_for == 'listing_author') {
            ?>
            <p><strong><?php echo esc_html__('You have received a new Appointment','listingpro-bookings'); ?></strong></p>
            <?php
        }
        ?>
        <h3><?php echo esc_html__('Appointment Details','listingpro-bookings'); ?></h3>
        <p><strong><?php echo esc_html__('Listing Title:','listingpro-bookings'); ?></strong> <a href="<?php echo $listing_perma; ?>"><?php echo $listing_title; ?></a></p>
        <p><strong><?php echo esc_html__('Appointment Date:','listingpro-bookings'); ?></strong> <?php echo $booking_date; ?></p>
        <p><strong><?php echo esc_html__('Appointment Time:','listingpro-bookings'); ?></strong> <?php echo $booking_start.' - '.$booking_ends; ?></p>
        <p><strong><?php echo esc_html__('Appointment Status:','listingpro-bookings'); ?></strong> <?php echo listingpro_booking_status_label($booking_status) ?></p>
        <?php
        if($booking_action == 'create' && $mail_for == 'booker') {
            ?>
            <p><?php echo esc_html__('you will be notified if your Appointment is APPROVED or CANCELED.','listingpro-bookings'); ?></p>
            <?php
        }
        return ob_get_clean();
    }
}

function listingpro_booking_load_textdomain() {
    load_plugin_textdomain( 'listingpro-bookings', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'listingpro_booking_load_textdomain' );

if(!function_exists('listingpro_booking_status_label')){
    function listingpro_booking_status_label( $status = '' ) {
        $statuses_arr = array(
            'PENDING' => esc_html__('PENDING','listingpro-bookings'),
            'APPROVED' => esc_html__('APPROVED','listingpro-bookings'),
            'CANCELED' => esc_html__('CANCELED','listingpro-bookings'),
        );
        
        return isset($statuses_arr[$status]) ? $statuses_arr[$status] : $status;
    }
}