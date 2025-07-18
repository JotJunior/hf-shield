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
    'client_created_successfully' => 'Cliente criado com sucesso.',
    'save_secret_warning' => 'Salve este segredo em um local seguro. Você não poderá recuperá-lo novamente.',
    'user_created_successfully' => 'Usuário criado com sucesso.',
    'scope_created_successfully' => 'Escopo criado com sucesso.',
    'all_scopes_updated_successfully' => 'Todos os escopos atualizados com sucesso.',
    'no_scopes_selected' => 'Nenhum escopo selecionado.',
    'user_not_found' => 'Usuário não encontrado.',
    'scope_already_registered' => 'O escopo :scope já está registrado.',
    'wrong_client_number' => 'Número de cliente incorreto.',
    'wrong_tenant_number' => 'Número de inquilino incorreto.',
    'selected' => 'Selecionado: :value',
    'field_already_used' => ':label :value já está em uso.',
    'field_not_found' => ':label :value não encontrado.',
    'passwords_must_match' => 'As senhas devem coincidir.',
    'key_success' => 'Pares de chaves criados com sucesso',
    'key_exists' => 'As chaves já existem, sobrescrever? [s/n]',
    'document_type' => 'Tipo de documento (cpf|rg|passport|ie|other]',
    'name' => 'Nome',
    'email' => 'E-mail',
    'phone' => 'Telefone',
    'federal_document' => 'Documento Federal',
    'password' => 'Senha',
    'repeat_password' => 'Repetir Senha',
    'redirect_uri' => 'URI de Redirecionamento',
    'client_id' => 'ID do Cliente',
    'client_secret' => 'Segredo do Cliente',
    'tenant_id' => 'ID do Inquilino',
    'username' => 'Nome de usuário',
    'description' => 'Descrição',
    'add_scope_prompt' => 'Adicionar escopo :scope? (s/n/t]',
    'pick_a_number' => 'Escolha um número',
    'client_list_prompt' => 'ID do Cliente: (*] [ENTER para pular ou digite "-" para obter a lista de clientes]',
    'tenant_list_prompt' => 'ID do Inquilino: (*] [Digite "-" para obter a lista de inquilinos]',
    'data_stream' => 'Criar modelo de fluxo de dados',
    'action_description' => 'Ação do comando',
    'create_key_pairs_description' => 'Criar pares de chaves de criptografia para token OAuth',
    'force' => 'Forçar substituição de arquivo',
    'keys_path' => 'Diretório de destino para as chaves',
    'oauth_client_description' => 'Criar um Cliente OAuth',
    'oauth_scope_description' => 'Criar um escopo OAuth',
    'oauth_tenant_description' => 'Criar um Inquilino OAuth',
    'oauth_user_description' => 'Criar um Usuário OAuth',
    'setup_logger_description' => 'Configurar o registrador do HfShield',
    'unauthorized_access' => 'Acesso não autorizado',
    'unauthorized_client' => 'Cliente não autorizado',
    'unauthorized_user' => 'Usuário não autorizado',
    'unauthorized_session' => 'Acesso não autorizado. Por favor, verifique sua configuração de sessão.',
    'missing_resource_scope' => 'Nenhum escopo de autorização foi registrado para este recurso. Por favor, verifique sua configuração.',
    'no_data_stream_configured' => 'Nenhum fluxo de dados configurado. Por favor, verifique sua configuração.',
    'logger_setup_successfully' => 'Registrador configurado com sucesso.',
    'session_actions' => [
        'list' => 'listou',
        'view' => 'visualizou',
        'create' => 'criou',
        'update' => 'atualizou',
        'delete' => 'excluiu',
        'verify' => 'verificou',
        'read' => 'leu',
        'undefined_user' => 'usuário desconhecido',
    ],
    'log_messages' => [
        'user_create_new' => 'criou um novo registro de :resource chamado \':name\'.',
        'user_list_resources' => 'visualizou uma lista de :resources.',
        'system_list_resources' => 'O sistema carregou uma lista de :resources.',
        'system_view_user' => 'O sistema carregou as informações de :user.',
        'user_action_resource' => ':action o recurso :resource.',
        'user_action_resource_name' => ':action o recurso :resource \':name\'.',
        'user_logged_in' => ':username logou com sucesso.',
    ],
    'empty_current_password' => 'A senha atual está vazia.',
    'empty_password' => 'A senha esta vazia.',
    'failed_update_entity' => 'A atualização da entidade falhou.',
    'forbidden_access' => 'Acesso proibido.',
    'invalid_password' => 'Senha inválida.',
    'invalid_public_key_credential' => 'Credenciais de chave publica inválidas.',
    'password_must_not_be_empty' => 'A senha não deve estar vazia.',
    'expired_otp_code' => 'O código de verificação expirou.',
    'invalid_otp_code' => 'Código de verificação inválido.',
    'otp_code_validated' => 'Código de verificação validado com sucesso.',
    'password_changed_successfully' => 'Senha alterada com sucesso.',
    'check_your_phone' => 'Verifique o código de verificação enviado por Whatsapp para o telefone de final :phone.',
    'scopes_synchronized_successfully' => 'Escopos sincronizados com sucesso.',
    'missing_access_token' => 'Token de acesso ausente.',
    'unauthorized_scope' => 'Escopo não autorizado.',
];
