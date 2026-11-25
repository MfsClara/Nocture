<?php
require_once "nucleo.php";

$posts = carregarJson($arqPosts);
$membros = carregarJson($arqMembros);

usort($posts, function($a, $b) {
    return strtotime($b["data"]) - strtotime($a["data"]);
});

$filtro = trim($_GET["q"] ?? "");
$membroAtual = null;
$qtdMeusPosts = 0;
$qtdMinhasReacoes = 0;

if (estaConectado()) {
    $membroAtual = obterMembro($membros, $_SESSION["membro"]["id"]);
    if ($membroAtual) {
        foreach ($posts as $p) {
            if (isset($p["membro_id"]) && $p["membro_id"] === $membroAtual["id"]) {
                $qtdMeusPosts++;
                $qtdMinhasReacoes += count($p["votos"] ?? []);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOCTURNE // Feed </title>
    <link rel="stylesheet" href="css/dark.css">
</head>
<body>

<div class="grade-principal">
    <aside class="coluna-nav">
        <a href="index.php" class="logo-nocturne">
            <span class="simbolo-logo">⸸</span>
            <div>
                <strong>NOCTURNE</strong>
                <small>UNDERGROUND</small>
            </div>
        </a>

        <nav class="links-nav">
            <a href="index.php" class="<?= $filtro === '' ? 'ativo' : '' ?>">◈ Feed </a>
            <a href="index.php?q=dark" class="<?= $filtro !== '' ? 'ativo' : '' ?>">⌕ Explorar Temas</a>
            <?php if (estaConectado()): ?>
                <a href="postar.php">＋ Novo Registro</a>
                <a href="santuario.php?id=<?= urlencode($_SESSION["membro"]["id"]) ?>">☾ Meu Santuário</a>
                <a href="desconectar.php" class="link-sair">✕ Encerrar Sessão</a>
            <?php else: ?>
                <a href="acesso.php">☾ Entrar no Subsolo</a>
                <a href="acesso.php?aba=registrar">＋ Criar Identidade</a>
            <?php endif; ?>
        </nav>

        <div class="box-dica">
            <span class="tag-roxa">PERFIS ATIVOS</span>
            <p>Explore os santuários ou entre com a senha padrão <code>nocturne666</code>.</p>
        </div>
    </aside>

    <main class="coluna-feed">
        <form method="GET" action="index.php" class="form-busca">
            <input type="text" name="q" value="<?= htmlspecialchars($filtro) ?>" placeholder="Pesquisar por @perfil, tema, música, estética...">
            <button type="submit">Filtrar</button>
            <?php if ($filtro !== ""): ?>
                <a href="index.php" class="btn-limpar">Limpar</a>
            <?php endif; ?>
        </form>

        <?php
        $totalExibido = 0;
        foreach ($posts as $post):
            $membroIdPost = $post["membro_id"] ?? "";
            $autor = obterMembro($membros, $membroIdPost);
            if (!$autor) continue;

            if ($filtro !== "") {
                $conteudoBusca = mb_strtolower(($post["legenda"] ?? "") . " " . $autor["usuario"] . " " . $autor["nome"] . " " . $autor["bio"] . " " . ($post["local"] ?? ""));
                if (mb_strpos($conteudoBusca, mb_strtolower($filtro)) === false) {
                    continue;
                }
            }
            $totalExibido++;

            $votos = $post["votos"] ?? [];
            $comentarios = $post["comentarios"] ?? [];
            $jaReagiu = estaConectado() && in_array($_SESSION["membro"]["id"], $votos);

            $avatarAutor = $autor["avatar"];
            if (strpos($avatarAutor, "midia/") !== 0) {
                $avatarAutor = "midia/" . $avatarAutor;
            }

            // Garante que o caminho da imagem do post aponte corretamente para a pasta midia/
            $imagemPost = $post["imagem"];
            if (strpos($imagemPost, "midia/") !== 0) {
                $imagemPost = "midia/" . $imagemPost;
            }
        ?>
            <article class="cartao-post" id="reg-<?= htmlspecialchars($post["id"]) ?>">
                <header class="topo-post">
                    <a href="santuario.php?id=<?= urlencode($autor["id"]) ?>" class="info-autor">
                        <img src="<?= htmlspecialchars($avatarAutor) ?>" alt="Avatar">
                        <div>
                            <strong>@<?= htmlspecialchars($autor["usuario"]) ?></strong>
                            <span>⌖ <?= htmlspecialchars($post["local"] ?: "Zona Oculta") ?></span>
                        </div>
                    </a>
                    <time><?= date("d/m/Y • H:i", strtotime($post["data"])) ?></time>
                </header>

                <div class="moldura-imagem">
                    <img src="<?= htmlspecialchars($imagemPost) ?>" alt="Registro visual">
                </div>

                <div class="corpo-post">
                    <p class="texto-legenda"><?= nl2br(htmlspecialchars($post["legenda"])) ?></p>

                    <div class="barra-interacao">
                        <?php if (estaConectado()): ?>
                            <a href="index.php?reagir=<?= urlencode($post["id"]) ?>" class="btn-reacao <?= $jaReagiu ? 'ativo' : '' ?>">
                                <?= $jaReagiu ? "🖤" : "🤍" ?> <span><?= count($votos) ?></span>
                            </a>
                        <?php else: ?>
                            <span class="btn-reacao">🖤 <span><?= count($votos) ?></span></span>
                        <?php endif; ?>

                        <span class="contador-com">💬 <?= count($comentarios) ?> sussurros</span>
                    </div>

                    <div class="area-comentarios">
                        <?php foreach ($comentarios as $c): ?>
                            <?php $autorCom = obterMembro($membros, $c["membro_id"] ?? ""); ?>
                            <div class="linha-comentario">
                                <a href="santuario.php?id=<?= urlencode($c["membro_id"] ?? "") ?>">@<?= htmlspecialchars($autorCom ? $autorCom["usuario"] : "espectro") ?></a>
                                <span><?= htmlspecialchars($c["texto"] ?? "") ?></span>
                            </div>
                        <?php endforeach; ?>

                        <?php if (estaConectado()): ?>
                            <form method="POST" action="index.php" class="form-sussurro">
                                <input type="hidden" name="comando" value="comentar">
                                <input type="hidden" name="post_id" value="<?= htmlspecialchars($post["id"]) ?>">
                                <input type="text" name="mensagem" maxlength="240" placeholder="Deixar um sussurro nas sombras..." required>
                                <button type="submit">Enviar</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if ($totalExibido === 0): ?>
            <div class="aviso-vazio">Nenhum registro encontrado nas sombras para "<?= htmlspecialchars($filtro) ?>".</div>
        <?php endif; ?>
    </main>

    <aside class="coluna-lateral">
        <?php if ($membroAtual): 
            $avatarLogado = $membroAtual["avatar"];
            if (strpos($avatarLogado, "midia/") !== 0) {
                $avatarLogado = "midia/" . $avatarLogado;
            }
        ?>
            <div class="card-lateral">
                <div class="cabecalho-usuario">
                    <img src="<?= htmlspecialchars($avatarLogado) ?>" alt="Avatar">
                    <div>
                        <strong>@<?= htmlspecialchars($membroAtual["usuario"]) ?></strong>
                        <span><?= htmlspecialchars($membroAtual["nome"]) ?></span>
                    </div>
                </div>
                <p class="bio-resumo"><?= htmlspecialchars($membroAtual["bio"]) ?></p>
                <div class="metricas-usuario">
                    <div><strong><?= $qtdMeusPosts ?></strong><span>Posts</span></div>
                    <div><strong><?= $qtdMinhasReacoes ?></strong><span>Curtidas</span></div>
                </div>
                <a href="santuario.php?id=<?= urlencode($membroAtual["id"]) ?>&modo=editar" class="btn-contorno">Editar Santuário</a>
            </div>
        <?php else: ?>
            <div class="card-lateral">
                <span class="tag-roxa">ACESSO RESTRITO</span>
                <h3>Entre na Rede</h3>
                <p>Conecte-se para publicar registros, curtir e comentar nos perfis.</p>
                <a href="acesso.php" class="btn-primario">Fazer Login</a>
            </div>
        <?php endif; ?>

        <div class="card-lateral">
            <h4>Comunidade</h4>
            <div class="lista-membros">
                <?php foreach ($membros as $m): 
                    $avatarMembroLista = $m["avatar"];
                    if (strpos($avatarMembroLista, "midia/") !== 0) {
                        $avatarMembroLista = "midia/" . $avatarMembroLista;
                    }
                ?>
                    <a href="santuario.php?id=<?= urlencode($m["id"]) ?>" class="item-membro">
                        <img src="<?= htmlspecialchars($avatarMembroLista) ?>" alt="">
                        <div>
                            <strong>@<?= htmlspecialchars($m["usuario"]) ?></strong>
                            <small><?= htmlspecialchars($m["nome"]) ?></small>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>
</div>

</body>
</html>