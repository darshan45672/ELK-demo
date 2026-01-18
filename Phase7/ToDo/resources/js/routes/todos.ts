import { queryParams, type RouteQueryOptions, type RouteDefinition } from '../wayfinder'

/**
 * @see \App\Http\Controllers\TodoController::index
 * @route '/todos'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get", "head"],
    url: '/todos',
} satisfies RouteDefinition<["get", "head"]>

index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
 * @see \App\Http\Controllers\TodoController::store
 * @route '/todos'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/todos',
} satisfies RouteDefinition<["post"]>

store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
 * @see \App\Http\Controllers\TodoController::show
 * @route '/todos/{todo}'
 */
export const show = (params: { id: number | string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(params, options),
    method: 'get',
})

show.definition = {
    methods: ["get", "head"],
    url: '/todos/{id}',
} satisfies RouteDefinition<["get", "head"]>

show.url = (params: { id: number | string }, options?: RouteQueryOptions) => {
    return show.definition.url.replace('{id}', String(params.id)) + queryParams(options)
}

/**
 * @see \App\Http\Controllers\TodoController::update
 * @route '/todos/{todo}'
 */
export const update = (params: { id: number | string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(params, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/todos/{id}',
} satisfies RouteDefinition<["patch"]>

update.url = (params: { id: number | string }, options?: RouteQueryOptions) => {
    return update.definition.url.replace('{id}', String(params.id)) + queryParams(options)
}

/**
 * @see \App\Http\Controllers\TodoController::destroy
 * @route '/todos/{todo}'
 */
export const destroy = (params: { id: number | string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(params, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/todos/{id}',
} satisfies RouteDefinition<["delete"]>

destroy.url = (params: { id: number | string }, options?: RouteQueryOptions) => {
    return destroy.definition.url.replace('{id}', String(params.id)) + queryParams(options)
}

/**
 * @see \App\Http\Controllers\TodoController::toggleComplete
 * @route '/todos/{todo}/toggle'
 */
export const toggle = (params: { id: number | string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggle.url(params, options),
    method: 'post',
})

toggle.definition = {
    methods: ["post"],
    url: '/todos/{id}/toggle',
} satisfies RouteDefinition<["post"]>

toggle.url = (params: { id: number | string }, options?: RouteQueryOptions) => {
    return toggle.definition.url.replace('{id}', String(params.id)) + queryParams(options)
}
