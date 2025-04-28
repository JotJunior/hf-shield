<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */
use Cose\Algorithms;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;

use function Hyperf\Support\env;

return [
    'creation_profile' => [
        'rp_name' => env('WEBAUTHN_RP_NAME', 'HfShield Application'),
        'rp_id' => env('WEBAUTHN_RP_ID', null), // Default to the hostname of the origin
        'rp_icon' => env('WEBAUTHN_RP_ICON', null),
        'origin' => env('WEBAUTHN_ORIGIN', 'https://localhost'),
        'challenge_length' => env('WEBAUTHN_CHALLENGE_LENGTH', 32),
        'timeout' => env('WEBAUTHN_TIMEOUT', 60000),
        'authenticator_selection_criteria' => [
            'attachment_mode' => env('WEBAUTHN_AUTHENTICATOR_ATTACHMENT', AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_NO_PREFERENCE),
            'require_resident_key' => false,
            'user_verification' => env('WEBAUTHN_AUTHENTICATOR_SELECTION', AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED),
        ],
        'extensions' => [
            'loc' => true,
        ],
        'public_key_credential_parameters' => [ # You should not change this list
            Algorithms::COSE_ALGORITHM_ES256K,
            Algorithms::COSE_ALGORITHM_ES384,
            Algorithms::COSE_ALGORITHM_ES256,
            Algorithms::COSE_ALGORITHM_ES512,
            Algorithms::COSE_ALGORITHM_RS256,
            Algorithms::COSE_ALGORITHM_RS384,
            Algorithms::COSE_ALGORITHM_RS512,
        ],
        'attestation_conveyance' => env('WEBAUTHN_ATTESTATION_CONVEYANCE', PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE),
    ],
    'request_profile' => [
        'rp_id' => env('WEBAUTHN_RP_ID', null), // Default to the hostname of the origin
        'challenge_length' => env('WEBAUTHN_CHALLENGE_LENGTH', 32),
        'timeout' => env('WEBAUTHN_TIMEOUT', 60000),
        'user_verification' => env('WEBAUTHN_AUTHENTICATOR_SELECTION', AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED),
        'extensions' => [
            'loc' => true,
        ],
    ],
    'metadata' => [
        'enabled' => false,
    ],
];
