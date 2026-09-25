import { router, usePage } from '@inertiajs/react';
import type { SharedProps } from '@/types/shared';

const LABELS = {
    changelog: 'Changelog',
    playground: 'Playground',
    terminal: 'Terminal',
} as const;

/** Floating bar shown to the signed-in owner while previewing a non-active template. */
export function PreviewBar() {
    const { template } = usePage<SharedProps>().props;

    return (
        <div
            role="status"
            style={{
                position: 'fixed',
                insetInline: 0,
                bottom: '1rem',
                zIndex: 2147483000,
                display: 'flex',
                justifyContent: 'center',
                pointerEvents: 'none',
                fontFamily: 'ui-sans-serif, system-ui, sans-serif',
            }}
        >
            <div
                style={{
                    pointerEvents: 'auto',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '0.75rem',
                    padding: '0.5rem 0.75rem 0.5rem 1rem',
                    borderRadius: '9999px',
                    background: '#18181b',
                    color: '#fafafa',
                    fontSize: '0.8125rem',
                    boxShadow: '0 10px 30px rgb(0 0 0 / 0.25)',
                }}
            >
                <span>
                    Previewing <strong>{LABELS[template.id]}</strong> — visitors
                    still see the active template.
                </span>
                <button
                    type="button"
                    onClick={() =>
                        router.get(window.location.pathname, {
                            template: 'reset',
                        })
                    }
                    style={{
                        border: '1px solid rgb(255 255 255 / 0.25)',
                        borderRadius: '9999px',
                        padding: '0.25rem 0.75rem',
                        background: 'transparent',
                        color: 'inherit',
                        cursor: 'pointer',
                    }}
                >
                    Exit preview
                </button>
                <a
                    href="/admin/appearance"
                    style={{
                        borderRadius: '9999px',
                        padding: '0.25rem 0.75rem',
                        background: '#6366f1',
                        color: '#fff',
                        textDecoration: 'none',
                    }}
                >
                    Activate…
                </a>
            </div>
        </div>
    );
}
