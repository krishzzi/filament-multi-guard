<?php

namespace Iotronlab\FilamentMultiGuard\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Iotronlab\FilamentMultiGuard\Commands\Support\MultiGuardAbstractCommand;

class FilamentMultiGuardCommand extends MultiGuardAbstractCommand
{

    protected $description = 'generate filament context with all necessary files';
    protected $signature = 'make:multiguard {name?} {--f|force}';


    public function handle(): int
    {
        $this->info('Multi Guard Context Creation Start');
        sleep(1);
        $this->warn('Current Context: '. $this->getCurrentContext());
        sleep(2);
        $this->generateContextRequirements();
        sleep(1);
        $this->info('Multi Guard Context Creation Finish');
        return self::SUCCESS;

    }


    public function getAllowedStubs()
    {
        return  [
            'middleware' => [
                'Middleware' => 'ContextMiddleware'
            ],
            'livewire_pages' => [
                'Login' => 'ContextSignin',
                'Register' => 'ContextRegister',
                'Forget' => 'ContextForget',
            ],
            'pages' => [
                'Dashboard' => 'Dashboard',
                'Profile' => 'Profile'
            ],
            'config' => true,
            'provider' => true,
        ];
    }


    protected function generateContextRequirements()
    {
        // Step 1
        $this->generateFileSystem();
        // Step 2
        $this->generateStubsForContext();
    }


    protected function generateFileSystem()
    {
        $this->createDirectories();
    }

    protected function generateStubsForContext()
    {
        $allowedStubs = $this->getAllowedStubs();
        if (!empty($allowedStubs['middleware']))
        {
            foreach ($allowedStubs['middleware'] as $key => $middleware)
            {
                $this->copyMiddlewareStub($middleware);
            }

        }

        if (!empty($allowedStubs['livewire_pages']))
        {
            foreach ($allowedStubs['livewire_pages'] as $key => $livewirePage)
            {
                $this->copyLivewirePageStub($key,$livewirePage);
            }
        }


        if (!empty($allowedStubs['pages']))
        {
            foreach ($allowedStubs['pages'] as $key => $page)
            {
                $this->copyFilamentContextPage($key,$page);
            }
        }


       if(!empty($this->fillBag) && $allowedStubs['config'])
       {
           $this->copyConfigStub();
       }

       $this->copyProviderStub();

       if (isset($this->fillBag['provider']))
       {
           $this->registerProvider();
       }

    }


    // Copy Stubs
    protected function copyMiddlewareStub(string $stub_name)
    {

        $middleware = $this->getPreparedStubs('Middleware');

        if (! $this->option('force') && $this->checkForCollision([$middleware['path']])) {
            return static::INVALID;
        }

        $this->warn('warming up middleware');
        sleep(1);
        $this->copyStubToApp($stub_name, $middleware['path'], [
            'class' => (string) $middleware['class'],
            'name' => (string) $middleware['name'],
            'namespace' => (string) $middleware['namespace'],
        ]);

        $this->fillBag['middlewares'][] = $middleware['namespace'].'\\'.$middleware['class'].'::class';
        $this->info('middleware added successfully for '.$this->getCurrentContext());

    }

    private function copyLivewirePageStub(string $append_name,string $stub_name)
    {
       // dd($append_name,$stub_name);

        $context = $this->getCurrentContext();

        $path = app_path('Http/Livewire/'.ucfirst($context).'/'.ucfirst($append_name).'.php');
        if (! $this->option('force') && $this->checkForCollision([$path])) {
            return static::INVALID;
        }


        $this->copyStubToApp($stub_name, $path, [
            //'class' => (string) $loginClass,
            'namespace' => 'App\Http\Livewire\\'.ucfirst($context),
            'class' => (string) ucfirst($append_name),
            'name' => (string) $this->getCurrentContextName($context),
            'contextName' => ucfirst($context)
        ]);

        $this->fillBag['livewire_pages'][$append_name] = 'App\\Http\\Livewire\\'.ucfirst($this->getCurrentContext()).'\\'.ucfirst($append_name).'::class';

    }

    private function copyConfigStub()
    {

        $context = $this->getCurrentContext();
        $contextName = $this->getCurrentContextName($context);
        $configPath = config_path($contextName->prepend('/')->append('.php'));

        $contextNamespace = $context
            ->replace('\\', '\\\\')
            ->prepend('\\\\')
            ->prepend('App');

        if (! $this->option('force') && $this->checkForCollision([
                $configPath,
            ])) {
            return static::INVALID;
        }

        // Context Pages
        $replacementPages = implode(','.PHP_EOL.str_repeat("\t", 3), array_map(
            static function ($key, $value) {
                return $value;
            },
            array_keys($this->fillBag['pages']),
            $this->fillBag['pages']
        ));



        // App/Http/Livewire
        $replacementLivewirePages = implode(','.PHP_EOL.str_repeat("\t", 3), array_map(
            static function ($key, $value) {
                return "'".strtolower($key)."' => $value";
            },
            array_keys($this->fillBag['livewire_pages']),
            $this->fillBag['livewire_pages']
        ));

        // Middleware
        $replacementMiddleware = implode(','.PHP_EOL, array_map(
            static function ($key, $value) {
                return "$value";
            },
            array_keys($this->fillBag['middlewares']),
            $this->fillBag['middlewares']
        ));


        $guessRouteName = explode('-',$contextName);

        $this->copyStubToApp('ContextConfig', $configPath, [
            'route_path' => $guessRouteName[0],
            'namespace' => (string) $contextNamespace,
            'livewire_path' => (string) $context->replace('\\', '/'),
            'contextPages' => $replacementPages,
            'pages' => $replacementLivewirePages,
            'contextMiddleware' => $replacementMiddleware
        ]);

    }

    private function copyProviderStub()
    {
        $context = $this->getCurrentContext();
        $contextName = $this->getCurrentContextName($context);

        $serviceProviderClass = $context->afterLast('\\')->append('ServiceProvider');

        $serviceProviderPath = $serviceProviderClass
            ->prepend('/')
            ->prepend(app_path('Providers'))
            ->append('.php');

        if (! $this->option('force') && $this->checkForCollision([
                $serviceProviderPath,
            ])) {
            return static::INVALID;
        }


        $this->copyStubToApp('ContextServiceProvider', $serviceProviderPath, [
            'class' => (string) $serviceProviderClass,
            'name' => (string) $contextName,
        ]);

        $this->fillBag['provider'] = 'App\Providers\\'.$serviceProviderClass.'::class';

    }

    private function copyFilamentContextPage(string $append_name,string $stub_name)
    {
        $page = $this->getPreparedStubs('Pages');
        $location = $this->getCurrentContextLocation($this->getCurrentContext()).'/Pages/'.$append_name.'.php';

        if (! $this->option('force') && $this->checkForCollision([$location])) {
            return static::INVALID;
        }

        $this->copyStubToApp($stub_name, $location, [
            'namespace' => (string) $page['namespace'],
        ]);

        $this->fillBag['pages'][$append_name] = $page['namespace'].'\\'.$append_name.'::class';

    }


}
