<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class Sistema implements InterfaceModelo
{
    private mixed $codigo_sistema;
    private mixed $codigo_empresa;
    private mixed $cliente_padrao;
    private mixed $fornecedor_padrao;
    private string $versao_sistema;
    private string $anexa_documentos;
    private bool $modulo_contabil;
    private bool $pedidos;
    private bool $cloudinary;
    private bool $google_agenda;
    private string $endereco_json_google;
    private string $conta_capital_social;
    private string $conta_lucros_apropriar;
    private string $conta_prejuizos_acumulados;
    private string $conta_vendas_a_vista;
    private string $conta_vendas_a_prazo;
    private string $conta_servicos_a_vista;
    private string $conta_servicos_a_prazo;
    private string $conta_custo_mercadorias_vendas;
    private string $conta_custo_servicos_prestados;
    private string $conta_apuracao_resultado;
    private string $versao_sistema_java;

    public function tabela()
    {
        return (string) 'sistema';
    }

    public function modelo()
    {
        return (array) [];
    }
    /**
     * Função responsável por colcoar os dados nas variáveis
     * @param array $dados
     * @return void
     */
    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_sistema', $dados) == true) {
            if ($dados['codigo_sistema'] != 0) {
                $this->codigo_sistema = (int) intval($dados['codigo_sistema'], 10);
            } else {
                $this->codigo_sistema = 0;
            }
        } else {
            $this->codigo_sistema = 0;
        }

        $this->empresa = (int) (isset($dados['empresa']) ? (int) intval($dados['empresa'], 10) : 0);
        $this->cliente_padrao = (int) (isset($dados['cliente_padrao']) ? $dados['cliente_padrao'] : 0);
        $this->fornecedor_padrao = (int) (isset($dados['fornecedor_padrao']) ? $dados['fornecedor_padrao'] : 0);

        $this->versao_sistema = (string) (isset($dados['versao_sistema']) ? (string) $dados['versao_sistema'] : 'alfa 0.0');
        $this->anexa_documentos = (string) (isset($dados['anexa_documentos']) ? (string) $dados['anexa_documentos'] : 'NAO');

        $this->modulo_contabil = (bool) (isset($dados['modulo_contabil']) ? (bool) filter_var($dados['modulo_contabil'], FILTER_VALIDATE_BOOLEAN) : false);
        $this->pedidos = (bool) (isset($dados['pedidos']) ? (bool) filter_var($dados['pedidos'], FILTER_VALIDATE_BOOLEAN) : false);
        $this->cloudinary = (bool) (isset($dados['cloudinary']) ? (bool) filter_var($dados['cloudinary'], FILTER_VALIDATE_BOOLEAN) : false);
        $this->google_agenda = (bool) (isset($dados['google_agenda']) ? (bool) filter_var($dados['google_agenda'], FILTER_VALIDATE_BOOLEAN) : false);

        $this->endereco_json_google = (string) (isset($dados['endereco_json_google']) ? (string) $dados['endereco_json_google'] : '');

        $this->conta_capital_social = (string) (isset($dados['conta_capital_social']) ? (string) $dados['conta_capital_social'] : '');
        $this->conta_lucros_apropriar = (string) (isset($dados['conta_lucros_apropriar']) ? (string) $dados['conta_lucros_apropriar'] : '');
        $this->conta_prejuizos_acumulados = (string) (isset($dados['conta_prejuizos_acumulados']) ? (string) $dados['conta_prejuizos_acumulados'] : '');
        $this->conta_vendas_a_vista = (string) (isset($dados['conta_vendas_a_vista']) ? (string) $dados['conta_vendas_a_vista'] : '');
        $this->conta_vendas_a_prazo = (string) (isset($dados['conta_vendas_a_prazo']) ? (string) $dados['conta_vendas_a_prazo'] : '');
        $this->conta_servicos_a_vista = (string) (isset($dados['conta_servicos_a_vista']) ? (string) $dados['conta_servicos_a_vista'] : '');
        $this->conta_servicos_a_prazo = (string) (isset($dados['conta_servicos_a_prazo']) ? (string) $dados['conta_servicos_a_prazo'] : '');
        $this->conta_custo_mercadorias_vendas = (string) (isset($dados['conta_custo_mercadorias_vendidas']) ? (string) $dados['conta_custo_mercadorias_vendidas'] : '');
        $this->conta_custo_servicos_prestados = (string) (isset($dados['conta_custo_servicos_prestados']) ? (string) $dados['conta_custo_servicos_prestados'] : '');
        $this->conta_apuracao_resultado = (string) (isset($dados['conta_apuracao_resultado']) ? (string) $dados['conta_apuracao_resultado'] : '');
        $this->versao_sistema_java = (string) (isset($dados['versao_sistema_java']) ? (string) $dados['versao_sistema_java'] : '');
    }

    /**
     * Função responsável por salvar os dados no banco de dados
     * @param array $dados
     * @return bool
     */
    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        // file_put_contents('jsssss.json', json_encode(['teste' => $dados, 'codigo' => $this->codigo_sistema]));
        
        if($this->codigo_sistema == 0){
            $dados_empresa = $this->montar_array();
            $dados_empresa['codigo_empresa'] = $this->empresa;
            $dados_empresa['versao_sistema'] = 'alfa 0.0';
            return (bool) model_insert((string) $this->tabela(), (array) $dados_empresa);
        }else{
            return (bool) model_update((string) $this->tabela(), ['where' => [['codigo_sistema', '==', $this->codigo_sistema]]], (array) $this->montar_array());
        }
    }

    /**
     * Função responsável por pesquisar um registro no banco de dados
     * @param array $filtro
     * @return array
     */
    public function pesquisar($filtro)
    {
        return (array) model_one((string) $this->tabela(), (array) $filtro['filtro']);
    }

    /**
     * Função responsável por pesquisar todos os registros no banco de dados
     * @param array $filtro
     * @return array
     */
    public function pesquisar_todos($filtro)
    {
        // return (array) model_all((string) $this->tabela(), (array) $filtro['filtro'], (array) $filtro['odenacao'], (int) $filtro['limite']);

        return (array) [];
    }

    /**
     * Função responsável por salvar no banco de dados a configuração da integração com o google agenda e copiar os arquivos para a pasta do sistema
     * @param mixed $dados
     * @param mixed $files
     * @return array
     */
    public function salvar_dados_google_agenda($dados, $files)
    {
        // $diretorio = __DIR__ . '/../anexos/google_agenda/';

        // if (!is_dir($diretorio)) {
        //     @mkdir($diretorio, 0777, true);
        // }
        // if (isset($files['json_google']) && $files['json_google']['error'] === UPLOAD_ERR_OK) {

        //     $arquivoTmp = $files['json_google']['tmp_name'];

        //     $nomeOriginal = $files['json_google']['name'];

        //     $extensao = pathinfo($nomeOriginal, PATHINFO_EXTENSION);

        //     $novoNome = uniqid('google_agenda_') . '.' . $extensao;

        //     $destino = $diretorio . $novoNome;

        //     if (move_uploaded_file($arquivoTmp, $destino)) {
        //         $retorno_configuracao_empresa = (array) $this->pesquisar((array) ['filtro' => ['empresa', '===', model_id($dados['empresa'])]]);
        //         $retorno = (bool) false;

        //         if (empty($retorno_configuracao_empresa) == false) {
        //             $retorno_configuracao_empresa['endereco_json_google'] = $novoNome;
        //             $retorno_configuracao_empresa['codigo_sistema'] = $retorno_configuracao_empresa['_id'];
        //             $retorno = $this->salvar_dados($retorno_configuracao_empresa);
        //         }

        //         return [
        //             'sucesso' => $retorno,
        //             'arquivo' => $novoNome,
        //             'caminho' => $destino
        //         ];
        //     } else {

        //         return [
        //             'sucesso' => false,
        //             'erro' => 'Erro ao mover arquivo'
        //         ];
        //     }
        // }

        return [
            'sucesso' => false,
            'erro' => 'Nenhum arquivo enviado'
        ];
    }

    public function montar_array(){
        $dados = (array) [];

        // if($this->codigo_empresa != 0){
        //     $dados['codigo_empresa'] = (int) $this->codigo_empresa;
        // }

        // if($this->versao_sistema != ''){
        //     $dados['versao_sistema'] = (string) $this->versao_sistema;
        // }

        // if($this->anexa_documentos != ''){
        //     $dados['anexa_documentos'] = (string) $this->anexa_documentos;
        // }

        // if($this->endereco_json_google != ''){
        //     $dados['endereco_json_google'] = (string) $this->endereco_json_google;
        // }

        // if($this->conta_capital_social != ''){
        //     $dados['conta_capital_social'] = (string) $this->conta_capital_social;
        // }

        // if($this->conta_custo_mercadorias_vendas != ''){
        //     $dados['conta_custo_mercadorias_vendidas'] = (string) $this->conta_custo_mercadorias_vendas;
        // }

        // if($this->conta_custo_servicos_prestados != ''){
        //     $dados['conta_custo_servicos_prestados'] = (string) $this->conta_custo_servicos_prestados;
        // }

        // if($this->conta_lucros_apropriar != ''){
        //     $dados['conta_lucros_apropriar'] = (string) $this->conta_lucros_apropriar;
        // }

        // if($this->conta_prejuizos_acumulados != ''){
        //     $dados['conta_prejuizos_acumulados'] = (string) $this->conta_prejuizos_acumulados;
        // }

        // if($this->conta_vendas_a_vista != ''){
        //     $dados['conta_vendas_a_vista'] = (string) $this->conta_vendas_a_vista;
        // }

        // if($this->conta_vendas_a_prazo != ''){
        //     $dados['conta_vendas_a_prazo'] = (string) $this->conta_vendas_a_prazo;
        // }

        // if($this->conta_servicos_a_vista != ''){
        //     $dados['conta_servicos_a_vista'] = (string) $this->conta_servicos_a_vista;
        // }

        // if($this->conta_servicos_a_prazo != ''){
        //     $dados['conta_servicos_a_prazo'] = (string) $this->conta_servicos_a_prazo;
        // }

        // if($this->conta_apuracao_resultado != ''){
        //     $dados['conta_apuracao_resultado'] = (string) $this->conta_apuracao_resultado;
        // }

        // if($this->versao_sistema_java != ''){
        //     $dados['versao_sistema_java'] = (string) $this->versao_sistema_java;
        // }

        $dados['modulo_contabil'] = (bool) $this->modulo_contabil;
        $dados['pedidos'] = (bool) $this->pedidos;
        $dados['cloudinary'] = (bool) $this->cloudinary;
        $dados['google_agenda'] = (bool) $this->google_agenda;
        $dados['cliente_padrao'] = (int) $this->cliente_padrao;
        $dados['fornecedor_padrao'] = (int) $this->fornecedor_padrao;
        
        return (array) $dados;
    }
}
?>