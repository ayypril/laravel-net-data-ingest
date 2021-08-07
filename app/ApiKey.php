<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class ApiKey extends Model
{
    use HasSnowflakePrimary;
    protected $table = 'apikeys';

}
