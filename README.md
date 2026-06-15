# Sistema de Unidades de Conservação

Sistema web para exibição de informações sobre Unidades de Conservação (UCs), permitindo consultar detalhes como nome, descrição, data de criação, instituição responsável e municípios abrangidos. O projeto também possibilita o envio de comunicações (sugestões ou reportes de problemas) para as autoridades gestoras.

Desenvolvido para o **Hands On Work VI** do curso de Análise e Desenvolvimento de Sistemas, em parceria com a aluna Geovana Vargas Casarin projeto UNIVALI.

---

## 🛠️ Tecnologias Utilizadas

- **Frontend**: HTML5, CSS3
- **Backend**: PHP 8.4 (com PDO)
- **Banco de Dados**: MySQL
- **Gerenciamento do BD**: phpMyAdmin (opcional)
- **Ambiente Local**: XAMPP / Apache

---

## Acessos Rápidos (ambiente local)

| Acesso | URL |

| **Site principal** | `http://localhost/projeto/index.php` |
| **phpMyAdmin** | `http://localhost/phpmyadmin` |
| **Banco de dados (tabela comunicacao)** | `http://localhost/phpmyadmin/index.php?route=/sql&db=unidades_conservacao&table=comunicacao&pos=0` |

---

## Pré-requisitos

Antes de rodar o projeto, certifique-se de ter instalado:

1. **PHP** (versão 7.4 ou superior – recomendado PHP 8.4)
2. **MySQL** (ou MariaDB)
3. **Servidor Web** (Apache, ou o servidor embutido do PHP)
4. **phpMyAdmin** (opcional, para gerenciar o banco visualmente)

> **Ambiente recomendado:** [XAMPP](https://www.apachefriends.org/) – já inclui Apache, MySQL e phpMyAdmin em um único pacote.

---

## Como Rodar o Projeto

### 1. Clonar o Repositório

No terminal, execute:

```bash
git clone <URL_DO_REPOSITORIO>
cd sistema-unidades-conservacao
2. Configurar o Banco de Dados
Acesse o phpMyAdmin: http://localhost/phpmyadmin

Crie um banco de dados com o nome: unidades_conservacao

Selecione o banco criado

Vá na aba "Importar"

Escolha o arquivo banco.sql (fornecido no projeto)

Clique em "Executar"

As tabelas (instituicao, municipio, unidade_conservacao, unidades_municipio, comunicacao) serão criadas automaticamente, com dados de exemplo já inseridos.

3. Configurar a Conexão com o Banco
Edite o arquivo conexao.php com as credenciais corretas do seu ambiente:

php
<?php
$host = 'localhost';
$usuario = 'root';
$senha = '';        // No XAMPP, geralmente é vazio
$banco = 'unidades_conservacao';

$conexao = mysqli_connect($host, $usuario, $senha, $banco);

if (!$conexao) {
    die('Erro ao conectar ao banco de dados: ' . mysqli_connect_error());
}
?>


4. Rodar o Servidor
Opção A – PHP embutido (recomendado para testes rápidos)
bash
php -S localhost:8000
Acesse: http://localhost:8000/index.php

Opção B – Apache (XAMPP)
Copie a pasta sistema-unidades-conservacao para dentro de htdocs:

Mac (Intel): /Applications/XAMPP/htdocs/

Mac (Apple Silicon): /Applications/XAMPP/xamppfiles/htdocs/

Windows: C:\xampp\htdocs\

Inicie o Apache no XAMPP

Acesse: http://localhost/sistema-unidades-conservacao/index.php

Funcionalidades do Sistema
Funcionalidade	Descrição
Listar UCs	Exibe todas as Unidades de Conservação cadastradas
Ver detalhes	Mostra informações completas: nome, data, instituição, municípios, descrição, imagem
Enviar comunicação	Usuário pode reportar problemas ou enviar sugestões
Comunicações ordenadas	Listagem da mais recente para a mais antiga
Status da comunicação	"Em análise" (0) ou "Analisada" (1)
Solução de Problemas Comuns
Problema	Possível causa e solução
Erro ao conectar ao banco	• MySQL não está rodando → inicie o serviço
• Credenciais incorretas → verifique conexao.php
• Banco não existe → importe o banco.sql
Página em branco	Erros do PHP desabilitados → ative display_errors = On no php.ini
PHP não encontrado	PHP não instalado ou fora do PATH → instale ou adicione ao PATH
Porta já em uso	Outro serviço na mesma porta → use php -S localhost:8080
404 - Página não encontrada	Caminho incorreto → verifique se o projeto está dentro de htdocs/
Imagens não carregam	Verifique se os arquivos estão na pasta assets/ e os caminhos estão corretos
Ativar exibição de erros no PHP
Para debug, adicione no início do arquivo:

php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
