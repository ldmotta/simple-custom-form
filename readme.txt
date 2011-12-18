=== WP Simple Custom Form ===

Contributors: ldmotta

Donate link: http://motanet.com.br

Tags: custom form, form, contato

Requires at least: 3.0

Tested up to: 3.0

Stable tag: 1.0.0


== Description ==


Este plugin cria formulários customizados, que podem ser inseridos em uma página qualquer através de uma marcação simples.
Os formulários inseridos no corpo do post ou página, são criados com uma marcação html customizada, ao gosto do usuário. 
Poderão ser inseridos quantos formulários forem necessários.

* Para maiores informações, visite o nosso site em http://motanet.com.br/wp-simple-custom-form/ ou http://siscomserv.com.br


== Installation ==

1. Faça upload do "WP Simple Custom Form" para a pasta `/wp-content/plugins/`  (mantenha a pasta original do plugin).

2. Ative o plugin na interface de 'Plugins' do WordPress.

3. Na Aba de opções configure/adicione novos formulários, preenchendo as seguintes informações:
   - Descrição: Descrição simples para identificação do formulário na lista de formulários.
   - HTML do formulário: Marcação HTML do formulário que será utilizado na página ou post, não precisa adicionar a tag 'form' nem botão submit, apenas crie as marcações para os campos do formulário com div, label, input etc.
   - Corpo do e-mail: Texto que será enviado no corpo do e-mail para o administrador.
   - E-mail de destino: E-mail para onde se quer enviar os dados do formulário digitados pelo usuário.

4. Crie uma página/post e inclua a marcação [wp_custom_form x] onde 'x' é o número do formulário adicionado pelo plugin.

== License ==

This file is part of WP Simple Custom Form
WP Simple Custom Form is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published
by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
WP Simple Custom Form is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with WP Simple Custom Form. If not, see <http://www.gnu.org/licenses/>.

== Frequently Asked Questions ==

= Can I suggest a feature for the plugin? =
Of course, visit [WP Simple Custom Form Home Page](http://motanet.com.br/wp-simple-custom-form/)

== Changelog ==

= 1.0 =
* Criação do plugin para criar formulários customizados.
* Corrigido o problema de não renderizar o html na pág ou post 

== Screenshots ==
1. Menu na área administrativa
2. Página de listagem dos formulários criados
3. Página de inclusão de novos formulários
4. Página de edição de formulário
