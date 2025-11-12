# Sistema de Gestão de Estoque para Mercado

Um sistema completo de gestão de estoque desenvolvido em PHP puro seguindo o padrão MVC, conforme especificações da atividade prática.

## 📋 Características do Sistema

### Tecnologias Utilizadas
- **PHP Puro** (sem frameworks)
- **MySQL** com PDO
- **HTML5, CSS3, JavaScript** vanilla
- **Bootstrap 5.1.3** para interface responsiva
- **Font Awesome 6.0** para ícones

### Arquitetura
- **Padrão MVC** (Model-View-Controller)
- **Front Controller** (ponto único de entrada)
- **Singleton** para conexão com banco
- **Prepared Statements** para segurança
- **Transações PDO** para integridade

### Funcionalidades Principais
- ✅ Sistema de autenticação com sessões
- ✅ Gerenciamento completo de produtos
- ✅ Controle de movimentações de estoque
- ✅ Dashboard com estatísticas em tempo real
- ✅ Alertas de estoque baixo
- ✅ Exportação de relatórios em CSV
- ✅ Interface responsiva
- ✅ Bubble Sort para ordenação alfabética
- ✅ Proteção contra SQL Injection e XSS

## 🚀 Instalação e Configuração

### Pré-requisitos
- **PHP 7.4+** com extensões PDO e MySQL
- **MySQL 5.7+** ou **MariaDB 10.3+**
- **Servidor Web** (Apache, Nginx)
- **mod_rewrite** habilitado (Apache)

### Passos de Instalação

1. **Clone ou baixe o sistema:**
   ```bash
   # Copie a pasta sistema_mercado para o diretório do servidor web
   # Ex: /var/www/html/sistema_mercado ou C:\xampp\htdocs\sistema_mercado
   ```

2. **Configure o banco de dados:**
   - Edite o arquivo `app/config/database.php`
   - Ajuste as credenciais de conexão:
   ```php
   private $host = 'localhost';
   private $database = 'mercado_db';
   private $username = 'seu_usuario';
   private $password = 'sua_senha';
   ```

3. **Execute o script de banco:**
   ```sql
   # Importe o arquivo script_banco.sql no MySQL
   mysql -u root -p < script_banco.sql
   # Ou execute via phpMyAdmin/HeidiSQL
   ```

4. **Configure o servidor web:**
   - Configure o DocumentRoot para a pasta `public/`
   - Certifique-se de que mod_rewrite está habilitado
   - O arquivo `.htaccess` já está configurado

5. **Configure permissões (Linux):**
   ```bash
   chmod -R 755 sistema_mercado/
   chown -R www-data:www-data sistema_mercado/
   ```

### Configuração do Virtual Host (Apache)

```apache
<VirtualHost *:80>
    ServerName sistema-mercado.local
    DocumentRoot /caminho/para/sistema_mercado/public
    
    <Directory "/caminho/para/sistema_mercado/public">
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/sistema-mercado_error.log
    CustomLog ${APACHE_LOG_DIR}/sistema-mercado_access.log combined
</VirtualHost>
```

## 👥 Usuários de Teste

O sistema inclui usuários pré-cadastrados para teste:

| Usuário | Senha | Nome | Perfil |
|---------|-------|------|--------|
| `admin` | `123456` | Administrador | Administrador |
| `maria` | `123456` | Maria Silva | Usuário |
| `joao` | `123456` | João Santos | Usuário |
| `ana` | `123456` | Ana Costa | Usuário |

## 🗂️ Estrutura do Projeto

```
sistema_mercado/
├── app/
│   ├── config/
│   │   └── database.php          # Configuração do banco
│   ├── controllers/
│   │   ├── LoginController.php   # Autenticação
│   │   ├── HomeController.php    # Dashboard
│   │   ├── ProdutosController.php # Gestão de produtos
│   │   └── EstoqueController.php # Gestão de estoque
│   ├── models/
│   │   ├── Usuario.php          # Model de usuários
│   │   ├── Produto.php          # Model de produtos
│   │   └── Movimentacao.php     # Model de movimentações
│   └── views/
│       ├── login.php            # Tela de login
│       ├── home.php             # Dashboard
│       ├── produtos/
│       │   └── listar.php       # Listagem de produtos
│       └── estoque/
│           └── index.php        # Gestão de estoque
├── public/
│   ├── index.php                # Front Controller
│   ├── .htaccess               # Configurações Apache
│   └── css/
│       └── style.css           # Estilos customizados
├── script_banco.sql            # Script de criação do banco
├── requisitos_funcionais.txt   # Requisitos do sistema (87 itens)
├── DER_descricao.txt          # Descrição do modelo de dados
├── casos_de_teste.txt         # Casos de teste (35 cenários)
└── README.md                  # Este arquivo
```

## 🎯 Funcionalidades Detalhadas

