<?php

namespace App\Livewire\Scheduling\Catalog;

use App\Models\ProcedureCategory;
use App\Models\Procedures as ProceduresModel;
use Carbon\Carbon;
use Livewire\Component;

class Procedures extends Component
{
    protected $listeners = [
        'refreshNewProcedure' => '$refresh',
    ];

    public $searchProcedures, $filterByCategory;
    public $categories = [];

    public function mount()
    {
        $this->categories = ProcedureCategory::query()->where('business_id', auth()->user()->business->id)->get();
    }

    public function render()
    {
        $proceduresQuery = ProceduresModel::query()
            ->with('category')
            ->where('business_id', auth()->user()->business->id)
            ->orderBy('name', 'ASC');

        if ($this->searchProcedures) {
            $proceduresQuery->where(function ($query) {
                $query->where('name', 'LIKE', '%' . $this->searchProcedures . '%')->orWhereRelation('category', 'name', 'LIKE', '%' . $this->searchProcedures . '%');
            });
        }

        if ($this->filterByCategory) {
            $proceduresQuery->where(function ($query) {
                $query->where('product_category_id', $this->filterByCategory);
            });
        }

        $procedures = $proceduresQuery->paginate(10);

        foreach ($procedures as $procedure) {
            $procedure->duration = Carbon::createFromFormat('H:i:s', $procedure->duration)->format('H:i');
        }

        return view('livewire.scheduling.catalog.procedures', [
            'procedures' => $procedures
        ]);
    }
}
