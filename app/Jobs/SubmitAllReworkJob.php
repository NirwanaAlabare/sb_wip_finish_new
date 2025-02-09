<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Rework as ReworkModel;

class SubmitAllReworkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($defect)
    {
        $this->
        $this->onQueue('processing');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->allDefect->count() > 0) {
            foreach ($allDefect as $defect) {
                // create rework
                $createRework = ReworkModel::create([
                    "defect_id" => $defect->id,
                    "status" => "NORMAL"
                ]);

                // update defect
                $defectSql = Defect::where('id', $defect->id)->update([
                    "defect_status" => "reworked"
                ]);

                // create rft
                $createRft = Rft::create([
                    'master_plan_id' => $defect->master_plan_id,
                    'so_det_id' => $defect->so_det_id,
                    "status" => "REWORK",
                    "rework_id" => $createRework->id
                ]);
            }

            if ($allDefect->count() > 0) {
                $this->emit('alert', 'success', "Semua DEFECT berhasil di REWORK");
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. DEFECT tidak berhasil di REWORK.");
            }
        } else {
            $this->emit('alert', 'warning', "Data tidak ditemukan.");
        }
    }
}
