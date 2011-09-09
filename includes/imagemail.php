<?php
class imagemail {
    var $_from          ='';
    var $_to            ='';
    var $_subject       ='';
    var $_message       ='';
    var $_header        ='';
    var $_encoded_attach='';
    var $_limiter       ='';
    var $_cid           ='';
    var $_src_image     ='';
    var $_type          ='';
    var $_style         ='';    
    var $_charset       ='';
    private static $instance = null;

    /**
     * Contrutor da classe: Define as variÃ¡veis $_limiter (limitador de conteÃºdo do email),
     * $_cid (identificador Ãºnico para referÃªncia do conteÃºdo da imagem)
     * @return void
     */
    function  __construct() {
        $this->_limiter = "_sep_". date('YmdHms'). time() . "_sep_";
        $this->_cid = date('YmdHms').'.'.time();
        $this->_charset = 'iso-8859-1';
    }

    /**
     * Define o charset para o titulo e conteÃºdo da mensagem;
     * @return array Array contendo o tÃ­tulo e o conteÃºdo da mensagem
     */
    function set_entity() {
        $content = array($this->_subject, $this->_message);
        array_walk($content, create_function('&$item', "$item=utf8_decode(html_entity_decode($item, ENT_QUOTES, \"$this->_charset\"));"));
        list($this->_subject, $this->_message) = $content;
    }

    /**
     * Cria o header padrnão para inclusnão de imagem no corpo do email
     * @return void
     */
    function make_header () {
        $this->_header  = "From: $this->_from\r\n";
        $this->_header .= "MIME-version: 1.0\r\n";
        if ($this->_style == "image") {
            $this->_header .= "Content-type: multipart/related; boundary=\"$this->_limiter\"\r\n";
        } else {
            $this->_header .="Content-Type: text/html\n";
        }
    }

    /**
     * Cria todo o conteÃºdo do do email
     * @return void
     */
    function make_message () {
        $this->make_header();
        $mensagem = '';
        if ($this->_style == "image") {
            $mensagem .= "--$this->_limiter\r\n";
            $mensagem .= "Content-type: $this->_type; charset=iso-8859-1\r\n";
            $mensagem .= "$this->_message\r\n";
            $mensagem .= "--$this->_limiter\r\n";
            $mensagem .= "Content-type: image/jpeg; name=\"Imagem.jpg\"\r\n";
            $mensagem .= "Content-Transfer-Encoding: base64\r\n";
            $mensagem .= "Content-ID: <$this->_cid>\r\n";
            if($this->_encoded_attach)
                $mensagem .= "\n$this->_encoded_attach\r\n";
            $mensagem .= "--$this->_limiter--\r\n";
        } else {
            $mensagem .= "$this->_message\r\n";
        }
        $this->add_message($mensagem);
    }

    /**
     * Define $_encoded_attach com o encode base64 da imagem
     * @param string $full_image_path Caminho completo da imagem
     * @return void
     */
    private function get_base64 ($full_image_path) {
        $arquivo=fopen($full_image_path,'r');
        $contents = fread($arquivo, filesize($full_image_path));
        $this->_encoded_attach = chunk_split(base64_encode($contents));
        fclose($arquivo);
    }

    /**
     * Define o tipo de conteÃºdo do email
     * @param string $type Tipo de de conteÃºdo que pode ser text ou html, valor parnão 'text'
     * @return void
     */
    function set_type ($type='text') {
        switch ($type) {
            case "text":
                $this->_type = "text/plain";
                break;
            case "html":
                $this->_type = "text/html";
                break;
            default:
                $this->_type = "text/plain";
                break;
        }
    }

    /**
     * Define a variável $_style com o stilo do email
     * @param string $text stylo image ou simple
     */
    public function set_style($text) {
        $this->_style = $text;
    }

    /**
     * Define o src da imagem que serÃ¡ inserida no corpo do email
     * @param string $full_image_path Caminho completo da imagem
     * @param string $alt Texto que serÃ¡ exibido quando o mouse estiver sobre a imagem
     * @return string Tag img com src igual ao cÃ³digo que faz referÃªncia ao conteÃºdo da imagem
     */
    function add_image ($full_image_path='', $alt='') {
        $this->get_base64($full_image_path);
        return "<img src=\"cid:$this->_cid\" alt=\"$alt\" />";
    }

    /**
     * Define a variÃ¡vel $_from com o email do remetente
     * @param <type> $text Email do remetente, aceita o formato Nome <email@email.com>
     * @return void
     */
    public function add_from($text) {
        $this->_from = $text;
    }

    /**
     * Define a variÃ¡vel $_to com o email do destinatÃ¡rio
     * @param string $text Email do destinatÃ¡rio, aceita o formato Nome <email@email.com>
     */
    public function add_to($text) {
        $this->_to = $text;
    }
    
    /**
     * Define a variÃ¡vel $_subject com o assunto do email
     * @param string $text Texto contendo o assuto do email
     * @return void
     */
    public function add_subject($text) {
        $this->_subject = $text;
    }

    /**
     * Define o conteÃºdo da mensagem
     * @param string $text ConteÃºdo da mensagem, aceita o formato text/html ou text/plain
     * @return void
     */
    public function add_message($text) {
        $this->_message = $text;
    }

    /**
     * Formata o conteÃºdo e envia a imagem
     * @return bolean True se o email foi enviado ou false caso haja erro no envio 
     */
    function send () {
        $this->make_message();
        try {
            //$r = @wp_mail( $this->_to, $this->_subject, $this->_message, $this->_header );
            $r = mail($this->_to, $this->_subject, $this->_message, $this->_header);
        } catch (Exception $e) {$r=0;}
        return $r;
    }

    /**
     *
     * Cria uma nova instancia desta classe, e não necessita de um destrutor, pois quando o usuÃ¡rio
     * muda de pÃ¡gina, esta instancia é destruÃ­da automaticamente.
     * @exemplo $objInstancia = GeralClass::getInstance()->metodoQualquer()
     */
    public static function getInstance() {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Evita clonagem desta classe
     */
    public function __clone() {
        trigger_error('Clone não é permitido.', E_USER_ERROR);
    }
    
    /**
     * Evita desserializaÃ§não desta classe
     */
    public function __wakeup() {
        trigger_error('Deserializing não é permitido.', E_USER_ERROR);
    }
}
?>
