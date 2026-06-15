<?php

declare(strict_types=1);

require __DIR__ . '/conexao.php';
require __DIR__ . '/helpers.php';

$id = getIntParam($_GET, 'id');

if ($id === null) {
    redirect('index.php');
}

$sqlUnidade = '
    SELECT
        u.id,
        u.nome,
        u.descricao,
        u.imagem,
        u.data_criacao,
        i.nome AS instituicao,
        GROUP_CONCAT(CONCAT(m.nome, " - ", m.estado) ORDER BY m.nome SEPARATOR ", ") AS municipios
    FROM unidade_conservacao u
    INNER JOIN instituicao i ON u.instituicao_id = i.id
    LEFT JOIN unidade_municipio um ON um.unidade_id = u.id
    LEFT JOIN municipio m ON m.id = um.municipio_id
    WHERE u.id = ?
    GROUP BY u.id, i.nome
';

$stmtUnidade = mysqli_prepare($conexao, $sqlUnidade);
$unidade = null;

if ($stmtUnidade) {
    mysqli_stmt_bind_param($stmtUnidade, 'i', $id);
    mysqli_stmt_execute($stmtUnidade);
    $resultadoUnidade = mysqli_stmt_get_result($stmtUnidade);
    $unidade = $resultadoUnidade ? mysqli_fetch_assoc($resultadoUnidade) : null;
    mysqli_stmt_close($stmtUnidade);
}

if (!$unidade) {
    redirect('index.php');
}

$sqlComunicacoes = '
    SELECT titulo, descricao, data_hora, email, status
    FROM comunicacao
    WHERE unidade_id = ?
    ORDER BY data_hora DESC
';

$stmtComunicacoes = mysqli_prepare($conexao, $sqlComunicacoes);
$comunicacoes = [];

if ($stmtComunicacoes) {
    mysqli_stmt_bind_param($stmtComunicacoes, 'i', $id);
    mysqli_stmt_execute($stmtComunicacoes);
    $resultadoComunicacoes = mysqli_stmt_get_result($stmtComunicacoes);
    $comunicacoes = $resultadoComunicacoes
        ? mysqli_fetch_all($resultadoComunicacoes, MYSQLI_ASSOC)
        : [];
    mysqli_stmt_close($stmtComunicacoes);
}

