import { StatusBadge, type StatusTone } from '@/templates/changelog/components/ui/StatusBadge';
import { useTranslation } from '@/hooks/useTranslation';
import type { ProjectStatus } from '@/types/content';

const toneByStatus: Record<ProjectStatus, StatusTone> = {
  live: 'signal',
  maintained: 'neutral',
  'in-progress': 'warning',
  archived: 'muted',
};

export function ProjectStatusBadge({ status, className }: { status: ProjectStatus; className?: string }) {
  const { t } = useTranslation();
  return <StatusBadge label={t(`projectStatus.${status}`)} tone={toneByStatus[status]} className={className} />;
}
