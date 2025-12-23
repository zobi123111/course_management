<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Approved</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
    </style>
</head>

<body>
    <h2>Booking Approved</h2>
    <p>A booking has been approved. <br> Here are the details:</p>

    <table>
        <tr>
            <th>Student</th>
            <td>{{ $booking->users->fname . ' ' . $booking->users->lname ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Organization Unit</th>
            <td>{{ $booking->organizationUnit->org_unit_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Resource</th>
            <td>{{ $booking->resources->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Start Time</th>
            <td>{{ $booking->start }}</td>
        </tr>
        <tr>
            <th>End Time</th>
            <td>{{ $booking->end }}</td>
        </tr>
        <tr>
            <th>Booking Type</th>
            <td>
                @if($booking->booking_type == 1)
                    Solo
                @elseif($booking->booking_type == 2)
                    Lesson
                @elseif($booking->booking_type == 3)
                    Standby
                @endif
            </td>
        </tr>
        <tr>
            <th>Resource Type</th>
            <td>
                @if($booking->resource_type == 1)
                    Plane
                @elseif($booking->resource_type == 2)
                    Simulator
                @elseif($booking->resource_type == 3)
                    Classroom
                @endif
            </td>
        </tr>
        <tr>
            <th>Instructor</th>
            <td>{{ $booking->instructor_id ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ $booking->status }}</td>
        </tr>
    </table>

</body>

</html>
