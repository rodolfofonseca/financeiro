/**
 * Função responsável por exibir um alerta quando um campo obrigatório estiver vazio
 * @param {string} mensagem 
 */
function alerta_campo_vazio(mensagem) {
  Swal.fire('ATENÇÃO', mensagem, 'warning');
}

/**
 * Função responsável por imprimir o conteúdo de uma div, ocultando o botão de impressão e ajustando a classe da div para ocupar toda a largura da página durante a impressão, e depois restaurando as configurações originais após a impressão.
 * @param {HTMLElement} div_botao 
 * @param {string} class_remover 
 * @param {HTMLElement} div_trocar 
 * @param {string} class_voltar 
 */
function imprimir(div_botao, class_remover, div_trocar, class_voltar) {
  div_botao.style.display = 'none';

  div_trocar.classList.remove(class_remover);
  div_trocar.classList.add('col-12');

  window.print();

  window.setTimeout(function () {
    div_trocar.classList.remove('col-12');
    div_trocar.classList.add(class_voltar);
    div_botao.style.display = 'block';
  }, 500);

}

/**
 * Função responsável por fechar a janela atual do navegador, geralmente usada para fechar pop-ups ou janelas de diálogo
 */
function fechar() {
  window.close();
}

/**
 * Função responsável por exibir ou ocultar um elemento de carregamento (loader) com base no valor booleano passado como argumento. Se o valor for true, o loader será exibido: caso contário ele permanece oculto.
 * @param {boolean} boolean 
 */
function loader_sistema(boolean) {
  if (boolean == true) {
    document.querySelector('#loader').style.display = 'flex';
  } else {
    document.querySelector('#loader').style.display = 'none';
  }
}

/**
 * Função responsável por validar o tipo de usuário e redirecionar para a página de dashboard caso o tipo seja 'COMUM', garantindo que apenas usuários com privilégios administrativos tenham acesso a determinadas áreas do sistema.
 * @param {string} tipo_usuario 
 */
function validar_acesso_administrador(tipo_usuario) {

  if (tipo_usuario == 'COMUM') {
    window.location.href = sistema.url('/dashboard.php', { 'rota': 'index' });
  }
}

/**
 * Função responsável por validar o retorno de uma operação e exibir mensagens apropriadas ao usuário.
 * @param {object} retorno 
 * @param {string} endereco 
 * @param {number} versao 
 * @param {string} rota 
 */
function validar_retorno(retorno, endereco = '', versao = 0, rota = 'index') {
  if (versao == 0) {
    if (retorno.status == true) {
      this.Swal.fire({ title: "SUCESSO NA OPERAÇÃO!", text: "Operação realizada com sucesso!", icon: "success" });
    } else {
      this.Swal.fire({ title: "FALHA NA OPERAÇÃO!", text: "Erro durante o processo, tente mais tarde!", icon: "error" });
    }
  } else {
    this.Swal.fire({ title: retorno.titulo, text: retorno.mensagem, icon: retorno.icone });
  }

  if (endereco != '') {
    window.setTimeout(function () {
      window.location.href = sistema.url(endereco, { 'rota': rota });
    }, 2500);
  }
}

/** 
 * Função para decodificar entidades HTML, útil para exibir descrições ou textos que foram codificados para evitar problemas de segurança, como XSS.
 * @param {string} html - A string contendo entidades HTML codificadas.
 * @returns {string} - A string com as entidades HTML decodificadas.
*/
function decodeHTML(html) {
  const txt = document.createElement("textarea");
  txt.innerHTML = html;
  return txt.value;
}

/**
 * Função resposável por adicionar a mascara de contas contábeis aos campos passados
 * @param {object} campo 
 * @returns 
 */
