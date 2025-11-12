<?php
$mensagem = $mensagem ?? null;
$tipoMensagem = $tipoMensagem ?? 'info';
$termoBusca = $termoBusca ?? '';
$livros = $livros ?? [];
$usuario_nome = $usuario_nome ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Biblioteca - Livros</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Cadastro de Livros</h1>
        <div class="user-info">
            <span>Usuario: <?php echo htmlspecialchars($usuario_nome); ?></span>
            <a href="index.php?action=logout" class="btn-logout">Sair</a>
        </div>
    </div>

    <div class="card">
        <?php if ($mensagem): ?>
            <div class="alert alert-<?php echo $tipoMensagem; ?>"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Gerenciar Livros</h2>
            <div>
                <a href="index.php" class="btn-secondary">Voltar ao Menu</a>
                <button onclick="abrirModal()" class="btn-primary">Novo Livro</button>
            </div>
        </div>

        <div class="search-box">
            <input type="text" id="campoBusca" placeholder="Buscar por titulo, autor, ISBN ou categoria..."
                   value="<?php echo htmlspecialchars($termoBusca); ?>">
            <button onclick="realizarBusca()" class="btn-primary">Buscar</button>
            <button onclick="limparBusca()" class="btn-secondary">Limpar</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titulo</th>
                    <th>Autor</th>
                    <th>ISBN</th>
                    <th>Categoria</th>
                    <th>Estoque</th>
                    <th>Est. Min</th>
                    <th>Status</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($livros) > 0): ?>
                    <?php foreach ($livros as $livro): ?>
                        <tr>
                            <td><?php echo $livro['id']; ?></td>
                            <td><?php echo htmlspecialchars($livro['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($livro['autor']); ?></td>
                            <td><?php echo htmlspecialchars($livro['isbn']); ?></td>
                            <td><?php echo htmlspecialchars($livro['categoria']); ?></td>
                            <td><?php echo $livro['estoque_atual']; ?></td>
                            <td><?php echo $livro['estoque_minimo']; ?></td>
                            <td>
                                <?php if ($livro['estoque_atual'] < $livro['estoque_minimo']): ?>
                                    <span style="color: #dc3545; font-weight: bold;">Baixo</span>
                                <?php elseif ($livro['estoque_atual'] == 0): ?>
                                    <span style="color: #dc3545; font-weight: bold;">Esgotado</span>
                                <?php else: ?>
                                    <span style="color: #28a745;">OK</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <button onclick='editarLivro(<?php echo json_encode($livro); ?>)' class="btn-warning">Editar</button>
                                <button onclick="excluirLivro(<?php echo $livro['id']; ?>, '<?php echo addslashes($livro['titulo']); ?>')" class="btn-danger">Excluir</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 20px;">Nenhum livro encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitulo">Novo Livro</h3>
            <button class="close-modal" onclick="fecharModal()">&times;</button>
        </div>
        <form id="formLivro" method="POST" action="index.php?action=livros-cadastrar">
            <input type="hidden" name="id" id="livroId">

            <div class="form-group">
                <label for="titulo">Titulo *</label>
                <input type="text" id="titulo" name="titulo" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="autor">Autor *</label>
                    <input type="text" id="autor" name="autor" required>
                </div>
                <div class="form-group">
                    <label for="isbn">ISBN *</label>
                    <input type="text" id="isbn" name="isbn" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="editora">Editora</label>
                    <input type="text" id="editora" name="editora">
                </div>
                <div class="form-group">
                    <label for="ano_publicacao">Ano de Publicacao</label>
                    <input type="number" id="ano_publicacao" name="ano_publicacao" min="1000" max="<?php echo date('Y'); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="categoria">Categoria *</label>
                <input type="text" id="categoria" name="categoria" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="estoque_atual">Estoque Atual *</label>
                    <input type="number" id="estoque_atual" name="estoque_atual" min="0" required value="0">
                </div>
                <div class="form-group">
                    <label for="estoque_minimo">Estoque Minimo *</label>
                    <input type="number" id="estoque_minimo" name="estoque_minimo" min="0" required value="5">
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn-primary" style="flex: 1;">Salvar</button>
                <button type="button" onclick="fecharModal()" class="btn-secondary" style="flex: 1;">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModal() {
        document.getElementById('modal').classList.add('active');
        document.getElementById('modalTitulo').textContent = 'Novo Livro';
        document.getElementById('formLivro').reset();
        document.getElementById('formLivro').action = 'index.php?action=livros-cadastrar';
        document.getElementById('livroId').value = '';
    }

    function fecharModal() {
        document.getElementById('modal').classList.remove('active');
    }

    function editarLivro(livro) {
        document.getElementById('modal').classList.add('active');
        document.getElementById('modalTitulo').textContent = 'Editar Livro';
        document.getElementById('formLivro').action = 'index.php?action=livros-editar';
        document.getElementById('livroId').value = livro.id;
        document.getElementById('titulo').value = livro.titulo;
        document.getElementById('autor').value = livro.autor;
        document.getElementById('isbn').value = livro.isbn;
        document.getElementById('editora').value = livro.editora || '';
        document.getElementById('ano_publicacao').value = livro.ano_publicacao || '';
        document.getElementById('categoria').value = livro.categoria;
        document.getElementById('estoque_atual').value = livro.estoque_atual;
        document.getElementById('estoque_minimo').value = livro.estoque_minimo;
    }

    function excluirLivro(id, titulo) {
        if (confirm('Tem certeza que deseja excluir o livro "' + titulo + '"?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'index.php?action=livros-excluir';
            form.innerHTML = '<input type="hidden" name="id" value="' + id + '">';
            document.body.appendChild(form);
            form.submit();
        }
    }

    function realizarBusca() {
        const termo = document.getElementById('campoBusca').value;
        window.location.href = 'index.php?action=livros&busca=' + encodeURIComponent(termo);
    }

    function limparBusca() {
        window.location.href = 'index.php?action=livros';
    }

    document.getElementById('campoBusca').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            realizarBusca();
        }
    });

    window.onclick = function(event) {
        const modal = document.getElementById('modal');
        if (event.target === modal) {
            fecharModal();
        }
    }
</script>

</body>
</html>
