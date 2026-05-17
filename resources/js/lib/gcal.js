import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';

dayjs.extend(utc);

function stripMarkdown(text) {
    return String(text || '')
        .replace(/[#*_`>~-]/g, '')
        .replace(/\[(.*?)\]\(.*?\)/g, '$1')
        .replace(/\s+/g, ' ')
        .trim()
        .slice(0, 800);
}

export function gcalUrl(event) {
    const start = dayjs.utc(event.starts_at).format('YYYYMMDDTHHmmss[Z]');
    const end = dayjs
        .utc(event.ends_at || dayjs(event.starts_at).add(2, 'hour'))
        .format('YYYYMMDDTHHmmss[Z]');

    const params = new URLSearchParams({
        action: 'TEMPLATE',
        text: event.title,
        dates: `${start}/${end}`,
        details: stripMarkdown(event.description),
        location: event.location || event.online_url || '',
    });

    return `https://calendar.google.com/calendar/render?${params.toString()}`;
}
