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
                <th>Licence</th>
                <th>Associated Ratings</th>
                <th>Medical</th>
                <th>Passport Status</th> 
                <th>Action</th> 
            </tr>
        </thead>
        <tbody> 
        @php
            if (!function_exists('getTooltip')) {
                function getTooltip($status, $type) {
                    return match ($status) {
                        'Red' => "This $type has expired.",
                        'Yellow' => "This $type will expire soon.",
                        'Green' => "This $type is valid.",
                        'Non-Expiring' => "This $type does not expire.",
                        default => "Status unknown.",
                    };
                }
            }
        @endphp

        @foreach($users as $user)
            @php
                $doc = $user->documents;
                $ratingsByLicence = $user->usrRatings->groupBy('linked_to');
            @endphp

            {{-- ====== ROW 1: UK DATA ====== --}}
            <tr>
                {{-- Name --}}
                <td>{{ $user->fname }} {{ $user->lname }}</td>

                {{-- UK Licence --}}
                <td>
                    @if($doc && $doc->licence_file_uploaded)
                        @php
                            $status = $doc->licence_non_expiring ? 'Non-Expiring' : $doc->licence_status;
                            $color = $status === 'Red' ? 'danger' : ($status === 'Yellow' ? 'warning' : 'success');
                            $date = $doc->licence_non_expiring ? 'Non-Expiring' : ($doc->licence_expiry_date ? date('d/m/Y', strtotime($doc->licence_expiry_date)) : 'N/A');
                            $tooltip = getTooltip($status, 'UK Licence');
                        @endphp
                        <span class="badge bg-{{ $color }}" data-bs-toggle="tooltip" title="{{ $tooltip }}">{{ $date }}</span>
                    @else
                        <span class="text-muted">Not Uploaded</span>
                    @endif
                </td>

                {{-- UK Ratings --}}
                <td>
                    @php $ratings = $ratingsByLicence['licence_1'] ?? collect(); @endphp
                    @foreach ($ratings as $ur)
                    @php
        $r = $ur->rating;

        $expiry = $ur->expiry_date
            ? \Carbon\Carbon::parse($ur->expiry_date)->format('d/m/Y')
            : 'N/A';

        $status = $ur->expiry_status;
        $color = match($status) {
            'Red' => 'danger',
            'Orange' => 'warning',
            'Amber' => 'info',
            'Blue' => 'primary',
            default => 'secondary'
        };

        $tooltip = $r ? "{$r->name} expires on $expiry" : "Rating not available";
    @endphp

    @if($r)
        <span class="badge bg-{{ $color }}" data-bs-toggle="tooltip" title="{{ $tooltip }}">
            {{ $r->name }}
        </span>

        @if($r->children && $r->children->count())
            @foreach($r->children as $child)
                <span class="badge bg-light text-dark border ms-1" data-bs-toggle="tooltip" title="Child of {{ $r->name }}">
                    → {{ $child->name }}
                </span>
            @endforeach
        @endif
    @else
        <span class="badge bg-secondary">No Rating</span>
    @endif

                    @endforeach
                </td>

                {{-- UK Medical --}}
                <td>
                    @if($doc && $doc->medical_file_uploaded)
                        @php
                            $status = $doc->medical_status;
                            $color = $status === 'Red' ? 'danger' : ($status === 'Yellow' ? 'warning' : 'success');
                            $date = $doc->medical_expirydate ? date('d/m/Y', strtotime($doc->medical_expirydate)) : 'N/A';
                            $tooltip = getTooltip($status, 'UK Medical');
                        @endphp
                        <span class="badge bg-{{ $color }}" data-bs-toggle="tooltip" title="{{ $tooltip }}">{{ $date }}</span>
                    @else
                        <span class="text-muted">Not Uploaded</span>
                    @endif
                </td>

                {{-- Passport --}}
                <td rowspan="2">
                    @if($doc && $doc->passport_file_uploaded)
                        @php
                            $status = $doc->passport_status;
                            $color = $status === 'Red' ? 'danger' : ($status === 'Yellow' ? 'warning' : 'success');
                            $date = $doc->passport_expiry_date ? date('d/m/Y', strtotime($doc->passport_expiry_date)) : 'N/A';
                            $tooltip = getTooltip($status, 'Passport');
                        @endphp
                        <span class="badge bg-{{ $color }}" data-bs-toggle="tooltip" title="{{ $tooltip }}">{{ $date }}</span>
                    @else
                        <span class="text-muted">Not Uploaded</span>
                    @endif
                </td>

                {{-- Action --}}
                <td rowspan="2">
                    <a href="{{ route('user.show', ['user_id' => encode_id($user->id)]) }}" class="view-icon" title="View User" style="font-size:18px;">
                        <i class="fa fa-eye text-danger me-2"></i>
                    </a>
                </td>
            </tr>

            {{-- ====== ROW 2: EASA DATA ====== --}}
            <tr>
                {{-- Empty cell for name --}}
                <td></td>

                {{-- EASA Licence --}}
                <td>
                    @if($doc && $doc->licence_file_uploaded_2)
                        @php
                            $status = $doc->licence_non_expiring_2 ? 'Non-Expiring' : $doc->licence_2_status;
                            $color = $status === 'Red' ? 'danger' : ($status === 'Yellow' ? 'warning' : 'success');
                            $date = $doc->licence_non_expiring_2 ? 'Non-Expiring' : ($doc->licence_expiry_date_2 ? date('d/m/Y', strtotime($doc->licence_expiry_date_2)) : 'N/A');
                            $tooltip = getTooltip($status, 'EASA Licence');
                        @endphp
                        <span class="badge bg-{{ $color }}" data-bs-toggle="tooltip" title="{{ $tooltip }}">{{ $date }}</span>
                    @else
                        <span class="text-muted">Not Uploaded</span>
                    @endif
                </td>

                {{-- EASA Ratings --}}
                <td>
    @php
        $groupedEASA = [];

        if (isset($ratingsByLicence['licence_2'])) {
            foreach ($ratingsByLicence['licence_2'] as $rating) {
                $parent_id = $rating->parent_id;
                $ratingModel = $rating->rating;

                if (!$ratingModel) {
                    continue; // skip if rating is null
                }

                $ratingName = $ratingModel->name ?? 'N/A';

                if ($parent_id === null) {
                    $groupedEASA[$rating->rating_id] = [
                        'parent' => $ratingName,
                        'children' => [],
                    ];
                } else {
                    if (!isset($groupedEASA[$parent_id])) {
                        $groupedEASA[$parent_id] = [
                            'parent' => optional($rating->parentRating)->name ?? 'Unknown Parent',
                            'children' => [],
                        ];
                    }

                    $groupedEASA[$parent_id]['children'][] = $ratingName;
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
    @endforeach

                </td>

                {{-- EASA Medical --}}
                <td>
                    @if($doc && $doc->medical_file_uploaded_2)
                        @php
                            $status = $doc->medical_2_status;
                            $color = $status === 'Red' ? 'danger' : ($status === 'Yellow' ? 'warning' : 'success');
                            $date = $doc->medical_expirydate_2 ? date('d/m/Y', strtotime($doc->medical_expirydate_2)) : 'N/A';
                            $tooltip = getTooltip($status, 'EASA Medical');
                        @endphp
                        <span class="badge bg-{{ $color }}" data-bs-toggle="tooltip" title="{{ $tooltip }}">{{ $date }}</span>
                    @else
                        <span class="text-muted">Not Uploaded</span>
                    @endif
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
