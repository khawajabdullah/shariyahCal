import DOMPurify from 'dompurify';

const SENSITIVE_KEYS = new Set([
  'password',
  'password_confirmation',
  'current_password',
  'remember',
]);

export function sanitizePlainText(value) {
  if (typeof value !== 'string') {
    return value;
  }

  return DOMPurify.sanitize(value, {
    ALLOWED_TAGS: [],
    ALLOWED_ATTR: [],
    KEEP_CONTENT: true,
  }).replace(/\u0000/g, '').replace(/\s+/g, ' ').trim();
}

export function sanitizeForm(payload, skipKeys = SENSITIVE_KEYS) {
  if (Array.isArray(payload)) {
    return payload.map((item) => sanitizeForm(item, skipKeys));
  }

  if (payload && typeof payload === 'object') {
    return Object.fromEntries(
      Object.entries(payload).map(([key, value]) => [
        key,
        skipKeys.has(key) ? value : sanitizeForm(value, skipKeys),
      ]),
    );
  }

  return sanitizePlainText(payload);
}
