<?php

declare(strict_types=1);

namespace LuhnSummarizer;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Hooks
 * Manages WordPress hooks for post data manipulation and asset enqueuing.
 */
class Hooks
{

    private Summarizer $summarizer;

    public function __construct(Summarizer $summarizer)
    {
        $this->summarizer = $summarizer;
        add_filter('wp_insert_post_data', [$this, 'auto_summarize_new_post'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_editor_assets']);
    }

    /**
     * Automatically generates an excerpt for NEW posts if the setting is enabled.
     */
    public function auto_summarize_new_post(array $data, array $postarr): array
    {
        // Only trigger for new posts that aren't revisions/autosaves
        if (!empty($postarr['ID'])) {
            $status = get_post_status($postarr['ID']);
            if ($status !== false && $status !== 'new' && $status !== 'auto-draft') {
                return $data;
            }
        }

        $options = get_option('luhn_summarizer_options');
        if (empty($options['auto_generate'])) {
            return $data;
        }

        // Don't overwrite existing excerpts if they were manually filled
        if (!empty($data['post_excerpt'])) {
            return $data;
        }

        if (empty($data['post_content'])) {
            return $data;
        }

        $sentence_count = (int) ($options['sentence_count'] ?? 3);
        $data['post_excerpt'] = $this->summarizer->summarize($data['post_content'], $sentence_count);

        return $data;
    }

    /**
     * Enqueues the JS needed for the manual generation button in the editor.
     */
    public function enqueue_editor_assets(string $hook): void
    {
        if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
            return;
        }

        wp_enqueue_script(
            'luhn-editor-js',
            plugins_url('assets/js/luhn-editor.js', dirname(__FILE__)),
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script('luhn-editor-js', 'luhnData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('luhn_generate_nonce'),
            'strings' => [
                'button_text' => __('✨ Generate Luhn Excerpt', 'luhn-summarizer'),
                'generating' => __('Generating...', 'luhn-summarizer'),
            ]
        ]);
    }
}
