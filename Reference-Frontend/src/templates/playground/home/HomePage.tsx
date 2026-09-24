import type { HomePageProps } from '@/templates/types';
import { AboutBlock } from './AboutBlock';
import { BookNook } from './BookNook';
import { CareerTickets } from './CareerTickets';
import { ClientsWall } from './ClientsWall';
import { HeroBento } from './HeroBento';
import { PostcardContact } from './PostcardContact';
import { SchoolBadges } from './SchoolBadges';
import { StackBoard } from './StackBoard';
import { TapeMarquee } from './TapeMarquee';
import { WorkList } from './WorkList';
import { WritingGrid } from './WritingGrid';

export function HomePage(data: HomePageProps) {
  const skillNames = data.skillGroups.flatMap((group) => group.skills.map((skill) => skill.name));

  return (
    <>
      <HeroBento profile={data.profile} socials={data.socials} />
      <TapeMarquee items={skillNames} />
      <AboutBlock profile={data.profile} />
      <StackBoard groups={data.skillGroups} />
      <CareerTickets entries={data.career} />
      <WorkList projects={data.projects} />
      <ClientsWall companies={data.companies} testimonials={data.testimonials} />
      <SchoolBadges education={data.education} certifications={data.certifications} />
      <WritingGrid articles={data.articles} />
      <BookNook books={data.books} />
      <PostcardContact profile={data.profile} />
    </>
  );
}
