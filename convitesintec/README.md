# SINTEC 2.0 — Sistema de Convite Interativo

## Arquivos

```
sintec/
├── index.php     ← Página do convite (frontend)
├── rsvp.php      ← API que salva respostas no MySQL
├── admin.php     ← Painel com login para ver respostas
├── db.php        ← Configuração da conexão MySQL
├── sintec2.sql   ← Schema do banco de dados
└── README.md
```

## Instalação passo a passo

### 1. Criar o banco de dados

Abra o phpMyAdmin ou terminal MySQL e rode:

```sql
source /caminho/para/sintec2.sql
```

Ou cole o conteúdo de `sintec2.sql` direto no phpMyAdmin → Aba "SQL".

### 2. Configurar a conexão

Edite `db.php` com seus dados:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sintec2');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
```

### 3. Trocar a senha do admin

O SQL cria um admin com senha padrão `password` (hash bcrypt).
**TROQUE ANTES DE PUBLICAR.** Rode no PHP:

```php
echo password_hash('nova_senha_aqui', PASSWORD_BCRYPT);
```

E atualize no banco:

```sql
UPDATE admins SET password = 'HASH_GERADO' WHERE username = 'admin';
```

Ou crie um novo admin:
```sql
INSERT INTO admins (username, password)
VALUES ('seunome', 'HASH_GERADO');
```

### 4. Subir os arquivos

Envie todos os arquivos para o servidor (FTP, cPanel, etc.).

### 5. Acessar

| Página    | URL                              |
|-----------|----------------------------------|
| Convite   | `https://seusite.com/index.php`  |
| Admin     | `https://seusite.com/admin.php`  |

---

## Funcionalidades do painel admin

- Login com usuário e senha (bcrypt)
- Cards com totais: confirmados, recusados, geral
- Busca por nome em tempo real
- Filtro por tipo de resposta
- Paginação (20 por página)
- Exportar CSV com filtros aplicados
- Logout seguro
