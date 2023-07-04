document.addEventListener('click', function (event) {
    const target = event.target;
    if (target.classList.contains('search')) {
        const value = target.previousElementSibling.value.toLowerCase();
        console.log(value);
        if (target.classList.contains('inCheatSheet')) {
            const refID = document.querySelector('.cheatSheet-inner').dataset.ref;
            document.querySelector('.cheatModal .cheatSheet-inner').remove();
            fetch('includes/calls/cheatSheet.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `search=${value}&ref=${refID}`
            })
            .then(response => response.text())
            .then(data => {
                document.querySelector('.cheatModal').insertAdjacentHTML('beforeend', data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        } else {
            let filter = '';
            let status = '';
            let page = '';
            const search = `&search=${value}`;
            document.querySelectorAll('.filter select').forEach(function (select) {
                if (select.dataset.type === 'filter') {
                    filter = `&filter=${select.value}`;
                }
                if (select.dataset.type === 'status') {
                    const thisStatus = select.value === 'New Quote' ? 'New+Quote' : select.value;
                    status = `&status=${thisStatus}`;
                }
            });
            const urlParams = new URLSearchParams(window.location.search);
            for (const [key, value] of urlParams.entries()) {
                if (key === 'page') {
                    page = page === '' ? `page=${value}` : page;
                }
            }
            window.location.assign(`dashboard.php?${page}${search}${filter}${status}`);
        }
    }
});
