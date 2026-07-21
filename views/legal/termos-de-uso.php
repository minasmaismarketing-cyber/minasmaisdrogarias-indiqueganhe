<?php
/**
 * Termos de Uso — Sistema Indique e Ganhe
 *
 * Rota pública sugerida:
 * /termos-de-uso
 *
 * Este arquivo deve ser carregado pelo layout público já existente no projeto.
 */

$pageTitle = 'Termos de Uso';
?>

<style>
    .legal-page {
        width: min(100% - 32px, 880px);
        margin: 32px auto 56px;
        color: #292929;
    }

    .legal-page__header {
        margin-bottom: 24px;
        padding: 28px 24px;
        background: #ffffff;
        border: 1px solid #eeeeee;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .legal-page__eyebrow {
        display: inline-block;
        margin-bottom: 10px;
        color: #d71920;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .legal-page__title {
        margin: 0;
        color: #202020;
        font-size: clamp(1.8rem, 5vw, 2.6rem);
        line-height: 1.12;
    }

    .legal-page__subtitle {
        margin: 12px 0 0;
        color: #666666;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .legal-page__content {
        padding: 28px 24px;
        background: #ffffff;
        border: 1px solid #eeeeee;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .legal-page__section + .legal-page__section {
        margin-top: 30px;
        padding-top: 30px;
        border-top: 1px solid #eeeeee;
    }

    .legal-page h2 {
        margin: 0 0 14px;
        color: #202020;
        font-size: 1.2rem;
        line-height: 1.35;
    }

    .legal-page h3 {
        margin: 22px 0 10px;
        color: #333333;
        font-size: 1rem;
        line-height: 1.45;
    }

    .legal-page p,
    .legal-page li {
        color: #555555;
        font-size: 0.96rem;
        line-height: 1.75;
    }

    .legal-page p {
        margin: 0 0 14px;
    }

    .legal-page ul,
    .legal-page ol {
        margin: 12px 0 16px;
        padding-left: 22px;
    }

    .legal-page li + li {
        margin-top: 8px;
    }

    .legal-page strong {
        color: #333333;
    }

    .legal-page a {
        color: #c8151d;
        font-weight: 700;
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .legal-page__notice {
        margin: 20px 0;
        padding: 18px;
        background: #fff4f4;
        border-left: 4px solid #d71920;
        border-radius: 12px;
    }

    .legal-page__notice p:last-child {
        margin-bottom: 0;
    }

    .legal-page__company {
        padding: 18px;
        background: #f7f7f7;
        border-radius: 14px;
    }

    .legal-page__company p:last-child {
        margin-bottom: 0;
    }

    .legal-page__actions {
        display: flex;
        justify-content: center;
        margin-top: 24px;
    }

    .legal-page__back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 46px;
        padding: 0 24px;
        color: #ffffff !important;
        background: #d71920;
        border-radius: 999px;
        font-weight: 800;
        text-decoration: none !important;
        transition:
            transform 0.2s ease,
            opacity 0.2s ease;
    }

    .legal-page__back:hover {
        opacity: 0.92;
        transform: translateY(-1px);
    }

    .legal-page__footer {
        margin-top: 24px;
        color: #777777;
        font-size: 0.82rem;
        text-align: center;
        line-height: 1.6;
    }

    @media (max-width: 600px) {
        .legal-page {
            width: min(100% - 24px, 880px);
            margin-top: 20px;
        }

        .legal-page__header,
        .legal-page__content {
            padding: 22px 18px;
            border-radius: 16px;
        }

        .legal-page__section + .legal-page__section {
            margin-top: 24px;
            padding-top: 24px;
        }
    }
</style>

