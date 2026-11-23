<?php
declare(strict_types=1);

session_start();

function escapar(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function formatarMoeda(int $centavos): string
{
    return 'R$ ' . number_format($centavos / 100, 2, ',', '.');
}

$produtos = [
    'cafe' => ['nome' => 'Café da serra', 'descricao' => 'Pacote demonstrativo de 500 g.', 'preco' => 1890, 'estoque' => 8, 'cor' => 'cafe'],
    'caderno' => ['nome' => 'Caderno pontilhado', 'descricao' => 'Capa firme para organizar ideias.', 'preco' => 2450, 'estoque' => 12, 'cor' => 'azul'],
    'garrafa' => ['nome' => 'Garrafa térmica', 'descricao' => 'Modelo fictício de 600 ml.', 'preco' => 7900, 'estoque' => 5, 'cor' => 'verde'],
];

$_SESSION['carrinho'] = $_SESSION['carrinho'] ?? [];
$_SESSION['token_formulario'] = $_SESSION['token_formulario'] ?? bin2hex(random_bytes(32));
$mensagem = '';
$tipoMensagem = 'informacao';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['token']) && is_string($_POST['token']) ? $_POST['token'] : '';
    if (!hash_equals($_SESSION['token_formulario'], $token)) {
        http_response_code(400);
        $mensagem = 'Não foi possível validar o formulário. Recarregue a página e tente novamente.';
        $tipoMensagem = 'erro';
    } else {
        $acao = isset($_POST['acao']) && is_string($_POST['acao']) ? $_POST['acao'] : '';
        $codigo = isset($_POST['produto']) && is_string($_POST['produto']) ? $_POST['produto'] : '';

        if ($acao === 'adicionar') {
            $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);
            if (!isset($produtos[$codigo])) {
                http_response_code(400);
                $mensagem = 'O produto selecionado não está no catálogo.';
                $tipoMensagem = 'erro';
            } elseif ($quantidade === false || $quantidade === null || $quantidade < 1) {
                http_response_code(400);
                $mensagem = 'Informe uma quantidade inteira maior que zero.';
                $tipoMensagem = 'erro';
            } else {
                $quantidadeAtual = (int) ($_SESSION['carrinho'][$codigo] ?? 0);
                $novaQuantidade = $quantidadeAtual + $quantidade;
                if ($novaQuantidade > $produtos[$codigo]['estoque']) {
                    http_response_code(400);
                    $mensagem = 'A quantidade solicitada excede o estoque demonstrativo.';
                    $tipoMensagem = 'erro';
                } else {
                    $_SESSION['carrinho'][$codigo] = $novaQuantidade;
                    $mensagem = 'Item adicionado ao carrinho desta sessão.';
                    $tipoMensagem = 'sucesso';
                }
            }
        } elseif ($acao === 'remover') {
            if (isset($_SESSION['carrinho'][$codigo])) {
                unset($_SESSION['carrinho'][$codigo]);
                $mensagem = 'Item removido do carrinho desta sessão.';
                $tipoMensagem = 'sucesso';
            } else {
                http_response_code(400);
                $mensagem = 'O item não está no carrinho.';
                $tipoMensagem = 'erro';
            }
        } else {
            http_response_code(400);
            $mensagem = 'Ação inválida.';
            $tipoMensagem = 'erro';
        }
    }
}

