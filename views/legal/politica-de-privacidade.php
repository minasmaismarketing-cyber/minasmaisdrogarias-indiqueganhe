<?php
/**
 * Política de Privacidade — Sistema Indique e Ganhe
 *
 * Rota pública sugerida:
 * /politica-de-privacidade
 *
 * Este arquivo deve ser carregado pelo layout público existente.
 */

$pageTitle = 'Política de Privacidade';
?>

<style>
    .legal-page {
        width: min(100% - 32px, 880px);
        margin: 32px auto 56px;
        color: #292929;
    }

    .legal-page__header,
    .legal-page__content {
        background: #ffffff;
        border: 1px solid #eeeeee;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .legal-page__header {
        margin-bottom: 24px;
        padding: 28px 24px;
    }

    .legal-page__content {
        padding: 28px 24px;
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

    .legal-page__notice--neutral {
        background: #f7f7f7;
        border-left-color: #777777;
    }

    .legal-page__notice p:last-child,
    .legal-page__company p:last-child {
        margin-bottom: 0;
    }

    .legal-page__company {
        padding: 18px;
        background: #f7f7f7;
        border-radius: 14px;
    }

    .legal-page__table-wrapper {
        width: 100%;
        margin: 18px 0;
        overflow-x: auto;
        border: 1px solid #eeeeee;
        border-radius: 14px;
    }

    .legal-page__table {
        width: 100%;
        min-width: 620px;
        border-collapse: collapse;
    }

    .legal-page__table th,
    .legal-page__table td {
        padding: 14px;
        border-bottom: 1px solid #eeeeee;
        color: #555555;
        font-size: 0.9rem;
        line-height: 1.55;
        text-align: left;
        vertical-align: top;
    }

    .legal-page__table th {
        color: #333333;
        background: #f7f7f7;
        font-weight: 800;
    }

    .legal-page__table tr:last-child td {
        border-bottom: 0;
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
        line-height: 1.6;
        text-align: center;
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
            Política de Privacidade
        </h1>

        <p class="legal-page__subtitle">
            Versão 1.0<br>
            Última atualização: 21 de julho de 2026
        </p>
    </header>

    <article class="legal-page__content">
        <section class="legal-page__section">
            <h2>1. Apresentação</h2>

            <p>
                Esta Política de Privacidade explica como a
                <strong>M.H.L. Drogaria S.A.</strong>, nome fantasia
                <strong>Drogaria Minas Mais</strong>, coleta, utiliza,
                armazena, compartilha e protege os dados pessoais tratados
                por meio do Sistema Indique e Ganhe.
            </p>

            <p>
                A utilização do sistema pressupõe que o usuário tenha lido
                esta Política e compreendido as práticas aqui descritas.
            </p>

            <p>
                O tratamento de dados pessoais será realizado conforme a
                legislação brasileira aplicável, especialmente a Lei Geral
                de Proteção de Dados Pessoais — LGPD.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>2. Identificação da controladora</h2>

            <p>
                Para as operações descritas nesta Política, a responsável
                pelas decisões sobre o tratamento dos dados é:
            </p>

            <div class="legal-page__company">
                <p>
                    <strong>Controladora:</strong>
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
                    <strong>E-mail geral:</strong>
                    <a href="mailto:minasmais29@gmail.com">
                        minasmais29@gmail.com
                    </a>
                </p>

                <p>
                    <strong>Canal de privacidade:</strong>
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

            <div class="legal-page__notice legal-page__notice--neutral">
                <p>
                    A empresa ainda não possui encarregado pelo tratamento
                    de dados pessoais formalmente identificado nesta
                    Política. Enquanto isso, solicitações relacionadas à
                    privacidade deverão ser encaminhadas ao canal indicado
                    acima.
                </p>
            </div>
        </section>

        <section class="legal-page__section">
            <h2>3. Quem pode utilizar o sistema</h2>

            <p>
                O Sistema Indique e Ganhe é destinado exclusivamente a
                pessoas físicas com idade igual ou superior a 18 anos e
                CPF válido.
            </p>

            <p>
                Ao concluir o cadastro, o usuário declara possuir 18 anos
                ou mais e ser responsável pelas informações fornecidas.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>4. Dados pessoais tratados</h2>

            <p>
                Conforme a utilização do sistema, poderão ser tratados os
                dados descritos a seguir.
            </p>

            <h3>4.1. Dados informados pelo usuário</h3>

            <ul>
                <li>nome completo;</li>
                <li>CPF;</li>
                <li>endereço de e-mail;</li>
                <li>telefone e número de WhatsApp;</li>
                <li>senha, armazenada de forma protegida;</li>
                <li>informações enviadas em solicitações de suporte;</li>
                <li>
                    confirmação de leitura e aceite dos documentos
                    apresentados no cadastro.
                </li>
            </ul>

            <h3>4.2. Dados relacionados às indicações</h3>

            <ul>
                <li>link exclusivo de indicação;</li>
                <li>identificação do indicador;</li>
                <li>identificação do amigo indicado após seu cadastro;</li>
                <li>data e horário de acesso ao link;</li>
                <li>status da indicação;</li>
                <li>status da instalação do aplicativo;</li>
                <li>status do cadastro no aplicativo;</li>
                <li>motivos de aprovação ou reprovação;</li>
                <li>histórico de indicações;</li>
                <li>histórico de cupons e benefícios.</li>
            </ul>

            <h3>4.3. Dados técnicos</h3>

            <ul>
                <li>endereço IP;</li>
                <li>data e horário dos acessos;</li>
                <li>navegador utilizado;</li>
                <li>sistema operacional;</li>
                <li>identificador do dispositivo, quando disponibilizado;</li>
                <li>identificador AppsFlyer;</li>
                <li>informações relacionadas ao OneLink;</li>
                <li>registros de login e autenticação;</li>
                <li>eventos de instalação, abertura e cadastro;</li>
                <li>registros de erros e falhas de integração;</li>
                <li>informações de segurança e prevenção a fraudes;</li>
                <li>cidade ou localização aproximada, quando disponibilizada.</li>
            </ul>

            <h3>4.4. Dados relacionados aos cupons</h3>

            <ul>
                <li>código ou identificação do cupom;</li>
                <li>percentual e condições do benefício;</li>
                <li>data de geração e liberação;</li>
                <li>prazo de validade;</li>
                <li>status de utilização;</li>
                <li>informações necessárias para validação na VTEX;</li>
                <li>histórico de cancelamento, recusa ou utilização.</li>
            </ul>

            <div class="legal-page__notice">
                <p>
                    O Sistema Indique e Ganhe não solicita receitas,
                    diagnósticos, informações médicas ou outros dados
                    pessoais sensíveis relacionados à saúde.
                </p>
            </div>
        </section>

        <section class="legal-page__section">
            <h2>5. Como os dados são coletados</h2>

            <p>
                Os dados poderão ser coletados:
            </p>

            <ul>
                <li>diretamente do usuário durante o cadastro;</li>
                <li>durante o login e a utilização do sistema;</li>
                <li>quando o usuário gera ou compartilha seu link;</li>
                <li>quando o amigo indicado acessa o link;</li>
                <li>durante a instalação ou abertura do aplicativo;</li>
                <li>durante o cadastro no aplicativo Minas Mais;</li>
                <li>por meio de integrações com AppsFlyer, KOBE e VTEX;</li>
                <li>durante solicitações de suporte;</li>
                <li>
                    automaticamente, por registros técnicos necessários ao
                    funcionamento e à segurança.
                </li>
            </ul>
        </section>

        <section class="legal-page__section">
            <h2>6. Para que os dados são utilizados</h2>

            <p>
                Os dados poderão ser tratados para:
            </p>

            <ul>
                <li>criar e manter a conta do usuário;</li>
                <li>confirmar a identidade e validar o CPF;</li>
                <li>autenticar o acesso ao sistema;</li>
                <li>permitir alteração e recuperação de senha;</li>
                <li>gerar links exclusivos de indicação;</li>
                <li>identificar a origem de acessos e instalações;</li>
                <li>associar o amigo indicado ao respectivo indicador;</li>
                <li>confirmar a instalação e o cadastro no aplicativo;</li>
                <li>avaliar e atualizar o status das indicações;</li>
                <li>gerar, liberar, exibir e validar cupons;</li>
                <li>controlar a validade e a utilização dos benefícios;</li>
                <li>prestar suporte e responder solicitações;</li>
                <li>enviar comunicações operacionais sobre a campanha;</li>
                <li>prevenir autoindicação, duplicidade, fraude e abuso;</li>
                <li>investigar irregularidades;</li>
                <li>proteger usuários, sistemas e a operação da empresa;</li>
                <li>corrigir erros e melhorar o funcionamento do sistema;</li>
                <li>produzir relatórios estatísticos e operacionais;</li>
                <li>cumprir obrigações legais ou regulatórias;</li>
                <li>
                    exercer direitos em processos administrativos,
                    arbitrais ou judiciais.
                </li>
            </ul>
        </section>

        <section class="legal-page__section">
            <h2>7. Fundamentos para o tratamento</h2>

            <p>
                Conforme a finalidade e a situação concreta, o tratamento
                poderá ser realizado com fundamento:
            </p>

            <ul>
                <li>
                    na execução dos Termos de Uso e do Regulamento da
                    Campanha;
                </li>
                <li>no cumprimento de obrigação legal ou regulatória;</li>
                <li>no exercício regular de direitos;</li>
                <li>
                    no legítimo interesse da Minas Mais, respeitados os
                    direitos e as legítimas expectativas dos titulares;
                </li>
                <li>
                    na prevenção a fraudes e na segurança do titular e do
                    sistema;
                </li>
                <li>no consentimento, quando essa for a base adequada;</li>
                <li>em outras hipóteses permitidas pela LGPD.</li>
            </ul>

            <p>
                A utilização do sistema não significa que todas as operações
                de tratamento dependam exclusivamente do consentimento.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>8. Tratamento dos dados do amigo indicado</h2>

            <p>
                O indicador deverá compartilhar apenas o link disponibilizado
                pelo sistema e não deverá cadastrar terceiros em nome deles.
            </p>

            <p>
                O amigo indicado deverá fornecer pessoalmente seus próprios
                dados e concluir as etapas necessárias no aplicativo Minas
                Mais.
            </p>

            <p>
                O indicador poderá visualizar informações limitadas sobre o
                andamento da indicação, como:
            </p>

            <ul>
                <li>indicação pendente;</li>
                <li>indicação aprovada;</li>
                <li>indicação reprovada;</li>
                <li>benefício liberado.</li>
            </ul>

            <p>
                O indicador não terá acesso à senha, CPF completo, e-mail,
                telefone ou outros dados privados do amigo indicado, salvo
                informações que o próprio titular tenha compartilhado
                diretamente.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>9. Compartilhamento com fornecedores e parceiros</h2>

            <p>
                Os dados poderão ser compartilhados com fornecedores
                estritamente na medida necessária à prestação dos serviços.
            </p>

            <div class="legal-page__table-wrapper">
                <table class="legal-page__table">
                    <thead>
                        <tr>
                            <th>Fornecedor ou plataforma</th>
                            <th>Finalidade</th>
                            <th>Dados relacionados</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>AppsFlyer</td>
                            <td>
                                Atribuição do link, instalação e eventos do
                                aplicativo.
                            </td>
                            <td>
                                Identificadores técnicos, link de indicação,
                                instalação, abertura e eventos.
                            </td>
                        </tr>

                        <tr>
                            <td>KOBE</td>
                            <td>
                                Integrações, cadastro e confirmação de eventos
                                relacionados ao aplicativo.
                            </td>
                            <td>
                                Dados cadastrais e informações técnicas
                                necessárias à integração.
                            </td>
                        </tr>

                        <tr>
                            <td>VTEX</td>
                            <td>
                                Geração, validação e utilização de cupons em
                                pedidos.
                            </td>
                            <td>
                                Identificação do benefício, condições do cupom
                                e dados necessários à utilização.
                            </td>
                        </tr>

                        <tr>
                            <td>WhatsApp / Meta</td>
                            <td>
                                Compartilhamento voluntário e atendimento pelo
                                suporte.
                            </td>
                            <td>
                                Telefone, mensagem enviada e informações
                                fornecidas voluntariamente.
                            </td>
                        </tr>

                        <tr>
                            <td>Hospedagem e infraestrutura</td>
                            <td>
                                Armazenamento, segurança e funcionamento do
                                sistema.
                            </td>
                            <td>
                                Dados cadastrais, registros técnicos, logs e
                                banco de dados.
                            </td>
                        </tr>

                        <tr>
                            <td>Equipe de desenvolvimento</td>
                            <td>
                                Manutenção, correção de falhas, suporte técnico
                                e evolução do sistema.
                            </td>
                            <td>
                                Acesso limitado aos dados necessários à
                                atividade técnica.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p>
                Também poderá haver compartilhamento:
            </p>

            <ul>
                <li>com autoridades, quando exigido por lei;</li>
                <li>para cumprimento de decisão judicial ou administrativa;</li>
                <li>para investigação e prevenção de fraude;</li>
                <li>com assessorias jurídicas, auditorias ou consultorias;</li>
                <li>
                    em reorganizações societárias, fusões, aquisições ou
                    operações semelhantes, observada a legislação.
                </li>
            </ul>

            <p>
                A Minas Mais não comercializa os dados pessoais cadastrados
                no Sistema Indique e Ganhe.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>10. AppsFlyer e tecnologias de atribuição</h2>

            <p>
                O sistema e o aplicativo Minas Mais utilizam tecnologias da
                AppsFlyer para identificar quando um acesso, instalação,
                abertura ou cadastro ocorreu a partir de um link de indicação.
            </p>

            <p>
                Poderão ser tratados identificadores técnicos, parâmetros do
                OneLink, informações do dispositivo, endereço IP, eventos de
                instalação, abertura, cadastro e outros dados necessários à
                atribuição.
            </p>

            <p>
                Essas informações ajudam a determinar se a indicação cumpriu
                os critérios da campanha e a prevenir manipulações.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>11. Cookies e armazenamento técnico</h2>

            <p>
                O sistema não utiliza cookies de marketing, Google Analytics,
                Meta Pixel ou Google Tag Manager.
            </p>

            <p>
                Poderão ser utilizados cookies, sessões ou mecanismos de
                armazenamento estritamente necessários para:
            </p>

            <ul>
                <li>manter o usuário autenticado;</li>
                <li>proteger a sessão;</li>
                <li>prevenir acessos indevidos;</li>
                <li>preservar informações temporárias do formulário;</li>
                <li>garantir o funcionamento técnico da aplicação.</li>
            </ul>

            <p>
                O AppsFlyer Web SDK poderá utilizar identificadores e
                tecnologias técnicas necessárias à atribuição e mensuração
                das indicações.
            </p>

            <p>
                Caso sejam adicionadas futuramente tecnologias não essenciais
                ou voltadas à publicidade, esta Política deverá ser atualizada
                e poderão ser apresentados mecanismos específicos de escolha.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>12. Decisões e verificações automatizadas</h2>

            <p>
                Algumas verificações poderão ocorrer automaticamente para
                identificar:
            </p>

            <ul>
                <li>cadastros anteriores;</li>
                <li>duplicidade de CPF ou e-mail;</li>
                <li>autoindicação;</li>
                <li>duplicidade de dispositivo ou IP;</li>
                <li>instalação não atribuída ao link;</li>
                <li>padrões incomuns ou suspeitos;</li>
                <li>descumprimento dos limites da campanha.</li>
            </ul>

            <p>
                O usuário poderá solicitar esclarecimentos ou contestar o
                resultado pelos canais de atendimento, sem prejuízo das
                medidas necessárias à proteção dos mecanismos antifraude.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>13. Retenção dos dados</h2>

            <p>
                Os dados serão mantidos somente pelo período necessário para
                as finalidades descritas nesta Política, observados os
                seguintes critérios:
            </p>

            <div class="legal-page__table-wrapper">
                <table class="legal-page__table">
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th>Prazo ou critério de retenção</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>Dados da conta</td>
                            <td>
                                Enquanto a conta estiver ativa e pelo período
                                adicional necessário às obrigações legais e
                                defesa de direitos.
                            </td>
                        </tr>

                        <tr>
                            <td>Registros de aceite</td>
                            <td>
                                Durante a relação com o usuário e pelo período
                                necessário à comprovação e defesa de direitos.
                            </td>
                        </tr>

                        <tr>
                            <td>Logs de segurança</td>
                            <td>
                                Em regra, entre 6 e 12 meses, podendo haver
                                retenção maior quando necessária à investigação.
                            </td>
                        </tr>

                        <tr>
                            <td>Histórico de cupons e campanha</td>
                            <td>
                                Até 5 anos, salvo obrigação ou necessidade
                                legítima de prazo diferente.
                            </td>
                        </tr>

                        <tr>
                            <td>Dados de suporte</td>
                            <td>
                                Até 5 anos, conforme a natureza da solicitação.
                            </td>
                        </tr>

                        <tr>
                            <td>Registro antifraude após exclusão</td>
                            <td>
                                CPF e informações mínimas necessárias pelo
                                prazo de 2 meses.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p>
                Os prazos poderão ser ajustados quando houver obrigação legal,
                investigação, processo, solicitação de autoridade ou
                necessidade de exercício regular de direitos.
            </p>

            <p>
                Encerrada a necessidade, os dados poderão ser eliminados,
                anonimizados ou mantidos de forma segura quando permitido
                pela legislação.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>14. Exclusão da conta</h2>

            <p>
                O usuário poderá solicitar a exclusão da conta por meio da
                funcionalidade disponível no sistema ou pelos canais de
                atendimento.
            </p>

            <p>
                Após a confirmação:
            </p>

            <ul>
                <li>o login será bloqueado imediatamente;</li>
                <li>indicações pendentes serão canceladas;</li>
                <li>cupons não utilizados serão cancelados;</li>
                <li>não haverá prazo para desfazer a exclusão;</li>
                <li>um novo cadastro poderá ser realizado imediatamente;</li>
                <li>
                    o novo cadastro não restabelecerá cupons, indicações ou
                    benefícios anteriores;
                </li>
                <li>
                    o CPF poderá permanecer em registro antifraude por dois
                    meses;
                </li>
                <li>
                    dados necessários ao cumprimento de obrigações legais e
                    defesa de direitos poderão ser conservados.
                </li>
            </ul>
        </section>

        <section class="legal-page__section">
            <h2>15. Segurança da informação</h2>

            <p>
                A Minas Mais adota medidas técnicas e administrativas
                destinadas a proteger os dados contra acessos não autorizados,
                perda, alteração, destruição ou divulgação indevida.
            </p>

            <p>
                Entre as medidas que poderão ser adotadas estão:
            </p>

            <ul>
                <li>controle e restrição de acessos;</li>
                <li>proteção de senhas;</li>
                <li>registros de eventos e atividades;</li>
                <li>limitação de tentativas;</li>
                <li>monitoramento de irregularidades;</li>
                <li>cópias de segurança;</li>
                <li>atualização de sistemas;</li>
                <li>separação de ambientes e responsabilidades;</li>
                <li>contratos e orientações com prestadores.</li>
            </ul>

            <p>
                Nenhum sistema digital é completamente imune a incidentes.
                Caso seja identificado um incidente relevante, serão tomadas
                as providências cabíveis conforme a legislação e a avaliação
                dos riscos envolvidos.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>16. Direitos do titular</h2>

            <p>
                O titular poderá solicitar, nos casos previstos na legislação:
            </p>

            <ul>
                <li>confirmação da existência de tratamento;</li>
                <li>acesso aos dados pessoais;</li>
                <li>
                    correção de dados incompletos, inexatos ou desatualizados;
                </li>
                <li>
                    anonimização, bloqueio ou eliminação de dados tratados em
                    desconformidade;
                </li>
                <li>portabilidade, quando aplicável e regulamentada;</li>
                <li>
                    eliminação de dados tratados com consentimento, observadas
                    as hipóteses legais de conservação;
                </li>
                <li>
                    informação sobre entidades públicas e privadas com as
                    quais ocorreu compartilhamento;
                </li>
                <li>
                    informação sobre a possibilidade de não fornecer
                    consentimento e suas consequências;
                </li>
                <li>revogação do consentimento, quando aplicável;</li>
                <li>
                    oposição a tratamento realizado em desconformidade com a
                    legislação;
                </li>
                <li>
                    revisão de decisões tomadas unicamente com base em
                    tratamento automatizado, quando aplicável.
                </li>
            </ul>

            <p>
                Alguns pedidos poderão não resultar em eliminação imediata
                quando a conservação for necessária para cumprir obrigação
                legal, prevenir fraudes, exercer direitos ou atender outra
                hipótese autorizada.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>17. Como exercer seus direitos</h2>

            <p>
                Solicitações relacionadas aos dados pessoais poderão ser
                enviadas para:
            </p>

            <ul>
                <li>
                    E-mail:
                    <a href="mailto:ecommerce1@drogariasminasmais.com.br">
                        ecommerce1@drogariasminasmais.com.br
                    </a>
                </li>

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
            </ul>

            <p>
                Para proteger os dados contra fraude, a Minas Mais poderá
                solicitar informações necessárias à confirmação da identidade
                do requerente.
            </p>

            <p>
                O atendimento ocorre de segunda a sexta-feira, das 8h às 18h,
                com prazo médio estimado de resposta de até dois dias úteis,
                sem prejuízo dos prazos legais aplicáveis.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>18. Responsabilidades do usuário</h2>

            <p>
                O usuário deverá:
            </p>

            <ul>
                <li>fornecer dados verdadeiros e próprios;</li>
                <li>manter os dados atualizados;</li>
                <li>proteger sua senha e seu dispositivo;</li>
                <li>não compartilhar acesso à conta;</li>
                <li>não cadastrar terceiros sem autorização;</li>
                <li>não utilizar o sistema para fraude ou abuso;</li>
                <li>
                    comunicar situações suspeitas ou acessos não autorizados.
                </li>
            </ul>

            <p>
                O usuário não deverá enviar pelo suporte senhas, documentos
                desnecessários, informações de saúde ou outros dados não
                solicitados.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>19. Serviços externos</h2>

            <p>
                O sistema poderá disponibilizar links para o WhatsApp, o
                aplicativo Minas Mais, lojas de aplicativos e outros serviços
                externos.
            </p>

            <p>
                Esses serviços possuem políticas de privacidade e termos
                próprios. A Minas Mais não controla integralmente as práticas
                de plataformas externas.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>20. Alterações desta Política</h2>

            <p>
                Esta Política poderá ser atualizada em razão de alterações
                legais, tecnológicas, operacionais ou comerciais.
            </p>

            <p>
                A versão vigente será disponibilizada no sistema com a
                respectiva data de atualização.
            </p>

            <p>
                Quando uma alteração relevante afetar o tratamento dos dados
                ou os direitos dos usuários, a Minas Mais poderá apresentar
                aviso e solicitar nova ciência ou aceite, quando necessário.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>21. Documentos relacionados</h2>

            <p>
                Esta Política deve ser lida juntamente com:
            </p>

            <ul>
                <li>
                    <a href="<?= htmlspecialchars(url('termos-de-uso'), ENT_QUOTES, 'UTF-8') ?>">
                        Termos de Uso
                    </a>
                </li>

                <li>
                    <a href="<?= htmlspecialchars(url('regulamento'), ENT_QUOTES, 'UTF-8') ?>">
                        Regulamento da Campanha Indique e Ganhe
                    </a>
                </li>
            </ul>
        </section>

        <section class="legal-page__section">
            <h2>22. Contato</h2>

            <div class="legal-page__company">
                <p>
                    <strong>M.H.L. Drogaria S.A.</strong><br>
                    Drogaria Minas Mais
                </p>

                <p>
                    CNPJ 09.396.401/0001-87
                </p>

                <p>
                    Avenida Afonso Pena, nº 504, Centro,
                    Campo Belo/MG, CEP 37270-000
                </p>

                <p>
                    <strong>Canal de privacidade:</strong><br>
                    <a href="mailto:ecommerce1@drogariasminasmais.com.br">
                        ecommerce1@drogariasminasmais.com.br
                    </a>
                </p>

                <p>
                    <strong>WhatsApp:</strong><br>
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