# Instalação
Criar um arquivo chamado "configuracao.ini"
Nele criar a seguinte configuração no banco de dados mongodb

[DB]
db = "NOME DO SEU BANCO DE DADOS"
dns = "DNS DO SEU BANCO DE DADOS";


ex:
[DB]
db = "banco_de_dados_de_exemplo"
dns = "mongodb+srv://MEU_LOGIN:MINHA_SENHA.@alguma_coisa_que_o_mongo_cria_automatico.mongodb.net/sistema?retryWrites=true&w=majority"

Rodar as atualizações da pasta Versão

# financeiro
Sistema de controle financeiro para pessoas físicas e jurídicas

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
