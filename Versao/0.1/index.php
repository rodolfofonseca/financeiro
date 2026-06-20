<?php
ini_set('memory_limit', -1);
ignore_user_abort(true);

define('RAIZ_SISTEMA', '../../');
define('DIRETORIO_VERSAO', str_replace('\\', '/', __DIR__) . '/arquivos');

require_once RAIZ_SISTEMA . 'classes/bancoDeDados.php';
require_once RAIZ_SISTEMA . 'Classes/Sistema/db.php';

router_add('index', function () {
  ignore_user_abort(true);

  $mensagens = (array) [];
  $atualizacao = new Atualizacoes();
  ?>
  <!DOCTYPE html>
  <html>

  <head>
    <title><?php echo $atualizacao->get_titulo(); ?></title>
    <link rel="shortcut icon" href="<?php echo RAIZ_SISTEMA; ?>/assets/images/favicon.png" />
    <script type="text/javascript" src="<?php echo RAIZ_SISTEMA; ?>/dist/js/sistema.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo RAIZ_SISTEMA; ?>/css/estilo.css" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style type="text/css">
      body {
        font-family: Verdana;
      }

      #total_contas {
        width: 500px;
      }

      #contas_calculadas {
        height: 800px;
        width: 500px;
        overflow: auto;
      }

      * {
        font-family: Times New Roman;
      }

      a {
        text-decoration: none;
        color: #009950;
        font-weight: bold;
      }

      .mainColor {
        color: #009950 !important
      }

      .errorColor {
        color: red !important
      }

      .upper {
        text-transform: uppercase;
      }

      .flex-column {
        display: flex !important;
        flex-direction: column !important;
      }

      .flex-evenly {
        display: flex !important;
        justify-content: space-evenly !important;
      }

      .m-bottom-15 {
        margin-bottom: 15px;
      }

      .font-15 {
        font-size: 15px !important
      }

      .font-20 {
        font-size: 20px !important
      }

      .btn {
        height: 40px !important;
      }

      .card {
        width: 800px;
        height: 500px;
        border-radius: 10px;
        overflow: hidden;
        padding: 55px 55px 37px;
        background: #fff;
        margin: 10px 0 10px 0;
      }

      .messageTitle {
        font-size: 20px;
        text-align: center;
        font-weight: bold;
      }

      .messageCenter {
        font-size: 20px;
        text-align: center;
        margin-top: 15px;
        margin-bottom: 15px;
      }

      .container {
        width: 100%;
        min-height: 100%;
        height: auto;
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;
        background: -webkit-linear-gradient(top, #058f50, #1782a6);
      }

      #porcentagem {
        margin-left: 300px;
        margin-right: auto;
      }

      .linha {
        margin-left: auto;
        margin-right: auto;
      }

      .display_none {
        display: none;
      }
    </style>
    <script type="text/javascript">
      var MENSAGENS = [];
      function exibir_percentual_carregamento(quantidade, total) {
        var percentual = parseInt((quantidade * 100) / total, 10);
        document.querySelector('#total_contas').innerHTML = 'Processando ' + percentual + '%';
      }

      function exibir_mensagem(mensagem) {
        MENSAGENS.push(mensagem);

        if (MENSAGENS.length > 30) {
          MENSAGENS.splice(0, 1);
        }

        document.querySelector('#contas_calculadas').innerHTML = MENSAGENS.join('<br>');
      }

      window.onload = (function () {
        var mensagens = JSON.parse(document.querySelector('#mensagens').value);

        if (mensagens.length > 0) {
          alert(mensagens.join('\r\n'));
        }
        window.location.href = sistema.url('/index.php', { 'rota': 'exibir_notas' });
      });
    </script>
  </head>

  <body>
    <div class="container">
      <div class="card">
        <div class="row">
          <div class="col-12">
            <div id="calculos">
              <div id="total_contas">
              </div>
              <br />
              <div id="contas_calculadas">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php
    $atualizacao->exibir_mensagem('Começando a atualização do sistema.');

    $atualizacao->executar_atualizacao();

    $retorno = (array) model_all('empresa', []);

    if (empty($retorno) == false) {
      foreach ($retorno as $empresa) {
        // model_update((string) 'sistema', (array) [] ,(array) ['versao_sistema' => (string) ' alfa 0.1']);
        model_insert('sistema', ['empresa' => $empresa['_id'], 'versao_sistema' => 'alfa 0.1']);
      }
    }
    // header("Location: index.php?rota=exibir_notas");
  
    ?>
    <input type="hidden" id="mensagens" value='<?php echo json_encode($mensagens); ?>' />
  </body>
  <?php

  exit;
});

