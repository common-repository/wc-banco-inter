<?php

function wc_banco_inter_sub_pro()
{
    produtosEcommerce();
    /**Fazer uma lista de funcionalidades da versão PRO
        Parcelamento por Pix
        Parcelamento por Boleto
        Atualizações mais rápidas
        Integração PIX
        Gráfico de vendas
        Baixa automática por PIX
        Parametrização de vencimento de boleto
        Status do Plugin
        Tela de Log de Erros
        Integração por Webhook
        Integração por Extrato Bancário
        Segunda via de boleto
*/
    $diferenças = [
        'Baixa automática de Pix',
        'Painel de informações de Vendas e Gráficos',
        'Parametrização de vencimento do boleto',
        'Tela de status do Plugin',
        'Tela que lista os boletos e seus status',
        'Tela de Log com as informações de erros',
        'Inicio de correções nas primeiras horas de localização do erro',
        'Área do cliente com especialistas',
        'Suporte',
        'Integração por Webhook',
        'Integração por Extrato Bancário',
        'Atualizações mais rápidas',
        'Integração com Woocommerce Multistore',
        'Integração com Woocommerce Subscriptions',
        'Integração com Woocommerce Subscriptions Pro',
        'Integração com Woocommerce Subscriptions Multistore',
        'Integração com Enhancer for WooCommerce Subscriptions',
        'Podem enviar ideias e sugestões para as próximas versões',
        'Integração com Plugin WhatsApp API para WordPress e Woocommerce',
        'Parcelamento por Pix',
        'Parcelamento por Boleto',
        'Atualizações mais rápidas',
        'Integração PIX Oficial',
        'Gráfico de vendas',
        'Baixa automática por PIX',
        'Parametrização de vencimento de boleto',
        'Status do Plugin',
        'Tela de Log de Erros',
        'Integração por Webhook',
        'Integração por Extrato Bancário',
        'Segunda via de boleto',

    ];

    $iguais = [
        'Baixa automática de boletos',
        'Vendas e Configurações em Boletos',
        'Vendas e Configurações em Pix',
    ];

    $verificarSistema = [
        'dirPrincipal' => is_dir(WP_CONTENT_DIR . '/banco-inter'),
        'dirErro' => is_dir(WP_CONTENT_DIR . '/banco-inter/errors'),
        'fileError' => is_file(WP_CONTENT_DIR . '/banco-inter/errors/error.log'),
    ];
    $principal = $verificarSistema['dirPrincipal'] ? '<td style="color: green;text-align: center;"><span class="dashicons dashicons-saved"></span></td>' : '<td style="color: red;text-align: center;"><span class="dashicons dashicons-no-alt"></span></td>';
    $erro = $verificarSistema['dirErro'] ? '<td style="color: green;text-align: center;"><span class="dashicons dashicons-saved"></span></td>' : '<td style="color: red;text-align: center;"><span class="dashicons dashicons-no-alt"></span></td>';
    $fileError = $verificarSistema['fileError'] ? '<td style="color: green;text-align: center;"><span class="dashicons dashicons-saved"></span></td>' : '<td style="color: red;text-align: center;"><span class="dashicons dashicons-no-alt"></span></td>';

    _e('

    <table class="wp-list-table widefat striped table-view-list">
        <thead>
            <th><h1>Sistema</h1></th>
            <th><h1 style="color: gold;text-align: center;">Status</h1></th>
        </thead>
        <tbody>
            <tr>
                <td width=40%>Diretório Principal</td>
                '.$principal.'
            </tr>
            <tr>
                <td width=40%>Diretório de Erros</td>
                '.$erro.'
            </tr>
            <tr>
                <td width=40%>Arquivo de Erros</td>
                '.$fileError.'
            </tr>
        </tbody>
        <tfooter>
            <tr>
                <td></td>
                <td style="text-align: right;" colspan="3">
                    <a class="button button-primary button-hero load-customize hide-if-no-customize"
                    href="https://cp.diletec.com.br/produto/banco-inter-para-woocommerce-pro-5">
                        Adquira já sua Versão PRO por R$175,00 <b>Promoção válida para os 10 primeiros</b>
                    </a>
                </td>
            </tr>
        </tfooter>
    </table>
    <br>
    <table class="wp-list-table widefat striped table-view-list">
        <thead>
            <th><h1>Diferenças</h1></th>
            <th><h1 style="color: gold;text-align: center;">PRO</h1></th>
            <th><h1 style="text-align: center;">FREE</h1></th>
        </thead>
        <tbody>
        ');
    foreach ($diferenças as &$value) {
        _e('
            <tr>
                <td width=40%>' . $value . '</td>
                <td style="color: green;text-align: center;"><span class="dashicons dashicons-saved"></span></td>
                <td style="color: red;text-align: center;"><span class="dashicons dashicons-no-alt"></span></td>
            </tr>'
        );
    }
    foreach ($iguais as &$value) {
        _e('
            <tr>
                <td width=40%>' . $value . '</td>
                <td style="color: green;text-align: center;"><span class="dashicons dashicons-saved"></span></td>
                <td style="color: green;text-align: center;"><span class="dashicons dashicons-saved"></span></td>
            </tr>'
        );
    }
    _e('
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td></td>
                <td style="text-align: right;" colspan="3">
                    <a class="button button-primary button-hero load-customize hide-if-no-customize"
                    href="https://cp.diletec.com.br/produto/banco-inter-para-woocommerce-pro-5">
                        Adquira já sua Versão PRO por R$175,00 <b>Promoção válida para os 10 primeiros</b>
                    </a>
                </td>
            </tr>
        </tfoot>
    </table>
    ');
}

function produtosEcommerce(){
//Apresentar os plugins disponíveis do json do diletec https://perfexfranqueador.diletec.com.br/admin/ecommerce/products_page/getJson
try {
    $json = file_get_contents('https://cp.diletec.com.br/ecommerce/products_page/getJson?order=date&categories%5B%5D=2&categories%5B%5D=5&min_price=50&max_price=500');
    $plugins = json_decode($json, true);
} catch (\Throwable $th) {
    $plugins = [];
}

// $plugins = array_reverse($json);
_e('
<div class="wrap">
<div id="wrapper">
    <div class="content">

<div class="products-list col-md-12">
    <div class="row">
    <h2>Instale os Plugins abaixo e transforme o seu e-commerce</h2>
');
foreach ($plugins['products'] as $key => $product):
    //var_dump($product); exit;
    $product = (object)$product;
    //var_dump($product['id']);
    $linkProduct = $product->slug;
    $acentos = array(
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i',
        'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u',
        'ç' => 'c',
        'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A',
        'É' => 'E', 'È' => 'E', 'Ê' => 'E',
        'Í' => 'I', 'Ì' => 'I', 'Î' => 'I',
        'Ó' => 'O', 'Ò' => 'O', 'Õ' => 'O', 'Ô' => 'O',
        'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U',
        'Ç' => 'C'
    );
    $linkProduct = strtr( $linkProduct, $acentos );
    $linkProduct = strtolower($linkProduct);
    $linkProduct = str_replace(" ", "-", $linkProduct);
?>
        <a href="<?php echo "https://cp.diletec.com.br/produto/" . $linkProduct; ?>" class="product-link col-md-4 mb-4" id="<?php echo $product->id; ?>">
            <div class="product">
                <div id="<?php echo "product-" . $product->id; ?>" class="carousel slide" data-interval="false">
                    <div class="carousel-inner" role="listbox">
                        <div class="item active">
                            <img src="<?php echo "https://cp.diletec.com.br/uploads/ecommerce/products/" . $product->main_image['image']; ?>" alt="<?php echo $product->name; ?>">
                        </div>
                        <?php foreach ($product->secondary_images as $index => $image): ?>
                            <div class="item">
                                <img src="<?php echo "https://cp.diletec.com.br/uploads/ecommerce/products/" . $image['image']; ?>" alt="<?php echo $product->name; ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($product->secondary_images) > 0): ?>
                        <button class="left carousel-control" href="#<?php echo "product-" . $product->id; ?>" data-slide="prev">
                            <i class="glyphicon glyphicon-chevron-left" aria-hidden="true"></i>
                        </button>
                        <button class="right carousel-control" href="#<?php echo "product-" . $product->id; ?>" data-slide="next">
                            <i class="glyphicon glyphicon-chevron-right" aria-hidden="true"></i>
                        </button>
                    <?php endif; ?>
                </div>
                <h5><?php echo $product->name; ?></h5>
                <small>
                    <?php if ($product->prom_price == 0 || !($product->prom_valid_from < date('Y-m-d H:i:s') && $product->prom_valid_to > date('Y-m-d H:i:s') || $product->prom_valid_to == NULL)): ?>
                        <span><?php //echo 'R$'; echo number_format($product->price,2,",","."); ?></span>
                    <?php else: ?>
                        <del><?php //echo 'R$'; echo number_format($product->price,2,",","."); ?></del>
                        <span><?php //echo 'R$'; echo number_format($product->prom_price, 2,",","."); ?></span>
                    <?php endif; ?>
                </small>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
</div>
</div>
</div>
<style type="text/css">
    .product {
        display: inline-block;
        margin: 5px;
        border-radius: 5px;
        text-align: center;
        width: 25%;
        min-height: 300px;
        max-height: 350px;
        overflow: hidden;
    }

    .products-list .product .item img {
        width: 360px;
        height: 230px;
        transition: all 0.3s ease;
    }

    .product:hover .carousel {
        box-shadow: 0 0 40px rgba(0, 0, 0, 0.5);
    }

    .product:hover h5, .product:hover small:not(.stock small){
        color: rgb(37 99 235/var(--tw-bg-opacity));
    }

    .products-list .product .carousel:hover .item img {
        transform: scale(1.1);
    }

    .product-link {
        border-radius: 5px;
        text-align: center;
        color: #4a4a4a;
        padding: 0;
    }

    .product h5 {
        font-size: 14px;
        font-weight: bold;
        text-decoration: none;
        text-transform: uppercase;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 0px;
    }

    .product del {
        color: #c1c1c1;
        margin: 5px;
    }

    .product small span {
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        text-transform: uppercase;
    }

    .products-list {
        text-align: center;
        display: inline-block;
    }

    .carousel .product-buttons:not(.product-buttons:has(.custom-countdown)) button, .carousel button:focus, .carousel button.carousel-control{
        border: none;
        opacity: 0;
        transition: 0.3s;
    }

    .carousel:hover button {
        display: block;
        opacity: 1 !important;
    }

    .promo {
        position: absolute;
        top: 15px;
        left: 20px;
        padding-left: 5px;
        padding-right: 5px;
        border: none;
        color: #ffffff;
        background-color: rgb(37 99 235/var(--tw-bg-opacity));
        font-weight: bold;
        font-size: 14px;
        z-index: 100;
    }

    .stock {
        position: absolute;
        top: 15px;
        right: 20px;
        padding-left: 5px;
        padding-right: 5px;
        border: none;
        color: #ffffff;
        background-color: rgb(37 99 235/var(--tw-bg-opacity));
        font-weight: bold;
        font-size: 14px;
        z-index: 100;
    }

    .stock-orange {
        position: absolute;
        top: 15px;
        right: 20px;
        padding-left: 5px;
        padding-right: 5px;
        border: none;
        color: #ffffff;
        background-color: orange;
        font-weight: bold;
        font-size: 14px;
        z-index: 100;
    }

    .product-buttons {
        position: absolute;
        bottom: 0;
        transition: all .3s ease-out;
        left: 20px;
        z-index: 100;
    }

    .carousel:hover .product-buttons:not(.product-buttons:has(.custom-countdown)),  .product-buttons:has(.btn-warning), .product-buttons:has(.btn-danger){
        bottom: 15px;
    }

    .product-buttons .btn-danger{
        border: none;
        color: white;
    }

    .product-buttons .btn-danger:hover{
        background-color: red;
    }

    .product-buttons button{
        border-radius: 0%;
        color: #6f6f6f;
        background-color: #ffffff;
    }

    /**Mobile */
    @media (max-width: 767px) {
        .product {
            display: inline-grid;
            max-width: 100%;
            min-height: 250px;
            max-height: 260px;
            margin: 5px 0px 5px 0px;
        }

        .products-list .product .item img {
            width: 280px;
            height: 200px;
        }

        .product-buttons {
            bottom: 0;
        }

        .product-buttons button{
            border-radius: 0%;
            color: #6f6f6f;
            background-color: #ffffff;
        }

        .carousel{
            margin: auto;
        }
    }

    .custom-countdown, .custom-countdown-release{
        border: 2px solid #06A3FC; /* Cor da borda azul */
        /*padding: 10px;*/
        display: flex;
        justify-content: space-between; /* Espaço entre o ícone e o countdown */
        align-items: center; /* Centraliza verticalmente */
        background-color: white; /* Fundo branco */
        color: #06A3FC; /* Cor do texto em azul */
        width: 220px; /* Largura do countdown */
        height: 38px;
        margin-bottom: 15px;
        border-radius: 0.25rem;
        overflow: hidden;
    }

    .custom-countdown-release{
        border: 2px solid orange !important; /* Cor da borda azul */
        color: orange !important;
    }

    .custom-countdown i, .custom-countdown-release i {
        color: white; /* Cor do ícone em branco */
        line-height: 38px;
        font-size: 25px;
    }

    .countdown-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        margin: 0 auto;
    }

    .countdown-label {
        font-weight: bold;
        font-size: 10px;
    }

    .countdown {
        font-size: 14px; /* Tamanho do texto do countdown */
        font-weight: bold;
    }

    .cd-blue {
        height: 38px;
        width: 40px;
        margin: 0;
        padding: 0;
        background: #06A3FC;
    }

    .cd-orange {
        height: 38px;
        width: 40px;
        margin: 0;
        padding: 0;
        background: orange;
    }

    .product-buttons:has(.custom-countdown), .product-buttons:has(.custom-countdown-release){
        left: 50%;
        transform: translate(-50%, 0%);
    }

    .product-buttons .custom-countdown button{
        display: block;
        height: 38px;
        width: 40px;
        margin: 0;
        padding: 0;
        background: #06A3FC;
        border: none;
    }

    .custom-countdown{
        display: none;
        float: right;
        background-image: linear-gradient(90deg, #06A3FC 20%, white 20%, white 80%, #06A3FC 40%);
        background-size: 100% 100%;
        background-repeat: no-repeat;
    }

    .custom-countdown-release{
        display: none;
        float: right;
        background-image: linear-gradient(90deg, orange 20%, white 20%, white 80%, white 40%);
        background-size: 100% 100%;
        background-repeat: no-repeat;
    }
</style>

<?php
}