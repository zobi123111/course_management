@extends('layout.app')

@section('title', 'Student Reports Section')
@section('sub-title', 'Student Reports Section')
<style>

</style>
@section('content')

<div class="card pt-4">
    <div class="card-body">
     @if(auth()->user()->is_admin == 1)
<table class="table table-bordered table-striped" id="document_table">
    <thead>
        <tr>
            <th>Name</th>
            <th>UK Licence Status</th>
            <th>Associated Ratings (UK)</th>
            <th>EASA Licence Status</th>
            <th>Associated Ratings (EASA)</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @php

        if (!function_exists('getTooltip')) {
        function getTooltip($status, $type) {
        return match ($status) {
        'Red' => "This {type} has expired.",
        'Yellow' => "This {type} will expire soon.",
        'Green' => "This {type} is valid.",
        'Non-Expiring' => "This {type} does not expire.",
        default => "Status unknown.",
        };
        }
        }


        @endphp

        @foreach($users as $user)
        <tr>
            <td>{{ $user->fname }} {{ $user->lname }}</td>

            @php
            $doc = $user->documents;
            $ratingsByLicence = $user->usrRatings->groupBy('linked_to');
            @endphp
        

            {{-- Licence 1 --}}
            <td>
                @if($doc && $doc->licence_file) 
                    @php 
                        if($doc->licence_non_expiring) { 
                            $status = 'Non-Expiring';
                            $color = 'success';
                            $date = 'Non-Expiring';
                        } else {
                            $status = $doc->licence_status; 
                            $color = $status === 'Red' ? 'danger' : ($status === 'Yellow' ? 'warning' : 'success');
                            $date = $doc->licence_expiry_date ? date('d/m/Y', strtotime($doc->licence_expiry_date)) : 'N/A';
                        }

                        $tooltip = getTooltip($status, 'UK License');
                    @endphp
               
            
                <span class="badge bg-{{ $color }}" data-bs-toggle="tooltip" title="{{ $tooltip }}">{{ $date }}</span>
                @else
                <span class="text-muted">Not Uploaded</span>
                @endif
            </td>

            <td>
                <?php

                $groupedEASA = [];

                if (isset($ratingsByLicence['licence_1'])) {
                    //   print_r($ratingsByLicence['licence_1']);
                    foreach ($ratingsByLicence['licence_1'] as $ratings) {
                        $child_id = $ratings->rating_id;
                        $parent_id = $ratings->parent_id;
                        // echo "parent $parent_id  child $child_id <br>";

                        if ($parent_id === null && $ratings->rating) {

                            $groupedEASA[$child_id] = [
                                'parent' => $ratings->rating->name,
                                'children' => [],
                            ];
                        } elseif ($ratings->rating) {
                            $parentRating = $ratings->parentRating;
                            $childRating = $ratings->rating;

                            if (!isset($groupedEASA[$parent_id])) {

                                $groupedEASA[$parent_id] = [
                                    'parent' => $parentRating?->name ?? '',
                                    'children' => [],
                                ];
                            }

                            $groupedEASA[$parent_id]['children'][] = $childRating->name;
                        } else {
                            $parentRating = $ratings->parentRating;
                            $groupedEASA[$parent_id] = [
                                'parent' => $parentRating?->name ?? '',
                                'children' => [],  // No children here
                            ];
                        }
                    }
                }



                ?>

                @foreach ($groupedEASA as $entry)
                <strong>{{ $entry['parent'] }}</strong><br>
                @if (!empty($entry['children']))
                <ul style="margin-left: 15px;">
                    @foreach ($entry['children'] as $child)
                    <li>{{ $child }}</li>
                    @endforeach
                </ul>
                @endif
                <br>
                @endforeach
            </td>





            {{-- Licence 2 --}}
            <td>
                @if($doc && $doc->licence_file_2)
                @php
                if ($doc->licence_non_expiring_2) {
                $status = 'Non-Expiring';
                $color = 'success';
                $date = 'Non-Expiring';
                } else {
                $status = $doc->licence_2_status;
                $color = $status === 'Red' ? 'danger' : ($status === 'Yellow' ? 'warning' : 'success');
                $date = $doc->licence_expiry_date_2 ? date('d/m/Y', strtotime($doc->licence_expiry_date_2)) : 'N/A';
                }
                $tooltip = getTooltip($status, 'EASA Licence');
                @endphp
                <span class="badge bg-{{ $color }}" data-bs-toggle="tooltip" title="{{ $tooltip }}">{{ $date }}</span>
                @else
                <span class="text-muted">Not Uploaded</span>
                @endif
            </td>

            {{-- Associated Ratings (Licence 2) --}}
            <td>
                @php
                $groupedEASA = [];

                if (isset($ratingsByLicence['licence_2'])) {
                foreach ($ratingsByLicence['licence_2'] as $ratings) {
                $child_id = $ratings->rating_id;
                $parent_id = $ratings->parent_id;

                if ($parent_id === null && $ratings->rating) {
                $groupedEASA[$child_id] = [
                'parent' => $ratings->rating->name,
                'children' => [],
                ];
                } elseif ($ratings->rating) {
                $parentRating = $ratings->parentRating;
                $childRating = $ratings->rating;

                if (!isset($groupedEASA[$parent_id])) {
                $groupedEASA[$parent_id] = [
                'parent' => $parentRating?->name ?? 'Unknown Parent',
                'children' => [],
                ];
                }

                $groupedEASA[$parent_id]['children'][] = $childRating->name;
                }
                }
                }
                @endphp

                @foreach ($groupedEASA as $entry)
                <strong>{{ $entry['parent'] }}</strong><br>
                @if (!empty($entry['children']))
                <ul style="margin-left: 15px;">
                    @foreach ($entry['children'] as $child)
                    <li>{{ $child }}</li>
                    @endforeach
                </ul>
                @endif
                <br>
                @endforeach
            </td>

         

            {{-- View Link --}}
            <td>
                <a href="{{ route('user.show', ['user_id' => encode_id($user->id)]) }}" class="view-icon" title="View User" style="font-size:18px; cursor: pointer;">
                    <i class="fa fa-eye text-danger me-2"></i>
                </a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif


    </div>
</div>

@endsection

@section('js_scripts')
<script>
    $(document).ready(function() {
        $('#document_table').DataTable({
            searching: true,
            pageLength: 10,
            language: {
                emptyTable: "No records found"
            }
        });

    });
</script>
@endsection