router_add('exibir_notas', function () {
  $atualizacao = new Atualizacoes();
  ?>
  <title><?php echo $atualizacao->get_titulo(); ?></title>
  <meta charset="UTF-8">
  <title><?php echo $atualizacao->get_titulo(); ?></title>
  <link rel="icon" type="image/png" sizes="16x16" href="imagens/icone_sistema.ico">
  <script type="text/javascript"
    src="<?php echo RAIZ_SISTEMA; ?>/dist/js/sistema.js?v=<?php echo filemtime('dist/js/sistema.js'); ?>"></script>
  <link rel="stylesheet" type="text/css" href="css/estilo.css?v=<?php echo filemtime('css/estilo.css'); ?>" />
  <style>
    * {
      font-family: Times New Roman;
    }

    a {
      text-decoration: none;
      color: #009950;
      font-weight: bold;
    }

    .mainColor {
      color: #009950 !important
    }

    .errorColor {
      color: red !important
    }

    .upper {
      text-transform: uppercase;
    }

    .flex-column {
      display: flex !important;
      flex-direction: column !important;
    }

    .flex-evenly {
      display: flex !important;
      justify-content: space-evenly !important;
    }

    .m-bottom-15 {
      margin-bottom: 15px;
    }

    .font-15 {
      font-size: 15px !important
    }

    .font-20 {
      font-size: 20px !important
    }

    .btn {
      height: 40px !important;
    }

    .card {
      width: 800px;
      border-radius: 10px;
      overflow: hidden;
      padding: 55px 55px 37px;
      background: #fff;
      margin: 10px 0 10px 0;
    }

    .messageTitle {
      font-size: 20px;
      text-align: center;
      font-weight: bold;
    }

    .messageCenter {
      font-size: 20px;
      text-align: center;
      margin-top: 15px;
      margin-bottom: 15px;
    }

    .container {
      width: 100%;
      min-height: 100%;
      height: auto;
      display: flex;
      flex-direction: row;
      justify-content: center;
      align-items: center;
      background: -webkit-linear-gradient(top, #058f50, #1782a6);
    }

    #porcentagem {
      margin-left: 300px;
      margin-right: auto;
    }

    .linha {
      margin-left: auto;
      margin-right: auto;
    }

    .display_none {
      display: none;
    }
  </style>
  <div class="container" id="notas_atualizacao">
    <div class="card">
      <div class="row">
        <div class="col-12 text-justify">
          <p>
          <div class="messageTitle">NOTAS da versão <?php echo $atualizacao->get_titulo(); ?></div>
          </p>
          <p><?php echo $atualizacao->notas(); ?></p>
          <p class="messageCenter">Atualização <b><?php echo $atualizacao->get_titulo(); ?></b> realizada com
            <b>sucesso</b>!</p>
          <br />
          <b><a class="btn col-12 upper" href="<?php echo RAIZ_SISTEMA; ?>">TELA PRINCIPAL</a></b>
        </div>
      </div>
    </div>
  </div>
  <?php
  exit;
});

class Atualizacoes
{
  private $versao;
  private $versao_atualizacao;
  private $nome_banco;
  private $dns;
  private $dns_password;
  private $titulo_atualizacao;
  private $usa_banco_rotina_pesada;
  private $cliente_banco_dados;
  private $diretorio_log;
  private $caminho_log;
  private $arquivo_atualizacao;
  private $settings = ['dns' => 'mongodb://127.0.0.1/', 'authentication' => [], 'options' => ['typeMap' => ['array' => 'array', 'document' => 'array', 'root' => 'array']]];

  function __construct()
  {
    $this->versao = (string) basename(dirname(__FILE__));
    $this->versao_atualizacao = (string) '0.1';
    $this->nome_banco = (string) 'documentos';
    $this->dns = (string) 'mongodb://127.0.0.1';
    $this->titulo_atualizacao = (string) 'ATUALIZAÇÃO VERSÃO V0.1';
    $this->arquivo_atualizacao = (string) 'Versao/0.1/index.php';
  }

  public function get_titulo()
  {
    return (string) $this->titulo_atualizacao;
  }

  /**
   * Função responsável por carregas a configuração do banco de dados.
   */
  private function carregar_configuracao()
  {
    $this->dns = (string) (getenv('MONGODB_URI') ?: $this->settings['dns']);
    $this->nome_banco = (string) (getenv('MONGODB_DBNAME') ?: $this->nome_banco);
    $this->dns_password = $this->dns;

    $authentication = $this->settings['authentication'];

    $this->cliente_banco_dados = (new MongoDB\Client($this->dns, $authentication));
  }

  /**
   * Retorna o DNS do banco de dados
   * @return string dns
   */
  public function get_dns()
  {
    $this->carregar_configuracao();
    return $this->dns_password;
  }

  /**
   * Retorna o nome do banco de dados.
   * @return string nome_banco
   */
  public function get_nome_banco()
  {
    $this->carregar_configuracao();
    return $this->nome_banco;
  }

  /**
   * Função responsável por criar o cliente do banco de dados
   * @return object do banco de dados
   */
  private function criar_cliente_banco_dados()
  {
    $this->carregar_configuracao();

    return $this->cliente_banco_dados->selectDatabase($this->nome_banco);
  }

