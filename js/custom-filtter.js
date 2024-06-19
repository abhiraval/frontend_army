jQuery(document).ready(function($) {
    $('#custom-filter').change(function() {
        var filter = $(this).val();
        $.ajax({
            url: ajax_params.ajax_url,
            type: 'POST',
            data: {
                action: 'custom_filter_products',
                filter: filter
            },
            success: function(response) {
                if (response) {
                    $('ul.products').html(response);
                }
            }
        });
    });

});
jQuery(document).ready(function($) {
    $('#gift_card_message').on('input', function() {
        var maxlength = 140;
        var remaining = maxlength - $(this).val().length;
        $('.char-count .remaining').text(remaining);
    });
});

