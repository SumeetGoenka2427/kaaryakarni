<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Services\Media\ImageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/** Replace / remove an ImageService image stored in a JSON column. */
trait HandlesImageUpload
{
    protected function syncImage(Request $request, Model $model, string $column, string $directory, array $widths, ?float $ratio = null, string $input = 'image', string $disk = 'public'): void
    {
        $images = app(ImageService::class);

        if ($request->hasFile($input)) {
            $old = $model->{$column};
            $model->{$column} = $images->store($request->file($input), $directory, $widths, $ratio, $disk);
            $images->delete($old);
        } elseif ($request->boolean("remove_{$input}") && $model->{$column}) {
            $images->delete($model->{$column});
            $model->{$column} = null;
        }
    }
}
