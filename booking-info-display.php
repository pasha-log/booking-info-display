<?php
/*
Plugin Name: Booking Info Display
Plugin URI: N/A
Description: This plugin displays booking information from the booking.personal.9.4.1 plugin.
Version: 1.0
Author: Pasha Loguinov
Author URI: http://ewebsiteservices.com/
*/

function get_booking_info() {
    global $wpdb;
    $booking_table = $wpdb->prefix . 'booking'; // use the booking table
    $bookingdates_table = $wpdb->prefix . 'bookingdates'; // use the bookingdates table    
    
    $sql = "
    SELECT 
        booking.booking_id, 
        MIN(booking.sort_date) as startDate, 
        booking.form, 
        MAX(bookingdates.booking_date) as endDate
    FROM 
        $booking_table AS booking
    INNER JOIN 
        $bookingdates_table AS bookingdates ON booking.booking_id = bookingdates.booking_id
    WHERE 
        booking.booking_type = 3
        AND YEAR(booking.sort_date) = YEAR(CURDATE())
        AND bookingdates.approved = 1
        AND booking.booking_id != 125
    GROUP BY
        booking.booking_id
    ORDER BY 
        startDate
    ";

    
    // Get the bookings
    $bookings = $wpdb->get_results($sql);
    
    return $bookings;
}

function display_booking_info() {

    $bookings = get_booking_info();
    $output .= '<table class="booking-info-table">';
    // Add table headers
    $output .= '<tr><th>Booking ID</th><th>Start Date</th><th>End Date</th><th>Name</th></tr>';
    // Add table rows for each booking
    foreach ($bookings as $booking) {
        // Extract the first name and last name from the form data
        preg_match('/name3\^([^\~]*)/', $booking->form, $firstNameMatches);
        preg_match('/secondname3\^([^\~]*)/', $booking->form, $lastNameMatches);
        $firstName = $firstNameMatches[1] ?? '';
        $lastName = $lastNameMatches[1] ?? '';
        $fullName = $firstName . ' ' . $lastName;

        // Format the start date and end date
        $startDate = date('F j, Y', strtotime($booking->startDate));
        $endDate = date('F j, Y', strtotime($booking->endDate));

        $output .= '<tr>';
        $output .= '<td>' . $booking->booking_id . '</td>';
        $output .= '<td>' . $startDate . '</td>';
        $output .= '<td>' . $endDate . '</td>';
        $output .= '<td>' . $fullName . '</td>';
        $output .= '</tr>';
    }
    $output .= '</table>';
    return $output;
}

add_shortcode('display_booking_info', 'display_booking_info');

function enqueue_plugin_styles() {
    wp_enqueue_style('booking-info-display-styles', plugin_dir_url(__FILE__) . 'css/style.css');
}
add_action('wp_enqueue_scripts', 'enqueue_plugin_styles');
?>