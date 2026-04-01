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

        <div class="col-md-3">
            <label class="form-label">Off Blocks</label>
            <input type="time" class="form-control editable" 
                   name="sectors[{{ $index }}][start_time]" 
                   value="{{ $sector->start_time ?? '' }}" disabled>
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

        <div class="col-md-3">
            <label class="form-label">On Blocks</label>
            <input type="time" class="form-control editable" 
                   name="sectors[{{ $index }}][end_time]" 
                   value="{{ $sector->end_time ?? '' }}" disabled>
        </div>
    </div>

    <button type="button" class="btn btn-danger btn-sm mt-2 removeSectorBtn" disabled>
        Remove
    </button>
</div>