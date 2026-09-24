// Exports Reference-Frontend/src/data/*.ts to JSON (English values only) for DemoContentSeeder.
const fs = require('fs'),
    path = require('path'),
    ts = require('typescript');
const dir = path.join(__dirname, '../Reference-Frontend/src/data'),
    out = path.join(__dirname, '../database/seeders/data');
const load = (file) => {
    const src = fs.readFileSync(path.join(dir, file), 'utf8');
    const js = ts.transpileModule(src, {
        compilerOptions: {
            module: ts.ModuleKind.CommonJS,
            target: ts.ScriptTarget.ES2022,
        },
    }).outputText;
    const m = { exports: {} };
    new Function('module', 'exports', 'require', js)(m, m.exports, () => ({}));
    return m.exports;
};
const en = (v) =>
    Array.isArray(v)
        ? v.map(en)
        : v && typeof v === 'object'
          ? 'en' in v && 'ar' in v
              ? v.en
              : Object.fromEntries(
                    Object.entries(v).map(([k, x]) => [k, en(x)]),
                )
          : v;
const files = {
    profile: 'profile',
    socials: 'socials',
    skills: 'skillCategories,skills',
    companies: 'companies',
    experiences: 'experiences',
    education: 'education',
    certifications: 'certifications',
    testimonials: 'testimonials',
    projects: 'projects',
    articles: 'articles',
    books: 'books',
    uses: 'usesGroups',
    now: 'nowPage',
    settings: 'siteSettings',
};
for (const [file, names] of Object.entries(files)) {
    const mod = load(file + '.ts');
    const data = names.includes(',')
        ? Object.fromEntries(names.split(',').map((n) => [n, en(mod[n])]))
        : en(mod[names]);
    fs.writeFileSync(
        path.join(out, file + '.json'),
        JSON.stringify(data, null, 2) + '\n',
    );
    console.log(
        file,
        Array.isArray(data) ? data.length : Object.keys(data).length,
    );
}
