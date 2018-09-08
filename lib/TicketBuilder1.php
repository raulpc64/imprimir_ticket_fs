<?php 
require_once 'plugins/imprimir_ticket/extras/TicketWriter1.php';

/**
* Clase pare imprimir tickets.
*/
class TicketBuilder1
{
    use TicketWriter1;

    private $ticket;
    private $anchoPapel;
    private $sinComandos;

    private $document;
    private $documentType;
    private $empresa;

    public function __construct($terminal = null, $comandos = false) 
    {
        $this->ticket = '';

        $this->anchoPapel = ($terminal->anchopapel) ? $terminal->anchopapel : '45';        
        $this->comandoCorte = ($terminal->comandocorte) ? $terminal->comandocorte : '27.105';
        $this->sinComandos = $comandos;

        //$this->writeTicketHeader($empresa);
        //$this->writeTicketBody($document, $documentType);
        //$this->writeTicketFooter($document, $leyenda);
    }

    public function writeCompanyBlock($empresa)
    {
        $this->addSplitter('=');

        $this->addText($empresa->nombrecorto, true, true);
        $this->addBigText($empresa->direccion, true, true);

        if ($empresa->telefono) {
            $this->addText('TEL: ' . $empresa->telefono, true, true);
        }

        $this->addText(FS_CIFNIF . ': ' . $empresa->cifnif, true, true);
        $this->addSplitter('=');
		$this->addText("CLIENTE: " . $document->nombrecliente);
		$this->addSplitter('=');
    }

    public function writeHeaderBlock($headerLines)
    {
        foreach ($headerLines as $line) {
            $this->addText($line, true, true);
        }

        
    }



    public function writeBodyBlock($document, $documentType)
    {
        $text = strtoupper($documentType) . ' ' . $document->codigo;
        $this->addText($text, true, true);

        $text = $document->fecha . ' ' . $document->hora;
        $this->addText($text, true, true);

        $this->addSplitter('=');
        $this->addLabelValue('CANTIDAD','IMPORTE');

        $totaliva=0;
        foreach ($document->get_lineas() as $linea) {
            $this->addSplitter('-');
			$pvpaux = $linea->pvpunitario + ($linea->pvpunitario * $linea->iva / 100);
			$pvpauxtotal = $pvpaux * $linea->cantidad;
            $this->addLabelValue($linea->cantidad,$this->priceFormat($pvpauxtotal));
			$this->addBigText($linea->descripcion);   
			$totaliva += $linea->pvptotal * $linea->iva / 100; 
        }
        $this->addSplitter('=');
        $this->addLabelValue('IVA',$this->priceFormat($totaliva));
        $this->addLabelValue('TOTAL:',$this->priceFormat($document->total));
    }

    public function writeFooterBlock($footerLines, $leyenda, $codigo)
    {
        $this->addLineBreak(2);

        foreach ($footerLines as $line) {
            $this->addBigText($line, true, true);
        }

        $this->addText($leyenda, true, true);
        $this->addBarcode($codigo);
    }

    public function toString()
    {
        $this->addLineBreak(4);
        $this->paperCut();
        
        return $this->ticket;
    }
}