<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Gestão de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php?action=home">
                <i class="fas fa-chart-line me-2"></i><strong>StockPro</strong>
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
                            <?= htmlspecialchars(LoginController::getUsuarioLogado()['nome'] ?? 'Usuário', ENT_QUOTES, 'UTF-8') ?>
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

    <div class="container mt-4">
        <?php if (!empty($messages['sucesso'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($messages['sucesso'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($messages['aviso'])): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($messages['aviso'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Cards de Estatísticas -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100 py-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase text-muted small">Total de Produtos</div>
                                <div class="h4"><?= $dadosDashboard['status_estoque']['total_produtos'] ?? 0 ?></div>
                            </div>
                            <i class="fas fa-boxes fa-2x text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100 py-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase text-muted small">Entradas (30d)</div>
                                <div class="h4"><?= $dadosDashboard['estatisticas_movimentacao']['total_entradas'] ?? 0 ?></div>
                            </div>
                            <i class="fas fa-arrow-down fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100 py-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase text-muted small">Saídas (30d)</div>
                                <div class="h4"><?= $dadosDashboard['estatisticas_movimentacao']['total_saidas'] ?? 0 ?></div>
                            </div>
                            <i class="fas fa-arrow-up fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100 py-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase text-muted small">Produtos com Estoque Baixo</div>
                                <div class="h4"><?= count($dadosDashboard['produtos_estoque_baixo'] ?? []) ?></div>
                            </div>
                            <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">Produtos com Estoque Baixo</div>
                    <div class="card-body">
                        <?php if (empty($dadosDashboard['produtos_estoque_baixo'])): ?>
                            <p class="text-muted">Nenhum produto com estoque baixo.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach (array_slice($dadosDashboard['produtos_estoque_baixo'], 0, 8) as $p): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div><?= htmlspecialchars($p['nome'], ENT_QUOTES, 'UTF-8') ?> <small class="text-muted">(<?= htmlspecialchars($p['categoria'], ENT_QUOTES, 'UTF-8') ?>)</small></div>
                                        <span class="badge bg-warning rounded-pill"><?= $p['estoque_atual'] ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">Últimas Movimentações</div>
                    <div class="card-body">
                        <?php if (empty($dadosDashboard['ultimas_movimentacoes'])): ?>
                            <p class="text-muted">Nenhuma movimentação registrada.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach (array_slice($dadosDashboard['ultimas_movimentacoes'], 0, 8) as $m): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($m['produto_nome'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <div class="small text-muted"><?= HomeController::formatarDataHora($m['data_registro']) ?></div>
                                        </div>
                                        <span class="badge bg-<?= $m['tipo'] === 'entrada' ? 'success' : 'danger' ?> rounded-pill">
                                            <?= ($m['tipo'] === 'entrada' ? '+' : '-') . $m['quantidade'] ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(a){
                try { bootstrap.Alert.getOrCreateInstance(a).close(); } catch(e){}
            });
        }, 5000);
    </script>
</body>
</html>