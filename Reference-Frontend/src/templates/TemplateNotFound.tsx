import { useTemplatePages } from './useTemplate';

export function TemplateNotFound() {
  const { NotFound } = useTemplatePages();
  return <NotFound />;
}
