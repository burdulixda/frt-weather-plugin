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
    }

    public function widget($args, $instance)
    {
        $options = get_option('frtwp_options');
        $units = $options['frtwp_temperature_unit_field'] === 'C' ? 'metric' : 'imperial';

        $response = wp_remote_get(
            'https://api.openweathermap.org/data/2.5/weather?lat=41.715137&lon=44.827095&appid='
            . $options['frtwp_api_key_field'] . '&units=' . $units
        );

        $http_code = wp_remote_retrieve_response_code($response);
        
        if ($http_code === 200) {
            $body = wp_remote_retrieve_body($response);
            $res = json_decode($body);
        } else {
            return;
        }
        ?>

        <div>
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
                <p>Temperature: <?php echo $res->main->temp . $options['frtwp_temperature_unit_field']; ?></p>
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
}
