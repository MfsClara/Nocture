<?php
require_once "nucleo.php";

$idAlvo = $_GET["id"] ?? "";
$membros = carregarJson($arqMembros);
$posts = carregarJson($arqPosts);
$indice = null;

foreach ($membros as $i => $m) {
    if (($m["id"] ?? "") === $idAlvo) { $indice = $i; break; }
}
if ($indice === null) { header("Location: index.php"); exit; }

$donoDoPerfil = estaConectado() && $_SESSION["membro"]["id"] === $membros[$indice]["id"];
if (!$donoDoPerfil) { header("Location: index.php"); exit; }

$erro = "";
$boasVindas = ($_GET["boasvindas"] ?? "") === "1";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["atualizar_santuario"])) {
    $nome = trim($_POST["nome"] ?? "");
    $usuario = preg_replace('/[^a-zA-Z0-9._]/', '', trim($_POST["usuario"] ?? ""));
    $bio = trim($_POST["bio"] ?? "");
    $local = trim($_POST["local"] ?? "");

    if ($nome === "" || $usuario === "") {
        $erro = "Preencha seu nome e seu @usuário.";
    } else {
        $conflito = false;
        foreach ($membros as $j => $m) {
            if ($j !== $indice && strtolower($m["usuario"] ?? "") === strtolower($usuario)) {
                $conflito = true; break;
            }
        }
        if ($conflito) {
            $erro = "Esse @usuário já está sendo utilizado.";
        } else {
            $membros[$indice]["nome"] = $nome;
            $membros[$indice]["usuario"] = $usuario;
            $membros[$indice]["bio"] = $bio;
            $membros[$indice]["local"] = $local ?: "Subsolo";

            if (isset($_FILES["novo_avatar"]) && $_FILES["novo_avatar"]["error"] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES["novo_avatar"]["error"] !== UPLOAD_ERR_OK) {
                    $erro = "Não foi possível enviar a imagem. Tente novamente.";
                } else {
                    $mime = mime_content_type($_FILES["novo_avatar"]["tmp_name"]);
                    $extensoes = ["image/jpeg" => "jpg", "image/png" => "png", "image/webp" => "webp"];
                    if (!isset($extensoes[$mime]) || $_FILES["novo_avatar"]["size"] > 5 * 1024 * 1024) {
                        $erro = "Use uma imagem JPG, PNG ou WEBP de até 5MB.";
                    } else {
                        $arquivoNovo = "avatar_" . bin2hex(random_bytes(8)) . "." . $extensoes[$mime];
                        if (move_uploaded_file($_FILES["novo_avatar"]["tmp_name"], $dirMidia . "/" . $arquivoNovo)) {
                            $membros[$indice]["avatar"] = $arquivoNovo;
                        } else { $erro = "Não foi possível salvar sua foto."; }
                    }
                }
            }
            if ($erro === "") {
                gravarJson($arqMembros, $membros);
                $_SESSION["membro"] = [
                    "id" => $membros[$indice]["id"],
                    "usuario" => $usuario,
                    "nome" => $nome
                ];
                header("Location: santuario.php?id=" . urlencode($idAlvo));
                exit;
            }
        }
    }
}

$perfil = $membros[$indice];
$galeria = [];
$somaCurtidas = 0;
foreach ($posts as $p) {
    if (($p["membro_id"] ?? "") === $perfil["id"]) {
        $galeria[] = $p;
        $somaCurtidas += count($p["votos"] ?? []);
    }
}
usort($galeria, fn($a, $b) => strtotime($b["data"] ?? "") - strtotime($a["data"] ?? ""));
$modoEdicao = (($_GET["modo"] ?? "") === "editar") || $erro !== "";
$avatarPerfil = $perfil["avatar"] ?? "";
if ($avatarPerfil !== "" && strpos($avatarPerfil, "midia/") !== 0) $avatarPerfil = "midia/" . $avatarPerfil;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@<?= htmlspecialchars($perfil["usuario"]) ?> — NOCTURNE</title>
    <link rel="stylesheet" href="css/dark.css">