<main class="legal-page">
    <header class="legal-page__header">
        <span class="legal-page__eyebrow">
            Sistema Indique e Ganhe
        </span>

        <h1 class="legal-page__title">
            Termos de Uso
        </h1>

        <p class="legal-page__subtitle">
            Versão 1.0<br>
            Última atualização: 21 de julho de 2026
        </p>
    </header>

    <article class="legal-page__content">
        <section class="legal-page__section">
            <h2>1. Identificação da responsável pelo sistema</h2>

            <p>
                Estes Termos de Uso regulam o acesso e a utilização do
                <strong>Sistema Indique e Ganhe das Drogarias Minas Mais</strong>,
                disponibilizado e administrado por:
            </p>

            <div class="legal-page__company">
                <p>
                    <strong>Razão social:</strong>
                    M.H.L. Drogaria S.A.
                </p>

                <p>
                    <strong>Nome fantasia:</strong>
                    Drogaria Minas Mais
                </p>

                <p>
                    <strong>CNPJ:</strong>
                    09.396.401/0001-87
                </p>

                <p>
                    <strong>Endereço:</strong>
                    Avenida Afonso Pena, nº 504, Centro,
                    Campo Belo/MG, CEP 37270-000
                </p>

                <p>
                    <strong>E-mail:</strong>
                    <a href="mailto:ecommerce1@drogariasminasmais.com.br">
                        ecommerce1@drogariasminasmais.com.br
                    </a>
                </p>

                <p>
                    <strong>WhatsApp de suporte:</strong>
                    <a
                        href="https://wa.me/553599125296?text=Ol%C3%A1%21%20Vim%20pelo%20Sistema%20Indique%20e%20Ganhe%20das%20Drogarias%20Minas%20Mais%20e%20preciso%20de%20ajuda."
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        +55 35 9912-5296
                    </a>
                </p>
            </div>
        </section>

        <section class="legal-page__section">
            <h2>2. Aceitação destes Termos</h2>

            <p>
                Ao realizar o cadastro ou utilizar o Sistema Indique e Ganhe,
                o usuário declara que leu, compreendeu e aceitou estes Termos
                de Uso, a Política de Privacidade e o Regulamento da Campanha.
            </p>

            <p>
                Caso o usuário não concorde com alguma das condições
                apresentadas, não deverá concluir seu cadastro nem utilizar
                as funcionalidades do sistema.
            </p>

            <div class="legal-page__notice">
                <p>
                    <strong>Importante:</strong>
                    a participação na campanha é permitida somente para
                    pessoas físicas com 18 anos ou mais e CPF válido.
                </p>
            </div>
        </section>

        <section class="legal-page__section">
            <h2>3. Finalidade do Sistema Indique e Ganhe</h2>

            <p>
                O sistema tem como finalidade permitir que usuários realizem
                seu cadastro, obtenham um link pessoal de indicação,
                compartilhem esse link com outras pessoas, acompanhem o
                andamento das indicações e recebam benefícios promocionais
                quando cumpridos os critérios da campanha.
            </p>

            <p>
                O sistema poderá, entre outras funcionalidades:
            </p>

            <ul>
                <li>criar e administrar a conta do usuário;</li>
                <li>gerar um link exclusivo de indicação;</li>
                <li>registrar acessos realizados por meio do link;</li>
                <li>acompanhar a instalação e o cadastro no aplicativo Minas Mais;</li>
                <li>exibir o status das indicações;</li>
                <li>disponibilizar cupons e benefícios aprovados;</li>
                <li>permitir o acesso ao suporte;</li>
                <li>realizar verificações de segurança e prevenção a fraudes.</li>
            </ul>

            <p>
                O simples cadastro no sistema, a geração do link ou o seu
                compartilhamento não garantem, por si só, o recebimento de
                qualquer benefício.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>4. Requisitos para cadastro e participação</h2>

            <p>
                Para criar uma conta e participar, o usuário deverá:
            </p>

            <ul>
                <li>ser pessoa física com idade igual ou superior a 18 anos;</li>
                <li>possuir CPF válido e regular;</li>
                <li>informar dados próprios, verdadeiros, completos e atualizados;</li>
                <li>possuir e-mail e número de telefone ou WhatsApp válidos;</li>
                <li>criar e manter uma senha pessoal e segura;</li>
                <li>
                    aceitar estes Termos de Uso, a Política de Privacidade
                    e o Regulamento da Campanha;
                </li>
                <li>cumprir os critérios da campanha vigente.</li>
            </ul>

            <p>
                Não é necessário que o participante já seja cliente antigo
                ou possua cadastro anterior no aplicativo Minas Mais para
                atuar como indicador, desde que cumpra as condições da
                campanha.
            </p>

            <p>
                Colaboradores, franqueados e familiares de colaboradores
                poderão participar, desde que utilizem dados pessoais próprios
                e cumpram as mesmas regras aplicáveis aos demais usuários.
            </p>

            <p>
                A campanha possui abrangência em todo o território brasileiro,
                observadas as condições de funcionamento, disponibilidade dos
                canais de venda e regras comerciais aplicáveis.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>5. Responsabilidade pelos dados de cadastro</h2>

            <p>
                O usuário é responsável pela exatidão, autenticidade e
                atualização dos dados informados.
            </p>

            <p>
                É proibido:
            </p>

            <ul>
                <li>utilizar nome, CPF, telefone ou e-mail de terceiros;</li>
                <li>criar conta em nome de outra pessoa;</li>
                <li>informar dados falsos, incompletos ou fraudulentos;</li>
                <li>manter múltiplas contas para obter vantagens indevidas;</li>
                <li>ceder, vender ou compartilhar sua conta;</li>
                <li>permitir que terceiros utilizem sua senha.</li>
            </ul>

            <p>
                A Minas Mais poderá solicitar informações adicionais quando
                necessário para confirmar a identidade do usuário, corrigir
                divergências ou apurar suspeitas de irregularidade.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>6. Segurança da conta e da senha</h2>

            <p>
                A senha é pessoal e intransferível. O usuário deverá adotar
                cuidados razoáveis para impedir o acesso não autorizado à sua
                conta.
            </p>

            <p>
                Caso identifique perda de acesso, uso não autorizado ou
                qualquer situação suspeita, o usuário deverá alterar sua senha
                e entrar em contato com o suporte.
            </p>

            <p>
                A Minas Mais não solicitará o envio da senha completa por
                telefone, WhatsApp, e-mail ou redes sociais.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>7. Funcionamento das indicações</h2>

            <p>
                Após concluir o cadastro, o usuário poderá receber um link
                exclusivo para compartilhar com amigos.
            </p>

            <p>
                Para que uma indicação seja identificada e analisada, o amigo
                indicado deverá:
            </p>

            <ol>
                <li>acessar obrigatoriamente o link exclusivo do indicador;</li>
                <li>seguir o fluxo apresentado para acesso ou instalação do aplicativo;</li>
                <li>instalar ou abrir o aplicativo Minas Mais;</li>
                <li>concluir um novo cadastro utilizando dados próprios e verdadeiros;</li>
                <li>validar os dados de contato quando solicitado;</li>
                <li>cumprir os demais critérios estabelecidos no Regulamento da Campanha.</li>
            </ol>

            <p>
                O usuário que já tenha instalado o aplicativo, mas nunca tenha
                concluído um cadastro, poderá ser considerado elegível quando
                a instalação e o novo cadastro forem corretamente atribuídos
                ao link de indicação.
            </p>

            <p>
                Usuários que já tenham possuído cadastro anteriormente,
                inclusive aqueles que excluíram a conta, não serão considerados
                novos usuários para fins da campanha.
            </p>

            <p>
                Não é necessária a realização de uma compra pelo amigo indicado
                para que a indicação seja analisada, salvo se houver alteração
                expressa no Regulamento da campanha vigente.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>8. Validação das indicações</h2>

            <p>
                A indicação somente será considerada aprovada após o
                processamento e a confirmação das etapas exigidas.
            </p>

            <p>
                A validação poderá envolver informações fornecidas por
                integrações tecnológicas, incluindo o AppsFlyer, o aplicativo
                Minas Mais, a infraestrutura da empresa e os sistemas
                responsáveis pelos cupons.
            </p>

            <p>
                A aprovação poderá depender da confirmação de:
            </p>

            <ul>
                <li>acesso pelo link correto;</li>
                <li>atribuição da instalação ao indicador;</li>
                <li>conclusão do cadastro no aplicativo;</li>
                <li>validação de CPF, e-mail ou telefone;</li>
                <li>inexistência de cadastro anterior;</li>
                <li>ausência de autoindicação, duplicidade ou fraude;</li>
                <li>disponibilidade e funcionamento das integrações;</li>
                <li>cumprimento do prazo e das regras da campanha.</li>
            </ul>

            <p>
                O status poderá permanecer pendente enquanto o sistema aguarda
                dados, validações ou confirmações técnicas.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>9. Possíveis motivos de reprovação</h2>

            <p>
                Uma indicação poderá ser reprovada, entre outras hipóteses,
                quando for identificado:
            </p>

            <ul>
                <li>cadastro já existente;</li>
                <li>CPF já cadastrado;</li>
                <li>e-mail já cadastrado;</li>
                <li>instalação não atribuída ao link de indicação;</li>
                <li>cadastro incompleto;</li>
                <li>dados inválidos ou inconsistentes;</li>
                <li>cadastro realizado fora do período da campanha;</li>
                <li>autoindicação;</li>
                <li>duplicidade de dispositivo;</li>
                <li>duplicidade de IP ou padrão de acesso incompatível;</li>
                <li>suspeita fundamentada de fraude;</li>
                <li>limite de benefícios atingido;</li>
                <li>falha ou ausência de confirmação da integração;</li>
                <li>descumprimento destes Termos ou do Regulamento.</li>
            </ul>

            <p>
                Para preservar a segurança do sistema, a Minas Mais poderá não
                revelar detalhes técnicos de seus mecanismos de prevenção a
                fraudes.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>10. Benefícios e cupons</h2>

            <p>
                A concessão, o percentual, a validade e as condições de uso dos
                cupons serão definidos no Regulamento da Campanha e nas
                informações apresentadas no próprio benefício.
            </p>

            <p>
                Na campanha vigente entre
                <strong>21 de julho de 2026, às 00h00, e 31 de julho de 2026, às 23h59</strong>,
                aplicam-se as seguintes condições principais:
            </p>

            <h3>10.1. Benefício do indicador</h3>

            <ul>
                <li>um único cupom de 10% de desconto por usuário elegível;</li>
                <li>uso único;</li>
                <li>sem valor mínimo de compra;</li>
                <li>sem limite máximo de desconto;</li>
                <li>válido no site e no aplicativo Minas Mais;</li>
                <li>cumulativo com promoções de produtos;</li>
                <li>não cumulativo com outro cupom;</li>
                <li>válido até 31 de julho de 2026, às 23h59.</li>
            </ul>

            <h3>10.2. Benefício do amigo indicado</h3>

            <ul>
                <li>cupom de 5% de desconto na primeira compra;</li>
                <li>uso único;</li>
                <li>liberação após a aprovação da indicação;</li>
                <li>validade de 30 dias contados da liberação;</li>
                <li>sem valor mínimo de compra;</li>
                <li>sem limite máximo de desconto;</li>
                <li>cumulativo com promoções de produtos;</li>
                <li>não cumulativo com outro cupom.</li>
            </ul>

            <p>
                Os cupons são pessoais, vinculados ao cadastro elegível e não
                poderão ser convertidos em dinheiro, vendidos, transferidos,
                reproduzidos ou utilizados para obtenção de vantagem indevida.
            </p>

            <p>
                A liberação técnica e a utilização dos cupons poderão depender
                da integração com a plataforma VTEX e de outras ferramentas
                utilizadas pela Minas Mais.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>11. Práticas proibidas</h2>

            <p>
                O usuário não poderá:
            </p>

            <ul>
                <li>realizar autoindicação;</li>
                <li>criar cadastros falsos ou múltiplos;</li>
                <li>usar dados de terceiros sem autorização;</li>
                <li>simular instalações ou cadastros;</li>
                <li>utilizar robôs, scripts, emuladores ou automações;</li>
                <li>manipular links, parâmetros ou eventos técnicos;</li>
                <li>explorar erros ou vulnerabilidades do sistema;</li>
                <li>comprar, vender ou trocar indicações;</li>
                <li>comercializar ou transferir cupons;</li>
                <li>divulgar informações falsas em nome da Minas Mais;</li>
                <li>praticar atos ilícitos, abusivos ou fraudulentos;</li>
                <li>tentar acessar contas, dados ou áreas restritas de terceiros.</li>
            </ul>

            <p>
                A identificação dessas práticas poderá resultar no bloqueio da
                conta, cancelamento de indicações, cancelamento de cupons e
                adoção das medidas cabíveis.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>12. Suspensão, bloqueio ou encerramento da conta</h2>

            <p>
                A Minas Mais poderá suspender ou bloquear uma conta quando
                houver:
            </p>

            <ul>
                <li>suspeita de fraude ou abuso;</li>
                <li>violação destes Termos;</li>
                <li>uso de dados falsos ou de terceiros;</li>
                <li>risco à segurança do sistema;</li>
                <li>determinação legal, administrativa ou judicial;</li>
                <li>necessidade de investigação de irregularidade.</li>
            </ul>

            <p>
                Sempre que possível e compatível com a segurança da operação,
                o usuário poderá entrar em contato com o suporte para obter
                informações ou apresentar esclarecimentos.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>13. Exclusão da conta</h2>

            <p>
                O usuário poderá solicitar a exclusão de sua conta pela
                funcionalidade disponibilizada no sistema ou pelos canais de
                atendimento.
            </p>

            <p>
                Após a confirmação da exclusão:
            </p>

            <ul>
                <li>o login será bloqueado imediatamente;</li>
                <li>as indicações pendentes serão canceladas;</li>
                <li>os cupons ainda não utilizados serão cancelados;</li>
                <li>não haverá prazo para desfazer a exclusão;</li>
                <li>o usuário poderá realizar um novo cadastro imediatamente, se elegível;</li>
                <li>
                    o CPF poderá permanecer em registro restrito de prevenção
                    a fraudes pelo prazo de dois meses;
                </li>
                <li>
                    determinados registros poderão ser mantidos pelo período
                    necessário ao cumprimento de obrigações legais e ao
                    exercício regular de direitos.
                </li>
            </ul>

            <p>
                A realização de um novo cadastro não transforma o usuário em
                novo cliente para fins de campanhas que exijam inexistência de
                cadastro anterior.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>14. Disponibilidade do sistema</h2>

            <p>
                A Minas Mais buscará manter o sistema disponível e funcional,
                mas não garante funcionamento ininterrupto ou livre de erros.
            </p>

            <p>
                Poderão ocorrer interrupções causadas por:
            </p>

            <ul>
                <li>manutenção programada ou emergencial;</li>
                <li>atualizações técnicas;</li>
                <li>falhas de internet ou energia;</li>
                <li>indisponibilidade da hospedagem;</li>
                <li>problemas no aparelho ou navegador do usuário;</li>
                <li>falhas no aplicativo Minas Mais;</li>
                <li>indisponibilidade da AppsFlyer, VTEX, KOBE, WhatsApp ou outros terceiros;</li>
                <li>caso fortuito, força maior ou determinação de autoridade.</li>
            </ul>

            <p>
                A ocorrência de falha técnica não gera aprovação automática de
                indicação ou direito automático a cupom. Cada situação poderá
                ser analisada com base nos registros disponíveis.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>15. Serviços e plataformas de terceiros</h2>

            <p>
                Algumas funcionalidades dependem de serviços externos,
                incluindo, conforme aplicável:
            </p>

            <ul>
                <li>AppsFlyer, para atribuição de links, instalações e eventos;</li>
                <li>KOBE, para integrações e confirmações relacionadas ao aplicativo;</li>
                <li>VTEX, para criação, validação ou utilização de cupons;</li>
                <li>WhatsApp, para compartilhamento e suporte;</li>
                <li>lojas de aplicativos e sistemas operacionais móveis.</li>
            </ul>

            <p>
                Esses serviços possuem termos, políticas e condições próprios.
                A Minas Mais não controla integralmente a disponibilidade ou o
                funcionamento de plataformas mantidas por terceiros.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>16. Responsabilidades da Minas Mais</h2>

            <p>
                A Minas Mais é responsável por administrar o sistema dentro dos
                limites de sua atuação, aplicar as regras da campanha, proteger
                os dados sob sua responsabilidade e disponibilizar canais de
                atendimento.
            </p>

            <p>
                Nenhuma disposição destes Termos exclui direitos que sejam
                obrigatoriamente assegurados ao consumidor pela legislação
                brasileira.
            </p>

            <p>
                A Minas Mais não será responsável por prejuízos decorrentes
                exclusivamente de:
            </p>

            <ul>
                <li>informações incorretas fornecidas pelo usuário;</li>
                <li>compartilhamento voluntário de senha ou acesso à conta;</li>
                <li>uso de aparelho comprometido ou desatualizado;</li>
                <li>falhas externas fora do controle razoável da empresa;</li>
                <li>uso do sistema em desacordo com estes Termos;</li>
                <li>fraude praticada pelo próprio usuário ou por terceiros mediante sua colaboração.</li>
            </ul>
        </section>

        <section class="legal-page__section">
            <h2>17. Propriedade intelectual</h2>

            <p>
                As marcas, nomes, logotipos, telas, textos, elementos visuais,
                códigos, funcionalidades e demais conteúdos do Sistema Indique
                e Ganhe pertencem à Minas Mais ou são utilizados mediante
                autorização.
            </p>

            <p>
                O acesso ao sistema não concede ao usuário licença para copiar,
                modificar, reproduzir, comercializar, distribuir ou explorar
                esses conteúdos fora das finalidades da campanha.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>18. Privacidade e proteção de dados</h2>

            <p>
                O tratamento de dados pessoais realizado durante o cadastro e
                a utilização do sistema está descrito na
                <a href="<?= htmlspecialchars(url('politica-de-privacidade'), ENT_QUOTES, 'UTF-8') ?>">
                    Política de Privacidade
                </a>.
            </p>

            <p>
                A Política apresenta informações sobre os dados coletados,
                finalidades, integrações, compartilhamentos, segurança,
                retenção, exclusão e direitos dos titulares.
            </p>

            <p>
                O canal para dúvidas e solicitações relacionadas à privacidade é:
                <a href="mailto:ecommerce1@drogariasminasmais.com.br">
                    ecommerce1@drogariasminasmais.com.br
                </a>.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>19. Regulamento da Campanha</h2>

            <p>
                As condições comerciais e promocionais específicas estão
                detalhadas no
                <a href="<?= htmlspecialchars(url('regulamento'), ENT_QUOTES, 'UTF-8') ?>">
                    Regulamento da Campanha Indique e Ganhe
                </a>.
            </p>

            <p>
                Em caso de diferença entre uma regra geral destes Termos e uma
                condição promocional específica, prevalecerá o Regulamento
                para os assuntos diretamente relacionados à campanha, sem
                prejuízo da legislação aplicável.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>20. Atendimento e contestação</h2>

            <p>
                O suporte funciona de segunda a sexta-feira, das 8h às 18h,
                pelos seguintes canais:
            </p>

            <ul>
                <li>
                    WhatsApp:
                    <a
                        href="https://wa.me/553599125296?text=Ol%C3%A1%21%20Vim%20pelo%20Sistema%20Indique%20e%20Ganhe%20das%20Drogarias%20Minas%20Mais%20e%20preciso%20de%20ajuda."
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        +55 35 9912-5296
                    </a>
                </li>

                <li>
                    E-mail:
                    <a href="mailto:ecommerce1@drogariasminasmais.com.br">
                        ecommerce1@drogariasminasmais.com.br
                    </a>
                </li>
            </ul>

            <p>
                O prazo médio estimado para resposta é de até dois dias úteis,
                podendo variar conforme a complexidade da solicitação.
            </p>

            <p>
                A contestação de uma reprovação deverá ser apresentada em até
                um dia corrido após a disponibilização do respectivo status,
                acompanhada das informações necessárias para análise.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>21. Alterações destes Termos</h2>

            <p>
                Estes Termos poderão ser atualizados para refletir mudanças
                legais, técnicas, operacionais ou comerciais.
            </p>

            <p>
                A versão atualizada será disponibilizada no sistema com a
                respectiva data de atualização.
            </p>

            <p>
                Quando uma alteração relevante afetar direitos, deveres ou a
                utilização do sistema, a Minas Mais poderá solicitar novo
                aceite antes da continuidade do uso.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>22. Legislação aplicável e foro</h2>

            <p>
                Estes Termos são regidos pela legislação da República
                Federativa do Brasil.
            </p>

            <p>
                Fica indicado o foro da Comarca de Campo Belo/MG para a solução
                de controvérsias, sem prejuízo do foro legalmente assegurado ao
                consumidor ou de outra competência obrigatória prevista em lei.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>23. Disposições finais</h2>

            <p>
                A eventual tolerância da Minas Mais quanto ao descumprimento de
                alguma condição não representará renúncia ao direito de exigir
                seu cumprimento posteriormente.
            </p>

            <p>
                Caso alguma disposição seja considerada inválida ou
                inaplicável, as demais continuarão válidas.
            </p>

            <p>
                Ao concluir o cadastro, o usuário confirma que possui 18 anos
                ou mais e que aceita estes Termos de Uso, a Política de
                Privacidade e o Regulamento da Campanha.
            </p>
        </section>
    </article>

    <div class="legal-page__actions">
        <a
            class="legal-page__back"
            href="<?= htmlspecialchars(url('cadastro'), ENT_QUOTES, 'UTF-8') ?>"
        >
            Voltar para o cadastro
        </a>
    </div>

    <footer class="legal-page__footer">
        <p>
            M.H.L. Drogaria S.A. — CNPJ 09.396.401/0001-87<br>
            Avenida Afonso Pena, nº 504, Centro, Campo Belo/MG —
            CEP 37270-000
        </p>
    </footer>
</main>