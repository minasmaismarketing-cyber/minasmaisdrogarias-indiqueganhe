<?php
/**
 * Regulamento da Campanha Indique e Ganhe
 *
 * Rota pública sugerida:
 * /regulamento
 *
 * Este arquivo deve ser carregado pelo layout público já existente.
 */

$pageTitle = 'Regulamento da Campanha';
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

    .legal-page__notice--success {
        background: #f0fbf5;
        border-left-color: #168a4a;
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

    .legal-page__benefit {
        margin-top: 18px;
        padding: 20px;
        background: #ffffff;
        border: 1px solid #e9e9e9;
        border-radius: 16px;
    }

    .legal-page__benefit-title {
        margin: 0 0 12px;
        color: #d71920;
        font-size: 1.05rem;
        font-weight: 800;
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
            Drogarias Minas Mais
        </span>

        <h1 class="legal-page__title">
            Regulamento da Campanha Indique e Ganhe
        </h1>

        <p class="legal-page__subtitle">
            Versão 1.0<br>
            Período da campanha: 21 a 31 de julho de 2026<br>
            Última atualização: 21 de julho de 2026
        </p>
    </header>

    <article class="legal-page__content">
        <section class="legal-page__section">
            <h2>1. Empresa promotora</h2>

            <p>
                A campanha <strong>Indique e Ganhe</strong> é promovida por:
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
                    <strong>E-mail de atendimento:</strong>
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
            <h2>2. Objetivo da campanha</h2>

            <p>
                A campanha tem como objetivo incentivar a indicação de novos
                usuários para o aplicativo Minas Mais.
            </p>

            <p>
                O participante poderá gerar um link exclusivo de indicação,
                compartilhá-lo com amigos e receber o benefício previsto neste
                Regulamento quando ao menos uma indicação cumprir todos os
                critérios de aprovação.
            </p>

            <p>
                O amigo indicado elegível também poderá receber um benefício
                para utilização em sua primeira compra.
            </p>

            <div class="legal-page__notice legal-page__notice--neutral">
                <p>
                    A campanha não envolve sorteio, concurso, escolha aleatória
                    ou contemplação por chance. Os benefícios são concedidos
                    aos participantes que cumprirem os critérios objetivos
                    previstos neste Regulamento.
                </p>
            </div>
        </section>

        <section class="legal-page__section">
            <h2>3. Período de participação</h2>

            <p>
                A campanha será válida no seguinte período:
            </p>

            <ul>
                <li>
                    <strong>Início:</strong>
                    21 de julho de 2026, às 00h00.
                </li>

                <li>
                    <strong>Encerramento:</strong>
                    31 de julho de 2026, às 23h59.
                </li>
            </ul>

            <p>
                Todos os horários indicados neste Regulamento consideram o
                horário oficial de Brasília.
            </p>

            <p>
                Não haverá prazo adicional para conclusão ou validação de
                indicações pendentes após o encerramento da campanha.
            </p>

            <p>
                Para ser analisada, a indicação deverá ter todas as etapas
                obrigatórias concluídas e confirmadas dentro do período da
                campanha.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>4. Abrangência</h2>

            <p>
                A campanha possui abrangência nacional e poderá receber
                participantes de qualquer localidade do Brasil.
            </p>

            <p>
                A utilização dos benefícios estará sujeita à disponibilidade
                do site, do aplicativo Minas Mais, dos produtos, dos serviços
                de entrega e dos demais canais operacionais disponíveis para
                cada região.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>5. Quem pode participar</h2>

            <p>
                Poderá participar como indicador a pessoa que:
            </p>

            <ul>
                <li>seja pessoa física;</li>
                <li>possua idade igual ou superior a 18 anos;</li>
                <li>possua CPF válido;</li>
                <li>informe dados próprios, verdadeiros e atualizados;</li>
                <li>realize cadastro no Sistema Indique e Ganhe;</li>
                <li>
                    aceite os Termos de Uso, a Política de Privacidade e este
                    Regulamento;
                </li>
                <li>cumpra as condições da campanha.</li>
            </ul>

            <p>
                Não é necessário ser cliente antigo, possuir cadastro anterior
                ou já utilizar o aplicativo Minas Mais para participar como
                indicador.
            </p>

            <p>
                Colaboradores, franqueados e familiares de colaboradores ou
                franqueados poderão participar, desde que cumpram integralmente
                as mesmas regras aplicáveis aos demais usuários.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>6. Como participar</h2>

            <p>
                Para participar, o interessado deverá:
            </p>

            <ol>
                <li>acessar o Sistema Indique e Ganhe;</li>
                <li>realizar seu cadastro com dados próprios e verdadeiros;</li>
                <li>aceitar os documentos legais apresentados;</li>
                <li>acessar sua conta;</li>
                <li>gerar ou acessar seu link exclusivo de indicação;</li>
                <li>compartilhar o link com seus amigos;</li>
                <li>acompanhar o status das indicações pelo sistema.</li>
            </ol>

            <p>
                O simples cadastro, a geração do link ou o compartilhamento
                não geram direito automático ao cupom.
            </p>

            <p>
                O indicador receberá apenas um cupom de 10% durante toda a
                campanha, ainda que possua mais de uma indicação aprovada.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>7. Requisitos do amigo indicado</h2>

            <p>
                Para que a indicação seja considerada válida, o amigo indicado
                deverá:
            </p>

            <ol>
                <li>
                    acessar obrigatoriamente o link exclusivo recebido do
                    indicador;
                </li>

                <li>
                    seguir o fluxo de acesso, instalação ou abertura do
                    aplicativo apresentado pela campanha;
                </li>

                <li>instalar ou abrir o aplicativo Minas Mais;</li>

                <li>
                    concluir um novo cadastro utilizando nome, CPF, e-mail e
                    telefone próprios e verdadeiros;
                </li>

                <li>
                    validar telefone ou e-mail quando a confirmação for
                    solicitada;
                </li>

                <li>
                    concluir as etapas obrigatórias até 31 de julho de 2026,
                    às 23h59;
                </li>

                <li>
                    não possuir cadastro anterior no aplicativo Minas Mais;
                </li>

                <li>
                    não praticar autoindicação, duplicidade, fraude ou qualquer
                    conduta proibida.
                </li>
            </ol>

            <p>
                O amigo indicado não precisa realizar uma compra para que a
                indicação seja analisada.
            </p>

            <p>
                O usuário que já tenha instalado o aplicativo, mas nunca tenha
                concluído um cadastro, poderá ser considerado elegível, desde
                que o novo cadastro seja corretamente associado ao link de
                indicação.
            </p>

            <p>
                O usuário que já tenha possuído cadastro anteriormente,
                inclusive aquele que tenha excluído sua conta, não será
                considerado novo usuário para fins desta campanha.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>8. Atribuição e validação</h2>

            <p>
                A identificação da indicação poderá depender da integração
                entre o Sistema Indique e Ganhe, o aplicativo Minas Mais, a
                AppsFlyer, a KOBE, a VTEX e os demais sistemas envolvidos.
            </p>

            <p>
                A AppsFlyer será utilizada para confirmar, conforme os dados
                técnicos disponíveis:
            </p>

            <ul>
                <li>o acesso pelo link de indicação;</li>
                <li>a atribuição da instalação;</li>
                <li>a abertura do aplicativo;</li>
                <li>os eventos relacionados ao cadastro;</li>
                <li>a associação entre o indicador e o amigo indicado.</li>
            </ul>

            <p>
                A indicação somente será aprovada após o recebimento e a
                validação das informações necessárias.
            </p>

            <p>
                O status poderá permanecer pendente enquanto a confirmação
                técnica, cadastral ou antifraude não tiver sido concluída.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>9. Critérios de aprovação</h2>

            <p>
                A indicação poderá ser aprovada quando:
            </p>

            <ul>
                <li>o amigo acessar o link correto;</li>
                <li>a instalação ou abertura for atribuída ao indicador;</li>
                <li>o cadastro no aplicativo for concluído;</li>
                <li>o CPF, e-mail e telefone forem válidos;</li>
                <li>não existir cadastro anterior;</li>
                <li>as etapas forem concluídas durante a campanha;</li>
                <li>não houver autoindicação ou duplicidade;</li>
                <li>não forem identificados indícios de fraude;</li>
                <li>as integrações confirmarem os eventos necessários;</li>
                <li>todas as demais regras forem cumpridas.</li>
            </ul>

            <p>
                A aprovação da indicação não exige que o amigo indicado faça
                sua primeira compra.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>10. Motivos de reprovação</h2>

            <p>
                A indicação poderá ser reprovada, entre outras hipóteses, em
                razão de:
            </p>

            <ul>
                <li>cadastro já existente;</li>
                <li>CPF já cadastrado;</li>
                <li>e-mail já cadastrado;</li>
                <li>instalação não atribuída ao link de indicação;</li>
                <li>cadastro incompleto;</li>
                <li>dados inválidos, falsos ou inconsistentes;</li>
                <li>campanha encerrada;</li>
                <li>autoindicação;</li>
                <li>duplicidade de dispositivo;</li>
                <li>duplicidade de IP ou padrão de acesso incompatível;</li>
                <li>suspeita fundamentada de fraude;</li>
                <li>limite de benefício atingido;</li>
                <li>falha ou ausência de confirmação da integração;</li>
                <li>descumprimento deste Regulamento.</li>
            </ul>

            <p>
                Para preservar a efetividade dos mecanismos de segurança, a
                promotora poderá não divulgar detalhes técnicos específicos
                das regras antifraude.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>11. Benefício do indicador</h2>

            <div class="legal-page__benefit">
                <p class="legal-page__benefit-title">
                    Cupom exclusivo de 10% OFF
                </p>

                <p>
                    O indicador que possuir ao menos uma indicação aprovada
                    receberá um único cupom de 10% de desconto.
                </p>
            </div>

            <p>
                O cupom do indicador possui as seguintes condições:
            </p>

            <ul>
                <li>um cupom por usuário durante toda a campanha;</li>
                <li>uso único;</li>
                <li>pessoal e vinculado ao cadastro elegível;</li>
                <li>válido no site e no aplicativo Minas Mais;</li>
                <li>válido até 31 de julho de 2026, às 23h59;</li>
                <li>sem valor mínimo de compra;</li>
                <li>sem limite máximo de desconto;</li>
                <li>sem categorias de produtos previamente excluídas;</li>
                <li>cumulativo com promoções aplicadas aos produtos;</li>
                <li>não cumulativo com outro cupom;</li>
                <li>não conversível em dinheiro;</li>
                <li>não transferível e não comercializável.</li>
            </ul>

            <div class="legal-page__notice">
                <p>
                    A validade do cupom do indicador termina juntamente com a
                    campanha, independentemente da data ou do horário em que a
                    indicação for aprovada.
                </p>
            </div>
        </section>

        <section class="legal-page__section">
            <h2>12. Benefício do amigo indicado</h2>

            <div class="legal-page__benefit">
                <p class="legal-page__benefit-title">
                    Cupom de 5% OFF na primeira compra
                </p>

                <p>
                    O amigo indicado aprovado receberá um cupom de 5% de
                    desconto para utilização em sua primeira compra elegível.
                </p>
            </div>

            <p>
                O cupom do amigo indicado possui as seguintes condições:
            </p>

            <ul>
                <li>liberação após a aprovação da indicação;</li>
                <li>validade de 30 dias contados da data de liberação;</li>
                <li>uso único;</li>
                <li>aplicável somente à primeira compra elegível;</li>
                <li>pessoal e vinculado ao cadastro do amigo indicado;</li>
                <li>sem valor mínimo de compra;</li>
                <li>sem limite máximo de desconto;</li>
                <li>sem categorias de produtos previamente excluídas;</li>
                <li>cumulativo com promoções aplicadas aos produtos;</li>
                <li>não cumulativo com outro cupom;</li>
                <li>não conversível em dinheiro;</li>
                <li>não transferível e não comercializável.</li>
            </ul>
        </section>

        <section class="legal-page__section">
            <h2>13. Disponibilização e utilização dos cupons</h2>

            <p>
                Os benefícios poderão ser disponibilizados na área de cupons
                do sistema, no aplicativo Minas Mais ou por outro canal
                informado pela promotora.
            </p>

            <p>
                A geração, validação ou utilização dos cupons poderá depender
                da integração com a VTEX e com os demais sistemas envolvidos
                na operação.
            </p>

            <p>
                O participante é responsável por:
            </p>

            <ul>
                <li>acompanhar a disponibilização do benefício;</li>
                <li>verificar as condições apresentadas no cupom;</li>
                <li>utilizá-lo dentro do prazo de validade;</li>
                <li>manter sua conta e seus dados de acesso protegidos;</li>
                <li>não compartilhar ou comercializar o benefício.</li>
            </ul>

            <p>
                Cupons expirados, utilizados, cancelados ou vinculados a
                cadastro inelegível não poderão ser reativados, salvo correção
                de erro comprovadamente atribuível à promotora.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>14. Práticas proibidas</h2>

            <p>
                É proibido:
            </p>

            <ul>
                <li>indicar a si próprio;</li>
                <li>utilizar CPF, telefone ou e-mail de terceiros;</li>
                <li>criar contas falsas ou duplicadas;</li>
                <li>criar conta em nome de outra pessoa;</li>
                <li>simular instalações ou eventos do aplicativo;</li>
                <li>usar emuladores, robôs, scripts ou automações;</li>
                <li>manipular links, identificadores ou parâmetros;</li>
                <li>explorar falhas ou vulnerabilidades;</li>
                <li>comprar, vender ou trocar indicações;</li>
                <li>transferir, vender ou comercializar cupons;</li>
                <li>divulgar condições diferentes das oficiais;</li>
                <li>apresentar-se como representante da Minas Mais;</li>
                <li>praticar qualquer ato destinado a obter vantagem indevida.</li>
            </ul>
        </section>

        <section class="legal-page__section">
            <h2>15. Prevenção e análise de fraude</h2>

            <p>
                A promotora poderá realizar verificações automáticas e manuais
                destinadas a proteger a campanha e seus participantes.
            </p>

            <p>
                A análise poderá considerar, entre outras informações:
            </p>

            <ul>
                <li>CPF, e-mail e telefone;</li>
                <li>endereço IP;</li>
                <li>dispositivo e sistema operacional;</li>
                <li>identificadores técnicos;</li>
                <li>datas e horários de acesso;</li>
                <li>histórico de cadastro;</li>
                <li>quantidade e padrão das indicações;</li>
                <li>atribuição fornecida pela AppsFlyer;</li>
                <li>eventos confirmados pelo aplicativo;</li>
                <li>histórico de cupons e utilização.</li>
            </ul>

            <p>
                Havendo suspeita fundamentada de irregularidade, a indicação,
                o cupom ou a conta poderão permanecer suspensos durante a
                análise.
            </p>

            <p>
                A promotora poderá solicitar esclarecimentos ou informações
                adicionais para confirmar a regularidade da participação.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>16. Cancelamento e desclassificação</h2>

            <p>
                A promotora poderá reprovar indicações, cancelar benefícios,
                suspender contas ou desclassificar participantes quando houver:
            </p>

            <ul>
                <li>fraude ou tentativa de fraude;</li>
                <li>cadastro falso ou duplicado;</li>
                <li>uso de dados de terceiros;</li>
                <li>autoindicação;</li>
                <li>manipulação técnica;</li>
                <li>descumprimento deste Regulamento;</li>
                <li>erro evidente na concessão do benefício;</li>
                <li>determinação legal, administrativa ou judicial.</li>
            </ul>

            <p>
                A desclassificação poderá alcançar todas as indicações e
                benefícios associados ao participante, conforme a natureza da
                irregularidade identificada.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>17. Erros e divergências no sistema</h2>

            <p>
                Caso o sistema apresente percentual, quantidade, validade ou
                condição de benefício manifestamente incompatível com este
                Regulamento, a situação será analisada e corrigida.
            </p>

            <p>
                Um erro técnico isolado não cria automaticamente direito a um
                benefício diferente daquele oficialmente previsto.
            </p>

            <p>
                As correções deverão respeitar a boa-fé, os direitos do
                consumidor e os benefícios regularmente adquiridos.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>18. Indisponibilidade técnica</h2>

            <p>
                O funcionamento da campanha depende de serviços tecnológicos,
                conexão à internet e sistemas próprios e de terceiros.
            </p>

            <p>
                Poderão ocorrer interrupções ou falhas relacionadas a:
            </p>

            <ul>
                <li>internet ou energia;</li>
                <li>aparelho ou navegador do participante;</li>
                <li>aplicativo Minas Mais;</li>
                <li>AppsFlyer;</li>
                <li>KOBE;</li>
                <li>VTEX;</li>
                <li>WhatsApp;</li>
                <li>lojas de aplicativos;</li>
                <li>hospedagem e infraestrutura;</li>
                <li>manutenções ou atualizações.</li>
            </ul>

            <p>
                A promotora buscará corrigir falhas sob sua responsabilidade,
                mas não garante funcionamento ininterrupto de serviços
                externos.
            </p>

            <p>
                A ausência de confirmação técnica dentro do prazo da campanha
                poderá impedir a aprovação da indicação quando não existirem
                registros suficientes para validar o cumprimento das etapas.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>19. Exclusão da conta durante a campanha</h2>

            <p>
                Caso o participante exclua sua conta:
            </p>

            <ul>
                <li>o acesso será bloqueado imediatamente;</li>
                <li>as indicações pendentes serão canceladas;</li>
                <li>os cupons não utilizados serão cancelados;</li>
                <li>não haverá prazo para desfazer a exclusão;</li>
                <li>um novo cadastro poderá ser criado imediatamente;</li>
                <li>
                    o novo cadastro não recuperará indicações ou benefícios
                    anteriores;
                </li>
                <li>
                    a pessoa não será considerada novo usuário quando houver
                    registro de cadastro anterior.
                </li>
            </ul>

            <p>
                O CPF poderá permanecer em registro restrito de prevenção a
                fraudes pelo prazo de dois meses após a exclusão.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>20. Contestação de reprovação</h2>

            <p>
                O participante poderá contestar a reprovação em até
                <strong>um dia corrido</strong> após a disponibilização do
                respectivo status.
            </p>

            <p>
                A solicitação deverá ser encaminhada pelos canais oficiais e
                conter informações suficientes para localização e análise da
                indicação.
            </p>

            <p>
                O envio da contestação não garante aprovação automática. A
                decisão será baseada nos registros cadastrais, técnicos e
                operacionais disponíveis.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>21. Atendimento</h2>

            <p>
                O atendimento da campanha funciona de segunda a sexta-feira,
                das 8h às 18h.
            </p>

            <ul>
                <li>
                    <strong>WhatsApp:</strong>
                    <a
                        href="https://wa.me/553599125296?text=Ol%C3%A1%21%20Vim%20pelo%20Sistema%20Indique%20e%20Ganhe%20das%20Drogarias%20Minas%20Mais%20e%20preciso%20de%20ajuda."
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        +55 35 9912-5296
                    </a>
                </li>

                <li>
                    <strong>E-mail:</strong>
                    <a href="mailto:ecommerce1@drogariasminasmais.com.br">
                        ecommerce1@drogariasminasmais.com.br
                    </a>
                </li>
            </ul>

            <p>
                A mensagem sugerida para contato pelo WhatsApp é:
            </p>

            <div class="legal-page__notice legal-page__notice--success">
                <p>
                    “Olá! Vim pelo Sistema Indique e Ganhe das Drogarias Minas
                    Mais e preciso de ajuda.”
                </p>
            </div>

            <p>
                O prazo médio estimado para resposta é de até dois dias úteis,
                podendo variar conforme a complexidade da solicitação.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>22. Alteração, suspensão ou encerramento</h2>

            <p>
                A promotora poderá modificar, suspender ou encerrar a campanha
                quando houver:
            </p>

            <ul>
                <li>determinação legal ou de autoridade competente;</li>
                <li>fraude generalizada;</li>
                <li>falha técnica relevante;</li>
                <li>risco à segurança dos participantes;</li>
                <li>indisponibilidade de fornecedor essencial;</li>
                <li>caso fortuito ou força maior;</li>
                <li>circunstância que torne a continuidade inviável.</li>
            </ul>

            <p>
                Sempre que possível, alterações relevantes serão comunicadas
                pelos canais oficiais.
            </p>

            <p>
                Benefícios regularmente adquiridos serão respeitados, salvo
                fraude, erro evidente, determinação legal ou impossibilidade
                devidamente justificada.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>23. Proteção de dados</h2>

            <p>
                Os dados pessoais serão tratados conforme a
                <a href="<?= htmlspecialchars(url('politica-de-privacidade'), ENT_QUOTES, 'UTF-8') ?>">
                    Política de Privacidade
                </a>
                do Sistema Indique e Ganhe.
            </p>

            <p>
                A Política explica os dados coletados, suas finalidades, as
                integrações utilizadas, os prazos de retenção, as medidas de
                segurança e os direitos dos titulares.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>24. Aceite dos documentos</h2>

            <p>
                Ao concluir o cadastro e marcar a opção de aceite, o
                participante declara:
            </p>

            <ul>
                <li>possuir 18 anos ou mais;</li>
                <li>ter lido e aceitado este Regulamento;</li>
                <li>
                    ter lido e aceitado os
                    <a href="<?= htmlspecialchars(url('termos-de-uso'), ENT_QUOTES, 'UTF-8') ?>">
                        Termos de Uso
                    </a>;
                </li>
                <li>
                    ter lido a
                    <a href="<?= htmlspecialchars(url('politica-de-privacidade'), ENT_QUOTES, 'UTF-8') ?>">
                        Política de Privacidade
                    </a>;
                </li>
                <li>estar ciente das condições dos cupons e da campanha.</li>
            </ul>
        </section>

        <section class="legal-page__section">
            <h2>25. Disposições finais</h2>

            <p>
                A eventual tolerância quanto ao descumprimento de uma condição
                não representará renúncia ao direito de exigir seu cumprimento
                posteriormente.
            </p>

            <p>
                Caso alguma disposição seja considerada inválida ou
                inaplicável, as demais permanecerão válidas.
            </p>

            <p>
                Situações não previstas serão analisadas pela promotora com
                base neste Regulamento, nos Termos de Uso, na boa-fé e na
                legislação aplicável.
            </p>
        </section>

        <section class="legal-page__section">
            <h2>26. Legislação e foro</h2>

            <p>
                Este Regulamento é regido pela legislação da República
                Federativa do Brasil.
            </p>

            <p>
                Fica indicado o foro da Comarca de Campo Belo/MG para solução
                de controvérsias, sem prejuízo do foro legalmente assegurado ao
                consumidor e de outras competências obrigatórias previstas em
                lei.
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