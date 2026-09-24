/** Archive filters (validated on the server by App\Http\Requests\Site\*). */
export interface ArchiveSearch {
    q?: string;
    tech?: string;
    category?: 'platform' | 'product' | 'open-source' | 'design-system';
    sort?: 'newest' | 'oldest';
    view?: 'grid' | 'table';
}

export interface WritingSearch {
    q?: string;
    tag?: string;
}

export interface LibrarySearch {
    status?: 'reading' | 'read' | 'to-read';
    category?:
        | 'engineering'
        | 'design'
        | 'systems'
        | 'fiction'
        | 'history'
        | 'philosophy';
    view?: 'shelf' | 'grid';
}
