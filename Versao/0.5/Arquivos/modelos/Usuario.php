<?php
require_once 'Classes/bancoDeDados.php';
require_once 'Interface.php';
require_once 'Sistema.php';
require_once 'ContasContabeis.php';
require_once 'ContasFornecedores.php';

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
    private string $cpf_cnpj;
    private bool $status_usuario;

    private mixed $data_cadastro;
    private mixed $data_ultimo_login;
    private bool $atualizacao_completa;
    private string $status_usuario_pesquisa;
    private array $opcao = ['const' => 8];

    public function tabela()
    {
        return (string) 'usuarios';
    }

    public function modelo()
    {
        return (array) ['empresa' => 'objectId', 'nome_usuario' => (string) '', 'email_usuario' => (string) '', 'senha_usuario' => (string) '', 'data_cadastro' => 'date', 'ultimo_login' => 'date', 'salario' => (float) 0, 'login_usuario' => (string) '', 'cargo' => (string) '', 'tipo_usuario' => (string) '', 'celular' => (string) '', 'cep' => (string) '', 'logradouro' => (string) '', 'numero' => (string) '', 'bairro' => (string) '', 'uf' => (string) '', 'estado' => (string) '', 'cpf_cnpj' => (string) '', 'status_usuario' => (bool) true];
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
            }else{
                $this->id_usuario = null;
            }
        }else{
            $this->id_usuario = null;
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
        } else {
            $this->senha_usuario = (string) password_hash('', PASSWORD_DEFAULT, $this->opcao);
        }

        if (array_key_exists('salario', $dados) == true) {
            $this->salario = (float) floatval($dados['salario']);
        } else {
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
        $this->cpf_cnpj = (string) (isset($dados['cpf_cnpj']) ? (string) $dados['cpf_cnpj'] : '');
        $this->status_usuario = (bool) (isset($dados['status_usuario']) ? (bool) filter_var($dados['status_usuario'], FILTER_VALIDATE_BOOLEAN) : true);

        $this->atualizacao_completa = (bool) (isset($dados['atualizacao_completa']) ? (bool) filter_var($dados['atualizacao_completa'], FILTER_VALIDATE_BOOLEAN) : false);
        $this->status_usuario_pesquisa = (string) (isset($dados['status_usuario_pesquisa']) ? (string) $dados['status_usuario_pesquisa'] : 'TODOS');

        $this->data_ultimo_login = (isset($dados['data_ultimo_login']) ? model_date($dados['data_ultimo_login']) : model_date());
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

        if ($retorno_checagem == true) {
            if ($this->atualizacao_completa == true) {
                $retorno_operacao = (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->id_usuario], (array) ['empresa' => $this->empresa, 'nome_usuario' => (string) $this->nome_usuario, 'email_usuario' => (string) $this->email_usuario, 'data_cadastro' => model_date(), 'ultimo_login' => model_date(), 'salario' => (float) $this->salario, 'login_usuario' => (string) $this->login_usuario, 'tipo_usuario' => (string) $this->tipo_usuario, 'cargo' => (string) $this->cargo, 'celular' => (string) $this->celular, 'cep' => (string) $this->cep, 'logradouro' => (string) $this->logradouro, 'numero' => (string) $this->numero, 'bairro' => (string) $this->bairro, 'uf' => (string) $this->uf, 'estado' => (string) $this->estado, 'cpf_cnpj' => (string) $this->cpf_cnpj, 'status_usuario' => (bool) $this->status_usuario]);
            } else {
                $retorno_operacao = (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->id_usuario], (array) ['empresa' => $this->empresa, 'nome_usuario' => (string) $this->nome_usuario, 'email_usuario' => (string) $this->email_usuario, 'ultimo_login' => model_date(), 'celular' => (string) $this->celular, 'cep' => (string) $this->cep, 'logradouro' => (string) $this->logradouro, 'numero' => (string) $this->numero, 'bairro' => (string) $this->bairro, 'uf' => (string) $this->uf, 'estado' => (string) $this->estado, 'cpf_cnpj' => (string) $this->cpf_cnpj, 'status_usuario' => (bool) $this->status_usuario]);
            }
        } else {
            $retorno_operacao = (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'nome_usuario' => (string) $this->nome_usuario, 'email_usuario' => (string) $this->email_usuario, 'senha_usuario' => (string) $this->senha_usuario, 'data_cadastro' => model_date(), 'ultimo_login' => model_date(), 'salario' => (float) $this->salario, 'login_usuario' => (string) $this->login_usuario, 'tipo_usuario' => (string) $this->tipo_usuario, 'cargo' => (string) $this->cargo, 'celular' => (string) $this->celular, 'cep' => (string) $this->cep, 'logradouro' => (string) $this->logradouro, 'numero' => (string) $this->numero, 'bairro' => (string) $this->bairro, 'uf' => (string) $this->uf, 'estado' => (string) $this->estado, 'cpf_cnpj' => (string) $this->cpf_cnpj, 'status_usuario' => (bool) $this->status_usuario]);
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
            // $retorno_senha = (bool) true;
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
     * @param mixed $filtro
     * @return array
     */
    public function pesquisar($filtro)
    {
        return (array) model_one($this->tabela(), $filtro['filtro']);
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

        if (!empty($this->cpf_cnpj)) {
            array_push($filtro, ['cpf_cnpj', '===', (string) $this->cpf_cnpj]);
        }

        if (!empty($this->tipo_usuario)) {
            if ($this->tipo_usuario == 'CLIENTE') {
                array_push($filtro, ['tipo_usuario', '===', (string) 'CLIENTE']);
            } else if ($this->tipo_usuario == 'FORNECEDOR') {
                array_push($filtro, ['tipo_usuario', '===', (string) 'FORNECEDOR']);
            }
        }

        if ($this->status_usuario_pesquisa != 'TODOS') {
            if ($this->status_usuario_pesquisa == 'ATIVO') {
                array_push($filtro, ['status_usuario', '===', (bool) true]);
            } else if ($this->status_usuario_pesquisa == 'INATIVO') {
                array_push($filtro, ['status_usuario', '===', (bool) false]);
            }
        }

        if (array_key_exists('data_inicial', $dados) == true && array_key_exists('data_final', $dados) == true) {
            if (!empty($dados['data_inicial']) && !empty($dados['data_final'])) {
                array_push($filtro, ['ultimo_login', '>=', model_date($dados['data_inicial'], '00:00:00')]);
                array_push($filtro, ['ultimo_login', '<=', model_date($dados['data_final'], '23:59:59')]);
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

    /**
     * Função responsável por alteraar o status do usuário, para true
     * @param array $dados (array) ['codigo_usuario' => 'xxxx'];
     * @return bool
     */
    public function update_status_usuario($dados)
    {
        $array_filtro = (array) ['filtro' => (array) ['_id', '===', $dados['codigo_usuario']]];

        $retorno_usuario = (array) $this->pesquisar((array) $array_filtro);

        if (empty($retorno_usuario) == false) {
            $retorno_usuario['codigo_usuario'] = (string) $retorno_usuario['_id'];
            $retorno_usuario['status_usuario'] = (bool) true;
            $retorno_usuario['atualizacao_completa'] = (bool) false;

            return (bool) $this->salvar_dados($retorno_usuario);
        } else {
            return (bool) false;
        }
    }

    /**
     * Função responsável por inativar os clientes automáticamente após 6 meses
     * @param array $dados
     * @return array
     */
    public function inativar_usuario_120_dias($dados)
    {
        $data_atual = new DateTime();
        $data_atual->modify('-120 days');

        $filtro = (array) ['filtro' => (array) [], 'ordenacao' => (array) ['ultimo_login' => (bool) true], 'limite' => (int) 0];
        $filtro_montando = (array) [];

        array_push($filtro_montando, ['empresa', '===', model_id($dados['empresa'])]);
        array_push($filtro_montando, ['ultimo_login', '<=', model_date($data_atual->format('Y-m-d'), '23:59:59')]);
        $filtro['filtro'] = (array) ['and' => (array) $filtro_montando];

        $retorno_usuarios = (array) $this->pesquisar_todos($filtro);

        if (empty($retorno_usuarios) == false) {
            $contador = (int) 0;
            $objeto_contas_fornecedores = new ContasFornecedores();

            foreach ($retorno_usuarios as $usuario) {
                $usuario['codigo_usuario'] = (string) $usuario['_id'];
                $usuario['status_usuario'] = (bool) false;
                $usuario['atualizacao_completa'] = (bool) false;

                $retorno = (bool) $this->salvar_dados($usuario);

                if ($retorno == true) {
                    $objeto_contas_fornecedores->inativar_conta_fornecedor((array) ['empresa' => (string) $usuario['empresa'], 'fornecedor' => (string) $usuario['_id'], 'status' => (bool) false]);
                }

                $contador++;
            }

            return (array) ['status' => (bool) true, 'achou' => (bool) true, 'mensagem' => (string) 'Quantidade de usuários inativados: ' . $contador, 'quantidade' => (int) $contador];
        } else {
            return (array) ['status' => (bool) false, 'achou' => (bool) false, 'mensagem' => (string) '', 'quantidade' => (int) 0];
        }
    }

    /**
     * Função responsável por alterar o status do usuário juntamente com as contas vinculadas ao mesmo.
     * @param mixed $dados
     * @return bool
     */
    function alterar_status_usuario($dados)
    {
        $this->colocar_dados($dados);

        $retorno_usuario = (array) $this->pesquisar((array) ['filtro' => (array) ['_id', '===', $this->id_usuario]]);

        $this->colocar_dados($dados);

        if (empty($retorno_usuario) == false) {
            $dados_update['status_usuario'] = (bool) $this->status_usuario;
            $dados_update['ultimo_login'] = $this->data_ultimo_login;

            if (array_key_exists('cpf_cnpj', $retorno_usuario) == false) {
                $dados_update['cpf_cnpj'] = (string) '';
            }

            $retorno = (bool) model_update($this->tabela(), (array) ['_id', '===', $this->id_usuario], (array) $dados_update);

            if ($retorno == true) {
                $objeto_conta_fornecedor = new ContasFornecedores();
                $array_conta_fornecedor = (array) ['empresa' => (string) $this->empresa, 'fornecedor' => (string) $this->id_usuario, 'status' => (bool) $this->status_usuario];
                $objeto_conta_fornecedor->inativar_conta_fornecedor($array_conta_fornecedor);

                return (bool) true;
            } else {
                return (bool) false;
            }
        } else {
            return (bool) false;
        }
    }
}
