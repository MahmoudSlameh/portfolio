import {
    createContext,
    useCallback,
    useContext,
    useEffect,
    useMemo,
    useState,
    type ReactNode,
} from 'react';

interface CommandPaletteValue {
    isOpen: boolean;
    openPalette: () => void;
    closePalette: () => void;
}

const CommandPaletteContext = createContext<CommandPaletteValue | null>(null);

export function CommandPaletteProvider({ children }: { children: ReactNode }) {
    const [isOpen, setIsOpen] = useState(false);

    const openPalette = useCallback(() => setIsOpen(true), []);
    const closePalette = useCallback(() => setIsOpen(false), []);

    useEffect(() => {
        const handleKeyDown = (event: KeyboardEvent): void => {
            if (
                event.key.toLowerCase() !== 'k' ||
                !(event.metaKey || event.ctrlKey)
            )
                return;
            event.preventDefault();
            setIsOpen((current) => !current);
        };
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, []);

    const value = useMemo(
        () => ({ isOpen, openPalette, closePalette }),
        [isOpen, openPalette, closePalette],
    );

    return (
        <CommandPaletteContext.Provider value={value}>
            {children}
        </CommandPaletteContext.Provider>
    );
}

export function useCommandPalette(): CommandPaletteValue {
    const context = useContext(CommandPaletteContext);
    if (!context)
        throw new Error(
            'useCommandPalette must be used within CommandPaletteProvider',
        );
    return context;
}
