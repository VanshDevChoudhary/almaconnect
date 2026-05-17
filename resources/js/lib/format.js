const SYMBOLS = { INR: '₹', USD: '$', EUR: '€', GBP: '£' };

// INR amounts are stored in rupees; displayed in lakhs (LPA).
export function formatSalary(min, max, currency = 'INR') {
    if (!min && !max) return null;
    const symbol = SYMBOLS[currency] ?? `${currency} `;

    if (currency === 'INR') {
        const lpa = (n) => `${(n / 100000).toFixed(0)}`;
        if (min && max) return `${symbol}${lpa(min)}-${lpa(max)} LPA`;
        if (min) return `From ${symbol}${lpa(min)} LPA`;
        return `Up to ${symbol}${lpa(max)} LPA`;
    }

    const fmt = (n) => n.toLocaleString('en-US');
    if (min && max) return `${symbol}${fmt(min)} - ${symbol}${fmt(max)}`;
    if (min) return `From ${symbol}${fmt(min)}`;
    return `Up to ${symbol}${fmt(max)}`;
}

export function formatINR(amount) {
    return `₹${Number(amount || 0).toLocaleString('en-IN')}`;
}

export const JOB_TYPE_LABELS = {
    full_time: 'Full-time',
    internship: 'Internship',
    contract: 'Contract',
    part_time: 'Part-time',
};
