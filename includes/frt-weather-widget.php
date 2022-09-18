<?php
namespace FRTWP;

class Widget extends \WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'frtwp-widget',
            __('FRT Weather Widget', 'frt-weather-plugin')
        );

        add_action('widgets_init', function () {
            register_widget('\FRTWP\Widget');
        });
        
        add_action('wp_enqueue_scripts', array($this, 'FRTWPEnqueueScript'));
        add_action('wp_ajax_FRTWPGetUserCoordinates', array($this, 'FRTWPGetUserCoordinates'));
        add_action('wp_ajax_nopriv_FRTWPGetUserCoordinates', array($this, 'FRTWPGetUserCoordinates'));
    }

    public function widget($args, $instance)
    {
        $options = get_option('frtwp_options');
        $units = $options['frtwp_temperature_unit_field'] === 'C' ? 'metric' : 'imperial';
        
        $latitude = isset($_COOKIE['user_latitude']) ? $_COOKIE['user_latitude'] : '';
        $longitude = isset($_COOKIE['user_longitude']) ? $_COOKIE['user_longitude'] : '';

        $response = wp_remote_get(
            'https://api.openweathermap.org/data/2.5/weather?lat='
            . $latitude . '&lon=' . $longitude . '&appid='
            . $options['frtwp_api_key_field'] . '&units=' . $units
        );

        $http_code = wp_remote_retrieve_response_code($response);
        
        if ($http_code === 200) {
            $body = wp_remote_retrieve_body($response);
            $res = json_decode($body);
        } else {
            return;
        }

        $backgroundColor = apply_filters('background_color', $instance['backgroundColor']);
        $textColor = apply_filters('text_color', $instance['textColor']);
        $border = apply_filters('border', $instance['border']);
        $borderRadius = apply_filters('border_radius', $instance['borderRadius']);
        ?>

        <div
            style="padding: 10px;
                    background-color: <?php echo $backgroundColor; ?>;
                    color: <?php echo $textColor; ?>;
                    border: <?php echo $border; ?>;
                    border-radius: <?php echo $borderRadius; ?>">
            <?php if (isset($options['frtwp_weather_condition_field'])) : ?>
            <div>
                <img
                src="<?php echo 'https://openweathermap.org/img/wn/' . $res->weather[0]->icon . '@2x.png' ?>"
                alt="<?php echo $res->weather[0]->description; ?>">
            </div>
            <?php endif; ?>

            <p>Location: <?php echo $res->name . ', ' . $res->sys->country; ?></p>

            <p>Weather: <?php echo $res->weather[0]->main; ?></p>

            <?php if (isset($options['frtwp_temperature_field'])) : ?>
                <p>Temperature: <?php echo $res->main->temp; ?>
                    <?php if (isset($options['frtwp_temperature_scale_field'])) : ?>
                        <span>
                            <?php echo $options['frtwp_temperature_unit_field']; ?>
                        </span>
                    <?php endif; ?>
                </p>
            <?php endif; ?>

            <?php if (isset($options['frtwp_pressure_field'])) : ?>
                <p>Pressure: <?php echo $res->main->pressure; ?></p>
            <?php endif; ?>
            
            <?php if (isset($options['frtwp_wind_field'])) : ?>
                <p>
                    Wind speed:
                    <?php
                        echo $res->wind->speed . ($options['frtwp_temperature_unit_field'] === 'C' ? 'm/s' : 'mph');
                    ?>
                </p>
                <p>Wind direction: <?php echo $res->wind->deg; ?></p>
            <?php endif; ?>

            <?php if (isset($options['frtwp_visibility_field'])) : ?>
                <p>Visibility: <?php echo $res->visibility; ?>m</p>
            <?php endif; ?>

            <?php if (isset($options['frtwp_sunrise_field'])) : ?>
                <p>Sunrise: <?php echo gmdate("Y-m-d\TH:i:s\Z", $res->sys->sunrise); ?></p>
            <?php endif; ?>

            <?php if (isset($options['frtwp_sunset_field'])) : ?>
                <p>Sunset: <?php echo gmdate("Y-m-d\TH:i:s\Z", $res->sys->sunset); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    public function form($instance)
    {
        $backgroundColor = isset($instance['backgroundColor'])
        ? $instance['backgroundColor']
        : __('Background color', 'frt-weather-plugin');
        
        $textColor = isset($instance['textColor'])
        ? $instance['textColor']
        : __('Text color', 'frt-weather-plugin');

        $border = isset($instance['border'])
        ? $instance['border']
        : __('Widget border', 'frt-weather-plugin');

        $borderRadius = isset($instance['borderRadius'])
        ? $instance['borderRadius']
        : __('Widget border radius', 'frt-weather-plugin');
        ?>
        <p>
            <?php _e('Configure widget styling. All inputs allow standard CSS values.') ?>
        </p>
        <p>
            <label for="<?php echo $this->get_field_name('backgroundColor'); ?>">
                <?php _e('Background color', 'frt-weather-plugin'); ?>
            </label>
            <input
                type="text"
                id="<?php echo $this->get_field_id('backgroundColor'); ?>"
                name="<?php echo $this->get_field_name('backgroundColor'); ?>"
                value="<?php echo esc_attr($backgroundColor); ?>" />

        </p>
        <p>
            <label for="<?php echo $this->get_field_name('textColor'); ?>">
                <?php _e('Text color', 'frt-weather-plugin'); ?>
            </label>
            <input
                type="text"
                id="<?php echo $this->get_field_id('textColor'); ?>"
                name="<?php echo $this->get_field_name('textColor'); ?>"
                value="<?php echo esc_attr($textColor); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_name('border'); ?>">
                <?php _e('Border', 'frt-weather-plugin'); ?>
            </label>
            <input
                type="text"
                id="<?php echo $this->get_field_id('border'); ?>"
                name="<?php echo $this->get_field_name('border'); ?>"
                value="<?php echo esc_attr($border); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_name('borderRadius'); ?>">
                <?php _e('Border radius', 'frt-weather-plugin'); ?>
            </label>
            <input
                type="text"
                id="<?php echo $this->get_field_id('borderRadius'); ?>"
                name="<?php echo $this->get_field_name('borderRadius'); ?>"
                value="<?php echo esc_attr($borderRadius); ?>" />
        </p>
        <?php
    }

    public function FRTWPEnqueueScript()
    {
        wp_enqueue_script('frtwp-script', plugin_dir_url(__FILE__) . '/script.js', array('jquery'));
        wp_localize_script(
            'frtwp-script',
            'frtwp_ajax_object',
            array(
                'ajaxurl' => admin_url('admin-ajax.php')
            )
        );
    }

    public function FRTWPGetUserCoordinates()
    {
        if (isset($_REQUEST)) {
            $coordinates = $_REQUEST['coordinates'];
            $latitude = $coordinates['latitude'];
            $longitude = $coordinates['longitude'];

            setcookie('user_latitude', $latitude, time() + (86400 * 30), "/");
            setcookie('user_longitude', $longitude, time() + (86400 * 30), "/");
        }

        wp_die();
    }
}
