# GUIA DE ESTUDO - SISTEMA DE BIBLIOTECA

## SOBRE ESTE DOCUMENTO

Este guia tem como objetivo orientar seu processo de estudo e desenvolvimento do projeto. Ele NÃO contém respostas prontas, mas sim:

- A ordem recomendada de construção do projeto
- Perguntas-guia para estimular seu raciocínio
- Conceitos-chave que você precisa dominar
- Referências aos arquivos deste repositório para consulta

**IMPORTANTE:** Em projetos reais, o tema pode variar, mas a estrutura e os conceitos serão similares. Use este projeto como referência, não como modelo a ser copiado.

### NOTA SOBRE ESTILIZAÇÃO (CSS)

Os arquivos CSS deste projeto foram **intencionalmente esvaziados**. Você encontrará apenas comentários orientadores sobre o que PODE ser estilizado, mas nenhuma implementação pronta.

**Por quê?**
- A estilização faz parte da sua avaliação
- Copiar estilos prontos não demonstra aprendizado
- Cada aluno deve criar sua própria identidade visual
- Desenvolver CSS do zero reforça o entendimento de layout e design

**O que fazer?**
- Leia os comentários no arquivo `sistema/public/css/style.css`
- Crie seus próprios estilos baseando-se nas sugestões
- Pesquise sobre Flexbox, Grid, responsividade
- Pense na experiência do usuário (cores, contrastes, espaçamentos)

---

## 1. ESTRUTURA RECOMENDADA DE DESENVOLVIMENTO

### 1.1 Ordem de Construção

```
1. Banco de Dados (Schema)
   ↓
2. Configuração e Conexão com BD
   ↓
3. Models (Camada de Dados)
   ↓
4. Controllers (Camada de Lógica)
   ↓
5. Views (Camada de Apresentação)
   ↓
6. Roteador (Front Controller)
   ↓
7. Testes e Refinamentos
```

### 1.2 Por Que Esta Ordem?

**Banco de Dados primeiro:**
- Define a estrutura de dados que todo o sistema usará
- Estabelece as relações entre entidades
- Previne retrabalho nas camadas superiores

**Configuração e Conexão:**
- Permite testar a conexão antes de escrever código complexo
- Centraliza configurações (evita duplicação)

**Models antes de Controllers:**
- Os Controllers dependem dos Models para acessar dados
- Models encapsulam a lógica de negócio e acesso ao BD
- Facilita testes isolados de cada componente

**Views por último:**
- Precisa dos Controllers para processar dados
- É a camada mais fácil de ajustar visualmente
- Permite focar primeiro na lógica, depois na apresentação

**Roteador integra tudo:**
- Conecta requisições às ações apropriadas
- Define o fluxo de navegação do sistema

---

## 2. ETAPAS DETALHADAS DE DESENVOLVIMENTO

### ETAPA 1: MODELAGEM E CRIAÇÃO DO BANCO DE DADOS

#### O que você precisa fazer:

**Análise dos Requisitos:**
- Identificar as entidades principais do sistema
- Mapear os relacionamentos entre entidades
- Definir os atributos de cada entidade

#### Perguntas-guia:

1. **Identificação de Entidades:**
   - Quais são os "substantivos" principais do sistema?
   - Que informações precisam ser armazenadas permanentemente?
   - Existe relação de "muitos para muitos" ou "um para muitos"?

2. **Definição de Campos:**
   - Que campos são obrigatórios (NOT NULL)?
   - Que campos precisam ser únicos (UNIQUE)?
   - Que tipo de dado é mais apropriado (INT, VARCHAR, DATE, ENUM)?
   - Precisa de campos de auditoria (data_cadastro, data_atualizacao)?

3. **Relacionamentos:**
   - Como uma entidade se relaciona com outra?
   - Precisa de Foreign Keys para garantir integridade?
   - O que acontece se um registro pai for excluído (CASCADE, RESTRICT)?

4. **Valores Padrão:**
   - Que campos podem ter valores DEFAULT?
   - Faz sentido usar AUTO_INCREMENT?
   - Precisa de timestamps automáticos?

