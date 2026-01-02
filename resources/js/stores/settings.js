import { atom, computed } from 'nanostores'

const enabledLanguages = window.$tbp?.enabledLanguages || [];

// Grouping logic to handle cases where same language might have multiple bibles
export const $availableLanguages = atom([]);
export const $availableBibles = atom([]);

const languagesMap = {};
enabledLanguages.forEach(item => {
    if (!languagesMap[item.value]) {
        languagesMap[item.value] = {
            value: item.value,
            label: item.itemText,
            bibles: [],
            default_bible: item.default_bible || '',
            is_default: !!item.is_default
        };
    }

    const biblesToAdd = item.bibles_data || [];
    // Fallback if bibles_data is missing but bibles is present
    if (biblesToAdd.length === 0 && item.bibles) {
        const ids = Array.isArray(item.bibles) ? item.bibles : [item.bibles];
        const names = (item.bible_name || '').split(', ');
        ids.forEach((id, idx) => {
            biblesToAdd.push({id, name: names[idx] || id});
        });
    }

    biblesToAdd.forEach(b => {
        if (!languagesMap[item.value].bibles.find(eb => eb.value === b.id)) {
            languagesMap[item.value].bibles.push({
                value: b.id,
                label: b.name
            });
        }
    });

    if (item.is_default) {
        languagesMap[item.value].is_default = true;
        languagesMap[item.value].default_bible = item.default_bible;
    }
});

const groupedLanguages = Object.values(languagesMap);
$availableLanguages.set(groupedLanguages);

export const $translationOptions = computed($availableLanguages, (languages) => {
    const options = [];
    languages.forEach(lang => {
        if (lang.bibles.length > 1) {
            lang.bibles.forEach(bible => {
                options.push({
                    value: `${lang.value}|${bible.value}`,
                    label: `${lang.label} (${bible.label})`,
                    language: lang.value,
                    bible: bible.value
                });
            });
        } else if (lang.bibles.length === 1) {
            const bible = lang.bibles[0];
            options.push({
                value: `${lang.value}|${bible.value}`,
                label: lang.label,
                language: lang.value,
                bible: bible.value
            });
        }
    });
    return options;
});

const defaultLangConfig = groupedLanguages.find(l => l.is_default) || groupedLanguages[0];
const initialLang = localStorage.getItem('tbp_language') || defaultLangConfig?.value || '';
const initialLangConfig = groupedLanguages.find(l => l.value === initialLang) || defaultLangConfig;

// Initialize selected bible with preference or language's default
let initialBible = localStorage.getItem('tbp_bible');
if (!initialBible || !initialLangConfig?.bibles.find(b => b.value === initialBible)) {
    initialBible = initialLangConfig?.default_bible || initialLangConfig?.bibles[0]?.value || '';
}

export const $selectedLanguage = atom(initialLangConfig?.value || '');
export const $selectedBible = atom(initialBible);

export const $selectedCombined = computed([$selectedLanguage, $selectedBible], (lang, bible) => {
    return `${lang}|${bible}`;
});

export const setSelectedCombined = (value) => {
    const [lang, bible] = value.split('|');
    // If the language is changing, we want to set it first.
    // The subscription in $selectedLanguage will handle updating $availableBibles
    // and might try to set a default bible, but our subsequent $selectedBible.set(bible) will win.
    if (lang) $selectedLanguage.set(lang);
    if (bible) $selectedBible.set(bible);
}

// Update available bibles when language changes
$selectedLanguage.subscribe(langValue => {
    localStorage.setItem('tbp_language', langValue);
    const langConfig = groupedLanguages.find(l => l.value === langValue);
    const bibles = langConfig?.bibles || [];
    $availableBibles.set(bibles);

    // If current bible is not in the new language's bibles, switch to the language default or first available
    if (bibles.length > 0 && !bibles.find(b => b.value === $selectedBible.get())) {
        $selectedBible.set(langConfig.default_bible || bibles[0].value);
    }
});

$selectedBible.subscribe(value => {
    if (value) {
        localStorage.setItem('tbp_bible', value);
    }
});
