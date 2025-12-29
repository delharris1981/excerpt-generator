<?php

declare(strict_types=1);

namespace LuhnSummarizer;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Admin
 * Handles the WordPress dashboard settings and configuration.
 */
class Admin
{

    private const OPTION_NAME = 'luhn_summarizer_options';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Adds the settings page to the WordPress menu.
     */
    public function register_menu(): void
    {
        add_options_page(
            __('Luhn Excerpts Settings', 'luhn-summarizer'),
            __('Luhn Excerpts', 'luhn-summarizer'),
            'manage_options',
            'luhn-summarizer',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Registers settings via the WordPress Settings API.
     */
    public function register_settings(): void
    {
        register_setting(self::OPTION_NAME, self::OPTION_NAME, [
            'sanitize_callback' => [$this, 'sanitize_options'],
            'default' => [
                'sentence_count' => 3,
                'auto_generate' => 1,
                'language' => 'en',
            ],
        ]);

        add_settings_section(
            'luhn_main_section',
            __('Luhn Algorithmic Settings', 'luhn-summarizer'),
            null,
            'luhn-summarizer'
        );

        add_settings_field(
            'sentence_count',
            __('Sentence Count', 'luhn-summarizer'),
            [$this, 'render_number_field'],
            'luhn-summarizer',
            'luhn_main_section',
            ['label_for' => 'sentence_count']
        );

        add_settings_field(
            'auto_generate',
            __('Auto-generate on Save', 'luhn-summarizer'),
            [$this, 'render_checkbox_field'],
            'luhn-summarizer',
            'luhn_main_section',
            ['label_for' => 'auto_generate']
        );

        add_settings_field(
            'language',
            __('Language', 'luhn-summarizer'),
            [$this, 'render_select_field'],
            'luhn-summarizer',
            'luhn_main_section',
            ['label_for' => 'language']
        );
    }

    /**
     * Sanitizes the input settings.
     */
    public function sanitize_options(array $input): array
    {
        $output = [];

        if (isset($input['sentence_count'])) {
            $value = (int) $input['sentence_count'];
            $output['sentence_count'] = ($value > 0) ? $value : 3;
        }

        $output['auto_generate'] = isset($input['auto_generate']) ? 1 : 0;

        if (isset($input['language'])) {
            $allowed = ['en', 'ru'];
            $output['language'] = in_array($input['language'], $allowed) ? $input['language'] : 'en';
        }

        return $output;
    }

    /**
     * Renders the settings page HTML.
     */
    public function render_settings_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields(self::OPTION_NAME);
                do_settings_sections('luhn-summarizer');
                submit_button(__('Save Settings', 'luhn-summarizer'));
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Input field callback for sentence count.
     */
    public function render_number_field(array $args): void
    {
        $options = get_option(self::OPTION_NAME);
        $value = $options['sentence_count'] ?? 3;
        ?>
        <input type="number" id="<?php echo esc_attr($args['label_for']); ?>"
            name="<?php echo esc_attr(self::OPTION_NAME); ?>[<?php echo esc_attr($args['label_for']); ?>]"
            value="<?php echo esc_attr((string) $value); ?>" min="1" max="10" class="small-text">
        <p class="description">
            <?php esc_html_e('The maximum number of sentences in the generated excerpt.', 'luhn-summarizer'); ?></p>
        <?php
    }

    /**
     * Input field callback for auto-generate toggle.
     */
    public function render_checkbox_field(array $args): void
    {
        $options = get_option(self::OPTION_NAME);
        $value = $options['auto_generate'] ?? 0;
        ?>
        <input type="checkbox" id="<?php echo esc_attr($args['label_for']); ?>"
            name="<?php echo esc_attr(self::OPTION_NAME); ?>[<?php echo esc_attr($args['label_for']); ?>]" value="1" <?php checked(1, (int) $value); ?>>
        <label for="<?php echo esc_attr($args['label_for']); ?>">
            <?php esc_html_e('Automatically generate an excerpt when a new post is published.', 'luhn-summarizer'); ?>
        </label>
        <?php
    }

    /**
     * Input field callback for language selection.
     */
    public function render_select_field(array $args): void
    {
        $options = get_option(self::OPTION_NAME);
        $value = $options['language'] ?? 'en';
        ?>
        <select id="<?php echo esc_attr($args['label_for']); ?>"
            name="<?php echo esc_attr(self::OPTION_NAME); ?>[<?php echo esc_attr($args['label_for']); ?>]">
            <option value="en" <?php selected($value, 'en'); ?>><?php esc_html_e('English', 'luhn-summarizer'); ?></option>
            <option value="ru" <?php selected($value, 'ru'); ?>><?php esc_html_e('Russian', 'luhn-summarizer'); ?></option>
        </select>
        <p class="description">
            <?php esc_html_e('Select the primary language of your content for better summarization accuracy.', 'luhn-summarizer'); ?>
        </p>
        <?php
    }
}
