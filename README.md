Controle de visitas e visitantes
---------------------------

## Introdução
O projeto consiste em um sistema de controle de visitas e visitantes, em que é possível 
cadastrar, consultar e modificar dados, além de emitir relatórios.

## Tecnologias
A ideia do projeto foi não utilizar nenhum framework e limitar o uso de bibliotecas externas.
O projeto é composto por um backend em forma de uma API RESTful em PHP e um frontend utilizando ReactJS
criado com o Create React App.

### Bibliotecas utilizadas a se destacar

#### Backend - PHP
- CoffeCode/DataLayer
- Firebase/JWT
- PHPSpreadsheet
- Nyholm/Psr7

#### Frontend - ReactJS
- Axios
- React-Router
- React-Bootstrap
- Fontawesome

### Autenticação
A autenticação é feita utilizando JWT (JSON Web Token). Para evitar ataques de CSRF (Cross-Site Request Forgery) 
foram tomadas algumas medidas:

- Sessões são determinadas por dois tokens. Um access token de curta duração e um refresh token de duração prolongada.
- O access token é armazenado somente em memória sendo enviado no header Authorization de cada requisição.
- O refresh token é armazenado em um cookie HttpOnly.
- Sempre que o access token expirar, o refresh token é utilizado para gerar tanto um novo access token quanto um novo refresh token.
- Caso o mesmo refresh token seja utilizado duas vezes, a sessão é invalidada.

## Instalação
O projeto foi desenvolvido utilizando a versão 8.1 do PHP e o NodeJS na versão 18.5.0.

Para instalar as dependências do projeto, basta executar o comando `npm install` 
na pasta cliente na raiz do projeto e `composer install` na pasta api, também na raiz do projeto.

O projeto utiliza o banco de dados MySQL/MariaDB, portanto é necessário criar um banco de dados.
O arquivo `api/.env.example` contém as variáveis de ambiente necessárias para a conexão com o banco de dados.
Já o arquivo `api/config/database.sql` contém o script para criação das tabelas do banco de dados.

Para o frontend é necessário a execução do comando `npm build` para gerar os arquivos estáticos da SPA que
poderão ser utilizados em um servidor.

Em relação ao servidor, é necessário redirecionar todo o tráfego para os arquivos 
`api/public/index.php` e `client/build/index.html` no backend e frontend, respectivamente.
No backend em especial, o servidor precisa garantir que as variáveis de ambiente que representam queries da url
que foi redirecionada sejam geradas, e.g. 'QUERY_PARAMS'.
