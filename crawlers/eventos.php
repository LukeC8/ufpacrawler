<?php
//header('Content-Type: text/html; charset=CP1252');
echo "/**************************************************************<br>";
echo "Aplicativo ufpa - lado servidor<br>";
echo "<br>";
echo "<blockquote>Descricao do arquivo:<br>";
echo "<blockquote>	- Le a pagina de eventos e adiciona";
echo "<br>	- os eventos mais recentes no banco de dados</blockquote>";
echo "<br>	Autor: lucas.correa@itec.ufpa.br";
echo "<br>	Criado em: Qua 05 de Fev de 2014";
echo "<br>	Versao do script: 2.1";
echo "<br>	obs: Script sem uso de operacoes envolvendo arquivos";
echo "</blockquote>";
echo "**************************************************************/<br><br>";

include 'config.php';

$eventos_update[20];

$ch = curl_init();

// informar URL e outras funções ao CURL
curl_setopt($ch, CURLOPT_URL, "http://www.portal.ufpa.br/imprensa/todosEventos.php");
//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Acessar a URL e salvar em uma variavel
$pgeventos = curl_exec($ch);
curl_close($ch);

$texto_limpo = strip_tags($pgeventos, '<li><a></a></li>');
//echo $texto_limpo;

if(!($link = mysql_connect($mysql_host, $mysql_user, $mysql_password)))
{
	echo "conexao com o bd falhou";
	exit;
}

mysql_set_charset('UTF8', $link);

//seleciona base de dados
if(!mysql_select_db($mysql_database,$link))
{
	echo "conexao com o bd falhou";
	exit;
}


if(strstr($texto_limpo, '404') != null)
{
	echo "<br>\nerro ao acessar pagina\n<br>";
}
else
{
	$texto_limpo = strstr($texto_limpo, "Todos");
	$texto_limpo = strstr($texto_limpo, "href=");
	$text_inLines = explode("\n", $texto_limpo);
	for($linha=0, $eventos_atualizados = 0;$linha<100;$linha+=6, $eventos_atualizados++)
	{
		if(strlen($text_inLines[$linha]) < 15) // fim da lista  de eventos (olhar html)
			break;
		$array = explode('"', $text_inLines[$linha]);
		
		$eventos_update[$eventos_atualizados*2] = $array[1];
		
		$eventos_update[($eventos_atualizados*2) + 1] = strstr($text_inLines[$linha + 2], " - ");
		
		//echo $eventos_update[$eventos_atualizados*2];
		//echo $eventos_update[($eventos_atualizados*2) + 1];
		
		//echo "<br><br>";
	}
	
	echo $eventos_atualizados;
	//echo "<br><br>"."eventos_BD"."<br>";
	//limpa todas as linhas da tabela **
	// pensar em um algoritmo melhor para atualizar a tabela
	mysql_query("TRUNCATE eventos");
	
	$query = "SELECT * FROM `eventos` ORDER BY `id` DESC LIMIT 0 , 10";
	
	mysql_query("SET NAMES 'utf8'");
	mysql_query('SET character_set_connection=utf8');
	mysql_query('SET character_set_client=utf8');
	mysql_query('SET character_set_results=utf8');
	
	for($i = 0; $i < $eventos_atualizados; $i++)
	{
		//echo "<br> codigo sql gerado = ";
		$sql = sprintf("INSERT INTO `eventos`(`evento`, `link`) VALUES('%s', '%s')\n", iconv("CP1252", "UTF-8", addslashes($eventos_update[($i*2)+1])), $eventos_update[$i*2]); 
		//echo "<br><br>";
		//---------------------------------------------------------------------
		$result = mysql_query($sql) or die(mysql_error());
			if(!$result)
		{
			echo "erro [03]";
			break;
		}
	}
	
	mysql_close($link);
}

?>