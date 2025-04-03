<?php

declare(strict_types=1);
/**
 * This file is part of hf-shield.
 *
 * @link     https://github.com/JotJunior/hf-shield
 * @contact  hf-shield@jot.com.br
 * @license  MIT
 */
return [
    // Command messages
    'client_created_successfully' => 'Cliente criado com sucesso.',
    'save_secret_warning' => 'Salve este segredo em um local seguro. Você não poderá recuperá-lo novamente.',
    'user_created_successfully' => 'Usuário criado com sucesso.',
    'scope_created_successfully' => 'Escopo criado com sucesso.',
    'all_scopes_updated_successfully' => 'Todos os escopos foram atualizados com sucesso.',
    'no_scopes_selected' => 'Nenhum escopo selecionado.',
    'user_not_found' => 'Usuário não encontrado.',
    'scope_already_registered' => 'O escopo :scope já está registrado.',
    'wrong_client_number' => 'Número de cliente incorreto.',
    'wrong_tenant_number' => 'Número de tenant incorreto.',
    'selected' => 'Selecionado: :value',
    'field_already_used' => ':label :value já está em uso.',
    'field_not_found' => ':label :value não encontrado.',
    'passwords_must_match' => 'As senhas devem coincidir.',

    // Form labels and prompts
    'name' => 'Nome',
    'email' => 'E-mail',
    'phone' => 'Telefone',
    'federal_document' => 'Documento Federal',
    'password' => 'Senha',
    'repeat_password' => 'Repetir Senha',
    'redirect_uri' => 'URI de Redirecionamento',
    'client_id' => 'ID do Cliente',
    'tenant_id' => 'ID do Tenant',
    'username' => 'Nome de usuário',
    'description' => 'Descrição',
    'add_scope_prompt' => 'Adicionar escopo :scope? (s/n)',
    'pick_a_number' => 'Escolha um número',
    'client_list_prompt' => 'ID do Cliente: (*) [ENTER para pular ou digite "-" para obter a lista de clientes]',
    'tenant_list_prompt' => 'ID do Tenant: (*) [Digite "-" para obter a lista de tenants]',

    // Exception messages
    'unauthorized_access' => 'Acesso não autorizado',
    'unauthorized_client' => 'Cliente não autorizado',
    'unauthorized_user' => 'Usuário não autorizado',
    'unauthorized_session' => 'Acesso não autorizado. Por favor, verifique sua configuração de sessão.',
    'missing_resource_scope' => 'Nenhum escopo de autorização foi registrado para este recurso. Por favor, verifique sua configuração.',
];
