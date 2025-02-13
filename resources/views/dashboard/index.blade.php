@section('title', 'Dashboard')
@section('sub-title', 'Welcome to Dashboard')
@extends('layout.app')
@section('content')
<!--  -->

<div class="row">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Documents Pie Chart</h5>

        <!-- Pie Chart -->
        <canvas id="pieChart" style="max-height: 400px;"></canvas>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
          document.addEventListener("DOMContentLoaded", () => {
            let ctx = document.querySelector('#pieChart').getContext('2d');
            new Chart(ctx, {
              type: 'pie',
              data: {
                labels: ['Total Documents', 'Read Documents', 'Unread Documents'],
                datasets: [{
                  label: 'Document Statistics',
                  data: [
                    {{ $totalDocuments }},
                    {{ $readDocuments }},
                    {{ $unreadDocuments }}
                  ],
                  backgroundColor: [
                    'rgb(54, 162, 235)',  // Blue
                    'rgb(75, 192, 192)',  // Green
                    'rgb(255, 99, 132)'   // Red
                  ],
                  hoverOffset: 4
                }]
              }
            });
          });
        </script>
        <!-- End Pie Chart -->

      </div>
    </div>
  </div>
</div>


@endsection

@section('js_scripts')


@endsection