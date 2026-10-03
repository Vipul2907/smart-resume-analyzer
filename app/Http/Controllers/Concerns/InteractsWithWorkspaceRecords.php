<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

trait InteractsWithWorkspaceRecords
{
    private function create(string $model, string $table, array $data): void
    {
        $columns = array_flip(Schema::getColumnListing($table));
        $model::create(array_intersect_key($data, $columns));
    }

    private function updateAvailable(object $model, string $table, array $data): void
    {
        $columns = array_flip(Schema::getColumnListing($table));
        $model->update(array_intersect_key($data, $columns));
    }

    private function owns(Request $request, object $model): void
    {
        abort_unless($model->user_id === $request->user()->id, 404);
    }

    private function jobStatuses(): array
    {
        return ['saved', 'applied', 'interviewing', 'offer', 'rejected', 'withdrawn', 'closed'];
    }
}