#### Arquivos de Referência:
- `ENTREGA_02_DER_descricao.txt` - Documentação do modelo de dados
- `ENTREGA_03_script_banco.sql` - Script SQL completo


### ETAPA 2: CONFIGURAÇÃO E CONEXÃO COM O BANCO

#### O que você precisa fazer:

**Arquivo de Configuração:**
- Criar arquivo centralizado com dados de conexão
- Implementar função de conexão com tratamento de erros
- Adicionar funções auxiliares (sanitização, validação de sessão)

#### Perguntas-guia:

1. **PDO vs MySQLi:**
   - Por que usar PDO em vez de mysqli_*?
   - Quais as vantagens de prepared statements?
   - Como configurar PDO para lançar exceções?

2. **Segurança:**
   - Como prevenir SQL Injection desde o início?
   - Que modo de fetch usar (FETCH_ASSOC, FETCH_OBJ)?
   - Por que desabilitar emulação de prepared statements?

3. **Organização:**
   - Onde armazenar credenciais de banco?
   - Como deixar o código portável (fácil de mudar de ambiente)?
   - Que funções auxiliares são reutilizáveis em todo o projeto?

#### Arquivos de Referência:
- `sistema/config/database.php`

---

### ETAPA 3: DESENVOLVIMENTO DOS MODELS

#### O que você precisa fazer:

**Para cada entidade, criar um Model que:**
- Represente os dados da tabela
- Implemente operações CRUD (Create, Read, Update, Delete)
- Contenha validações de negócio
- Trate exceções de banco de dados

#### Perguntas-guia:

1. **Estrutura do Model:**
   - Que propriedades privadas/protected preciso?
   - Como receber a conexão PDO no construtor?
   - Que métodos públicos devem ser expostos?

2. **Operações CRUD:**
   - **Create:** Como validar dados antes de inserir? Que exceções capturar?
   - **Read:** Preciso de busca simples (por ID) e busca complexa (filtros)?
   - **Update:** Como garantir que o registro existe antes de atualizar?
   - **Delete:** Preciso verificar dependências antes de excluir?

3. **Validações:**
   - Que regras de negócio existem (ex: estoque não pode ser negativo)?
   - Onde validar: no Model, no Controller, ou em ambos?
   - Como retornar mensagens de erro úteis?

4. **Casos Especiais:**
   - Preciso de métodos customizados além do CRUD básico?
   - Existem cálculos ou lógica complexa (ex: atualizar estoque)?
   - Como lidar com transações (operações que precisam ser atômicas)?

#### Arquivos de Referência:
- `sistema/models/Usuario.php` - Autenticação
- `sistema/models/Livro.php` - CRUD completo + validações
- `sistema/models/Movimentacao.php` - Transações e alertas
---

### ETAPA 4: DESENVOLVIMENTO DOS CONTROLLERS

#### O que você precisa fazer:

**Para cada área funcional, criar um Controller que:**
- Receba requisições (GET/POST)
- Valide dados de entrada
- Chame os Models apropriados
- Envie feedback ao usuário (mensagens de sucesso/erro)
- Redirecione para as views corretas

#### Perguntas-guia:

1. **Organização:**
   - Como organizar Controllers (um por entidade ou por funcionalidade)?
   - Que verificações fazer antes de processar uma ação?
   - Como verificar se o usuário está autenticado?

2. **Processamento de Requisições:**
   - Como diferenciar GET (exibir) de POST (processar)?
   - Como capturar dados de formulários ($_POST)?
   - Como validar se todos campos obrigatórios foram enviados?

3. **Interação com Models:**
   - Como instanciar os Models necessários?
   - Como passar dados do formulário para o Model?
   - Como tratar retornos de sucesso vs erro?

4. **Feedback ao Usuário:**
   - Como armazenar mensagens temporárias (flash messages)?
   - Que informações incluir na mensagem (sucesso, erro, detalhes)?
   - Como redirecionar após processar uma ação (POST-Redirect-GET)?

#### Arquivos de Referência:
- `sistema/controllers/LoginController.php` - Autenticação e sessões
- `sistema/controllers/LivrosController.php` - CRUD completo
- `sistema/controllers/EstoqueController.php` - Lógica de movimentação
- `sistema/controllers/HomeController.php` - Página inicial simples

