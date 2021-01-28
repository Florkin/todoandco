$('.ajax-submit').on('submit', function (e) {
    e.preventDefault();
    let form = $(this);
    let miniature = form.closest('.task-miniature');

    $.ajax({
        url: form.attr('action'),
        beforeSend: function() {
            miniature.find('.loader').removeClass('d-none');
        },
        complete: function() {
            miniature.find('.loader').addClass('d-none');
        },
        success: function (data) {
            if (data.status === 'success') {
                miniature.parent().hide(200, function () {
                    miniature.parent().remove();
                });
            }
        }
    })

})
