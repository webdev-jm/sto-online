<?php

namespace App\Http\Livewire\Customer;

use Livewire\Component;
use App\Models\Salesman;
use App\Models\District;
use App\Models\Area;
use Illuminate\Support\Facades\Session;

class SalesmanQuickCreate extends Component
{
    public bool $showModal = false;

    // Salesman fields
    public string $code = '';
    public string $name = '';
    public string $type = '';
    public $district_id = null;

    // District inline create
    public bool $showDistrictForm = false;
    public string $districtCode = '';
    public string $districtCreatedMessage = '';
    public array $selectedAreas = [];   // area IDs to assign to the new district

    // Area inline create
    public bool $showAreaForm = false;
    public string $areaCode = '';
    public string $areaName = '';
    public string $areaCreatedMessage = '';

    public array $districts = [];
    public array $areas = [];

    public array $salesmanTypes = [
        'DIRECT BOOKING' => 'DIRECT BOOKING',
        'VAN SALESMAN' => 'VAN SALESMAN',
        'PRE-BOOKING' => 'PRE-BOOKING',
    ];

    protected $listeners = ['openSalesmanModal' => 'openModal'];

    public function mount(): void
    {
        $this->loadDistricts();
        $this->loadAreas();
    }

    public function openModal(): void
    {
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetSalesmanForm();
    }

    public function toggleDistrictForm(): void
    {
        $this->showDistrictForm = !$this->showDistrictForm;
        $this->districtCode = '';
        $this->selectedAreas = [];
        $this->districtCreatedMessage = '';
        $this->resetErrorBag('districtCode');
    }

    public function toggleAreaForm(): void
    {
        $this->showAreaForm = !$this->showAreaForm;
        $this->areaCode = '';
        $this->areaName = '';
        $this->areaCreatedMessage = '';
        $this->resetErrorBag(['areaCode', 'areaName']);
    }

    public function saveDistrict(): void
    {
        $this->validate(
            ['districtCode' => 'required|max:255'],
            [],
            ['districtCode' => 'District Code']
        );

        $accountBranch = Session::get('account_branch');

        $district = District::create([
            'account_branch_id' => $accountBranch->id,
            'district_code'     => $this->districtCode,
        ]);

        if (!empty($this->selectedAreas)) {
            $district->areas()->sync($this->selectedAreas);
        }

        $this->district_id = $district->id;
        $this->districtCode = '';
        $this->selectedAreas = [];
        $this->showDistrictForm = false;
        $this->districtCreatedMessage = 'District "' . $district->district_code . '" created and selected.';
        $this->loadDistricts();
    }

    public function saveArea(): void
    {
        $this->validate([
            'areaCode' => 'required|max:255',
            'areaName' => 'required|max:255',
        ], [], ['areaCode' => 'Area Code', 'areaName' => 'Area Name']);

        $account = Session::get('account');
        $accountBranch = Session::get('account_branch');

        $area = Area::create([
            'account_id'        => $account->id,
            'account_branch_id' => $accountBranch->id,
            'code'              => $this->areaCode,
            'name'              => $this->areaName,
        ]);

        if ($this->district_id) {
            $district = District::find($this->district_id);
            $district?->areas()->attach($area->id);
            $suffix = ' and assigned to district.';
        } else {
            $suffix = ' (no district selected — assign via district module).';
        }

        $this->areaCode = '';
        $this->areaName = '';
        $this->showAreaForm = false;
        $this->areaCreatedMessage = 'Area "' . $area->code . ' - ' . $area->name . '" created' . $suffix;
        $this->loadAreas();
    }

    public function save(): void
    {
        $this->validate([
            'code'        => 'required|max:255',
            'name'        => 'required|max:255',
            'type'        => 'required',
            'district_id' => 'nullable|integer',
        ]);

        $account = Session::get('account');
        $accountBranch = Session::get('account_branch');

        $salesman = Salesman::create([
            'account_id'        => $account->id,
            'account_branch_id' => $accountBranch->id,
            'district_id'       => $this->district_id ?: null,
            'code'              => $this->code,
            'name'              => $this->name,
            'type'              => $this->type,
        ]);

        $label = '[' . $salesman->code . '] ' . $salesman->name;

        $this->dispatch('salesmanCreated', id: $salesman->id, label: $label);

        $this->showModal = false;
        $this->resetSalesmanForm();
    }

    private function loadAreas(): void
    {
        $accountBranch = Session::get('account_branch');

        $this->areas = Area::where('account_branch_id', $accountBranch->id)
            ->get()
            ->map(fn($a) => ['id' => $a->id, 'code' => $a->code, 'name' => $a->name])
            ->toArray();
    }

    private function loadDistricts(): void
    {
        $accountBranch = Session::get('account_branch');

        $this->districts = District::where('account_branch_id', $accountBranch->id)
            ->get()
            ->mapWithKeys(fn($d) => [$d->id => $d->district_code])
            ->toArray();
    }

    private function resetSalesmanForm(): void
    {
        $this->code = '';
        $this->name = '';
        $this->type = '';
        $this->district_id = null;
        $this->districtCode = '';
        $this->selectedAreas = [];
        $this->areaCode = '';
        $this->areaName = '';
        $this->districtCreatedMessage = '';
        $this->areaCreatedMessage = '';
        $this->showDistrictForm = false;
        $this->showAreaForm = false;
        $this->resetErrorBag();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.customer.salesman-quick-create');
    }
}
