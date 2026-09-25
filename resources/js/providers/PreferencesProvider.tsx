import {
    createContext,
    useCallback,
    useContext,
    useEffect,
    useMemo,
    useState,
    type ReactNode,
} from 'react';

export type Theme = 'light' | 'dark';

const THEME_KEY = 'changelog:theme';
const THEME_COOKIE = 'theme';

interface PreferencesValue {
    theme: Theme;
    toggleTheme: () => void;
}

const PreferencesContext = createContext<PreferencesValue | null>(null);

const persist = (value: Theme): void => {
    try {
        localStorage.setItem(THEME_KEY, value);
    } catch {
        // Storage can be unavailable (private mode); the cookie still works.
    }
    document.cookie = `${THEME_COOKIE}=${value};path=/;max-age=31536000;samesite=lax`;
};

/**
 * Light/dark theme. The server renders with the theme cookie (or light); without a saved choice the
 * system preference is applied after hydration, so server and client markup always match.
 */
export function PreferencesProvider({
    initialTheme,
    children,
}: {
    initialTheme: Theme | null;
    children: ReactNode;
}) {
    const [theme, setTheme] = useState<Theme>(initialTheme ?? 'light');

    useEffect(() => {
        if (initialTheme !== null) return;
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            setTheme('dark');
        }
    }, [initialTheme]);

    useEffect(() => {
        const root = document.documentElement;
        root.dataset.theme = theme;
        root.style.colorScheme = theme;
    }, [theme]);

    const toggleTheme = useCallback(() => {
        setTheme((current) => {
            const next = current === 'dark' ? 'light' : 'dark';
            persist(next);
            return next;
        });
    }, []);

    const value = useMemo(() => ({ theme, toggleTheme }), [theme, toggleTheme]);

    return (
        <PreferencesContext.Provider value={value}>
            {children}
        </PreferencesContext.Provider>
    );
}

export function usePreferences(): PreferencesValue {
    const context = useContext(PreferencesContext);
    if (!context)
        throw new Error(
            'usePreferences must be used within PreferencesProvider',
        );
    return context;
}
