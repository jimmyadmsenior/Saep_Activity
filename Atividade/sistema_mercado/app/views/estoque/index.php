<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Estoque - Sistema de Gestão de Estoque</title>
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
                        <a class="nav-link" href="index.php?action=produtos">
                            <i class="fas fa-box me-1"></i>Produtos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php?action=estoque">
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

        <?php if (!empty($messages['aviso'])): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($messages['aviso'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Cabeçalho -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-warehouse me-2"></i>Gestão de Estoque
            </h1>
            <div>
                <button type="button" class="btn btn-sm btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalRelatorio">
                    <i class="fas fa-download me-1"></i>Exportar Relatório
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMovimentacao">
                    <i class="fas fa-plus me-1"></i>Nova Movimentação
                </button>
            </div>
        </div>

        <!-- Alertas de Estoque Baixo -->
        <?php if (!empty($produtosEstoqueBaixo)): ?>
            <div class="alert alert-warning" role="alert">
                <h4 class="alert-heading">
                    <i class="fas fa-exclamation-triangle me-2"></i>Atenção: Produtos com Estoque Baixo
                </h4>
                <p>Os seguintes produtos estão com estoque abaixo do mínimo:</p>
                <ul class="mb-0">
                    <?php foreach (array_slice($produtosEstoqueBaixo, 0, 5) as $produto): ?>
                        <li>
                            <strong><?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?></strong> - 
                            Atual: <?= $produto['estoque_atual'] ?>, Mínimo: <?= $produto['estoque_minimo'] ?>
                        </li>
                    <?php endforeach; ?>
                    <?php if (count($produtosEstoqueBaixo) > 5): ?>
                        <li><em>... e mais <?= count($produtosEstoqueBaixo) - 5 ?> produtos</em></li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Seleção de Produto para Movimentação -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-search me-2"></i>Produtos Disponíveis
                            <small class="text-muted">(Ordenados por Bubble Sort)</small>
                        </h6>
                    </div>
                    <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                        <?php if (empty($produtos)): ?>
                            <div class="text-center">
                                <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                                <p class="text-gray-500">Nenhum produto cadastrado.</p>
                                <a href="index.php?action=produtos" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i>Cadastrar Produtos
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <input type="text" class="form-control" id="filtrarProdutos" placeholder="Filtrar produtos...">
                            </div>
                            <div id="listaProdutos">
                                <?php foreach ($produtos as $produto): ?>
                                    <?php 
                                    $statusClass = HomeController::getClasseStatusEstoque($produto);
                                    $statusText = HomeController::getTextoStatusEstoque($produto);
                                    ?>
                                    <div class="produto-item border rounded p-3 mb-2 cursor-pointer" 
                                         data-produto-id="<?= $produto['id'] ?>"
                                         data-nome="<?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                         onclick="selecionarProduto(<?= $produto['id'] ?>)">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($produto['categoria'], ENT_QUOTES, 'UTF-8') ?></small>
                                                <?php if (!empty($produto['codigo_barras'])): ?>
                                                    <br><small class="text-muted">
                                                        <i class="fas fa-barcode me-1"></i>
                                                        <?= htmlspecialchars($produto['codigo_barras'], ENT_QUOTES, 'UTF-8') ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                                                <br><small class="text-muted">Est: <?= $produto['estoque_atual'] ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Histórico de Movimentações -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-history me-2"></i>Últimas Movimentações
                        </h6>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="carregarMovimentacoes()">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="filtrarMovimentacoes()">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="listaMovimentacoes">
                            <?php if (empty($ultimasMovimentacoes)): ?>
                                <div class="text-center">
                                    <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                                    <p class="text-gray-500">Nenhuma movimentação registrada.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Data/Hora</th>
                                                <th>Produto</th>
                                                <th>Tipo</th>
                                                <th>Qtd</th>
                                                <th>Usuário</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ultimasMovimentacoes as $mov): ?>
                                                <tr>
                                                    <td>
                                                        <small>
                                                            <?= HomeController::formatarData($mov['data_movimentacao']) ?><br>
                                                            <?= date('H:i', strtotime($mov['data_registro'])) ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <small>
                                                            <?= htmlspecialchars($mov['produto_nome'], ENT_QUOTES, 'UTF-8') ?><br>
                                                            <span class="text-muted"><?= htmlspecialchars($mov['categoria'], ENT_QUOTES, 'UTF-8') ?></span>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?= $mov['tipo'] == 'entrada' ? 'success' : 'danger' ?>">
                                                            <?= ucfirst($mov['tipo']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="fw-bold"><?= $mov['quantidade'] ?></td>
                                                    <td><small><?= htmlspecialchars($mov['usuario_nome'], ENT_QUOTES, 'UTF-8') ?></small></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                onclick="reverterMovimentacao(<?= $mov['id'] ?>)"
                                                                title="Reverter">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
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
            </div>
        </div>
    </div>

    <!-- Modal Nova Movimentação -->
    <div class="modal fade" id="modalMovimentacao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exchange-alt me-2"></i>Nova Movimentação de Estoque
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formMovimentacao" method="POST" action="index.php?action=estoque-registrar" novalidate>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="produto_id" class="form-label">Produto *</label>
                            <select class="form-select" id="produto_id" name="produto_id" required>
                                <option value="">Selecione um produto</option>
                                <?php foreach ($produtos as $produto): ?>
                                    <option value="<?= $produto['id'] ?>" 
                                            data-estoque="<?= $produto['estoque_atual'] ?>"
                                            data-minimo="<?= $produto['estoque_minimo'] ?>">
                                        <?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?> 
                                        (Est: <?= $produto['estoque_atual'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Selecione um produto.</div>
                        </div>

                        <div id="infoProdutoSelecionado" class="alert alert-info d-none">
                            <h6>Informações do Produto:</h6>
                            <div id="detalhesproduto"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tipo" class="form-label">Tipo de Movimentação *</label>
                                    <select class="form-select" id="tipo" name="tipo" required>
                                        <option value="">Selecione o tipo</option>
                                        <option value="entrada">Entrada (+)</option>
                                        <option value="saida">Saída (-)</option>
                                    </select>
                                    <div class="invalid-feedback">Selecione o tipo de movimentação.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantidade" class="form-label">Quantidade *</label>
                                    <input type="number" class="form-control" id="quantidade" name="quantidade" 
                                           min="1" required>
                                    <div class="invalid-feedback">Quantidade deve ser maior que zero.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="data_movimentacao" class="form-label">Data da Movimentação *</label>
                            <input type="date" class="form-control" id="data_movimentacao" name="data_movimentacao" 
                                   value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                            <div class="invalid-feedback">Data é obrigatória e não pode ser futura.</div>
                        </div>

                        <div class="mb-3">
                            <label for="observacao" class="form-label">Observação</label>
                            <textarea class="form-control" id="observacao" name="observacao" 
                                      rows="3" placeholder="Observações sobre a movimentação (opcional)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Registrar Movimentação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Exportar Relatório -->
    <div class="modal fade" id="modalRelatorio" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-download me-2"></i>Exportar Relatório de Movimentações
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="GET" action="index.php">
                    <input type="hidden" name="action" value="estoque-relatorio-csv">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="data_inicio" class="form-label">Data Início</label>
                                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" 
                                           value="<?= date('Y-m-01') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="data_fim" class="form-label">Data Fim</label>
                                    <input type="date" class="form-control" id="data_fim" name="data_fim" 
                                           value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="produto_relatorio" class="form-label">Produto (opcional)</label>
                            <select class="form-select" id="produto_relatorio" name="produto_id">
                                <option value="">Todos os produtos</option>
                                <?php foreach ($produtos as $produto): ?>
                                    <option value="<?= $produto['id'] ?>">
                                        <?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-download me-1"></i>Exportar CSV
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let modalMovimentacao = new bootstrap.Modal(document.getElementById('modalMovimentacao'));

        // Validação do formulário
        (function() {
            'use strict';
            
            const form = document.getElementById('formMovimentacao');
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        })();

        // Seleção de produto na lista
        function selecionarProduto(id) {
            const select = document.getElementById('produto_id');
            select.value = id;
            select.dispatchEvent(new Event('change'));
            modalMovimentacao.show();
        }

        // Filtrar produtos
        document.getElementById('filtrarProdutos').addEventListener('input', function() {
            const filtro = this.value.toLowerCase();
            const produtos = document.querySelectorAll('.produto-item');
            
            produtos.forEach(function(produto) {
                const nome = produto.dataset.nome.toLowerCase();
                if (nome.includes(filtro)) {
                    produto.style.display = 'block';
                } else {
                    produto.style.display = 'none';
                }
            });
        });

        // Mostrar informações do produto selecionado
        document.getElementById('produto_id').addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            const infoProduto = document.getElementById('infoProdutoSelecionado');
            const detalhes = document.getElementById('detalhesproduct');
            
            if (this.value) {
                const estoque = option.dataset.estoque;
                const minimo = option.dataset.minimo;
                const nome = option.textContent.split(' (Est:')[0];
                
                let status = 'OK';
                let classeStatus = 'success';
                
                if (estoque == 0) {
                    status = 'Esgotado';
                    classeStatus = 'danger';
                } else if (estoque < minimo) {
                    status = 'Estoque Baixo';
                    classeStatus = 'warning';
                }
                
                detalhes.innerHTML = `
                    <strong>Produto:</strong> ${nome}<br>
                    <strong>Estoque Atual:</strong> ${estoque} unidades<br>
                    <strong>Estoque Mínimo:</strong> ${minimo} unidades<br>
                    <strong>Status:</strong> <span class="badge bg-${classeStatus}">${status}</span>
                `;
                
                infoProduto.classList.remove('d-none');
            } else {
                infoProduto.classList.add('d-none');
            }
        });

        // Validação de quantidade para saída
        document.getElementById('tipo').addEventListener('change', function() {
            const quantidadeInput = document.getElementById('quantidade');
            const produtoSelect = document.getElementById('produto_id');
            
            if (this.value === 'saida' && produtoSelect.value) {
                const option = produtoSelect.options[produtoSelect.selectedIndex];
                const estoqueAtual = parseInt(option.dataset.estoque);
                quantidadeInput.max = estoqueAtual;
                quantidadeInput.placeholder = `Máximo: ${estoqueAtual}`;
            } else {
                quantidadeInput.removeAttribute('max');
                quantidadeInput.placeholder = '';
            }
        });

        // Função para reverter movimentação
        function reverterMovimentacao(id) {
            if (confirm('Tem certeza que deseja reverter esta movimentação? Esta ação não pode ser desfeita.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'index.php?action=estoque-reverter';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id';
                input.value = id;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Carregar movimentações via AJAX
        function carregarMovimentacoes() {
            // Implementar se necessário
            location.reload();
        }

        // Filtrar movimentações
        function filtrarMovimentacoes() {
            // Implementar filtros avançados se necessário
        }

        // Limpar formulário ao fechar modal
        document.getElementById('modalMovimentacao').addEventListener('hidden.bs.modal', function () {
            const form = document.getElementById('formMovimentacao');
            form.reset();
            form.classList.remove('was-validated');
            document.getElementById('infoProdutoSelecionado').classList.add('d-none');
            document.getElementById('data_movimentacao').value = '<?= date('Y-m-d') ?>';
        });

        // Destacar produto selecionado
        document.querySelectorAll('.produto-item').forEach(function(item) {
            item.addEventListener('mouseenter', function() {
                this.classList.add('bg-light');
            });
            
            item.addEventListener('mouseleave', function() {
                this.classList.remove('bg-light');
            });
        });

        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>

    <style>
        .produto-item {
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .produto-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }
        
        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }
        
        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }
        
        .border-left-danger {
            border-left: 0.25rem solid #e74a3b !important;
        }
    </style>
</body>
</html>