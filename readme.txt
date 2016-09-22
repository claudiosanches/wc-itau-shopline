=== Itau Shopline for WooCommerce ===
Contributors: claudiosanches
Tags: woocommerce, itau, shopline, payment
Requires at least: 4.0
Tested up to: 4.6
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Itau Shopline gateway for WooCommerce

== Description ==

Adicione o Itaú Shopline como método de pagamento em sua loja WooCommerce.

[Itaú Shopline](https://www.itau.com.br/empresas/recebimentos/shopline/) é um método de pagamento brasileiro desenvolvido pelo Itaú.

O plugin Itaú Shopline for WooCommerce foi desenvolvido sem nenhum incentivo do Itaú Shopline ou da Itaú. Nenhum dos desenvolvedores deste plugin possuem vínculos com estas empresas.

Este plugin foi desenvolvido a partir da documentação oficial do Itaú Shopline.

= Compatibilidade =

Compatível desde a versão 2.3.x até 2.6.x do WooCommerce.

É requerido o plugin [WooCommerce Extra Checkout Fields for Brazil](http://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/) para o envio do CPF ou CPNJ para o Itaú, junto com o número do endereço e bairro.

= Instalação =

Confira o nosso guia de instalação e configuração do Itaú Shopline na aba [Installation](http://wordpress.org/plugins/wc-itau-shopline/installation/).

= Dúvidas? =

Você pode esclarecer suas dúvidas usando:

* A nossa sessão de [FAQ](http://wordpress.org/plugins/wc-itau-shopline/faq/).
* Utilizando o nosso [fórum no Github](https://github.com/claudiosmweb/wc-itau-shopline).
* Criando um tópico no [fórum de ajuda do WordPress](http://wordpress.org/support/plugin/wc-itau-shopline).

= Colaborar =

Você pode contribuir com código-fonte em nossa página no [GitHub](https://github.com/claudiosmweb/wc-itau-shopline).

== Installation ==

= Instalação do plugin: =

* Envie os arquivos do plugin para a pasta wp-content/plugins, ou instale usando o instalador de plugins do WordPress.
* Ative o plugin.

= Requerimentos: =

* É necessário possuir uma conta empresa no [Itaú Shopline](https://www.itau.com.br/empresas/recebimentos/shopline/).
* Ter instalada a versão mais recente do [WooCommerce](http://wordpress.org/plugins/woocommerce/).
* E ter instalada a versão mais recente do [WooCommerce Extra Checkout Fields for Brazil](http://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/).

= Configurações do Plugin: =

Com o plugin instalado acesse o admin do WordPress e entre em "WooCommerce" > "Configurações" > "Finalizar compra" > "Itau Shopline".

Habilite o Itau Shopline, preenche os campos de "Código do site" e "Chave de criptografia". Importante observar que este plugin irá funcionar com todos os métodos de pagamento configurados na sua conta do Itau Shopline, caso você necessite ativar cartão de crédito, isso deve ser feito junto ao Itaú e a Rede, sem ter a necessidade de mudar qualquer coisa neste plugin ou na configuração.

Pronto, sua loja já pode receber pagamentos pelo Itau Shopline.

== Frequently Asked Questions ==

= Qual é a licença do plugin? =

Este plugin esta licenciado como GPL.

= O que eu preciso para utilizar este plugin? =

* Ter instalada a versão mais recente do [WooCommerce](http://wordpress.org/plugins/woocommerce/)
* Instalar a versão mais recente do [WooCommerce Extra Checkout Fields for Brazil](http://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/).
* Possuir uma conta empresa no [Itaú Shopline](https://www.itau.com.br/empresas/recebimentos/shopline/).

= Quais são os meios de pagamento que o plugin aceita? =

Serão aceitos todos os meios de pagamento do Itau Shopline, sendo eles boleto bancário, débito em conta e financiamento para clientes Itaú e cartão de crédito (que deve ser habilitado junto ao Itaú e a [Rede](https://www.userede.com.br/)).

= O status do pedido não é alterado automaticamente? =

Sim é alterado automaticamente usando um sistema de sounda que irá consultar os dados dos pedidos no Itau Shopline.

Esta sounda é ativada a cada 3 horas por cron, desta forma é de vital importância que o cron do WordPress esteja funcionando corretamente.

Caso os status não estejam sendo alterados, é recomendado desativar o cron do WordPress e configurar o servidor para rodar o cron acessando o arquivo `wp-cron.php` pelo menos a cada 5 minutos.

= O pedido foi pago e ficou com o status de "processando" e não como "concluído", isto esta certo? =

Sim, esta certo e significa que o plugin esta trabalhando como deveria.

Todo gateway de pagamentos no WooCommerce deve mudar o status do pedido para "processando" no momento que é confirmado o pagamento e nunca deve ser alterado sozinho para "concluído", pois o pedido deve ir apenas para o status "concluído" após ele ter sido entregue.

Para produtos baixáveis a configuração padrão do WooCommerce é permitir o acesso apenas quando o pedido tem o status "concluído", entretanto nas configurações do WooCommerce na aba *Produtos* é possível ativar a opção **"Conceder acesso para download do produto após o pagamento"** e assim liberar o download quando o status do pedido esta como "processando".

= Mais dúvidas relacionadas ao funcionamento do plugin? =

Por favor, caso você tenha algum problema com o funcionamento do plugin, [abra um tópico no fórum do plugin](https://wordpress.org/support/plugin/wc-itau-shopline#postform) com o link arquivo de log (ative ele nas opções do plugin e tente fazer uma compra, depois vá até WooCommerce > Status do Sistema, selecione o log do *itau-shopline* e copie os dados, depois crie um link usando o [pastebin.com](http://pastebin.com) ou o [gist.github.com](http://gist.github.com)), desta forma fica mais rápido para fazer o diagnóstico.

== Screenshots ==

1. Configurações do plugin.
2. Método de pagamento na página de finalizar o pedido.
3. Exemplo dos meios de pagamentos sendo exibidos no Itaú Shopline.

== Changelog ==

= 1.1.0 - 2016/09/22 =

- Adicionada opção para gerar apenas boletos.
- Corrigida uma mensagem de erro incorreta que aparecia na página de pagar o pedido quando feito pelo painel de administração.

= 1.0.0 - 2015/09/03 =

- Versão inicial.

== Upgrade Notice ==

= 1.1.0 =

- Adicionada opção para gerar apenas boletos.
- Corrigida uma mensagem de erro incorreta que aparecia na página de pagar o pedido quando feito pelo painel de administração.
