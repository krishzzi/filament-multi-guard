<?php

namespace Iotronlab\FilamentMultiGuard;

use Filament\Facades\Filament;
use Filament\FilamentManager;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Traits\ForwardsCalls;

class FilamentMultiGuard
{
    use ForwardsCalls;

    protected static array $passwordRules = [];
    protected array $contexts = [];

    protected ?string $currentContext = null;

    public function __construct(FilamentManager $filament)
    {
        $this->contexts['filament'] = $filament;
    }

    public function setContext(string $context = null)
    {
        $this->currentContext = $context;

        return $this;
    }

    public function currentContext(): string
    {
        return $this->currentContext ?? 'filament';
    }

    public function getContext()
    {
        return $this->contexts[$this->currentContext ?? 'filament'];
    }

    public function getContexts(): array
    {
        return $this->contexts;
    }

    public function addContext(string $name)
    {
        $this->contexts[$name] = new FilamentManager();

        return $this;
    }

    public function forContext(string $context, callable $callback)
    {
        $currentContext = Filament::currentContext();

        Filament::setContext($context);

        $callback();

        Filament::setContext($currentContext);

        return $this;
    }

    public function forAllContexts(callable $callback)
    {
        $currentContext = Filament::currentContext();

        foreach ($this->contexts as $key => $context) {
            Filament::setContext($key);

            $callback();
        }

        Filament::setContext($currentContext);

        return $this;
    }

    public function auth(): Guard
    {
        $context = $this->currentContext();

        return auth()->guard(config("{$context}.auth.guard", config('filament.auth.guard')));
    }



    public static function getPasswordRules(): array
    {
        return static::$passwordRules;
    }

    public static function setPasswordRules(array $rules)
    {
        static::$passwordRules = $rules;
    }



    /**
     * Dynamically handle calls into the filament instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $response = $this->forwardCallTo($this->getContext(), $method, $parameters);

        if ($response instanceof FilamentManager) {
            return $this;
        }

        return $response;
    }
}
