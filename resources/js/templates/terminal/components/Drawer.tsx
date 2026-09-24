import { X } from 'lucide-react';
import { useEffect, useRef, type ReactNode } from 'react';
import { useTranslation } from '@/hooks/useTranslation';
import { cn } from '@/lib/utils';

interface DrawerProps {
    open: boolean;
    onClose: () => void;
    side: 'start' | 'end';
    label: string;
    children: ReactNode;
}

/** Off-canvas panel built on <dialog> so focus trapping and Escape come for free. */
export function Drawer({ open, onClose, side, label, children }: DrawerProps) {
    const { t } = useTranslation();
    const dialogRef = useRef<HTMLDialogElement>(null);

    useEffect(() => {
        const dialog = dialogRef.current;
        if (!dialog) return;
        if (open && !dialog.open) dialog.showModal();
        if (!open && dialog.open) dialog.close();
    }, [open]);

    return (
        <dialog
            ref={dialogRef}
            aria-label={label}
            onClose={onClose}
            onClick={(event) => {
                if (event.target === event.currentTarget) onClose();
            }}
            className={cn(
                'tm-drawer bg-tm-card text-ink fixed inset-y-0 m-0 h-dvh max-h-none w-[340px] max-w-[88vw] overflow-y-auto p-[30px]',
                side === 'start'
                    ? 'tm-drawer-start start-0 end-auto'
                    : 'tm-drawer-end start-auto end-0',
            )}
        >
            <div className="mb-2 flex justify-end">
                <button
                    type="button"
                    onClick={onClose}
                    aria-label={t('nav.close')}
                    className="hover:bg-tm-tile inline-flex size-10 items-center justify-center rounded-md text-[#62a92b]"
                >
                    <X aria-hidden className="size-5" />
                </button>
            </div>
            {children}
        </dialog>
    );
}