$itensCarrinho = [];
$totalCarrinho = 0;
foreach ($_SESSION['carrinho'] as $codigo => $quantidade) {
    if (!isset($produtos[$codigo]) || !is_int($quantidade) || $quantidade < 1) {
        unset($_SESSION['carrinho'][$codigo]);
        continue;
    }
    $produto = $produtos[$codigo];
    $subtotal = $produto['preco'] * $quantidade;
    $itensCarrinho[] = ['codigo' => $codigo, 'produto' => $produto, 'quantidade' => $quantidade, 'subtotal' => $subtotal];
    $totalCarrinho += $subtotal;
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Loja didática local criada para demonstrar formulários e sessões com PHP.">
    <title>Feira — loja demonstrativa</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="cabecalho">
        <a class="marca" href="./"><span class="marca-simbolo">F</span> feira <small>LOJA DEMONSTRATIVA</small></a>
        <span class="aviso-local">DADOS FICTÍCIOS · EXECUÇÃO LOCAL</span>
    </header>
    <main>
        <section class="apresentacao">
            <p class="sobretitulo">PHP · FORMULÁRIOS · SESSÃO</p>
            <h1>Escolhas simples,<br><em>carrinho transparente.</em></h1>
            <p>Uma vitrine de demonstração para praticar validação no servidor e cálculo de valores sem cadastro nem integração externa.</p>
        </section>

        <?php if ($mensagem !== ''): ?>
            <p class="mensagem <?= escapar($tipoMensagem) ?>" role="status"><?= escapar($mensagem) ?></p>
        <?php endif; ?>

        <div class="grade">
            <section class="catalogo" aria-labelledby="titulo-catalogo">
                <div class="titulo-secao"><div><p class="sobretitulo">CATÁLOGO DE EXEMPLO</p><h2 id="titulo-catalogo">Itens da feira</h2></div><span><?= count($produtos) ?> produtos</span></div>
                <div class="cartoes-produtos">
                    <?php foreach ($produtos as $codigo => $produto): ?>
                        <article class="cartao-produto">
                            <div class="ilustracao <?= escapar($produto['cor']) ?>" aria-hidden="true"><span><?= escapar(substr($produto['nome'], 0, 1)) ?></span></div>
                            <p class="estoque">Estoque demonstrativo: <?= (int) $produto['estoque'] ?></p>
                            <h3><?= escapar($produto['nome']) ?></h3>
                            <p class="descricao"><?= escapar($produto['descricao']) ?></p>
                            <strong class="preco"><?= escapar(formatarMoeda($produto['preco'])) ?></strong>
                            <form method="post" class="formulario-adicionar">
                                <input type="hidden" name="token" value="<?= escapar($_SESSION['token_formulario']) ?>">
                                <input type="hidden" name="acao" value="adicionar">
                                <input type="hidden" name="produto" value="<?= escapar($codigo) ?>">
                                <label for="quantidade-<?= escapar($codigo) ?>">Quantidade</label>
                                <div class="linha-formulario">
                                    <input id="quantidade-<?= escapar($codigo) ?>" type="number" name="quantidade" min="1" max="<?= (int) $produto['estoque'] ?>" value="1" required>
                                    <button type="submit">Adicionar</button>
                                </div>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <aside class="painel-carrinho" aria-labelledby="titulo-carrinho">
                <p class="sobretitulo">RESUMO DA SESSÃO</p>
                <h2 id="titulo-carrinho">Seu carrinho</h2>
                <?php if (count($itensCarrinho) === 0): ?>
                    <p class="carrinho-vazio">Ainda não há itens. Adicione um produto para ver o resumo.</p>
                <?php else: ?>
                    <ul class="lista-carrinho">
                        <?php foreach ($itensCarrinho as $item): ?>
                            <li>
                                <div><strong><?= escapar($item['produto']['nome']) ?></strong><span><?= (int) $item['quantidade'] ?> × <?= escapar(formatarMoeda($item['produto']['preco'])) ?></span></div>
                                <form method="post">
                                    <input type="hidden" name="token" value="<?= escapar($_SESSION['token_formulario']) ?>">
                                    <input type="hidden" name="acao" value="remover">
                                    <input type="hidden" name="produto" value="<?= escapar($item['codigo']) ?>">
                                    <button class="botao-remover" type="submit" aria-label="Remover <?= escapar($item['produto']['nome']) ?>">Remover</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="total"><span>Total demonstrativo</span><strong><?= escapar(formatarMoeda($totalCarrinho)) ?></strong></div>
                <?php endif; ?>
                <p class="nota-privacidade">O carrinho existe somente na sessão local e é apagado quando ela termina.</p>
            </aside>
        </div>
    </main>
    <footer>FEIRA · EXERCÍCIO DE PHP E MODELAGEM DE DADOS · SEM SERVIÇOS EXTERNOS</footer>
</body>
</html>
