<div class="sector-row border rounded p-3 mt-3">

    <div class="row g-3">

        <div class="col-md-3">
            <label class="form-label">Date</label>
            <input type="date" class="form-control editable" 
                   name="sectors[][lesson_date]" 
                   value="{{ $sector->lesson_date ?? '' }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Departure</label>
            <input type="text" class="form-control editable" 
                   name="sectors[][departure_airfield]" 
                   value="{{ $sector->departure_airfield ?? '' }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Destination</label>
            <input type="text" class="form-control editable" 
                   name="sectors[][destination_airfield]" 
                   value="{{ $sector->destination_airfield ?? '' }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Off Blocks</label>
            <input type="time" class="form-control editable" 
                   name="sectors[][start_time]" 
                   value="{{ $sector->start_time ?? '' }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Takeoff</label>
            <input type="time" class="form-control editable" 
                   name="sectors[][takeoff_time]" 
                   value="{{ $sector->takeoff_time ?? '' }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Landing</label>
            <input type="time" class="form-control editable" 
                   name="sectors[][landing_time]" 
                   value="{{ $sector->landing_time ?? '' }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">On Blocks</label>
            <input type="time" class="form-control editable" 
                   name="sectors[][end_time]" 
                   value="{{ $sector->end_time ?? '' }}">
        </div>
    </div>

    <button type="button" class="btn btn-danger btn-sm mt-2 removeSectorBtn">
        Remove
    </button>
</div>