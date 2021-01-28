function hideElem(elem) {
    let height = elem.height();
    elem.css({
        'height': height + 'px',
        'overflow': 'hidden',
    })
    elem.animate({
        'max-width' : 0 + '%',
        'padding' : 0,
    }, 400, function () {
        elem.remove();
    });
}

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
                hideElem(miniature.parent());
            }
        }
    })

})
