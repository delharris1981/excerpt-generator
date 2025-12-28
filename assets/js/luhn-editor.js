(function($) {
    'use strict';

    $(document).ready(function() {
        /**
         * Wait for the editor to initialize.
         * We support both Classic Editor and Gutenberg (Block Editor).
         */
        const init = function() {
            // Check for Excerpt panel in Gutenberg
            const isGutenberg = !!window.wp && !!window.wp.data;

            if (isGutenberg) {
                injectGutenbergButton();
            } else {
                injectClassicButton();
            }
        };

        /**
         * Gutenberg Injection Logic
         */
        function injectGutenbergButton() {
            // We use a safe polling method because Gutenberg panels might be delayed or lazy-loaded
            const interval = setInterval(function() {
                const excerptPanel = $('.editor-post-excerpt');
                if (excerptPanel.length && !$('#luhn-generate-btn').length) {
                    const $btn = $('<button/>', {
                        id: 'luhn-generate-btn',
                        type: 'button',
                        class: 'components-button is-secondary',
                        style: 'margin-top: 10px; width: 100%; display: flex; justify-content: center;',
                        text: luhnData.strings.button_text,
                        click: handleGeneration
                    });

                    excerptPanel.append($btn);
                }
            }, 1000);
        }

        /**
         * Classic Editor Injection Logic
         */
        function injectClassicButton() {
            const excerptBox = $('#postexcerpt .inside');
            if (excerptBox.length && !$('#luhn-generate-btn').length) {
                const $btn = $('<button/>', {
                    id: 'luhn-generate-btn',
                    type: 'button',
                    class: 'button',
                    style: 'margin-top: 10px;',
                    text: luhnData.strings.button_text,
                    click: handleGeneration
                });
                excerptBox.append($btn);
            }
        }

        /**
         * Core Generation Logic
         */
        function handleGeneration(e) {
            e.preventDefault();
            const $btn = $(this);
            const originalText = $btn.text();

            // Get content from editor
            let content = '';
            if (window.wp && window.wp.data && window.wp.data.select('core/editor')) {
                // Gutenberg content
                content = window.wp.data.select('core/editor').getEditedPostContent();
            } else if (typeof tinyMCE !== 'undefined' && tinyMCE.get('content')) {
                // Classic TinyMCE
                content = tinyMCE.get('content').getContent();
            } else {
                // Plain textarea fallback
                content = $('#content').val();
            }

            if (!content) {
                alert('Please add some content first.');
                return;
            }

            $btn.text(luhnData.strings.generating).prop('disabled', true);

            $.ajax({
                url: luhnData.ajax_url,
                type: 'POST',
                data: {
                    action: 'luhn_generate_summary',
                    nonce: luhnData.nonce,
                    content: content
                },
                success: function(response) {
                    if (response.success) {
                        updateExcerptField(response.data.summary);
                    } else {
                        alert(response.data.message || 'Error generating summary.');
                    }
                },
                error: function() {
                    alert('Server communication error.');
                },
                complete: function() {
                    $btn.text(originalText).prop('disabled', false);
                }
            });
        }

        /**
         * Updates the UI excerpt field
         */
        function updateExcerptField(summary) {
            if (window.wp && window.wp.data && window.wp.data.dispatch('core/editor')) {
                // Gutenberg
                window.wp.data.dispatch('core/editor').editPost({ excerpt: summary });
            } else {
                // Classic
                $('#excerpt').val(summary);
            }
        }

        init();
    });

})(jQuery);
