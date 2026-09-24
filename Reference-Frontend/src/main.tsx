import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { createRouter, RouterProvider } from '@tanstack/react-router';
import { CommandPaletteProvider } from '@/providers/CommandPaletteProvider';
import { PreferencesProvider } from '@/providers/PreferencesProvider';
import { ToastProvider } from '@/providers/ToastProvider';
import { TemplateNotFound } from '@/templates/TemplateNotFound';
import { routeTree } from './routeTree.gen';
import './styles/main.css';

const router = createRouter({
  routeTree,
  scrollRestoration: true,
  defaultPreload: 'intent',
  defaultNotFoundComponent: TemplateNotFound,
});

declare module '@tanstack/react-router' {
  interface Register {
    router: typeof router;
  }
}

const rootElement = document.getElementById('root');
if (!rootElement) throw new Error('Root element #root is missing from index.html');

createRoot(rootElement).render(
  <StrictMode>
    <PreferencesProvider>
      <ToastProvider>
        <CommandPaletteProvider>
          <RouterProvider router={router} />
        </CommandPaletteProvider>
      </ToastProvider>
    </PreferencesProvider>
  </StrictMode>,
);
