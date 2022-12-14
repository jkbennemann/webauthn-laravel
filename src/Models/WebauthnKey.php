<?php

namespace Jkbennemann\Webauthn\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $signatureCounter
 * @property string $alias
 * @property string $credentialId
 * @property string $aaguid
 * @property string $attestationFormat
 * @property string $certificate
 * @property string $credentialPublicKey
 * @property string $certificateChain
 * @property string $certificateIssuer
 * @property string $certificateSubject
 * @property bool $rootValid
 * @property bool $userPresent
 * @property bool $userVerified
 * @property Carbon $lastLogin
 */
class WebauthnKey extends Model
{
    use HasFactory;

    protected $table = 'webauthn_keys';

    protected $guarded = [];

    protected $dates = [
        'lastLogin',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('webauthn.model'));
    }

    public function trackLogin(): void
    {
        $this->lastLogin = now();

        $this->save();
    }
}
