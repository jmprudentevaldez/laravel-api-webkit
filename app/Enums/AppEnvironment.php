<?php

namespace App\Enums;

enum AppEnvironment: string
{
    case PRODUCTION = 'production';
    case UAT = 'uat';
    case DEVELOPMENT = 'development';
    case LOCAL = 'local';
    case TESTING = 'testing';
}
