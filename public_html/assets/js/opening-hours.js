(() => {
    const table = document.querySelector('[data-opening-hours]');
    if (!table) return;

    const timeZone = table.dataset.timezone || 'Asia/Kolkata';
    const clockParts = new Intl.DateTimeFormat('en-GB', {
        timeZone,
        weekday: 'long',
        hour: '2-digit',
        minute: '2-digit',
        hourCycle: 'h23',
    });
    const timeLabel = (minutes) => {
        const hour = Math.floor(minutes / 60);
        const minute = minutes % 60;
        const suffix = hour >= 12 ? 'PM' : 'AM';
        const hour12 = hour % 12 || 12;
        return `${hour12}:${String(minute).padStart(2, '0')} ${suffix}`;
    };

    const update = () => {
        const parts = Object.fromEntries(clockParts.formatToParts(new Date()).map(({ type, value }) => [type, value]));
        const today = parts.weekday.toLowerCase();
        const now = Number(parts.hour) * 60 + Number(parts.minute);

        table.querySelectorAll('[data-opening-hours-row]').forEach((row) => {
            const isToday = row.dataset.day === today;
            row.classList.toggle('hours__row--today', isToday);
            const state = row.querySelector('.hours__state');
            if (!state) return;
            state.replaceChildren();
            if (!isToday) return;

            const periods = (row.dataset.periods || '').split(',').filter(Boolean).map((period) => {
                const [opens, closes] = period.split('-').map((value) => {
                    const [hour, minute] = value.split(':').map(Number);
                    return hour * 60 + minute;
                });
                return { opens, closes };
            });
            const active = periods.find(({ opens, closes }) => now >= opens && now < closes);
            const next = periods.find(({ opens }) => now < opens);
            const badge = document.createElement('span');
            if (active) {
                badge.className = 'badge badge--success';
                badge.textContent = 'Open now';
            } else {
                badge.className = 'badge badge--muted';
                badge.textContent = next ? `Opens at ${timeLabel(next.opens)}` : (periods.length ? 'Closed for today' : 'Closed');
            }
            state.append(badge);
        });
    };

    update();
    window.setInterval(update, 30_000);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) update();
    });
})();
