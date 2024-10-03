<?php

declare(strict_types=1);

arch('models')
    ->expect('App\Models')
    ->toHaveMethod('casts')
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->toOnlyBeUsedIn([
        'App\Console',
        'App\Http',
        'App\Jobs',
        'App\Observers',
        'App\Mail',
        'App\Models',
        'App\Providers',
        'App\Rules',
        'App\Services',
        'App\Actions',
        'Database\Factories',
        'Database\Seeders\DatabaseSeeder',
    ]);

arch('ensure factories', function () {
    $models = getModels();

    foreach ($models as $model) {
        /* @var \Illuminate\Database\Eloquent\Factories\HasFactory $model */
        expect($model::factory())
            ->toBeInstanceOf(Illuminate\Database\Eloquent\Factories\Factory::class);
    }
});

/**
 * Get all models in the app/Models directory.
 *
 * @return array<int, class-string<\Illuminate\Database\Eloquent\Model>>
 */
function getModels(): array
{
    $models = glob(__DIR__.'/../../app/Models/*.php');

    return collect($models)
        ->map(function ($file) {
            $className = 'App\Models\\'.basename($file, '.php');

            // Check if the class extends Pivot
            if (is_subclass_of($className, Illuminate\Database\Eloquent\Relations\Pivot::class)) {
                return null; // Exclude this model
            }

            return $className;
        })
        ->filter() // Remove null values from the collection
        ->toArray();
}
