<?php
require_once 'classes/bancoDeDados.php';
require_once 'Interface.php';
require_once 'Sistema.php';
require_once 'ContasContabeis.php';
require_once 'ContasFornecedores.php';

class Usuario implements InterfaceModelo
{
    private int $id_usuario;
    private int $empresa;
    private string $nome_usuario;
    private string $email_usuario;
    private string $senha_usuario;
    private string $login_usuario;
    private string $tipo_usuario;
    private float $salario;
    private string $cargo;
    private string $celular;
    private string $cep;
    private string $logradouro;
    private string $bairro;
    private string $uf;
    private string $estado;
    private string $numero;
    private string $cpf_cnpj;
    private bool $status_usuario;

    private mixed $data_cadastro;
    private mixed $data_ultimo_login;
    private bool $atualizacao_completa;
    private string $status_usuario_pesquisa;
    private array $opcao = ['const' => 8];

    public function tabela()
    {
        return (string) 'usuario';
    }

    public function modelo()
    {
        return (array) [];
    }

    /**
     * Função responsável por colocar os dados no campos fazendo as devidas validações
     * @param array $dados
     * @return void
     */
    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_usuario', $dados) == true) {
            if ($dados['codigo_usuario'] != 0) {
                $this->id_usuario = (int) intval($dados['codigo_usuario'], 10);
            } else {
                $this->id_usuario = 0;
            }
        } else {
            $this->id_usuario = 0;
        }

        if (array_key_exists('empresa', $dados) == true) {
            $this->empresa = $dados['empresa'];
        }

        if (array_key_exists('nome_usuario', $dados) == true) {
            $this->nome_usuario = (string) strtoupper($dados['nome_usuario']);
        }

        if (array_key_exists('email_usuario', $dados) == true) {
            $this->email_usuario = (string) $dados['email_usuario'];
        }

        if (array_key_exists('senha_usuario', $dados) == true) {
            $this->senha_usuario = (string) password_hash($dados['senha_usuario'], PASSWORD_DEFAULT, $this->opcao);
        } else {
            $this->senha_usuario = (string) password_hash('', PASSWORD_DEFAULT, $this->opcao);
        }

        if (array_key_exists('salario', $dados) == true) {
            $this->salario = (float) floatval($dados['salario']);
        } else {
            $this->salario = (float) 0;
        }

        $this->login_usuario = (string) (isset($dados['login_usuario']) ? (string) $dados['login_usuario'] : '');
        $this->tipo_usuario = (string) (isset($dados['tipo_usuario']) ? (string) $dados['tipo_usuario'] : 'Administrador');
        $this->cargo = (string) (isset($dados['cargo']) ? (string) $dados['cargo'] : '');
        $this->celular = (string) (isset($dados['celular']) ? (string) $dados['celular'] : '');
        $this->cep = (string) (isset($dados['cep']) ? (string) $dados['cep'] : '');
        $this->logradouro = (string) (isset($dados['logradouro']) ? (string) $dados['logradouro'] : '');
        $this->bairro = (string) (isset($dados['bairro']) ? (string) $dados['bairro'] : '');
        $this->uf = (string) (isset($dados['uf']) ? (string) $dados['uf'] : '');
        $this->estado = (string) (isset($dados['estado']) ? (string) $dados['estado'] : '');
        $this->numero = (string) (isset($dados['numero']) ? (string) $dados['numero'] : '');
        $this->cpf_cnpj = (string) (isset($dados['cpf_cnpj']) ? (string) $dados['cpf_cnpj'] : '');
        $this->status_usuario = (bool) (isset($dados['status_usuario']) ? (bool) filter_var($dados['status_usuario'], FILTER_VALIDATE_BOOLEAN) : true);

        $this->atualizacao_completa = (bool) (isset($dados['atualizacao_completa']) ? (bool) filter_var($dados['atualizacao_completa'], FILTER_VALIDATE_BOOLEAN) : false);
        $this->status_usuario_pesquisa = (string) (isset($dados['status_usuario_pesquisa']) ? (string) $dados['status_usuario_pesquisa'] : 'TODOS');

        $this->data_ultimo_login = (isset($dados['data_ultimo_login']) ? model_date($dados['data_ultimo_login']) : '');
        $this->data_cadastro = (string) (isset($dados['data_cadastro']) ? (string) model_date($dados['data_cadastro']) : '');
    }

    /**
     * Função responsável por salvar os dados no banco de dados
     * @param mixed $dados
     * @return bool
     */
    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        if ($this->id_usuario == 0) {
            $user_dados = $this->montar_array();
            $user_dados['data_cadastro'] = model_date();
            $user_dados['ultimo_login'] = model_date();

            if ($this->tipo_usuario == 'Administrador') {
                $user_dados['senha_usuario'] = $this->senha_usuario;
            }

            return (bool) model_insert((string) $this->tabela(), (array) $user_dados);
        } else {
            return (bool) model_update((string) $this->tabela(), ['where' => (array) [['codigo_usuario', '=', (int) $this->id_usuario]]], (array) $this->montar_array());
        }
    }

    /**
     * Função responsável por realizar o login do usuário no sistema. Esta função recebe como parâmetro atrávés de array o login e senha e retorna o código se existir login e senha compatíveis senão retorna 0.
     * @param array $dados ['login_usuario' => 'xxxx', 'senha_usuario' => (string) 'xxxxxx' ];
     * @return array
     */
    public function login_sistema($dados)
    {
        $this->colocar_dados($dados);

        $retorno_usuario = (array) model_one($this->tabela(), ['where' => [['email_usuario', '=', (string) $this->email_usuario]]]);
        if (empty($retorno_usuario) == false) {
            $retorno_senha = (bool) password_verify($dados['senha_usuario'], $retorno_usuario['senha_usuario']);
            // $retorno_senha = (bool) true;
            $objeto_sistema = new Sistema();
            $retorno_sistema = (array) $objeto_sistema->pesquisar(['filtro' => ['where' => [['codigo_empresa', '=', $retorno_usuario['codigo_empresa']]]]]);

            // file_put_contents('json.json', json_encode(['retorno' => $retorno_usuario, 'fil' => ['where' => [['email_usuario', '=', (string) $this->email_usuario]], 'sistema' => $retorno_sistema]]));

            $versao_sistema = (string) 'alfa 0.0';

            if (empty($retorno_sistema) == false) {
                $versao_sistema = (string) $retorno_sistema['versao_sistema'];
            }

            if ($retorno_senha == true) {
                $retorno_usuario['versao_sistema'] = (string) $versao_sistema;
                return (array) $retorno_usuario;
            } else {
                return (array) [];
            }
        } else {
            return (array) [];
        }
        return (array) [];
    }

    /**
     * Função responsável por pesquisar os dados de todos os usuários no bacno de dados
     * @param array $dados
     * @return array
     */
    public function pesquisar_todos($dados)
    {
        return (array) model_all($this->tabela(), $dados['filtro'], $dados['ordenacao']);
    }

    /**
     * Função responsável por pesquisar um usuário no banco de dados
     * @param mixed $filtro
     * @return array
     */
    public function pesquisar($filtro)
    {
        return (array) model_one($this->tabela(), $filtro['filtro']);
    }

    /**
     * Função responsáel por pesquisar o login_usuario no banco de e retornar essa informação
     * @param mixed $id_usuario identificador do usuário do tipo codigo_usuario
     * @return (string) login_usuario retorna o login do usuário
     */
    public function retornar_usuario($id_usuario)
    {
        // $retorno_usuario = (array) $this->pesquisar((array) ['filtro' => (array) ['codigo_usuario', '===', $id_usuario]]);

        // if (empty($retorno_usuario) == false) {
        //     if (array_key_exists('email_usuario', $retorno_usuario) == true) {
        //         return (string) $retorno_usuario['email_usuario'];
        //     } else {
        //         return (string) '';
        //     }
        // } else {
        //     return (string) '';
        // }
        return (string) '';
    }

    /**
     * Função responsável alterar o campo último login no banco de dados
     * @param array $dados
     * @return void
     */
    public function update_ultimo_login($dados)
    {
        $retorno = (bool) model_update((string) $this->tabela(), (array) ['where' => [['codigo_usuario', '=', $dados['codigo_usuario']]]], (array) ['ultimo_login' => model_date()]);
    }

    /**
     * Função responsável por salvar o avatar do usuário no sistema
     * @param mixed $dados
     * @param mixed $file
     * @return bool
     */
    public function salvar_imagem_avatar($dados, $file)
    {
        $codigo_usuario = '';
        // $extensao = '';

        if (array_key_exists('codigo_usuario', $dados)) {
            $codigo_usuario = (string) $_POST['codigo_usuario'];
        }

        // $extensao = pathinfo($file['arquivo']['name'], PATHINFO_EXTENSION);

        // $pasta = 'imagens/avatar';

        // if (!is_dir($pasta)) {
        //     mkdir($pasta, 0777, true);
        // }

        // chmod($pasta, 0777);

        // $nome_arquivo = (string) $pasta . '/' . $codigo_usuario . '.' . $extensao;

        // if (move_uploaded_file($file['arquivo']['tmp_name'], $nome_arquivo)) {
        //     return true;
        // }

        // return false;

        $conteudo = file_get_contents($file['arquivo']['tmp_name']);
        
        $base64 = base64_encode($conteudo);
        
        $mime = mime_content_type($file['arquivo']['tmp_name']);
        
        $imagem = 'data:' . $mime . ';base64,' . $base64;
        return (bool) $this->alterar_foto($imagem, $codigo_usuario);

    }

    /**
     * Função responsável por alterar o avatar do usuário
     * @param mixed $imagem
     * @param mixed $codigo_usuario
     * @return bool
     */
    public function alterar_foto($imagem, $codigo_usuario)
    {
        return (bool) model_update((string) $this->tabela(), ['where' => [['codigo_usuario', '==', $codigo_usuario]]], ['avatar' => (string) $imagem]);
    }

    /**
     * Função responsável por alterar os dados do usuário pesquisando as suas informações
     * @param array $dados
     * @return bool
     */
    public function alterar_com_pesquisa($dados)
    {
        $this->colocar_dados($dados);

        $retorno_pesquisa = (array) $this->pesquisar((array) ['filtro' => (array) ['where' => [['codigo_usuario', '=', $this->id_usuario]]]]);

        if (empty($retorno_pesquisa) == false) {
            $this->colocar_dados($retorno_pesquisa);
            return (bool) $this->salvar_dados($dados);
        } else {
            return (bool) false;
        }
    }

    /**
     * Função responsável por alterar o salário do funcionário no banco de dados
     * @param array $dados
     * @return (bool) return;
     */
    public function alterar_salario($dados)
    {
        return (bool) model_update((string) $this->tabela(), (array) ['where' => [['codigo_usuario', '=', $dados['codigo_usuario']]]], (array) ['salario' => (double) doubleval(str_replace(',', '.', $dados['salario']))]);
    }

    /**
     * Função responsável por verificar se o usuário existe e caso exista faz a troca da senha
     * @param array $dados
     * @return (bool) $return
     */
    public function alterar_senha($dados)
    {
        $this->colocar_dados($dados);

        $retorno_usuario = (array) $this->pesquisar((array) ['filtro' => (array) ['where' => [['email_usuario', '===', (string) $this->email_usuario]]]]);

        if (empty($retorno_usuario) == false) {
            return (bool) model_update((string) $this->tabela(), ['where' => [['codigo_usuario', '=', $retorno_usuario['codigo_usuario']]]], (array) ['senha_usuario' => (string) $this->senha_usuario]);
        } else {
            return (bool) false;
        }
        return (bool) false;
    }

    /**
     * Rota responsável por montar o filtro de pesquisar, pesqusiar e retornar as informações para a rota
     * que chamou para que a mesma possa processar os dados.
     * @param array $dados
     * @return array $retorno_final
     */
    public function pesquisar_cliente($dados)
    {
        $this->colocar_dados($dados);
        $retorno_final = (array) [];

        $modulo_contabil = (bool) (isset($dados['modulo_contabil']) ? (bool) filter_var($dados['modulo_contabil'], FILTER_VALIDATE_BOOLEAN) : false);

        $filtro = (array) [];
        $retorno = (array) [];

        if (!empty($this->empresa)) {
            array_push($filtro, ['codigo_empresa', '=', $this->empresa]);
        }

        if (!empty($this->nome_usuario)) {
            array_push($filtro, ['nome_usuario', 'LIKE', (string) $this->nome_usuario]);
        }

        if (!empty($this->email_usuario)) {
            array_push($filtro, ['email_usuario', '=', (string) $this->email_usuario]);
        }

        if (!empty($this->celular)) {
            array_push($filtro, ['celular', '=', (string) $this->celular]);
        }

        if (!empty($this->cpf_cnpj)) {
            array_push($filtro, ['cpf_cnpj', '=', (string) $this->cpf_cnpj]);
        }

        if (!empty($this->tipo_usuario)) {
            if ($this->tipo_usuario == 'CLIENTE') {
                array_push($filtro, ['tipo_usuario', '=', (string) 'CLIENTE']);
            } else if ($this->tipo_usuario == 'FORNECEDOR') {
                array_push($filtro, ['tipo_usuario', '=', (string) 'FORNECEDOR']);
            }
        }

        if ($this->status_usuario_pesquisa != 'TODOS') {
            if ($this->status_usuario_pesquisa == 'ATIVO') {
                array_push($filtro, ['status_usuario', '=', (bool) true]);
            } else if ($this->status_usuario_pesquisa == 'INATIVO') {
                array_push($filtro, ['status_usuario', '=', (bool) false]);
            }
        }

        if (array_key_exists('data_inicial', $dados) == true && array_key_exists('data_final', $dados) == true) {
            if (!empty($dados['data_inicial']) && !empty($dados['data_final'])) {
                array_push($filtro, ['ultimo_login', '>=', model_date($dados['data_inicial'], '00:00:00')]);
                array_push($filtro, ['ultimo_login', '<=', model_date($dados['data_final'], '23:59:59')]);
            }
        }

        if (empty($filtro) == false) {
            $retorno = (array) $this->pesquisar_todos((array) ['filtro' => (array) ['where' => (array) $filtro], 'ordenacao' => (array) [['nome_usuario', 'ASC']]]);
        }

        if ($modulo_contabil == true) {
            if (!empty($retorno)) {
                $objeto_conta_contabil = new ContasContabeis();

                foreach ($retorno as $cliente_fornecedor) {
                    $filtro_montando_conta = (array) [];
                    $modulo_contabil = (array) ['local_conta_id_1' => (string) '', 'conta_contabil_1' => (string) '', 'local_conta_id_2' => (string) '', 'conta_contabil_2' => (string) ''];
                    $filtro_conta = (array) ['filtro' => (array) [], 'ordenacao' => (array) ['conta_contabil' => (bool) true], 'limite' => (int) 0];

                    if ($dados['empresa'] != '') {
                        array_push($filtro_montando_conta, (array) ['empresa', '===', $this->empresa]);
                    }

                    array_push($filtro_montando_conta, (array) ['id_local', '===', $cliente_fornecedor['codigo_cliente_fornecedor']]);

                    $filtro_conta['filtro'] = (array) ['and' => (array) $filtro_montando_conta];

                    $retorno_modulo_contabil = (array) $objeto_conta_contabil->pesquisar_todos($filtro_conta);

                    if (empty($retorno_modulo_contabil) == false) {
                        $contador = (int) 1;

                        foreach ($retorno_modulo_contabil as $contabil) {
                            if ($contador == 1) {
                                $modulo_contabil['local_conta_id_1'] = (string) $contabil['local_conta_id'];
                                $modulo_contabil['conta_contabil_1'] = (string) $contabil['conta_contabil'];

                                $contador++;
                            } else {
                                $modulo_contabil['local_conta_id_2'] = (string) $contabil['local_conta_id'];
                                $modulo_contabil['conta_contabil_2'] = (string) $contabil['conta_contabil'];
                            }
                        }

                        $cliente_fornecedor['modulo_contabil'] = (array) $modulo_contabil;
                    } else {
                        $cliente_fornecedor['modulo_contabil'] = (array) $modulo_contabil;
                    }

                    array_push($retorno_final, $cliente_fornecedor);
                }
            }
        } else {
            $retorno_final = (array) $retorno;
        }

        return (array) $retorno_final;
    }

    /**
     * Função responsável por inativar os clientes automáticamente após 6 meses
     * @param array $dados
     * @return array
     */
    public function inativar_usuario_120_dias($dados)
    {
        $query = (string) "update usuario set status_usuario = false where ultimo_login <= current_date - interval '120 days' and codigo_empresa = :empresa";
        $resultado = model_query($query, (array) $dados);

        return (array) $resultado;
    }

    /**
     * Função responsável por alterar o status do usuário juntamente com as contas vinculadas ao mesmo.
     * @param mixed $dados
     * @return bool
     */
    function alterar_status_usuario($dados)
    {
        $this->colocar_dados($dados);

        // $retorno_usuario = (array) $this->pesquisar((array) ['filtro' => (array) ['codigo_usuario', '===', $this->id_usuario]]);

        // $this->colocar_dados($dados);

        // if (empty($retorno_usuario) == false) {
        //     $dados_update['status_usuario'] = (bool) $this->status_usuario;
        //     $dados_update['ultimo_login'] = $this->data_ultimo_login;

        //     if (array_key_exists('cpf_cnpj', $retorno_usuario) == false) {
        //         $dados_update['cpf_cnpj'] = (string) '';
        //     }

        //     $retorno = (bool) model_update($this->tabela(), (array) ['codigo_usuario', '===', $this->id_usuario], (array) $dados_update);

        //     if ($retorno == true) {
        //         $objeto_conta_fornecedor = new ContasFornecedores();
        //         $array_conta_fornecedor = (array) ['empresa' => (string) $this->empresa, 'fornecedor' => (string) $this->id_usuario, 'status' => (bool) $this->status_usuario];
        //         $objeto_conta_fornecedor->inativar_conta_fornecedor($array_conta_fornecedor);

        //         return (bool) true;
        //     } else {
        //         return (bool) false;
        //     }
        // } else {
        //     return (bool) false;
        // }
        return (bool) false;
    }

    public function montar_array()
    {
        $dados = (array) [];

        if ($this->empresa != 0) {
            $dados['codigo_empresa'] = (int) $this->empresa;
        }

        if ($this->nome_usuario != '') {
            $dados['nome_usuario'] = (string) $this->nome_usuario;
        }

        if ($this->email_usuario != '') {
            $dados['email_usuario'] = (string) $this->email_usuario;
        }

        if ($this->data_cadastro != '') {
            $dados['data_cadastro'] = (string) $this->data_cadastro;
        }

        if ($this->data_ultimo_login != '') {
            $dados['ultimo_login'] = (string) $this->data_ultimo_login;
        }

        if ($this->salario != 0) {
            $dados['salario'] = (double) $this->salario;
        }

        if ($this->login_usuario != '') {
            $dados['login_usuario'] = (string) $this->login_usuario;
        }

        if ($this->cargo != '') {
            $dados['cargo'] = (string) $this->cargo;
        }

        if ($this->tipo_usuario != '') {
            $dados['tipo_usuario'] = (string) $this->tipo_usuario;
        }

        if ($this->celular != '') {
            $dados['celular'] = (string) $this->celular;
        }

        if ($this->cep != '') {
            $dados['cep'] = (string) $this->cep;
        }

        if ($this->logradouro != '') {
            $dados['logradouro'] = (string) $this->logradouro;
        }

        if ($this->numero != '') {
            $dados['numero'] = (string) $this->numero;
        }

        if ($this->bairro != '') {
            $dados['bairro'] = (string) $this->bairro;
        }

        if ($this->uf != '') {
            $dados['uf'] = (string) $this->uf;
        }

        if ($this->estado != '') {
            $dados['estado'] = (string) $this->estado;
        }

        if ($this->cpf_cnpj != '') {
            $dados['cpf_cnpj'] = (string) $this->cpf_cnpj;
        }

        $dados['status_usuario'] = (bool) $this->status_usuario;

        return (array) $dados;
    }
}