</head>
<body>
<div class="grade-santuario">
    <aside class="coluna-nav">
        <a href="index.php" class="logo-nocturne"><span class="simbolo-logo">⸸</span><div><strong>NOCTURNE</strong><small>UNDERGROUND NET</small></div></a>
        <nav class="links-nav">
            <a href="index.php">◈ Feed Noturno</a>
            <a href="postar.php">＋ Novo Registro</a>
            <a href="santuario.php?id=<?= urlencode($perfil["id"]) ?>" class="ativo">☾ Meu Santuário</a>
            <a href="desconectar.php" class="link-sair">✕ Encerrar Sessão</a>
        </nav>
    </aside>

    <main class="conteudo-santuario">
        <?php if ($boasVindas && $modoEdicao): ?>
            <section class="boas-vindas-perfil">
                <span class="tag-azul">IDENTIDADE CRIADA</span>
                <h1>Agora, personalize seu santuário.</h1>
                <p>Adicione uma foto, escolha como quer aparecer na NOCTURNE e conte um pouco sobre você. Quando terminar, salve para entrar no feed.</p>
            </section>
        <?php endif; ?>

        <section class="cabecalho-santuario">
            <div class="capa-escura"></div>
            <div class="dados-santuario">
                <img src="<?= htmlspecialchars($avatarPerfil) ?>" alt="Avatar" class="avatar-perfil">
                <div class="textos-perfil">
                    <h1>@<?= htmlspecialchars($perfil["usuario"]) ?></h1>
                    <h2><?= htmlspecialchars($perfil["nome"]) ?></h2>
                    <p><?= htmlspecialchars($perfil["bio"] ?? "") ?></p>
                    <span class="local-perfil">⌖ <?= htmlspecialchars($perfil["local"] ?? "Subsolo") ?></span>
                </div>
                <?php if (!$modoEdicao): ?><a href="santuario.php?id=<?= urlencode($perfil["id"]) ?>&modo=editar" class="btn-contorno">Editar Perfil</a><?php endif; ?>
            </div>

            <?php if ($modoEdicao): ?>
                <?php if ($erro !== ""): ?><div class="alerta-erro perfil-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
                <form method="POST" enctype="multipart/form-data" class="form-dark form-edicao">
                    <input type="hidden" name="atualizar_santuario" value="1">
                    <div class="grade-form-perfil">
                        <div><label>Nome de exibição</label><input type="text" name="nome" maxlength="60" value="<?= htmlspecialchars($perfil["nome"]) ?>" required></div>
                        <div><label>Nome de usuário (@)</label><input type="text" name="usuario" maxlength="30" value="<?= htmlspecialchars($perfil["usuario"]) ?>" required></div>
                        <div class="campo-completo"><label>Biografia</label><textarea name="bio" maxlength="160" rows="3" placeholder="Como você se descreveria?"><?= htmlspecialchars($perfil["bio"] ?? "") ?></textarea><small>Até 160 caracteres.</small></div>
                        <div><label>Local / Refúgio</label><input type="text" name="local" maxlength="60" value="<?= htmlspecialchars($perfil["local"] ?? "") ?>" placeholder="Sua cidade ou lugar favorito"></div>
                        <div><label>Foto de perfil</label><input type="file" name="novo_avatar" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG ou WEBP • até 5MB</small></div>
                    </div>
                    <div class="linha-botoes">
                        <button type="submit" class="btn-primario">Salvar meu perfil</button>
                        <a href="santuario.php?id=<?= urlencode($perfil["id"]) ?>" class="btn-contorno">Cancelar</a>
                    </div>
                </form>
            <?php endif; ?>

            <div class="stats-santuario">
                <div><strong><?= count($galeria) ?></strong><span>Registros</span></div>
                <div><strong><?= $somaCurtidas ?></strong><span>Curtidas Recebidas</span></div>
            </div>
        </section>

        <h3 class="titulo-secao">Galeria de Registros</h3>
        <?php if (!$galeria): ?><div class="aviso-vazio">Seu santuário ainda está vazio. Faça seu primeiro registro no feed.</div><?php endif; ?>
        <div class="grade-galeria">
            <?php foreach ($galeria as $item):
                $img = $item["imagem"] ?? "";
                if ($img !== "" && strpos($img, "midia/") !== 0) $img = "midia/" . $img;
            ?>
                <a href="index.php#reg-<?= urlencode($item["id"]) ?>" class="item-galeria">
                    <img src="<?= htmlspecialchars($img) ?>" alt="">
                    <div class="overlay-galeria"><span>♥ <?= count($item["votos"] ?? []) ?></span><span>💬 <?= count($item["comentarios"] ?? []) ?></span></div>
                </a>
            <?php endforeach; ?>
        </div>
    </main>
</div>
</body>
</html>
