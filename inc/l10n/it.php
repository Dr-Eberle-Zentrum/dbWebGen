<?php
    $_L10N = array(
        'boolean-field.default.yes' => 'Sì',
        'boolean-field.default.no' => 'No',

        'chart-type.annotated-timeline' => 'Cronologia annotata',
        'chart-type.bar' => 'Grafico a barre',
        'chart-type.candlestick' => 'Grafico a candele',
        'chart-type.geo' => 'Mappa geografica',
        'chart-type.leaflet' => 'Mappa Leaflet',
        'chart-type.network-visjs' => 'Rete (vis.js)',
        'chart-type.pie' => 'Grafico a torta',
        'chart-type.sankey' => 'Diagramma di Sankey',
        'chart-type.table' => 'Tabella',
        'chart-type.timeline' => 'Cronologia',
        'chart-type.plaintext' => 'Testo non formattato',
        'chart-type.sna' => 'Analisi di rete',
        'chart-type.treemap' => 'Treemap (diagramma a riquadri)',
        'chart-type.custom' => 'Grafico Google personalizzato',
        'chart-type.graph3d-visjs' => 'Grafico 3D (vis.js)',

        'chart.empty-result' => 'Il risultato della query è vuoto, quindi non viene visualizzata alcuna rappresentazione',

        'chart.plaintext.settings' => <<<HTML
            <p>Genera un output di testo semplice del risultato della query. Viene mostrata solo la prima colonna della prima riga del risultato, indipendentemente dall'aspetto del risultato della query.</p>
HTML
        ,

        'chart.annotated-timeline.settings' => <<<HTML
            <p>Genera una cronologia annotata. La prima colonna deve essere in formato data, tutte le colonne successive devono essere numeriche (<a href="https://developers.google.com/chart/interactive/docs/gallery/annotationchart#data-format" target="_blank">Informazioni</a>).</p>
            <div class="form-group">
            <label class="control-label">Opzioni</label>
            <div class='checkbox top-margin-zero'>
                <label>$1 Mostra scala separata per la seconda cronologia</label>
            </div>
            </div>
HTML
        ,

        'chart.bar.settings' => <<<HTML
            <p>Genera un grafico a barre. Le intestazioni dei gruppi devono essere nella prima colonna, seguite da una colonna per ogni caratteristica del gruppo (<a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/barchart#data-format">Dettagli</a>).</p>
            <div class="form-group">
            <label class="control-label">Orientamento delle barre</label>
            <div>
                <label class="radio-inline">$1 Orizzontale</label>
                <label class="radio-inline">$2 Verticale</label>
            </div>
            </div>
            <div class="form-group">
            <label class="control-label">Barre impilate</label>
            <div>$3</div>
            </div>
HTML
        ,
        'chart.bar.stacking.0' => 'Nessuna impilatura',
        'chart.bar.stacking.1' => 'Valori assoluti impilati',
        'chart.bar.stacking.percent' => 'Valori relativi impilati che sommano al 100%',
        'chart.bar.stacking.relative' => 'Valori relativi impilati che sommano a 1',

        'chart.candlestick.settings' => <<<HTML
            <p>Mostra il valore di apertura e chiusura su una varianza. Richiede <a target=_blank href="https://developers.google.com/chart/interactive/docs/gallery/candlestickchart#data-format">4 colonne</a> nel risultato della query.</p>
HTML
        ,

        'chart.custom.settings' => <<<HTML
            <p>Genera un grafico personalizzato di Google. I pacchetti di grafici Google da includere, il nome della classe del grafico e le opzioni devono essere specificati.</p>
            <p>I tipi di grafico possibili, le colonne richieste e le opzioni disponibili sono descritti nella <a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery">documentazione di Google Charts</a>.</p>
            <div class="form-group">
            <label>Pacchetti di grafici Google</label>
            <div>Inserisci un elenco separato da virgole dei pacchetti da includere, ad esempio <code>corechart</code>:</div>
            $1
            </div>
            <div class="form-group">
            <label>Classe del grafico Google</label>
            <div>Inserisci il nome della classe del grafico, ad esempio <code>LineChart</code>:</div>
            $2
            </div>
HTML
        ,

        'chart.geo.region-helptext' => <<<HELPTEXT
            I seguenti valori possono essere utilizzati qui:
            <ul style="padding-left:1.25em">
            <li><code>world</code> - Intero mondo.</li>
            <li>
              Continente o subcontinente, identificato dal suo
              <a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/geochart#Continent_Hierarchy">codice a 3 cifre</a>, ad esempio <code>011</code> per l'Africa occidentale.
            </li>
            <li>
              Un paese, identificato da un
              <a target="_blank" href="http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2">codice ISO 3166-1 alpha-2</a>,
              ad esempio, <code>AU</code> per l'Australia.
            </li>
            <li>
              Uno stato negli Stati Uniti, identificato da un
              <a target="_blank" href="http://en.wikipedia.org/wiki/ISO_3166-2:US">codice ISO 3166-2:US</a>, ad esempio,
              <code>US-AL</code> per l'Alabama.
            </li>
            </ul>
HELPTEXT
        ,

        'chart.geo.settings' => <<<HTML
            <p>Genera una mappa di un paese, un continente o una regione con marcatori o aree colorate, a seconda del tipo di rappresentazione.</p>
            <div class="form-group">
            <label class="control-label">Tipo di rappresentazione</label>
            <div class="radio"  style="margin-top:0">
                <label class="">$1 <i>Marcatori</i> &mdash; Cerchi per regioni, scalati in base ai valori forniti (<a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/geochart#markers-mode-format">colonne richieste</a>)</label>
            </div>
            <div class="radio">
                <label class="">$2 <i>Regioni</i> &mdash; Colora intere regioni (<a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/geochart#regions-mode-format">colonne richieste</a>)</label>
            </div>
            <div class="radio">
                <label class="">$3 <i>Testo</i> &mdash; Etichetta le regioni con testo come "Asia" o "Russia" (<a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/geochart#text-mode-format">colonne richieste</a>)</label>
            </div>
            </div>
            <div class="form-group">
            <label for="$4" class="control-label">Regione rappresentata $5</label> $6
            </div>
HTML
        ,

        'chart.google-base.settings' => <<<HTML
            <div class="form-group">
            <label class="control-label">Altre opzioni (JSON o oggetto letterale JavaScript)</label>
            <p>$1</p>
            <label class="control-label">JavaScript dopo il rendering</label>
            <p>$2</p>
            </div>
HTML
        ,

        'chart.graph3d-visjs.settings' => <<<HTML
            <p>Genera un grafico tridimensionale con diverse modalità di rappresentazione possibili.</p>
            <p>L'ordine delle colonne nel risultato deve essere il seguente (i nomi delle prime tre colonne saranno utilizzati come titoli degli assi x, y e z):
            <ol style="padding-left:1.25em">
                <li>Valore dell'asse x (numerico o testo)</li>
                <li>Valore dell'asse y (numerico o testo)</li>
                <li>Valore dell'asse z (numerico o testo)</li>
                <li>Stile del punto dati (valore obbligatorio per la scala dei colori o valore di dimensione per la scala delle dimensioni, altrimenti ignorato)</li>
                <li>Tooltip (testo opzionale per il tooltip quando il cursore del mouse è sopra il punto dati)</li>
                <li>Gruppo (valore opzionale del gruppo dell'oggetto dati; se specificato, viene utilizzato per l'animazione)</li>
            </ol>
            </p>
            <div class="form-group">
            <label class="control-label">Modalità di rappresentazione</label>
            <p>$1</p>
            <label class="control-label">Opzioni personalizzate (oggetto JavaScript) $2</label>
            <p>$3</p>
            </div>
HTML
        ,
        'chart.graph3d-visjs.options.help-head' => 'Opzioni di visualizzazione',
        'chart.graph3d-visjs.options.help-body' => 'Le opzioni di visualizzazione disponibili si trovano nella <a target="_blank" href="https://visjs.github.io/vis-graph3d/docs/graph3d/index.html#Configuration_Options">documentazione Graph3d di vis.js</a>',
        'chart.graph3d-visjs.style.bar' => 'Barre',
        'chart.graph3d-visjs.style.bar-color' => 'Barre con scala di colori',
        'chart.graph3d-visjs.style.bar-size' => 'Barre con scala di dimensioni',
        'chart.graph3d-visjs.style.dot' => 'Sfere',
        'chart.graph3d-visjs.style.dot-line' => 'Sfere con linee',
        'chart.graph3d-visjs.style.dot-color' => 'Sfere con scala di colori',
        'chart.graph3d-visjs.style.dot-size' => 'Sfere con scala di dimensioni',
        'chart.graph3d-visjs.style.line' => 'Linee',
        'chart.graph3d-visjs.style.grid' => 'Griglia',
        'chart.graph3d-visjs.style.surface' => 'Superficie',
        
        'chart.leaflet.settings' => <<<HTML
            <p><a target="_blank" href="http://leafletjs.com/">Leaflet</a> genera mappe interattive adatte ai dispositivi mobili.</p>
            <div class='form-group'>
            <label class="control-label">Formato delle coordinate</label>
            <div class="radio"  style="margin-top:0">
                <label class="">$1 <i>Coordinate puntuali</i> &mdash; le prime due colonne devono indicare latitudine (<i>y</i>) e longitudine (<i>x</i>)</label>
            </div>
            <div class="radio"  style="margin-top:0">
                <label class="">$2 <i>Well-Known-Text</i> &mdash; la prima colonna contiene una <a target="_blank" href="https://it.wikipedia.org/wiki/Well-known_text">rappresentazione WKT</a> (questo consente, oltre ai punti, altre geometrie come poligoni)</label>
            </div>
            <p>Tutte le altre colonne vengono visualizzate in una finestra popup per il marker del rispettivo record. Verranno mostrati solo i record con coordinate non vuote.</p>
            </div>
            <div class='form-group'>
            <label class="control-label">Fornitore di tessere della mappa</label>
            <p>$3</p>
            <div>Modello URL personalizzato per le tessere della mappa (opzionale; sovrascrive la selezione sopra):</div>
            <div>$4</div>
            </div>
            <div class="form-group">
            <label class="control-label">Opzioni di visualizzazione</label>
            <div class='checkbox top-margin-zero'><label>$5 Scala</label></div>
            <div class='checkbox'><label>$6 Mappa di panoramica</label></div>
            <div class='checkbox'>Fattore massimo di zoom (vuoto per valore predefinito): $7</div>
            <div class='checkbox'>Informazioni sulla licenza (HTML): $8</div>
            <label class="control-label">Sistema di riferimento delle coordinate</label>
            <p>Le coordinate del risultato della query devono eventualmente essere trasformate in questo sistema di riferimento.</p>
            <div class='form-group'>$9</div>
            </div>
            <div class='form-group'>
            <label class="control-label">Codice JavaScript aggiuntivo</label>
            <p>$10</p>
            </div>
HTML
        ,
        'chart.leaflet.no-data' => '<b>Attenzione:</b> La query non ha restituito risultati.',

        'chart.network-visjs.options-help' => 'Personalizza questo oggetto JSON per definire opzioni di rete personalizzate (vedi <a target="_blank" href="http://visjs.org/docs/network/#options">documentazione</a>).',
        'chart.network-visjs.nodequery-help' => <<<HTML
            <p>Query SQL che fornisce informazioni sui nodi (opzionale). Le colonne devono essere denominate come segue:</p>
            <ol class='columns'>
            <li><code>id</code>ID del nodo (stringa o intero)</li>
            <li><code>label</code>Nome del nodo (stringa)</li>
            <li><code>options</code>: <a target="_blank" href="http://visjs.org/docs/network/nodes.html">Opzioni del nodo</a> (oggetto JSON) - opzionale; definisce opzioni individuali per ogni nodo. Le opzioni individuali hanno priorità rispetto alle opzioni generali dei nodi specificate in "Opzioni personalizzate".</li>
            </ol>
HTML
        ,
        'chart.network-visjs.settings' => <<<HTML
            <p>Genera un grafo di rete. Il risultato della query deve essere un elenco di archi con le seguenti colonne:</p>
            <ol class='columns'>
            <li><code>source</code>: ID del nodo sorgente (stringa o intero)</li>
            <li><code>target</code>: ID del nodo di destinazione (stringa o intero)</li>
            <li><code>weight</code>: Peso dell'arco (opzionale), corrisponde alla larghezza dell'arco in pixel (valore numerico), valore predefinito: 1</li>
            <li><code>label</code>: Etichetta dell'arco (stringa) - opzionale</li>
            <li><code>options</code>: <a target="_blank" href="http://visjs.org/docs/network/edges.html">Opzioni dell'arco</a> (oggetto JSON) - opzionale; definisce opzioni individuali per ogni arco. Le opzioni individuali hanno priorità rispetto alle opzioni generali degli archi specificate in "Opzioni personalizzate".</li>
            </ol>
            <p><a target="_blank" href="http://ionicons.com/">ionicons</a> sono supportate come simboli dei nodi.</p>
            <label class='control-label'>Query dei nodi $1</label>
            <p>$2</p>
            <div class='checkbox top-margin-zero'>
            <label>$3 Rimuovi nodi che non sono presenti nel risultato della query dei nodi</label>
            </div>
            <label class='control-label'>Opzioni personalizzate $4</label>
            <p>$5</p>
HTML
        ,
        'chart.network-visjs.node-query-invalid' => 'Query dei nodi non valida. Sono consentite solo query SELECT. Query ignorata.',
        'chart.network-visjs.node-query-prep' => 'Preparazione della query dei nodi fallita.',
        'chart.network-visjs.node-query-exec' => 'Esecuzione della query dei nodi fallita.',
        'chart.network-visjs.stabilizing-info' => 'La rete si sta ancora stabilizzando, ma può già essere utilizzata.',
        'chart.network-visjs.stabilizing-stop' => 'Clicca qui per interrompere la stabilizzazione.',

        'chart.pie.settings' => <<<HTML
            <p>Genera un grafico a torta. Le etichette per le sezioni della torta devono essere nella prima colonna. La seconda colonna deve contenere valori numerici (<a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/piechart#data-format">Dettagli</a>).</p>
            <div class="form-group">
            <label class="control-label">Opzioni di visualizzazione</label>
            <div class='checkbox top-margin-zero'><label>$1 Rappresentazione 3D</label></div>
            <div class='checkbox'><label>$2 Donut invece di torta (ignorato se 3D attivato)</label></div>
            </div>
            <div class="form-group">
            <label class="control-label">Etichetta delle sezioni della torta</label>
            <div>$3</div>
            </div>
            <div class="form-group">
            <label class="control-label">Posizionamento della legenda</label>
            <div>$4</div>
            </div>
HTML
        ,
        'chart.pie.pie-slice-text.percentage' => 'Percentuale',
        'chart.pie.pie-slice-text.label' => 'Titolo (1ª colonna del risultato)',
        'chart.pie.pie-slice-text.value' => 'Valore assoluto (2ª colonna del risultato)',
        'chart.pie.pie-slice-text.none' => 'Nessuno',
        'chart.pie.legend-position.bottom' => 'Sotto la torta',
        'chart.pie.legend-position.labeled' => 'Linee verso la torta',
        'chart.pie.legend-position.left' => 'A sinistra della torta',
        'chart.pie.legend-position.none' => 'Nascondi legenda',
        'chart.pie.legend-position.right' => 'A destra della torta',
        'chart.pie.legend-position.top' => 'Sopra la torta',

        'chart.sankey.settings' => <<<HTML
            <p>Genera un diagramma di flusso tra due insiemi di valori.</p>
            <p><a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/sankey#data-format">Colonne richieste</a>:
            <ul class='columns'>
            <li>1. Valore sorgente (stringa)</li>
            <li>2. Valore di destinazione (stringa)</li>
            <li>3. Peso (numerico)</li>
            </ul>
            </p>

HTML
        ,

        'chart.table.settings' => <<<HTML
            <p>Genera una tabella con dati.</p>
            <div class="form-group">
            <label class="control-label">Opzioni</label>
            <div class='checkbox top-margin-zero'>
                <label>$1 Consenti HTML nelle celle</label>
            </div>
            </div>
HTML

        , 'chart.sna.settings' => <<<HTML
            <p>Genera una panoramica delle misure di centralità dei nodi in un grafo di rete non orientato.</p>
            <p>Il risultato della query deve essere un elenco di archi con le seguenti colonne:</p>
            <ol class='columns'>
            <li><code>source</code>: ID del nodo sorgente (stringa o intero)</li>
            <li><code>target</code>: ID del nodo di destinazione (stringa o intero)</li>
            </ol>
            <label class='control-label'>Query dei nodi $1</label>
            <p>$2</p>
            <!--<div class='checkbox top-margin-zero'>
            <label>$3 Rimuovi nodi che non sono presenti nel risultato della query dei nodi</label>
            </div>-->
            <div class="form-group">
            <label class="control-label">Opzioni</label>
            <div class='checkbox top-margin-zero'>
                <label>$3 Consenti HTML nelle etichette dei nodi</label>
            </div>
            </div>
            <div class="form-group">
            <label>Etichetta della colonna dei nodi nella tabella dei risultati:</label>
            $4
            </div>
            <div class="form-group">
            <label>Ordina la tabella dei risultati per:</label>
            $5
            </div>
            <div class="form-group">
            <label>Limita l'elenco dei risultati</label>
            <div>Inserisci un numero per limitare l'elenco dei risultati o lascia vuoto per mostrare tutti i nodi:</div>
            $6
            </div>
HTML
        ,

        'chart.sna.nodequery-help' => <<<HTML
            <p>Query SQL che fornisce i nomi dei nodi da visualizzare al posto degli ID (opzionale). Le colonne devono essere:</p>
            <ol class='columns'>
            <li><code>id</code>ID del nodo (stringa o intero), corrisponde agli ID dei nodi <code>source</code>/<code>target</code> nella query di rete sopra</li>
            <li><code>label</code>Nome del nodo (stringa)</li>
            </ol>
HTML
        ,

        'chart.sna.help-link' => 'Clicca qui per visualizzare una spiegazione delle colonne',
        'chart.sna.help-content' => <<<HTML
            <li><b>Centralità di intermediazione</b>:
            Questo valore indica se molti percorsi attraverso la rete passano attraverso questo nodo. I valori di tutti i nodi sono normalizzati nell'intervallo da 0 a 1, cioè il nodo con la centralità di intermediazione più alta nella rete ha il valore 1, mentre quello con la più bassa ha il valore 0. I nodi con un valore alto hanno una maggiore influenza sul flusso di informazioni nella rete, poiché molti percorsi li attraversano. Questi nodi hanno anche maggiori probabilità di collegare parti altrimenti isolate della rete o, al contrario, di dividere una rete se rimossi.</li>
            <li><b>Coefficiente di clustering</b>:
            Il coefficiente di clustering di un nodo indica quanto sono interconnessi i suoi vicini diretti. Il valore rappresenta la proporzione di collegamenti tra i nodi vicini diretti rispetto al numero massimo possibile di connessioni. I nodi con un alto coefficiente di clustering indicano la formazione di clique locali. L'interpretazione di questo coefficiente è più significativa per i nodi con un vicinato più ampio.</li>
            <li><b>Grado</b>:
            Il grado di un nodo indica il numero di nodi vicini direttamente collegati ad esso.</li>
HTML
        ,

        'chart.sna.node-column-label' => 'Nodo',
        'chart.sna.result.betweenness-centrality' => 'Centralità di intermediazione',
        'chart.sna.result.degree-centrality' => 'Grado',
        'chart.sna.result.clustering-coefficient' => 'Coefficiente di clustering',
        'chart.sna.sort-cb' => 'Centralità di intermediazione (decrescente)',
        'chart.sna.sort-cc' => 'Coefficiente di clustering (decrescente)',
        'chart.sna.sort-cd' => 'Grado (decrescente)',
        'chart.sna.sort-node' => 'Nome del nodo (crescente)',

        'chart.timeline.settings' => <<<HTML
        <p>Genera una cronologia scorrevole con sezioni temporali e barre. Le colonne richieste nel risultato della query sono <a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/timeline#data-format">spiegate qui</a>.</p>
        <div class="form-group">
            <label class="control-label">Opzioni</label>
            <div class='checkbox top-margin-zero'>
            <label>$1 Mostra identificatori di riga</label>
            </div>
            <div class='checkbox'>
            <label>$2 Colore per tutte le barre: $3</label>
            </div>
            <div class='checkbox'>
            <label>$4 Mostra tooltip</label>
            </div>
        </div>
HTML
        ,

        'chart.treemap.settings' => <<<HTML
            <p>Genera una rappresentazione basata su riquadri di dati gerarchici. Ogni nodo (prima colonna) ha un nodo padre (seconda colonna; tranne il nodo radice), oltre a un valore numerico che determina la dimensione del riquadro (terza colonna).</p>
            <p><a target="_blank" href="https://developers.google.com/chart/interactive/docs/gallery/treemap#data-format">Colonne richieste</a>:
            <ul class='columns'>
            <li>1. Nodo (stringa)</li>
            <li>2. Nodo padre (stringa)</li>
            <li>3. Peso (numerico; maggiore di zero)</li>
            <li>4. Scala di colori (opzionale, numerico; valore relativo per determinare il colore del riquadro)</li>
            </ul>
            </p>
HTML
        ,

        'delete.success' => 'Il record è stato eliminato con successo.',
        'delete.confirm-head' => 'Conferma eliminazione',
        'delete.confirm-msg' => 'Conferma di voler eliminare questo record. Questa azione non può essere annullata. L\'eliminazione funziona solo se questo record non è referenziato da altri record.',
        'delete.button-cancel' => 'Annulla',
        'delete.button-delete' => 'Elimina',

        'error.db-connect' => 'Impossibile stabilire una connessione al database.',
        'error.db-prepare' => 'Errore durante la preparazione della query al database.',
        'error.db-execute' => 'Errore durante l\'esecuzione della query al database.',
        'error.delete-exec' => 'Eliminazione fallita. Probabilmente questo record è ancora referenziato da un altro record.',
        'error.delete-count' => 'Il record non può essere eliminato perché probabilmente non esiste più. Si prega di verificare.',
        'error.delete-file-warning' => 'Uno o più file non possono essere eliminati dal server.',
        'error.edit-obj-not-found' => 'Il record richiesto non è stato trovato.',
        'error.exception' => 'Errore di eccezione: $1',
        'error.field-value-missing' => 'Non è definito alcun valore per il campo "$1".',
        'error.field-required' => 'Si prega di fornire un valore per il campo obbligatorio <b>$1</b>.',
        'error.field-multi-required' => 'Si prega di selezionare almeno un valore per il campo a selezione multipla <b>$1</b>.',
        'error.file-retrieval-no-upload' => 'Nessun file è stato caricato per questo record.',
        'error.password-too-short' => 'La password è troppo corta, la lunghezza minima è $1.',
        'error.password-hash-missing' => 'Errore di configurazione: La funzione di hash della password <i>$1</i> non esiste.',
        'error.password-lower' => 'La password deve contenere almeno $1 lettere alfabetiche minuscole (<code>a-z</code>).',
        'error.password-upper' => 'La password deve contenere almeno $1 lettere alfabetiche maiuscole (<code>A-Z</code>).',
        'error.password-number' => 'La password deve contenere almeno $1 cifre (<code>0-9</code>).',
        'error.password-other' => 'La password deve contenere almeno $1 caratteri da questo set di caratteri: <code>$2</code>.',
        'error.upload-err-ini-size' => "Il caricamento supera l'impostazione upload_max_filesize in php.ini",
        'error.upload-err-form-size' => "Il caricamento supera l'impostazione MAX_FILE_SIZE nel modulo",
        'error.upload-err-partial' => "Il file è stato caricato solo parzialmente",
        'error.upload-err-no-file' => "Nessun file caricato",
        'error.upload-err-no-tmp-dir' => "Nessuna directory temporanea disponibile sul server",
        'error.upload-err-cant-write' => "Errore durante il salvataggio del file sul disco",
        'error.upload-err-extension' => "Caricamento del file interrotto da un'estensione",
        'error.upload-err-unknown' => "Errore sconosciuto durante il caricamento",
        'error.upload-filesize' => 'Il file caricato supera la dimensione massima consentita di $1 byte.',
        'error.upload-invalid-ext' => "L'estensione del file '$1' non è consentita. Sono consentite le seguenti estensioni: $2",
        'error.upload-location' => 'Errore di configurazione: la directory di destinazione per i caricamenti non è specificata.',
        'error.upload-create-dir' => 'Impossibile creare la directory di destinazione.',
        'error.upload-file-exists' => 'Impossibile caricare il file perché esiste già un file con lo stesso nome nella directory di destinazione.',
        'error.upload-move-file' => 'Impossibile salvare il file caricato.',
        'error.upload-store-db' => 'Errore di configurazione: il salvataggio dei file nel database non è (ancora) possibile.',
        'error.upload-no-file-provided' => 'Nessun file fornito per il campo obbligatorio <b>$1</b>.',
        'error.invalid-dbtype' => "Errore di configurazione: tipo di database non valido '$1'.",
        'error.invalid-display-expression' => "Errore di configurazione: l'espressione di visualizzazione in <code>display-expression</code> non è valida.",
        'error.invalid-function' => "Funzione non valida '$1'.",
        'error.invalid-login' => '$1 e/o $2 non sono validi.',
        'error.invalid-mode' => "Azione sconosciuta '$1'.",
        'error.invalid-params' => 'Uno o più parametri non sono validi o mancano.',
        'error.invalid-pk-value' => "Valore della chiave primaria non valido '$1'.",
        'error.invalid-lookup-table' => "Tabella referenziata non valida '$1'.",
        'error.invalid-lookup-field' => "Campo referenziato non valido '$1'.",
        'error.invalid-table' => "Tabella non valida '$1'.",
        'error.invalid-wkt-input' => 'Input WKT non valido.',
        'error.missing-pk-value' => "Valore mancante per la chiave primaria '$1'.",
        'error.no-plugin-functions' => 'Errore di configurazione: Non ci sono funzioni di plugin registrate che possono essere chiamate.',
        'error.no-values' => 'Nessun dato da salvare disponibile.',
        'error.not-allowed' => 'Non hai il permesso per questa azione.',
        'error.query-withouth-qualifier' => 'Query senza qualificatore',
        'error.missing-login-data' => 'Inserisci $1 e $2.',
        'error.map-picker-wkt' => '<b>Errore:</b> Il valore specificato <code>$1</code> non è valido e non può essere visualizzato.',
        'error.map-picker-single-marker' => 'Devi posizionare esattamente un marker sulla mappa.',
        'error.edit-inline-form-id-missing' => 'ID del modulo principale mancante.',
        'error.sequence-name' => 'Errore di configurazione: Il valore per <code>id_sequence_name</code> è probabilmente non valido.',
        'error.edit-update-rels-prep' => 'Preparazione dell\'aggiornamento delle relazioni per il campo $1 non riuscita (passo $2).',
        'error.edit-update-rels-exec' => 'Esecuzione dell\'aggiornamento delle relazioni per il campo $1 non riuscita (passo $2).',
        'error.sql-linkage-defaults' => 'Assegnazione dei valori predefiniti per gli attributi di relazione non riuscita.',
        'error.update-record-gone' => 'Il record modificato non può essere caricato. Potrebbe essere stato eliminato nel frattempo.',
        'error.storedquery-fetch' => 'Impossibile caricare la query.',
        'error.storedquery-config-table' => 'Errore di configurazione: Manca l\'impostazione per <code>querypage_stored_queries_table</code> in <code>$APP</code>.',
        'error.storedquery-create-table' => 'Impossibile creare la tabella per le query salvate.',
        'error.storedquery-exec-params' => 'Impossibile eseguire la query con $1',
        'error.storedquery-invalid-sql' => 'Query non valida. Sono consentite solo query SELECT!',
        'error.storedquery-invalid-params' => 'Tabella e/o campo non validi nella query parametrizzata.',
        'error.lookup-async.invalid-params' => 'Errore nella ricerca: parametri di ricerca non validi.',
        'error.lookup-async.connect-db' => 'Errore nella ricerca: connessione al database fallita.',
        'error.lookup-async.stmt-error' => 'Errore nella ricerca: impossibile interrogare il database.',
        'error.lookup-async.query-whitespace' => 'Errore nella ricerca: il termine di ricerca contiene troppi spazi vuoti.',
        'error.merge-primary-key-setting-missing' => 'Chiave primaria non definita nelle impostazioni per la tabella <code>$1</code>! Fusione annullata. Contatta il tuo amministratore!',
        
        'geom-field.placeholder' => 'Inserisci il valore WKT o selezionalo tramite "$1"',
        'geom-field.map-picker-button-label' => 'Mappa',
        'geom-field.map-picker-button-tooltip' => 'Assegna coordinate tramite una mappa',
        'geom-field.map-picker-view-tooltip' => 'Mostra questa geometria su una mappa',

        'global-search.cache-notice' => '<b>Nota:</b> Questi risultati di ricerca provengono da una cache che verrà aggiornata tra $1 minuti.',
        'global-search.input-placeholder' => 'Cerca',
        'global-search.results-for' => 'Risultati della ricerca per',
        'global-search.term-too-short' => '<p>Questo termine di ricerca è troppo corto, deve contenere almeno $1 caratteri.</p>',
        'global-search.no-results' => 'Nessun risultato per questa ricerca.',
        'global-search.one-result' => 'Un risultato trovato.',
        'global-search.results-info' => 'I risultati della ricerca sono stati trovati in $1 $2. $3',
        'global-search.results-one' => 'uno',
        'global-search.results-table-singular' => 'tabella',
        'global-search.results-table-plural' => 'tabelle',
        'global-search.results-jump' => 'Vai alla tabella',
        'global-search.results-found-detail' => 'Trovati $1 risultati di ricerca.',
        'global-search.show-more-preview' => 'Mostra tutti i risultati',
        'global-search.show-more-detail' => 'Si prega di modificare il termine di ricerca per restringere il numero di risultati.',
        'global-search.limited-results-hint' => 'Solo i primi $1 risultati di ricerca sono elencati qui.',
        'global-search.goto-top' => 'Torna su',

        'helper.html-text-clipped' => 'Testo troncato a causa della lunghezza. Clicca per visualizzare l\'intero testo.',
        'helper.help-popup-title' => 'Guida all\'inserimento',

        'info.new-edit-update-rels-prep-problems' => 'La relazione con il record $1 nel campo $2 non può essere aggiornata (P).',
        'info.new-edit-update-rels-exec-problems' => 'La relazione con il record $1 nel campo $2 non può essere aggiornata (E).',
        'info.new-edit-update-rels-inline-defaults' => 'Il record è stato salvato, ma il record collegato $1 nel campo $2 non può essere salvato.',
        'info.new-edit-update-rels-inline-prep' => 'I dettagli della relazione nel campo $1 non possono essere aggiornati per il record $2 (P).',
        'info.new-edit-update-rels-inline-exec' => 'I dettagli della relazione nel campo $1 non possono essere aggiornati per il record $2 (E).',

        'info-box.success-head' => 'Successo',
        'info-box.error-head' => 'Errore',
        'info-box.sql-codes' => 'Codici di errore',
        'info-box.info-head' => 'Informazione',

        'list.num-indicator' => 'Mostra record <b>$1</b>&ndash;<b>$2</b> di <b>$3</b>',
        'list.total-indicator' => 'Numero totale nella tabella: <b>$1</b>',
        'list.button-new' => 'Nuovo record',
        'list.jump-to-page' => 'Vai alla pagina',

        'login.button' => 'Accedi',
        'login.head' => 'Accedi',
        'login.logout-navbar-label' => 'Esci',
        'login.guest-access' => 'Accesso ospite',

        'lookup-field.record' => 'Record',
        'lookup-field.create-new-button' => 'Nuovo',
        'lookup-field.create-new-tooltip' => 'Se non trovi il record desiderato nell\'elenco a discesa, clicca su questo pulsante per creare un nuovo record.',
        'lookup-field.placeholder' => 'Clicca per selezionare',
        'lookup-field.linkage-details-edit-tooltip' => 'Modifica i dettagli della relazione',
        'lookup-field.linkage-assoc-edit-tooltip' => 'Modifica il record $1 referenziato',
        'lookup-field.linkage-assoc-delete-tooltip' => 'Rimuovi la relazione con questo/a $1',
        'lookup-field.linked-records-clipped-tooltip' => 'Testo troncato a causa della lunghezza. Clicca per visualizzare l\'intero testo.',
        'lookup-field.linked-record-no-display-value' => 'Non è stato possibile determinare un valore di visualizzazione per questo record referenziato, quindi viene mostrato il suo valore identificativo',
        'lookup-field.max-async' => 'Solo i primi $1 risultati vengono mostrati nell\'elenco a discesa. Si prega di utilizzare un testo di ricerca più restrittivo.',
        'lookup-field.linkage-details-missing' => 'Per tutti i record collegati nella seguente lista, che hanno un\'icona rossa <span class="glyphicon glyphicon-th-list"></span>, è necessario inserire i dettagli richiesti della relazione cliccando sull\'icona!',

        'main-page.html' => '<p>Seleziona un\'azione dal menu.</p>',

        'map-picker.done-button' => 'Fatto',
        'map-picker.edit-instructions' => " Posiziona un marker nella posizione desiderata. Per creare un marker, clicca su una delle icone di forma (ad esempio l'icona <span class='glyphicon glyphicon-map-marker'></span> per il marker puntuale), e disegna poi il marker sulla mappa. Per modificare un marker esistente, clicca sull'icona <span class='glyphicon glyphicon-edit'></span> e segui le istruzioni che appariranno. Quando hai finito, clicca sul pulsante <span class='glyphicon glyphicon-check'></span> Fatto.",

        'menu.browse+edit' => 'Sfoglia & Modifica',
        'menu.new' => 'Nuovo record',

        'merge.page-heading' => 'Unisci $1',
        'merge.intro' => 'Nella tabella sottostante, i dati del record $1 nella seconda riga (il cosiddetto record "<i>Slave</i>") devono essere trasferiti nel record $1 nella prima riga (il cosiddetto record "<i>Master</i>"). Esamina e regola le caselle di selezione in ogni colonna per definire quali dati devono essere salvati nel record $1 unito. Se in una colonna sono selezionate entrambe le caselle, i dati di entrambi i record $1 saranno uniti (nel caso di collegamenti multipli) o il valore del record Slave sarà aggiunto al valore del record Master (nel caso di dati testuali). Quindi clicca sul pulsante "Unisci".',
        'merge.button-merge' => 'Unisci',
        'merge.button-merge-again' => 'Unisci di nuovo',
        'merge.info-rollback' => 'L\'unione è stata annullata a causa di errori.',
        'merge.success' => 'I record sono stati uniti con successo. Si prega di esaminare il record unito nella prima riga della tabella. Se necessario, puoi unire nuovamente i due record o eliminare il record Slave se non è più necessario.',
        'merge.fail' => 'Non è stato possibile unire i record. Potrebbero esserci restrizioni definite nel database che lo impediscono.',
        'merge.button-cancel' => 'Annulla',
        'merge.nothing-to-do' => 'I valori selezionati nella tabella non modificherebbero il record Master. Pertanto, non è necessaria alcuna unione.',
        'merge.record-pushed' => 'Questo record $1 è stato selezionato come destinazione per un\'unione. Ora naviga al record $1 rilevante per l\'unione e avvia l\'unione premendo il pulsante "Unisci". Prima dell\'unione, puoi scegliere quali dati esattamente devono essere trasferiti.',
        'merge.list-of-referencing-records' => 'Il record Slave è referenziato dai seguenti record elencati di altre tabelle. Seleziona questa casella per aggiornare questi riferimenti in modo che puntino al record Master.',
        'merge.delete-slave-if-master-referenced' => 'Se uno dei record in questo elenco ha già un riferimento al record Master, i riferimenti al record Slave possono essere eliminati o mantenuti. Seleziona questa casella per eliminare i riferimenti al record Slave in questo caso. (Questa opzione è rilevante solo se la casella precedente è selezionata)',
        'merge.button-swap' => 'Inverti Slave & Master',

        'new-edit.heading-new' => 'Nuovo: $1',
        'new-edit.heading-edit' => 'Modifica: $1',
        'new-edit.heading-edit-inline' => 'Modifica dettagli di $1',
        'new-edit.save-button' => 'Salva',
        'new-edit.clear-button' => 'Pulisci modulo',
        'new-edit.intro-help' => "Compila il modulo e premi il pulsante <span class='glyphicon glyphicon-floppy-disk'></span> <b>Salva</b>. I campi con l'indicatore <span class='required-indicator'>&#9733;</span> sono obbligatori.",
        'new-edit.save-inline-hint' => 'Si prega di notare che queste modifiche saranno salvate solo quando il modulo principale sarà salvato.',
        'new-edit.field-optional-tooltip' => 'Questo campo è facoltativo',
        'new-edit.field-required-tooltip' => 'Questo campo è obbligatorio',
        'new-edit.success-new' => 'Il record è stato salvato nel database.',
        'new-edit.success-edit' => 'Il record è stato aggiornato nel database.',
        'new-edit.validation-error' => 'I dati inseriti nel modulo contengono errori! Si prega di correggere i campi evidenziati in rosso.',
        'new-edit.form-submitting' => 'Il modulo è in fase di invio. Attendere prego...',
        'new-edit.form-loading' => 'Il modulo è in fase di caricamento...',

        'plugin.csv.heading' => 'Importa file CSV: $1',
        'plugin.csv.error-column-count' => 'La riga $1 del file CSV contiene $2 colonne, quindi meno delle $3 colonne da importare! Potresti aver specificato separatori di campo, delimitatori di campo o caratteri di escape errati?',
        'plugin.csv.error-file-read' => 'Impossibile leggere il file CSV caricato.',
        'plugin.csv.error-no-columns' => 'Nessuna colonna da importare specificata.',
        'plugin.csv.label.csvfile' => 'File CSV',
        'plugin.csv.label.hasheader' => 'Intestazione?',
        'plugin.csv.value.hasheader' => 'La prima riga del file CSV contiene le intestazioni delle colonne',
        'plugin.csv.label.delimiter' => 'Separatore di campo',
        'plugin.csv.label.tabulator' => 'Usa tabulazione come separatore di campo',
        'plugin.csv.help.delimiter' => 'Carattere utilizzato come separatore di campo.',
        'plugin.csv.label.enclosure' => 'Delimitatore di campo',
        'plugin.csv.help.enclosure' => 'Carattere che delimita il contenuto del campo, specialmente se il separatore di campo è presente nel contenuto del campo.',
        'plugin.csv.label.escape' => 'Carattere di escape',
        'plugin.csv.help.escape' => 'Carattere utilizzato per mascherare un delimitatore di campo presente nel contenuto del campo.',
        'plugin.csv.label.columns' => 'Colonne da importare',
        'plugin.csv.help.columns' => 'Specifica le colonne presenti nel file CSV nell\'ordine corretto.',
        'plugin.csv.label.skipnull' => 'Valori mancanti',
        'plugin.csv.help.skipnull' => 'Seleziona le colonne in cui un valore mancante nel file CSV deve essere registrato come valore <code>NULL</code> nel database. Per impostazione predefinita, sono selezionati tutti i campi che non sono obbligatori.',
        'plugin.csv.label.import' => 'Importa!',
        'plugin.csv.info.aborted' => 'L\'importazione è stata interrotta a causa di un errore durante l\'importazione della riga $1 del file CSV.',
        'plugin.csv.success-import' => 'Sono stati importati $1 record nella tabella.',
        'plugin.csv.label.back-to-table' => 'Torna alla tabella',

        'querypage.sql-label' => 'Query SQL',
        'querypage.exec-button' => 'Esegui',
        'querypage.sql-help-head' => 'Guida per le query SQL',
        'querypage.sql-help-text' => <<<HTML
            <p>
            Inserisci qui la tua query SQL. Sono consentite solo query <code>SELECT</code>.
            </p>
            <p>
            <b>Query parametrizzata</b>: Puoi utilizzare segnaposto nominati con valori predefiniti invece di valori concreti nella tua query. Un parametro viene utilizzato come segue: <code>#{my_param|default_val}</code>, dove <code>my_param</code> è il nome del parametro e <code>default_val</code> è il valore predefinito. Quest'ultimo può anche essere vuoto, ma il separatore <code>|</code> deve comunque essere presente. Se il parametro è contrassegnato con <code>#!{...}</code>, ovvero con un punto esclamativo tra <code>#</code> e <code>{</code>, allora questo parametro deve essere obbligatoriamente fornito dall'utente o tramite un valore predefinito per eseguire la query.
            </p>
            <p>
            <b>Esempio</b>: <code>select * from users where lastname = #{Name|Norris}</code>
            </p>
            <p>
            Al parametro può essere opzionalmente assegnata un'etichetta, che verrà utilizzata nella vista di esecuzione. Il modello è quindi il seguente: <code>#{my_param:label|default_val}</code>, ad esempio <code>#{a:Età minima della persona|18}</code>
            </p>
            <p>
            Se un parametro appare più volte, è sufficiente specificarlo una volta completamente come descritto sopra. Tutte le altre occorrenze devono essere annotate solo con il nome del parametro senza la barra verticale successiva, ad esempio <code>#{p}</code> per il parametro <code>p</code>.
            </p>
            <p>
            Modalità esperto: è anche possibile specificare un campo a discesa opzionale da una tabella, che verrà quindi offerto all'utente nella vista delle query, secondo il seguente modello: <code>#{Name||table:person,field:fullname}</code>. Qui, dalla tabella <code>person</code>, in base alle impostazioni del progetto per il campo <code>fullname</code>, verrà offerta una casella a discesa per la selezione.
            </p>
            <p>
            È anche possibile specificare dropdown con selezione multipla come parametri. Il parametro sarà ad esempio il seguente: <code>#{x||table:person,field:full_name|flags:multi|expr:person_name|op:in}</code>. Spiegazione: <code>flags:multi</code> consente la selezione multipla. <code>expr:person_name</code> determina che la selezione multipla venga applicata come restrizione sul campo <code>person_name</code>. E <code>op:in</code> specifica che il comando SQL <code>in</code> venga utilizzato come operatore. Qui è anche possibile <code>not in</code>.
            </p>
HTML
        ,
        'querypage.store-settings-cache-expires' => 'Abilita cache. Intervallo di aggiornamento (secondi)',
        'querypage.store-settings-allow-public' => 'Questa visualizzazione è pubblica (usare con cautela).',
        'querypage.store-button-save' => 'Salva',
        'querypage.store-button-new' => 'Salva come nuovo',
        'querypage.store-button-update' => 'Aggiorna',
        'querypage.store-description-placeholder' => 'Descrizione',
        'querypage.store-title-placeholder' => 'Titolo',
        'querypage.store-intro' => 'Fornisci facoltativamente un titolo e una descrizione per la query:',
        'querypage.store-success' => 'La query è stata salvata. Visualizzazione live visibile al seguente link:',
        'querypage.store-error' => 'Si è verificato un errore durante il salvataggio della query.',
        'querypage.store-button-label' => 'Salva e genera link per la visualizzazione',
        'querypage.settings-head' => 'Impostazioni per la visualizzazione dei risultati',
        'querypage.settings-viz-label' => 'Tipo di visualizzazione',
        'querypage.param-query-refresh' => 'Aggiorna risultati',
        'querypage.results-head' => 'Visualizzazione dei risultati',
        'querypage.param-required' => 'Questo campo di query deve essere compilato, altrimenti la query non può essere eseguita',
        'querypage.param-hint' => 'I campi di query contrassegnati con ★ devono essere compilati.',

        'record-renderer.view-icon' => 'Visualizza questo record $1',
        'record-renderer.edit-icon' => 'Modifica questo record $1',
        'record-renderer.delete-icon' => 'Elimina questo record $1',
        'record-renderer.sort-asc' => 'Ordina in ordine crescente',
        'record-renderer.sort-desc' => 'Ordina in ordine decrescente',
        'record-renderer.search-icon' => 'Cerca',

        'search.transformation-invalid' => 'Errore di configurazione: <code>$APP[search_string_transformation]</code> non contiene il segnaposto <code>%s</code>',
        'search.popover-option-any' => 'Il campo contiene',
        'search.popover-option-word' => 'Il campo contiene la parola',
        'search.popover-option-exact' => 'Il campo corrisponde',
        'search.popover-option-start' => 'Il campo inizia con',
        'search.popover-option-end' => 'Il campo termina con',
        'search.popover-placeholder' => 'Testo di ricerca',
        'search.infotext-any' => "Cerca tutti i record in cui <span class='bg-success'><strong>$2</strong></span> è contenuto in <b>$1</b>.",
        'search.infotext-word' => "Cerca tutti i record in cui <span class='bg-success'><strong>$2</strong></span> è contenuto come parola in <b>$1</b>.",
        'search.infotext-exact' => "Cerca tutti i record in cui <b>$1</b> ha esattamente il valore <span class='bg-success'><strong>$2</strong></span>.",
        'search.infotext-start' => "Cerca tutti i record in cui <b>$1</b> inizia con <span class='bg-success'><strong>$2</strong></span>.",
        'search.infotext-end' => "Cerca tutti i record in cui <b>$1</b> termina con <span class='bg-success'><strong>$2</strong></span>.",
        'search.button-clear' => 'Cancella ricerca',
        'search.no-results' => 'Nessun risultato trovato.',
        'search.num-indicator' => 'Mostra risultati della ricerca <b>$1</b>&ndash;<b>$2</b> di <b>$3</b>',

        'setup.wizard.save-success' => 'Einstellungen wurden erfolgreich gespeichert!',
        'setup.wizard.save-error-file' => 'Einstellungen konnten nicht gespeichert werden. Stellen Sie sicher, dass der Webserver-Prozess auf Dateiebene Schreibrechte auf der Einstellungsdatei hat bzw. die Datei erzeugen darf.',
        'setup.heading' => 'Einstellungen',

        'text-field.remaining-chars' => 'Zeichen übrig.',

        'upload-field.browse' => 'Durchsuchen',
        'upload-field.hint-empty' => 'Hinweis: Wenn Sie die bestehende Datei am Server nicht überschreiben wollen, können Sie dieses Feld leer lassen.',
        'upload-field.remove-existing-file' => 'Setzen Sie das Häkchen, um die existierende Datei <code>$1</code> vom Server zu löschen',

        'view.invalid-record' => 'Dieser Datensatz kann nicht angezeigt werden. Möglicherweise wurde er gelöscht.',
        'view.add-related-data-button' => 'Verknüpfen mit',
        'view.edit-icon' => 'Diesen $1-Datensatz bearbeiten',
        'view.edit-button' => 'Bearbeiten',
        'view.delete-icon' => 'Diesen $1-Datensatz löschen',
        'view.delete-button' => 'Löschen',
        'view.list-icon' => 'Alle $1 anzeigen',
        'view.list-button' => 'Alle anzeigen',
        'view.new-icon' => 'Neuen $1-Datensatz anlegen',
        'view.new-button' => 'Neu',
        'view.related-icon' => 'Verknüpfte Datensätze anzeigen (klicken, um Auswahl zu bekommen)',
        'view.related-button' => 'Verknüpfte Datensätze',
        'view.related-menu-item' => '$1 (via $2)',
        'view.hidden-fields-hint-1' => 'Dieser $1-Datensatz hat ein leeres Feld.',
        'view.hidden-fields-hint-N' => 'Dieser $1-Datensatz hat $2 leere Felder.',
        'view.show-hidden-field-1' => 'Leeres Feld anzeigen',
        'view.show-hidden-field-N' => 'Leere Felder anzeigen',
        'view.merge-icon' => 'Diesen $1-Datensatz mit einem anderen $1-Datensatz zusammenführen',
        'view.merge-button' => 'Zusammenführen',
    );
?>