function mascara_conta(campo) {
  var conta = campo.value;

  if (existe(campo)) {
    conta = strReplace(".", "", conta);

    if (conta.length == 0) {
      conta = "";
    } else if (conta.length == 1) {
      if (conta == 0) {
        conta = '0';
        return false;
      } else {
        conta = conta + "0000000000000000";
      }

    } else if (conta.length == 2) {
      conta = conta + "000000000000000";

    } else if (conta.length == 3) {
      conta = conta + "00000000000000";

    } else if (conta.length == 4) {
      conta = conta + "0000000000000";

    } else if (conta.length == 5) {
      conta = conta + "000000000000";

    } else if (conta.length == 6) {
      conta = conta + "00000000000";

    } else if (conta.length == 7) {
      conta = conta + "0000000000";

    } else if (conta.length == 8) {
      conta = conta + "000000000";

    } else if (conta.length == 9) {
      conta = conta + "00000000";

    } else if (conta.length == 10) {
      conta = conta + "0000000";

    } else if (conta.length == 11) {
      conta = conta + "000000";

    } else if (conta.length == 12) {
      conta = conta + "00000";

    } else if (conta.length == 13) {
      conta = conta + "0000";

    } else if (conta.length == 14) {
      conta = conta + "000";

    } else if (conta.length == 15) {
      conta = conta + "00";

    } else if (conta.length == 16) {
      conta = conta + "0";

    } else if (conta.length == 17) {
      conta = conta + "";
    }
  }

  if ((campo.value).length !== 0) {
    conta = conta.substr(0, 1) + '.' +
      conta.substr(1, 1) + '.' +
      conta.substr(2, 2) + '.' +
      conta.substr(4, 3) + '.' +
      conta.substr(7, 4) + '.' +
      conta.substr(11, 6);
  }

  campo.value = conta;
}

/**
 * Função responsável por fazer a identação da conta contábil de acordo com o seu grau
 * @param {int} conta_grau 
 * @param {string} conta_contabil 
 * @returns 
 */
function validar_grau_conta(conta_grau, conta_contabil) {
  if (conta_grau == 1) {
    return conta_contabil;
  } else {
    for (let contador = 1; contador < conta_grau; contador++) {
      conta_contabil = '&nbsp;&nbsp;&nbsp;&nbsp;' + conta_contabil;
    }

    return conta_contabil;
  }
}

/**
 * Função responsável por exibir uma barra de progresso utilizando a biblioteca SweetAlert2, mostrando o título fornecido e um indicador visual do progresso, além de um texto que pode ser atualizado para refletir o status atual da operação.
 * @param {string} titulo - O título a ser exibido na barra de progresso.
 */
function barra_progresso(titulo) {
  Swal.fire({
    title: titulo,
    html: `<div style="width:100%; background:#e9ecef; border-radius:10px; overflow:hidden;"> <div id="barra_progresso" style=" width:0%; height:25px; background:#198754; text-align:center; line-height:25px;  color:#fff; font-weight:bold; transition: width .2s; ">  0% </div></div><div id="texto_progresso" style="margin-top:10px;">Iniciando... </div>`,
    allowOutsideClick: false,
    showConfirmButton: false
  });
}

/**
 * Função responsável por atualizar a barra de progresso exibida pela função barra_progresso, calculando o percentual com base no índice atual e no tamanho total do retorno, e atualizando tanto a largura da barra quanto o texto que indica o progresso atual.
 * @param {int} index 
 * @param {int} tamanho_retorno 
 * @param {htmlElement} barra_progresso 
 * @param {htmlElement} texto_progresso 
 */
function atualizar_barra_progresso(index, tamanho_retorno, barra_progresso, texto_progresso) {
  let percentual = Math.round(((index + 1) / tamanho_retorno) * 100);
  let barra = barra_progresso;
  let texto = texto_progresso;
  barra.style.width = percentual + '%';
  barra.innerHTML = percentual + '%';

  texto.innerHTML = 'Processando ' + (index + 1) + ' de ' + tamanho_retorno;
}

