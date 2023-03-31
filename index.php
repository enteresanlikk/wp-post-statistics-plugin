<?php
/*
 * Plugin Name: Post Statistics
 * Version: 1.0.0
 * Author: Bilal Demir
 * Author URI: http://bilaldemir.dev
 * Text Domain: post-statistics
 * Domain Path: /languages
 */

class PostStatistics {
    public function __construct() {
        add_action('init', [$this, 'Init']);
        add_action('admin_init', [$this, 'AdminInit']);
        add_action('admin_menu', [$this, 'MenuSetup']);
        add_filter('the_content', [$this, 'ContentFilter']);
    }

    public function Init() {
        load_plugin_textdomain(
            'post-statistics',
            false,
            dirname(plugin_basename(__FILE__)).'/languages'
        );
    }

    public function AdminInit() {
        add_settings_section(
            'default',
            null,
            null,
            'post-statistics');

        add_settings_field(
            'post_statistics_location',
            __('Location', 'post-statistics'),
            [
                $this,
                'LocationField'
            ],
            'post-statistics',
            'default'
        );
        register_setting(
            'post-statistics',
            'post_statistics_location',
            [
                'sanitize_callback' => [
                    $this,
                    'SanitizeLocation'
                ],
                'default' => '0'
            ]
        );

        add_settings_field(
            'post_statistics_headline_text',
            __('Headline Text', 'post-statistics'),
            [
                $this,
                'HeadlineTextField'
            ],
            'post-statistics',
            'default'
        );
        register_setting(
            'post-statistics',
            'post_statistics_headline_text',
            [
                'sanitize_callback' => 'sanitize_text_field',
                'default' => __('Post Statistics', 'post-statistics')
            ]
        );

        add_settings_field(
            'post_statistics_word_count',
            __('Word Count', 'post-statistics'),
            [
                $this,
                'CheckboxField'
            ],
            'post-statistics',
            'default',
            [
                'name' => 'post_statistics_word_count'
            ]
        );
        register_setting(
            'post-statistics',
            'post_statistics_word_count',
            [
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '1'
            ]
        );

        add_settings_field(
            'post_statistics_character_count',
            __('Character Count', 'post-statistics'),
            [
                $this,
                'CheckboxField'
            ],
            'post-statistics',
            'default',
            [
                'name' => 'post_statistics_character_count'
            ]
        );
        register_setting(
            'post-statistics',
            'post_statistics_character_count',
            [
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '1'
            ]
        );

        add_settings_field(
            'post_statistics_read_time',
            __('Read Time', 'post-statistics'),
            [
                $this,
                'CheckboxField'
            ],
            'post-statistics',
            'default',
            [
                'name' => 'post_statistics_read_time'
            ]
        );
        register_setting(
            'post-statistics',
            'post_statistics_read_time',
            [
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '1'
            ]
        );
    }

    public function SanitizeLocation($input) {
        if ($input == '0' || $input == '1') {
            return $input;
        }
        add_settings_error(
            'post_statistics_location',
            'post_statistics_location_error',
            __('Invalid location', 'post-statistics'),
        );
        return get_option('post_statistics_location');
    }

    public function LocationField() {
        $selected = get_option('post_statistics_location');
        $options = [
            '0' => __('Top', 'post-statistics'),
            '1' => __('Bottom', 'post-statistics')
        ];

        echo "<select name='post_statistics_location'>";
        foreach ($options as $value => $label) {
            echo "<option value='".$value."' ".selected($selected, $value, false).">".$label."</option>";
        }
        echo "</select>";
    }

    public function HeadlineTextField() {
        $value = get_option('post_statistics_headline_text');
        echo "<input type='text' name='post_statistics_headline_text' value='".esc_attr($value)."' />";
    }

    public function CheckboxField($args) {
        $name = $args['name'];
        $checked = get_option($name);
        echo "<input type='checkbox' name='".$name."' value='1' ".checked($checked, '1', false)." />";
    }

    public function MenuSetup() {
        add_options_page(
            __('Post Statistics', 'post-statistics'),
            __('Post Statistics', 'post-statistics'),
            'manage_options',
            'post-statistics',
            [
                $this,
                'OptionsPageSetup'
            ]
        );
    }
    public function OptionsPageSetup() {
        echo "<div class='wrap'>
            <h1>".(__('Post Statistics', 'post-statistics'))."</h1>
            <p>".(__('Here you can see the statistics of your website.', 'post-statistics'))."</p>
            <form action='options.php' method='post'>";

        settings_fields('post-statistics');
        do_settings_sections('post-statistics');
        submit_button();

        echo "</form></div>";
    }

    public function ContentFilter($content) {
        if (is_main_query() && is_single()) {
            return $this->CreateHTML($content);
        }
        return $content;
    }

    public function CreateHTML($content) {
        $location = get_option('post_statistics_location', '0');
        $headline_text = esc_html(get_option('post_statistics_headline_text', __('Post Statistics', 'post-statistics')));
        $word_count = get_option('post_statistics_word_count', '1');
        $character_count = get_option('post_statistics_character_count', '1');
        $read_time = get_option('post_statistics_read_time', '1');

        if ($word_count || $character_count || $read_time) {
            $wordCount = 0;
            if($word_count || $read_time) {
                $wordCount = str_word_count(strip_tags($content));
            }

            $html = "<div class='post-statistics'>";
            $html .= "<h3>" . $headline_text . "</h3>";
            if ($word_count) {
                $html .= str_replace('{count}', $wordCount, __('Word Count: {count}', 'post-statistics')). "</br>";
            }
            if ($character_count) {
                $html .= str_replace('{count}', strlen(strip_tags($content)), __('Character Count: {count}', 'post-statistics')). "</br>";
            }
            if ($read_time) {
                $html .= str_replace('{time}', round($wordCount / 225), __('Read Time: {time} minute(s)', 'post-statistics')). "</br>";
            }
            $html .= "</div>";

            if ($location == '0') {
                return $html . $content;
            } else {
                return $content . $html;
            }
        }
        return $content;
    }
}

new PostStatistics();