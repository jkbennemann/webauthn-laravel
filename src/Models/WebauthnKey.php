<?php

namespace Jkbennemann\Webauthn\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebauthnKey extends Model
{
    use HasFactory;

    protected $table = 'webauthn_keys';

    protected $guarded = [];
}
