# Manual Técnico detalhado - Projeto Integrador

Este manual descreve a arquitetura, os padrões de projeto e o funcionamento de um conjunto de algoritmos em PHP, ao qual podemos chamar de framework.

---

## Capítulo 1: Sobre o MVC e o Fluxo de Requisições

O projeto segue o padrão **MVC (Model-View-Controller)**. O motor central desse fluxo é o arquivo `app/core/Router.php`.

Em uma aplicação MVC, a requisição do usuário deve ser sempre direcionada ao Controller, que por sua vez interage com o Model e outras classes auxiliares, retornando a View.

O Controller é o "cérebro" da aplicação, responsável por receber as requisições, processá-las e retornar as respostas.

O Model é a representação dos dados da aplicação. Você deve, sempre que possível, evitar o uso de arrays para representar os dados vindos do banco de dados: prefira utilizar objetos da camada Model.

A camada View é responsável por apresentar os dados ao usuário. A View interage com o Controller (que envia e recebe dados da camada View) e apresenta os dados para o usuário. A View não deve interagir diretamente com o banco de dados.

Em uma arquitetura MVC, no sistema ainda podem existir outras camadas, tais como as camadas Service, Repository, Helper, etc.
O objetivo é separar as responsabilidades de forma que cada camada tenha uma única função bem definida. Você pode, inclusive, criar novas camadas se achar necessário.

---

## Capítulo 2: Sistema de Roteamento

Para garantir que apenas os controllers sejam acessados pela web, o sistema utiliza um algoritmo de roteamento centralizado. A classe `Router`, encontrada no arquivo `app/core/Router.php`, é responsável por isso.

A lógica de roteamento é invocada no arquivo `public/index.php`, que atua como o ponto de entrada único (Front Controller). Nesse arquivo, é criado um objeto da classe `Router` e as rotas da aplicação são registradas. Após isso, o método `run()` é executado.

É importante destacar que apenas as rotas informadas em `public/index.php` estarão disponíveis. Se uma rota solicitada não estiver na lista, o sistema retornará um erro 404.

O método `run()` captura a URL solicitada (URI) e o método HTTP (GET, POST, etc.) para encontrar a rota correspondente.

Veja o método `run()`:

```php
public function run()
{
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    foreach ($this->routes as $route) {
        if ($method === $route['method'] && $uri === $route['route']) {
            return $this->dispatch($route['action']);
        }
    }
}
```

Quando uma rota coincide, o `dispatch` instancia o controller dinamicamente. Isso é relevante! Perceba que em nenhum momento fazemos um comando `new` para criação de uma Controller: essa lógica é realizada dinamicamente pelo método `dispatch`.

```php
private function dispatch(string $action)
{
    list($controllerName, $method) = explode('@', $action);
    $controllerClass = "app\\controllers\\$controllerName";
    $controller = new $controllerClass();
    return $controller->$method();
}
```

### Registro de Rotas (`public/index.php`)
As rotas são amigáveis e podem conter parâmetros:
```php
$router->get('/usuarios', 'UsuarioController@index');
$router->post('/login/submit', 'AutenticacaoController@logar');
```

---

## Capítulo 3: Namespaces e o Autoloading de Classes

Para compreender o uso de `namespaces` e `autoloading`, é importante, primeiro, entender quais problemas essas abordagens resolvem.

O primeiro deles está relacionado à inclusão manual de arquivos. Sem o uso de `namespaces` e `autoloading`, seria necessário incluir cada arquivo PHP manualmente. À medida que o sistema cresce, esse processo se torna complexo e propenso a erros.

Outro problema comum é o conflito de nomes. Em sistemas maiores, diferentes partes da aplicação podem utilizar classes com o mesmo nome. Os `namespaces` permitem agrupar classes em "espaços lógicos" diferentes, como `app\core\Database` e `app\models\Database`.

No projeto, o `autoloader` é definido em `app/core/Autoload.php` e registrado via `spl_autoload_register`. Sempre que o sistema tenta utilizar uma classe ainda não carregada, o `autoloader` é chamado para localizá-la baseando-se no namespace.

```php
spl_autoload_register(function ($class) {
    $prefix = 'app\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;

    $relativeClass = substr($class, strlen($prefix));
    $file = __DIR__ . '/../' . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) require_once $file;
});
```

---

## Capítulo 4: Conexão e Inicialização do Banco de Dados

A gestão da conexão com o banco de dados é realizada de forma centralizada pela classe `app\database\ConnectionFactory`. Esta classe utiliza uma estratégia de **Lazy Initialization** para garantir eficiência.

### A Classe ConnectionFactory

