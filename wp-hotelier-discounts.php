<?php
/**
 * Plugin Name: WP Hotelier Discounts
 * Description: Agrega descuentos por número de noches y por cantidad de personas en WP Hotelier.
 * Version: 1.2
 * Author: Jeyson Diaz
 */

if (!defined('ABSPATH')) {
    exit; // Seguridad
}

// Agregar menú en el administrador
function whd_add_admin_menu() {
    add_menu_page('Descuentos WP Hotelier', 'Descuentos Hotel', 'manage_options', 'whd-settings', 'whd_settings_page');
}
add_action('admin_menu', 'whd_add_admin_menu');

// Página de configuración
function whd_settings_page() {
    ?>
    <div class="wrap">
        <h1>Configuración de Descuentos</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('whd_settings_group');
            do_settings_sections('whd-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Registrar ajustes
function whd_register_settings() {
    register_setting('whd_settings_group', 'whd_discounts_guests');
    register_setting('whd_settings_group', 'whd_discounts_nights');
    add_settings_section('whd_section', 'Configurar descuentos', null, 'whd-settings');
    add_settings_field('whd_discounts_guests_field', 'Descuentos por número de personas', 'whd_discounts_guests_field_callback', 'whd-settings', 'whd_section');
    add_settings_field('whd_discounts_nights_field', 'Descuentos por número de noches', 'whd_discounts_nights_field_callback', 'whd-settings', 'whd_section');
}
add_action('admin_init', 'whd_register_settings');

function whd_discounts_guests_field_callback() {
    $value = get_option('whd_discounts_guests', []);
    echo '<table>'; 
    for ($i = 1; $i <= 4; $i++) {
        echo '<tr><td>Personas ' . $i . ':</td><td><input type="number" name="whd_discounts_guests[' . $i . ']" value="' . esc_attr($value[$i] ?? '') . '" min="0" max="100"> %</td></tr>';
    }
    echo '</table>';
}

function whd_discounts_nights_field_callback() {
    $value = get_option('whd_discounts_nights', []);
    echo '<table>'; 
    for ($i = 3; $i <= 5; $i++) {
        echo '<tr><td>Noches ' . $i . ':</td><td><input type="number" name="whd_discounts_nights[' . $i . ']" value="' . esc_attr($value[$i] ?? '') . '" min="0" max="100"> %</td></tr>';
    }
    echo '</table>';
}

// Aplicar descuentos
add_filter('hotelier_booking_price', function($price, $booking) {
    $guests = $booking->get_adults();
    $nights = $booking->get_nights();
    
    $discounts_guests = get_option('whd_discounts_guests', []);
    $discounts_nights = get_option('whd_discounts_nights', []);
    
    if (isset($discounts_guests[$guests])) {
        $price *= (1 - $discounts_guests[$guests] / 100);
    }
    if (isset($discounts_nights[$nights])) {
        $price *= (1 - $discounts_nights[$nights] / 100);
    }
    
    return $price;
}, 10, 2);
