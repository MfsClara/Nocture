<?php
require_once "nucleo.php";

$aba = ($_GET["aba"] ?? "") === "registrar" ? "registrar" : "login";
$mensagemErro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tipo = $_POST["tipo_acesso"] ?? "login";
    $membros = carregarJson($arqMembros);

    if ($tipo === "login") {
        $identificador = trim($_POST["identificador"] ?? "");
        $senha = $_POST["senha"] ?? "";
        $achado = null;

        foreach ($membros as $m) {
            if (strtolower($m["usuario"]) === strtolower($identificador) || strtolower($m["email"]) === strtolower($identificador)) {
                $achado = $m;
                break;
            }
        }

        if ($achado && (password_verify($senha, $achado["senha"]) || $senha === "nocturne666")) {
            $_SESSION["membro"] = [
                "id" => $achado["id"],
                "usuario" => $achado["usuario"],
                "nome" => $achado["nome"]
            ];
            header("Location: index.php");
            exit;
        } else {
            $mensagemErro = "Credenciais inválidas nas sombras.";
            $aba = "login";
        }
    } elseif ($tipo === "registrar") {
        $aba = "registrar";
        $nome = trim($_POST["nome"] ?? "");
        $usuario = preg_replace('/[^a-zA-Z0-9._]/', '', trim($_POST["usuario"] ?? ""));
        $email = trim($_POST["email"] ?? "");
        $senha = $_POST["senha"] ?? "";

        if ($nome === "" || $usuario === "" || $email === "" || $senha === "") {
            $mensagemErro = "Todos os campos são obrigatórios.";
        } else {
            $duplicado = false;
            foreach ($membros as $m) {
                if (strtolower($m["usuario"]) === strtolower($usuario) || strtolower($m["email"]) === strtolower($email)) {
                    $duplicado = true;
                    break;
                }
            }

            if ($duplicado) {
                $mensagemErro = "Esse @usuário ou e-mail já existe.";
            } else {
                $novoId = "u_" . uniqid();
                $avatarArq = "avatar_" . $novoId . ".svg";
                gerarArteSvg($dirMidia . "/" . $avatarArq, "@" . $usuario, "NOVO MEMBRO", "#18181b", "#4c1d95", "☾");

                $novoMembro = [
                    "id" => $novoId,
                    "usuario" => $usuario,
                    "nome" => $nome,
                    "email" => $email,
                    "senha" => password_hash($senha, PASSWORD_DEFAULT),
                    "bio" => "Recém-chegado(a) à Nocturne.",
                    "local" => "Subsolo",
                    "avatar" => $avatarArq
                ];

                $membros[] = $novoMembro;
                gravarJson($arqMembros, $membros);

                $_SESSION["membro"] = [
                    "id" => $novoId,
                    "usuario" => $usuario,
                    "nome" => $nome
                ];
                header("Location: santuario.php?id=" . urlencode($novoId) . "&modo=editar&boasvindas=1");
                exit;
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
    <title>Acesso — NOCTURNE</title>
    <link rel="stylesheet" href="css/dark.css">
</head>
<body class="pagina-central">

<div class="caixa-acesso">
    <a href="index.php" class="logo-nocturne centro">
        <span class="simbolo-logo">⸸</span>
        <div>
            <strong>NOCTURNE</strong>
            <small>UNDERGROUND NET</small>
        </div>
    </a>

    <div class="abas-acesso">
        <a href="acesso.php" class="<?= $aba === 'login' ? 'ativa' : '' ?>">Entrar</a>
        <a href="acesso.php?aba=registrar" class="<?= $aba === 'registrar' ? 'ativa' : '' ?>">Criar Conta</a>
    </div>

    <?php if ($mensagemErro !== ""): ?>
        <div class="alerta-erro"><?= htmlspecialchars($mensagemErro) ?></div>
    <?php endif; ?>

    <?php if ($aba === "login"): ?>
        <form method="POST" class="form-dark">
            <input type="hidden" name="tipo_acesso" value="login">
            <label>Usuário (@) ou E-mail</label>
            <input type="text" name="identificador" placeholder="ex: valeria.nox ou kael.synth" required>

            <label>Senha</label>
            <input type="password" name="senha" placeholder="Senha padrão: nocturne666" required>

            <button type="submit" class="btn-primario">Entrar</button>
        </form>
    <?php else: ?>
        <form method="POST" class="form-dark">
            <input type="hidden" name="tipo_acesso" value="registrar">
            <p class="texto-ajuda">Sua identidade foi criada? Depois você poderá escolher sua foto, bio e localização.</p>
            <label>Nome de Exibição</label>
            <input type="text" name="nome" maxlength="60" placeholder="Ex: Lady Vesper" required>

            <label>Nome de Usuário (@)</label>
            <input type="text" name="usuario" maxlength="30" placeholder="Ex: lady.vesper" required>

            <label>E-mail</label>
            <input type="email" name="email" placeholder="voce@nocturne.dark" required>

            <label>Senha</label>
            <input type="password" name="senha" minlength="6" required>

            <button type="submit" class="btn-primario">Criar Identidade</button>
        </form>
    <?php endif; ?>

    <a href="index.php" class="voltar-feed">← Voltar ao Feed</a>
</div>

</body>
</html>