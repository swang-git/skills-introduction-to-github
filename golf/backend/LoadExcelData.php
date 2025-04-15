<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use \App\Http\Controllers\LoadKJGameDataController;

class LoadExcelData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'load-excel-data:KJGameData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load kj game data from an excel file (vis URL) to table golf.kj_game_data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $instance = new LoadKJGameDataController(null);
            // $xfle = $this->argument('xlsxfile');
            // $coln = $this->argument('colName');
            // $instance->loadGameDataByCol($xfle, $coln);
            // $kjsdir = '/sites/tmp/kjsfiles';
            // $lastLoadedlFile = $instance->getLatestFile($kjsdir);
            $KJexcelFile = $instance->loadKJExcelFile();
            $this->info("loadKJExcelFile was successful [$KJexcelFile]");
            $retval = $instance->processKJExcel($KJexcelFile);
            $this->info("processKJExcel($KJexcelFile) is", $retval['status']);
        } catch(exception $ex) {
            $this->info("loadKJExcelFile FAILED $ex->error");
        }
    }
}
