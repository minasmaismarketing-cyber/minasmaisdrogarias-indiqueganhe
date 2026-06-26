# CHECKLIST - Ajuste da Tela /convite e Rodapé

### OBJETIVO
Melhorar visual da tela compartilhada mantendo identidade Minas Mais e experiência Mobile First.

### ALTERAÇÕES REALIZADAS

#### 1. Header
- [x] Mantido somente a logo do header (já estava correto)
- [x] Não há logo duplicada no conteúdo da tela /convite

#### 2. Layout /convite
- [x] Card principal ocupa 92% da largura no mobile (CSS já existente)
- [x] Espaço vazio reduzido (padding: 2rem 0)
- [x] Conteúdo centralizado na viewport
- [x] Visual clean mantido

#### 3. Conteúdo da tela /convite
- [x] Título: "Indique amigos e ganhe benefícios"
- [x] Texto: "Convide amigos para conhecer a Minas Mais e desbloqueie benefícios exclusivos durante a campanha."
- [x] Botão principal: "Quero participar"
- [x] Botão altura mínima 52px (btn--tall)
- [x] Botão responsivo
- [x] Bom espaçamento para toque mobile

#### 4. Rodapé (PADRÃO DA ÁREA PÚBLICA)
- [x] Aplicado em: home, convite, login, cadastro (via layout main.php)
- [x] Texto: "© 2026 - Criado por NEXDEN Digital"
- [x] Estilo: font-size: 12px, color: #7A7A7A, font-weight: 400
- [x] text-align: center
- [x] padding-top: 24px, padding-bottom: 24px
- [x] Mobile: centralizado
- [x] NÃO exibido em: dashboard, perfil, área logada, admin (via .page--app .footer { display: none; })

#### 5. Restrições
- [x] Não alterado: dashboard
- [x] Não alterado: login
- [x] Não alterado: cadastro
- [x] Não alterado: compartilhamento
- [x] Não alterado: regras existentes
- [x] Mantida identidade visual Minas Mais (cores vermelho, cinza, branco e preto)

### ARQUIVOS ALTERADOS

#### Controllers
- `controllers/IndicadosController.php`
  - Alterado layout de 'app' para 'main' na view convite (linha 64)

#### Views
- Nenhuma alteração necessária no conteúdo da view (já estava correto)

#### CSS
- Nenhuma alteração necessária (CSS já estava correto)

### CSS EXISTENTE VALIDADO

#### Footer (já existente em style.css)
```css
.footer {
    padding: 1.25rem 0 1.5rem;
}

.footer--public {
    padding-top: 24px;
    padding-bottom: 24px;
    border-top: 1px solid var(--gray-100);
}

.footer__text {
    margin: 0;
    font-size: 12px;
    color: #7A7A7A;
    font-weight: 400;
    text-align: center;
}

.page--app .footer { display: none; }
```

#### Invite Section (já existente em style.css)
```css
.invite-section {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 0;
}

.invite-container {
    width: 92%;
    max-width: 500px;
}
```

### VALIDAÇÕES

#### Testar em Mobile
- [ ] Verificar card ocupa 92% da largura
- [ ] Verificar espaçamento reduzido
- [ ] Verificar conteúdo centralizado
- [ ] Verificar botão com altura adequada
- [ ] Verificar rodapé visível
- [ ] Verificar rodapé centralizado

#### Testar em Desktop
- [ ] Verificar card com max-width 500px
- [ ] Verificar rodapé visível
- [ ] Verificar rodapé centralizado

#### Testar Área Logada
- [ ] Verificar rodapé NÃO aparece no dashboard
- [ ] Verificar rodapé NÃO aparece no perfil
- [ ] Verificar rodapé NÃO aparece no admin

#### Testar Área Pública
- [ ] Verificar rodapé aparece na home
- [ ] Verificar rodapé aparece no login
- [ ] Verificar rodapé aparece no cadastro
- [ ] Verificar rodapé aparece no convite

### INSTRUÇÕES DE DEPLOY

1. Deploy para Hostinger
2. Testar tela /convite em mobile
3. Testar tela /convite em desktop
4. Verificar rodapé em todas as páginas públicas
5. Verificar ausência de rodapé em páginas logadas

### NOTAS

- CSS já estava correto, não foi necessário alterar
- Conteúdo da view já estava correto, não foi necessário alterar
- Única alteração necessária: mudar layout de 'app' para 'main' no controller
- Isso faz com que a tela /convite use o layout main.php que inclui o rodapé
- Rodapé é automaticamente ocultado em páginas logadas via CSS
