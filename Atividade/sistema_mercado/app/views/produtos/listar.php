<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - Sistema de Gestão de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php?action=home">
                <i class="fas fa-store me-2"></i>Sistema de Estoque
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=home">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php?action=produtos">
                            <i class="fas fa-box me-1"></i>Produtos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=estoque">
                            <i class="fas fa-warehouse me-1"></i>Estoque
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?= htmlspecialchars(LoginController::getUsuarioLogado()['nome'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?action=logout">
                                <i class="fas fa-sign-out-alt me-2"></i>Sair
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Mensagens de Flash -->
        <?php
        $messages = LoginController::getFlashMessages();
        if (!empty($messages['sucesso'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= htmlspecialchars($messages['sucesso'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($messages['erro'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($messages['erro'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Cabeçalho -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-box me-2"></i>Gerenciamento de Produtos
            </h1>
            <div>
                <a href="index.php?action=produtos-exportar-csv" class="btn btn-sm btn-success me-2">
                    <i class="fas fa-download me-1"></i>Exportar CSV
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProduto">
                    <i class="fas fa-plus me-1"></i>Novo Produto
                </button>
            </div>
        </div>

        <!-- Filtros de Busca -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="index.php" class="row g-3">
                    <input type="hidden" name="action" value="produtos">
                    
                    <div class="col-md-4">
                        <label for="busca" class="form-label">Buscar Produto</label>
                        <input type="text" class="form-control" id="busca" name="busca" 
                               value="<?= htmlspecialchars($_GET['busca'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="Nome, código ou marca...">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="categoria" class="form-label">Categoria</label>
                        <select class="form-select" id="categoria" name="categoria">
                            <option value="">Todas as categorias</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>" 
                                        <?= ($_GET['categoria'] ?? '') === $cat ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>Buscar
                        </button>
                        <a href="index.php?action=produtos" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Limpar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabela de Produtos -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    Lista de Produtos (<?= count($produtos) ?> encontrados)
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($produtos)): ?>
                    <div class="text-center">
                        <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Nenhum produto encontrado.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProduto">
                            <i class="fas fa-plus me-1"></i>Cadastrar Primeiro Produto
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Produto</th>
                                    <th>Categoria</th>
                                    <th>Marca</th>
                                    <th>Preço Venda</th>
                                    <th>Estoque</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produtos as $produto): ?>
                                    <tr>
                                        <td><?= $produto['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <?php if (!empty($produto['codigo_barras'])): ?>
                                                <br><small class="text-muted">
                                                    <i class="fas fa-barcode me-1"></i>
                                                    <?= htmlspecialchars($produto['codigo_barras'], ENT_QUOTES, 'UTF-8') ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars($produto['categoria'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($produto['marca'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= HomeController::formatarMoeda($produto['preco_venda']) ?></td>
                                        <td>
                                            <span class="fw-bold"><?= $produto['estoque_atual'] ?></span> / 
                                            <small class="text-muted"><?= $produto['estoque_minimo'] ?></small>
                                        </td>
                                        <td>
                                            <?php 
                                            $statusClass = HomeController::getClasseStatusEstoque($produto);
                                            $statusText = HomeController::getTextoStatusEstoque($produto);
                                            ?>
                                            <span class="badge bg-<?= $statusClass ?>">
                                                <?= $statusText ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="editarProduto(<?= $produto['id'] ?>)"
                                                        title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="excluirProduto(<?= $produto['id'] ?>, '<?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?>')"
                                                        title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Produto -->
    <div class="modal fade" id="modalProduto" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProdutoTitle">
                        <i class="fas fa-box me-2"></i>Novo Produto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formProduto" method="POST" novalidate>
                    <div class="modal-body">
                        <input type="hidden" id="produto_id" name="id">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="nome" class="form-label">Nome do Produto *</label>
                                    <input type="text" class="form-control" id="nome" name="nome" required maxlength="100">
                                    <div class="invalid-feedback">Nome é obrigatório.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="codigo_barras" class="form-label">Código de Barras</label>
                                    <input type="text" class="form-control" id="codigo_barras" name="codigo_barras" maxlength="50">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="categoria" class="form-label">Categoria *</label>
                                    <select class="form-select" id="categoria_modal" name="categoria" required>
                                        <option value="">Selecione uma categoria</option>
                                        <?php foreach (ProdutosController::getCategoriasDisponiveis() as $cat): ?>
                                            <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Categoria é obrigatória.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="marca" class="form-label">Marca</label>
                                    <input type="text" class="form-control" id="marca" name="marca" maxlength="50">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="preco_custo" class="form-label">Preço de Custo</label>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="number" class="form-control" id="preco_custo" name="preco_custo" 
                                               min="0" step="0.01" placeholder="0,00">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="preco_venda" class="form-label">Preço de Venda</label>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="number" class="form-control" id="preco_venda" name="preco_venda" 
                                               min="0" step="0.01" placeholder="0,00">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="estoque_atual" class="form-label">Estoque Atual</label>
                                    <input type="number" class="form-control" id="estoque_atual" name="estoque_atual" 
                                           min="0" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="estoque_minimo" class="form-label">Estoque Mínimo</label>
                                    <input type="number" class="form-control" id="estoque_minimo" name="estoque_minimo" 
                                           min="0" value="10">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Exclusão -->
    <div class="modal fade" id="modalExcluir" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2 text-danger"></i>Confirmar Exclusão
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o produto?</p>
                    <div class="alert alert-warning">
                        <strong id="nomeProdutoExcluir"></strong>
                    </div>
                    <p class="text-muted">Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="formExcluir" method="POST" action="index.php?action=produtos-excluir" class="d-inline">
                        <input type="hidden" id="idProdutoExcluir" name="id">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let modalProduto = new bootstrap.Modal(document.getElementById('modalProduto'));
        let modalExcluir = new bootstrap.Modal(document.getElementById('modalExcluir'));

        // Validação do formulário
        (function() {
            'use strict';
            
            const form = document.getElementById('formProduto');
            form.addEventListener('submit', function(event) {
                const isEdit = document.getElementById('produto_id').value !== '';
                form.action = isEdit ? 'index.php?action=produtos-editar' : 'index.php?action=produtos-cadastrar';
                
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        })();

        // Função para editar produto
        async function editarProduto(id) {
            try {
                const response = await fetch(`index.php?action=produtos-buscar&id=${id}`);
                const produto = await response.json();
                
                if (produto.erro) {
                    alert('Erro: ' + produto.erro);
                    return;
                }
                
                // Preencher formulário
                document.getElementById('produto_id').value = produto.id;
                document.getElementById('nome').value = produto.nome;
                document.getElementById('codigo_barras').value = produto.codigo_barras || '';
                document.getElementById('categoria_modal').value = produto.categoria;
                document.getElementById('marca').value = produto.marca || '';
                document.getElementById('preco_custo').value = produto.preco_custo || '';
                document.getElementById('preco_venda').value = produto.preco_venda || '';
                document.getElementById('estoque_atual').value = produto.estoque_atual;
                document.getElementById('estoque_minimo').value = produto.estoque_minimo;
                
                // Alterar título do modal
                document.getElementById('modalProdutoTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Editar Produto';
                
                modalProduto.show();
            } catch (error) {
                console.error('Erro ao carregar produto:', error);
                alert('Erro ao carregar dados do produto');
            }
        }

        // Função para excluir produto
        function excluirProduto(id, nome) {
            document.getElementById('idProdutoExcluir').value = id;
            document.getElementById('nomeProdutoExcluir').textContent = nome;
            modalExcluir.show();
        }

        // Limpar formulário ao abrir modal para novo produto
        document.getElementById('modalProduto').addEventListener('hidden.bs.modal', function () {
            const form = document.getElementById('formProduto');
            form.reset();
            form.classList.remove('was-validated');
            document.getElementById('produto_id').value = '';
            document.getElementById('modalProdutoTitle').innerHTML = '<i class="fas fa-box me-2"></i>Novo Produto';
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (alert.classList.contains('alert-dismissible')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
    </script>
</body>
</html>