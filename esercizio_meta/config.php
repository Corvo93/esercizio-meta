<?php

class Config {
	
	//proprietà della classe
	
	private $xml_path;
	public $dictionary = array();
	private const CACHE = "cache/cache.json";
	
	//costruttore
	
	public function __construct($xml) {
		$this->xml_path = $xml;
		$cached = $this->isCached();
		if (is_array($cached)) {
			$this->dictionary = $cached;
		} else {
			$this->parse();
			$this->cacheInsert();
			}
	}
	
	/*
	La funzione parseParam viene utilizzata per analizzare un nodo XML e inserire le informazioni contenute in esso nel dizionario di configurazione ($this->dictionary)
	$isGrouped (parametro opzionale) indica se il nodo contiene informazioni raggruppate. 
	*/
	
	private function parseParam($node, $isGrouped = false) {
		if (isset($node->firstChild)) {
			if($isGrouped) {
				return $node->firstChild->data;
			} else {
				$this->dictionary[$node->getAttribute("name")] = $node->firstChild->data;
				return 0;
			}
		}
		if ($isGrouped) {
			return $this->parseControl($node->getAttribute("value"));
		} else {
			if (self::endsWith($node->getAttribute("name"), "[]")) {
				if (isset($this->dictionary[$node->getAttribute('name')]))
				$this->dictionary[$node->getAttribute('name')][] = $this->parseControl($node->getAttribute('value'));
			 else 
				$this->dictionary[$node->getAttribute('name')] = array($this->parseControl($node->getAttribute('value')));
			} else {
				$this->dictionary[$node->getAttribute('name')] = $this->parseControl($node->getAttribute('value'));
			}
		}
	}
	
	//La funzione parseGroup analizza un nodo XML di tipo "Group" e tutti i suoi nodi figli, che possono essere di tipo "Group" o "Param"
	
	private function parseGroup($node, $name) {
		foreach ($node->childNodes as $child) {
			if ($child->localName == "Group") {
				//chiamata ricorsiva 
				$this->parseGroup($child, $name . $child->getAttribute("name") . "/");
			} else {
				if ($child->localName == "Param") {
					if (self::endsWith($child->getAttribute('name'), '[]')) {
						if (isset($this->dictionary[$name . $child->getAttribute('name')]))
							$this->dictionary[$name . $child->getAttribute('name')][] = $this->parseParam($child, true);
						else
							$this->dictionary[$name . $child->getAttribute('name')] = array($this->parseParam($child, true));

                } else {
                    $this->dictionary[$name . $child->getAttribute('name')] = $this->parseParam($child, true);
					}
				}
			}
				
		}
	}
	
	/*
	La funzione parseImport viene richiamata quando viene trovato un tag <Import> all'interno del file XML. 
	Questo tag contiene l'attributo "src" che indica il percorso del file XML che deve essere importato.
	La funzione recupera il percorso assoluto della directory contenente il file XML corrente 
	e usa il percorso specificato nell'attributo "src" per accedere al file da importare.
	*/
	
	private function parseImport($node) {
		//trovo il percorso assoluto della directory contenente il file xml specificato
		$directory = realpath(dirname($this->xml_path));
		try {
			$this->parse($directory . "/" . $node->getAttribute("src"));
		} catch(ErrorExpection $e) {
			print ("Errore nell'importazione del file" . $node->getAttribute("src"). ". Errore " . $e->getMessage() . "\n");
		}
	}
	
	
	
	/*
	La funzione parse si occupa di analizzare il file XML di configurazione. 
	In particolare, carica il file XML specificato nel costruttore, 
	individua il tag radice "Config" e scandisce i suoi figli alla ricerca dei tag "Group", "Param" e "Import".
	*/
	
	private function parse($alt_path = null){
		$doc = new DOMDocument();
		if($doc->load($alt_path ?? $this->xml_path)) {
			$config = $doc->getElementsByTagName("Config")[0];
			foreach ($config->childNodes as $child) {
				if ($child->localName == "Group") {
					$this->parseGroup($child, $child->getAttribute("name") . "/");
				} else {
					if ($child->localName == "Param") {
						$this->parseParam($child);
					} else {
						if ($child->localName == "Import") {
							$this->parseImport($child);
						}
					}
				}
			}
		}
	}
	
	//La funzione parseControl controlla la stringa in input e restituisce il valore corrispondente

	private function parseControl($input) {
		if (is_numeric($input)) {
			return intval($input);
		} else {
			if (strcasecmp($input, "true") === 0) {
				return true;
			} else {
				if (strcasecmp($input, "false") === 0) {
				return false;
			} else {
				return $input;
				}
			}
		}
	}
	
	//funzione che controlla se l'hash del file selezionato è già presente nella cache
	
	private function isCached() {
		if (file_exists(self::CACHE)) {
			$hash = hash_file("md5", $this->xml_path);
			$cache = json_decode(file_get_contents(self::CACHE), true);
			if (isset($cache[$hash])) {
				return $cache[$hash];
			} 
		}
		return false;
	}
	
	
	
	//funzione che inserisce l'hash nella cache
	
	private function cacheInsert() {
		if (file_exists(self::CACHE)) {
			/*memorizzo i dati decodificati del file json dove sono contenuti gli hash. Il secondo parametro viene impostato a true
			per ottenere un array associativo e non un oggetto*/
			$cache = json_decode(file_get_contents(self::CACHE), true);
		} else {
			$cache = null;
		}
		//eseguo l'hash del percorso del file di configurazione
		$hash = hash_file("md5", $this->xml_path);
		if (empty($cache)) {
			$cache = array ($hash => $this->dictionary);
		} else {
			$cache[$hash] = $this->dictionary;
		}
		//scrivo il nuovo record nel file json	
		file_put_contents(self::CACHE, json_encode($cache));
	}
	
	//funzione per svuotare la cache
	
	public function clearCache() {
		if (file_exists(self::CACHE)) {
			unlink(self::CACHE);
		}
	}
	
	//La funzione endsWith verifica se una stringa termina con un'altra stringa
	
	public static function endsWith($haystack, $needle): bool {
        $length = strlen($needle);
        if (!$length) {
            return true;
        }
        return substr($haystack, -$length) === $needle;
    }	
		
}