<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class Event extends Model
{
    use HasSnowflakePrimary;
    protected $table = 'events';



}
