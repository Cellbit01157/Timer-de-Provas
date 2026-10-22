# Timer de Provas ⏱️

Sistema web completo e responsivo para cronometragem, gestão de alertas audiovisuais e auditoria de avaliações, concursos e exames acadêmicos.

---

## 🚀 Funcionalidades Principais

1. **Autenticação Segura do Coordenador:**
   - Cadastro com validação de CPF e E-mail.
   - Envio automático de confirmação de cadastro por e-mail.
   - Criptografia de senhas com algoritmo `bcrypt` (`password_hash`).
   - Acesso seguro utilizando **E-mail ou CPF** com sessões nativas PHP protegidas contra Session Fixation.

2. **Timer de Provas Configurável:**
   - Definição do tempo total da avaliação (horas e minutos).
   - Definição do momento de início dos alertas (ex: quando faltarem 15 minutos).
   - Definição do intervalo de repetição entre cada alerta (ex: a cada 5 minutos).
   - Opção de ligar/desligar alertas sonoros com 4 timbres sintetizados via **Web Audio API** (Beep Duplo, Sino Escolar, Sirene Suave, Ping Curto) — sem necessidade de carregar arquivos externos de áudio.
   - Botão para testar o som do alerta antes de iniciar a prova.
   - Alertas visuais e flash de tela para atenção silenciosa na sala.
   - Suporte à vibração em dispositivos móveis (`navigator.vibrate`).
   - Modo **Tela Cheia (Fullscreen)** para projeção em data-show e monitores.
   - Ajustes rápidos de tempo durante a aplicação (+1m, +5m, -1m, -5m).

3. **Registro de Resultados e Posições:**
   - Botão para registrar a entrega das provas pelos candidatos em tempo real.
   - Registro numerado das posições (1º Lugar, 2º Lugar, etc.), horário da entrega e tempo decorrido.
   - Salvamento do histórico da prova com observações e relatório final da sala.

4. **Auditoria & Rastreabilidade (Logs):**
   - Página exclusiva de auditoria (`historico.php`) para fiscalizações.
   - Armazenamento automático de qualquer evento: cadastros, logins, início de timer, pausas, retomadas, ajustes de tempo, disparos de alerta e finalizações.
   - Filtros de busca por ação, usuário, termo e endereço IP com paginação.

5. **Notificações Toast Modernas:**
   - Alertas de interface amigáveis (sucesso, atenção, erro e informação) estilizados com Tailwind CSS e auto-dismiss com barra de progresso.

---

## 🛠️ Stack Tecnológica

- **Backend:** PHP 8+ estruturado com sessões nativas seguras.
- **Banco de Dados:** MySQL 5.7+ / MariaDB com conexões estritas via **PDO** e Prepared Statements contra SQL Injection.
- **Frontend:** HTML5, CSS3, Tailwind CSS (Mobile-First) e Vanilla JavaScript com Web Audio API.

---

## 📂 Estrutura do Projeto

```text
TimerdeProvas/
├── actions/                      # Scripts isolados de processamento de regras
│   ├── auth_login.php            # Processa autenticação por E-mail ou CPF
│   ├── auth_logout.php           # Encerra sessão e audita logout
│   ├── auth_register.php         # Cadastro de coordenador, bcrypt e disparo de e-mail
│   ├── timer_log_event.php       # Endpoint AJAX para log de eventos em tempo real
│   ├── timer_save_config.php     # Salva preferências de alertas do timer
│   └── timer_save_run.php        # Salva o resultado final e posições dos alunos
├── assets/
│   ├── css/
│   │   └── custom.css            # Animações de alerta, display digital e transições
│   └── js/
│       ├── masks.js              # Máscaras de CPF e inputs
│       ├── timer.js              # Engine do Timer, Web Audio API, posições e tela cheia
│       └── toast.js              # Gerenciador de toasts flutuantes
├── config/
│   ├── bd.php                    # Conexão PDO segura (Prepared Statements)
│   └── config.php                # Constantes de URL, timezone e inicialização de sessão
├── includes/
│   ├── auth.php                  # Helpers de sessão, validação de CPF e autenticação
│   ├── footer.php                # Rodapé e scripts globais
│   ├── header.php                # Barra de navegação responsiva (Mobile-First)
│   ├── logger.php                # Registrador de logs de auditoria
│   ├── mailer.php                # Template e despachante de e-mail de confirmação
│   └── toast.php                 # Helpers de mensagens toast na sessão
├── database.sql                  # Script SQL de criação do banco e tabelas
├── instalar.php                  # Assistente web de instalação em 1 clique
├── index.php                     # Página de apresentação e boas-vindas
├── login.php                     # Formulário de login do coordenador
├── cadastro.php                  # Formulário de cadastro com validações
├── timer.php                     # Interface do cronômetro de provas e alertas
├── historico.php                  # Painel de auditoria e logs do sistema
├── provas.php                    # Histórico de provas realizadas e posições dos alunos
├── requisitos.md                 # Especificação de requisitos do projeto
└── README.md                     # Documentação oficial
```

---

## ⚡ Como Rodar o Projeto

### Opção 1: Usando o XAMPP / WampServer / Laragon
1. Copie a pasta do projeto para a pasta pública do seu servidor local:
   - Exemplo XAMPP: `C:\xampp\htdocs\TimerdeProvas`
2. Certifique-se de que os serviços **Apache** e **MySQL** estejam iniciados no painel do XAMPP.
3. Abra o navegador e acesse:
   ```text
   http://localhost/TimerdeProvas/instalar.php
   ```
4. Clique em **"Instalar e Inicializar Banco de Dados"** para criar o banco `timer_provas` e todas as tabelas automaticamente.
5. Pronto! Acesse `http://localhost/TimerdeProvas`.

### Opção 2: Usando o Servidor Embutido do PHP (CLI)
1. Certifique-se de ter o MySQL ativo e execute o script `database.sql` no seu MySQL:
   ```bash
   mysql -u root -p < database.sql
   ```
2. Inicie o servidor PHP embutido na pasta do projeto:
   ```bash
   php -S 127.0.0.1:8000
   ```
3. Acesse no navegador:
   ```text
   http://127.0.0.1:8000
   ```

---

## 🔑 Acesso Demonstrativo Pré-cadastrado

Caso queira testar imediatamente sem cadastrar uma nova conta:
- **E-mail:** `coordenador@etec.sp.gov.br`
- **Senha:** `Admin@123`

---

## 🛡️ Regras de Segurança Implementadas
- **Zero SQL Injection:** Todas as operações no banco utilizam parâmetros vinculados (`bindValue`/`execute`) através do PDO.
- **Proteção contra Cross-Site Scripting (XSS):** Todas as saídas de tela são sanitizadas com `htmlspecialchars`.
- **Criptografia Forte:** Senhas sempre hashadas com `PASSWORD_BCRYPT`.
- **Auditoria Obrigatória:** Toda alteração de estado ou configuração gera um registro imutável com IP, data/hora e identificação do usuário.
