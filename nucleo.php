<?php
session_start();

$dirBanco = __DIR__ . "/banco";
$dirMidia = __DIR__ . "/midia";

if (!is_dir($dirBanco)) {
    mkdir($dirBanco, 0777, true);
}

if (!is_dir($dirMidia)) {
    mkdir($dirMidia, 0777, true);
}

$arqMembros = $dirBanco . "/membros.json";
$arqPosts   = $dirBanco . "/postagem.json"; 
function carregarJson($caminho) {
    if (!file_exists($caminho)) {
        return [];
    }

    $conteudo = json_decode(file_get_contents($caminho), true);

    return is_array($conteudo) ? $conteudo : [];
}

function gravarJson($caminho, $dados) {
    file_put_contents(
        $caminho,
        json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );
}

function obterMembro($membros, $id) {
    foreach ($membros as $membro) {
        if (($membro["id"] ?? "") === $id) {
            return $membro;
        }
    }

    return null;
}

function estaConectado() {
    return isset($_SESSION["membro"]) && !empty($_SESSION["membro"]["id"]);
}


function gerarArteSvg($caminho, $titulo, $subtitulo, $corFundo = "#101827", $corDestaque = "#dc2626", $simbolo = "☾") {
    $titulo = htmlspecialchars($titulo, ENT_QUOTES | ENT_XML1, "UTF-8");
    $subtitulo = htmlspecialchars($subtitulo, ENT_QUOTES | ENT_XML1, "UTF-8");
    $simbolo = htmlspecialchars($simbolo, ENT_QUOTES | ENT_XML1, "UTF-8");
    $svg = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<svg xmlns="http://www.w3.org/2000/svg" width="500" height="500" viewBox="0 0 500 500">'
        . '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
        . '<stop offset="0" stop-color="' . $corFundo . '"/><stop offset="1" stop-color="' . $corDestaque . '"/>'
        . '</linearGradient></defs><rect width="500" height="500" rx="42" fill="url(#g)"/>'
        . '<circle cx="250" cy="190" r="112" fill="none" stroke="#ffffff" stroke-opacity=".16" stroke-width="2"/>'
        . '<text x="250" y="235" text-anchor="middle" font-size="112" fill="#ffffff">' . $simbolo . '</text>'
        . '<text x="250" y="375" text-anchor="middle" font-family="Arial,sans-serif" font-size="30" font-weight="bold" fill="#ffffff">' . $titulo . '</text>'
        . '<text x="250" y="414" text-anchor="middle" font-family="Arial,sans-serif" font-size="17" letter-spacing="4" fill="#dbeafe">' . $subtitulo . '</text></svg>';
    return file_put_contents($caminho, $svg) !== false;
}
function obterListaMembrosOficial() {
    $senhaPadrao = password_hash("nocturne666", PASSWORD_DEFAULT);

    return [
        ["id" => "u01", "grupo" => "gta", "usuario" => "midnight.jason", "nome" => "Jason Duval", "email" => "jason@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Asfalto e sobrevivência em Leonida.", "local" => "Vice City", "avatar" => "avatarjason.jpeg"],
        ["id" => "u02", "grupo" => "gta", "usuario" => "caminos.out", "nome" => "Lucia Caminos", "email" => "lucia@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Correr pra frente sem olhar pra trás.", "local" => "Vice City", "avatar" => "avatarlucia.jpg"],
        ["id" => "u03", "grupo" => "gta", "usuario" => "static.donnie", "nome" => "Donnie", "email" => "donnie@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Esquemas e tecnologia nas sombras.", "local" => "Vice City", "avatar" => "avatardonnie.webp"],
        ["id" => "u04", "grupo" => "gta", "usuario" => "brian.rev", "nome" => "Brian", "email" => "brian@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Manobras e fuga rápida.", "local" => "Vice City", "avatar" => "avatarbrian.jpeg"],
        ["id" => "u05", "grupo" => "gta", "usuario" => "iron.cal", "nome" => "Cal", "email" => "cal@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Negócios na área industrial.", "local" => "Vice City", "avatar" => "avatarcal.webp"],
        ["id" => "u06", "grupo" => "gta", "usuario" => "mattia.lock", "nome" => "Mattia", "email" => "mattia@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Controle de perímetro.", "local" => "Vice City", "avatar" => "avatarmattia.webp"],
        ["id" => "u07", "grupo" => "gta", "usuario" => "kat.eyes", "nome" => "Kat", "email" => "kat@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Olhos em tudo na cidade.", "local" => "Vice City", "avatar" => "avatarkat.jpeg"],

        ["id" => "u08", "grupo" => "deathnote", "usuario" => "new.god", "nome" => "Light Yagami", "email" => "light@nocturne.dark", "senha" => $senhaPadrao, "bio" => "O deus do novo mundo.", "local" => "Tóquio", "avatar" => "avatarlight.jpg"],
        ["id" => "u09", "grupo" => "deathnote", "usuario" => "misamisa", "nome" => "Misa Amane", "email" => "misa@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Devota à justiça de Kira.", "local" => "Tóquio", "avatar" => "avatarmisa.jpg"],
        ["id" => "u10", "grupo" => "deathnote", "usuario" => "apple.death", "nome" => "Ryuk", "email" => "ryuk@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Maçãs e tédio mortal.", "local" => "Reino dos Shinigami", "avatar" => "avatarryuk.jpg"],
        ["id" => "u11", "grupo" => "deathnote", "usuario" => "sugar.l", "nome" => "L Lawliet", "email" => "l@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Investigando o caso Kira.", "local" => "Tóquio", "avatar" => "avatarl.jpg"],
        ["id" => "u12", "grupo" => "deathnote", "usuario" => "taskforce.mat", "nome" => "Matsuda", "email" => "matsuda@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Força-tarefa japonesa.", "local" => "Tóquio", "avatar" => "avatarmatsuda.jpg"],

        ["id" => "u13", "grupo" => "musica", "usuario" => "joon.archive", "nome" => "RM (BTS)", "email" => "rm@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Monochrome e poesia noturna.", "local" => "Seul", "avatar" => "avatarrm.jpg"],
        ["id" => "u14", "grupo" => "musica", "usuario" => "worldwide.jin", "nome" => "Jin (BTS)", "email" => "jin@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Melodias e calmaria.", "local" => "Seul", "avatar" => "avatarjin.jpg"],
        ["id" => "u15", "grupo" => "musica", "usuario" => "agustd.beats", "nome" => "Suga (BTS)", "email" => "suga@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Beats de estúdio na madrugada.", "local" => "Seul", "avatar" => "avatarsuga.jpg"],
        ["id" => "u16", "grupo" => "musica", "usuario" => "hobi.sun", "nome" => "J-Hope (BTS)", "email" => "jhope@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Ritmo e energia nas sombras.", "local" => "Seul", "avatar" => "avatarhobi.jpg"],
        ["id" => "u17", "grupo" => "musica", "usuario" => "serendipity.jm", "nome" => "Jimin (BTS)", "email" => "jimin@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Dança e notas de piano.", "local" => "Seul", "avatar" => "avatarjimin.jpg"],
        ["id" => "u18", "grupo" => "musica", "usuario" => "v.jazz", "nome" => "V (BTS)", "email" => "v@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Jazz analógico e polaroids.", "local" => "Seul", "avatar" => "avatartaehyung.jpg"],
        ["id" => "u19", "grupo" => "musica", "usuario" => "golden.jk", "nome" => "Jungkook (BTS)", "email" => "jk@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Vocais noturnos e criatividade.", "local" => "Seul", "avatar" => "avatarjungkook.jpg"],
        ["id" => "u20", "grupo" => "musica", "usuario" => "am.humbug", "nome" => "Arctic Monkeys", "email" => "arctic@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Sleepless nights e neon.", "local" => "Sheffield", "avatar" => "avatararctic.jpg"],
        ["id" => "u21", "grupo" => "musica", "usuario" => "malcolm.tunes", "nome" => "Malcolm Todd", "email" => "malcolm@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Indie pop e guitarras suaves.", "local" => "Los Angeles", "avatar" => "avatarmalcolm.jpg"],
        ["id" => "u22", "grupo" => "musica", "usuario" => "moonlight.ag", "nome" => "Ariana Grande", "email" => "ariana@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Eternal sunshine e vocais celestiais.", "local" => "New York", "avatar" => "avatarariana.jpg"],
        ["id" => "u23", "grupo" => "musica", "usuario" => "gold.chains", "nome" => "Bruno Mars", "email" => "bruno@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Funk, soul e ritmo pesado.", "local" => "Las Vegas", "avatar" => "avatargolden.jpg"],

        ["id" => "u24", "grupo" => "haikyuu", "usuario" => "grand.king", "nome" => "Toru Oikawa", "email" => "oikawa@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Perfeição e treino em quadra.", "local" => "Miyagi", "avatar" => "avataroikawa.jpg"],
        ["id" => "u25", "grupo" => "haikyuu", "usuario" => "spiker.rock", "nome" => "Hajime Iwaizumi", "email" => "iwaizumi@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Força e apoio à equipe.", "local" => "Miyagi", "avatar" => "avatariwazume.jpg"],
        ["id" => "u26", "grupo" => "haikyuu", "usuario" => "exact.toby", "nome" => "Tobio Kageyama", "email" => "kageyama@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Precisão absoluta no levantamento.", "local" => "Miyagi", "avatar" => "avatarkageyama.jpg"],
        ["id" => "u27", "grupo" => "haikyuu", "usuario" => "fly.shoyo", "nome" => "Shoyo Hinata", "email" => "hinata@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Voando alto rumo ao topo.", "local" => "Miyagi", "avatar" => "avatarhinata.jpg"],
        ["id" => "u28", "grupo" => "haikyuu", "usuario" => "guardian.nsy", "nome" => "Yu Nishinoya", "email" => "nishinoya@nocturne.dark", "senha" => $senhaPadrao, "bio" => "A defesa de Karasuno nunca cai.", "local" => "Miyagi", "avatar" => "avatarnishinoya.jpg"],
        ["id" => "u29", "grupo" => "haikyuu", "usuario" => "tanaka.rage", "nome" => "Ryunosuke Tanaka", "email" => "tanaka@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Garra e barulho em quadra.", "local" => "Miyagi", "avatar" => "avatartanaka.jpg"],
        ["id" => "u30", "grupo" => "haikyuu", "usuario" => "captain.daichi", "nome" => "Daichi Sawamura", "email" => "daichi@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Liderança e firmeza.", "local" => "Miyagi", "avatar" => "avatardaichi.jpg"],
        ["id" => "u31", "grupo" => "haikyuu", "usuario" => "tsukishima.kei", "nome" => "Kei Tsukishima", "email" => "tsukishima@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Bloqueio estratégico e silêncio.", "local" => "Miyagi", "avatar" => "avatartsukisima.jpg"],
        ["id" => "u32", "grupo" => "haikyuu", "usuario" => "yama.serve", "nome" => "Tadashi Yamaguchi", "email" => "yamaguchi@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Saque viagem e evolução.", "local" => "Miyagi", "avatar" => "avataryamaguchi.jpg"],
        ["id" => "u33", "grupo" => "haikyuu", "usuario" => "hey.hey.bokuto", "nome" => "Kotaro Bokuto", "email" => "bokuto@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Hey hey hey! O ás chegou.", "local" => "Tóquio", "avatar" => "avatarppfp.jpg"],
        ["id" => "u34", "grupo" => "haikyuu", "usuario" => "akaashi.calm", "nome" => "Keiji Akaashi", "email" => "akaashi@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Foco e paciência.", "local" => "Tóquio", "avatar" => "avatarakaashi.jpg"],
        ["id" => "u35", "grupo" => "haikyuu", "usuario" => "nekoma.cat", "nome" => "Tetsuro Kuroo", "email" => "kuroo@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Bloqueios táticos de Nekoma.", "local" => "Tóquio", "avatar" => "avatarkuroo.jpg"],
        ["id" => "u36", "grupo" => "haikyuu", "usuario" => "brain.kenma", "nome" => "Kenma Kozume", "email" => "kenma@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Videogames e estratégia em quadra.", "local" => "Tóquio", "avatar" => "avatarkenma.jpg"],
        ["id" => "u44", "grupo" => "haikyuu", "usuario" => "fox.setter", "nome" => "Atsumu Miya", "email" => "atsumu@nocturne.dark", "senha" => $senhaPadrao, "bio" => "O melhor levantador.", "local" => "Hyogo", "avatar" => "avataratsumu.jpg"],
        ["id" => "u45", "grupo" => "haikyuu", "usuario" => "samu.onigiri", "nome" => "Osamu Miya", "email" => "osamu@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Onigiri é melhor do que jogar voleibol.", "local" => "Hyogo", "avatar" => "avatarosamu.jpg"],

        ["id" => "u37", "grupo" => "konoha", "usuario" => "uzumaki.wind", "nome" => "Naruto Uzumaki", "email" => "naruto@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Lámen à meia-noite e foco no sonho.", "local" => "Konoha", "avatar" => "avatarnaruto.jpg"],
        ["id" => "u38", "grupo" => "konoha", "usuario" => "sharingan.book", "nome" => "Kakashi Hatake", "email" => "kakashi@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Sharingan e livros de Icha Icha.", "local" => "Konoha", "avatar" => "avatarkakashi.jpg"],
        ["id" => "u39", "grupo" => "konoha", "usuario" => "avenger.sasuke", "nome" => "Sasuke Uchiha", "email" => "sasuke@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Caminho solitário na escuridão.", "local" => "Rota Sombria", "avatar" => "avatarsasuke.jpg"],
        ["id" => "u40", "grupo" => "konoha", "usuario" => "medical.skr", "nome" => "Sakura Haruno", "email" => "sakura@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Força bruta e medicina ninja.", "local" => "Konoha", "avatar" => "avatarsakura.jpg"],
        ["id" => "u41", "grupo" => "konoha", "usuario" => "gentle.hyuga", "nome" => "Hinata Hyuga", "email" => "hinatah@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Determinação gentil.", "local" => "Konoha", "avatar" => "avatarhinatanaruto.jpg"],
        ["id" => "u42", "grupo" => "konoha", "usuario" => "masked.tobi", "nome" => "Obito Uchiha (Tobi)", "email" => "obito@nocturne.dark", "senha" => $senhaPadrao, "bio" => "O mundo real é ilusão.", "local" => "Esconderijo Akatsuki", "avatar" => "avatartobi.jpg"],

        ["id" => "u43", "grupo" => "terror", "usuario" => "spirit.net", "nome" => "Akito Izanami", "email" => "akito@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Paranormal e chuva em Shibuya.", "local" => "Shibuya", "avatar" => "avatarakito.jpg"],

        ["id" => "u46", "grupo" => "residentevil", "usuario" => "kennedy.leon", "nome" => "Leon S. Kennedy", "email" => "leon@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Entre missões, sobrevivência e noites sem descanso.", "local" => "Raccoon City", "avatar" => "avatarleon.jpg"],
        ["id" => "u47", "grupo" => "residentevil", "usuario" => "ada.red", "nome" => "Ada Wong", "email" => "ada@nocturne.dark", "senha" => $senhaPadrao, "bio" => "Sempre um passo à frente.", "local" => "Desconhecido", "avatar" => "avatarada.jpg"]
    ];
}

