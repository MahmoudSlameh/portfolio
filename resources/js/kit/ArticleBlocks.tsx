import { Fragment, type ReactNode } from 'react';
import type { ArticleBlock } from '@/types/content';

export type ArticleBlockType = ArticleBlock['type'];

export interface ArticleBlockContext {
    index: number;
    isFirst: boolean;
    /** 1-based number of the heading (only meaningful for heading blocks). */
    headingIndex: number;
}

/**
 * One render function per block type. The type is exhaustive: when a new block type is added to
 * the article builder, every template fails to compile until it renders it.
 */
export type ArticleBlockRenderers = {
    [K in ArticleBlockType]: (
        block: Extract<ArticleBlock, { type: K }>,
        context: ArticleBlockContext,
    ) => ReactNode;
};

const renderBlock = (
    block: ArticleBlock,
    renderers: ArticleBlockRenderers,
    context: ArticleBlockContext,
): ReactNode =>
    // The mapped type guarantees the renderer matches the block's type.
    (
        renderers[block.type] as (
            block: ArticleBlock,
            context: ArticleBlockContext,
        ) => ReactNode
    )(block, context);

/** Headless article body: walks the blocks and calls the template's renderer for each one. */
export function ArticleBlocks({
    blocks,
    renderers,
    className,
}: {
    blocks: ArticleBlock[];
    renderers: ArticleBlockRenderers;
    className?: string;
}) {
    let headingIndex = 0;

    return (
        <div className={className}>
            {blocks.map((block, index) => {
                if (block.type === 'heading') headingIndex += 1;

                return (
                    <Fragment key={`${block.type}-${index}`}>
                        {renderBlock(block, renderers, {
                            index,
                            isFirst: index === 0,
                            headingIndex,
                        })}
                    </Fragment>
                );
            })}
        </div>
    );
}
