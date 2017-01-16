<?php
/*-----------------------------------------------
 * Projeto UFPA Scrapers
 *-----------------------------------------------
 * Arquivo: ScraperNews.php
 *
 * Descricao: Classe para acessar os dados da
 *            paginas de noticias, eventos e editais.
 *
 * Autor: lucas.correa[at]itec.ufpa.br
 * Data de Criacao: 13/01/17
 * versao: 1.0
 *
 * Changes:
 *  16/01/17 - $ul_lists => $ulLists
 *  14/01/17 - Add changeURL method
 *
 *----------------------------------------------*/

require_once('News.php');
require_once('Scraper.php');

class ScraperNews extends Scraper
{
    private $news;

    function __construct($url)
    {
        parent::__construct($url);

        $this->news = array();
    }

    function changeURL($newURL)
    {
        parent::changeURL($newURL);

        unset($this->news);

        $this->news = array();
    }

    function scrapePage($dataFormat = Scraper::RETURN_FORMAT_JSON)
    {
        try
        {
            $webPage = $this->getWebPage();

            $this->loadHTML($webPage);

            $ulLists = $this->pageDom->getElementById('todasNoticias')->getElementsByTagName('ul');

            foreach($ulLists as $ul)
                foreach($ul->getElementsByTagName('li') as $li)
                    foreach($li->getElementsByTagName('a') as $a)
                        $this->news[] = new News($a->nodeValue, $a->getAttribute('href'));
        }
        catch (Exception $e)
        {
            echo $e->getMessage() . "\n";
        }
        finally
        {
            if ($dataFormat != Scraper::RETURN_FORMAT_JSON)
                return $this->news; //Array

            return json_encode($this->news); //JSON 
        }
    }
}

?>
