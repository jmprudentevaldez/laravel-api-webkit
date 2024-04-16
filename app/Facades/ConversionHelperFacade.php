<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class ConversionHelperFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ConversionHelper';
    }
}
