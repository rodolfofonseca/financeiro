# Instalação

Este projeto usa **variáveis de ambiente** para configuração.

## Desenvolvimento local

1) Crie um arquivo `.env` na raiz do repositório (você pode começar a partir do `.env.example`).

Exemplo:

```env
MONGODB_URI=mongodb://127.0.0.1:27017
MONGODB_DBNAME=controleFinanceiro
```

2) Execute os scripts de atualização na pasta `Versao/` quando necessário.

## Docker (desenvolvimento local)

Este repositório inclui uma configuração Docker que executa:
- PHP + Apache (servindo a raiz deste repositório)
- MongoDB (em um container)

### Início rápido

1) Inicie os containers:

```bash
docker compose up --build
```

2) Abra:
- `http://localhost:8080/`

### Configuração do banco de dados

O app lê as variáveis de ambiente do arquivo `.env` (na raiz do projeto). No Docker, o `docker compose` injeta essas variáveis via `env_file`.

Config padrão (Docker):

```env
MONGODB_URI=mongodb://mongo:27017
MONGODB_DBNAME=controleFinanceiro

# Opcional (apenas se o seu Mongo exigir autenticação)
# MONGODB_USERNAME=
# MONGODB_PASSWORD=
```

Se você quiser usar o MongoDB Atlas/Mongo remoto, atualize o arquivo `.env` com a sua string de conexão.

### Persistência de uploads

Os uploads ficam em `anexos/`. O Docker Compose usa um volume nomeado para `/var/www/html/anexos` para que os arquivos enviados persistam entre reinicializações/rebuilds.

# financeiro
Sistema de controle financeiro para pessoas físicas e jurídicas

## ATUALIZAÇÃO 0.5 LANÇAMENTO 03/06/2026
Correção de alguns bugs de banco de dados
Atualização de visual para carregamento de produtos
Correção do cadastro de notas fiscais

## ATUALIZAÇÃO 0.4 LANÇAMENTO 26/05/2026
Desenvolviemnto de novos realtórios na dashboard
Correção da função de marcar as contas como vencidas quando chega o horário
Adição de filtro para pesquisa e extratos
Correção da função de baixar o extrato para gerar contas automáticas
Adição de relatório em excell nas movimentações, cadastros e contas
Adição na dashboard e nas contas de contas vencidas

## ATUALIZAÇÃO 0.3 LANÇAMENTO 01/05/2026
Correção do módulo de alterar salário do colaborador, para não trocar mais a senha, quando altera o salário
Colocado icone de sino no sistema de notificações.
Desenvolvido sistema para o usuário realizar a troca da senha caso tenha esquecido, através do email... Caso o email exista cadastrado dentro do sistema é realizada a troca da senha.
Realizado a troca das mensagens para quando o sistema não encontra um cep
Adicionado no módulo de contas_pagar_receber sistema para adicioanr cliente/fornecedor as contas
Correção do fechamento contábil, antes quando a empresa fechava no negativo não ficava ( - ) o valor.
No módulo de contas a pagar e receber colocado para ter o fornecedor e os boletos e comprovantes de pagamentos
Validado para não deixar ir para o backend caso o email informado no cadastro seja vazio
Validado o fechamento contábil geral para quando o resultado for negativo ele salvar negativo
Colocado sistema de assinatura do extrato
No sistema de cadastro de clientes/fornecedor colocado campo no filtro para diferenciar o tipo de cadastro, assim como na tabela para diferenciar a pesquisa
Correção do sistema de movimentações, para fazer o cálculo de forma correta
Desenvolvido módulo de cadastro de produtos
Desenvolvido sistema de vincular as contas ao fornecedor, para o módulo contábil do futuro
Alterado sistema de extrato, para quando o mesmo é quitando, cancelado não pode mais ser alterado.
Todos os locais do sistema a palavra "holerite" foi trocado pela palavra extrato
Adicionado na tabela de movimentação linha onde é possível verificar o valor total de movimentações mensal
Adicionado função de mensagem, quando se possui contas vencidas de menses passados.
Adicionado nas movimentações o saldo da conta atual

## ATUALIZAÇÃO 0.2 LANÇAMENTO 01/04/2026

Criação de menu para o cadastro de notas fiscais de entrada e de saída
Desenvolvimento de campo para o usuário cadastrar comprovantes de pagamentos das contas, caso o sistema esteja configurado para receber comprovantes
Desenvolvimento de novos relatórios no dashboard.
