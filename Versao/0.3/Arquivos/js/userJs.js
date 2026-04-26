function alerta_campo_vazio(campo) {
    Swal.fire('Erro', 'O campo ' + campo + ' não pode ser vazio!', 'warning');
}
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
function fechar() {
    window.close();
}
function loader_sistema(boolean) {
    if (boolean == true) {
        document.querySelector('#loader').style.display = 'flex';
    } else {
        document.querySelector('#loader').style.display = 'none';
    }
}
function validar_acesso_administrador(tipo_usuario) {

    if (tipo_usuario == 'COMUM') {
        window.location.href = sistema.url('/dashboard.php', { 'rota': 'index' });
    }
}
function validar_retorno(retorno, endereco = '', versao = 0, rota = 'index') {
	if (versao == 0) {
		if (retorno.status == true) {
			this.Swal.fire({ title: "SUCESSO NA OPERAÇÃO!", text: "Operação realizada com sucesso!", icon: "success" });
		} else {
			this.Swal.fire({ title: "FALHA NA OPERAÇÃO!", text: "Erro durante o processo, tente mais tarde!", icon: "error" });
		}
	}else{
		this.Swal.fire({title: retorno.titulo, text: retorno.mensagem, icon: retorno.icone});
	}

	if (endereco != '') {
		window.setTimeout(function () {
			window.location.href = sistema.url(endereco, {'rota':rota});
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
  function validar_grau_conta(conta_grau, conta_contabil){
    if(conta_grau == 1){
      return conta_contabil;
    }else{
      for(let contador = 1; contador < conta_grau; contador++){
        conta_contabil = '&nbsp;&nbsp;&nbsp;&nbsp;'+conta_contabil;
      }

      return conta_contabil;
    }
  }