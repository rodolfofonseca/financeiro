<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class Sistema implements InterfaceModelo{
    private mixed $codigo_sistema;
    private mixed $empresa;
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

    public function tabela()
    {
        return (string) 'sistema';
    }

    public function modelo(){
        return (array) ['empresa' => 'objectId', 'versao_sistema' => (string) '', 'anexa_documentos' => (string) '', 'modulo_contabil' => 'bool', 'pedidos' => 'bool', 'cloudinary' => 'bool', 'google_agenda' => 'bool', 'endereco_json_google' => (string) '', 'conta_capital_social' => (string) '', 'conta_lucros_apropriar' => (string) '', 'conta_prejuizos_acumulados' => (string) '', 'conta_vendas_a_vista' => (string) '', 'conta_vendas_a_prazo' => (string) '', 'conta_servicos_a_vista' => (string) '', 'conta_servicos_a_prazo' => (string) '', 'conta_custo_mercadorias_vendidas' => (string) '', 'conta_custo_servicos_prestados' => (string) '', 'conta_apuracao_resultado' => (string) '', 'cliente_padrao' => 'objectId', 'fornecedor_padrao' => 'objectId'];
    }
    /**
     * Função responsável por colcoar os dados nas variáveis
     * @param array $dados
     * @return void
     */
    public function colocar_dados($dados)
    {
        if(array_key_exists('codigo_sistema', $dados) == true){
            if($dados['codigo_sistema'] != ''){
                $this->codigo_sistema = model_id($dados['codigo_sistema']);
            }else{
                $this->codigo_sistema = null;
            }
        }else{
            $this->codigo_sistema = null;
        }

        $this->empresa = (isset($dados['empresa']) ? model_id($dados['empresa']):'');
        $this->cliente_padrao = (isset($dados['cliente_padrao']) ? model_id($dados['cliente_padrao']):'');
        $this->fornecedor_padrao = (isset($dados['fornecedor_padrao']) ? model_id($dados['fornecedor_padrao']):'');

        $this->versao_sistema = (string) (isset($dados['versao_sistema']) ? (string) $dados['versao_sistema']:'alfa 0.0');
        $this->anexa_documentos = (string) (isset($dados['anexa_documentos']) ? (string) $dados['anexa_documentos']:'NAO');
        
        $this->modulo_contabil = (bool) (isset($dados['modulo_contabil']) ? (bool) filter_var($dados['modulo_contabil'], FILTER_VALIDATE_BOOLEAN):false);
        $this->pedidos = (bool) (isset($dados['pedidos']) ? (bool) filter_var($dados['pedidos'], FILTER_VALIDATE_BOOLEAN):false);
        $this->cloudinary = (bool) (isset($dados['cloudinary']) ? (bool) filter_var($dados['cloudinary'], FILTER_VALIDATE_BOOLEAN):false);
        $this->google_agenda = (bool) (isset($dados['google_agenda']) ? (bool) filter_var($dados['google_agenda'], FILTER_VALIDATE_BOOLEAN):false);
        
        $this->endereco_json_google = (string) (isset($dados['endereco_json_google']) ? (string) $dados['endereco_json_google']:'');
        
        $this->conta_capital_social = (string) (isset($dados['conta_capital_social']) ? (string) $dados['conta_capital_social']:'');
        $this->conta_lucros_apropriar = (string) (isset($dados['conta_lucros_apropriar']) ? (string) $dados['conta_lucros_apropriar']:'');
        $this->conta_prejuizos_acumulados = (string) (isset($dados['conta_prejuizos_acumulados']) ? (string) $dados['conta_prejuizos_acumulados']:'');
        $this->conta_vendas_a_vista = (string) (isset($dados['conta_vendas_a_vista'])? (string) $dados['conta_vendas_a_vista']:'');
        $this->conta_vendas_a_prazo = (string) (isset($dados['conta_vendas_a_prazo']) ? (string) $dados['conta_vendas_a_prazo']:'');
        $this->conta_servicos_a_vista = (string) (isset($dados['conta_servicos_a_vista']) ? (string) $dados['conta_servicos_a_vista']:'');
        $this->conta_servicos_a_prazo = (string) (isset($dados['conta_servicos_a_prazo']) ? (string) $dados['conta_servicos_a_prazo']:'');
        $this->conta_custo_mercadorias_vendas = (string) (isset($dados['conta_custo_mercadorias_vendidas']) ? (string) $dados['conta_custo_mercadorias_vendidas']: '');
        $this->conta_custo_servicos_prestados = (string) (isset($dados['conta_custo_servicos_prestados']) ? (string) $dados['conta_custo_servicos_prestados']:'');
        $this->conta_apuracao_resultado = (string) (isset($dados['conta_apuracao_resultado']) ? (string) $dados['conta_apuracao_resultado']:'');
    }

    /**
     * Função responsável por salvar os dados no banco de dados
     * @param array $dados
     * @return bool
     */
    public  function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        file_put_contents('json.json', (array) ['empresa' => $this->empresa, 'versao_sistema' => (string) $this->versao_sistema, 'anexa_documentos' => (string) $this->anexa_documentos, 'modulo_contabil' => (bool) $this->modulo_contabil, 'pedidos' => (bool) $this->pedidos, 'cloudinary' => (bool) $this->cloudinary, 'google_agenda' => (bool) $this->google_agenda, 'endereco_json_google' => (string) $this->endereco_json_google, 'conta_capital_social' => (string) $this->conta_capital_social, 'conta_lucros_apropriar' => (string) $this->conta_lucros_apropriar, 'conta_prejuizos_acumulados' => (string) $this->conta_prejuizos_acumulados, 'conta_vendas_a_vista' => (string) $this->conta_vendas_a_vista, 'conta_vendas_a_prazo' => (string) $this->conta_vendas_a_prazo, 'conta_servicos_a_vista' => (string) $this->conta_servicos_a_vista, 'conta_servicos_a_prazo' => (string) $this->conta_servicos_a_prazo, 'conta_custo_mercadorias_vendidas' => (string) $this->conta_custo_mercadorias_vendas, 'conta_custo_servicos_prestados' => (string) $this->conta_custo_servicos_prestados, 'conta_apuracao_resultado' => (string) $this->conta_apuracao_resultado, 'cliente_padrao' => $this->cliente_padrao, 'fornecedor_padrao' => $this->fornecedor_padrao], JSON_UNESCAPED_UNICODE);

        if($this->codigo_sistema == null){
            return (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'versao_sistema' => (string) $this->versao_sistema, 'anexa_documentos' => (string) $this->anexa_documentos, 'modulo_contabil' => (bool) $this->modulo_contabil, 'pedidos' => (bool) $this->pedidos, 'cloudinary' => (bool) $this->cloudinary, 'google_agenda' => (bool) $this->google_agenda, 'endereco_json_google' => (string) $this->endereco_json_google, 'conta_capital_social' => (string) $this->conta_capital_social, 'conta_lucros_apropriar' => (string) $this->conta_lucros_apropriar, 'conta_prejuizos_acumulados' => (string) $this->conta_prejuizos_acumulados, 'conta_vendas_a_vista' => (string) $this->conta_vendas_a_vista, 'conta_vendas_a_prazo' => (string) $this->conta_vendas_a_prazo, 'conta_servicos_a_vista' => (string) $this->conta_servicos_a_vista, 'conta_servicos_a_prazo' => (string) $this->conta_servicos_a_prazo, 'conta_custo_mercadorias_vendidas' => (string) $this->conta_custo_mercadorias_vendas, 'conta_custo_servicos_prestados' => (string) $this->conta_custo_servicos_prestados, 'conta_apuracao_resultado' => (string) $this->conta_apuracao_resultado, 'cliente_padrao' => $this->cliente_padrao, 'fornecedor_padrao' => $this->fornecedor_padrao]);
        }else{
            return (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_sistema], (array) ['empresa' => $this->empresa, 'versao_sistema' => (string) $this->versao_sistema, 'anexa_documentos' => (string) $this->anexa_documentos, 'modulo_contabil' => (bool) $this->modulo_contabil, 'pedidos' => (bool) $this->pedidos, 'cloudinary' => (bool) $this->cloudinary, 'google_agenda' => (bool) $this->google_agenda, 'endereco_json_google' => (string) $this->endereco_json_google, 'conta_capital_social' => (string) $this->conta_capital_social, 'conta_lucros_apropriar' => (string) $this->conta_lucros_apropriar, 'conta_prejuizos_acumulados' => (string) $this->conta_prejuizos_acumulados, 'conta_vendas_a_vista' => (string) $this->conta_vendas_a_vista, 'conta_vendas_a_prazo' => (string) $this->conta_vendas_a_prazo, 'conta_servicos_a_vista' => (string) $this->conta_servicos_a_vista, 'conta_servicos_a_prazo' => (string) $this->conta_servicos_a_prazo, 'conta_custo_mercadorias_vendidas' => (string) $this->conta_custo_mercadorias_vendas, 'conta_custo_servicos_prestados' => (string) $this->conta_custo_servicos_prestados, 'conta_apuracao_resultado' => (string) $this->conta_apuracao_resultado, 'cliente_padrao' => $this->cliente_padrao, 'fornecedor_padrao' => $this->fornecedor_padrao]);
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
        return (array) model_all((string) $this->tabela(), (array) $filtro['filtro'], (array) $filtro['odenacao'], (int) $filtro['limite']);
    }
}
?>