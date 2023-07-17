<?php

namespace Iotronlab\FilamentMultiGuard\Commands;

use Illuminate\Support\Facades\File;
use Iotronlab\FilamentMultiGuard\Commands\Support\MultiGuardAbstractCommand;

class MultiGuardRemoveCommand extends MultiGuardAbstractCommand
{


    protected $signature = 'clean:multiguard {name?} {--f|force}';



    public function handle():int
    {

        if (is_dir(app_path($this->getCurrentContext())))
        {
            $this->cleanUp();
        }else{
            $this->warn($this->getCurrentContext(). ' context not found!');
        }



        return self::SUCCESS;
    }

    private function cleanUp()
    {
        $this->warn('Scanning For '.$this->getCurrentContext());
        $locations = [
            app_path($this->getCurrentContext()),
            app_path('Http/Livewire/'.$this->getCurrentContext()),
            base_path('config/'.$this->getCurrentContextName($this->getCurrentContext()).'.php'),
//            app_path('Providers/'.$this->getCurrentContext().'ServiceProvider.php')
        ];



        $this->warn('Cleaning Start');

        foreach ($locations as $path) {
            if (File::exists($path)) {
                if (is_dir($path)) {
                    File::deleteDirectory($path);
                } else {
                    File::delete($path);
                }
            }
        }

       // UnRegister Service Provider From App.php
        $provider =  'App\\'.'Providers\\'.$this->getCurrentContext().'ServiceProvider::class';
        $this->unRegisterProvider($provider);
        sleep(1);
        $providerPath = app_path('Providers/'.$this->getCurrentContext().'ServiceProvider.php');
        if (File::exists($providerPath)) {
            File::delete($providerPath);
        }
        $this->info($this->getCurrentContext().' MultiGuard Context Remove Successfully');

        sleep(4);



        $this->warn('Cleaning End');

    }


}
