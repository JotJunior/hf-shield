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
    'client_created_successfully' => 'Cliente creado exitosamente.',
    'save_secret_warning' => 'Guarde este secreto en un lugar seguro. No podrá recuperarlo nuevamente.',
    'user_created_successfully' => 'Usuario creado exitosamente.',
    'scope_created_successfully' => 'Alcance creado exitosamente.',
    'all_scopes_updated_successfully' => 'Todos los alcances actualizados exitosamente.',
    'no_scopes_selected' => 'Ningún alcance seleccionado.',
    'user_not_found' => 'Usuario no encontrado.',
    'scope_already_registered' => 'El alcance :scope ya está registrado.',
    'wrong_client_number' => 'Número de cliente incorrecto.',
    'wrong_tenant_number' => 'Número de inquilino incorrecto.',
    'selected' => 'Seleccionado: :value',
    'field_already_used' => ':label :value ya está en uso.',
    'field_not_found' => ':label :value no encontrado.',
    'passwords_must_match' => 'Las contraseñas deben coincidir.',
    'key_success' => 'Pares de claves creados exitosamente',
    'key_exists' => 'Las claves ya existen, ¿sobrescribir? [s/n]',

    // Form labels and prompts
    'name' => 'Nombre',
    'email' => 'Correo electrónico',
    'phone' => 'Teléfono',
    'federal_document' => 'Documento Federal',
    'password' => 'Contraseña',
    'repeat_password' => 'Repetir Contraseña',
    'redirect_uri' => 'URI de Redirección',
    'client_id' => 'ID de Cliente',
    'client_secret' => 'Secreto de Cliente',
    'tenant_id' => 'ID de Inquilino',
    'username' => 'Nombre de usuario',
    'description' => 'Descripción',
    'add_scope_prompt' => '¿Añadir alcance :scope? (s/n/t)',
    'pick_a_number' => 'Elija un número',
    'client_list_prompt' => 'ID de Cliente: (*) [ENTER para omitir o escriba "-" para obtener la lista de clientes]',
    'tenant_list_prompt' => 'ID de Inquilino: (*) [Escriba "-" para obtener la lista de inquilinos]',
    'data_stream' => 'Crear plantilla de flujo de datos',

    // Commands descriptions
    'action_description' => 'Acción del comando',
    'create_key_pairs_description' => 'Crear pares de claves de cifrado para token OAuth',
    'force' => 'Forzar reemplazo de archivo',
    'keys_path' => 'Directorio de destino para las claves',
    'oauth_client_description' => 'Crear un Cliente OAuth',
    'oauth_scope_description' => 'Crear un alcance OAuth',
    'oauth_tenant_description' => 'Crear un Inquilino OAuth',
    'oauth_user_description' => 'Crear un Usuario OAuth',
    'setup_logger_description' => 'Configurar el registrador de HfShield',

    // Exception messages
    'unauthorized_access' => 'Acceso no autorizado',
    'unauthorized_client' => 'Cliente no autorizado',
    'unauthorized_user' => 'Usuario no autorizado',
    'unauthorized_session' => 'Acceso no autorizado. Por favor, verifique su configuración de sesión.',
    'missing_resource_scope' => 'No se han registrado alcances de autorización para este recurso. Por favor, verifique su configuración.',

    // logger setup
    'no_data_stream_configured' => 'Ningún flujo de datos configurado. Por favor, verifique su configuración.',
    'logger_setup_successfully' => 'Registrador configurado exitosamente.',

    // Messages
    'session_actions' => [
        'list' => 'listado',
        'view' => 'visualizado',
        'create' => 'creado',
        'update' => 'actualizado',
        'delete' => 'eliminado',
        'verify' => 'verificado',
        'read' => 'leído',
    ],
    'log_messages' => [
        'user_create_new' => 'creó un nuevo :resource llamado \':name\'.',
        'user_list_resources' => 'visualizó una lista de :resources.',
        'system_list_resources' => 'El sistema cargó una lista de :resources.',
        'system_view_user' => 'El sistema cargó la información de :user.',
        'user_action_resource' => ':action el :resource.',
        'user_action_resource_name' => ':action el :resource \':name\'.',
    ],
];