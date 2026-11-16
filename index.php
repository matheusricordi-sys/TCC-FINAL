<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SafeFlow | Gestão de EPI</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="landing-nav">
        <a class="brand" href="index.php">🛡 SafeFlow</a>
        <nav>
           
            <a href="#epi">O que é EPI</a>
            <a href="#tipos">Tipos</a>
            <a href="#normas">Normas</a>
            <a href="#recursos">Recursos</a>
            <a href="#sobre">Sobre nós</a>
        </nav>
        <a class="btn btn-primary" href="acessar.php">Acessar Sistema</a>
    </header>

    <section class="hero">
        <div class="hero-content">
            <span class="eyebrow light">GESTÃO INTELIGENTE DE SEGURANÇA</span>
            <h1>
                EPI sob controle.<br>
                <span>Segurança em fluxo.</span>
            </h1>
            <p>Centralize equipamentos, funcionários, certificados, documentos e compras em uma única plataforma.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="acessar.php">Começar agora</a>
                <a class="btn btn-outline" href="#sobre">Conhecer</a>
            </div>
            <div class="badges">
                <span>✓ CA Obrigatório</span>
                <span>✓ NR-6 Compliant</span>
                <span>✓ ISO 45001</span>
            </div>
        </div>
        <img class="hero-image" src="https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?auto=format&fit=crop&w=1200&q=85">
    </section>

    <section class="stats">
        <div>
            <b>2,1 mi</b>
            <small>acidentes registrados</small>
        </div>
        <div>
            <b>612 mil</b>
            <small>CATs emitidas</small>
        </div>
        <div>
            <b>2,7 mil</b>
            <small>óbitos relacionados</small>
        </div>
        <div>
            <b>99,9%</b>
            <small>monitoramento</small>
        </div>
    </section>

    <section id="epi" class="section two-col">
        <div>
            <span class="eyebrow">NR-6</span>
            <h2>O que é EPI?</h2>
            <p>Equipamento de Proteção Individual é todo dispositivo ou produto de uso individual destinado à proteção contra riscos capazes de ameaçar a segurança e a saúde do trabalhador.</p>
            <ul class="check-list">
                <li>Todo EPI deve possuir Certificado de Aprovação válido.</li>
                <li>A entrega deve ser registrada e vinculada ao trabalhador.</li>
                <li>O empregador deve orientar, treinar, fornecer e substituir equipamentos.</li>
            </ul>
        </div>
        <div class="photo-grid">
            <img src="https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80">
            <img src="https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=800&q=80">
            <img src="https://images.unsplash.com/photo-1572981779307-38b8cabb2407?auto=format&fit=crop&w=1000&q=80">
        </div>
    </section>

    <section id="tipos" class="dark-section">
        <div class="section">
            <span class="eyebrow blue">CLASSIFICAÇÃO</span>
            <h2>Tipos de EPI</h2>
            <div class="type-grid">
                <?php
                foreach ([
                    'Cabeça' => 'Capacetes e acessórios',
                    'Olhos / Face' => 'Óculos e protetores faciais',
                    'Audição' => 'Abafadores e plugs',
                    'Respiratório' => 'PFF2 e respiradores',
                    'Mãos / Braços' => 'Luvas e mangas',
                    'Tronco' => 'Aventais e vestimentas',
                    'Pés / Pernas' => 'Botas e perneiras',
                    'Quedas' => 'Cinturões e talabartes'
                ] as $a => $b):
                ?>
                    <div class="type-card">
                        <b><?= $a ?></b>
                        <span><?= $b ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="normas" class="section">
        <span class="eyebrow">CONFORMIDADE</span>
        <h2>Normas Regulamentadoras</h2>
        <div class="norm-grid">
            <?php
            $normas = [
                'NR-6' => 'Equipamento de Proteção Individual',
                'NR-7' => 'PCMSO',
                'NR-9' => 'Avaliação de riscos',
                'NR-10' => 'Segurança em eletricidade',
                'NR-12' => 'Máquinas e equipamentos',
                'NR-35' => 'Trabalho em altura'
            ];
            $descricoesNormas = [
                'NR-6' => 'Estabelece o fornecimento gratuito de EPIs adequados, com Certificado de Aprovação (CA), orientação de uso e registro das entregas ao trabalhador.',
                'NR-7' => 'Define o Programa de Controle Médico de Saúde Ocupacional, incluindo exames admissionais, periódicos, de retorno, mudança de função e demissionais.',
                'NR-9' => 'Orienta a identificação, a avaliação e o controle dos riscos físicos, químicos e biológicos presentes no ambiente de trabalho.',
                'NR-10' => 'Estabelece medidas de controle e sistemas preventivos para proteger trabalhadores que atuam em instalações e serviços com eletricidade.',
                'NR-12' => 'Define requisitos para prevenir acidentes com máquinas, como proteções, dispositivos de parada, capacitação e manutenção segura.',
                'NR-35' => 'Regulamenta atividades acima de 2 metros com risco de queda, exigindo planejamento, treinamento, análise de risco e sistemas de proteção.'
            ];
            foreach ($normas as $a => $b):
            ?>
                <div class="norm" role="button" tabindex="0" aria-expanded="false">
                    <strong><?= $a ?></strong>
                    <b><?= $b ?></b>
                    <p><?= $descricoesNormas[$a] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="recursos" class="section">
        <span class="eyebrow">PLATAFORMA</span>
        <h2>Tudo em um só fluxo</h2>
        <div class="feature-grid">
            <?php
            foreach ([
                'Gestão de CA',
                'Rastreabilidade',
                'Controle de estoque',
                'Relatórios NR-6',
                'Alertas inteligentes',
                'Conformidade'
            ] as $x):
            ?>
                <div>
                    <i>✓</i>
                    <b><?= $x ?></b>
                    <p>Controle centralizado para uma operação mais segura.</p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="sobre" class="section about-safeflow">
        <div class="about-intro">
            <span class="eyebrow">SOBRE A SAFEFLOW</span>
            <h2>Segurança do trabalho mais simples, organizada e confiável.</h2>
            <p>A SafeFlow é uma plataforma de gestão de EPIs criada para apoiar empresas na proteção de suas equipes e no cumprimento das exigências de segurança do trabalho.</p>
            <p>Reunimos em um só lugar o controle de equipamentos, certificados, entregas, estoque, documentos e alertas. Assim, gestores tomam decisões com mais agilidade e trabalhadores têm a proteção adequada para cada atividade.</p>
        </div>
        <div class="about-values">
            <div>
                <strong>Nosso propósito</strong>
                <p>Transformar a conformidade em uma rotina prática para empresas e colaboradores.</p>
            </div>
            <div>
                <strong>Como atuamos</strong>
                <p>Digitalizamos processos que antes dependiam de planilhas, papéis e controles descentralizados.</p>
            </div>
            <div>
                <strong>No mercado</strong>
                <p>Ajudamos organizações a reduzir falhas operacionais, manter a rastreabilidade e fortalecer a cultura de prevenção.</p>
            </div>
        </div>
    </section>

    <section class="cta">
        <h2>Transforme conformidade em rotina.</h2>
        <p>Menos planilhas. Mais rastreabilidade. Mais segurança.</p>
        <a class="btn btn-white" href="acessar.php">Acessar o SafeFlow</a>
    </section>

    <footer>© 2026 SafeFlow · Gestão de EPI</footer>
    <script src="js/app.js"></script>
</body>
</html>
