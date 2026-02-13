export type RouteQueryOptions = {
    query?: Record<string, string | number | boolean | undefined>;
    mergeQuery?: Record<string, string | number | boolean | undefined>;
};

export type RouteDefinition<T extends string | string[] = string> = {
    url: string;
    method: T extends string[] ? T[number] : T;
};

export type RouteMetaDefinition<T extends string | string[] = string> = {
    url: string;
    methods: T extends string[] ? T : T[];
};

export type RouteFormDefinition<T extends string = string> = {
    action: string;
    method: T;
};

export function queryParams(options?: RouteQueryOptions): string {
    const params = options?.query ?? options?.mergeQuery;
    if (!params) return '';

    const entries = Object.entries(params).filter(
        ([, v]) => v !== undefined && v !== null,
    );
    if (entries.length === 0) return '';

    return '?' + new URLSearchParams(
        entries.map(([k, v]) => [k, String(v)]),
    ).toString();
}

export function applyUrlDefaults<T extends Record<string, unknown>>(args: T): T {
    return args;
}
