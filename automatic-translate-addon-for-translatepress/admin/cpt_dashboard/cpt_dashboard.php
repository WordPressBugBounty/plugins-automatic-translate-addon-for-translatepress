<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('Tpa_Dashboard')){
    class Tpa_Dashboard{

        /**
         * Init
         * @var object
         */
        private static $init;

        /**
         * Tabs data
         * @var array
         */
        private $tabs_data=array();

        /**
         * Instance
         * @return object
         */
        public static function instance(){
            if(!isset(self::$init)){
                self::$init = new self();
            }
            return self::$init;
        }

        public function __construct(){
            add_action('wp_ajax_tpa_hide_review_notice', array($this, 'tpa_hide_review_notice'));
        }

        /**
         * Sort column data
         * @param array $columns
         * @param array $value
         * @return array
         */
        public function sort_column_data($columns, $value){
            $result = array();
            foreach($columns as $key => $label) {
                $result[$key] = isset($value[$key]) ? sanitize_text_field($value[$key]) : '';
            }
            return $result;
        }

        /**
         * Store options
         * @param string $plugin_name
         * @param string $prefix
         * @param array $data
         * @return void
         */

        public static function store_options($prefix='', $unique_key='', $old_data='update', array $data = array()){
            if(!empty($prefix) && isset($data['string_count']) && isset($data['character_count'])){
                $prefix = sanitize_key($prefix);
                $all_data = get_option('cpt_dashboard_data', array());

                $data['time_taken'] = isset( $data['time_taken'] ) ? absint( $data['time_taken'] ) : 0;
                $data['string_count'] = absint( $data['string_count'] );
                $data['character_count'] = absint( $data['character_count'] );
                
                if(isset($all_data[$prefix])){
                    $data_update = false;
                    foreach($all_data[$prefix] as $key => $translate_data){
                        if(!empty($unique_key) && isset($translate_data[$unique_key]) && 
                        sanitize_text_field($translate_data[$unique_key]) === sanitize_text_field($data[$unique_key]) && 
                        sanitize_text_field($translate_data['service_provider']) === sanitize_text_field($data['service_provider']) &&
                        sanitize_text_field($translate_data['target_language']) === sanitize_text_field($data['target_language']) &&
                        sanitize_text_field($translate_data['source_language']) === sanitize_text_field($data['source_language'])
                        ){
                            
                            if($old_data=='update'){
                                $data['string_count'] = absint($data['string_count']) + absint($translate_data['string_count'] ?? 0);
                                $data['character_count'] = absint($data['character_count']) + absint($translate_data['character_count'] ?? 0);
                                $data['time_taken'] = absint($data['time_taken']) + absint($translate_data['time_taken'] ?? 0);
                            }
                            
                            foreach($data as $id => $value){
                                $all_data[$prefix][$key][sanitize_key($id)] = sanitize_text_field($value);
                            }
                            $data_update = true;
                        }
                    }

                    if(!$data_update){
                        $all_data[$prefix][] = array_map('sanitize_text_field', $data);
                    }
                }else{
                    $all_data[$prefix][] = array_map('sanitize_text_field', $data);
                }

                update_option('cpt_dashboard_data', $all_data);
            }
        }

        /**
         * Get translation data
         * @param string $prefix
         * @return array
         */
        public static function get_translation_data($prefix, $key_exists=array()){
            $prefix = sanitize_key($prefix);
            $all_data = get_option('cpt_dashboard_data', array());
            $data = array();

            if(isset($all_data[$prefix])){
                $total_string_count = 0;
                $total_character_count = 0;

                foreach($all_data[$prefix] as $key => $value){

                    $continue=false;
                    foreach($key_exists as $key_exists_key => $key_exists_value){
                        if(!isset($value[$key_exists_key]) || (isset($value[$key_exists_key]) && $value[$key_exists_key] !== $key_exists_value)){
                            $continue=true;
                            break;
                        }
                    }

                    if($continue){
                        continue;
                    }

                    $total_string_count += isset($value['string_count']) ? absint($value['string_count']) : 0;
                    $total_character_count += isset($value['character_count']) ? absint($value['character_count']) : 0;
                }

                $data = array(
                    'prefix' => $prefix,
                    'data' => array_map(function($item) {
                        return array_map('sanitize_text_field', $item);
                    }, $all_data[$prefix]),
                    'total_string_count' => $total_string_count,
                    'total_character_count' => $total_character_count,
                );
            }else{
                $data = array(
                    'prefix' => $prefix,
                    'total_string_count' => 0,
                    'total_character_count' => 0,
                );
            }

            return $data;
        }

        public static function ctp_enqueue_assets(){
            if(function_exists('wp_style_is') && !wp_style_is('tpa-review-style', 'enqueued')){
                $plugin_url = plugin_dir_url(__FILE__);
                wp_enqueue_style('tpa-review-style', esc_url($plugin_url.'assets/css/cpt-dashboard.css'), array(), '1.0.0', 'all');
                wp_enqueue_script('tpa-review-script', esc_url($plugin_url.'assets/js/cpt-dashboard.js'), array('jquery'), '1.0.0', true);
            }
        }

        public static function format_number_count($number){
            if ($number >= 1000000) {
                return round($number / 1000000, 1) . 'M';
            } elseif ($number >= 1000) {
                return round($number / 1000, 1) . 'K';
            }
            return $number;
        }

        public static function review_notice($prefix, $plugin_name, $url){
            if(self::tpa_hide_review_notice_status($prefix)){
                return;
            }
            
            $translation_data = self::get_translation_data($prefix);
            
            $total_character_count = is_array($translation_data) && isset($translation_data['total_character_count']) ? $translation_data['total_character_count'] : 0;
            
            if($total_character_count < 50000){ 
                return;
            }

            $total_character_count = self::format_number_count($total_character_count);

            add_action('admin_enqueue_scripts', array(self::class, 'ctp_enqueue_assets'));

            $cool_plugins_url = esc_url('https://coolplugins.net/');
            $message = sprintf(
                /* translators: %1$s: Plugin name, %2$s: Number of characters translated, %3$s: Cool Plugins URL */
                __('Thanks for using <b>%1$s</b>! You have translated <b>%2$s</b> characters so far using our plugin!<br>Please give us a quick rating, it works as a boost for us to keep working on more <a style="text-decoration: none;" href="%3$s" target="_blank" rel="noopener noreferrer"><b>Cool Plugins</b></a>!', 'automatic-translate-addon-for-translatepress'),
                $plugin_name,
                $total_character_count,
                $cool_plugins_url
            );

            $prefix = sanitize_key($prefix);
            $message = wp_kses_post($message);
            $url = esc_url($url);

            add_action('tpa_display_admin_notices', function() use ($message, $prefix, $url){

                $html= '<div class="notice notice-info is-dismissible cpt-review-notice">';
                
                $html .= '<div class="cpt-review-notice-content"><p>'.wp_kses_post($message).'</p><div class="tpa-review-notice-dismiss" data-prefix="'.esc_attr($prefix).'" data-nonce="'.esc_attr(wp_create_nonce('tpa_hide_review_notice')).'"><a href="'.esc_url($url).'" target="_blank" rel="noopener noreferrer" class="button button-primary">Rate Now! ★★★★★</a><button class="button cpt-already-reviewed">'.esc_html__('Already Reviewed', 'automatic-translate-addon-for-translatepress').'</button><button class="button cpt-not-interested">'.esc_html__('Not Interested', 'automatic-translate-addon-for-translatepress').'</button></div></div></div>';
                
                echo wp_kses_post($html);
            });

        }

        public static function tpa_hide_review_notice_status($prefix){
            $prefix = sanitize_key( $prefix );
            $review_notice_dismissed = get_option( 'cpt_review_notice_dismissed', array() );
            if ( ! is_array( $review_notice_dismissed ) || ! isset( $review_notice_dismissed[ $prefix ] ) ) {
                return false;
            }
            return absint( $review_notice_dismissed[ $prefix ] ) === 1;
        }

        public function tpa_hide_review_notice(){

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( esc_html__( 'Insufficient permissions to dismiss notices. Administrator access required.', 'automatic-translate-addon-for-translatepress' ) );
                return;
            }
            check_ajax_referer( 'tpa_hide_review_notice', 'nonce' );
            $prefix = isset( $_POST['prefix'] ) ? sanitize_key( wp_unslash( $_POST['prefix'] ) ) : 'tpa';
            if ( '' === $prefix ) {
                $prefix = 'tpa';
            }
            $review_notice_dismissed = get_option( 'cpt_review_notice_dismissed', array() );
            if ( ! is_array( $review_notice_dismissed ) ) {
                $review_notice_dismissed = array();
            }
            $review_notice_dismissed[ $prefix ] = 1;
            update_option( 'cpt_review_notice_dismissed', $review_notice_dismissed );
            wp_send_json_success();
        }
    }
}
     