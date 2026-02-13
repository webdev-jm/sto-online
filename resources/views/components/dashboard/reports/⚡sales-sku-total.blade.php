<?php

use Livewire\Component;

new class extends Component
{
    public $year;

    public function mount() {
        $this->year = date('Y');
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SALES BY SKU ({{ $year }})</h3>
            <div class="card-tools">
                <input type="number" class="form-control form-control-sm" wire:model.live="year">
            </div>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container4"></div>
        </div>
    </div>
</div>

@assets
    <script src="https://cdn.jsdelivr.net/npm/@highcharts/grid-lite/grid-lite.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@highcharts/grid-lite/css/grid-lite.css">
@endassets

@script
    <script>
        function generateRandomData(rows) {
            const names = ['John', 'Jane', 'Alex', 'Chris', 'Katie', 'Michael'];
            const departments = ['HR', 'Engineering', 'Sales', 'Marketing', 'Finance'];
            const positions = [
                'Manager',
                'Software Developer',
                'Sales Executive',
                'Marketing Specialist',
                'Financial Analyst'
            ];
            const columns = {
                ID: [],
                Name: [],
                Department: [],
                Position: [],
                Email: [],
                Phone: []
            };

            for (let i = 0; i < rows; i++) {
                const nameIndex = Math.floor(Math.random() * names.length);
                const departmentIndex = Math.floor(Math.random() * departments.length);
                const positionIndex = Math.floor(Math.random() * positions.length);
                const id = i + 1;
                const email = `${names[nameIndex].toLowerCase()}${id}@example.com`;
                const phone = `123-456-7${Math.floor(Math.random() * 1000)
                    .toString()
                    .padStart(3, '0')}`;

                columns.ID.push(id);
                columns.Name.push(names[nameIndex]);
                columns.Department.push(departments[departmentIndex]);
                columns.Position.push(positions[positionIndex]);
                columns.Email.push(email);
                columns.Phone.push(phone);
            }

            return columns;
        }

        console.log(generateRandomData(100));

        Grid.grid('container4', {
            dataTable: {
                columns: generateRandomData(100)
            },
            rendering: {
                rows: {
                    minVisibleRows: 5
                }
            },
            columns: [{
                id: 'ID',
                width: 60
            }]
        });
    </script>
@endscript