---

### ETAPA 5: DESENVOLVIMENTO DAS VIEWS

#### O que você precisa fazer:

**Para cada funcionalidade, criar Views que:**
- Exibam dados de forma clara e organizada
- Contenham formulários para entrada de dados
- Mostrem mensagens de feedback
- Sejam responsivas e acessíveis

#### Perguntas-guia:

1. **Estrutura HTML:**
   - Como organizar a estrutura semântica (header, main, footer)?
   - Que elementos HTML5 usar (nav, section, article)?
   - Como garantir acessibilidade (labels, aria-*)?

2. **Formulários:**
   - Que tipos de input usar (text, number, date, select)?
   - Como adicionar validações HTML5 (required, pattern, min, max)?
   - Como pré-preencher campos em edição (value="<?= ... ?>")?
   - Como prevenir XSS ao exibir dados do usuário?

3. **Exibição de Dados:**
   - Como iterar sobre arrays de resultados (foreach)?
   - Como formatar datas, números, moedas?
   - Como exibir mensagens condicionalmente (if/else)?
   - Como destacar visualmente situações especiais (alertas, avisos)?

4. **Interatividade:**
   - Preciso de JavaScript para funcionalidades dinâmicas?
   - Como abrir/fechar modals?
   - Como validar formulários no cliente antes de enviar?
   - Como fazer buscas dinâmicas?

#### Arquivos de Referência:
- `sistema/views/login.php` - Formulário simples
- `sistema/views/home.php` - Menu principal com cards
- `sistema/views/livros/listar.php` - CRUD com modal e busca
- `sistema/views/estoque/index.php` - Formulários + histórico + alertas

---

### ETAPA 6: ROTEAMENTO (FRONT CONTROLLER)

#### O que você precisa fazer:

**Criar um Front Controller que:**
- Seja o único ponto de entrada da aplicação
- Inicialize sessões e configurações
- Direcione requisições aos Controllers apropriados
- Faça autoload de classes

#### Perguntas-guia:

1. **Estrutura:**
   - Que inicializações são necessárias (session, configs)?
   - Como mapear URLs para ações?
   - Como passar parâmetros adicionais (ID de registro, etc)?

2. **Roteamento:**
   - Como capturar a ação solicitada ($_GET['action'])?
   - Como definir uma rota padrão (página inicial)?
   - Como organizar as rotas (switch, array de rotas)?

3. **Autoload:**
   - Como carregar automaticamente Models e Controllers?
   - Preciso de um autoloader PSR-4 ou simples require?

4. **Configuração de Servidor:**
   - Como configurar URL rewriting (.htaccess)?
   - Como remover index.php da URL?
   - Como tratar erros 404?

#### Arquivos de Referência:
- `sistema/public/index.php` - Front Controller completo
- `sistema/public/.htaccess` - Configuração Apache

---

### ETAPA 7: TESTES E REFINAMENTOS

#### O que você precisa fazer:

**Testar sistematicamente:**
- Cada operação CRUD
- Validações de formulário
- Mensagens de erro e sucesso
- Casos extremos (boundary cases)
- Segurança (SQL Injection, XSS, CSRF)

#### Perguntas-guia:

1. **Testes Funcionais:**
   - Todas as operações CRUD funcionam?
   - As validações estão funcionando (campos obrigatórios, formatos)?
   - As mensagens de feedback são claras?
   - Os redirecionamentos estão corretos?

2. **Testes de Segurança:**
   - Como testar proteção contra SQL Injection?
   - Como verificar se dados são escapados corretamente (XSS)?
   - As rotas protegidas impedem acesso não autorizado?
   - As senhas estão sendo armazenadas com hash?

3. **Testes de Casos Extremos:**
   - O que acontece com strings vazias?
   - O que acontece com números negativos onde não deveria?
   - Como o sistema lida com duplicatas?
   - Como o sistema trata dependências (excluir registro com relacionamentos)?

4. **Refinamentos:**
   - O código está legível e bem comentado?
   - Existem duplicações que podem ser refatoradas?
   - As mensagens de erro são úteis para o usuário?
   - A interface é intuitiva?

