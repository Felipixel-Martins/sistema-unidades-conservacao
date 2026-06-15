<?php

declare(strict_types=1);

require __DIR__ . '/conexao.php';
require __DIR__ . '/helpers.php';

$sql = '
    SELECT
        u.id,
        u.nome,
        u.descricao,
        u.imagem,
        u.data_criacao,
        i.nome AS instituicao
    FROM unidade_conservacao u
    INNER JOIN instituicao i ON u.instituicao_id = i.id
    ORDER BY u.nome ASC
';

$resultado = mysqli_query($conexao, $sql);
$unidades = $resultado ? mysqli_fetch_all($resultado, MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unidades de Conservação</title>
    <link rel="icon" type="image/svg+xml" href="assets/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('style.css')); ?>">
</head>
<body>
    <header class="hero">
        <div class="container hero__content">
            <span class="hero__eyebrow">Patrimônio natural</span>
            <h1>Unidades de conservação em destaque</h1>
            <p>
                Explore áreas protegidas, conheça as instituições responsáveis e
                acompanhe os canais de comunicação de cada unidade.
            </p>
        </div>
    </header>

    <main class="container page-content">
        <section class="section-heading">
            <div>
                <span class="section-heading__eyebrow">Catálogo</span>
                <h2>Espaços preservados para conhecer e acompanhar</h2>
            </div>
            <p>
                Uma vitrine simples e organizada para consulta pública das unidades.
            </p>
        </section>

        <?php if ($unidades === []) : ?>
            <section class="empty-state">
                <h2>Nenhuma unidade encontrada</h2>
                <p>Cadastre registros no banco para exibir o catálogo nesta página.</p>
            </section>
        <?php else : ?>
            <section class="cards">
                <?php foreach ($unidades as $unidade) : ?>
                    <article class="card">
                        <img
                            class="card__image card__image--<?= e(getUnitImageVariant($unidade)); ?>"
                            src="<?= e(getUnitImage($unidade)); ?>"
                            alt="Imagem de <?= e($unidade['nome']); ?>"
                        >

                        <div class="card__content">
                            <div class="card__meta">
                                <span><?= e($unidade['instituicao']); ?></span>
                                <span><?= e(formatDate($unidade['data_criacao'] ?? null)); ?></span>
                            </div>

                            <h3><?= e($unidade['nome']); ?></h3>
                            <p><?= e($unidade['descricao']); ?></p>

                            <a class="btn btn--primary" href="detalhes.php?id=<?= (int) $unidade['id']; ?>">
                                Ver detalhes
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>