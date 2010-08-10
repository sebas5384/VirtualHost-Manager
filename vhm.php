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
 * Checa se existe a pasta ~/.vhm
 */

/*
 * Le os dados do ~/.vhm/conf @TODO
 */



/*
 * Variaveis de Configuração
 */
define("APACHE_PATH", '/Applications/MAMP/conf/apache');
define("APACHE_PATH_SITES", APACHE_PATH . '/sites-enabled/');
define("APACHE_PATH_WWW", '/Applications/MAMP/htdocs/');
define("HOSTS_PATH", '/etc/hosts');
define("APACHE_PORT", 80);


/*
 * MAC - Checa se existe a pasta "sites-enabled" e o include no "httpd.conf"
 */
if (PHP_OS == 'Darwin') {
	// "sites-enabled"
	if ( !file_exists( APACHE_PATH_SITES ) ) {
		mkdir( APACHE_PATH_SITES , 0755 );
	}
	
	// Include da pasta com os vhosts
	$includeVhosts = "NameVirtualHost *:" . APACHE_PORT . "\n\n";
	$includeVhosts .= "# Include the virtual host configuration\n";
	$includeVhosts .= "Include " . APACHE_PATH_SITES . "\n\n";
	
	$conf = file_get_contents( APACHE_PATH . '/httpd.conf' );

	if ( !preg_match( "/ˆInclude " . preg_replace("/\//", "\/", APACHE_PATH_SITES) . "$/", $conf ) ) {
		file_put_contents( APACHE_PATH . '/httpd.conf', $conf . $includeVhosts );
	}
}


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
	}
	
	/*
	 * Configuração do VH
	 */
	$vhConf = "<VirtualHost *:" . APACHE_PORT . ">";
	$vhConf .= "\n\tDocumentRoot " . $vhPath;
	$vhConf .= "\n\tServerName localhost." . $nome;
	$vhConf .= "\n</VirtualHost>"; 
		
	/*
	 * Escreve o arquivo de configuração do VH
	 */
	sudo_write_file( APACHE_PATH_SITES . $nome , $vhConf);
	
	
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
		hosts_configuration($hConf, $hTitulo, $nome);
		
		/*
		 * Escreve o arquivo de configuração do HOSTS
		 */
		sudo_write_file( HOSTS_PATH , $hConf );
	}
	
	/*
	 * Cria o index.html
	 */
	$htmIndex = $vhPath . '/index.php';
	if (!file_exists($htmIndex)) {
		file_put_contents( $htmIndex , '<h1>Virtual Host criado com VHM !!!</h1>' );
	}
	
	/*
	 * Restarta o Apache
	 */
	restart_apache();
	
	/*
	 * Feedback do proceso
	 */
	global $feedback;
	$feedback .= "\n+-----------------------------------------------+\n";
	$feedback .= "|           Dados do Virtual Host               |\n";
	$feedback .= "+-----------------------------------------------+\n";
	$feedback .= " DocumentRoot: $vhPath\n";
	$feedback .= "          URL: http://localhost.$nome\n";
	$feedback .= "+------------------------------------------+\n\n";
}


/**
 * Apaga o Virtual Host @TODO
 */
function delete_vhost( $nome = NULL ) {
	
	$nome = strtolower($nome);
	
	/*
	 * Apaga configuração do VH
	 */
	sudo_remove_file( APACHE_PATH_SITES . $nome );
	
	/*
	 * Apaga configuração do HOSTS
	 */
	
}


/**
 * Restarta o Apache
 */
function restart_apache() {
	switch (PHP_OS) {
		case "Linux":
			system( "sudo service apache2 restart", $result );
		break;
		case "Darwin":
			system( "/Applications/MAMP/Library/bin/apachectl restart", $result );
		break;
	}
}


function hosts_configuration( &$hConf, $hTitulo, $nome ) {
	
	/*
	 * Cria o dominio
	 */
	$hConf .= "\n\n## " . $hTitulo . " ##\n";
	$hConf .= "127.0.0.1	localhost." . $nome;
}

/**
 * Escreve no arquivo com permissão root
 */
function sudo_write_file( $path, $text) {

	exec( "echo '$text' | sudo tee $path", $result );

}

/**
 * Apaga arquivo com permissão root
 */
function sudo_remove_file( $path ) {
	
	exec( "sudo rm -rf $path", $result );
	
}
