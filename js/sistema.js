var sistema = (function (window) {
    ;
    var sistema = {
        topo: (function () {
            var el_frame = null;
            if (window.frames['FrameCorpo']) {
                el_frame = window;
            }
            else if (window.parent.frames['FrameCorpo']) {
                el_frame = window.parent;
            }
            else if (window.parent.parent.frames['FrameCorpo']) {
                el_frame = window.parent.parent;
            }
            else if (window.parent.parent.parent.frames['FrameCorpo']) {
                el_frame = window.parent.parent;
            }
            else if (window.parent.parent.parent.parent.frames['FrameCorpo']) {
                el_frame = window.parent.parent.parent.parent;
            }
            return el_frame;
        }),
        verificar_status:(function(status, arquivo = null){
            if(status == true){
                Swal.fire('Sucesso!', 'Operação realizada com sucesso!', 'success');

                if(arquivo != null){
                    window.setTimeout(function(){
                        window.location.href = arquivo;
                    }, 2500);
                }
            }else{
                Swal.fire('Erro', 'Erro durante a operação!', 'error');
            }
        }),
        remover_linha_tabela:(function(tabela){
            let tamanho_tabela = tabela.rows.length;

            for(let contador = (tamanho_tabela-1); contador >=0; contador--){
                tabela.deleteRow(contador);
            }

            return tabela;
        }),
        gerar_td:(function(classe, texto, tipo = '', colspan = false, valor_colpan = ''){
            let coluna = document.createElement('td');

            if(tipo == ''){
                coluna.textContent = texto;
            }else if(tipo == 'inner'){
                coluna.innerHTML = texto;
            }else if(tipo == 'append'){
                coluna.appendChild(texto);
            }

            for(let contador = 0; contador < classe.length; contador++){
                coluna.classList.add(classe[contador]);
            }

            if(colspan == true){
                coluna.setAttribute('colspan', valor_colpan);
            }

            return coluna;
        }),
        gerar_botao:(function(id_botao, texto, classe, funcao = ''){
            let button = document.createElement('button');
            
            button.id = id_botao;
            button.textContent = texto;

            button.classList.add('custom-radius');

            for(let contador = 0; contador < classe.length; contador++){
                button.classList.add(classe[contador]);
            }

            if(funcao != ''){
                button.addEventListener('click', funcao);
            }

            return button;
        }),
        gerar_option:(function(value, text){
            let option = document.createElement('option');
    
            option.value = value;
            option.text = text;
    
            return option;
        }),
        gerar_checkbox:(function(name, identificador){
            let checkbox = document.createElement('input');
            checkbox.setAttribute('type', 'checkbox');
            checkbox.setAttribute('name', name);
            checkbox.setAttribute('id', identificador);
            checkbox.setAttribute('class', 'form-control custom-radius');
            return checkbox;
        }),
        remover_option:(function(select){
            let tamanho_select = select.length;

            if(tamanho_select > 1){
                for(let contador = 0; contador < tamanho_select; contador++){
                    if(contador != 0){
                        select.remove(contador);
                    }
                }
            }

            return select;
        }), 
        each: (function (lista, funcao) {
            var tamanho = 0;
            var usar_chave = false;
            var i = 0;
            var item = null;
            if ('length' in lista) {
                tamanho = lista.length;
            }
            if (funcao.length == 2) {
                usar_chave = true;
            }
            if (tamanho > 0) {
                for (i = 0; i < tamanho; i++) {
                    if (usar_chave == true) {
                        funcao(i, lista[i]);
                    }
                    else {
                        funcao(lista[i]);
                    }
                }
            }
            else {
                if (usar_chave == true) {
                    for (item in lista) {
                        funcao(item, lista[item]);
                    }
                }
                else {
                    for (item in lista) {
                        funcao(lista[item]);
                    }
                }
            }
        }),
        listeners: {
            moeda: function (e) {
                var max = sistema.integer(this.getAttribute('maxlength'));
                if (max == 0) {
                    max = 11;
                }
                var sltd = window.getSelection().toString();
                if ((sltd == this.value) && ((e.key != 'Tab') && (e.key != 'Enter'))) {
                    this.value = '';
                }
                var value = (this.value + e.key).replace(/[^0-9\,]/g, '');
                if (e.key == 'Backspace') {
                    value = value.slice(0, -1);
                }
                if (e.key == 'Delete') {
                    value = value.slice(0, -1);
                }
                var integer = value.split(',')[0];
                var decimal = value.split(',')[1];
                if (decimal != null) {
                    decimal = decimal + '';
                    if (decimal.length > 2) {
                        decimal = decimal.substr(0, 2);
                    }
                    value = integer + ',' + decimal;
                }
                else {
                    value = integer;
                    if (e.key == ',') {
                        value = value + ',';
                    }
                }
                if (value.length > max) {
                    value = value.substr(0, max);
                }
                this.value = value;
                if ((e.key != 'Tab') && (e.key != 'Enter')) {
                    e.preventDefault();
                }
            }
        },
        validation: {
            texto: function (e) {
                var value = this.value.replace(/[^A-z0-9ÀÁÂÃÄÅàáâãäÒÓÔÕÕÖòóôõöÈÉÊèéêÇçÌÍìíÙÚÜùúü\s].:,;/g, '');
            },
            moeda: function (e) {
                var value = this.value.replace(/[^0-9\,]/g, '');
                if (value.length > 0) {
                    var integer = sistema.integer(value.split(',')[0]);
                    var decimal = sistema.right(value.split(',')[1] || 0, 2, '0').substr(0, 2);
                    this.value = integer + ',' + decimal;
                    return true;
                }
                this.value = '';
                return false;
            }
        },
        mask: (function () {
            window.document.addEventListener('focus', function (event) {
                var element = event.target;
                if (element.getAttribute == null) {
                    return false;
                }
                else if (element.getAttribute('sistema-mask') == 'moeda') {
                    element.removeEventListener('keydown', sistema.listeners.moeda, true);
                    element.addEventListener('keydown', sistema.listeners.moeda, true);
                    element.removeEventListener('blur', sistema.validation.moeda, true);
                    element.addEventListener('blur', sistema.validation.moeda, true);
                }
                return true;
            }, true);
            window.document.addEventListener('mouseover', function (event) {
                var element = event.target;
                if (element.getAttribute == null) {
                    return false;
                }
                if (element.getAttribute('sistema-help') != null) {
                    element.removeEventListener('click', sistema.listeners.help, true);
                    element.addEventListener('click', sistema.listeners.help, true);
                }
                if (element.getAttribute('sistema-date-picker') != null) {
                    element.removeEventListener('click', sistema.listeners.picker, true);
                    element.addEventListener('click', sistema.listeners.picker, true);
                }
            }, true);
        }),
        url: (function (endereco, params) {
            var url = sistema.replace('\\', '/', window.location.href);
            var opt = [];
            url = (url.indexOf('?') > 0) ? url.substr(0, url.indexOf('?') + 1) : url;
            for (var key in params) {
                opt.push(key + '=' + params[key]);
            }
            url = url.substr(0, url.lastIndexOf('/')) + endereco;
            if (opt.length > 0) {
                url = url + '?' + opt.join('&');
            }
            return url;
        }),
        loader: (function (show) {
            if (show == true) {
                loader_sistema(true);
            }
            else {
                loader_sistema(false);
            }
        }),
        request: {
            send: (function (opt) {
                var method = opt.method;
                var url = opt.url;
                var data = opt.data;
                var complete = opt.complete;
                var loader = opt.loader;
                var parse = opt.parse || true;
                var fr = document.createElement('iframe');
                if (loader) {
                    sistema.loader(true);
                }
                fr.setAttribute('id', 'fake-ajax-iframe-' + sistema.date('u'));
                fr.setAttribute('src', '#');
                fr.setAttribute('style', 'display: none');
                sistema.element('body').appendChild(fr);
                var doc = fr.contentDocument || fr.contentWindow.document;
                var doc_body = doc.body;
                var fm = doc.createElement('form');
                fm.setAttribute('id', 'fake-ajax-form-' + sistema.date('u'));
                fm.setAttribute('method', method);
                fm.setAttribute('action', url);
                for (var key in data) {
                    var field = document.createElement('textarea');
                    if ((sistema.is_array(data[key]) == true) || (sistema.is_object(data[key]) == true)) {
                        data[key] = JSON.stringify(data[key]);
                    }
                    field.setAttribute('name', key);
                    field.innerHTML = data[key];
                    fm.appendChild(field);
                }
                doc_body.appendChild(fm);
                if(fm.id== HTMLTextAreaElement) console.log(" É um objeto text: " + fm.id);
                doc_body.querySelector('#' + fm.id).submit();
                fr.onload = (function () {
                    var doc = (fr.contentDocument || fr.contentWindow.document);
                    if (parse) {
                        try {
                            complete(JSON.parse(doc.body.innerHTML));
                        }
                        catch (e) {
                            console.log('ERRO AO INTERPRETAR RESPOSTA', e, '\n', doc.body.innerHTML);
                        }
                    }
                    else {
                        complete(doc.body.innerHTML);
                    }
                    if (loader) {
                        sistema.loader(false);
                    }
                    sistema.remove(fr);
                });
            }),
            post: (function (url, data, complete, loader) {
                if (loader === void 0) { loader = true; }
                sistema.request.send({
                    method: 'post',
                    url: sistema.url(url),
                    data: data,
                    complete: complete,
                    loader: loader
                });
            }),
            get: (function (url, data, complete, loader) {
                if (loader === void 0) { loader = true; }
                sistema.request.send({
                    method: 'get',
                    url: sistema.url(url),
                    data: data,
                    complete: complete,
                    loader: loader
                });
            })
        },
        download: (function (endereco, params, tempo_remover) {
            if (tempo_remover === void 0) { tempo_remover = 5000; }
            var fr = document.createElement('iframe');
            fr.setAttribute('id', 'download-iframe-' + sistema.date('u'));
            fr.setAttribute('style', 'display: none');
            fr.setAttribute('src', sistema.url(endereco, params));
            sistema.element('body').appendChild(fr);
            window.setTimeout(function () {
                sistema.remove(fr);
            }, tempo_remover);
        }),
        paginate: (function (lista, numero_pagina, quantidade_por_pagina) {
            if (quantidade_por_pagina === void 0) { quantidade_por_pagina = 50; }
            var inicio = 0;
            var fim = 0;
            var pagina = [];
            var total = 0;
            inicio = (numero_pagina - 1) * quantidade_por_pagina;
            fim = inicio + quantidade_por_pagina;
            sistema.each(lista, function (item) {
                if ((total >= inicio) && (total < fim)) {
                    pagina.push(item);
                }
                total = total + 1;
            });
            if (total > 0) {
                total = Math.ceil(total / quantidade_por_pagina);
            }
            if (total == 0) {
                total = 1;
            }
            return {
                pagina: pagina,
                total: total
            };
        }),
        tab: (function() {
            window.document.addEventListener('click', function (event) {
              var element = event.target;
              if (element.getAttribute == null) {
                return false;
              }
              if (element.getAttribute("sistema-tab")) {
              let listaInput = window.document.body.getElementsByTagName("input");
                for(var input of listaInput) 
                  input.classList.remove("ativo")

                element.classList.add("ativo")
                ativarDivTab()
              }
            });

            function ativarDivTab() {
              if (!window.document.body) return;
              let listaInput = window.document.body.getElementsByTagName("input");
              
              for(var div of window.document.documentElement.querySelectorAll(".tab")) {
                div.style.display = "none"
              }
              
              for(var input of listaInput) {
                  if (input.getAttribute("sistema-tab") == null) return false
                  
                input.classList.add("btn", "btn-desativado")
                if (input.classList.contains("ativo")) {
                  let id = "#tab-" + input.getAttribute("sistema-tab");
                  document.querySelector(id).style.display = "block"
                }
              }
          }
          ativarDivTab()
        }),
        initialize: (function (fn) {
            if (typeof fn === 'function') {
                window.addEventListener('load', fn, true);
            }
        })
    };
    window.document.addEventListener('DOMContentLoaded', function () {
        try {
            sistema.topo().EscondeDivTempo();
            if (window.addEventListener) window.addEventListener('keydown', function (e) {
                if (e.key == 'Escape') sistema.topo().sistema.modal.close();
            }, false);
            else if (document.attachEvent) document.attachEvent('onkeydown', function (e) {
                if (e.key == 'Escape') sistema.topo().sistema.modal.close();
            });
        }
        catch (e) { }
        sistema.mask();
        sistema.tab();
    }, true);
    return sistema;
})(window);


cookies_filtro = () => {
    var pathname = (window.location.pathname).replace("/", "");
    pathname = "_" + pathname.substr(0, pathname.search("/"))
    return pathname
}
  