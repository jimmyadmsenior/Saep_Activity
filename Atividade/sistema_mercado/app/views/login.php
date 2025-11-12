<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestão de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid vh-100">
        <div class="row h-100">
            <div class="col-12 col-md-6 d-flex align-items-center justify-content-center">
                <div class="card shadow-lg border-0" style="width: 100%; max-width: 400px;">
                    <div class="card-body p-5">
                        <!-- Logo/Título -->
                        <div class="text-center mb-4">
                            <i class="fas fa-store fa-3x text-primary mb-3"></i>
                            <h2 class="card-title text-center mb-1">Sistema de Estoque</h2>
                            <p class="text-muted">Gestão de Mercado</p>
                        </div>

                        <!-- Mensagens de Flash -->
                        <?php
                        $messages = LoginController::getFlashMessages();
                        if (!empty($messages['erro'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($messages['erro'], ENT_QUOTES, 'UTF-8') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($messages['sucesso'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= htmlspecialchars($messages['sucesso'], ENT_QUOTES, 'UTF-8') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Formulário de Login -->
                        <form method="POST" action="index.php?action=autenticar" novalidate>
                            <div class="mb-3">
                                <label for="usuario" class="form-label">
                                    <i class="fas fa-user me-1"></i>Usuário
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="usuario" 
                                       name="usuario" 
                                       required 
                                       maxlength="50"
                                       autocomplete="username"
                                       placeholder="Digite seu usuário">
                                <div class="invalid-feedback">
                                    Por favor, informe seu usuário.
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="senha" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Senha
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control form-control-lg" 
                                           id="senha" 
                                           name="senha" 
                                           required 
                                           autocomplete="current-password"
                                           placeholder="Digite sua senha">
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Por favor, informe sua senha.
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Entrar
                                </button>
                            </div>
                        </form>

                        <!-- Informações de Teste -->
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-info-circle me-1"></i>Usuários de Teste:
                            </h6>
                            <small class="text-muted">
                                <strong>admin/123456</strong> - Administrador<br>
                                <strong>maria/123456</strong> - Maria Silva<br>
                                <strong>joao/123456</strong> - João Santos
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Painel lateral com informações -->
            <div class="col-md-6 d-none d-md-flex align-items-center justify-content-center bg-primary text-white">
                <div class="text-center p-5">
                    <i class="fas fa-boxes fa-5x mb-4 opacity-75"></i>
                    <h1 class="display-4 mb-4">Controle Total</h1>
                    <p class="lead mb-4">
                        Gerencie o estoque do seu mercado de forma eficiente e segura.
                    </p>
                    <div class="row text-center">
                        <div class="col-4">
                            <i class="fas fa-chart-line fa-2x mb-2"></i>
                            <p>Relatórios</p>
                        </div>
                        <div class="col-4">
                            <i class="fas fa-bell fa-2x mb-2"></i>
                            <p>Alertas</p>
                        </div>
                        <div class="col-4">
                            <i class="fas fa-shield-alt fa-2x mb-2"></i>
                            <p>Segurança</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação do formulário
        (function() {
            'use strict';
            
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });

            // Toggle senha
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('senha');
            
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });

            // Auto-focus no primeiro campo
            document.getElementById('usuario').focus();
        })();
    </script>
</body>
</html>