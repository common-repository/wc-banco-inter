document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('copiarCDBarras');
    if(btn !== null){
        btn.addEventListener('click', function() {
            copiarCDBarras();
        });
    }

    var btnpix = document.getElementById('copiarPix');
    if(btnpix !== null){
        btnpix.addEventListener('click', function() {
            copiarPix();
        });
    }
});

function copiarCDBarras() {
    var textoCopiado = document.getElementById("boleto");
    textoCopiado.select();
    document.execCommand("Copy");
    alert("Texto Copiado: " + textoCopiado.value);
}

function copiarPix() {
    var textoCopiado = document.getElementById("pix");
    textoCopiado.select();
    document.execCommand("Copy");
    alert("Texto Copiado: " + textoCopiado.value);
}

document.addEventListener('DOMContentLoaded', function() {
    /**Pegar URL https://wordpress.diletec.com.br/checkout/order-received/17585/?key=wc_order_HdKukGhmA8Hpt*/
    var url = window.location.href;
    /**Pegar o numero que existe na url /17585/ */
    var id = url.match(/(\d+)/);
    if(id !== null){
        id = id[0];
        setInterval(function(){
            jQuery.ajax({
                url: "/?wc-api=wc_banco_inter_pix&id="+id,
                type: "GET",
                success: function (data) {
                    data = JSON.parse(data);
                    if(data.status == "CONCLUIDA"){
                        window.location.reload();
                    }else if(data.instrucao == "reload"){
                        window.location.reload();
                    }
                }
            });
        }, 30000);
    }
});