<?php
/*
Plugin Name: Wp Simple Custom Form
Plugin URI: http://motanet.com.br/wp-simple-custom-form/
Description: Cria formulários customizados, em uma página ou post, exatamente como descrito pelo desenvolvedor.
Author: Luciano D. Mota
Version: 1.0
Author URI: http://motanet.com.br
*/

// Pre-2.6 compatibility ( From: http://codex.wordpress.org/Determining_Plugin_and_Content_Direct
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

define( template_url    , WP_PLUGIN_URL . '/scf/template/' );
define( DS, '/' );

// Incluir as funções do imagemail
require_once( dirname(__FILE__) . DS . 'includes/imagemail.php' );

class wpCustomForm {
	private static $wpdb;
	private static $info;
	
	/**
	 * inicializar - Função de inicialização, centraliza a definição de filtros/ações
	 *
	 */
	public static function inicializar() {

		global $wpdb;
		
		//Definir ganchos
		add_filter( "the_content", array( "wpCustomForm", "renderForm" ) );
		
		add_action( "admin_menu", array( "wpCustomForm", "adicionarMenu" ) );
		
		//Mapear objetos WP
		wpCustomForm::$wpdb = $wpdb;
		
		//Outros mapeamentos
		wpCustomForm::$info['plugin_fpath'] = dirname( __FILE__ ); 

	}

