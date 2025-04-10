<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */
return [
    // Command messages
    'client_created_successfully' => 'Client created successfully.',
    'save_secret_warning' => 'Save this secret in a safe place. You will not be able to retrieve it again.',
    'user_created_successfully' => 'User created successfully.',
    'scope_created_successfully' => 'Scope created successfully.',
    'all_scopes_updated_successfully' => 'All scopes updated successfully.',
    'no_scopes_selected' => 'No scopes selected.',
    'user_not_found' => 'User not found.',
    'scope_already_registered' => 'Scope :scope is already registered.',
    'wrong_client_number' => 'Wrong client number.',
    'wrong_tenant_number' => 'Wrong tenant number.',
    'selected' => 'Selected: :value',
    'field_already_used' => ':label :value is already used.',
    'field_not_found' => ':label :value not found.',
    'passwords_must_match' => 'Passwords must match.',
    'key_success' => 'Key pairs created successfully',

    // Form labels and prompts
    'name' => 'Name',
    'email' => 'E-mail',
    'phone' => 'Phone',
    'federal_document' => 'Federal Document',
    'password' => 'Password',
    'repeat_password' => 'Repeat Password',
    'redirect_uri' => 'Redirect URI',
    'client_id' => 'Client ID',
    'tenant_id' => 'Tenant ID',
    'username' => 'Username',
    'description' => 'Description',
    'add_scope_prompt' => 'Add scope :scope? (y/n/a)',
    'pick_a_number' => 'Pick a number',
    'client_list_prompt' => 'Client ID: (*) [ENTER to skip or type "-" for get the client list]',
    'tenant_list_prompt' => 'Tenant ID: (*) [Type "-" for get the tenant list]',

    // Exception messages
    'unauthorized_access' => 'Unauthorized access',
    'unauthorized_client' => 'Unauthorized Client',
    'unauthorized_user' => 'Unauthorized User',
    'unauthorized_session' => 'Unauthorized access. Please check your session configuration.',
    'missing_resource_scope' => 'No authorization scopes have been registered for this resource. Please verify your configuration.',
];