### 🔐 Sistema de Autenticação
- Login seguro com senha criptografada
- Controle de sessões
- Proteção de rotas
- Logout com limpeza completa da sessão

### 📦 Gestão de Produtos
- Cadastro com validação completa
- Busca por nome, código de barras ou marca
- Filtro por categoria
- Edição e exclusão
- Status visual do estoque (OK, Baixo, Esgotado)
- Exportação em CSV

### 📊 Controle de Estoque
- Movimentações de entrada e saída
- Validação de estoque disponível
- Transações PDO para integridade
- Histórico completo de movimentações
- Reversão de movimentações incorretas
- Alertas automáticos de estoque baixo
- Ordenação alfabética com Bubble Sort

### 🏠 Dashboard Interativo
- Estatísticas em tempo real
- Produtos com estoque baixo
- Últimas movimentações
- Gráficos por categoria
- Indicadores visuais

## 🔒 Segurança Implementada

### Proteções
- **SQL Injection:** Prepared statements em todas as consultas
- **XSS:** htmlspecialchars() em todas as saídas
- **CSRF:** Tokens de validação
- **Autenticação:** Senhas com password_hash()
- **Sessões:** Regeneração de ID após login
- **Headers:** Configurações de segurança no .htaccess

### Validações
- Cliente (HTML5) e servidor (PHP)
- Sanitização de dados de entrada
- Verificação de tipos de dados
- Controle de acesso por rotas

## 🧪 Testes

O arquivo `casos_de_teste.txt` contém 35 casos de teste cobrindo:
- Autenticação e segurança
- CRUD de produtos
- Movimentações de estoque
- Interface e usabilidade
- Proteções contra ataques
- Performance e transações

## 📱 Responsividade

- Interface adaptável para desktop, tablet e mobile
- Bootstrap 5.1.3 para componentes responsivos
- Testes em diferentes resoluções
- Menu colapsável em dispositivos móveis

## ⚡ Performance

- Conexão singleton com banco
- Índices otimizados nas tabelas
- Cache de arquivos estáticos
- Compressão GZIP habilitada
- Consultas otimizadas com JOINs

## 🔧 Configurações Avançadas

### Produção
1. Alterar credenciais do banco
2. Desabilitar exibição de erros PHP
3. Configurar SSL/HTTPS
4. Ajustar limites de upload
5. Implementar backup automático

### Desenvolvimento
1. Habilitar logs de erro
2. Configurar xdebug
3. Usar banco de desenvolvimento
4. Ativar modo de debug

## 📚 Documentação Técnica

- **requisitos_funcionais.txt:** 87 requisitos funcionais detalhados
- **DER_descricao.txt:** Modelo de dados completo com relacionamentos
- **casos_de_teste.txt:** 35 casos de teste para validação

## 🆘 Solução de Problemas

### Problemas Comuns

1. **Erro de conexão com banco:**
   - Verificar credenciais em `app/config/database.php`
   - Confirmar se MySQL está rodando
   - Verificar se banco `mercado_db` foi criado

2. **Erro 404 nas páginas:**
   - Verificar se mod_rewrite está habilitado
   - Conferir DocumentRoot do servidor
   - Validar arquivo `.htaccess`

3. **Sessão não funciona:**
   - Verificar permissões da pasta de sessões
   - Confirmar configurações de sessão no PHP
   - Checar se cookies estão habilitados

4. **Erro de prepared statements:**
   - Verificar extensão PDO MySQL
   - Confirmar versão do PHP (7.4+)
   - Validar sintaxe das consultas

## 📞 Suporte

Para dúvidas sobre implementação ou configuração:
1. Consulte os arquivos de documentação
2. Verifique os casos de teste
3. Analise os logs de erro do sistema
4. Revise as configurações do ambiente

## 📋 Checklist de Implementação

- [x] Arquitetura MVC implementada
- [x] Front Controller configurado
- [x] 3 tabelas principais criadas
- [x] Relacionamentos com foreign keys
- [x] 4 controllers principais
- [x] 3 models com operações CRUD
- [x] 4 views responsivas
- [x] Sistema de autenticação completo
- [x] Bubble Sort implementado
- [x] Transações PDO para estoque
- [x] Prepared statements em todas consultas
- [x] Proteção XSS com htmlspecialchars
- [x] Flash messages implementadas
- [x] Exportação CSV funcional
- [x] Alertas de estoque baixo
- [x] Documentação completa
- [x] 87 requisitos funcionais atendidos
- [x] 35 casos de teste documentados

## 🎉 Sistema Completo e Funcional!

O sistema está 100% implementado conforme especificações da atividade, incluindo todas as funcionalidades obrigatórias, documentação completa e casos de teste abrangentes.

---

**Desenvolvido em:** Novembro 2025  
**Tecnologias:** PHP, MySQL, Bootstrap, JavaScript  
**Padrão:** MVC com Front Controller  
**Segurança:** SQL Injection e XSS protegidos  
**Interface:** Responsiva e moderna