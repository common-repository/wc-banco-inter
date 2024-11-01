<?php

/**
 * Configurações do projeto Pix
 */

//DADOS GERAIS DO PIX (DINÂMICO E ESTÁTICO)
define('BACO_INTER_PIX_KEY','34640331000108');
define('BACO_INTER_PIX_MERCHANT_NAME','DILETEC PROCESSAMENTO DE DADOS E SOFTWARE EIRELI');
define('BACO_INTER_PIX_MERCHANT_CITY','BELO HORIZONTE');

require __DIR__.'/Payload.php';


//INSTANCIA PRINCIPAL DO PAYLOAD PIX
$obPayload = (new Payload)->setPixKey(BACO_INTER_PIX_KEY)
                          ->setDescription('Pagamento do pedido 123456')
                          ->setMerchantName(BACO_INTER_PIX_MERCHANT_NAME)
                          ->setMerchantCity(BACO_INTER_PIX_MERCHANT_CITY)
                          ->setAmount(100.00)
                          ->setTxid('Woocommerce');

//CÓDIGO DE PAGAMENTO PIX
$payloadQrCode = $obPayload->getPayload();
print_r($payloadQrCode);