Nosso framework é resiliente: se a conexão com o banco de dados específico falhar (por exemplo, no primeiro acesso onde o banco ainda não existe), o sistema tenta inicializá-lo automaticamente:

1.  **Conexão Primária:** Tenta conectar usando as configurações de `Config.php`.
2.  **Fallback de Inicialização:** Se falhar, conecta ao servidor MySQL e invoca `DatabaseInitializer`.
3.  **Criação Automática:** O `DatabaseInitializer` cria o banco e executa o `script.sql`.

```php
public static function getConnection() {
    if (self::$connection == null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
            self::$connection = self::createConnection($dsn);
        } catch(Exception $e) {
            // Inicialização automática do banco de dados
            $dsn = "mysql:host=" . DB_HOST;
            $connectionTemp = self::createConnection($dsn);
            
            $databaseInit = new DatabaseInitializer();
            $databaseInit->init($connectionTemp);

            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
            self::$connection = self::createConnection($dsn);
        }
    }
    return self::$connection;
}
```

---

## Capítulo 5: As Camadas Model, Repository e Service

Além do MVC básico, estruturamos a aplicação em camadas suplementares para melhor organização:

### 1. Model (Entidades)
Os Models (ex: `app\models\Usuario.php`) são objetos puros que representam os dados. Eles possuem o método `arrayParaObjeto()` para facilitar a hidratação (preenchimento do objeto) a partir de dados vindos do banco.

### 2. Repository (Acesso a Dados)
Os Repositories (ex: `app\repositories\UsuarioRepository.php`) isolam o SQL. Utilizamos `prepare` e `bindValue` em todas as consultas para prevenir ataques de **SQL Injection**.

### 3. Service (Lógica de Negócios)
Os Services (ex: `app\services\UsuarioService.php`) concentram as regras de negócio. Eles coordenam as ações entre Repositories e garantem a integridade dos dados antes da persistência.

---

## Capítulo 6: Sistema de Validações

As validações ocorrem em dois níveis:

1.  **Validações Estruturais (Entradas de formulários):** Usam a classe `app\helpers\Validador.php` para checar campos obrigatórios no Controller.
2.  **Validações de Negócio:** Realizadas nos **Services** para checar regras como "e-mail já cadastrado".

### Exemplo de uso de validações de entradas de formulários:
```php
$validador = new Validador();
$validador->obrigatorio('email', $email)
          ->obrigatorio('senha', $senha);

if ($validador->temErros()) {
    return $this->view('login', ['erros' => $validador->getErros()]);
}
```

---

## Capítulo 7: Autenticação, Sessão e Perfis (Roles)

Nosso framework gerencia a segurança de forma centralizada, utilizando sessões do PHP para persistir a identidade do usuário e métodos utilitários na Controller base para restringir o acesso.

### 1. Gestão de Sessão e Identidade
Quando o `AutenticacaoService` valida as credenciais de um usuário, ele armazena o objeto `Usuario` (Model) diretamente na superglobal `$_SESSION`:

```php
$_SESSION['usuario_logado'] = $usuario;
```

O uso de um objeto completo em vez de apenas o ID permite que qualquer parte do sistema acesse o nome, e-mail e perfil do usuário sem a necessidade de novas consultas ao banco de dados.

### 2. Métodos de Verificação na Controller Base
A classe `app\core\Controller.php` fornece métodos que automatizam o controle de acesso. Eles devem ser chamados no início das ações que exigem proteção.

#### `autenticacaoRequired()`
Verifica se existe um usuário logado. Se não houver, o sistema redireciona automaticamente para a página de login.

```php
public function autenticacaoRequired() {
    if (!isset($_SESSION['usuario_logado'])) {
        $this->redirect(URL_BASE . '/login');
    }
    return true;
}
```

#### `adminRequired()`
Além de verificar se o usuário está logado, este método valida se ele possui o perfil de administrador (`admin`). É fundamental para proteger áreas restritas de gestão.

```php
public function adminRequired() {
    if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado']->getPerfil() !== 'admin') {
        $this->redirect(URL_BASE . '/login');
    }
    return true;
}
```

### 3. Exemplo Prático de Uso
Veja como aplicar essas verificações em uma Controller específica:

```php
namespace app\controllers;
use app\core\Controller;

class HumoristaController extends Controller {

    public function list() {
        // Apenas usuários logados podem ver a lista
        $this->autenticacaoRequired();
        // ... lógica de listagem
    }

    public function delete() {
        // Apenas administradores podem excluir humoristas
        $this->adminRequired();
        // ... lógica de exclusao
    }
}
```
