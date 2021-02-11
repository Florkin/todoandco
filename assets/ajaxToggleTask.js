function toggleTask(button) {
    let miniature = button.closest('.task-miniature');

    $.ajax({
        url: button.attr('href'),
        beforeSend: function () {
            miniature.find('.loader').removeClass('d-none');
        },
        complete: function () {
            miniature.find('.loader').addClass('d-none');
        },
        success: function (data) {
            let newMiniature = $.parseHTML(data);
            miniature.replaceWith(newMiniature);
            // Attach event to element just created
            $(newMiniature).find('.toggle-done-btn').on('touch click', function (e) {
                e.preventDefault();
                toggleTask($(this))
            })
        }
    })
}

$('.toggle-done-btn').on('touch click', function (e) {
    e.preventDefault();
    toggleTask($(this))
})
