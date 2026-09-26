/**
 * Types of a Template Spec (`studio/v1`), derived from the generated catalogue so they can never
 * disagree with the server's validator (App\Support\Studio\SpecValidator). See docs/12-ai-templates.md.
 */
import type { StudioCatalogue } from './catalogue';

type Catalogue = StudioCatalogue;

export type ColorRole = Catalogue['colorRoles'][number];
export type Palette = Record<ColorRole, string>;
export type FontId = keyof Catalogue['fonts'];
export type TokenValue<T extends keyof Catalogue['tokens']> =
    Catalogue['tokens'][T][number];

export type HomeSectionName = keyof Catalogue['homeSections'];
export type SectionVariant<S extends HomeSectionName> =
    Catalogue['homeSections'][S]['variants'][number];

type PropValue<Rule> = Rule extends { type: 'boolean' }
    ? boolean
    : Rule extends { type: 'integer' }
      ? number
      : never;

export type SectionProps<S extends HomeSectionName> = {
    [K in keyof Catalogue['homeSections'][S]['props']]?: PropValue<
        Catalogue['homeSections'][S]['props'][K]
    >;
};

/** One entry of `pages.home`: a section, its variant and its props. */
export type HomeSection = {
    [S in HomeSectionName]: {
        section: S;
        variant: SectionVariant<S>;
        props?: SectionProps<S>;
    };
}[HomeSectionName];

export type PageName = keyof Catalogue['pages'];
export type PageVariant<P extends PageName> = Catalogue['pages'][P][number];
export type CopyKey = keyof Catalogue['copy'];

export interface TemplateSpec {
    $schema: Catalogue['version'];
    name: string;
    tokens: {
        colors: { light: Palette; dark: Palette };
        fonts: { display: FontId; body: FontId; mono: FontId };
        radius: TokenValue<'radius'>;
        density: TokenValue<'density'>;
        shadow: TokenValue<'shadow'>;
        motion: TokenValue<'motion'>;
    };
    layout: {
        header: { variant: Catalogue['layout']['header'][number] };
        footer: { variant: Catalogue['layout']['footer'][number] };
        container: Catalogue['containers'][number];
    };
    pages: { home: HomeSection[] } & {
        [P in PageName]: { variant: PageVariant<P> };
    };
    copy?: Partial<Record<CopyKey, string>>;
    css?: string;
}
