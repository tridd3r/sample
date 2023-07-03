
$(document).on('click', '.search', function () {
    if ($(this).hasClass('inCheatSheet')) {
        let value = $(this).prev().val();
        value = value.toLowerCase();
        console.log(value);
        let refID = $('.cheatSheet-inner').data('ref');
        $('.cheatModal .cheatSheet-inner').remove();
        $.post('includes/calls/cheatSheet.php', ({ search: value, ref: refID }), function (data) {
            $('.cheatModal').append(data);
        })
    } else {
        let value = $(this).prev().val(), filter = '', status = '', page = '';
        let search = '&search=' + value;
        $('.filter select').each(function () {
            if ($(this).data().type == 'filter') {
                filter = '&filter=' + $(this).val();
            }
            if ($(this).data().type == 'status') {
                let thisStatus = $(this).val() == "New Quote" ? 'New+Quote' : $(this).val();
                status = '&status=' + thisStatus;
            }
        })
        const urlParams = new URLSearchParams(window.location.search);
        for (const [key, value] of urlParams.entries()) {
            if (key == 'page') {
                page = page == '' ? 'page=' + value : page;
            }
        }
        window.location.assign(`dashboard.php?${page}${search}${filter}${status}`);
    }
})