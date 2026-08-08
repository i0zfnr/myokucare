import fs from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const locales = ['bm', 'en', 'zh-CN'];

function phpString(value) {
    return `'${String(value).replaceAll('\\', '\\\\').replaceAll("'", "\\'")}'`;
}

function render(value, depth = 0) {
    const indent = '    '.repeat(depth);
    const childIndent = '    '.repeat(depth + 1);
    const rows = Object.entries(value).map(([key, child]) =>
        `${childIndent}${phpString(key)} => ${typeof child === 'object' ? render(child, depth + 1) : phpString(child)},`
    );

    return `[\n${rows.join('\n')}\n${indent}]`;
}

for (const locale of locales) {
    const jsonPath = path.join(root, 'lang', `${locale}.json`);
    const catalogue = JSON.parse(fs.readFileSync(jsonPath, 'utf8'));
    const grouped = {};

    for (const [key, translation] of Object.entries(catalogue)) {
        if (!key.startsWith('ui.')) continue;
        const segments = key.slice(3).split('.');
        let cursor = grouped;
        for (const segment of segments.slice(0, -1)) cursor = cursor[segment] ??= {};
        cursor[segments.at(-1)] = translation;
    }

    const targetDirectory = path.join(root, 'lang', locale);
    fs.mkdirSync(targetDirectory, { recursive: true });
    fs.writeFileSync(path.join(targetDirectory, 'ui.php'), `<?php\n\nreturn ${render(grouped)};\n`, 'utf8');
}

console.log('Generated grouped ui.php catalogues for bm, en, and zh-CN.');