#### Arquivos de Referência:
- `ENTREGA_08_casos_de_teste.txt` - 18 casos de teste documentados

---

## 3. CHECKLIST DE FUNCIONALIDADES

Use esta lista para verificar se implementou todas as funcionalidades essenciais. Para cada item, indique os arquivos envolvidos e conceitos utilizados.

### 3.1 Sistema de Autenticação

- [ ] **Login de usuário**
  - Arquivos: `LoginController.php`, `Usuario.php`, `login.php`
  - Conceitos: Sessões PHP, password_verify, prepared statements

- [ ] **Proteção de rotas**
  - Arquivos: `database.php` (verificarLogin), todos os Controllers
  - Conceitos: Middleware, verificação de sessão, redirecionamento

- [ ] **Logout**
  - Arquivos: `LoginController.php`
  - Conceitos: session_destroy, redirecionamento

- [ ] **Mensagens de erro de autenticação**
  - Arquivos: `LoginController.php`, `login.php`
  - Conceitos: Flash messages, feedback ao usuário

### 3.2 Gestão de Entidade Principal (ex: Livros)

- [ ] **Listagem de registros**
  - Arquivos: `LivrosController.php`, `Livro.php`, `livros/listar.php`
  - Conceitos: SELECT, fetch, loops PHP

- [ ] **Ordenação de registros**
  - Arquivos: `Livro.php` (método ordenarPorTitulo)
  - Conceitos: Bubble Sort, algoritmos de ordenação

- [ ] **Busca/Filtro de registros**
  - Arquivos: `Livro.php` (método buscar), `livros/listar.php`
  - Conceitos: LIKE, wildcards, concatenação de condições SQL

- [ ] **Cadastro de novo registro**
  - Arquivos: `LivrosController.php`, `Livro.php`, `livros/listar.php` (modal)
  - Conceitos: INSERT, validações, prepared statements

- [ ] **Edição de registro existente**
  - Arquivos: `LivrosController.php`, `Livro.php`, `livros/listar.php` (modal)
  - Conceitos: UPDATE, buscar por ID, pré-preencher formulário

- [ ] **Exclusão de registro**
  - Arquivos: `LivrosController.php`, `Livro.php`, `livros/listar.php`
  - Conceitos: DELETE, confirmação JavaScript, CASCADE

- [ ] **Validações**
  - Arquivos: `Livro.php` (método validar)
  - Conceitos: Validação de campos obrigatórios, unicidade, ranges

### 3.3 Gestão de Movimentações (Operações Complexas)

- [ ] **Registro de movimentações**
  - Arquivos: `EstoqueController.php`, `Movimentacao.php`, `estoque/index.php`
  - Conceitos: INSERT, relacionamentos, validações

- [ ] **Atualização automática de estoque**
  - Arquivos: `Movimentacao.php`, `Livro.php`
  - Conceitos: Transações, COMMIT/ROLLBACK, cálculos

- [ ] **Sistema de alertas**
  - Arquivos: `Movimentacao.php` (verificarEstoqueBaixo)
  - Conceitos: Lógica condicional, comparação de valores

- [ ] **Histórico de movimentações**
  - Arquivos: `Movimentacao.php` (listarHistorico), `estoque/index.php`
  - Conceitos: JOIN, ordenação DESC, LIMIT

### 3.4 Interface do Usuário

- [ ] **Página inicial/Menu**
  - Arquivos: `HomeController.php`, `home.php`
  - Conceitos: Navegação, cards, links

- [ ] **Design responsivo**
  - Arquivos: `public/css/style.css`
  - Conceitos: CSS Grid, Flexbox, media queries

- [ ] **Modals dinâmicos**
  - Arquivos: `livros/listar.php`
  - Conceitos: JavaScript, manipulação DOM, eventos

- [ ] **Indicadores visuais**
  - Arquivos: `livros/listar.php`, `estoque/index.php`
  - Conceitos: Classes CSS condicionais, cores semafóricas

- [ ] **Mensagens de feedback**
  - Arquivos: Todos os Controllers, todas as Views
  - Conceitos: Flash messages, exibição condicional

---

## 4. DICAS PRATICAS SOBRE ESTILIZACAO

