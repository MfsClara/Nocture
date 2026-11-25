<?php
require_once "nucleo.php";

if (!estaConectado()) {
    header("Location: acesso.php");
    exit;
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $legenda = trim($_POST["legenda"] ?? "");
    $local = trim($_POST["local"] ?? "");

    if (!isset($_FILES["imagem"]) || $_FILES["imagem"]["error"] !== UPLOAD_ERR_OK) {
        $erro = "Escolha uma imagem para enviar.";
    } else {
        $tmp = $_FILES["imagem"]["tmp_name"];
        $mime = mime_content_type($tmp);
        $permitidos = ["image/jpeg" => "jpg", "image/png" => "png", "image/webp" => "webp"];

        if (!isset($permitidos[$mime])) {
            $erro = "Formato não suportado. Use JPG, PNG ou WEBP.";
        } elseif ($_FILES["imagem"]["size"] > 8 * 1024 * 1024) {
            $erro = "O arquivo deve ter no máximo 8MB.";
        } else {
            $nomeArquivo = "reg_" . bin2hex(random_bytes(6)) . "." . $permitidos[$mime];
            if (move_uploaded_file($tmp, $dirMidia . "/" . $nomeArquivo)) {
                $posts = carregarJson($arqPosts);
                $posts[] = [
                    "id" => uniqid("reg_"),
                    "membro_id" => $_SESSION["membro"]["id"],
                    "imagem" => $nomeArquivo,
                    "legenda" => $legenda,
                    "local" => $local ?: "Zona Subterrânea",
                    "data" => date("Y-m-d H:i:s"),
                    "votos" => [],
                    "comentarios" => []
                ];
                gravarJson($arqPosts, $posts);
                header("Location: index.php");
                exit;
            } else {
                $erro = "Erro ao salvar imagem no servidor.";
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
    <title>Novo Registro — NOCTURNE</title>
    <link rel="stylesheet" href="css/dark.css">
</head>
<body class="pagina-central">

<div class="caixa-acesso caixa-larga">
    <h2>Novo Registro Noturno</h2>
    <p class="subtexto">Compartilhe uma estética, som, livro ou momento sombrio.</p>

    <?php if ($erro !== ""): ?>
        <div class="alerta-erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="form-dark">
        <label>Imagem (JPG, PNG ou WEBP)</label>
        <input type="file" name="imagem" accept="image/jpeg,image/png,image/webp" required>

        <label>Legenda / Reflexão</label>
        <textarea name="legenda" rows="4" maxlength="1500" placeholder="Escreva algo sobre esse registro..."></textarea>

        <label>Localização</label>
        <input type="text" name="local" maxlength="80" placeholder="Ex: Subsolo Club, Catedral Velha...">

        <button type="submit" class="btn-primario">Publicar no Feed</button>
    </form>

    <a href="index.php" class="voltar-feed">← Cancelar e voltar</a>
</div>

</body>
</html>