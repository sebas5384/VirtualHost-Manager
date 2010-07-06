#!/usr/bin/php -q
<?php
/*
 * Define STDIN in case if it is not already
 * defined by PHP for some reason
 */
if(!defined("STDIN")) {
	define("STDIN", fopen('php://stdin','r'));
}

/*
 * Checa se vem argumentos
 */
if ( preg_match( '/^\-\-php\=/' , $argv[1] ) ) {
	array_shift($argv);
}
else if (count($argv) == 0) exit;

/*
 * Variaveis de Configuração
 */
define("APACHE_PATH", '/etc/apache2');
define("APACHE_PATH_SITES", APACHE_PATH . '/sites-enabled/');
define("APACHE_PATH_WWW", '/var/www/');
define("HOSTS_PATH", '/etc/hosts');
define("USER_NAME", 'sebas');

/*
 * Feedback
 */
$feedback = "";

/*
 * Se for nw | del | up
 */
switch ($argv[1]) {
	/*
	 * Cria o vhost
	 */
	case 'nw':
	case 'new':	
		new_vhost($argv[2]);
	break;
	
	/*
	 * Deleta o vhost
	 */
	case 'del':
	case 'delete':
		delete_vhost($argv[2]);
	break;
	
	/*
	 * Restarta o Apache
	 */
	case 'up':
		restart_apache();
	break;
}

/*
 * Imprime o Feedback
 */
echo $feedback;


/**
 * Cria o novo Virtual Host.
 */
function new_vhost( $nome = NULL ) {
	
	$nome = strtolower($nome);
	
	/*
	 * Cria a pasta no WWW do Apache
	 */
	$vhPath = APACHE_PATH_WWW . $nome;
	if ( !file_exists( $vhPath ) ) {
		mkdir( $vhPath , 0755 );
		chown($vhPath, USER_NAME);
	}
	
	/*
	 * Configuração do VH
	 */
	$vhConf = "<VirtualHost *:80>";
	$vhConf .= "\n\tDocumentRoot " . $vhPath;
	$vhConf .= "\n\tServerName localhost." . $nome;
	$vhConf .= "\n</VirtualHost>"; 
		
	/*
	 * Escreve o arquivo de configuração do VH
	 */
	file_put_contents( APACHE_PATH_SITES . $nome , $vhConf );
	
	
	/*
	 * Le o arquivo de configuração do HOSTS
	 */
	$hConf = file_get_contents( HOSTS_PATH );
	
	/*
	 * Checa a existencia
	 */
	$hTitulo = strtoupper($nome);
	if ( !preg_match( "/" . $hTitulo . "/" , $hConf ) ) {
		/*
		 * Cria o dominio
		 */
		$hConf .= "\n\n## " . $hTitulo . " ##\n";
		$hConf .= "127.0.0.1	localhost." . $nome;
		
		/*
		 * Escreve o arquivo de configuração do HOSTS
		 */
		file_put_contents( HOSTS_PATH , $hConf );
	}
	
	/*
	 * Cria o index.html
	 */
	$htmIndex = $vhPath . '/index.php';
	file_put_contents( $htmIndex , '<h1>Virtual Host criado com VHM !!!</h1>' );
	chmod( $htmIndex , 0755 );
	chown( $htmIndex , USER_NAME);
	
	/*
	 * Restarta o Apache
	 */
	restart_apache();
	
	/*
	 * Feedback do proceso
	 */
	global $feedback;
	$feedback .= "\n+------------------------------------------+\n";
	$feedback .= "|           Dados do Virtual Host          |\n";
	$feedback .= "+------------------------------------------+\n";
	$feedback .= "DocumentRoot: $vhPath\n";
	$feedback .= "         URL: http://localhost.$nome\n";
	$feedback .= "+------------------------------------------+\n\n";
}


/**
 * Restarta o Apache
 */
function restart_apache() {
	exec("service apache2 restart");
}
