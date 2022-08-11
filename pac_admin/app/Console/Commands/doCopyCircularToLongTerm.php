<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class doCopyCircularToLongTerm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copy:doCircularToLongTerm {circular_id} {finishedDate} {id}';

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
        Artisan::call('copy:circularToLongTerm', [
            'circular_id' => $circular_id,'finishedDate'=>$finishedDate,'id'=>$longTermId
        ]);
    }

}
