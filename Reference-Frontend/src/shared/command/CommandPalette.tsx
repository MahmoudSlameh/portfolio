import { useNavigate } from '@tanstack/react-router';
import {
  BookOpen,
  CornerDownLeft,
  FileText,
  FolderGit2,
  Hash,
  Languages,
  Mail,
  Moon,
  Search,
  SquareArrowOutUpRight,
  type LucideIcon,
} from 'lucide-react';
import { useEffect, useId, useMemo, useRef, useState, type KeyboardEvent, type MouseEvent } from 'react';
import { homeSections, primaryNav } from '@/config/navigation';
import type { DictionaryKey } from '@/i18n/dictionary';
import { useClipboard } from '@/hooks/useClipboard';
import { useTranslation } from '@/hooks/useTranslation';
import { fuzzyScore } from '@/lib/fuzzy';
import type { SearchIndex } from '@/lib/content';
import { cn } from '@/lib/utils';
import { useCommandPalette } from '@/providers/CommandPaletteProvider';
import { usePreferences } from '@/providers/PreferencesProvider';
import { useToast } from '@/providers/ToastProvider';

type PaletteGroup = 'sections' | 'pages' | 'projects' | 'articles' | 'books' | 'actions';

interface PaletteItem {
  id: string;
  group: PaletteGroup;
  label: string;
  meta?: string;
  keywords: string;
  icon: LucideIcon;
  perform: () => void;
}

const GROUP_ORDER: PaletteGroup[] = ['sections', 'pages', 'projects', 'articles', 'books', 'actions'];
const EMPTY_QUERY_LIMIT: Partial<Record<PaletteGroup, number>> = { books: 4, articles: 3, projects: 4 };

const groupLabelKey = (group: PaletteGroup): DictionaryKey => `palette.group.${group}`;

interface CommandPaletteProps {
  searchIndex: SearchIndex;
  email: string;
}

