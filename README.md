# hf-shield

Um módulo para gerenciamento, validação de autenticação e autorização utilizando OAuth 2.0, com suporte robusto para
hierarquia de escopos e fluxo de autenticação.

## Índice

1. [Introdução](#introdução)
2. [Instalação](#instalação)
3. [Configuração](#configuração)
4. [Definindo as permissões](#definindo-as-permissões)

---

## Introdução

O **hf-shield** é um módulo projetado para facilitar a implementação de autenticação e controle de acesso baseado em
escopos. Ele segue as diretrizes do protocolo OAuth 2.0 e é especialmente útil para sistemas distribuídos, multi-tenant
e APIs que demandam hierarquias complexas de permissões.

Com fluxos customizáveis e validações bem definidas, o **hf-shield** oferece uma maneira segura e escalável de garantir
o acesso a recursos, com validações focadas em tokens, usuários e clientes.

## Instalação

Certifique-se de que o seu projeto utiliza o PHP 8.2 ou superior para garantir a compatibilidade total.

Para começar a usar o **hf-shield**, sugerimos primeiro instalar o `hyperf/hyperf-skeleton`.

```shell
composer create-project hyperf/hyperf-skeleton my-project
```

Durante a instalação, aceitar os seguintes pacotes:

- Redis client: `hyperf/redis`
- Config Center: opção 3 ETCD
- AMQP Component `hyperf/amqp`
- Elasticsearch component `hyperf/elasticsearch`

Após a instalação, entre no diretório do projeto e instale este módulo:

```bash
cd my-project
composer require jot/hf-shield
```

---

## Configuração

Após instalar o módulo, é necessário configurá-lo. Verifique se todas as dependências estão instaladas em seu ambiente
antes de iniciar o serviço.

Você pode levantar seu ambiente de desenvolvimento a partir do [docker-composer](./docker-compose.yml) neste
repositório.

### Dependências

#### ETCD

Após instalação do serviço no seu ambiente, execute o comando abaixo:

```shell
php bin/hyperf.php vendor:publish hyperf/etcd
```

#### REDIS

Toda a gestão de cache e rate-limit são armazenadas no Redis. Após a instalação do serviço, execute o comando abaixo:

```shell
php bin/hyperf.php vendor:publish hyperf/redis
```

Com a configuração criada, publique as credenciais no ETCD com o seguinte comando:

```shell
php bin/hyperf.php etcd:put redis
``` 

#### ELASTICSEARCH

Esta aplicação foi construída para usar o Elasticsearch como base de dados principal.

```shell
php bin/hyperf.php vendor:publish jot/hf-elastic
```

Após editar o arquivo `.env` com as credenciais necessárias, registre-as no etcd:

```shell
php bin/hyperf.php etcd:put hf_elastic
``` 

#### SWAGGER

Os comandos de geração de código disponibilizados pelo módulo `jot/hf-repository` já criam controladores, entidades e
repositórios de dados com o básico necessário do Swagger, o que faz com que a aplicação já nasça com suas APIs
documentadas.

```shell
php bin/hyperf.php vendor:publish hyperf/swagger
```

#### RATE-LIMIT

Assim como o swagger, o módulo `jot/hf-repository` também implementa a configuração de _throttling_ da aplicação, que
pode ser configurada globalmente e reimplementada caso a caso nos métodos dos controladores por meio de suas
_annotations_.

```shell
php bin/hyperf.php vendor:publish hyperf/rate-limit
```

#### OAUTH2

E por fim, adicione as configurações para o funcionamento deste módulo:

```shell
php bin/hyperf.php vendor:publish jot/hf-shield
```

**Exemplo de `config/autoload/hf_oauth2.php`:**

```php
return [
    'token_format' => 'JWT',            // formato do token. Por padrão, JWT
    'private_key' => '',                // path ou conteúdo da chave privada
    'public_key' => '',                 // path ou conteúdo da chave pública 
    'encryption_key' => '',             // string para criptografia dos dados
    'token_days' => 'P1D',              // validade do token no padrão DateTimeInterval do php
    'refresh_token_days' => 'P1M',      // validade do refresh token no padrão DateTimeInterval do php
    'revoke_user_old_tokens' => true,   // habilita gatilho que revoga os tokens anteriores do usuário/cliente
];
```

#### MIGRATIONS

Depois de tudo configurado, é hora de executar as migrations para que os índices necessários para o processo de
autenticação sejam criados:

```shell
php bin/hyperf.php elastic:migrate
```

---

## Definindo as permissões

---

### Hierarquia dos escopos de validação da API

O diagrama a seguir descreve como a hierarquia de escopos funciona no **hf-shield**:

```mermaid
flowchart TD
    tenant[Tenant]
    client[Client]
    user[User]
    token[Token]
    scope[Scope]
%% Relações hierárquicas revisadas:
    tenant -->|Possui um ou mais| client
    client -->|É usado para criar| token
    token -->|É criado por| user
    token -->|Contém escopos autorizados do Client| scope
    client -->|Vincula escopos considerando o Tenant| scope
    user -->|Relacionado com escopos do Tenant| scope
```

### Regras de nomenclatura dos escopos

Os escopos devem ser nomeados seguindo o seguinte padrão: `[serviço]:[recurso]:[permissão]`

Exemplos:

```
api-events:event:list
api-events:event:create
api-shopping:order:create
api-shopping:order:update
api-shopping:order:list
```

### Fluxo de autenticação

O fluxo de autenticação esperado pelo **hf-shield** é descrito no diagrama abaixo:

```mermaid
flowchart TD
    start((REQUEST))
    validateToken[Verificar assinatura, validade e metadados do token]
    invalidToken[HTTP 401: UnauthorizedAccessException]
    checkResourceScopes[Verifica se o recurso tem escopos vinculados]
    missingResourceScope[HTTP 400: MissingResourceScopeException]
    checkTokenScopes[Verifica se o token possui os escopos necessários]
    unauthorizedToken[HTTP 401: UnauthorizedAccessException]
    validateClient[Verifica se o cliente é válido e ativo]
    invalidClient[HTTP 401: UnauthorizedClientException]
    validateUser[Verifica se o Usuário é válido, ativo e possui os escopos necessários]
    unauthorizedUser[HTTP 401: UnauthorizedUserException]
    success[HTTP 200: Usuário pode acessar o recurso desejado]
    response((RESPONSE))
%% Fluxo principal
    start --> validateToken
    validateToken -->|Válido| checkResourceScopes
    validateToken -->|Inválido| invalidToken
    checkResourceScopes -->|Vinculado| checkTokenScopes
    checkResourceScopes -->|Não Vinculado| missingResourceScope
    checkTokenScopes -->|Escopos Suficientes| validateClient
    checkTokenScopes -->|Escopos Insuficientes| unauthorizedToken
    validateClient -->|Válido| validateUser
    validateClient -->|Inválido| invalidClient
    validateUser -->|Inválido| unauthorizedUser
    validateUser -->|Válido| success
    success --> response
```

---



