<div class="sector-row border rounded p-3 mt-3" data-existing="1">

    <div class="row g-3">

        <div class="col-md-3">
            <label class="form-label">Date</label>
            <input type="date" class="form-control editable" 
                   name="sectors[{{ $index }}][lesson_date]" 
                   value="{{ $sector->lesson_date ?? '' }}" disabled>
        </div>

        <div class="col-md-3">
            <label class="form-label">Departure</label>
            <input type="text" class="form-control editable" 
                   name="sectors[{{ $index }}][departure_airfield]" 
                   value="{{ $sector->departure_airfield ?? '' }}" disabled>
        </div>

        <div class="col-md-3">
            <label class="form-label">Destination</label>
            <input type="text" class="form-control editable" 
                   name="sectors[{{ $index }}][destination_airfield]" 
                   value="{{ $sector->destination_airfield ?? '' }}" disabled>
        </div>

        @php
            $eventType = $trainingEvent?->course->enable_mp_lifus;
            $op = $sector->operation ?? null;
        @endphp

        <div class="col-md-3">
            <label class="form-label">Operation</label>
            <select class="form-select editable" name="sectors[{{ $index }}][operation]" disabled>

                @if($eventType == 1)
                    <option value="1" {{ $op == '1' ? 'selected' : '' }}>PF LHS</option>
                    <option value="2" {{ $op == '2' ? 'selected' : '' }}>PF RHS</option>

                @elseif($eventType == 2 || $eventType == 3)
                    <option value="1" {{ $op == '1' ? 'selected' : '' }}>PF LHS</option>
                    <option value="2" {{ $op == '2' ? 'selected' : '' }}>PM LHS</option>
                    <option value="3" {{ $op == '3' ? 'selected' : '' }}>PF RHS</option>
                    <option value="4" {{ $op == '4' ? 'selected' : '' }}>PM RHS</option>
                @endif

            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Off Blocks</label>
            <input type="time" class="form-control editable" 
                   name="sectors[{{ $index }}][start_time]" 
                   value="{{ $sector->start_time ?? '' }}" disabled>
        </div>

        <div class="col-md-3">
            <label class="form-label">On Blocks</label>
            <input type="time" class="form-control editable" 
                   name="sectors[{{ $index }}][end_time]" 
                   value="{{ $sector->end_time ?? '' }}" disabled>
        </div>

        <div class="col-md-3">
            <label class="form-label">Takeoff</label>
            <input type="time" class="form-control editable" 
                   name="sectors[{{ $index }}][takeoff_time]" 
                   value="{{ $sector->takeoff_time ?? '' }}" disabled>
        </div>

        <div class="col-md-3">
            <label class="form-label">Landing</label>
            <input type="time" class="form-control editable" 
                   name="sectors[{{ $index }}][landing_time]" 
                   value="{{ $sector->landing_time ?? '' }}" disabled>
        </div>

    </div>

    <button type="button" class="btn btn-danger btn-sm mt-2 removeSectorBtn" disabled>
        Remove
    </button>
</div>