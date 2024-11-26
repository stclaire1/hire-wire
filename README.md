# hire wire
It's for the best developers walking the "high wire" to prove their skills in a hiring challenge!

# Especificação Técnica: Sistema de Contas Bancárias para code teste

Este sistema permitirá aos usuários criar e gerenciar três tipos de contas bancárias: Conta Poupança, Conta Corrente e Conta Investimentos. A aplicação será composta por um backend desenvolvido em Laravel e um frontend em Vue.js, com autenticação via Laravel Passport.

Funcionalidades Gerais

# 1.	Cadastro de Usuário
o	O sistema permitirá que os usuários se cadastrem fornecendo:
	Nome
	CPF (único)
	E-mail (único)
	Senha
o	Validação para garantir que não existam CPFs ou e-mails duplicados.
# 2.	Tipos de Contas
o	Conta Poupança
o	Conta Corrente
o	Conta Investimentos
# 3.	Operações Disponíveis
o	Depósito: Adicionar valores ao saldo de uma conta específica.
o	Consulta de Saldo: Consultar o saldo atual de qualquer conta do usuário.
o	Correção Monetária: Aplicada mensalmente conforme as regras de cada tipo de conta.
4.	Autenticação e Controle de Acesso
o	Apenas usuários autenticados podem acessar as operações de suas contas.
o	Autenticação será feita via Laravel Passport com tokens de acesso OAuth2.

Regras de Negócio
# 1.	Conta Poupança
o	Depósito:
	O valor depositado é adicionado diretamente ao saldo da conta.
o	Correção Monetária:
	0,001% do saldo é aplicado no final de cada mês.
# 2.	Conta Corrente
o	Depósito:
	O valor depositado é incrementado de R$0,50 antes de ser adicionado ao saldo.
	Exemplo: Depósito de R$100,00 → R$100,50 adicionados ao saldo.
o	Correção Monetária:
	0,1% do saldo é aplicado no final de cada mês.
# 3.	Conta Investimentos
o	Depósito:
	O valor depositado é incrementado de R$0,50 antes de ser adicionado ao saldo.
	Exemplo: Depósito de R$200,00 → R$200,50 adicionados ao saldo.
o	Correção Monetária:
	0,1% do saldo é aplicado no final de cada mês.