$status = $_GET['status'] ?? null;
$isApaBaleiaFranca = isApaBaleiaFranca($unidade);
$isSerraDoTabuleiro = isSerraDoTabuleiro($unidade);
$isRioVermelho = isRioVermelho($unidade);
$isArvoredo = isArvoredo($unidade);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($unidade['nome']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('style.css')); ?>">
</head>
<body>

    <header class="hero hero--compact">
        <div class="container hero__content">
            <a class="back-link back-link--button" href="index.php">← Voltar para a listagem</a>
            <span class="hero__eyebrow">Detalhes da unidade</span>
            <h1><?= e($unidade['nome']); ?></h1>
            <p><?= e($unidade['descricao']); ?></p>
        </div>
    </header>

    <main class="container page-content page-content--details">
        <section class="details-grid">
            <article class="panel panel--highlight">
                <img
                    class="details-image details-image--<?= e(getUnitImageVariant($unidade)); ?>"
                    src="<?= e(getUnitImage($unidade)); ?>"
                    alt="Imagem de <?= e($unidade['nome']); ?>"
                >

                <div class="detail-list">
                    <div>
                        <span>Instituição</span>
                        <strong><?= e($unidade['instituicao']); ?></strong>
                    </div>
                    <div>
                        <span>Data de criação</span>
                        <strong><?= e(formatDate($unidade['data_criacao'] ?? null)); ?></strong>
                    </div>
                    <div>
                        <span>Municípios</span>
                        <strong><?= e($unidade['municipios'] ?: 'Não informado'); ?></strong>
                    </div>
                </div>
            </article>

            <aside class="panel">
                <div class="panel__header">
                    <h2>Enviar comunicação</h2>
                    <p>Compartilhe uma observação ou mensagem relacionada a esta unidade.</p>
                </div>

                <?php if ($status === 'ok') : ?>
                    <div class="alert alert--success">Comunicação enviada com sucesso.</div>
                <?php elseif ($status === 'erro') : ?>
                    <div class="alert alert--error">Não foi possível enviar a comunicação. Revise os campos.</div>
                <?php endif; ?>

                <form action="salvar_comunicacao.php" method="POST" class="form">
                    <input type="hidden" name="unidade_id" value="<?= $id; ?>">

                    <label for="titulo">Título</label>
                    <input id="titulo" type="text" name="titulo" maxlength="200" required>

                    <label for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao" rows="5" required></textarea>

                    <label for="email">E-mail</label>
                    <input id="email" type="email" name="email" maxlength="150" required>

                    <button class="btn btn--primary btn--full" type="submit">Enviar comunicação</button>
                </form>
            </aside>
        </section>

        <?php if ($isApaBaleiaFranca) : ?>
            <section class="section-heading section-heading--spaced">
                <div>
                    <span class="section-heading__eyebrow">Apresentação institucional</span>
                    <h2>Sobre a APA da Baleia Franca</h2>
                </div>
                <p>
                    Conteúdo institucional organizado para consulta pública, educação ambiental e apoio ao turismo responsável.
                </p>
            </section>

            <section class="profile-layout">
                <article class="panel profile-intro">
                    <span class="profile-tag">Unidade de Conservação de Uso Sustentável</span>
                    <h2>A APABF protege habitats essenciais para a baleia franca-austral e para a biodiversidade costeira catarinense.</h2>
                    <p>
                        A Área de Proteção Ambiental da Baleia Franca (APABF) é uma Unidade de Conservação de Uso Sustentável criada em 14 de setembro de 2000, por meio de Decreto Federal. A unidade abrange cerca de 154.867 hectares da costa centro-sul de Santa Catarina, protegendo importantes ecossistemas marinhos e costeiros, além de áreas fundamentais para a reprodução da baleia franca-austral (Eubalaena australis).
                    </p>
                    <p>
                        A criação da APA da Baleia Franca surgiu da necessidade de preservar habitats essenciais para espécies migratórias ameaçadas, especialmente os ambientes costeiros utilizados pelas baleias durante o período reprodutivo. Esses habitats possuem grande relevância ecológica, pois funcionam como áreas de abrigo, reprodução e desenvolvimento dos filhotes.
                    </p>
                    <p>
                        A conservação das áreas marinhas e costeiras é considerada estratégica para a manutenção da biodiversidade. Diferentemente das unidades terrestres, as áreas protegidas marinhas desempenham um papel fundamental na proteção de partes vitais dos ciclos biológicos das espécies, contribuindo para a conservação dos recursos naturais em escala regional e nacional.
                    </p>
                </article>

                <aside class="panel profile-facts">
                    <h3>Panorama rápido</h3>
                    <div class="facts-grid">
                        <div class="fact-card">
                            <span>Criação</span>
                            <strong>14/09/2000</strong>
                        </div>
                        <div class="fact-card">
                            <span>Área protegida</span>
                            <strong>154.867 hectares</strong>
                        </div>
                        <div class="fact-card">
                            <span>Gestão</span>
                            <strong>ICMBio</strong>
                        </div>
                        <div class="fact-card">
                            <span>Foco</span>
                            <strong>Conservação marinha e costeira</strong>
                        </div>
                    </div>
                </aside>
            </section>

            <section class="feature-grid">
                <article class="panel">
                    <h3>Importância ecológica</h3>
                    <p>
                        A APA protege enseadas utilizadas por fêmeas e filhotes de baleia franca, além de ecossistemas associados como costões rochosos, dunas, banhados, lagoas e remanescentes de Mata Atlântica. A região apresenta elevada importância ambiental, paisagística e científica, funcionando também como espaço para pesquisas, educação ambiental e turismo sustentável.
                    </p>
                    <p>
                        A unidade protege ambientes fundamentais para a reprodução da baleia franca-austral e contribui para a conservação de diversos ecossistemas costeiros e marinhos. Além das baleias, a APA abriga rica biodiversidade associada à Mata Atlântica, lagoas costeiras, restingas, dunas e áreas úmidas.
                    </p>
                    <p>
                        As áreas protegidas marinhas são consideradas essenciais para garantir a manutenção dos ciclos ecológicos, o equilíbrio ambiental e a recuperação populacional de espécies ameaçadas.
                    </p>
                </article>

                <article class="panel">
                    <h3>Turismo e conservação</h3>
                    <p>
                        O turismo de observação de baleias é uma das principais atividades desenvolvidas na região e representa importante ferramenta de conscientização ambiental e desenvolvimento sustentável.
                    </p>
                    <p>
                        Com base nessas preocupações, o Projeto Baleia Franca propôs, em 1999, ao Ministério do Meio Ambiente, a criação da unidade de conservação. O objetivo foi harmonizar as atividades humanas com a presença das baleias, promovendo o turismo de observação de forma sustentável e controlada.
                    </p>
                    <p>
                        Em 2006, o IBAMA estabeleceu novas regras para o turismo embarcado de observação de baleias na APA da Baleia Franca. As medidas foram implementadas devido ao crescimento desordenado da atividade e tem como objetivo minimizar os impactos causados às baleias, especialmente as fêmeas com filhotes.
                    </p>
                    <p>
                        As normas determinam restrições para embarcações motorizadas em determinadas áreas da unidade, criando zonas de refúgio onde os animais possam permanecer sem perturbação. Em algumas praias, a observação das baleias é permitida apenas a partir da faixa de areia.
                    </p>
                </article>

                <article class="panel">
                    <h3>Gestão e proteção</h3>
                    <p>
                        A baleia franca-austral foi intensamente caçada durante séculos, principalmente devido à grande quantidade de óleo e gordura corporal. A redução populacional causada pela caça tornou necessária a adoção de medidas internacionais de proteção e conservação.
                    </p>
                    <p>
                        Diversas instituições e organismos internacionais destacam a criação de áreas protegidas como uma das principais estratégias para a preservação dos cetáceos. Estudos científicos apontam que a degradação dos habitats costeiros, o aumento do tráfego de embarcações, a poluição ambiental e o emalhamento em redes de pesca representam ameaças significativas para a espécie.
                    </p>
                    <p>
                        O Plano de Manejo da APA da Baleia Franca começou a ser elaborado em 2016 e foi concluído em 2018 por meio de um processo participativo envolvendo o Conselho Gestor e os diferentes setores usuários da região. O documento estabelece diretrizes para o uso sustentável dos recursos naturais e para a gestão da unidade de conservação.
                    </p>
                    <p>
                        A APA da Baleia Franca possui Conselho Gestor e Plano de Manejo, instrumentos fundamentais para o planejamento e gestão da unidade. A conservação da área depende da integração entre órgãos ambientais, pesquisadores, comunidades locais e setores econômicos da região.
                    </p>
                </article>
            </section>

            <section class="feature-grid feature-grid--contact">
                <article class="panel">
                    <h3>Administração e papel institucional</h3>
                    <p>
                        A APA da Baleia Franca é administrada pelo Instituto Chico Mendes de Conservação da Biodiversidade (ICMBio) e desempenha importante papel na conservação da biodiversidade marinha brasileira, na proteção dos ecossistemas costeiros e no desenvolvimento de ações de educação ambiental.
                    </p>
                </article>

                <article class="panel contact-card">
                    <h3>Endereço e contato</h3>
                    <p><strong>Área de Proteção Ambiental da Baleia Franca</strong></p>
                    <p>Instituto Chico Mendes de Conservação da Biodiversidade - ICMBio</p>
                    <p>Av. Santa Catarina, n° 1465 - Bairro Paes Leme</p>
                    <p>Imbituba/SC - CEP: 88.780-000</p>
                    <p>Telefone: (48) 3255-6710</p>
                    <p>E-mail: <a href="mailto:apadabaleiafranca@icmbio.gov.br">apadabaleiafranca@icmbio.gov.br</a></p>
                    <p>Website: <a href="http://www.icmbio.gov.br/apabaleiafranca/" target="_blank" rel="noreferrer">www.icmbio.gov.br/apabaleiafranca</a></p>
                </article>
            </section>
        <?php endif; ?>

        <?php if ($isSerraDoTabuleiro) : ?>
            <section class="section-heading section-heading--spaced">
                <div>
                    <span class="section-heading__eyebrow">Apresentação institucional</span>
                    <h2>Parque Estadual da Serra do Tabuleiro</h2>
                </div>
                <p>
                    Uma apresentação organizada da maior unidade de conservação de proteção integral de Santa Catarina.
                </p>
            </section>

            <section class="profile-layout">
                <article class="panel profile-intro">
                    <span class="profile-tag">Proteção integral</span>
                    <h2>O PAEST protege biodiversidade, recursos hídricos e paisagens-chave da Mata Atlântica catarinense.</h2>
                    <p>
                        O Parque Estadual da Serra do Tabuleiro (PAEST) é a maior Unidade de Conservação de Proteção Integral de Santa Catarina. Criado em 1975, com base nos estudos dos botânicos Pe. Raulino Reitz e Roberto Miguel Klein, o parque foi instituído com o objetivo de proteger a rica biodiversidade da região e preservar importantes mananciais hídricos que abastecem a Grande Florianópolis e o litoral sul catarinense.
                    </p>
                    <p>
                        Com aproximadamente 84.130 hectares, o Parque ocupa cerca de 1% do território catarinense e abrange áreas dos municípios de Florianópolis, Palhoça, Santo Amaro da Imperatriz, Águas Mornas, São Bonifácio, São Martinho, Imaruí, Paulo Lopes e Garopaba. Também fazem parte da unidade diversas ilhas costeiras, como as ilhas do Siriu, dos Cardos, do Largo, do Andrade e do Coral, além dos arquipélagos das Três Irmãs e Moleques do Sul.
                    </p>
                    <p>
                        O nome da unidade é derivado da Serra do Tabuleiro, formação montanhosa de cume tabular bastante visível a partir da região de Florianópolis.
                    </p>
                </article>

                <aside class="panel profile-facts">
                    <h3>Panorama rápido</h3>
                    <div class="facts-grid">
                        <div class="fact-card">
                            <span>Criação</span>
                            <strong>1975</strong>
                        </div>
                        <div class="fact-card">
                            <span>Área protegida</span>
                            <strong>84.130 hectares</strong>
                        </div>
                        <div class="fact-card">
                            <span>Abrangência</span>
                            <strong>1% do território catarinense</strong>
                        </div>
                        <div class="fact-card">
                            <span>Destaque</span>
                            <strong>Maior UC de proteção integral do estado</strong>
                        </div>
                    </div>
                </aside>
            </section>

            <section class="feature-grid">
                <article class="panel">
                    <h3>Importância ecológica</h3>
                    <p>
                        O Parque Estadual da Serra do Tabuleiro possui enorme relevância ecológica por abrigar uma das áreas mais representativas da Mata Atlântica em Santa Catarina. A unidade protege uma ampla diversidade de ecossistemas e concentra cinco das seis grandes formações vegetais do bioma presentes no Estado.
                    </p>
                    <p>
                        Nas regiões litorâneas, sob forte influência marítima, predominam ecossistemas de restinga e manguezal. A Floresta Ombrófila Densa cobre grande parte das serras do parque, apresentando elevada riqueza de plantas epífitas. Nas encostas superiores ocorre a chamada matinha nebular, constantemente envolta pela neblina proveniente da umidade oceânica. Já nas áreas mais elevadas encontram-se a Floresta Ombrófila Mista, conhecida como Floresta com Araucárias, e os campos de altitude.
                    </p>
                    <p>
                        Cada um desses ambientes abriga espécies próprias da fauna e flora catarinense, tornando o parque um importante refúgio para a biodiversidade da Mata Atlântica.
                    </p>
                    <p>
                        O Parque Estadual da Serra do Tabuleiro protege uma das áreas mais importantes da Mata Atlântica catarinense, preservando ecossistemas costeiros, florestais e de altitude. A unidade desempenha papel fundamental na conservação da biodiversidade, na proteção dos recursos hídricos e na manutenção do equilíbrio climático regional.
                    </p>
                </article>

                <article class="panel">
                    <h3>Recursos hídricos e paisagem</h3>
                    <p>
                        Além da importância ecológica, o Parque Estadual da Serra do Tabuleiro desempenha papel fundamental na conservação dos recursos hídricos do Estado. Protegidas pela vegetação nativa estão as nascentes dos rios Vargem do Braço, Cubatão e D'Una, responsáveis pelo abastecimento de água de grande parte da população da Grande Florianópolis e do litoral sul catarinense.
                    </p>
                    <p>
                        Devido às características de relevo, solo e cobertura vegetal, o parque também atua como importante regulador climático regional, contribuindo para o equilíbrio ambiental e para a manutenção da qualidade da água.
                    </p>
                    <p>
                        Dentro da área do parque, no município de Palhoça, encontra-se a Baixada do Maciambu, considerada uma das mais expressivas paisagens de restinga do litoral brasileiro. A região é formada por cordões arenosos em formato semicircular, originados pelas oscilações do nível do mar ao longo de milhares de anos, sendo reconhecida como importante monumento geológico.
                    </p>
                </article>

                <article class="panel">
                    <h3>Turismo, educação e pesquisa</h3>
                    <p>
                        O Parque Estadual da Serra do Tabuleiro possui grande relevância científica e representa um campo privilegiado para pesquisas ambientais, estudos sobre biodiversidade e conservação da Mata Atlântica. Sua proximidade com grandes centros urbanos também favorece o desenvolvimento de atividades de educação ambiental, turismo ecológico e recreação em contato com a natureza.
                    </p>
                    <p>
                        A sede principal do parque está localizada em Palhoça, na região da Baixada do Maciambu. O espaço conta com centro de visitantes e trilhas educativas que permitem aos visitantes conhecer espécies nativas e os diferentes ecossistemas protegidos pela unidade. O parque também possui centros temáticos nos municípios de Imaruí e São Bonifácio.
                    </p>
                    <p>
                        As paisagens preservadas, a diversidade de espécies e os ambientes praticamente intocados fazem da Serra do Tabuleiro um dos maiores patrimônios naturais de Santa Catarina.
                    </p>
                    <p>
                        O parque possui grande potencial para o turismo ecológico e para atividades de educação ambiental. As trilhas, centros de visitantes e paisagens naturais proporcionam experiências de contato com a natureza, incentivando a conscientização sobre a importância da conservação ambiental.
                    </p>
                    <p>
                        A diversidade de habitats presentes no parque torna a unidade um importante campo para pesquisas científicas relacionadas à fauna, flora, geologia, recursos hídricos e conservação da Mata Atlântica.
                    </p>
                </article>
            </section>

            <section class="feature-grid feature-grid--contact">
                <article class="panel">
                    <h3>Visitação</h3>
                    <p><strong>Horário de visitação</strong></p>
                    <p>Quarta-feira a domingo, das 9h às 17h.</p>
                    <p>Entrada gratuita.</p>
                </article>

                <article class="panel contact-card">
                    <h3>Agendamento e contato</h3>
                    <p>E-mail para agendamento: <a href="mailto:agendamentoserradotabuleiro@gmail.com">agendamentoserradotabuleiro@gmail.com</a></p>
                    <p>Contato geral: <a href="mailto:tabuleiro@ima.sc.gov.br">tabuleiro@ima.sc.gov.br</a></p>
                    <p><strong>Como chegar</strong></p>
                    <p>Rodovia BR-101, Km 238, Baixada do Maciambu - Palhoça/SC.</p>
                    <p>O portal de entrada está localizado a aproximadamente 500 metros da marginal da BR-101, conforme sinalização.</p>
                    <p>Website: <a href="https://www.parquedotabuleiro.com.br/" target="_blank" rel="noreferrer">www.parquedotabuleiro.com.br</a></p>
                </article>
            </section>
        <?php endif; ?>

        <?php if ($isRioVermelho) : ?>
            <section class="section-heading section-heading--spaced">
                <div>
                    <span class="section-heading__eyebrow">Apresentação institucional</span>
                    <h2>Parque Estadual do Rio Vermelho</h2>
                </div>
                <p>
                    Uma apresentação profissional sobre biodiversidade, história, restauração ambiental e uso público da unidade.
                </p>
            </section>

            <section class="profile-layout">
                <article class="panel profile-intro">
                    <span class="profile-tag">Proteção integral</span>
                    <h2>O PAERVE protege ecossistemas costeiros da Ilha de Santa Catarina e recursos hídricos essenciais para a região.</h2>
                    <p>
                        O Parque Estadual do Rio Vermelho (PAERVE) é uma Unidade de Conservação de Proteção Integral criada pelo Decreto Estadual n° 308/2007. Localizado no município de Florianópolis, no nordeste da Ilha de Santa Catarina, o parque possui aproximadamente 1.532 hectares, situando-se entre a Praia do Moçambique, a leste, e a Lagoa da Conceição, a oeste.
                    </p>
                    <p>
                        A unidade foi criada com o objetivo de conservar importantes remanescentes de Floresta Ombrófila Densa (Mata Atlântica), vegetação de restinga e a fauna associada, além de proteger o complexo hídrico da região. O parque também promove ações de recuperação ambiental, pesquisas científicas, educação ambiental, recreação em contato com a natureza e turismo ecológico.
                    </p>
                </article>

                <aside class="panel profile-facts">
                    <h3>Panorama rápido</h3>
                    <div class="facts-grid">
                        <div class="fact-card">
                            <span>Criação</span>
                            <strong>Decreto Estadual n° 308/2007</strong>
                        </div>
                        <div class="fact-card">
                            <span>Área protegida</span>
                            <strong>1.532 hectares</strong>
                        </div>
                        <div class="fact-card">
                            <span>Localização</span>
                            <strong>Nordeste da Ilha de Santa Catarina</strong>
                        </div>
                        <div class="fact-card">
                            <span>Foco</span>
                            <strong>Restinga, floresta e sistema hídrico</strong>
                        </div>
                    </div>
                </aside>
            </section>

            <section class="feature-grid">
                <article class="panel">
                    <h3>Histórico</h3>
                    <p>
                        Na década de 1950, a área atualmente ocupada pelo Parque Estadual do Rio Vermelho apresentava intensa degradação ambiental, sendo utilizada para agricultura, pastejo e coleta de lenha, além de sofrer frequentes incêndios.
                    </p>
                    <p>
                        Em 1962, a região foi transformada na Estação Florestal do Rio Vermelho, vinculada à Secretaria da Agricultura do Estado. O objetivo principal era realizar experimentos de reflorestamento com espécies exóticas, especialmente pinheiros-americanos (Pinus spp.) e eucaliptos (Eucalyptus spp.).
                    </p>
                    <p>
                        A partir de 1963 foram implantados extensos reflorestamentos experimentais com espécies provenientes de diversos países, incluindo Bahamas, Filipinas, Espanha, Portugal, Austrália, Japão e Estados Unidos. Nas dunas próximas à Praia do Moçambique também foram introduzidas espécies vegetais utilizadas para estabilização do solo arenoso.
                    </p>
                    <p>
                        Em 1974, a área passou a se chamar Parque Florestal do Rio Vermelho, ampliando seus objetivos para incluir conservação ambiental, turismo, lazer e escotismo.
                    </p>
                    <p>
                        Com a criação do Sistema Nacional de Unidades de Conservação (SNUC), em 2000, iniciou-se o processo de recategorização da área. Após estudos técnicos, discussões públicas e reconhecimento da importância ecológica da região, foi criado oficialmente o Parque Estadual do Rio Vermelho em 24 de maio de 2007.
                    </p>
                </article>

                <article class="panel">
                    <h3>Ecossistemas e flora</h3>
                    <p>
                        O Parque Estadual do Rio Vermelho abriga uma grande diversidade de ecossistemas costeiros da Mata Atlântica. Aproximadamente 67% de sua área é composta por ambientes naturais, incluindo restingas, dunas, banhados, corpos d'água e Floresta Ombrófila Densa.
                    </p>
                    <p>
                        As restingas ocupam grande parte do parque e apresentam formações herbáceas, arbustivas e arbóreas adaptadas às condições de solo arenoso, salinidade e forte exposição solar. Entre as espécies típicas encontram-se a batateira-da-praia (Ipomoea pes-caprae), feijão-de-porco (Canavalia rosea), pitangueira (Eugenia uniflora), guabiroba-da-praia (Campomanesia littoralis) e diversas bromélias.
                    </p>
                    <p>
                        Nas áreas úmidas e banhados desenvolvem-se espécies adaptadas a solos alagados, enquanto o Morro dos Macacos abriga remanescentes de Floresta Ombrófila Densa, com espécies arbóreas como palmito-juçara (Euterpe edulis), cedro (Cedrela fissilis), canjarana (Cabralea canjerana) e jerivá (Syagrus romanzoffiana).
                    </p>
                    <p>
                        O parque possui elevada importância botânica por abrigar espécies ameaçadas de extinção e espécies endêmicas, como a Mimosa catharinensis, planta encontrada exclusivamente na região.
                    </p>
                    <p>
                        Um dos grandes desafios de conservação da unidade é o controle de espécies exóticas invasoras, especialmente pinheiros-americanos, eucaliptos e casuarinas introduzidos durante os antigos projetos de reflorestamento.
                    </p>
                </article>

                <article class="panel">
                    <h3>Fauna e particularidades</h3>
                    <p>
                        A diversidade de ambientes do parque proporciona abrigo para uma rica fauna da Mata Atlântica. Levantamentos recentes registraram cerca de 140 espécies de aves, incluindo espécies típicas de restingas, áreas úmidas e florestas densas.
                    </p>
                    <p>
                        Entre as aves observadas estão o tucano-de-bico-preto (Ramphastos vitellinus), gralha-azul (Cyanocorax caeruleus), coruja-buraqueira (Athene cunicularia), aracuã-escamoso (Ortalis squamata) e o raro gavião-pombo-pequeno (Amadonastur lacernulatus), espécie ameaçada de extinção e endêmica da Mata Atlântica.
                    </p>
                    <p>
                        O parque também abriga diversas espécies de répteis, anfíbios e mamíferos, como jararaca (Bothrops jararaca), teiu (Salvator merianae), tatu-galinha (Dasypus novemcinctus), mão-pelada (Procyon cancrivorus) e macaco-prego (Sapajus nigritus), que dá nome ao Morro dos Macacos.
                    </p>
                    <p>
                        Entre as espécies ameaçadas destaca-se o lagartinho-da-praia (Liolaemus occipitalis), réptil raro que vive exclusivamente em dunas costeiras do sul do Brasil.
                    </p>
                    <p>
                        Os rios, córregos e áreas alagadas do parque também abrigam diversas espécies de peixes adaptadas aos ambientes de água doce da região.
                    </p>
                    <p>
                        Além da biodiversidade, o Parque Estadual do Rio Vermelho possui grande importância hídrica. Em seu subsolo encontra-se o Aquífero Ingleses-Rio Vermelho, responsável pelo abastecimento de água de grande parte do norte da Ilha de Santa Catarina.
                    </p>
                    <p>
                        A unidade também possui relevância arqueológica e histórica. Dentro de sua área localiza-se um dos sambaquis mais antigos da Ilha de Santa Catarina, datado de aproximadamente 5 mil anos.
                    </p>
                </article>
            </section>

            <section class="feature-grid">
                <article class="panel">
                    <h3>Infraestrutura e visitação</h3>
                    <p>
                        O parque possui diversas estruturas destinadas ao uso público, educação ambiental e conservação da natureza. Entre elas estão o Camping do Rio Vermelho, o Centro de Triagem de Animais Silvestres (CETAS), trilhas ecológicas, áreas de visitação e estruturas de apoio institucional.
                    </p>
                    <p>
                        A visitação pública é livre nas trilhas e acessos à Praia do Moçambique e à Lagoa da Conceição. Os visitantes também podem participar de atividades de educação ambiental e eventos realizados em contato com a natureza.
                    </p>
                </article>

                <article class="panel">
                    <h3>Viveiro de mudas</h3>
                    <p>
                        O Parque mantém um viveiro de produção de mudas nativas utilizado para ações de recuperação ambiental, educação ambiental e doação de espécies para projetos socioambientais.
                    </p>
                    <p>
                        Atualmente o viveiro produz espécies típicas dos ecossistemas do parque e recebe visitas mediante agendamento.
                    </p>
                    <p><strong>Horário de funcionamento:</strong> segunda a sexta-feira, das 12h às 17h.</p>
                </article>

                <article class="panel">
                    <h3>Trilhas e percurso de longo curso</h3>
                    <p>
                        A Trilha Ecológica do PAERVE é uma atividade guiada que permite aos visitantes conhecerem parte da fauna silvestre tratada pelo Centro de Triagem de Animais Silvestres (CETAS).
                    </p>
                    <p>
                        A trilha possui percurso em deck de madeira e duração média de 50 minutos.
                    </p>
                    <p><strong>Funcionamento:</strong> terça-feira a domingo, das 9h às 17h.</p>
                    <p>
                        O Parque Estadual do Rio Vermelho integra a Trilha de Longo Curso do Caminho da Ilha de Santa Catarina (CAISCA), percurso com aproximadamente 18 quilômetros que conecta diferentes ecossistemas da unidade, incluindo restingas, dunas, lagoas e áreas florestais.
                    </p>
                    <p>
                        A trilha segue a metodologia oficial da Rede Trilhas e possui sinalização padronizada conforme modelos internacionais utilizados em trilhas de longo percurso.
                    </p>
                </article>
            </section>

            <section class="feature-grid feature-grid--contact">
                <article class="panel">
                    <h3>Importância ecológica e gestão</h3>
                    <p>
                        O Parque Estadual do Rio Vermelho desempenha papel fundamental na conservação dos ecossistemas costeiros da Ilha de Santa Catarina, protegendo restingas, dunas, florestas, áreas úmidas e recursos hídricos essenciais para a região.
                    </p>
                    <p>
                        A unidade também contribui para pesquisas científicas, conservação da biodiversidade, educação ambiental e recuperação de ecossistemas degradados.
                    </p>
                    <p>
                        O Conselho Consultivo do Parque Estadual do Rio Vermelho atua de forma participativa na gestão da unidade, contribuindo para a implementação de ações de conservação e cumprimento dos objetivos do parque.
                    </p>
                </article>

                <article class="panel contact-card">
                    <h3>Contatos e localização</h3>
                    <p>E-mail: <a href="mailto:riovermelho@ima.sc.gov.br">riovermelho@ima.sc.gov.br</a></p>
                    <p>Agendamentos: <a href="mailto:agendamento.trilhariovermelho@gmail.com">agendamento.trilhariovermelho@gmail.com</a></p>
                    <p>Telefone: (48) 3665-4194</p>
                    <p>Localização: Parque Estadual do Rio Vermelho - Florianópolis/SC.</p>
                    <p>Website: <a href="https://www.ima.sc.gov.br/index.php/biodiversidade/unidades-de-conservacao/parque-estadual-do-rio-vermelho-paerve" target="_blank" rel="noreferrer">Portal oficial do PAERVE</a></p>
                </article>
            </section>
        <?php endif; ?>

        <?php if ($isArvoredo) : ?>
            <section class="section-heading section-heading--spaced">
                <div>
                    <span class="section-heading__eyebrow">Apresentação institucional</span>
                    <h2>Reserva Biológica Marinha do Arvoredo</h2>
                </div>
                <p>
                    Um panorama profissional sobre uma das mais importantes áreas marinhas protegidas de Santa Catarina e sua relevância para a biodiversidade costeira e oceânica.
                </p>
            </section>

            <section class="profile-layout">
                <article class="panel profile-intro">
                    <span class="profile-tag">Proteção integral federal</span>
                    <h2>A REBIO Arvoredo protege um dos mais importantes conjuntos de ecossistemas marinhos do sul do Brasil.</h2>
                    <p>
                        A Reserva Biológica Marinha do Arvoredo (REBIO Arvoredo) é uma Unidade de Conservação Federal de Proteção Integral criada em 12 de março de 1990, por meio do Decreto Federal nº 99.142. Localizada no litoral de Santa Catarina, entre os municípios de Florianópolis e Bombinhas, a reserva possui aproximadamente 17.600 hectares e é considerada uma das áreas marinhas mais importantes do Brasil para a conservação da biodiversidade.
                    </p>
                    <p>
                        A unidade abrange as ilhas do arquipélago do Arvoredo, incluindo a Ilha do Arvoredo, Ilha da Galé, Ilha Deserta e o Calhau de São Pedro, além de extensa área marinha ao redor dessas formações insulares.
                    </p>
                    <p>
                        Administrada pelo Instituto Chico Mendes de Conservação da Biodiversidade (ICMBio), a reserva foi criada com o objetivo de proteger ecossistemas marinhos e costeiros de grande relevância ecológica, garantindo a preservação da fauna, flora e dos processos naturais associados aos ambientes marinhos da região.
                    </p>
                </article>

                <aside class="panel profile-facts">
                    <h3>Panorama rápido</h3>
                    <div class="facts-grid">
                        <div class="fact-card">
                            <span>Criação</span>
                            <strong>12/03/1990</strong>
                        </div>
                        <div class="fact-card">
                            <span>Área protegida</span>
                            <strong>17.600 hectares</strong>
                        </div>
                        <div class="fact-card">
                            <span>Gestão</span>
                            <strong>ICMBio</strong>
                        </div>
                        <div class="fact-card">
                            <span>Destaque</span>
                            <strong>Biodiversidade marinha de alta relevância</strong>
                        </div>
                    </div>
                </aside>
            </section>

            <section class="feature-grid">
                <article class="panel">
                    <h3>Biodiversidade marinha</h3>
                    <p>
                        A Reserva Biológica Marinha do Arvoredo apresenta elevada diversidade biológica devido à influência simultânea de correntes marítimas quentes e frias, oceânicas e costeiras. Essa característica permite a ocorrência de espécies típicas de diferentes regiões climáticas em um mesmo ambiente.
                    </p>
                    <p>
                        Nos ecossistemas marinhos da reserva são encontrados peixes, corais, esponjas, equinodermos, crustáceos e diversos outros organismos marinhos que compõem um dos ambientes subaquáticos mais ricos do litoral sul brasileiro.
                    </p>
                    <p>
                        A região também serve como área de abrigo, alimentação, reprodução e crescimento para inúmeras espécies marinhas, contribuindo diretamente para a manutenção dos estoques pesqueiros do litoral catarinense.
                    </p>
                    <p>
                        Além disso, animais como baleias-francas, lobos-marinhos e pinguins podem ser observados na região durante determinados períodos do ano.
                    </p>
                </article>

                <article class="panel">
                    <h3>Ecossistemas terrestres</h3>
                    <p>
                        As ilhas da reserva possuem importantes remanescentes de Mata Atlântica, que abrigam espécies nativas da fauna e flora insular.
                    </p>
                    <p>
                        As áreas terrestres também são utilizadas como locais de reprodução para aves marinhas, desempenhando papel fundamental para a conservação dessas espécies no litoral catarinense.
                    </p>
                    <p>
                        A diversidade de habitats existentes na reserva favorece a ocorrência de espécies raras e ameaçadas de extinção, tanto em ambientes terrestres quanto marinhos.
                    </p>
                </article>

                <article class="panel">
                    <h3>Patrimônio histórico e arqueológico</h3>
                    <p>
                        A Reserva Biológica Marinha do Arvoredo também possui relevância arqueológica e histórica. Em suas ilhas existem sítios arqueológicos com sambaquis e inscrições rupestres, evidenciando a presença humana antiga na região costeira de Santa Catarina.
                    </p>
                </article>

                <article class="panel">
                    <h3>Proteção e conservação</h3>
                    <p>
                        Por se tratar de uma Unidade de Conservação de Proteção Integral, o acesso à reserva é controlado e permitido apenas para pesquisas científicas e atividades de educação ambiental mediante autorização prévia do ICMBio.
                    </p>
                    <p>
                        As medidas de proteção têm como objetivo minimizar impactos ambientais e garantir a conservação dos ecossistemas naturais da área.
                    </p>
                    <p>
                        O mergulho recreativo é permitido exclusivamente na ponta sul da Ilha do Arvoredo, na região do farol da Marinha do Brasil, área que não integra os limites oficiais da reserva biológica.
                    </p>
                </article>
            </section>

            <section class="feature-grid feature-grid--contact">
                <article class="panel">
                    <h3>Importância ecológica</h3>
                    <p>
                        A Reserva Biológica Marinha do Arvoredo é considerada uma das principais áreas de conservação marinha do sul do Brasil, desempenhando papel essencial na proteção da biodiversidade costeira e oceânica de Santa Catarina.
                    </p>
                    <p>
                        A unidade contribui para a conservação de espécies ameaçadas, manutenção dos ecossistemas marinhos e preservação dos recursos pesqueiros da região.
                    </p>
                </article>

                <article class="panel">
                    <h3>Pesquisa científica</h3>
                    <p>
                        A reserva representa importante área para pesquisas científicas relacionadas à biodiversidade marinha, oceanografia, conservação ambiental e ecologia costeira.
                    </p>
                    <p>
                        Os estudos desenvolvidos na unidade auxiliam no monitoramento ambiental e na elaboração de estratégias de conservação para os ecossistemas marinhos brasileiros.
                    </p>
                </article>

                <article class="panel contact-card">
                    <h3>Contatos</h3>
                    <p><strong>Instituto Chico Mendes de Conservação da Biodiversidade - ICMBio</strong></p>
                    <p>Telefone: (48) 3282-2163</p>
                    <p>E-mail: <a href="mailto:rebio.arvoredo@icmbio.gov.br">rebio.arvoredo@icmbio.gov.br</a></p>
                    <p>Website: <a href="http://www.icmbio.gov.br/rebioarvoredo/" target="_blank" rel="noreferrer">www.icmbio.gov.br/rebioarvoredo</a></p>
                </article>
            </section>
        <?php endif; ?>

        <section class="section-heading section-heading--spaced">
            <div>
                <span class="section-heading__eyebrow">Histórico</span>
                <h2>Comunicações cadastradas</h2>
            </div>
        </section>

        <?php if ($comunicacoes === []) : ?>
            <section class="empty-state empty-state--soft">
                <h2>Ainda não há comunicações</h2>
                <p>Seja a primeira pessoa a registrar uma mensagem para esta unidade.</p>
            </section>
        <?php else : ?>
            <section class="timeline">
                <?php foreach ($comunicacoes as $comunicacao) : ?>
                    <article class="panel timeline__item">
                        <div class="timeline__meta">
                            <span><?= e(formatDate($comunicacao['data_hora'] ?? null, 'd/m/Y H:i')); ?></span>
                            <span><?= e($comunicacao['email']); ?></span>
                        </div>
                        <h3><?= e($comunicacao['titulo']); ?></h3>
                        <p><?= nl2br(e($comunicacao['descricao'])); ?></p>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>