function alerta_campo_vazio(campo) {
    Swal.fire('Erro', 'O campo ' + campo + ' não pode ser vazio!', 'error');
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
function retornar_data(time_stamp, padrao = '') {
    const data = new Date(time_stamp.$date.$numberLong.substring(0, 10) * 1000);

    // return new Intl.DateTimeFormat("pt-BR", {dateStyle: "short", timeStyle: "short", timeZone: "America/Sao_Paulo"}).format(data);

    if (padrao == '' || padrao == 'BRAZIL' || padrao == 'BRASIL') {
        return new Intl.DateTimeFormat("pt-BR", { dateStyle: "short", timeZone: "America/Sao_Paulo" }).format(data);
    } else {
        return new Intl.DateTimeFormat("en-CA", { dateStyle: "short", timeZone: "America/Sao_Paulo" }).format(data);
    }
}
function validar_retorno(retorno, endereco = '', versao = 0) {
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
			window.location.href = sistema.url(endereco, {'rota':'index'});
		}, 2500);
	}
}