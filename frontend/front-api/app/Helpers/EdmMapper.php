<?
namespace App\Helpers;
use Illuminate\Support\Facades\Log;
use \EasyRdf\Graph as Graph;

class EdmMapper {

    private $data;
    private $rdf;

    public function __construct($data) {
        \EasyRdf\RdfNamespace::set('edm','http://www.europeana.eu/schemas/edm/');
        $this->data = $data;
        $this->graph = new Graph();
        $this->mapper();
    }  

    public function export() {
        return $this->graph->serialise('rdfxml');
    }

    private function mapper() {
        $this->graph->resource($this->data->url,'edm:ProvidedCHO');  
        $wr = $this->graph->resource($this->data->sameAs,'edm:WebResource');      
        //$wr->set('rdf:type',['rdf:resource'=>'http://www.europeana.eu/schemas/edm/FullTextResource']);
        

        @$this->set($wr,'dc:name',$this->data->name);
        @$this->set($wr,'dc:title',$this->data->name);
        @$this->set($wr,'dc:title',$this->data->headline);

        @$this->set($wr,'dc:creator',$this->data->author);
        @$this->set($wr,'dc:creator',$this->data->creator);

        @$this->set($wr,'dc:language',$this->data->inLanguage);


    /*
        $me = $this->graph->resource('http://www.example.com/joe#me', 'foaf:Person');
        $me->set('foaf:name', 'Joseph Bloggs');
        $me->set('foaf:title', 'Mr');
        $me->set('foaf:nick', 'Joe');
        $me->add('foaf:homepage', $this->graph->resource('http://example.com/joe/'));
    
        // I made these up; they are not officially part of FOAF
        $me->set('foaf:dateOfBirth', new \EasyRdf\Literal\Date('1980-09-08'));
        $me->set('foaf:height', 1.82);
    
        $project = $this->graph->newBnode('foaf:Project');
        $project->set('foaf:name', "Joe's current project");
        $me->set('foaf:currentProject', $project);
*/
    }


    private function set($r,$property,$value) {
        if (is_array($value)) {
            foreach($value as $k => $v) {
                if (is_object($v)){
                    if (((array)$v)["@value"] && ((array)$v)["@language"]) {
                        return $r->add($property,\EasyRdf\Literal::create(((array)$v)["@value"], ((array)$v)["@language"], null));
                    } else {
                        return $this->add_object($r, $property,$v);
                    }
                } else {
                    return $r->add($property,$v);
                }
            }
        } else {
            if (is_object($value)){
                if (((array)$value)["@value"] && ((array)$value)["@language"]) {
                    return $r->add($property,\EasyRdf\Literal::create(((array)$value)["@value"], ((array)$value)["@language"], null));
                } else {
                    return $this->add_object($r,$property,$value);
                }
            } else {
                return $r->add($property,$value);
            }
        }
    }


    private function add_object($r,$property,$value) {
        switch (((array)$value)["@type"]) {
            case "Language":
                $val = ((array)$value)["@id"];
                break;
        }


        return $r->add($property,$val);
    }
}