(function($){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    });

    const daysId = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const monthsId = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    window.getJakartaNow = function() {
        return new Date(new Date().toLocaleString('en-US', { timeZone: 'Asia/Jakarta' }));
    };

    window.startWibClock = function(selector, options = {}) {
        const target = document.querySelector(selector);
        if (!target) return null;

        const withDay = options.withDay !== false;
        const withDate = options.withDate !== false;
        const showSeconds = options.showSeconds || false;

        const render = () => {
            const now = getJakartaNow();
            const hh = String(now.getHours()).padStart(2, '0');
            const mm = String(now.getMinutes()).padStart(2, '0');
            const ss = String(now.getSeconds()).padStart(2, '0');
            const timePart = showSeconds ? `${hh}.${mm}.${ss}` : `${hh}.${mm}`;
            const datePart = `${now.getDate()} ${monthsId[now.getMonth()]} ${now.getFullYear()}`;
            const dayPart = daysId[now.getDay()];
            const prefix = [
                withDay ? dayPart : null,
                withDate ? datePart : null,
            ].filter(Boolean).join(', ');
            target.textContent = `${prefix ? prefix + ' · ' : ''}${timePart} WIB`;
        };

        render();
        return setInterval(render, 1000);
    };

    window.flashElement = function(target) {
        const $el = $(target);
        if (!$el.length) return;
        $el.addClass('flash-highlight');
        setTimeout(() => $el.removeClass('flash-highlight'), 1400);
    };

    $(function() {
        document.body.classList.add('page-loaded');
        startWibClock('#nav-clock', { withDay: false, showSeconds: false });
    });
})(jQuery);

function showToast(message, type = 'info') {
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div class="toast align-items-center text-bg-${type === 'danger' ? 'danger' : (type === 'success' ? 'success' : 'secondary')} border-0" role="alert" aria-live="assertive" aria-atomic="true" id="${toastId}">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>`;
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(container);
    }
    container.insertAdjacentHTML('beforeend', toastHtml);
    const toast = new bootstrap.Toast(document.getElementById(toastId), { delay: 3000 });
    toast.show();
}
