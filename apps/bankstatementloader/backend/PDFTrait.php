<?php
namespace App\Traits;
use Spatie\PdfToText\Pdf;
use Illuminate\Support\Facades\Log;

trait PDFTrait {
  private function procHoldings($holdings) {
    $x = []; $i = 0;
    foreach($holdings as $e) {
      $e = preg_replace('/[$|,|%]/', '', $e);
      if ($i++ < 2 ) {
        if (is_numeric($e)) $this->cleanMoney($e);
        $x[] = $e;
        continue;
      }
      if ($e == 'unknown' or $e == 'not applicable' or $e == 'blank' or $e == '--' or $e == '-') $e = null;
      else if (is_numeric($e)) $e = $this->cleanMoney($e);
      // else { // handle EAI - estimated annual income
      //   $xx = explode('.', $e);
      //   if (count($xx) >= 3) array_splice($xx, 2);
      //   $e = $this->cleanMoney(implode('.', $xx));
      // }
      $x[] = $e;
    }
    return $x;
  }
  protected function getEaiEy($str, $sym=null) {
    // Log::info("-fn-getEaiEy-CK-str=$str symbol=$sym");
    $xx = preg_split('/ /', $str);
    $str = $xx[0];
    if ($str == '-' || $str == '--') return ['unknown', 'unknown'];
    $str = preg_replace('/[$|,|%]/', '', $str);
    $x = explode('.', $str);
    if (count($x) != 3) return null;
    $EAI = $x[0]. '.' . substr($x[1], 0, 2);
    $EY = substr($x[1], 2, 1) . '.' . $x[2];
    // Log::info("-fn-getEaiEy-CK-str=$str", $x); Log::info("-fn-getEaiEy-CK-EAI=$EAI EY=$EY");
    return [$EAI, $EY];
  }
  protected function cleanMoney($str, $flag=null) { //Log::info("-CK-$flag str=$str LINE=".__LINE__);
    if ($flag != null) //Log::info("-CK-$flag str=$str");
    if ($str == '--' || $str == '-') return null;
    $x = explode(' ', $str);
    $m = $x[0];
    $mLong = preg_replace('/[$|,]/', '', $m); // Log::info("-CK-$flag str=$str mLong=$mLongLINE=".__LINE__);
    $xx = explode('.', $mLong);
    $countXX = count($xx);
    // Log::info("-CK-$flag str=$str mLong=$mLong LINE=".__LINE__  . " countXX=$countXX");
    // if ($xx[0] == 1) Log::info("-CK-explode . mLong=$mLong", $xx);
    if (count($xx) == 1) return $xx[0] . '.00';
    else if (count($xx) == 2) return $mLong;
    else if (count($xx) >= 3) array_splice($xx, 2);
    else if (intval($xx[1]) == 0) {
      $ret = $xx[0] . '.00';
      if ($flag != null) Log::info("-CK-$flag str=$str ret=$ret", $xx);
      // $xx[1] = substr($xx[1], 0, 2);
      // $ret = implode('.', $xx);
      // Log::info("-CK-implode . mLong=$mLong ret=$ret");
      return $ret;
    }
    // $ing = $xx[0];
    $xx[1] = substr($xx[1], 0, 2);
    // return $ing . '.' . $dcl;
    return implode('.', $xx);
  }
  // protected function cleanMoney($str) {
  //   $x = explode(' ', $str);
  //   $m = $x[0];
  //   return preg_replace('/[$|,]/', '', $m);
  //   // return preg_replace('/[$|,]/', '', $str);
  // }
  protected function cleanUplineJunk($str) {
    return preg_replace('/ MR_CE _BMJMSFBBBMFWZ_BBBBB \d{8}/', '', $str);
  }
  private function processLines($lines) {
    // $keptLines = ['Core Account', 'not applicable'];
    $newLines = [];
    $i = 0;
    foreach($lines as $line) {
      // if (in_array($line, $keptLines)) continue;
      // Log::debug("ProcessLines line=[$line]", [__line__]);
      if (preg_match('/588130.55.0/', $line) == 1) {
        return $newLines;
      }
      if (preg_match('/^(\\-|\d|\\$)/', $line) == 1) {
        // if (preg_match('/(.*)FEE$/', $lines[$i + 1]) == 1) {
        //   Log::info("===X==Process FEE Line $line", [$lines[$i + 1], $lines[$i + 2]]);
        //   $newLines[] = $line;
        //   $newLines[] = null;
        //   $newLines[] = null;
        //   $newLines[] = $lines[$i + 1];
        //   $newLines[] = $lines[$i + 2];
        //   continue;
        // }
        $x = explode(' ', $line); // Log::info("explode line=[$line]", [$lines[$i + 1], $x]);
        foreach($x as $v) {
          $v = trim($v);
          if ($v == '') continue;
          $newLines[] = $v;
        }
      } else if (preg_match('/:/', $line) == 1) {
        $x = explode(':', $line); //Log::debug("Processing lines", $x);
        foreach($x as $v) {
          $v = trim($v);
          if ($v == '') continue;
          $newLines[] = $v;
        }
      } else if (preg_match('/^DELL\s+/', $line) == 1) {
        $x = explode(' ', $line); //Log::debug("Processing lines", $x);
        foreach($x as $v) {
          $v = trim($v);
          if ($v == '') continue;
          $newLines[] = $v;
        }
      // } else if (preg_match('/^\d\d\\/\d\d/', $line) == 1 && preg_match('/(.*)FEE$/', $lines[$i + 1]) == 1) {
      // } else if (preg_match('/^\d\d\\/\d\d/', $line) == 1) {
      //   Log::info("   ===X==Process FEE Line $line", [$lines[$i + 1]]);
      //   $newLines[] = $line;
      //   $newLines[] = null;
      //   $newLines[] = null;
      //   $newLines[] = $lines[$i + 1];
      //   $newLines[] = $lines[$i + 2];
      } else {
        $newLines[] = $line;
      }
      $i++;
      // Log::debug("-CK-newLines", $newLines);
    }
    return $newLines;
  }
  protected function parseFidelityStatememt($pdfFile) { //Log::debug("-CK-parseFidelityStatemt: $pdfFile", [__line__, __file__]);
    // $config = new \Smalot\PdfParser\Config();
    // $config->setHorizontalOffset("\t");
    // $config->setHorizontalOffset('');
    // $parser = new \Smalot\PdfParser\Parser([], $config);
    $parser = new \Smalot\PdfParser\Parser();
    if (!file_exists($pdfFile)) return "$pdfFile";
    $pdf = $parser->parseFile($pdfFile);
    $pages  = $pdf->getPages();
    $ptxt = '';
    foreach ($pages as $page) {
      $ptxt .= $page->getText();
    }
    // $this->splitTxt($ptxt);
    // Log::debug("-CK- Samlot/PdfParse pages text ourt ", preg_split('/\t/', $ptxt));
    // $items = preg_split("/\0|\t|\n|\f|\r/", $ptxt);
    $items = preg_split("/\t|\f|\r|\n/", $ptxt);
    // $items = preg_split("/\t/", $ptxt);
    $lines = [];
    foreach($items as $item) {
      $line = preg_replace('/\n/', ' ', trim($item));
      if (!ctype_print($line)) continue;
      $lines[] = preg_replace('/\s+/', ' ', $line);
    }
    // Log::info('-CK-pdf lines', $lines);
    // return $lines;
    return $this->processLines($lines);
  }
  protected function parsePDF($pdfFile) {
    $parser = new \Smalot\PdfParser\Parser();
    if (!file_exists($pdfFile)) return "$pdfFile";
    $pdf = $parser->parseFile($pdfFile);
    $pages  = $pdf->getPages();
    $ptxt = '';
    foreach ($pages as $page) {
      $ptxt .= $page->getText();
    }
    // $this->splitTxt($ptxt);
    // Log::debug("-CK- Samlot/PdfParse pages text ourt ", preg_split('/\t/', $ptxt));
    $items = preg_split("/\0|\t|\n|\f|\r/", $ptxt);
    // $items = preg_split("/\t|\f|\r|\n/", $ptxt);
    // $items = preg_split("/\t/", $ptxt);
    $lines = [];
    foreach($items as $item) {
      $line = preg_replace('/\n/', ' ', trim($item));
      if (!ctype_print($line)) continue;
      $lines[] = preg_replace('/\s+/', ' ', $line);
    }
    // Log::info('-CK-pdf lines', $lines);
    return $lines;
    // return $this->processLines($lines);
  }
  protected function XX_OLD_parsePDF($pdfFile) { // this one mis order the PDF table order
    if (!file_exists($pdfFile)) return "$pdfFile";
    // $text = (new Pdf())->setPdf($pdfFile)->setOptions(['layout', 'r 96'])->text();
    // $text = (new Pdf())->setPdf($pdfFile)->text();
    $text = Pdf::getText($pdfFile);
    // Log::info("txtPDF=[$text]"); // exit(-100);
    // $x = preg_split('/\n|\r|\f|\t|\0/', $text);
    $x = preg_split('/\n/', $text);
    $lines = [];
    foreach ($x as $line) {
      if (ctype_print($line)) {
        $line = preg_replace('/\s+/', ' ', $line);
        $line = trim($line);
        $lines[] = $line;
      }
    }
    return $lines;
  }
  protected function writeToTempFile($filename, $lines) {
    // $ccfile = "/sites/tmp/$filename";
    $ccfile = config('constants.USER_SITE') . "/tmp/$filename";
    $fp = fopen($ccfile, 'w');
    $i = 0;
    foreach($lines as $line) {
        $i++;
        fwrite($fp, "$i, [$line]\n"); // only print printable line
    }
    fclose($fp);
  }
}
