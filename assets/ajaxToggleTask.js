$('.toggle-done-btn').on('touch click', function (e) {
    e.preventDefault();
    let button = $(this);
    let miniature = button.closest('.task-miniature');

    $.ajax({
        url: button.attr('href'),
        beforeSend: function() {
            miniature.find('.loader').removeClass('d-none');
        },
        complete: function() {
            miniature.find('.loader').addClass('d-none');
        },
        success: function (data) {
            miniature.replaceWith(data);
        }
    })

})
