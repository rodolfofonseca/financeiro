<?php
require_once 'Classes/bancoDeDados.php';
require_once 'Interface.php';
require_once 'Sistema.php';
require_once 'ContasContabeis.php';

class Usuario implements InterfaceModelo
{
    private mixed $id_usuario;
    private mixed $empresa;
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

    private mixed $data_cadastro;
    private mixed $data_ultimo_login;
    private array $opcao = ['const' => 8];

    public function tabela()
    {
        return (string) 'usuarios';
    }

    public function modelo()
    {
        return (array) ['empresa' => 'objectId', 'nome_usuario' => (string) '', 'email_usuario' => (string) '', 'senha_usuario' => (string) '', 'data_cadastro' => 'date', 'ultimo_login' => 'date', 'salario' => (float) 0, 'login_usuario' => (string) '', 'cargo' => (string) '', 'tipo_usuario' => (string) '', 'celular' => (string) '', 'cep' => (string) '', 'logradouro' => (string) '', 'numero' => (string) '', 'bairro' => (string) '', 'uf' => (string) '', 'estado' => (string) ''];
    }

    /**
     * Função responsável por colocar os dados no campos fazendo as devidas validações
     * @param array $dados
     * @return void
     */
    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_usuario', $dados) == true) {
            if ($dados['codigo_usuario'] != '') {
                $this->id_usuario = model_id($dados['codigo_usuario']);
            }
        }

        if (array_key_exists('empresa', $dados) == true) {
            $this->empresa = model_id($dados['empresa']);
        }

        if (array_key_exists('nome_usuario', $dados) == true) {
            $this->nome_usuario = (string) strtoupper($dados['nome_usuario']);
        }

        if (array_key_exists('email_usuario', $dados) == true) {
            $this->email_usuario = (string) $dados['email_usuario'];
        }

        if (array_key_exists('senha_usuario', $dados) == true) {
            $this->senha_usuario = (string) password_hash($dados['senha_usuario'], PASSWORD_DEFAULT, $this->opcao);
        }else{
            $this->senha_usuario = (string) password_hash('', PASSWORD_DEFAULT, $this->opcao);
        }

        if (array_key_exists('salario', $dados) == true) {
            $this->salario = (float) floatval($dados['salario']);
        }else{
            $this->salario = (float) 0;
        }

        $this->login_usuario = (string) (isset($dados['login_usuario']) ? (string) $dados['login_usuario'] : 'Sem Login');
        $this->tipo_usuario = (string) (isset($dados['tipo_usuario']) ? (string) $dados['tipo_usuario'] : 'Administrador');
        $this->cargo = (string) (isset($dados['cargo']) ? (string) $dados['cargo'] : '');
        $this->celular = (string) (isset($dados['celular']) ? (string) $dados['celular'] : '');
        $this->cep = (string) (isset($dados['cep']) ? (string) $dados['cep'] : '');
        $this->logradouro = (string) (isset($dados['logradouro']) ? (string) $dados['logradouro'] : '');
        $this->bairro = (string) (isset($dados['bairro']) ? (string) $dados['bairro'] : '');
        $this->uf = (string) (isset($dados['uf']) ? (string) $dados['uf'] : '');
        $this->estado = (string) (isset($dados['estado']) ? (string) $dados['estado'] : '');
        $this->numero = (string) (isset($dados['numero']) ? (string) $dados['numero'] : '');
    }

    /**
     * Função responsável por salvar os dados no banco de dados
     * @param mixed $dados
     * @return bool
     */
    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);
        $retorno_operacao = (bool) false;
        $retorno_checagem = (bool) false;

        $retorno_checagem = (bool) model_check((string) $this->tabela(), (array) ['email_usuario', '===', (string) $this->email_usuario]);
        
        // if ($this->tipo_usuario != 'CLIENTE' && $this->tipo_usuario != 'FORNECEDOR') {
        //     $retorno_checagem = (bool) model_check((string) $this->tabela(), (array) ['email_usuario', '===', (string) $this->email_usuario]);
        // } else if ($this->tipo_usuario == 'CLIENTE' && $this->id_usuario != null) {
        //     $retorno_checagem = (bool) model_check((string) $this->tabela(), (array) ['_id', '===', (string) $this->id_usuario]);
        // }

        if ($retorno_checagem == true) {
            $retorno_operacao = (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->id_usuario], (array) ['empresa' => $this->empresa, 'nome_usuario' => (string) $this->nome_usuario, 'email_usuario' => (string) $this->email_usuario, 'data_cadastro' => model_date(), 'ultimo_login' => model_date(), 'salario' => (float) $this->salario, 'login_usuario' => (string) $this->login_usuario, 'tipo_usuario' => (string) $this->tipo_usuario, 'cargo' => (string) $this->cargo, 'celular' => (string) $this->celular, 'cep' => (string) $this->cep, 'logradouro' => (string) $this->logradouro, 'numero' => (string) $this->numero, 'bairro' => (string) $this->bairro, 'uf' => (string) $this->uf, 'estado' => (string) $this->estado]);
        } else {
            $retorno_operacao = (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'nome_usuario' => (string) $this->nome_usuario, 'email_usuario' => (string) $this->email_usuario, 'senha_usuario' => (string) $this->senha_usuario, 'data_cadastro' => model_date(), 'ultimo_login' => model_date(), 'salario' => (float) $this->salario, 'login_usuario' => (string) $this->login_usuario, 'tipo_usuario' => (string) $this->tipo_usuario, 'cargo' => (string) $this->cargo, 'celular' => (string) $this->celular, 'cep' => (string) $this->cep, 'logradouro' => (string) $this->logradouro, 'numero' => (string) $this->numero, 'bairro' => (string) $this->bairro, 'uf' => (string) $this->uf, 'estado' => (string) $this->estado]);
        }



        return (bool) $retorno_operacao;
    }

    /**
     * Função responsável por realizar o login do usuário no sistema. Esta função recebe como parâmetro atrávés de array o login e senha e retorna o código se existir login e senha compatíveis senão retorna 0.
     * @param array $dados ['login_usuario' => 'xxxx', 'senha_usuario' => (string) 'xxxxxx' ];
     * @return array 
     */
    public function login_sistema($dados)
    {
        $this->colocar_dados($dados);

        $retorno_usuario = (array) model_one($this->tabela(), ['email_usuario', '===', (string) $this->email_usuario]);

        if (empty($retorno_usuario) == false) {
            $retorno_senha = (bool) password_verify($dados['senha_usuario'], $retorno_usuario['senha_usuario']);

            $objeto_sistema = new Sistema();
            $retorno_sistema = (array) $objeto_sistema->pesquisar(['empresa', '===', $retorno_usuario['empresa']]);

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
    }

    /**
     * Função responsável por pesquisar os dados de todos os usuários no bacno de dados
     * @param array $dados
     * @return array
     */
    public function pesquisar_todos($dados)
    {
        return (array) model_all($this->tabela(), $dados['filtro'], $dados['ordenacao'], $dados['limite']);
    }

    /**
     * Função responsável por pesquisar um usuário no banco de dados
     * @param mixed $dados
     * @return array
     */
    public function pesquisar($dados)
    {
        return (array) model_one($this->tabela(), $dados['filtro']);
    }

    /**
     * Função responsáel por pesquisar o login_usuario no banco de e retornar essa informação
     * @param mixed $id_usuario identificador do usuário do tipo _id
     * @return (string) login_usuario retorna o login do usuário
     */
    public function retornar_usuario($id_usuario)
    {
        $retorno_usuario = (array) $this->pesquisar((array) ['filtro' => (array) ['_id', '===', $id_usuario]]);

        if (empty($retorno_usuario) == false) {
            if (array_key_exists('email_usuario', $retorno_usuario) == true) {
                return (string) $retorno_usuario['email_usuario'];
            } else {
                return (string) '';
            }
        } else {
            return (string) '';
        }
    }

    /**
     * Função responsável alterar o campo último login no banco de dados
     * @param array $dados
     * @return void
     */
    public function update_ultimo_login($dados)
    {
        $retorno = (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $dados['codigo_usuario']], (array) ['ultimo_login' => model_date()]);
    }

    /**
     * Função responsável por salvar o avatar do usuário no sistema
     * @param mixed $dados
     * @param mixed $file
     * @return bool
     */
    public function salvar_imagem_avatar($dados, $file)
    {
        $codigo_usuario = (string) '';
        $extensao = (string) '';

        if (array_key_exists('codigo_usuario', $dados) == true) {
            $codigo_usuario = (string) $_POST['codigo_usuario'];
        }

        $extensao = pathinfo($file["arquivo"]["name"], PATHINFO_EXTENSION);

        $pasta = (string) 'imagens/avatar';

        if (!is_dir($pasta)) {
            mkdir($pasta, 0777, true);
        }

        $nome_arquivo = (string) $pasta . '/' . $codigo_usuario . "." . $extensao;

        if (move_uploaded_file($file['arquivo']['tmp_name'], $nome_arquivo)) {
            return (bool) true;
        } else {
            return (bool) false;
        }
    }

    /**
     * Função responsável por alterar os dados do usuário pesquisando as suas informações
     * @param array $dados
     * @return bool
     */
    public function alterar_com_pesquisa($dados)
    {
        $this->colocar_dados($dados);

        $retorno_pesquisa = (array) $this->pesquisar((array) ['filtro' => (array) ['_id', '===', $this->id_usuario]]);

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
        return (bool) model_update((string) $this->tabela(), (array) ['_id', '===', model_id($dados['codigo_usuario'])], (array) ['salario' => (double) doubleval(str_replace(',', '.', $dados['salario']))]);
    }

    /**
     * Função responsável por verificar se o usuário existe e caso exista faz a troca da senha
     * @param array $dados
     * @return (bool) $return
     */
    public function alterar_senha($dados)
    {
        $this->colocar_dados($dados);

        $retorno_usuario = (array) $this->pesquisar((array) ['filtro' => (array) ['email_usuario', '===', (string) $this->email_usuario]]);

        if (empty($retorno_usuario) == false) {
            return (bool) model_update((string) $this->tabela(), ['_id', '===', $retorno_usuario['_id']], (array) ['senha_usuario' => (string) $this->senha_usuario]);
        } else {
            return (bool) false;
        }
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

        $modulo_contabil = (bool) (isset($dados['modulo_contabil']) ? (bool) filter_var($dados['modulo_contabil'], FILTER_VALIDATE_BOOLEAN) : false);

        $retorno_final = (array) [];
        $filtro = (array) [];
        $retorno = (array) [];

        if (!empty($this->empresa)) {
            array_push($filtro, ['empresa', '===', $this->empresa]);
        }

        if (!empty($this->nome_usuario)) {
            array_push($filtro, ['nome_usuario', '=', (string) $this->nome_usuario]);
        }

        if (!empty($this->email_usuario)) {
            array_push($filtro, ['email_usuario', '===', (string) $this->email_usuario]);
        }

        if (!empty($this->celular)) {
            array_push($filtro, ['celular', '===', (string) $this->celular]);
        }

        if (!empty($this->tipo_usuario)) {
            if ($this->tipo_usuario == 'CLIENTE') {
                array_push($filtro, ['tipo_usuario', '===', (string) 'CLIENTE']);
            } else if ($this->tipo_usuario == 'FORNECEDOR') {
                array_push($filtro, ['tipo_usuario', '===', (string) 'FORNECEDOR']);
            } else if ($this->tipo_usuario == 'CLIENTE_FORNECEDOR') {
                array_push($filtro, ['tipo_usuario', '===', (string) '']);
            }
        }

        if (empty($filtro) == false) {
            $retorno = (array) $this->pesquisar_todos((array) ['filtro' => (array) ['and' => (array) $filtro], 'ordenacao' => (array) ['nome_usuario' => (bool) true], 'limite' => (int) 0]);
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

                    array_push($filtro_montando_conta, (array) ['id_local', '===', $cliente_fornecedor['_id']]);

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
}
