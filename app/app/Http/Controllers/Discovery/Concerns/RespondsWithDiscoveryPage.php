<?php

namespace App\Http\Controllers\Discovery\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

trait RespondsWithDiscoveryPage
{
    /**
     * Página completa ou fragmento HTML para "carregar mais" (X-Next-Page-Url).
     *
     * @param  array<string, mixed>  $data
     */
    protected function discoveryResponse(
        Request $request,
        LengthAwarePaginator $paginator,
        string $pageView,
        string $fragmentView,
        string $itemsKey,
        array $data = [],
    ): View|Response {
        $payload = array_merge($data, [$itemsKey => $paginator]);

        if ($request->boolean('fragment')) {
            return response()
                ->view($fragmentView, $payload)
                ->header('X-Next-Page-Url', $paginator->nextPageUrl() ?? '');
        }

        return view($pageView, $payload);
    }

    protected function discoveryPerPage(Request $request): int
    {
        $perPage = (int) $request->integer('per_page', 12);

        return max(6, min(24, $perPage ?: 12));
    }
}
