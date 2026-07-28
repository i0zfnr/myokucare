import crypto from 'node:crypto';
import fs from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const viewsRoot = path.join(root, 'resources', 'views');
const catalogPaths = {
    bm: path.join(root, 'lang', 'bm.json'),
    en: path.join(root, 'lang', 'en.json'),
    'zh-CN': path.join(root, 'lang', 'zh-CN.json'),
};
const catalogs = Object.fromEntries(
    Object.entries(catalogPaths).map(([locale, file]) => [
        locale,
        JSON.parse(fs.readFileSync(file, 'utf8')),
    ]),
);

const files = [];
function walk(directory) {
    for (const entry of fs.readdirSync(directory, { withFileTypes: true })) {
        const file = path.join(directory, entry.name);
        if (entry.isDirectory()) walk(file);
        else if (file.endsWith('.blade.php')) files.push(file);
    }
}
walk(viewsRoot);

function normalise(value) {
    return value.replace(/\s+/g, ' ').trim();
}

function translatable(value) {
    const text = normalise(value);
    return text.length > 1
        && /[A-Za-zÀ-ÿ]/u.test(text)
        && !/[{}@$]/.test(text)
        && !/^(MyOKUcare|A[+−-]|[A-Z0-9_.:/ -]{1,18})$/.test(text);
}

function keyFor(text) {
    const words = text.toLowerCase()
        .normalize('NFKD').replace(/\p{Diacritic}/gu, '')
        .replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '')
        .split('_').slice(0, 7).join('_') || 'text';
    return `ui.${words}.${crypto.createHash('sha1').update(text).digest('hex').slice(0, 8)}`;
}

const phrases = new Map();
for (const file of files) {
    const source = fs.readFileSync(file, 'utf8')
        .replace(/<(script|style)\b[\s\S]*?<\/\1>/giu, '');
    for (const match of source.matchAll(/>([^<{]*[A-Za-zÀ-ÿ][^<{]*)</gu)) {
        if (translatable(match[1])) phrases.set(normalise(match[1]), keyFor(normalise(match[1])));
    }
    for (const match of source.matchAll(/\b(placeholder|aria-label|title)=(["'])([^"'{}]*[A-Za-zÀ-ÿ][^"'{}]*)\2/gu)) {
        if (translatable(match[3])) phrases.set(normalise(match[3]), keyFor(normalise(match[3])));
    }
}

async function translate(text, target) {
    const language = target === 'zh-CN' ? 'zh-CN' : target;
    const url = new URL('https://translate.googleapis.com/translate_a/single');
    url.searchParams.set('client', 'gtx');
    url.searchParams.set('sl', 'ms');
    url.searchParams.set('tl', language);
    url.searchParams.set('dt', 't');
    url.searchParams.set('q', text);
    const response = await fetch(url, { headers: { 'User-Agent': 'MyOKUcare-localisation/1.0' } });
    if (!response.ok) throw new Error(`Translation request failed (${response.status})`);
    const data = await response.json();
    return data[0].map((part) => part[0]).join('');
}

async function mapLimit(items, limit, callback) {
    const output = new Array(items.length);
    let cursor = 0;
    async function worker() {
        while (cursor < items.length) {
            const index = cursor++;
            output[index] = await callback(items[index], index);
        }
    }
    await Promise.all(Array.from({ length: limit }, worker));
    return output;
}

const entries = [...phrases.entries()];
for (const [text, key] of entries) catalogs.bm[key] = text;

for (const locale of ['en', 'zh-CN']) {
    const pending = entries.filter(([, key]) => !catalogs[locale][key]);
    const translated = await mapLimit(pending, 5, async ([text, key], index) => {
        if (index % 50 === 0) console.log(`${locale}: ${index}/${pending.length}`);
        return [key, await translate(text, locale)];
    });
    for (const [key, value] of translated) catalogs[locale][key] = value;
}

for (const file of files) {
    let source = fs.readFileSync(file, 'utf8');
    const protectedBlocks = [];
    source = source.replace(/<(script|style)\b[\s\S]*?<\/\1>/giu, (block) => {
        protectedBlocks.push(block);
        return `___LOCALISATION_PROTECTED_BLOCK_${protectedBlocks.length - 1}___`;
    });
    source = source.replace(/>([^<{]*[A-Za-zÀ-ÿ][^<{]*)</gu, (whole, raw) => {
        const text = normalise(raw);
        const key = phrases.get(text);
        if (!key) return whole;
        const leading = raw.match(/^\s*/u)[0];
        const trailing = raw.match(/\s*$/u)[0];
        return `>${leading}{{ __('${key}') }}${trailing}<`;
    });
    source = source.replace(/\b(placeholder|aria-label|title)=(["'])([^"'{}]*[A-Za-zÀ-ÿ][^"'{}]*)\2/gu, (whole, attribute, quote, raw) => {
        const key = phrases.get(normalise(raw));
        return key ? `${attribute}="{{ __('${key}') }}"` : whole;
    });
    source = source.replace(/___LOCALISATION_PROTECTED_BLOCK_(\d+)___/g, (_, index) => protectedBlocks[Number(index)]);
    fs.writeFileSync(file, source);
}

for (const [locale, file] of Object.entries(catalogPaths)) {
    const sorted = Object.fromEntries(Object.entries(catalogs[locale]).sort(([a], [b]) => a.localeCompare(b)));
    fs.writeFileSync(file, `${JSON.stringify(sorted, null, 2)}\n`);
}

console.log(`Localised ${entries.length} static UI phrases in ${files.length} Blade templates.`);
