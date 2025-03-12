<!-- @section('title', 'Training Event') -->
@section('sub-title', 'Training Event')
@extends('layout.app')
@section('content')

<div class="card">
    <div class="card-body">
      <h5 class="card-title">{{ $trainingEvent?->course?->course_name }}</h5>

      <!-- Default Tabs -->
      <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="overview" aria-controls="overview" aria-selected="false" tabindex="-1">Overview</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="Lesson-tab" data-bs-toggle="tab" data-bs-target="#Lesson" type="button" role="tab" aria-controls="Lesson" aria-selected="true">Lesson Plan</button>
        </li>
        @foreach($groupUsers as $user)
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="student-tab-{{ $user->id }}" data-bs-toggle="tab" data-bs-target="#student-{{ $user->id }}" type="button" role="tab" aria-controls="contact" aria-selected="false">
                    {{ $user->fname }} {{ $user->lname }}
                </button>
            </li>
        @endforeach
      </ul>
      <div class="tab-content pt-2" id="myTabContent">
        <div class="tab-pane fade p-3" id="overview" role="tabpanel" aria-labelledby="overview-tab">
            <div class="card shadow-sm p-3">
                <h4 class="mb-3">Training Event Overview</h4>

                <div class="row mb-2">
                    <div class="col-md-6">
                        <strong>Course Name:</strong> {{ $trainingEvent->course->course_name ?? 'N/A' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Instructor:</strong> 
                        {{ optional($trainingEvent->instructor)->fname }} {{ optional($trainingEvent->instructor)->lname }}
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-6">
                        <strong>Start Time:</strong> {{ date('h:i A', strtotime($trainingEvent->start_time)) }}
                    </div>
                    <div class="col-md-6">
                        <strong>End Time:</strong> {{ date('h:i A', strtotime($trainingEvent->end_time)) }}
                    </div>
                </div>

                <div class="mt-3">
                    <strong>Students:</strong>
                    <ul class="list-group mt-2">
                        @forelse($groupUsers as $user)
                            <li class="list-group-item">
                                {{ $user->fname }} {{ $user->lname }}
                            </li>
                        @empty
                            <li class="list-group-item text-muted">No students assigned</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="tab-pane fade active show" id="Lesson" role="tabpanel" aria-labelledby="Lesson-tab">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Earum consequatur maiores possimus aperiam illo itaque nemo id vitae. Corporis aperiam reiciendis enim, nulla recusandae, hic blanditiis consectetur perferendis dignissimos culpa animi soluta necessitatibus expedita libero dolorum vero nisi quod corrupti sed? At quo quod minima fuga veniam corporis necessitatibus blanditiis, ab suscipit magnam. Ex, aliquid dolores. Cum quo pariatur quidem officiis porro ullam? Perspiciatis, necessitatibus id excepturi pariatur maiores aliquid a vel fugiat illo esse eius doloremque animi quasi repellat autem tempora, ex, est iusto qui debitis architecto dolore? Ex ea sit, magnam quae veritatis dolore exercitationem consequatur minus doloribus!
        </div>
        <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
          Saepe animi et soluta ad odit soluta sunt. Nihil quos omnis animi debitis cumque. Accusantium quibusdam perspiciatis qui qui omnis magnam. Officiis accusamus impedit molestias nostrum veniam. Qui amet ipsum iure. Dignissimos fuga tempore dolor.
        </div>
      </div><!-- End Default Tabs -->

    </div>
</div>
@endsection

@section('js_scripts')

<script>
$(document).ready(function() {
  
});
</script>

@endsection