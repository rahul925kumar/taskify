@extends('layouts.app')
@section('title', 'Task Reports')
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Task Reports</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Reports</a></li>
        </ul>
    </div>

    {{-- Employee Performance Table --}}
    <div class="card">
        <div class="card-header"><h4 class="card-title">Employee Performance</h4></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Total Tasks</th>
                            <th>Completed</th>
                            <th>Pending</th>
                            <th>Overdue</th>
                            <th>Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $emp)
                            <tr>
                                <td>{{ $emp->name }}</td>
                                <td>{{ $emp->total_tasks }}</td>
                                <td><span class="text-success">{{ $emp->completed_tasks }}</span></td>
                                <td><span class="text-warning">{{ $emp->pending_tasks }}</span></td>
                                <td><span class="text-danger">{{ $emp->overdue_tasks }}</span></td>
                                <td>
                                    @php $rate = $emp->total_tasks > 0 ? round(($emp->completed_tasks / $emp->total_tasks) * 100) : 0; @endphp
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $rate >= 70 ? 'success' : ($rate >= 40 ? 'warning' : 'danger') }}" style="width: {{ $rate }}%">{{ $rate }}%</div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h4 class="card-title">Tasks by Priority</h4></div>
                <div class="card-body"><canvas id="priorityChart" height="250"></canvas></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h4 class="card-title">Tasks by Type</h4></div>
                <div class="card-body"><canvas id="typeChart" height="250"></canvas></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h4 class="card-title">Tasks by Category</h4></div>
                <div class="card-body"><canvas id="categoryChart" height="250"></canvas></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h4 class="card-title">Monthly Tasks ({{ date('Y') }})</h4></div>
        <div class="card-body"><canvas id="monthlyChart" height="100"></canvas></div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/plugin/chart.js/chart.min.js') }}"></script>
<script>
    var colors = ['#1572e8', '#6861ce', '#ffc107', '#31ce36', '#f25961', '#48abf7', '#ff6384', '#36a2eb'];

    new Chart(document.getElementById('priorityChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_map(fn($k) => ucfirst($k), array_keys($tasksByPriority))) !!},
            datasets: [{ data: {!! json_encode(array_values($tasksByPriority)) !!}, backgroundColor: colors }]
        }
    });

    new Chart(document.getElementById('typeChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_map(fn($k) => ucfirst($k), array_keys($tasksByType))) !!},
            datasets: [{ data: {!! json_encode(array_values($tasksByType)) !!}, backgroundColor: colors }]
        }
    });

    new Chart(document.getElementById('categoryChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_map(fn($k) => ucfirst($k), array_keys($tasksByCategory))) !!},
            datasets: [{ data: {!! json_encode(array_values($tasksByCategory)) !!}, backgroundColor: colors }]
        }
    });

    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var monthlyData = @json($monthlyTasks);
    var totalByMonth = new Array(12).fill(0);
    var completedByMonth = new Array(12).fill(0);
    monthlyData.forEach(d => { totalByMonth[d.month-1] = d.total; completedByMonth[d.month-1] = d.completed; });

    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: months,
            datasets: [
                { label: 'Total', data: totalByMonth, backgroundColor: '#1572e8' },
                { label: 'Completed', data: completedByMonth, backgroundColor: '#31ce36' }
            ]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
</script>
@endpush
