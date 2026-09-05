/* Boreal Relay Admin JS — v2.0.0 */
(function($) {
    'use strict';

    $(document).ready(function() {
        /* Animate confidence badges */
        $('.br-confidence[data-pct]').each(function() {
            var pct = parseInt($(this).data('pct'), 10);
            if (pct < 60) {
                $(this).css({ background: '#fee2e2', color: '#991b1b' });
            } else if (pct < 80) {
                $(this).css({ background: '#fef3c7', color: '#92400e' });
            }
        });

        /* Show/hide API key */
        var $apiKey = $('#boreal_relay_openai_api_key');
        if ($apiKey.length) {
            $apiKey.after('<button type="button" class="button br-toggle-key" style="margin-left:6px">Show</button>');
            $('.br-toggle-key').on('click', function() {
                if ($apiKey.attr('type') === 'password') {
                    $apiKey.attr('type', 'text');
                    $(this).text('Hide');
                } else {
                    $apiKey.attr('type', 'password');
                    $(this).text('Show');
                }
            });
        }
    });

})(jQuery);