Como os arquivos CSS foram zerados intencionalmente, aqui estao orientacoes especificas sobre como abordar a estilizacao:

### 4.1 Por Onde Comecar?

1. **Reset CSS Basico:**
   - Comece removendo margins e paddings padrao do navegador
   - Configure box-sizing: border-box para facilitar calculos
   - Defina uma fonte padrao para o body

2. **Definir Paleta de Cores:**
   - Escolha 2-3 cores principais (tema do sistema)
   - Defina cores para estados: sucesso (verde), erro (vermelho), alerta (amarelo)
   - Pense em contraste (texto deve ser legivel sobre o fundo)

3. **Layout Geral:**
   - Como sera o container principal? Centralizado? Largura maxima?
   - Qual sera o espacamento geral (margins, paddings)?
   - Qual altura minima da pagina?

### 4.2 Prioridades de Estilizacao

**Nivel 1 - Essencial (minimo para funcionar):**
- Formularios (inputs, labels, botoes)
- Tabelas (borders, espacamento, headers)
- Alertas/mensagens (cores que comuniquem o tipo)
- Espacamento basico (nao deixar tudo colado)

**Nivel 2 - Importante (melhora muito a UX):**
- Estados de hover em botoes e links
- Layout responsivo basico
- Header/cabecalho estilizado
- Cards ou paineis de conteudo

**Nivel 3 - Bonus (diferencial):**
- Modals com overlay
- Animacoes sutis (transicoes)
- Indicadores visuais avancados (badges, status)
- Responsividade completa (mobile, tablet, desktop)

### 4.3 Tecnicas Recomendadas

**Flexbox para Alinhamento:**
```css
/* Use Flexbox para alinhar elementos horizontal ou verticalmente */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
```

**Grid para Layouts:**
```css
/* Use Grid para criar layouts de cards/menus */
.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}
```

**Variaveis CSS (opcional, mas util):**
```css
:root {
    --cor-principal: #667eea;
    --cor-sucesso: #28a745;
    --cor-erro: #dc3545;
}

.btn-primary {
    background: var(--cor-principal);
}
```

### 4.4 Checklist de Estilizacao

Antes de considerar a estilizacao completa, verifique:

- [ ] Formularios sao legiveis e usaveis
- [ ] Botoes tem aparencia clicavel e estados de hover
- [ ] Tabelas tem headers visiveis e linhas separadas
- [ ] Mensagens de sucesso/erro sao claramente distinguiveis
- [ ] Espacamento adequado (nada colado, nada muito espalhado)
- [ ] Funciona em resolucao desktop (1920x1080 e 1366x768)
- [ ] Contraste adequado entre texto e fundo
- [ ] Links sao identificaveis (cor diferente ou sublinhado)

### 4.5 Recursos para Estudo

- **Flexbox:** CSS-Tricks Flexbox Guide
- **Grid:** CSS-Tricks Grid Guide
- **Cores:** Adobe Color, Coolors (geradores de paleta)
- **Inspiracao:** Dribbble, Behance (busque por "dashboard" ou "admin panel")

---

## 5. CONCEITOS-CHAVE POR CATEGORIA

### 5.1 Banco de Dados

**Fundamentais:**
- Modelagem de dados (entidades, atributos, relacionamentos)
- Normalizacao (evitar redundancia e anomalias)
- Chaves primarias e estrangeiras
- Constraints (UNIQUE, NOT NULL, CHECK)
- Tipos de dados apropriados

**Avancados:**
- Transacoes (BEGIN, COMMIT, ROLLBACK)
- Integridade referencial (CASCADE, RESTRICT)
- Indices para performance
- JOINs (INNER, LEFT, RIGHT)
- Subconsultas

### 5.2 PHP

**Fundamentais:**
- Sintaxe basica (variaveis, arrays, loops, condicionais)
- Funcoes e parametros
- Superglobais ($_POST, $_GET, $_SESSION)
- Include/require
- Tratamento de strings (trim, strlen, etc)

**Intermediarios:**
- Orientacao a objetos (classes, metodos, propriedades)
- PDO (conexao, prepared statements, fetch)
- Sessoes (session_start, $_SESSION, session_destroy)
- Tratamento de excecoes (try-catch)
- Headers HTTP e redirecionamentos

