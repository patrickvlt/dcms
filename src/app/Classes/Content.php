<?php

namespace Pveltrop\DCMS\Classes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;

    protected $table = 'dcms_content';

    protected $primaryKey = 'UID';

    protected $guarded = ['id'];
}
