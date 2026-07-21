# Compartilhamento do benefício do indicado via WhatsApp

Funcionalidade **independente** da integração KOBE / AppsFlyer / OneLink.

## Comportamento

- Apenas **abre** o WhatsApp (`wa.me`) com mensagem pronta.
- Não há confirmação de envio, entrega ou leitura.
- Registro em `eventos`: `BENEFICIO_WHATSAPP_OPENED` com payload `canal=whatsapp`, `status=opened`.
- Cupom fixo informado na mensagem: **MINAS-5** (5% OFF). Não altera estoque de cupons 10%.
- Disponível somente para indicações com status de aprovação concluída (`APROVADO` / `BENEFICIO_LIBERADO` e/ou `VALIDADO` / `PREMIO_LIBERADO`).
- Telefone normalizado no backend (`normalize_brazilian_whatsapp_number`); UI mostra apenas máscara `••XX`.
- Rota: `POST /indicacoes/{id}/compartilhar-beneficio` (auth + CSRF).

## Contrato KOBE

Nenhuma mudança no endpoint, Bearer Token, payload ou respostas da API KOBE.