**Avancados:**
- Autoloading de classes
- Transacoes PDO
- Hashing de senhas (password_hash/verify)
- Algoritmos (ordenacao, busca)
- Patterns (MVC, Front Controller, PRG)

### 5.3 Seguranca

**Essenciais:**
- Prepared Statements (prevencao de SQL Injection)
- Escape de output (htmlspecialchars contra XSS)
- Validacao de entrada (tipos, formatos, ranges)
- Hash de senhas (NUNCA armazenar em texto plano)
- Verificacao de autenticacao

**Intermediarios:**
- Sanitizacao de dados (trim, stripslashes)
- CSRF tokens (em projetos mais avancados)
- Limitacao de tentativas de login
- Verificacao de permissoes
- HTTPS (em producao)

### 5.4 Frontend

**HTML:**
- Estrutura semantica (header, nav, main, section)
- Formularios (input types, labels, validation)
- Atributos de validacao (required, pattern, min, max)

**CSS:**
- Seletores e especificidade
- Flexbox para layouts
- Grid para layouts complexos
- Responsividade (media queries)
- Classes utilitarias

**JavaScript:**
- Selecao de elementos (querySelector, getElementById)
- Eventos (addEventListener)
- Manipulacao de DOM (classList, innerHTML)
- Validacao de formularios
- Fetch/AJAX (opcional)

---

## 6. BOAS PRATICAS IMPLEMENTADAS

### 6.1 Seguranca

- SEMPRE usar prepared statements
- SEMPRE escapar output com htmlspecialchars
- SEMPRE validar entrada do usuario
- NUNCA confiar em dados do cliente

### 6.2 Banco de Dados

- Usar transacoes para operacoes que precisam ser atomicas
- Capturar e tratar excecoes PDO
- Usar placeholders (?), nao concatenacao
- Fechar cursores quando apropriado

### 6.3 Arquitetura

- Separar logica de negocio (Model) de apresentacao (View)
- Controllers devem ser finos (chamam Models, nao fazem logica pesada)
- Reutilizar codigo (funcoes auxiliares, metodos compartilhados)
- Um ponto de entrada (Front Controller)

### 6.4 Feedback ao Usuario

- Sempre dar feedback de acoes (sucesso ou erro)
- Mensagens claras e especificas
- Redirecionar apos POST (evitar reenvio)
- Validar no cliente (UX) E servidor (seguranca)

### 6.5 Manutenibilidade

