$(document).ready(function() {
    $('#nav-ps-admin-language .dropdown-item').on('click', function(e) {
        e.preventDefault();

        var element = this;

        $.ajax({
            url: $(element).attr('href'),
            type: 'post',
            data: 'code=' + $(element).attr('data-language-code') + '&redirect=' + encodeURIComponent($('#input-ps-admin-language-redirect').val()),
            dataType: 'json',
            success: function(json) {
                if (json['redirect']) {
                    location = json['redirect'];
                }

                if (json['error']) {
                    $('#alert').prepend('<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    });
});