$membrosExistentes = carregarJson($arqMembros);
$membrosOficiais   = obterListaMembrosOficial();

$idsMembros     = array_column($membrosExistentes, "id");
$alterouMembros = false;

foreach ($membrosOficiais as $membro) {
    if (!in_array($membro["id"], $idsMembros, true)) {
        $membrosExistentes[] = $membro;
        $idsMembros[]        = $membro["id"];
        $alterouMembros      = true;
    }
}

if (!file_exists($arqMembros) || $alterouMembros) {
    gravarJson($arqMembros, $membrosExistentes);
}

if (!file_exists($arqPosts)) {
    gravarJson($arqPosts, []);
}

if (isset($_GET["reagir"]) && estaConectado()) {
    $idPost   = $_GET["reagir"];
    $idMembro = $_SESSION["membro"]["id"];

    $posts = carregarJson($arqPosts);

    foreach ($posts as $k => $post) {
        if (($post["id"] ?? "") === $idPost) {
            if (!isset($posts[$k]["votos"]) || !is_array($posts[$k]["votos"])) {
                $posts[$k]["votos"] = [];
            }

            if (in_array($idMembro, $posts[$k]["votos"], true)) {
                $posts[$k]["votos"] = array_values(
                    array_diff($posts[$k]["votos"], [$idMembro])
                );
            } else {
                $posts[$k]["votos"][] = $idMembro;
            }

            gravarJson($arqPosts, $posts);
            break;
        }
    }

    $destino = $_SERVER["HTTP_REFERER"] ?? "index.php";
    header("Location: " . $destino);
    exit;
}
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["comando"] ?? "") === "comentar" &&
    estaConectado()
) {
    $idPost   = $_POST["post_id"] ?? "";
    $mensagem = trim($_POST["mensagem"] ?? "");

    if ($idPost !== "" && $mensagem !== "") {
        $posts = carregarJson($arqPosts);

        foreach ($posts as $k => $post) {
            if (($post["id"] ?? "") === $idPost) {
                if (!isset($posts[$k]["comentarios"]) || !is_array($posts[$k]["comentarios"])) {
                    $posts[$k]["comentarios"] = [];
                }

                $posts[$k]["comentarios"][] = [
                    "membro_id" => $_SESSION["membro"]["id"],
                    "texto"     => $mensagem,
                    "data"      => date("Y-m-d H:i:s")
                ];

                gravarJson($arqPosts, $posts);
                break;
            }
        }
    }

    $destino = $_SERVER["HTTP_REFERER"] ?? "index.php";
    header("Location: " . $destino);
    exit;
}