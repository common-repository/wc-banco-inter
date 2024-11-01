=== Adicionar Banco Inter ao WooCommerce ===
Contributors: Diletec, danielwpsouza
Donate link: www.diletec.com.br
Tags: banco inter, woocommerce, e-commerce, boleto, pix, shop, cart, checkout, payments, paypal, storefront, stripe, woo commerce, pagseguro, PicPay, Nubank
Requires at least: 3.3
Tested up to: 6.6
Requires PHP: 7.0
Stable tag: 2.1.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Adiciona o Banco Inter como método de pagamento ao seu WooCommerce.

## Meios de Recebimentos

Receba por Boleto Pix, Boleto e Pix. Quando o Inter tiver API de cartão de crédito, também será adicionado.

## Banco Inter para Woocommerce!

![enter image description here](https://www.diletec.com.br/wp-content/uploads/2019/04/logo-diletec-cor-01.png)
Olá! Esse Plugin foi desenvolvido pela **[Diletec](https://cp.diletec.com.br/produto/banco-inter-para-woocommerce-pro-5)** para adicionar o metodo de pagamento de Boleto do Banco Inter ao Wordpress Woocommerce. Pretendemos também criar a integração com o **PIX** assim que a API for liberada.


## Files

O arquivo **index.php** é quem controla a aplicação, não é necessário nenhuma modificação. **Cuidado com permissões de arquivos!**
São necessários os arquivos **.key e .crt**

### Como configurar:
**1.**  Acesse a sua conta no Inter e crie uma Aplicação(integração);
**2.**  Ative o Plugin em seu wordpress e clique em **Settings**;
**3.**  Vá em configurações de Pagamento do WooCommerce e ative o Banco Inter;
**4.**  Coloque os dados da sua empresa;
**5.**  Coloque as credenciais da sua aplicação;
**6.**  Coloque os arquivos fornecidos pelo Inter .key e o arquivo .crt;
**7.**  Você está Pronto para vender com o Banco Inter.;


# Synchronization

A sincronização é por meio de navegação e *cron* do WP, ela será substituida para apenas *CRON*.


## Boleto PDF

Os boletos em PDF ficam na pasta **tmp** dentro do plugin. Os boletos tem o nome boleto-nossoNumero.pdf



## Publication

Esse plugin tem a sua publicação oficial no diretório de plugins do  **WordPress** e em nosso **Git**. Certifique de que está utilizando a versão oficial.


## Funcionalidades
+**1.** Parcelamento do Pix (Pro)
+**2.** Parcelamento do Boleto (Pro)
+**3.** Integração PIX
+**4.** Gráfico de vendas (Pro)
+**5.** Baixa automática por PIX (Pro)
+**6.** Parametrização de vencimento de boleto (Pro)
+**7.** Status do Plugin
+**8.** Tela de Log de Erros (Pro)
+**9.** Integração por Webhook
+**10.** Integração por Extrato Bancário (Pro)
+**11.** Segunda via de boleto (Pro)
+**12.** + Segurança (Pro)
+**13.** + Integrações (Pro)
+**14.** + Telas (Pro)
+**15.** + Funcionalidades (Pro)
+**16.** + Estável (Pro)
+**17.** Integração com Dokan (Pro)
+**18.** Integração com Dokan Multistore (Pro)
+**29.** Integração com Woocommerce Multistore (Pro)
+**20.** Integração com Woocommerce Subscriptions (Pro)
+**21.** Integração com Woocommerce Subscriptions Pro (Pro)
+**22.** Integração com Woocommerce Subscriptions Multistore (Pro)
+**23.** Integração com Enhancer for WooCommerce Subscriptions (Pro)
+**24.** Boleto Pix


## Erros
**Erros comuns:** reporte-os em nosso Git ou na distribuição do Wordpress para que possamos realizar a correção.

**Falhas de segurança:** Envie para o nosso e-mail, não faça divulgação dessas informações e não as utilize para prejudicar terceiros.

[developers@diletec.com.br](mailto:developers@diletec.com.br)
[cp.diletec.com.br](cp.diletec.com.br)


== Changelog ==

= 1.0 =
* Lançamento do plugin.

= 1.1 =
* Correções

= 1.2 =
* Melhorias em códigos

= 1.3 =
* Adicionado controle de log

= 1.4 =
* Correção do status do boleto
* Validações de campos antes da venda
* Integração de campos quando utilizado o Brazilian

= 1.4.1 =
* Atualização de API Banco Inter

= 1.4.2 =
* Tempo de execução
* Validação de campos com e sem outros Plugins

= 1.4.3 =
* Correção de cURL novas regras Inter
* Validação de PHP 7.4

= 1.4.4 =
* Correção de número de endereço
* Validação de PHP 8
* Wordpress 5.6

= 1.5.0 =
* PIX do Banco Central que logo será migrado para a API do banco Inter, mas acreditamos que o banco Inter ainda vá demorar alguns meses para liberar a API deles.

= 1.5.1 =
* Correções do PIX
* Melhorias em consumo

= 1.5.2 =
* Remoção do código digitável PIX
* Remoção do código digitável Boleto
* Melhorias de informações
* Remoção do Password na Integração (Atualize suas chaves junto ao Banco Inter)

= 1.6.0 =
* Correção de status do pedido
* Correção no arquivo de tradução
* Correção na Cron
* Painel do Plugin
* Link de ajuda online
* Lançamento do PRO
* Link do Pedido por e-mail

= 1.6.1 =
* Correção de Segurança

= 1.6.2 =
* Correções de ativação
* Correção de E-mail
* Correção de localização de arquivos
* Melhorias de segurança
* Melhorias de desempenho
* Correções de diretórios
* Criação do método responsável por verificar erros
* Link de boleto por e-mail
* Link do Pix por e-mail
* Botão de copiar o Pix

= 1.6.3 =
* Mudança nos Métodos de GET e POST
* Adição copiar código PIX
* Melhoria QR Code Pix
* Adição copiar código Boleto

= 1.6.4 =
* Melhorias no e-mail
* Correções no PIX

= 1.6.5 =
* Fix: Class Woocommerce desativada não quebra o site

= 1.6.6 =
* Fix: Class Woocommerce alert

= 1.6.7 =
* Fix: envio de E-mail de pedidos por Pix

= 1.7.0 =
* Fix: Conflito de Request com o WP e Plugins
* Feature: Adição de campo de clientId
* Feature: Adição de campo de clientSecret

= 1.7.1 =
* Fix: Cron

= 1.7.2 =
* Fix: Ads Banner

= 1.7.3 =
* Fix: Número da versão

= 1.7.4 =
* Feature: Adição de pre suporte ao WhatsApp API

= 1.7.5 =
* Fix: Alerta de conexão com o Banco Inter

= 1.7.6 =
* Fix: Melhoria em alerta de conexão com o Banco Inter

= 1.7.7 =
* Fix: Melhoria de organização de arquivos

= 1.8.0 =
* Feature: Melhorias de código

= 1.8.1 =
* Feature: Pix Parcelado
* Feature: Boleto Parcelado

= 2.1.0 =
* Feature: Boleto Pix