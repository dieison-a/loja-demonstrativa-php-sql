# Loja demonstrativa com PHP e SQL

Uma pequena loja local para praticar formulários, sessão, validação no servidor e modelagem relacional. A interface funciona sem banco de dados; `database/esquema.sql` documenta uma estrutura MySQL opcional, independente da demonstração.

O projeto foi escrito com assistência de IA a partir dos temas de Banco de Dados e Programação para Web II. A implementação não reaproveita credenciais, cadastros, imagens ou trechos dos arquivos de aula. Não existe login nem coleta de dados pessoais.

## Funcionalidades

- Catálogo de produtos fictícios.
- Carrinho mantido apenas na sessão local do navegador.
- Validação de produto e quantidade no servidor.
- Token contra requisições cruzadas nos formulários.
- Valores monetários calculados no PHP.
- Esquema SQL com produtos, pedidos e itens, sem contas de usuário.

## Executar

Com PHP 8.1 ou superior:

```powershell
php -S 127.0.0.1:8000
```

Abra `http://127.0.0.1:8000`. O servidor fica restrito à própria máquina. Para encerrar, use `Ctrl+C`.

O arquivo SQL é um exemplo didático e não é carregado pelo aplicativo. Não use dados reais neste protótipo.