	/**
	 * instalar - Função de instalação, chamada apenas na instalação do plugin
	 *
	 */
	public static function instalar() {

		if ( is_null(wpCustomForm::$wpdb) ) wpCustomForm::inicializar();
		
		//Criar base de dados apenas se não existir
		$sqlScf = "CREATE TABLE IF NOT EXISTS `".wpCustomForm::$wpdb->prefix."scf` (
			  `id_form` int(11) NOT NULL AUTO_INCREMENT,
			  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `source_form` text NOT NULL,
			  `source_email` text NOT NULL,
			  `status` BOOL NOT NULL,
			  `receiver` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
			  PRIMARY KEY (`id_form`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";

		wpCustomForm::$wpdb->query($sqlScf);
	}

	/**
	 * renderForm - Esta função busca no corpo do post/página, a chave para exibição do 
	 * formulário e armazena o resultado em $form_id
	 *
	 * @param string $post_texto Texto original do post
	 * @return string Texto com alterações feitas
	 */
	public static function renderForm( $post_texto ) {
	
	    // Encontra o padrão [wp_custom_form id], onde id é o índice do formulário
		if( preg_match( '|\[wp_custom_form (\d+) ?\]|', $post_texto, $matches ) ) {
	
		    $form_id = $matches[1];
	
			return wpCustomForm::getForm( $form_id );
	
		}
		
		return $post_texto;
		
	}

    /**
     * getForm - Captura o formulário salvo, atravez do id passado como parametro
     *
     */
	function getForm ( $form = "" ) { 
        
        $message = '';
        
	    if( $_SERVER['REQUEST_METHOD'] == "POST" ) {
	    
	        $sqlSelect = "SELECT * FROM " . wpCustomForm::$wpdb->prefix . "scf WHERE id_form='" . $_POST['id_form'] . "'";   

	        $form_data = wpCustomForm::$wpdb->get_row( $sqlSelect );

	        $source_form    = $form_data->source_form;
	        
	        $source_email   = $form_data->source_email;
	        
	        $receiver       = $form_data->receiver;
	        
	        $description    = $form_data->description;
	        
	        foreach( $_POST as $field => $value ){
            
                $source_email = str_replace( "[" . $field . "]", $value, $source_email );
	        
	        }

            $im = imagemail::getInstance();
            
            $html = sprintf( "%s", $source_email );            

            //Setando as variáveis para envio do email
            $im->add_from( "Fotolab Portal <dani@pensaweb.com.br>" );
            
            $im->add_to( "Destinatário <{$receiver}>" );
            
            $im->add_subject( "{$description}" );
            
            $im->set_type( "html" );
            
            $im->set_style( "simple" );            
            
            $im->add_message( $html );     

            try {
            
                $enviado = $im->send();
            
                if ( $enviado ) $message = "Mensagem enviada com sucesso! obrigado por entrar em contato.";
            
            } catch ( Exception $e ) { $enviado = 0; }
            
	    }
	
	    if ( !$form ) return "";

		$sqlSelect = "SELECT * FROM " . wpCustomForm::$wpdb->prefix . "scf WHERE id_form='" . $form . "' order by id_form desc";

		$form = wpCustomForm::$wpdb->get_row( $sqlSelect );

		if ( !$form->id_form ) return "";
		
		$content_form = stripcslashes( $form->source_form );
		
		$html = '';
		
		if ( $message != '' ) {
		    
		    $html .= "<div class='success'><p>{$message}</p></div>";   
		    
		}
		
		$html .= '<form name="wp_custom_form" class="form-contato" method="post">';
		
		$html .= sprintf( '<input type="hidden" name="id_form" value="%d">%s</form>', $form->id_form, $content_form );
	
		return $html;
	
	}
	
	function listForms ( $form = "") {
	
		global $current_user;
	
		//wp_get_current_user();
		if (isset($_POST['action']) && $_POST['action']=='apagar') {
		
			$str = implode(',', $_POST['form']);
		
			$sqlDelete = "DELETE FROM ".wpCustomForm::$wpdb->prefix."scf WHERE id_form in($str)";
		
			$del = wpCustomForm::$wpdb->query($sqlDelete);
		
		}
		
		$sqlSelect = "SELECT * FROM " . wpCustomForm::$wpdb->prefix . "scf";
		
		if ( $form ) {
		
			$sqlSelect .= " WHERE id_form='" . $form . "'";

		}
		
		$forms = wpCustomForm::$wpdb->get_results( $sqlSelect );

		$html_init = '';

		if ( $del ) {
		
		    $html_init="<div class='updated below-h2' id='message'><p>Formulário excluido.</p></div>";		
		
		}

		if( count( $forms ) ) {
		
			$html_init .= "<form name='action_add_forms' method='post' style='margin-top: 10px'>
			<div class='alignleft actions'  style='margin: 10px 0 10px'>
			<select name='action'>
				<option selected='selected' value='-1'>Ações em massa</option>
				<option value='apagar'>Mover para a lixeira</option>
			</select>
			<input type='submit' class='button-secondary action' id='doaction' name='doaction' value='Aplicar'>
			</div>
			<table class='widefat'><thead><tr>
				<th scope='col' style='width:15px;'></th>
				<th scope='co2'>Form ID</th>
				<th scope='co3'>Descrição</th>
				<th scope='co3'>Ações</th></tr></thead><tbody>";
		
			foreach ($forms as $item) {
			
				if( $current_user->wp_user_level == 10 ) {
			
					if (!$item->id_form)continue;
					
					$id = $item->id_form;
					
					$description = $item->description;
					
					$edit_url = "?page=add_forms&form={$id}&action=edit";
					
					$html_content .= <<<eof
						<tr class="alternate">
						<td>
						<input type="checkbox" name="form[]" value="{$id}" />
						</td>
						<td>{$id}</td>
						<td>{$description}</td>
						<td><a href="{$edit_url}" >Editar</a></td>
						</tr>
						
eof;
				}

			}

			$html = $html_init . $html_content . "</tbody></table></form>";

			echo $html;

		}

		echo "";

	}

	function adicionarMenu () {
	
		add_menu_page( "WP Custom Form", "Custom Forms", "level_10", "wp-custom-form", array( "wpCustomForm", "listForms" ) );

		add_submenu_page( "wp-custom-form", "Wp Custom Form", 'Adicionar novo', "level_0", "add_forms", array( "wpCustomForm", "abaOpcoes" ) );
	
	}
	
	function abaOpcoes () {

		//Predefinidos
		$templateVars['{UPDATED}'] = "";

        $templateVars['{ACTION}'] = isset($_GET['action']) ? $_GET['action'] : 'add';

        $templateVars['{DESCRIPTION}'] = "";
        $templateVars['{SOURCE_FORM}'] = "";
        $templateVars['{SOURCE_EMAIL}'] = "";
        $templateVars['{RECEIVER}'] = "";

		global $current_user;

        $description = htmlentities($_POST['description'], ENT_QUOTES, 'UTF-8');

        $source_form = htmlentities($_POST['source_form'], ENT_QUOTES, 'UTF-8');

        $source_email = htmlentities($_POST['source_email'], ENT_QUOTES, 'UTF-8');

        $receiver = htmlentities($_POST['receiver'], ENT_QUOTES, 'UTF-8');

        if ( isset( $_GET['form'] ) && isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) {

	        $sqlSelect = "SELECT * FROM " . wpCustomForm::$wpdb->prefix . "scf WHERE id_form='" . $_GET['form'] . "'";   

	        $form_data = wpCustomForm::$wpdb->get_row( $sqlSelect );
	        
	        $templateVars['{ID_FORM}'] = $_GET['form'];
	        
            $templateVars['{DESCRIPTION}'] =  stripslashes($form_data->description);

            $templateVars['{SOURCE_FORM}'] =  stripslashes($form_data->source_form);

            $templateVars['{SOURCE_EMAIL}'] =  stripslashes($form_data->source_email);

            $templateVars['{RECEIVER}'] =  stripslashes($form_data->receiver);
            
        }
        
		//Executar operações de salvamento do formulário
		if ( isset($_POST['action']) ) {
		 
		    if ( $_POST['action'] == 'add' ) {

			    $sqlInsert = "INSERT INTO " . wpCustomForm::$wpdb->prefix . "scf (description, source_form, source_email, receiver, status) 
				    VALUES ('" . $description . "', '" . $source_form . "', '" . $source_email . "', '" . $receiver . "', true)";

                wpCustomForm::$wpdb->query( $sqlInsert );

                $last_inserted = wpCustomForm::$wpdb->insert_id;
                
		        $templateVars['{UPDATED}'] = '<div id="message" class="updated fade"><p><strong>';

		        if ( $last_inserted ) {

			        $templateVars['{UPDATED}'] .= "Dados atualizados!";

		        } else {
		
			        $templateVars['{UPDATED}'] .= "Erro ao atualizar dados!";
		
		        }
		
		        $templateVars['{UPDATED}'] .= "</strong></p></div>";

		    } elseif ( $_POST['action'] == 'edit' ) {
		    
                $data = array(
                    'description'  => $description, 
                    'source_form'  => $source_form, 
			        'source_email' => $source_email, 
			        'receiver'     => $receiver 
                );

                $updated = wpCustomForm::$wpdb->update( wpCustomForm::$wpdb->prefix . "scf", $data, array( 'id_form' => $_POST['form'] ) );

		        $templateVars['{UPDATED}'] = '<div id="message" class="updated fade"><p><strong>';

		        if ( $updated ) {

			        $templateVars['{UPDATED}'] .= "Dados atualizados!";

		        } else {
		
			        $templateVars['{UPDATED}'] .= "Não houve alteração nos dados!";
		
		        }
		
		        $templateVars['{UPDATED}'] .= "</strong></p></div>";		    
	            
                $templateVars['{DESCRIPTION}'] =  stripslashes($description);

                $templateVars['{SOURCE_FORM}'] = stripslashes($source_form);

                $templateVars['{SOURCE_EMAIL}'] =  stripslashes($source_email);

                $templateVars['{RECEIVER}'] =  stripslashes($receiver);		    
		        

		    }
        
        }
        
		// Ler arquivo de template usando funções do WP
		$admTpl = file_get_contents( wpCustomForm::$info['plugin_fpath'] . "/admin_tpl.html" );
		
		$admTpl = strtr( $admTpl, $templateVars );

		echo $admTpl;
	}

}

/**
 *  Adicionar HOOKs do WordPress
 */
$mppPluginFile = substr( strrchr( dirname( __FILE__ ), DIRECTORY_SEPARATOR ), 1 ).DIRECTORY_SEPARATOR.basename( __FILE__ );

/** Funcao de instalacao */
register_activation_hook( $mppPluginFile, array( 'wpCustomForm', 'instalar' ) );

/** Funcao de edição */
//register_activation_hook( $mppPluginFile, array( 'wpCustomForm', 'wp-custom-edit' ) );

// filters: init, the_content, 
/** Funcao de inicializacao */
add_filter( 'init', 'wpCustomForm::inicializar' );

?>
