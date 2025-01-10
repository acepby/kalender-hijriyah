<?php
/*
Plugin Name: Kalender Hijriyah
Description: A Hijri calendar plugin for displaying Islamic dates with additional features.
Version: 1.0
Author: acepby
Author URI: https://r3volt.xyz
License: GPL2
*/

// Prevent direct access to the plugin file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include the year data
global $data;
include 'tahun.php';

// Enqueue necessary scripts and styles
function khgt_kalender_hijriyah_enqueue_scripts() {
    wp_enqueue_script('html2pdf', 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js', array(), null, true);
    wp_enqueue_style('kalender-hijriyah-style', plugin_dir_url(__FILE__) . 'style.css');
}
add_action('wp_enqueue_scripts', 'khgt_kalender_hijriyah_enqueue_scripts');

// Shortcode to display the Hijri Calendar
function kalender_hijriyah_shortcode($atts) {
    ob_start();
    global $data;
    $year_sc = $atts[0];

    // Default year selection
    if (isset($year_sc)) {
        $selected_year = $year_sc;
    } else {
        $selected_year = 1446; // Default year is current
    }

    $start_pasaran_offset = 4; // Start offset for the first year

    // Check if previous year is selected to get correct offset
    
    if ($selected_year > 1446) {
        $prev_year = $selected_year - 1;
        $last_month_prev_year = end($data[$prev_year]);
        $start_pasaran_offset = displayMonth($last_month_prev_year, $start_pasaran_offset, $prev_year);
    }
    
    // Display the selected year’s calendar
    foreach ($data[$selected_year] as $month) {
        $start_pasaran_offset = displayMonth($month, $start_pasaran_offset, $selected_year);
    }

    return ob_get_clean();
}
add_shortcode('khgt_calendar', 'kalender_hijriyah_shortcode');

// Function to display a single month in the Hijri Calendar
function displayMonth($month_data, $start_pasaran_offset, $year) {
    // Month details and calculation
    $month_name = $month_data[0];
    $start_day_name = $month_data[1];
    $start_date = $month_data[2];
    $start_day = DateTime::createFromFormat('d-M-y', $start_date)->format('w');
    $days_in_month = $month_data[3];
    $year_hijriyah = $year;
    $current_date = DateTime::createFromFormat('d-M-y', $start_date);
    $days_of_week = ['Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    
   
    echo '<div id="' . strtolower($month_name) . '-container" class="calendar-container">';
    echo '<h2 onclick="printMonth(\'' . strtolower($month_name) . '-container\')" title="Klik untuk print!"><span style="font-size: 2.1em; cursor: pointer;">' . $month_name . ' ' . $year_hijriyah . ' H</span></h2>';
    
    echo '<div class="calendar">'; 

    // Start creating the month grid
    echo '<div class="calendar-grid">';
    
    foreach ($days_of_week as $day) {
        echo '<div class="day-header">' . $day . '</div>';
    }

    // Empty cells for leading days of the month
    for ($i = 0; $i < $start_day; $i++) {
        echo '<div class="day-cell"></div>';
    }

    // Loop through the days of the month
    $current_date_masehi = date('d-M-Y');
    for ($day = 1; $day <= $days_in_month; $day++) {
        $hijri_date = convertHijriyahToMasehi($month_name, $day, $year);
        $pasaran = getPasaranJawa($start_pasaran_offset, $day - 1);
        $day_of_week = ($day + $start_day - 1) % 7;
        $style = 'day-cell';
        $tooltip = getTooltip($month_name, $day, $year);

        // Add styling and tooltip
        if ($hijri_date == $current_date_masehi) {
            $style .= ' today';
        } elseif ($tooltip) {
            $style .= ' tooltip';
        } elseif ($day_of_week == 0) {
            $style .= ' sunday';
        } elseif ($day_of_week == 5) {
            $style .= ' friday';
        }

        $arabic_day = convertToArabicNumbers($day);

        echo '<div class="' . $style . '" title="' . $tooltip . '">';
        echo '<span style="font-size: 1.5em;">' . $arabic_day . '</span><br>';
        echo '<span style="font-size: 0.9em;">' . $pasaran . '<br>' . $hijri_date . '</span></div>';
    }

    // Empty cells for trailing days of the month to ensure 35 cells
    $total_cells = $start_day + $days_in_month;
    $empty_cells = 35 - $total_cells;
    for ($i = 0; $i < $empty_cells; $i++) {
        echo '<div class="day-cell"></div>';
    }

    echo '</div>'; // Close calendar-grid
    echo '</div>'; // Close calendar
    echo '</div>'; // Close calendar-container
   

    return ($start_pasaran_offset + $days_in_month) % 5;
}

// Helper functions (convertHijriyahToMasehi, getPasaranJawa, etc.) remain the same
function convertHijriyahToMasehi($bulan, $hari_ke, $year) {
    global $data;
    $date_start = null;
    $days_in_month = 0;

    if (!isset($data[$year])) {
        return "Data untuk tahun $year tidak ditemukan.";
    }

    foreach ($data[$year] as $d) {
        if ($d[0] == $bulan) {
            $date_start = DateTime::createFromFormat('d-M-y', $d[2]);
            $days_in_month = $d[3];
            break;
        }
    }

    if ($date_start === null) {
        return "Bulan tidak ditemukan.";
    }

    $interval = new DateInterval('P' . ($hari_ke - 1) . 'D');
    $date_start->add($interval);

    return $date_start->format('d/m/Y');
}

function getPasaranJawa($start_index, $offset) {
    $pasaran = ["Legi", "Pahing", "Pon", "Wage", "Kliwon"];
    $index = ($start_index + $offset) % 5;
    return $pasaran[$index];
}

function convertToArabicNumbers($number) {
    $arabic_numbers = ['0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤', '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩'];
    return strtr($number, $arabic_numbers);
}

function getTooltip($bulan, $day, $year) {
    $tooltip = '';
    if ($bulan == 'Muharram' && $day == 1 && $year == 1446) {
        $tooltip = 'Hari Tahun baru Islam 1446 H = L/B: 40/120, Tinggi hilal/elongasi: 6,85°/8°'; 
    } elseif (($day == 29 OR $day == 30)  && $bulan == 'Muharram') {
        $tooltip = 'New Moon 2024-08-04 18:13:39 WIB';     
    } elseif (($day == 29 OR $day == 30) && $bulan == 'Syakban' && $year == 1446) {
        $tooltip = 'New Moon Riyadh, 025-02-28 21:55:02 WIB. Tinggi hilal: 6.33°, elongasi: 8.11°';
    } elseif (in_array($day, [13, 14, 15])) {
        $tooltip = 'Ayyamul bidh';
    } elseif ($bulan == 'Muharram' && $day == 9) {
        $tooltip = 'Hari Tasua';
    } elseif ($bulan == 'Muharram' && $day == 10) {
        $tooltip = 'Hari Asyuro';
    } elseif ($bulan == 'Safar' && $day == 1 && $year == 1446) {
        $tooltip = 'Awal bulan Safar, Denver (UTC-6,L:39.7392,B:-104.9903), 2024-08-05 02:09:01 UTC, H:5.32°,  E:8.03°'; 
    } elseif ($day == 30  && $bulan == 'Safar' && $year == 1446) {
        $tooltip = 'New Moon 2024-09-03 08:56:12 WIB';   
    } elseif ($bulan == 'Rabiulawal' && $day == 1 && $year == 1446) {
        $tooltip = 'Awal bulan Rabiulawal,  Dakar (UTC,L: 14.6928, B:-17.4467), 2024-09-03 19:19:52 UTC, H:5.66°, E:8.08°';         
    } elseif ($bulan == 'Rabiulawal' && $day == 12) {
        $tooltip = 'Hari Maulid Nabi';
    } elseif ($bulan == 'Rabiulawal' && $day == 30) {
        $tooltip = 'New Moon, 2024-10-03 01:49:54 WIB';  
    } elseif ($bulan == 'Rabiulakhir' && $day == 1 && $year == 1446) {
        $tooltip = 'Awal bulan Rabiulakhir, Mogadishu (UTC+3, L:2.0469, B:45.3182), 2024-10-03 14:50:15 UTC, H:6.05°, E:9.09°';        
    } elseif ($bulan == 'Rabiulakhir' && $day == 29) {
        $tooltip = 'New Moon, 2024-11-01 19:47:48 WIB'; 
        
    } elseif ($bulan == 'Rajab' && $day == 27) {
        $tooltip = 'Hari Isra Mi\'raj';
    } elseif ($bulan == 'Syakban' && $day == 15) {
        $tooltip = 'Hari Nisyfu Syaban';
    } elseif ($bulan == 'Ramadan' && $day == 1) {
        $tooltip = 'Awal Ramadhan';
    } elseif ($bulan == 'Ramadan' && $day == 17) {
        $tooltip = 'Hari Nuzulul Quran';
    } elseif (($day == 29 OR $day == 30) && $bulan == 'Ramadan' && $year == 1446) {
        $tooltip = 'New Moon Riyadh, 025-02-28 21:55:02 WIB. Tinggi hilal: 6.33°, elongasi: 8.11°';        
    } elseif ($bulan == 'Syawal' && $day == 1) {
        $tooltip = 'Hari Idul Fitri';
    } elseif ($bulan == 'Dzulhijjah' && $day == 9) {
        $tooltip = 'Hari Puasa Arafah';
    } elseif ($bulan == 'Dzulhijjah' && $day == 10) {
        $tooltip = 'Hari Idul Adha';
    } elseif ($bulan == 'Dzulhijjah' && in_array($day, [11, 12, 13])) {
        $tooltip = 'Hari Tasyrik';
    }
    return $tooltip;
}

?>
