import { marked } from 'marked';
import DOMPurify from 'isomorphic-dompurify';

marked.setOptions({
    breaks: true,
    gfm: true,
});

const ALLOWED_TAGS = [
    'p', 'br', 'strong', 'em', 'a', 'code', 'pre',
    'ul', 'ol', 'li', 'blockquote',
];
const ALLOWED_ATTR = ['href', 'title', 'rel', 'target'];

// Force safe link attributes on every anchor the sanitizer keeps.
DOMPurify.addHook('afterSanitizeAttributes', (node) => {
    if (node.tagName === 'A') {
        node.setAttribute('target', '_blank');
        node.setAttribute('rel', 'noopener nofollow');
    }
});

export function renderMarkdown(source) {
    if (!source) return '';
    const html = marked.parse(String(source));
    return DOMPurify.sanitize(html, {
        ALLOWED_TAGS,
        ALLOWED_ATTR,
        ALLOWED_URI_REGEXP: /^(?:https?:|mailto:)/i,
    });
}
