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
    'key_exists' => 'Keys already exist, overwrite? [y/n]',

    // Form labels and prompts
    'name' => 'Name',
    'email' => 'E-mail',
    'phone' => 'Phone',
    'federal_document' => 'Federal Document',
    'password' => 'Password',
    'repeat_password' => 'Repeat Password',
    'redirect_uri' => 'Redirect URI',
    'client_id' => 'Client ID',
    'client_secret' => 'Client Secret',
    'tenant_id' => 'Tenant ID',
    'username' => 'Username',
    'description' => 'Description',
    'add_scope_prompt' => 'Add scope :scope? (y/n/a)',
    'pick_a_number' => 'Pick a number',
    'client_list_prompt' => 'Client ID: (*) [ENTER to skip or type "-" for get the client list]',
    'tenant_list_prompt' => 'Tenant ID: (*) [Type "-" for get the tenant list]',
    'data_stream' => 'Create data stream template',

    // Commands descriptions
    'action_description' => 'Command action',
    'create_key_pairs_description' => 'Create an OAuth token encryption key pairs',
    'force' => 'Force file replacement',
    'keys_path' => 'Destination keys directory',
    'oauth_client_description' => 'Create an OAuth Client',
    'oauth_scope_description' => 'Create an OAuth scope',
    'oauth_tenant_description' => 'Create an OAuth Tenant',
    'oauth_user_description' => 'Create an OAuth User',
    'setup_logger_description' => 'Setup HfShield logger',

    // Exception messages
    'unauthorized_access' => 'Unauthorized access',
    'unauthorized_client' => 'Unauthorized Client',
    'unauthorized_user' => 'Unauthorized User',
    'unauthorized_session' => 'Unauthorized access. Please check your session configuration.',
    'missing_resource_scope' => 'No authorization scopes have been registered for this resource. Please verify your configuration.',

    // logger setup
    'no_data_stream_configured' => 'No data stream configured. Please verify your configuration.',
    'logger_setup_successfully' => 'Logger setup successfully.',

    // Messages
    'session_actions' => [
        'list' => 'listed',
        'view' => 'viewed',
        'create' => 'created',
        'update' => 'updated',
        'delete' => 'deleted',
        'verify' => 'verified',
        'read' => 'read',
    ],
    'log_messages' => [
        'user_create_new' => 'created a new :resource called \':name\'.',
        'user_list_resources' => 'viewed a list of :resources.',
        'system_list_resources' => 'The system load a list of :resources.',
        'system_view_user' => 'The sustem load :user\`s information.',
        'user_action_resource' => ':action the :resource.',
        'user_action_resource_name' => ':action the :resource \':name\'.',
    ],
];
