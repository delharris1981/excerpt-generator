<?php

declare(strict_types=1);

namespace LuhnSummarizer;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Ajax
 * Handles AJAX requests for manual summary generation.
 */
class Ajax
{

    private Summarizer $summarizer;

    public function __construct(Summarizer $summarizer)
    {
        $this->summarizer = $summarizer;
        add_action('wp_ajax_luhn_generate_summary', [$this, 'handle_generate_summary']);
    }

    /**
     * Responds to the AJAX request to generate a summary.
     */
    public function handle_generate_summary(): void
    {
        check_ajax_referer('luhn_generate_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permissions denied.', 'luhn-summarizer')]);
        }

        $content = isset($_POST['content']) ? wp_unslash((string) $_POST['content']) : '';

        if (empty($content)) {
            wp_send_json_error(['message' => __('No content provided.', 'luhn-summarizer')]);
        }

        $options = get_option('luhn_summarizer_options');
        $sentence_count = (int) ($options['sentence_count'] ?? 3);

        $summary = $this->summarizer->summarize($content, $sentence_count);

        wp_send_json_success([
            'summary' => $summary
        ]);
    }
}
