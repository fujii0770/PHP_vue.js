<?php

namespace App\Console\Commands;

use App\Http\Utils\CircularDocumentUtils;
use Illuminate\Console\Command;

class copyCircularToLongTerm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copy:circularToLongTerm {circular_id} {finishedDate} {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'copy long_term_document history circular data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $circular_id = $this->argument('circular_id');
        $longTermId = $this->argument('id');
        $finishedDate = $this->argument('finishedDate');
        CircularDocumentUtils::copyCircularDataToLongTerm($circular_id,$finishedDate,$longTermId);
    }

}
