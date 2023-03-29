<?
namespace App\Helpers;
use Illuminate\Support\Facades\Log;
use App\Helpers\CSV_Output;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;



class XLSX_Output extends CSV_Output{ 

    public function __construct() {
        parent::__construct();
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->row=1;
    }

    public function add($data) {
       
        if ($this->first) {
            $this->fillrow(self::$header);
            $this->first = false;
        }
        foreach($data as $d) {
            $this->fillrow(self::arrayfy(self::selectfields($d)));
        }
    }

    public function save($job) {

        fclose($this->fhandle);
        unlink($this->tmpname);

        $fhandle = fopen($this->tmpname."_query.txt", 'w');
        fwrite($fhandle, json_encode($job, JSON_PRETTY_PRINT));
        fclose($fhandle);

        $writer = new Xlsx($this->spreadsheet);
        $writer->save($this->tmpname.".xlsx");  

    
        $cmd = "zip -m -j " . $this->tmpname . ".zip " . $this->tmpname . ".xlsx" . " " . $this->tmpname."_query.txt";
        exec($cmd);
        return basename($this->tmpname);
    }

    private function fillrow($r) {
        foreach($r as $k=>$val) {
            $cell = $this->column($k) . $this->row;
            
            if (substr($val,0,1) == "=") { // data beginning with = is considered a formula, adding a ' prevents this
                $val = "'" . $val;
            }
            $this->sheet->setCellValue($cell,$val);
        }
        $this->row++;
    }

    private function column($num) {
        $col = "";
        $a = $num / 26;
        $b = $num % 26;

        if ($num > 25) {
            $col .= chr($a+64);
        }
        $col .= chr($b+65);
        return $col;
    }
}