/**
 * Função responsável por validar um campo específico, exibindo ou ocultando mensagens de validação e ajustando as classes CSS do campo com base no status de validação fornecido.
 * 
 * Se o status for true, a função oculta a mensagem de validação e remove a classe 'is-invalid' do campo, indicando que o campo é válido. Caso contrário, a função exibe um alerta com a mensagem fornecida, mostra a mensagem de validação e adiciona a classe 'is-invalid' ao campo, indicando que o campo é inválido.
 * @param {htmlElement} campo 
 * @param {htmlElement} div
 * @param {boolean} status
 * @param {string} mensagem 
 * @param {boolean} usar_div
 */
function validar_campo(campo, div, status = true, mensagem = '', usar_div = true) {
  if (status == true) {
    campo.classList.remove('is-invalid');

    if(usar_div == true){
      div.style.display = 'none';
    }
  } else {
    alerta_campo_vazio(mensagem);
    campo.classList.add('is-invalid');

    if(usar_div == true){
      div.style.display = 'block';
    }
  }
}

function validarCpfCnpj(valor) {
  valor = valor.replace(/\D/g, '');

  if (valor.length === 11) {
    return validarCPF(valor);
  }

  if (valor.length === 14) {
    return validarCNPJ(valor);
  }

  return false;
}

function validarCPF(cpf) {
  if (/^(\d)\1+$/.test(cpf)) {
    return false;
  }

  let soma = 0;
  let resto;

  for (let i = 1; i <= 9; i++) {
    soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
  }

  resto = (soma * 10) % 11;

  if (resto === 10 || resto === 11) {
    resto = 0;
  }

  if (resto !== parseInt(cpf.substring(9, 10))) {
    return false;
  }

  soma = 0;

  for (let i = 1; i <= 10; i++) {
    soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
  }

  resto = (soma * 10) % 11;

  if (resto === 10 || resto === 11) {
    resto = 0;
  }

  if (resto !== parseInt(cpf.substring(10, 11))) {
    return false;
  }

  return true;
}

function validarCNPJ(cnpj) {
  if (/^(\d)\1+$/.test(cnpj)) {
    return false;
  }

  let tamanho = cnpj.length - 2;
  let numeros = cnpj.substring(0, tamanho);
  let digitos = cnpj.substring(tamanho);
  let soma = 0;
  let pos = tamanho - 7;

  for (let i = tamanho; i >= 1; i--) {
    soma += numeros.charAt(tamanho - i) * pos--;

    if (pos < 2) {
      pos = 9;
    }
  }

  let resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);

  if (resultado != digitos.charAt(0)) {
    return false;
  }

  tamanho = tamanho + 1;
  numeros = cnpj.substring(0, tamanho);

  soma = 0;
  pos = tamanho - 7;

  for (let i = tamanho; i >= 1; i--) {
    soma += numeros.charAt(tamanho - i) * pos--;

    if (pos < 2) {
      pos = 9;
    }
  }

  resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);

  if (resultado != digitos.charAt(1)) {
    return false;
  }

  return true;
}

/**
 * Função responsável por calcular a diferença entre uma data fornecida em formato de timestamp e a data atual, verificando se a data fornecida está dentro de um determinado número de dias em relação à data atual. A função converte o timestamp para um objeto Date, ajusta para o fuso horário local, e compara com a data atual e a data resultante da subtração dos dias especificados, retornando true se a data fornecida estiver dentro do intervalo definido.
 * @param {*} time_stamp 
 * @param {*} diferenca 
 * @returns 
 */
function diferenca_datas(timestamp, diferenca) {
  const data = new Date(timestamp);

  const hoje = new Date();
  hoje.setHours(0, 0, 0, 0);

  const menosDias = new Date(hoje);
  menosDias.setDate(menosDias.getDate() - diferenca);

  return data >= menosDias;
}

/**
 * Função responsável por adicionar informações ao objeto passado por parâmetro
 * @param {*} objeto 
 * @param {*} chave 
 * @param {*} valor
 * @returns 
 */
function adicionar(objeto, chave, valor) {
  if (valor !== '') {
      objeto[chave] = valor;
  }
  
  return objeto;
}