# Timer de Provas
## Objetivos
Crie um site de timer com alertas para provas configurável.O usuário irar precisar cadastrar-se, e terminar o login. Após isso,será direcionado para a pagina principal e terá a opção de configurar o timer,o tempo,se emitira som, barulho ou aviso. Configurar o Timer: Definir o tempo; Definir o alerta; Definir o tempo entre cada alerta; 

### Stack Tecnlógico
- Backend: PHP estruturado com sessões nativas
- Banco de dados: MySQL (PDO para segurança)
- Frontend: HTML5, PHP, CSS, Tailwind CSS

#### Regras de negócio (CORE)
Tratar senhas de usuários com hash bcript
O sistema deve ter uma página de históricos e manter sempre os logs de qualquer alteração feita por 
qualquer usuário, para auditorias futuras.
 até a final, registra o resultado e posições.
  Início do Timer da Prova: Após o login, o coordenador deve definir o tempo total da prova e o tempo para o alerta. Ao iniciar a prova, o sistema deve iniciar automaticamente a contagem do tempo definido.
  Cadastro do Coordenador: O coordenador deve preencher os dados necessários e salvá-los no sistema. Após o cadastro, o sistema deve confirmar o registro e enviar uma mensagem de confirmação por e-mail.
   Login do Coordenador: O coordenador deve informar seu e-mail ou CPF e sua senha para acessar o sistema. Após a validação dos dados, o sistema deve direcioná-lo para o timer da prova.
 


##### Regras Globais
- Use sempre PDO para conexão e queries no MySQL para evitar SQL Injections
- Mantenha o código limpo e comente apenas logicas complexas.
- Separe os arquivos de forma lógica: um arquivo para conexão com a base (bd.php) e scripts de backend isolados e views em HTML5/PHP, nunca faça o sistema como um monolito, deixe sempre separados todas as regras para facilitar os futuros upgrades.
 - Estilize as telas em Tailwind de forma responsiva priorizando o MobileFrist.
 - Retorne sempre as mensagens de erros de forma claras na interface para o usuário (TOAST)
