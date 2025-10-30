<?php
namespace App\Traits;
use Illuminate\Support\Facades\Log;
trait ParserFlagPDFTrait {
  private function getSecurity($lines, $start) {
    $cusip2secNameMap = [
      '17275R102' => 'CISCO',
      '594918104' => 'MSFT',
      '31617H102' => 'SPAXX',
      '316048206' => 'FSJXX',
      '315911750' => 'FXAIX',
      '315911693' => 'FSKAX',
      '315792663' => 'FFTWX',
      '316067107' => 'CashRv',
      '31617H805' => 'FZDXX',
      'FDIC99367' => 'QBYIQ',
      // '24703L103' => 'DELL',
      '24703L202' => 'DELL',
      '00206R102' => 'AT&T',
      '482497104' => 'BEKE',
      'FDIC99425' => 'Ints', // interest earned
    ];
    $name = null;
    for ($i = $start; $i <= $start + 20; $i++) {
      $x = explode(' ', $lines[$i]);
      $name = trim($x[0]);
      if (strlen($name) == 9) {
        if (array_key_exists($name, $cusip2secNameMap)) {
          return [$i, $cusip2secNameMap[$name]];
        }
      }
    }
    // for ($i = $start; $i < $start + 10; $i++) {
    //   $name = trim($lines[$i]);
    //   if (strlen($name) == 9) {
    //     if (array_key_exists($name, $cusip2secNameMap)) {
    //       return [$i, $cusip2secNameMap[$name]];
    //     }
    //   }
    // }
    return [$i, $name];
  }
  private function shortName($name) {
    // if (preg_match('/^WARNER BROS DISCOVERY INC/', $name)) return "WBD"; // 934423104
    $short_name = [
      "FIDELITY GOVERNMENT CASH RESERVES" => "CashRv", // 316067107
      "WARNER BROS DISCOVERY INC" => "WBD", // 934423104
      "FIDELITY FREEDOM 2025" => "FFTWX",  // 315792663
      "FIDELITY TOTAL MARKET INDEX FUND" => "FSKAX",  // 315911693
      "AT&T INC COM USD1" => "T",  // 00206R102
      "MICROSOFT CORP" => "MSFT",  // 594918104
      "FIDELITY 500 INDEX FUND" => "FXAIX",  // 315911750
      "FIDELITY GOVERNMENT MONEY MARKET" => "SPAXX",  // 31617H102
      "FIDELITY GOVERNMENT MONEYMARKET" => "SPAXX",  // 31617H102
      "FIDELITY NJ MUNICIPAL MONEY MKT" => "FNJXX",  // 316089309
      "FIDELITY NJ MUNI MONEY MKT PREMIUM CLASS" => "FSJXX",  // 316048206
      "WINDSOR MEADOWS CA" => "WNDSR",
      "EAST WINDSOR M.U.A." => "WATER",
      "CISCO SYSTEMS INC" => "CSCO",
      // "486-579416-1" => "TransFr",
      "486-579416-1" => "486",
      "Z71-367818-1" => "Z71",
      "FIDELITY MONEY MARKET" => "SPRXX",  // 31617H102
      "FIDELITY GOVERNMENT CASH RESERVES" => "FDRXX",  // 31617H102
      "FIDELITY GOVERNMENT CASHRESERVES" => "FDRXX",  // 31617H102
      "FIDELITY MMKT PREMIUM CLASS" => "FZDXX",  // 31617H805
      "FDIC INSURED DEPOSIT" => "QBYIQ",  // FDIC99367
      "FDIC INSURED DEPOSIT AT BNY MELLON IRA" => "QBYIQ",  // FDIC99367
      "FDIC INSURED DEPOSIT AT BNY MELLON" => "QBYIQ",  // FDIC99367
      "FDIC INSURED DEPOSIT AT CITIBANK" => "QPCBQ",
      "FDIC INSURED DEPOSIT AT CITIBANK IRA" => "QPCBQ",
      "FEDERAL TAX WITHHELD" => "FeTaxW",
      "NJ STAT WTH" => "NjTaxW",
      "DELL TECHNOLOGIES INC CL C" => "DELL",
      "Ke Hldgs Inc Sponsored Ads" => "BEKE",
      "Ke" => "BEKE",
      "KE" => "BEKE",
      "FIDELITY NJ MUNI MONEY MKT PREMIUMCL" => "MUNI",
      "FIDELITY NJ AMT TAX-FREE MONEY MKT" => "TAXFR",
    ];
    if (array_key_exists($name, $short_name)) return $short_name[$name];
    return $name;
  }
  static $holdFlag = 'none';
  private function setHoldFlag($line) {
    // if (preg_match('/^Core Account Description/', $line)) self::$holdFlag = 'Core Account';
    if (preg_match('/^Core Account/', $line)) self::$holdFlag = 'Core Account';
    // if (preg_match('/^Mutual Funds Description/', $line)) self::$holdFlag = 'Mutual Funds';
    if (preg_match('/^Mutual Funds/', $line)) self::$holdFlag = 'Mutual Funds';
    // if (preg_match('/^Stocks Description|^Holdings Stocks Description/', $line)) self::$holdFlag = 'Stocks';
    if (preg_match('/^Stocks|^Holdings Stocks Description/', $line)) self::$holdFlag = 'Stocks';
  }
  static $actvFlag = 'none';
  protected function setActvFlag($line, $sym=null) {
    if (preg_match('/Exchanges In/', $line)) self::$actvFlag = 'Exchanges In';
    else if (preg_match('/Exchanges Out/', $line)) self::$actvFlag = 'Exchanges Out';
    else if (preg_match('/^Fees and Charges/', $line)) self::$actvFlag = 'Fees and Charges';
    // else if (preg_match('/Fees and Charges$/', $line)) self::$actvFlag = 'Fees and Charges';
    // else if (preg_match('/^Activity Fees and Charges/', $line)) self::$actvFlag = 'Fees and Charges';
    else if (preg_match('/^Bill Payments/', $line)) self::$actvFlag = 'Bill Payments';
    // else if (preg_match('/Core Fund Activity For more info/', $line)) self::$actvFlag = 'Core Fund Activity';
    // else if (preg_match('/^Core Fund Activity/', $line)) self::$actvFlag = 'Core Fund Activity';
    // else if (preg_match('/^Activity Core Fund Activity|Core Fund Activity/', $line)) self::$actvFlag = 'Core Fund Activity';
    else if (preg_match('/^Activity Core Fund Activity|^Core Fund Activity/', $line)) self::$actvFlag = 'Core Fund Activity';
    // else if (preg_match('/^Activity Securities Bought & Sold/', $line)) self::$actvFlag = 'Securities BS';
    else if (preg_match('/^Securities Bought & Sold/', $line)) self::$actvFlag = 'Securities BS';
    // else if (preg_match('/^Dividends, Interest & Other Income/', $line)) self::$actvFlag = 'Dividends Int';
    // else if (preg_match('/^Activity Dividends, Interest & Other Income/', $line)) self::$actvFlag = 'Dividends Int';
    else if (preg_match('/^Dividends, Interest & Other Income/', $line)) self::$actvFlag = 'Dividends Int';
    // else if (preg_match('/^Activity Dividends, Interest & Other Income (Includes dividend reinvestment) SettlementDate/', $line)) self::$actvFlag = 'Dividends Int';
    else if (preg_match('/^Contributions/', $line)) self::$actvFlag = 'Contributions';
    else if (preg_match('/^Activity Contributions/', $line)) self::$actvFlag = 'Contributions';
    else if (preg_match('/^Distributions/', $line)) self::$actvFlag = 'Distributions';
    else if (preg_match('/^Activity Taxes Withheld/', $line)) self::$actvFlag = 'Taxes Withheld';
    else if (preg_match('/^Taxes Withheld/', $line)) self::$actvFlag = 'Taxes Withheld';
    else if (preg_match('/^Activity Core Fund/', $line)) self::$actvFlag = 'Core Fund';
    else if (preg_match('/^Withdrawals/', $line)) self::$actvFlag = 'Withdrawals';
    // else if (preg_match('/^Withdrawals Date/', $line)) self::$actvFlag = 'Withdrawals';
    // else if (preg_match('/^Deposits Date/', $line)) self::$actvFlag = 'Deposits';
    else if (preg_match('/^Deposits/', $line)) self::$actvFlag = 'Deposits';
    else if (preg_match('/^Activity Debit Card Summary/', $line)) self::$actvFlag = 'Debit Card Activity';
    else if (preg_match('/^Other\s+Activity\s+Out/', $line)) self::$actvFlag = 'OtherActivityOut';
    else if (preg_match('/^Other\s+Activity\s+In/', $line)) self::$actvFlag = 'OtherActivityIn';
  }
}