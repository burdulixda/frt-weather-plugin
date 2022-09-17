<?php

namespace FRTWP;

class Settings
{
    public function __construct()
    {
        add_action('admin_init', array($this, 'FRTWPSettingsInit'));
        add_action('admin_menu', array($this, 'FRTWPOptionsPage'));
    }

    public function FRTWPSettingsInit()
    {
        register_setting('frtwp', 'frtwp_options');
        
        add_settings_section(
            'frtwp_section_general',
            __('General', 'frt-weather-plugin'),
            array($this, 'FRTWPSectionGeneralCallback'),
            'frtwp'
        );
        
        add_settings_field(
            'frtwp_api_key_field',
            __('OpenWeather API Key', 'frt-weather-plugin'),
            array($this, 'FRTWPApiKeyFieldCallback'),
            'frtwp',
            'frtwp_section_general',
            array(
                'label_for' => 'frtwp_api_key_field',
                'class' => 'frtwp_row',
            )
        );
    }
    
    public function FRTWPSectionGeneralCallback()
    {
        esc_html_e("whatever");
    }

    public function FRTWPApiKeyFieldCallback($args)
    {
        $options = get_option('frtwp_options');
        ?>
        <input
            type="text"
            id="<?php echo esc_attr($args['label_for']); ?>"
            name="frtwp_options[<?php echo esc_attr($args['label_for']); ?>]"
            value="<?php echo isset($options[$args['label_for']]) ? esc_attr($options[$args['label_for']]) : ''; ?>" />
        <?php
    }

    public function FRTWPOptionsPage()
    {
        add_menu_page(
            'FRT Weather',
            'FRT Weather',
            'manage_options',
            'frtwp',
            array($this, 'FRTWPPage')
        );
    }

    public function FRTWPPage()
    {
        if (! current_user_can('manage_options')) {
            return;
        }
    
        if (isset($_GET['settings-updated'])) {
            add_settings_error('frtwp_messages', 'frtwp_success', __('Settings Saved', 'frt-weather-plugin'), 'success');
        }
    
        settings_errors('frtwp_messages'); ?>
    
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
            <?php
                settings_fields('frtwp');
                do_settings_sections('frtwp');
                submit_button('Save Settings'); ?>
            </form>
        </div>
        <?php
    }
}