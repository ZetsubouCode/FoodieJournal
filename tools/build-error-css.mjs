import { readFile, mkdir, writeFile } from 'node:fs/promises';

const tokens = await readFile(new URL('../resources/css/tokens.css', import.meta.url), 'utf8');
const error = await readFile(new URL('../resources/css/error.css', import.meta.url), 'utf8');
const target = new URL('../public/assets/css/error.css', import.meta.url);
await mkdir(new URL('../public/assets/css/', import.meta.url), { recursive: true });
await writeFile(target, `/* Generated from resources/css/tokens.css and resources/css/error.css. */\n${tokens}\n${error}`);