  /**
   * Função responsável por copiar os arquivos que estão dentro da pasta de arquivos e adicionar os mesmos nos devidos locais.
   * @param string $sub_diretorio
   */
  private function copiar_arquivos($sub_diretorio = '')
  {
    $diretorio = scandir(DIRETORIO_VERSAO . $sub_diretorio);

    foreach ($diretorio as $nome) {
      if ($nome != '.' and $nome != '..') {
        $origem = DIRETORIO_VERSAO . "$sub_diretorio/$nome";
        $destino = RAIZ_SISTEMA . "$sub_diretorio/$nome";

        if (is_dir($origem) == false) {
          copy($origem, $destino);
        } else {

          if (is_dir($destino) == false) {
            mkdir($destino);
          }

          $this->copiar_arquivos("$sub_diretorio/$nome");
        }
        $this->exibir_mensagem($sub_diretorio . '/' . $nome . ' foi copiado!');
      }
    }

    return true;
  }

  /**
   * Função responsável por guardar na rotina de gerar log as mesagens de falha que apresenta.
   */
  private function execoes($ex)
  {
    echo $ex->getMessage();
  }

  /**
   * Função responsável por ativar ou desativar a validação da tabela do banco de dados.
   * @param string $tabela nome da tabela que de ser mexida
   * @param string $tipo [off/strict] 
   */
  public function ativar_desativar_validacoes($tabela, $tipo)
  {
    try {
      $collection = $this->criar_cliente_banco_dados();
      $collection->command(['collMod' => $tabela, 'validationAction' => 'error', 'validationLevel' => $tipo]);

      $this->exibir_mensagem('Alterou o nível de validação da tabela ' . $tabela . ' para ' . strtoupper($tipo) . '.');
    } catch (Exception $ex) {
      $this->execoes($ex);
    }
  }

  /**
   * Função responsável por criar o backup do banco de dados.
   * @param string $tabela nome da tabela que será criado o backup
   */
  public function criar_backup($tabela)
  {
    date_default_timezone_set('America/Sao_Paulo');
    $data_atual = (string) date('d_m_Y_H_i_s');

    $lista = (array) model_all($tabela);

    return (bool) file_put_contents('backup/' . $data_atual . '_' . $tabela . '.json', json_encode($lista));
  }

  /**
   * Função responsável por organizar as notas de atualização para que seja escrito na páginas após a atualização ser efetuada com sucesso
   */
  public function notas()
  {
    $notas = (string) file_get_contents('notas_atualizacao.txt');
    $notas = (string) str_replace("\n", '<br/>', $notas);
    $notas = (string) str_replace('{versao}', $this->versao_atualizacao, $notas);

    return $notas;
  }

  /**
   * Função responsável por alterar a validacao da tabela do banco de dados.
   */
  public function alterar_validacao($tabela, $modelo)
  {
    try {
      $data_base = $this->criar_cliente_banco_dados();
      //   $data_base->command(['collMod' => $tabela, 'validationAction' => 'error', 'validationLevel' => 'strict', 'validator' => (array) model_validator($modelo)]);

      $this->exibir_mensagem('Alterou a validação da tabela ' . $tabela . ' de acordo com a Modelos.');
    } catch (Exception $ex) {
      $this->execoes($ex);
    }
  }

  /**
   * Função responsável por criar novas tabela no banco de dados
   * @param string tabela nome que será a tabela
   * @param array modelo que deverá ter a tabela
   */
  function criar_tabela_banco_dados($tabela, $modelo)
  {
    try {
      $this->exibir_mensagem("Preparando para a criação da tabela " . $tabela);

      $data_base = $this->criar_cliente_banco_dados();
      $this->exibir_mensagem("Tabela " . $tabela . " preparando para adicionar as validações");

      //   $data_base->createCollection($tabela, ['validationAction' => 'error', 'validationLevel' => 'strict', 'validator' => (array) model_validator($modelo)]);
      $this->exibir_mensagem("Validações adicionadas com sucesso!");
    } catch (Exception $ex) {
      $this->execoes($ex);
    }
  }

  /**
   * Função responsável por apresentar a mensagem do que está sendo apresentado.
   */
  public function exibir_mensagem($mensagem)
  {
    ?>
    <script type="text/javascript">
      exibir_mensagem(<?= "'$mensagem'" ?>);
    </script>
    <?php
    flush();
    ob_flush();
  }

  /**
   * Função que exibe o percentual de carregamento.
   */
  function exibir_percentual_carregamento($quantidade, $total)
  {
    ?>
    <script type="text/javascript">
      exibir_percentual_carregamento(<?= $quantidade ?>, <?= $total ?>);
    </script>
    <?php
    flush();
    ob_flush();
  }
  public function executar_atualizacao()
  {
    $this->copiar_arquivos();
  }
}