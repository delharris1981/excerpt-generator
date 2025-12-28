<?php
/**
 * Plugin Name: Luhn Excerpt Generator
 * Description: Generates intelligent excerpts using the Luhn algorithm for automated summarization.
 * Version: 1.0.0
 * Author: Antigravity AI
 * Text Domain: luhn-summarizer
 * Requires PHP: 8.2
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Autoloading classes
spl_autoload_register(function ($class) {
    $prefix = 'LuhnSummarizer\\';
    $base_dir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Initialize the plugin
add_action('plugins_loaded', function () {
    $summarizer = new \LuhnSummarizer\Summarizer();

    new \LuhnSummarizer\Admin();
    new \LuhnSummarizer\Hooks($summarizer);
    new \LuhnSummarizer\Ajax($summarizer);
    new \LuhnSummarizer\UpdateManager(__FILE__);
});