export function CommandPalette({ searchIndex, email }: CommandPaletteProps) {
  const dialogRef = useRef<HTMLDialogElement>(null);
  const inputRef = useRef<HTMLInputElement>(null);
  const listRef = useRef<HTMLDivElement>(null);
  const [query, setQuery] = useState('');
  const [activeIndex, setActiveIndex] = useState(0);
  const baseId = useId();

  const { isOpen, closePalette } = useCommandPalette();
  const { toggleTheme, toggleLocale } = usePreferences();
  const { t } = useTranslation();
  const { copy } = useClipboard();
  const { notify } = useToast();
  const navigate = useNavigate();

  const items = useMemo<PaletteItem[]>(() => {
    const sectionItems: PaletteItem[] = homeSections.map((section) => ({
      id: `section-${section.id}`,
      group: 'sections',
      label: t(section.key),
      meta: `#${section.id}`,
      keywords: section.id,
      icon: Hash,
      perform: () => navigate({ to: '/', hash: section.id }),
    }));

    const pageItems: PaletteItem[] = primaryNav.map((page) => ({
      id: `page-${page.to}`,
      group: 'pages',
      label: t(page.key),
      meta: page.to,
      keywords: page.to,
      icon: SquareArrowOutUpRight,
      perform: () => navigate({ to: page.to }),
    }));

    const projectItems: PaletteItem[] = searchIndex.projects.map((project) => ({
      id: `project-${project.slug}`,
      group: 'projects',
      label: project.title,
      meta: `/projects/${project.slug}`,
      keywords: project.slug,
      icon: FolderGit2,
      perform: () => navigate({ to: '/projects/$slug', params: { slug: project.slug } }),
    }));

    const articleItems: PaletteItem[] = searchIndex.articles.map((article) => ({
      id: `article-${article.slug}`,
      group: 'articles',
      label: article.title,
      meta: '/writing',
      keywords: article.slug,
      icon: FileText,
      perform: () => navigate({ to: '/writing/$slug', params: { slug: article.slug } }),
    }));

    const bookItems: PaletteItem[] = searchIndex.books.map((book) => ({
      id: `book-${book.slug}`,
      group: 'books',
      label: book.title,
      meta: book.author,
      keywords: book.author,
      icon: BookOpen,
      perform: () => navigate({ to: '/books', hash: `book-${book.slug}` }),
    }));

    const actionItems: PaletteItem[] = [
      {
        id: 'action-theme',
        group: 'actions',
        label: t('palette.action.theme'),
        keywords: 'theme dark light mode',
        icon: Moon,
        perform: toggleTheme,
      },
      {
        id: 'action-language',
        group: 'actions',
        label: t('palette.action.language'),
        keywords: 'language arabic english rtl عربي',
        icon: Languages,
        perform: toggleLocale,
      },
      {
        id: 'action-email',
        group: 'actions',
        label: t('palette.action.copyEmail'),
        meta: email,
        keywords: 'email contact mail',
        icon: Mail,
        perform: () => {
          void copy(email).then((didCopy) => didCopy && notify(t('contact.copied')));
        },
      },
    ];

    return [...sectionItems, ...pageItems, ...projectItems, ...articleItems, ...bookItems, ...actionItems];
  }, [searchIndex, email, t, navigate, toggleTheme, toggleLocale, copy, notify]);

  const groupedResults = useMemo(() => {
    const trimmed = query.trim();
    return GROUP_ORDER.map((group) => {
      const groupItems = items.filter((item) => item.group === group);
      if (!trimmed) {
        const limit = EMPTY_QUERY_LIMIT[group];
        return { group, items: limit ? groupItems.slice(0, limit) : groupItems };
      }
      const scored = groupItems
        .map((item) => ({ item, score: fuzzyScore(trimmed, `${item.label} ${item.keywords}`) }))
        .filter(({ score }) => score > 0)
        .sort((a, b) => b.score - a.score)
        .map(({ item }) => item);
      return { group, items: scored };
    }).filter(({ items: groupItems }) => groupItems.length > 0);
  }, [items, query]);

  const flatResults = useMemo(() => groupedResults.flatMap(({ items: groupItems }) => groupItems), [groupedResults]);
  const activeItem = flatResults[activeIndex];
  const optionId = (item: PaletteItem): string => `${baseId}-${item.id}`;
  const activeOptionId = activeItem ? optionId(activeItem) : undefined;

  useEffect(() => {
    const dialog = dialogRef.current;
    if (!dialog) return;
    if (isOpen && !dialog.open) {
      setQuery('');
      setActiveIndex(0);
      dialog.showModal();
      inputRef.current?.focus();
    }
    if (!isOpen && dialog.open) dialog.close();
  }, [isOpen]);

  useEffect(() => {
    setActiveIndex(0);
  }, [query]);

  useEffect(() => {
    if (!activeOptionId) return;
    document.getElementById(activeOptionId)?.scrollIntoView({ block: 'nearest' });
  }, [activeOptionId]);

  const runItem = (item: PaletteItem): void => {
    closePalette();
    item.perform();
  };

  const handleKeyDown = (event: KeyboardEvent<HTMLInputElement>): void => {
    const total = flatResults.length;
    if (total === 0) return;

    const keyActions: Record<string, () => void> = {
      ArrowDown: () => setActiveIndex((index) => (index + 1) % total),
      ArrowUp: () => setActiveIndex((index) => (index - 1 + total) % total),
      Home: () => setActiveIndex(0),
      End: () => setActiveIndex(total - 1),
      Enter: () => activeItem && runItem(activeItem),
    };

    const action = keyActions[event.key];
    if (!action) return;
    event.preventDefault();
    action();
  };

  const handleBackdropClick = (event: MouseEvent<HTMLDialogElement>): void => {
    if (event.target === dialogRef.current) closePalette();
  };

  const titleId = `${baseId}-title`;
  const listId = `${baseId}-list`;

  return (
    <dialog
      ref={dialogRef}
      aria-labelledby={titleId}
      onClose={closePalette}
      onClick={handleBackdropClick}
      className="dialog-panel mx-auto mt-[10vh] mb-auto w-[min(40rem,calc(100vw-1.5rem))] overflow-hidden rounded-[6px] border border-line-strong bg-raised p-0 text-ink shadow-[0_24px_60px_-20px_rgb(0_0_0/0.45)]"
    >
      <h2 id={titleId} className="sr-only">
        {t('palette.title')}
      </h2>
      <div className="flex items-center gap-3 border-b border-line px-4">
        <Search aria-hidden className="size-4 shrink-0 text-ink-subtle" />
        <input
          ref={inputRef}
          type="text"
          role="combobox"
          aria-expanded="true"
          aria-controls={listId}
          aria-autocomplete="list"
          aria-activedescendant={activeOptionId}
          aria-label={t('palette.placeholder')}
          placeholder={t('palette.placeholder')}
          value={query}
          onChange={(event) => setQuery(event.target.value)}
          onKeyDown={handleKeyDown}
          autoComplete="off"
          spellCheck={false}
          className="h-14 min-w-0 flex-1 bg-transparent text-base text-ink placeholder:text-ink-subtle focus-visible:outline-none"
        />
        <kbd className="ltr-isolate rounded-[3px] border border-line px-1.5 py-0.5 font-mono text-[0.625rem] text-ink-subtle">
          esc
        </kbd>
      </div>

      <div ref={listRef} id={listId} role="listbox" aria-label={t('palette.title')} className="max-h-[min(60vh,26rem)] overflow-y-auto p-2">
        {flatResults.length === 0 && <p className="px-3 py-10 text-center text-sm text-ink-muted">{t('palette.empty')}</p>}
        {groupedResults.map(({ group, items: groupItems }) => {
          const groupId = `${baseId}-group-${group}`;
          return (
            <div key={group} role="group" aria-labelledby={groupId} className="mb-1">
              <p id={groupId} className="eyebrow px-3 pt-3 pb-1.5 text-ink-subtle">
                {t(groupLabelKey(group))}
              </p>
              {groupItems.map((item) => {
                const isActive = item === activeItem;
                const Icon = item.icon;
                return (
                  <div
                    key={item.id}
                    id={optionId(item)}
                    role="option"
                    aria-selected={isActive}
                    onMouseMove={() => setActiveIndex(flatResults.indexOf(item))}
                    onClick={() => runItem(item)}
                    className={cn(
                      'flex items-center gap-3 rounded-[4px] px-3 py-2.5 text-[0.9375rem]',
                      isActive ? 'bg-electric/15 text-ink ring-1 ring-electric/30' : 'text-ink',
                    )}
                  >
                    <Icon aria-hidden className={cn('size-4 shrink-0', isActive ? 'text-signal' : 'text-ink-subtle')} strokeWidth={1.75} />
                    <span className="min-w-0 flex-1 truncate">{item.label}</span>
                    {item.meta && (
                      <span className={cn('ltr-isolate hidden truncate font-mono text-[0.6875rem] sm:block', isActive ? 'text-paper/70' : 'text-ink-subtle')}>
                        {item.meta}
                      </span>
                    )}
                    {isActive && <CornerDownLeft aria-hidden className="size-3.5 shrink-0 text-paper/70 rtl:-scale-x-100" />}
                  </div>
                );
              })}
            </div>
          );
        })}
      </div>

      <div className="flex items-center justify-between gap-4 border-t border-line px-4 py-2.5 text-[0.6875rem] text-ink-subtle">
        <p className="flex items-center gap-4">
          <span className="flex items-center gap-1.5">
            <kbd className="font-mono">↑↓</kbd> {t('palette.hint.navigate')}
          </span>
          <span className="flex items-center gap-1.5">
            <kbd className="font-mono">↵</kbd> {t('palette.hint.select')}
          </span>
          <span className="hidden items-center gap-1.5 sm:flex">
            <kbd className="font-mono">esc</kbd> {t('palette.hint.close')}
          </span>
        </p>
        <p aria-live="polite" className="font-mono">
          {t('palette.results', { count: flatResults.length })}
        </p>
      </div>
    </dialog>
  );
}
