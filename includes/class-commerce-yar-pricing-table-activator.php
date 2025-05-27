<?php
class Commerce_Yar_Pricing_Table_Activator {
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'commerce_yar_token';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            pricing_type varchar(20) NOT NULL,
            title varchar(255) NOT NULL,
            price decimal(10,2) NOT NULL,
            features text NOT NULL,
            button_text varchar(255) NOT NULL,
            button_link text NOT NULL,
            price_code varchar(50) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Add default pricing data if table is empty
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        if ($count == 0) {
            $default_data = array(
                array(
                    'pricing_type' => 'monthly',
                    'title' => 'پلن پایه',
                    'price' => '100000',
                    'features' => "ویژگی 1\nویژگی 2\nویژگی 3",
                    'button_text' => 'خرید',
                    'button_link' => '#',
                    'price_code' => 'BASIC_M'
                ),
                array(
                    'pricing_type' => 'quarterly',
                    'title' => 'پلن سه ماهه',
                    'price' => '270000',
                    'features' => "ویژگی 1\nویژگی 2\nویژگی 3",
                    'button_text' => 'خرید',
                    'button_link' => '#',
                    'price_code' => 'BASIC_Q'
                ),
                array(
                    'pricing_type' => 'biannual',
                    'title' => 'پلن شش ماهه',
                    'price' => '500000',
                    'features' => "ویژگی 1\nویژگی 2\nویژگی 3",
                    'button_text' => 'خرید',
                    'button_link' => '#',
                    'price_code' => 'BASIC_B'
                ),
                array(
                    'pricing_type' => 'yearly',
                    'title' => 'پلن سالانه',
                    'price' => '900000',
                    'features' => "ویژگی 1\nویژگی 2\nویژگی 3",
                    'button_text' => 'خرید',
                    'button_link' => '#',
                    'price_code' => 'BASIC_Y'
                )
            );

            foreach ($default_data as $data) {
                $wpdb->insert($table_name, $data);
            }
        }
    }
} 