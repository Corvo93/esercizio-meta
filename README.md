# esercizio-meta
Creazione di una classe per la gestione di file di configurazione tramite XML

La classe deve leggere un file XML contenente dei parametri di configurazione. Se il design lo richiede possono essere create più classi che collaborano tra di loro.

File xml_per_prova.zip

Il file XML ha queste caratteristiche:

- il tag glz:Import indica che deve essere caricato un altro file XML, i file importati potrebbero contenere altre direttive di importazione.

es.
<?xml version="1.0" encoding="utf-8"?>
<glz:Config xmlns:glz="http://www.glizy.org/dtd/1.0/">
    <glz:Import src="import1.xml" />
    ....
</glz:Config>


- il tag glz:Group indica un gruppo di parametri, il gruppo viene aggiunto al nome del parametro che racchiude, possono esserci glz:Group annidati.
es.
<?xml version="1.0" encoding="utf-8"?>
<glz:Config xmlns:glz="http://www.glizy.org/dtd/1.0/">
    ....
    <glz:Group name="group">
        <glz:Group name="innergroup">
            <glz:Param name="value1" value="abc" />
            <glz:Param name="value2" value="def" />
        </glz:Group>
    </glz:Group>
    ....
</glz:Config>

- il tag glz:Param indica il parametro, quanto il parametro è un numero o un booleano c'è da fare il casting.

I valori con testo lungo o che contengono dei tag al loro interno vengono messi all'interno della direttiva XML CDATA.

Quando ci sono più glz:Param con lo stesso name, vale l'ultima occorrenza trovata, l'unica eccezione è quando il nome finisce con [] (es abcdef[]) in questo caso il valore diventa un array.

es.
<?xml version="1.0" encoding="utf-8"?>
<glz:Config xmlns:glz="http://www.glizy.org/dtd/1.0/">
    ....
    <glz:Param name="value3" value="prova1" />
    <glz:Param name="value3" value="prova5" />

    <glz:Param name="arrayvalue[]" value="abc" />
    <glz:Param name="arrayvalue[]" value="def" />

    <glz:Param name="longtext"><![CDATA[
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Magna laus. Cave putes quicquam esse verius. Hoc etsi multimodis reprehendi potest, tamen accipio, quod dant. Duo Reges: constructio interrete. </p>
    ]]></glz:Param>
    ....
</glz:Config>

La classe ha un metodo get per leggere i valori, es:
$config->get('chiave da leggere');

Il file di configurazione finale può essere composto da molti XML, questo potrebbe comportare un rallentamento nella procedura di parsing. 

E' necessario gestire un meccanismo di caching, in modo da non eseguire il parsing se il file è già stato parsato precedentemente, naturalmente eseguendo le opportune verifiche se il file principale è stato modificato successivamente al parsing.
Vengono riportate  le coppie chiave/valori del risultato del parsing del file myConfig.xml. Possono essere usati questi risultati come verifica della corretta implementazione della classe.

archive = archive/ (stringa)
imageCache = cache/ (stringa)
mode = GDLIB (stringa)
jpg_compression = 90 (intero)
thumbnail/width = 400 (intero)
thumbnail/height = 400 (intero)
thumbnail/crop = false (booleano)
thumbnail/filters = [FlipHorizontal, BlackAndWhite] (array)
medium/width = 400 (intero)
medium/height = 400 (intero)
medium/crop = true (booleano)
full/width = 800 (intero)
full/height = 600 (intero)
full/crop = false (booleano)
arrayvalue = [abc, def] (array)
group/innergroup/value1 = abc (stringa)
group/innergroup/value2 = def (stringa)
longtext = <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Magna laus. Cave putes quicquam esse verius. Hoc etsi multimodis reprehendi potest, tamen accipio, quod dant. Duo Reges: constructio interrete. </p> (stringa)
value1 = prova3 (stringa)
value2 = prova4 (stringa)
value3 = prova5 (stringa)
