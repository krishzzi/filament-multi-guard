<?php

namespace Iotronlab\FilamentMultiGuard\Commands\Support;

use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Filament\Support\Commands\Concerns\CanValidateInput;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

abstract class MultiGuardAbstractCommand extends Command
{
    use CanManipulateFiles,CanValidateInput;

    protected array $fillBag=[];



    protected function getCurrentContext(): Stringable|string
    {
        $input = $this->validateInput(
            fn () => $this->argument('name') ?? $this->askRequired('Name (e.g. `FilamentTeams`)', 'name'),
            'name',
            ['required', 'not_in:filament']
        );
        return Str::of($input)
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->replace('/', '\\');

    }

    protected function getCurrentContextName(Stringable|string $context): Stringable|string
    {
        return $context->afterLast('\\')->kebab();
    }

    protected function getCurrentContextLocation(Stringable|string $context): string
    {
        return app_path((string) $context->replace('\\', '/'));
    }


    protected function checkCollision(...$path): bool
    {
        if (! $this->option('force') && $this->checkForCollision($path)) {
            return static::INVALID;
        }
        return true;
    }


    // Methods
    protected function getPreparedStubs(string $append): array
    {
        $context = $this->getCurrentContext();
        $location = $this->getCurrentContextLocation($context);


        $currentNamespace = $context
            ->replace('\\', '\\\\')
            ->prepend('\\')
            ->prepend('App')
            ->append('\\')
            ->append($append);



        $currentClass = $context->afterLast('\\')->append($append);

        $currentPath = $currentClass
            ->prepend($location.'/'.$append.'/')
            ->append('.php');

        return [
            'class' => $currentClass,
            'name' => $this->getCurrentContextName($context),
            'namespace' => $currentNamespace,
            'path' => $currentPath,
        ];
    }




    protected function createDirectories()
    {
        $context = $this->getCurrentContext();
        $directoryPath = $this->getCurrentContextLocation($context);
        if (!is_dir($directoryPath))
        {
            $this->info('preparing context location');
            app(Filesystem::class)->makeDirectory(app_path('Http/Livewire/'.ucfirst($context)), force: $this->option('force'));
            app(Filesystem::class)->makeDirectory($directoryPath, force: $this->option('force'));
            app(Filesystem::class)->makeDirectory($directoryPath.'/Pages', force: $this->option('force'));
            app(Filesystem::class)->makeDirectory($directoryPath.'/Resources', force: $this->option('force'));
            app(Filesystem::class)->makeDirectory($directoryPath.'/Widgets', force: $this->option('force'));
        }else{
            $this->warn('requested context directory already exists!');
        }
    }




    protected function copyStubToApp(string $stub, string $targetPath, array $replacements = []): void
    {

        $filesystem = app(Filesystem::class);

        if (! $this->fileExists($stubPath = base_path("stubs/filament/{$stub}.stub"))) {
            $stubPath = __DIR__."/../../../stubs/{$stub}.stub";
        }

        $stub = Str::of($filesystem->get($stubPath));

        foreach ($replacements as $key => $replacement) {
            $stub = $stub->replace("{{ {$key} }}", $replacement);
        }

        $stub = (string) $stub;

        $this->writeFile($targetPath, $stub);
    }


    protected function registerProvider()
    {
        $provider = $this->fillBag['provider'];



        $path = config_path('app.php');
        $content = File::get($path);

        $content = str_replace(
            "App\Providers\RouteServiceProvider::class,",
            "App\Providers\RouteServiceProvider::class,\n\t\t$provider,",
            $content
        );

        File::put($path, $content);

        Config::clearResolvedInstances();
        $this->info('Service provider register successfully!');

    }


    protected function unRegisterProvider(string $provider)
    {

        $path = config_path('app.php');
        $content = File::get($path);

        $content = str_replace("\n\t\t$provider,", '', $content);

        File::put($path, $content);

        Config::clearResolvedInstances();
        $this->info('Service provider unregister successfully!');

    }






}