- Codigo legivel e mais importante que codigo "esperto"
- Nomes descritivos de variaveis e metodos
- Evitar duplicacao (DRY - Don't Repeat Yourself)
- Comentar decisoes nao obvias

---

## 7. ARMADILHAS COMUNS A EVITAR

### 7.1 Banco de Dados

- Esquecer de usar prepared statements (SQL Injection!)
- Nao tratar excecoes (erros silenciosos)
- Atualizar/deletar sem WHERE (afeta todas as linhas!)
- Esquecer de verificar se UPDATE/DELETE afetou linhas
- Nao usar transacoes em operacoes multiplas relacionadas

### 7.2 PHP

- Usar variaveis nao inicializadas (undefined index)
- Confundir = (atribuicao) com == (comparacao)
- Nao verificar REQUEST_METHOD antes de processar POST
- Esquecer de iniciar sessao (session_start)
- Dar echo/print durante redirecionamento (headers ja enviados)

### 7.3 Seguranca

- Nao escapar dados antes de exibir (XSS)
- Armazenar senhas em texto plano
- Nao validar dados no servidor (confiar so em validacao do cliente)
- Expor mensagens de erro tecnicas ao usuario
- Nao verificar autenticacao em rotas protegidas

### 7.4 Logica

- Nao validar se registro existe antes de editar/excluir
- Esquecer de redirecionar apos processamento de POST
- Nao limpar mensagens flash apos exibir
- Assumir que dados estao sempre presentes (verificar isset/empty)

### 7.5 Interface

- Nao fornecer feedback ao usuario
- Formularios sem labels (acessibilidade)
- Nao pre-preencher campos ao editar
- Exibir dados brutos (nao formatar datas, numeros)

---

## 8. ROTEIRO DE ESTUDO RECOMENDADO

### Semana 1: Fundamentos
- Revisar SQL (CREATE, INSERT, SELECT, UPDATE, DELETE)
- Estudar relacionamentos (1:N, N:N)
- Praticar PHP basico (arrays, loops, funcoes)
- Entender o padrao MVC (conceito e fluxo)

### Semana 2: Seguranca e PDO
- PDO: conexao, prepared statements, fetch modes
- Validacao e sanitizacao de dados
- Password hashing
- Prevencao de SQL Injection e XSS

### Semana 3: Desenvolvimento
- Analise deste projeto (ler codigo, entender fluxo)
- Praticar CRUD completo em um projeto proprio
- Implementar autenticacao basica
- Trabalhar com sessoes e flash messages

### Semana 4: Conceitos Avancados e CSS
- Transacoes de banco de dados
- Algoritmos (ordenacao, busca)
- Front Controller e roteamento
- **Praticar CSS: criar layouts, estilizar formularios e tabelas**
- Testes e refinamentos

---

## 9. CHECKLIST FINAL DE ESTUDO

**Backend:**
- [ ] Sei modelar um banco de dados (entidades, relacionamentos)
- [ ] Sei escrever CREATE TABLE com constraints
- [ ] Sei conectar com banco usando PDO
- [ ] Sei fazer CRUD completo com prepared statements
- [ ] Entendo o padrao MVC e consigo aplica-lo
- [ ] Sei validar dados (cliente e servidor)
- [ ] Sei implementar autenticacao basica (login/logout)
- [ ] Sei usar sessoes PHP
- [ ] Sei prevenir SQL Injection (prepared statements)
- [ ] Sei prevenir XSS (htmlspecialchars)
- [ ] Sei fazer transacoes quando necessario
- [ ] Sei implementar busca/filtro
- [ ] Entendo Front Controller e roteamento
- [ ] Sei debugar erros comuns (PDO exceptions, undefined index)

**Frontend:**
- [ ] Sei criar formularios HTML com validacao
- [ ] Sei exibir dados com foreach/loops
- [ ] Sei dar feedback ao usuario (mensagens)
- [ ] **Sei estilizar formularios com CSS**
- [ ] **Sei estilizar tabelas com CSS**
- [ ] **Sei criar layouts com Flexbox ou Grid**
- [ ] **Sei usar cores para comunicar status (sucesso, erro, alerta)**
- [ ] **Sei criar botoes estilizados com estados de hover**
- [ ] Sei usar JavaScript basico para interatividade
- [ ] Sei trabalhar com modals (se necessario)

---

## 10. CONCLUSAO

Este guia nao substitui a pratica. Use-o como um mapa, mas o verdadeiro aprendizado vem de:

1. **Ler e entender** o codigo deste repositorio
2. **Experimentar** modificando o codigo
3. **Criar** projetos proprios do zero
4. **Errar** e aprender com os erros
5. **Estilizar** criando sua propria identidade visual

**IMPORTANTE SOBRE CSS:**
Lembre-se que a estilizacao foi removida deste projeto intencionalmente. Nao procure por estilos prontos aqui. Use os comentarios orientadores em `sistema/public/css/style.css` como guia, mas crie suas proprias solucoes visuais. A capacidade de estilizar uma aplicacao web e tao importante quanto a logica de programacao.

Bons estudos! Lembre-se: o objetivo nao e memorizar codigo, mas entender conceitos e aplica-los a novos problemas.

---

**ARQUIVOS DE REFERENCIA PRINCIPAIS:**

- Documentacao: `ENTREGA_01_requisitos_funcionais.txt`, `ENTREGA_02_DER_descricao.txt`
- Banco de Dados: `ENTREGA_03_script_banco.sql`
- Configuracao: `sistema/config/database.php`
- Models: `sistema/models/*.php`
- Controllers: `sistema/controllers/*.php`
- Views: `sistema/views/**/*.php`
- Front Controller: `sistema/public/index.php`
- Casos de Teste: `ENTREGA_08_casos_de_teste.txt`
- **CSS (orientacoes):** `sistema/public/css/style.css`
