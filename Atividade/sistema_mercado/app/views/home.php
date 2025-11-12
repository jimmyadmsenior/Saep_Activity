<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Gestão de Estoque</title>
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
                        <a class="nav-link active" href="index.php?action=home">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=produtos">
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

        <?php if (!empty($messages['aviso'])): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($messages['aviso'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Cards de Estatísticas -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total de Produtos
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= $dadosDashboard['status_estoque']['total_produtos'] ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-boxes fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Estoque OK
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= $dadosDashboard['status_estoque']['estoque_ok'] ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Estoque Baixo
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= $dadosDashboard['status_estoque']['estoque_baixo'] ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Esgotados
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= $dadosDashboard['status_estoque']['esgotados'] ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Produtos com Estoque Baixo -->
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-exclamation-triangle me-2"></i>Produtos com Estoque Baixo
                        </h6>
                        <span class="badge bg-warning"><?= count($dadosDashboard['produtos_estoque_baixo']) ?></span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($dadosDashboard['produtos_estoque_baixo'])): ?>
                            <div class="text-center text-muted">
                                <i class="fas fa-check-circle fa-3x mb-3"></i>
                                <p>Todos os produtos estão com estoque adequado!</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Produto</th>
                                            <th>Categoria</th>
                                            <th>Atual</th>
                                            <th>Mínimo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($dadosDashboard['produtos_estoque_baixo'], 0, 5) as $produto): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td>
                                                    <span class="badge bg-secondary"><?= htmlspecialchars($produto['categoria'], ENT_QUOTES, 'UTF-8') ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $produto['estoque_atual'] == 0 ? 'danger' : 'warning' ?>">
                                                        <?= $produto['estoque_atual'] ?>
                                                    </span>
                                                </td>
                                                <td><?= $produto['estoque_minimo'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if (count($dadosDashboard['produtos_estoque_baixo']) > 5): ?>
                                <div class="text-center mt-3">
                                    <a href="index.php?action=produtos&estoque=baixo" class="btn btn-sm btn-outline-primary">
                                        Ver todos (<?= count($dadosDashboard['produtos_estoque_baixo']) ?>)
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Últimas Movimentações -->
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-history me-2"></i>Últimas Movimentações
                        </h6>
                        <a href="index.php?action=estoque" class="btn btn-sm btn-outline-primary">
                            Ver todas
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($dadosDashboard['ultimas_movimentacoes'])): ?>
                            <div class="text-center text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Nenhuma movimentação registrada.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach (array_slice($dadosDashboard['ultimas_movimentacoes'], 0, 5) as $mov): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($mov['produto_nome'], ENT_QUOTES, 'UTF-8') ?></h6>
                                            <p class="mb-1">
                                                <span class="badge bg-<?= $mov['tipo'] == 'entrada' ? 'success' : 'danger' ?>">
                                                    <?= ucfirst($mov['tipo']) ?>
                                                </span>
                                                <?= $mov['quantidade'] ?> unidades
                                            </p>
                                            <small><?= HomeController::formatarDataHora($mov['data_registro']) ?></small>
                                        </div>
                                        <span class="badge bg-<?= $mov['tipo'] == 'entrada' ? 'success' : 'danger' ?> rounded-pill">
                                            <?= $mov['tipo'] == 'entrada' ? '+' : '-' ?><?= $mov['quantidade'] ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estatísticas de Movimentação e Produtos por Categoria -->
        <div class="row">
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-bar me-2"></i>Movimentações (Últimos 30 dias)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <h4 class="text-success"><?= $dadosDashboard['estatisticas_movimentacao']['total_entradas'] ?? 0 ?></h4>
                                <small class="text-muted">Entradas</small>
                            </div>
                            <div class="col-4">
                                <h4 class="text-danger"><?= $dadosDashboard['estatisticas_movimentacao']['total_saidas'] ?? 0 ?></h4>
                                <small class="text-muted">Saídas</small>
                            </div>
                            <div class="col-4">
                                <h4 class="text-info"><?= $dadosDashboard['estatisticas_movimentacao']['total_movimentacoes'] ?? 0 ?></h4>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-tags me-2"></i>Produtos por Categoria
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($dadosDashboard['produtos_por_categoria'])): ?>
                            <?php foreach ($dadosDashboard['produtos_por_categoria'] as $categoria): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><?= htmlspecialchars($categoria['categoria'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="badge bg-primary rounded-pill"><?= $categoria['total'] ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center">Nenhum produto cadastrado.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>